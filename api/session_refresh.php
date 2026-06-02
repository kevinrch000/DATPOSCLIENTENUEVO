<?php
/**
 * DatPOS - API: Renovar Sesion (JWT)
 * Llamado por AJAX cuando el usuario da click en "Mantener sesion"
 * Equivale a: ActualizarTimeOut() del Site.Master
 * 
 * Renueva el JWT emitiendo uno nuevo con expiracion extendida.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

header('Content-Type: application/json; charset=utf-8');

if (isset($_SESSION['objBEUsuario'])) {
    // Renovar JWT (nuevo token con nueva expiracion)
    $renewed = renovarJwt();
    if ($renewed) {
        $_SESSION['last_activity'] = time();
        echo json_encode(array('success' => true, 'message' => 'Sesion renovada'));
    } else {
        // JWT invalido pero session activa — re-emitir desde session
        emitirJwt($_SESSION['objBEUsuario']);
        $_SESSION['last_activity'] = time();
        echo json_encode(array('success' => true, 'message' => 'Sesion renovada'));
    }
} else {
    http_response_code(401);
    echo json_encode(array('success' => false, 'message' => 'Sesion expirada'));
}
?>
