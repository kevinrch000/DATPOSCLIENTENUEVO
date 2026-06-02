<?php
/**
 * DatPOS - API: Usuario
 * Reemplaza los WebMethods de las páginas .aspx.vb relacionados a usuarios
 * 
 * Endpoints (via parámetro 'action'):
 *   - consultarUsuarios
 *   - cambiarContrasena
 *   - cargarFotoUsuario
 */

session_start();
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../BL/BLUsuario.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

// Verificar sesión
if (!isset($_SESSION['objBEUsuario'])) {
    http_response_code(401);
    jsonResponse(array('d' => '-1'));
}

$objUsuario = $_SESSION['objBEUsuario'];
$input = getJsonInput();
$action = $input['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    case 'consultarUsuarios':
        $blUsuario = new BLUsuario();
        $rows = $blUsuario->consultarUsuarios($objUsuario);
        
        $result = array();
        foreach ($rows as $fila) {
            $obj = new BEUsuario();
            $obj->ccod_usuario  = $fila[0] ?? '';
            $obj->cdsc_usuario  = $fila[1] ?? '';
            $obj->cdirec        = $fila[2] ?? '';
            $obj->cdsc_rol      = $fila[3] ?? '';
            $obj->estado        = $fila[4] ?? '';
            $obj->cdsc_tienda   = $fila[5] ?? '';
            $result[] = $obj;
        }
        
        jsonResponse(array('d' => $result));
        break;

    case 'cambiarContrasena':
        $blUsuario = new BLUsuario();
        $cambioClave = new BEUsuario();
        $cambioClave->cpassw = $input['contraActual'] ?? '';
        $cambioClave->cpasswordnueva = $input['contraNueva'] ?? '';

        $result = $blUsuario->cambiarContrasena($cambioClave, $objUsuario);
        jsonResponse(array('d' => $result));
        break;

    case 'cargarFotoUsuario':
        $blUsuario = new BLUsuario();
        $rows = $blUsuario->cargarFotoUsuario($objUsuario);
        
        $foto = '';
        if (count($rows) > 0 && !empty($rows[0][0])) {
            $foto = imageToBase64($rows[0][0]);
        }
        
        jsonResponse(array('d' => $foto));
        break;

    default:
        http_response_code(400);
        jsonResponse(array('error' => 'Acción no válida: ' . $action));
        break;
}
?>
