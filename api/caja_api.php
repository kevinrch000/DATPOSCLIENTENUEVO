<?php
/**
 * DatPOS - API: Cajas (Administración)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

switch ($method) {
    case 'ConsultarCajas':
        // sp_consultarcajas @ccod_empresa -> id_caja(0), ccod_caja(1), cdsc_caja(2), cstatus(3)
        $rows = Database::selectStoredTenant('sp_consultarcajas', array('@ccod_empresa' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'item' => "<input id='" . strval($f[1] ?? '') . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'ccod_caja' => strval($f[1] ?? ''), 'cdsc_caja' => strval($f[2] ?? ''), 'estado' => strval($f[3] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCaja':
        $input = getJsonInput();
        // sp_consultarcaja => SELECT * FROM Cajas:
        //   id_caja(0), ccod_cia(1), ccod_caja(2), cdsc_caja(3), cstatus(4), ...
        $rows = Database::selectStoredTenant('sp_consultarcaja', array('@ccod_empresa' => $objUsuario->ccod_empresa, '@ccod_caja' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_caja' => strval($f[2] ?? ''), 'cdsc_caja' => strval($f[3] ?? ''), 'cstatus' => strval($f[4] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarNumeradores':
        $input = getJsonInput();
        // sp_consultarnumeradores -> id_numer(0), cdoc_tipo(1), cdoc_serie(2), cdoc_nro(3)
        $rows = Database::selectStoredTenant('sp_consultarnumeradores', array('@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_caja' => $input['caja'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_numer' => strval($f[0] ?? ''), 'cdsc_numer' => '', 'cdoc_tipo' => strval($f[1] ?? ''), 'cdoc_serie' => strval($f[2] ?? ''), 'cdoc_nro' => strval($f[3] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['caja'][0] ?? array();
        $op   = $input['operacion'] ?? '';
        $sp   = ($op === 'nuevo') ? 'webDatpos_insertarcaja' : 'sp_editarcaja';
        Database::executeStoredTenant($sp, array(
            '@ccod_empresa' => $objUsuario->ccod_empresa,
            '@ccod_caja'    => $data['ccod_caja'] ?? '',
            '@cdsc_caja'    => $data['cdsc_caja'] ?? '',
            '@ccod_usuario' => $objUsuario->ccod_usuario,
        ), $objUsuario);
        // Numeradores: si vienen, se podrían guardar via sp_insertarnumerador (pendiente)
        jsonResponse(array('d' => array(array('ccod_caja' => $data['ccod_caja'] ?? ''))));
        break;

    case 'Eliminar':
        $input = getJsonInput();
        Database::executeStoredTenant('sp_eliminarcaja', array(
            '@ccod_empresa' => $objUsuario->ccod_empresa,
            '@ccod_caja'    => $input['caja'] ?? ''
        ), $objUsuario);
        jsonResponse(array('d' => true));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
