<?php
/**
 * Endpoint: Descargar XML de Comprobante
 * GET /api/documentos/descargar_xml?serie=B001&correlativo=62
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../BL/DocumentosBL.php';
require_once __DIR__ . '/documentos_helper.php';

// Validar que exista la sesión del usuario (JWT validado por auth.php)
if (!isset($_SESSION['objBEUsuario'])) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'error' => 'No autorizado. Inicie sesión nuevamente.'));
    exit;
}

$o = $_SESSION['objBEUsuario'];

$serie = $_GET['serie'] ?? '';
$correlativo = $_GET['correlativo'] ?? '';
$check = $_GET['check'] ?? '';

if (empty($serie) || empty($correlativo)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'error' => 'Serie y correlativo son requeridos.'));
    exit;
}

try {
    $bl = new DocumentosBL();
    $data = $bl->obtenerXml($serie, $correlativo, $o);

    // Un XML válido debe empezar con "<" y no contener texto de mock de prueba
    $esValido = ($data !== null && strlen($data) > 0 && trim($data)[0] === '<' && strpos($data, 'Mock XML Content') === false);
    
    // Si no es válido en la base de datos, intentamos ver si el comprobante existe para generarlo en caliente
    $comprobanteDatos = null;
    if (!$esValido) {
        $comprobanteDatos = $bl->obtenerDatosComprobante($serie, $correlativo, $o);
    }

    $existe = ($esValido || $comprobanteDatos !== null);

    if ($check === '1') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('success' => $existe, 'error' => $existe ? null : 'Archivo no encontrado'));
        exit;
    }

    if (!$existe) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(array('success' => false, 'error' => 'Archivo no encontrado'));
        exit;
    }

    // Si no es un XML válido pero el comprobante existe, lo generamos en caliente
    if (!$esValido && $comprobanteDatos !== null) {
        $data = DocumentosHelper::generarXml($comprobanteDatos['header'], $comprobanteDatos['details'], $comprobanteDatos['header']);
    }

    // Servir el XML para descarga directa
    $filename = $serie . '-' . $correlativo . '.xml';
    header('Content-Type: application/xml');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($data));
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');
    echo $data;
    exit;

} catch (Exception $e) {
    error_log("Error en descargar_xml.php: " . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('success' => false, 'error' => 'Error al procesar la descarga.'));
    exit;
}
?>
