<?php
/**
 * DatPOS - API: CierreCaja (Ventas)
 * Reemplaza lógica DA mezclada
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAAperturaCaja.php';
require_once __DIR__ . '/../DA/DAConsultaDocumento.php';
require_once __DIR__ . '/../BL/BLAperturaCaja.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

function S($v) { return ($v !== null) ? strval($v) : ''; }

$objBL = new BLAperturaCaja();

switch ($method) {
    case 'ConsultarCierreCaja':
        $rows = $objBL->ConsultarCierreCaja($objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'item' => "<input id='" . S($f[0]) . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_turno' => S($f[0]),
                'ccod_tienda' => S($f[1]),
                'ccod_usuario' => S($f[2]),
                'ccod_caja' => S($f[3]),
                'nmonto_ini' => S($f[4]),
                'nmonto_fin' => S($f[5]),
                'dfecha_ini' => S($f[6]),
                'dfecha_fin' => S($f[7]),
                'cstatus' => S($f[8]),
                'cdsc_tienda' => S($f[1]),
                'cdsc_usuario' => S($f[2]),
                'cdsc_caja' => S($f[3])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['DatTurno'][0] ?? array();
        $idTurno = $data['id_turno'] ?? '';
        if (!ctype_digit(strval($idTurno))) {
            $turnos = $objBL->ConsultarCierreCaja($objUsuario);
            $idTurno = isset($turnos[0][0]) ? $turnos[0][0] : 0;
        }
        
        require_once __DIR__ . '/../BE/BEAperturaCaja.php';
        $DatTurno = new BEAperturaCaja();
        $DatTurno->id_turno = $idTurno;
        $DatTurno->ntot_entreg = floatval($data['ntot_entreg'] ?? 0);
        $DatTurno->nmonto_fin = floatval($data['nmonto_fin'] ?? 0);
        $DatTurno->ndiferencia = floatval($data['ndiferencia'] ?? 0);
        
        $rows = $objBL->CierreCaja($DatTurno, $objUsuario);
        $lst = array();
        foreach ($rows as $f) { 
            $lst[] = array('id_turno' => S($f[0])); 
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarTienda':
        $objDA = new DAConsultaDocumento();
        $rows = $objDA->ConsultaTienda($objUsuario);
        $lst = array();
        foreach ($rows as $f) { 
            $lst[] = array('ccod_tiend' => S($f[0]), 'cnombr' => S($f[1])); 
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarCajaDeUsuario':
        $input = getJsonInput();
        $rows = $objBL->CargarCajaDeUsuario($input['ccod_usuario'] ?? '', $objUsuario);
        $lst = array();
        foreach ($rows as $f) { 
            $lst[] = array('ccod_caja' => S($f[0]), 'cdsc_caja' => S($f[1])); 
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarIdUsuario':
        $input = getJsonInput();
        $rows = $objBL->CargarIdUsuario($input['codigo'] ?? '', $objUsuario);
        $lst = array();
        foreach ($rows as $f) { 
            $lst[] = array('ccod_usuario' => S($f[0]), 'cdsc_usuario' => S($f[1])); 
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarIdCierreCaja':
        $input = getJsonInput();
        $rows = $objBL->ConsultarIdCierreCaja($input['id_turno'] ?? '', $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_turno' => S($f[0]),
                'cdsc_tienda' => S($f[1]),
                'cdsc_usuario' => S($f[2]),
                'cdsc_caja' => S($f[3]),
                'nmonto_ini' => S($f[4]),
                'nmonto_fin' => S($f[5]),
                'dfecha_ini' => S($f[6]),
                'dfecha_fin' => S($f[7]),
                'ccod_tienda' => S($f[8]),
                'ccod_usuario' => S($f[9]),
                'ccod_caja' => S($f[10]),
                'ntot_entreg' => S($f[11]),
                'ndiferencia' => S($f[12]),
                'cstatus' => S($f[13])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>