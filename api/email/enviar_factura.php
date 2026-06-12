<?php
/**
 * DatPOS - API: Enviar Factura por Email
 * 
 * Recibe un PDF en base64 y lo envía como adjunto al email del cliente.
 * Usa PHPMailer con Gmail SMTP (App Password).
 * 
 * POST /api/email/enviar_factura.php
 * Body JSON: {
 *   "email": "correo@ejemplo.com",
 *   "num_documento": "B001-00000062",
 *   "pdf_base64": "JVBERi0xLjQ...",
 *   "nombre_archivo": "Factura_B001-00000062.pdf",
 *   "monto_total": "150.00",
 *   "nombre_cliente": "Juan Pérez",
 *   "nombre_empresa": "Mi Empresa SAC"
 * }
 */

header('Content-Type: application/json; charset=utf-8');

// ============================================================
// Autenticación: verificar sesión JWT
// ============================================================
require_once __DIR__ . '/../../includes/auth.php';

if (!isset($_SESSION['objBEUsuario']) || $_SESSION['objBEUsuario'] === null) {
    echo json_encode(array('success' => false, 'error' => 'Sesión no válida'));
    exit;
}

// ============================================================
// Cargar configuración y PHPMailer
// ============================================================
require_once __DIR__ . '/../../config/email_config.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../libs/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ============================================================
// Recibir datos JSON
// ============================================================
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(array('success' => false, 'error' => 'Datos inválidos'));
    exit;
}

// Validar campos obligatorios
$email = trim($data['email'] ?? '');
$numDocumento = trim($data['num_documento'] ?? '');
$pdfBase64 = $data['pdf_base64'] ?? '';
$nombreArchivo = trim($data['nombre_archivo'] ?? 'Factura_DatPOS.pdf');
$montoTotal = trim($data['monto_total'] ?? '');
$nombreCliente = trim($data['nombre_cliente'] ?? 'Cliente');
$nombreEmpresa = trim($data['nombre_empresa'] ?? 'DatPOS');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(array('success' => false, 'error' => 'Email inválido'));
    exit;
}

if (empty($pdfBase64)) {
    echo json_encode(array('success' => false, 'error' => 'PDF no proporcionado'));
    exit;
}

if (empty($numDocumento)) {
    echo json_encode(array('success' => false, 'error' => 'Número de documento no proporcionado'));
    exit;
}

// ============================================================
// Decodificar PDF base64 a archivo temporal
// ============================================================
// Limpiar el prefijo data URI si existe
$pdfBase64Clean = $pdfBase64;
if (strpos($pdfBase64Clean, ',') !== false) {
    $pdfBase64Clean = explode(',', $pdfBase64Clean, 2)[1];
}

$pdfBinary = base64_decode($pdfBase64Clean);
if ($pdfBinary === false) {
    echo json_encode(array('success' => false, 'error' => 'Error al decodificar el PDF'));
    exit;
}

$tmpDir = sys_get_temp_dir();
$tmpFile = tempnam($tmpDir, 'datpos_factura_') . '.pdf';
file_put_contents($tmpFile, $pdfBinary);

// ============================================================
// Construir y enviar email con PHPMailer
// ============================================================
try {
    $mail = new PHPMailer(true);  // true = habilitar excepciones

    // Configuración SMTP
    $mail->isSMTP();
    $mail->Host       = EMAIL_HOST;
    $mail->SMTPAuth   = true;
    $mail->Username   = EMAIL_USER;
    $mail->Password   = EMAIL_PASS;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = EMAIL_PORT;
    $mail->CharSet    = EMAIL_CHARSET;
    $mail->SMTPDebug  = EMAIL_DEBUG;

    // Remitente y destinatario
    $mail->setFrom(EMAIL_FROM_ADDRESS, EMAIL_FROM_NAME);
    $mail->addAddress($email, $nombreCliente);

    // Asunto
    $mail->Subject = "Tu comprobante de pago - {$numDocumento}";

    // Cuerpo HTML del email
    $mail->isHTML(true);
    $mail->Body = buildEmailHtml($numDocumento, $montoTotal, $nombreCliente, $nombreEmpresa);
    
    // Texto plano alternativo
    $mail->AltBody = "Estimado/a {$nombreCliente},\n\n" .
        "Adjuntamos tu comprobante de pago.\n\n" .
        "Documento: {$numDocumento}\n" .
        "Monto Total: {$montoTotal}\n\n" .
        "Gracias por tu preferencia.\n" .
        "{$nombreEmpresa} - DatPOS";

    // Adjuntar PDF
    $mail->addAttachment($tmpFile, $nombreArchivo, 'base64', 'application/pdf');

    // Enviar
    $mail->send();

    // Limpiar archivo temporal
    if (file_exists($tmpFile)) {
        unlink($tmpFile);
    }

    echo json_encode(array(
        'success' => true,
        'message' => "Email enviado correctamente a {$email}"
    ));

} catch (Exception $e) {
    // Limpiar archivo temporal en caso de error
    if (file_exists($tmpFile)) {
        unlink($tmpFile);
    }

    error_log("Error enviando email factura [{$numDocumento}] a [{$email}]: " . $e->getMessage());
    
    echo json_encode(array(
        'success' => false,
        'error' => 'Error al enviar el email: ' . $mail->ErrorInfo
    ));
}

