<?php
/**
 * DatPOS - API: TiposOperacion (Administración)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

switch ($method) {
    case 'ConsultarTiposOperacion':
        // sp_consultartiposoperacion => SELECT * FROM TipoOperacion
        //   id_tipoper(0), ccod_cia(1), ccod_tipoper(2), cdsc_tipoper(3), ctipo_flag(4), cstatus(5)
        $rows = Database::selectStoredTenant('sp_consultartiposoperacion', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $code = strval($f[2] ?? '');
            $flag = strval($f[4] ?? '');
            $cs   = strval($f[5] ?? '');
            // FIX 73 / BUG 2.5: traducir el flag para que la columna
            // TIPO de la tabla muestre "Salida" / "Ingreso" en vez de
            // "S" / "I" (codigo crudo de TipoOperacion.ctipo_flag).
            $flagTxt = $flag;
            if ($flag === 'S')      $flagTxt = 'Salida';
            else if ($flag === 'I') $flagTxt = 'Ingreso';
            else if ($flag === 'T') $flagTxt = 'Transferencia';
            $lst[] = array(
                'item'        => "<input id='" . $code . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_ctoper'   => strval($f[0] ?? ''),
                'ccod_toper'  => $code,
                'cdsc_toper'  => strval($f[3] ?? ''),
                'flag'        => $flagTxt,
                'ctipo_flag'  => $flag,  // codigo crudo por compatibilidad
                'estado'      => ($cs === 'A' || $cs === '1') ? 'Activo' : 'Inactivo',
                'cstatus'     => $cs,
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarTipoOperacion':
        $input = getJsonInput();
        // sp_consultartipooperacion @ccod_cia, @ccod_tipoper => SELECT * FROM TipoOperacion ...
        $rows = Database::selectStoredTenant('sp_consultartipooperacion', array('@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tipoper' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_ctoper'  => strval($f[0] ?? ''),
                'ccod_toper' => strval($f[2] ?? ''),
                'cdsc_toper' => strval($f[3] ?? ''),
                'ctipo_flag' => strval($f[4] ?? ''),
                'cstatus'    => strval($f[5] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['tipooperacion'][0] ?? array();
        $op = $input['operacion'] ?? '';
        $cs = strval($data['cstatus'] ?? '1');
        try {
            if ($op === 'nuevo') {
                Database::executeStoredTenant('sp_insertartipooperacion', array(
                    '@ccod_cia'      => $objUsuario->ccod_empresa,
                    '@ccod_tipoper'  => $data['ccod_toper'] ?? '',
                    '@cdsc_tipoper'  => $data['cdsc_toper'] ?? '',
                    '@ctipo_flag'    => $data['ctipo_flag'] ?? '',
                    '@cstatus'       => ($cs === '1' || $cs === 'A') ? 'A' : 'I',
                    '@ccod_usuario'  => $objUsuario->ccod_usuario,
                ), $objUsuario);
            } else {
                Database::executeStoredTenant('sp_editartipooperacion', array(
                    '@ccod_cia'     => $objUsuario->ccod_empresa,
                    '@ccod_tipoper' => $data['ccod_toper'] ?? '',
                    '@cdsc_tipoper' => $data['cdsc_toper'] ?? '',
                    '@ctipo_flag'   => $data['ctipo_flag'] ?? '',
                    '@cstatus'      => ($cs === '1' || $cs === 'A') ? 'A' : 'I',
                ), $objUsuario);
            }
            // El JS espera ccod_toper === 'OK' como exito (igual que UnidadMedida / Almacenes)
            jsonResponse(array('d' => array(array('ccod_toper' => 'OK', 'cdsc_toper' => ''))));
        } catch (Exception $e) {
            error_log('[tipooperacion_api Guardar] '.$e->getMessage());
            jsonResponse(array('d' => array(array('ccod_toper' => 'ERR', 'cdsc_toper' => $e->getMessage()))));
        }
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $cod = $input['codigo'] ?? $input['id'] ?? $input['tipooperacion'] ?? '';
        try {
            Database::executeStoredTenant('sp_eliminartipooperacion', array(
                '@ccod_cia'     => $objUsuario->ccod_empresa,
                '@ccod_tipoper' => $cod
            ), $objUsuario);
            jsonResponse(array('d' => array(array('ccod_toper' => 'OK', 'cdsc_toper' => ''))));
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // FK violation -> 547 (asignado a articulos), igual que VB original
            $code = (stripos($msg, '547') !== false) ? '547' : 'ERR';
            error_log('[tipooperacion_api Eliminar] '.$msg);
            jsonResponse(array('d' => array(array('ccod_toper' => $code, 'cdsc_toper' => $msg))));
        }
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
