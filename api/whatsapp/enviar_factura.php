<?php
/**
 * DatPOS - API: Enviar Factura por WhatsApp (wa.me link)
 * 
 * Genera una URL wa.me con mensaje formateado para enviar
 * la confirmación de pago por WhatsApp.
 * 
 * POST /api/whatsapp/enviar_factura.php
 * Body JSON: {
 *   "numero": "987654321",
 *   "num_documento": "B001-00000062",
 *   "monto_total": "150.00",
 *   "nombre_empresa": "Mi Empresa SAC",
 *   "ruc_empresa": "20123456789"
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
// Recibir datos JSON
// ============================================================
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(array('success' => false, 'error' => 'Datos inválidos'));
    exit;
}

$numero = preg_replace('/[^0-9]/', '', trim($data['numero'] ?? ''));
$numDocumento = trim($data['num_documento'] ?? '');
$montoTotal = trim($data['monto_total'] ?? '');
$nombreEmpresa = trim($data['nombre_empresa'] ?? 'DatPOS');
$rucEmpresa = trim($data['ruc_empresa'] ?? '');
$fechaDocumento = trim($data['fecha_documento'] ?? date('d/m/Y'));

// Validaciones
if (strlen($numero) < 9) {
    echo json_encode(array('success' => false, 'error' => 'Número de WhatsApp inválido (mínimo 9 dígitos)'));
    exit;
}

if (empty($numDocumento)) {
    echo json_encode(array('success' => false, 'error' => 'Número de documento no proporcionado'));
    exit;
}

// ============================================================
// Construir mensaje WhatsApp
// ============================================================

// Agregar código de país si no lo tiene (Perú +51)
if (strlen($numero) === 9) {
    $numeroCompleto = '51' . $numero;
} else {
    $numeroCompleto = $numero;
}

// Construir mensaje formateado (sin emojis, solo formato WhatsApp)
$linea = "─────────────────────";

$mensaje = "*COMPROBANTE DE PAGO*\n"
    . "{$linea}\n\n"
    . "Empresa: *{$nombreEmpresa}*\n";

if (!empty($rucEmpresa)) {
    $mensaje .= "RUC: {$rucEmpresa}\n";
}

$mensaje .= "Documento: *{$numDocumento}*\n"
    . "Fecha: {$fechaDocumento}\n\n"
    . "{$linea}\n"
    . "TOTAL: *{$montoTotal}*\n"
    . "{$linea}\n\n"
    . "_Gracias por su compra_";

// Codificar para URL
$mensajeCodificado = rawurlencode($mensaje);
$waUrl = "https://wa.me/{$numeroCompleto}?text={$mensajeCodificado}";

// ============================================================
// Retornar URL para que el frontend la abra
// ============================================================
echo json_encode(array(
    'success' => true,
    'url' => $waUrl,
    'message' => "Enlace WhatsApp generado para +{$numeroCompleto}"
));
?>