// ============================================================
// Función: Construir cuerpo HTML del email
// ============================================================
function buildEmailHtml($numDocumento, $montoTotal, $nombreCliente, $nombreEmpresa) {
    return '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    </head>
    <body style="margin:0; padding:0; background-color:#f4f7fb; font-family:\'Segoe UI\', Roboto, Arial, sans-serif;">
        <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f7fb; padding:30px 0;">
            <tr>
                <td align="center">
                    <table width="500" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08);">
                        
                        <!-- Header con gradiente -->
                        <tr>
                            <td style="background:linear-gradient(135deg, #0d3d6e 0%, #1565a8 60%, #228ac9 100%); padding:28px 30px; text-align:center;">
                                <h1 style="color:#ffffff; font-size:22px; margin:0 0 4px; font-weight:700; letter-spacing:0.5px;">DATPOS</h1>
                                <p style="color:rgba(255,255,255,0.85); font-size:13px; margin:0;">Sistema de Punto de Venta</p>
                            </td>
                        </tr>

                        <!-- Contenido -->
                        <tr>
                            <td style="padding:30px;">
                                <p style="color:#333; font-size:15px; margin:0 0 20px; line-height:1.5;">
                                    Estimado/a <strong>' . htmlspecialchars($nombreCliente) . '</strong>,
                                </p>
                                <p style="color:#555; font-size:14px; margin:0 0 24px; line-height:1.6;">
                                    Adjuntamos tu comprobante de pago electrónico. A continuación el resumen:
                                </p>

                                <!-- Tarjeta de datos -->
                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0f5fa; border-radius:12px; overflow:hidden; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:20px;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding:8px 0; color:#666; font-size:13px; border-bottom:1px solid #e0e8f0;">Documento:</td>
                                                    <td style="padding:8px 0; color:#0d3d6e; font-size:14px; font-weight:700; text-align:right; border-bottom:1px solid #e0e8f0;">' . htmlspecialchars($numDocumento) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:8px 0; color:#666; font-size:13px; border-bottom:1px solid #e0e8f0;">Empresa:</td>
                                                    <td style="padding:8px 0; color:#333; font-size:14px; font-weight:600; text-align:right; border-bottom:1px solid #e0e8f0;">' . htmlspecialchars($nombreEmpresa) . '</td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:12px 0 8px; color:#666; font-size:13px;">Monto Total:</td>
                                                    <td style="padding:12px 0 8px; color:#228ac9; font-size:20px; font-weight:800; text-align:right;">' . htmlspecialchars($montoTotal) . '</td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>

                                <p style="color:#888; font-size:12px; margin:0 0 8px; text-align:center;">
                                    📎 El comprobante en PDF se encuentra adjunto a este correo.
                                </p>
                            </td>
                        </tr>

                        <!-- Footer -->
                        <tr>
                            <td style="background:#f8fafb; padding:20px 30px; text-align:center; border-top:1px solid #eef2f7;">
                                <p style="color:#999; font-size:11px; margin:0 0 4px;">
                                    Este es un mensaje automático generado por DatPOS.
                                </p>
                                <p style="color:#bbb; font-size:11px; margin:0;">
                                    © ' . date('Y') . ' ' . htmlspecialchars($nombreEmpresa) . ' — Todos los derechos reservados
                                </p>
                            </td>
                        </tr>

                    </table>
                </td>
            </tr>
        </table>
    </body>
    </html>';
}
?>
