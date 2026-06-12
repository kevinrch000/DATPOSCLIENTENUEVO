<?php
/**
 * DatPOS - API: AperturaCaja (Ventas)
 * Reemplaza: DA/DAAperturaCaja.vb logic (mixed in API)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../DA/DAAperturaCaja.php';
require_once __DIR__ . '/../DA/DAConsultaDocumento.php';
require_once __DIR__ . '/../BL/BLAperturaCaja.php';

if (!isset($_SESSION['objBEUsuario'])) { 
    echo json_encode(array('d' => '-1', 'error' => 'No hay sesión'));
    exit;
}
$objUsuario = $_SESSION['objBEUsuario'];

// FIX: completar desde env vars antes de abortar (mismo fix que facturacion_api.php)
if (empty($objUsuario->cnomser) || empty($objUsuario->cnombre_bd)) {
    $envServer = getenv('DATPOS_TENANT_SERVER');
    $envDb     = getenv('DATPOS_TENANT_DATABASE');
    if (!empty($envServer) && !empty($envDb)) {
        $objUsuario->cnomser    = $envServer;
        $objUsuario->cnombre_bd = $envDb;
    } else {
        echo json_encode(array('d' => '-1', 'error' => 'Sesion incompleta: vuelva a iniciar sesion.'));
        exit;
    }
}

$method = $_GET['method'] ?? '';

function S($v) { return ($v !== null) ? strval($v) : ''; }
function O($v) { return ($v !== null && $v !== DBNull) ? $v : null; }

$objBL = new BLAperturaCaja();

switch ($method) {
    case 'ConsultarCierreCaja':
        // Mapeo idéntico al VB: AperturaCaja.aspx.vb -> ConsultarCierreCaja
        // SP webDatpos_consultarCierreCaja devuelve descripciones en 1..3
        $rows = $objBL->ConsultarCierreCaja($objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            // FIX 73 / BUG 3.3: traducir codigo de Turno.cstatus a
            // texto. 'A' = Abierto, 'C' = Cerrado. Antes la columna
            // Estado mostraba la letra cruda.
            $csRaw = strval(S($f[8]));
            $cstxt = $csRaw;
            if ($csRaw === 'A')       $cstxt = 'Abierto';
            else if ($csRaw === 'C')  $cstxt = 'Cerrado';
            $lst[] = array(
                'item' => "<input id='" . S($f[0]) . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_turno' => S($f[0]),
                'cdsc_tienda' => S($f[1]),
                'cdsc_usuario' => S($f[2]),
                'cdsc_caja' => S($f[3]),
                'nmonto_ini' => S($f[4]),
                'nmonto_fin' => S($f[5]),
                'dfecha_ini' => S($f[6]),
                'dfecha_fin' => S($f[7]),
                'cstatus' => $cstxt,
                'cstatus_raw' => $csRaw // codigo crudo por compatibilidad
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['DatTurno'][0] ?? array();
        
        require_once __DIR__ . '/../BE/BEAperturaCaja.php';
        $DatTurno = new BEAperturaCaja();
        $DatTurno->ccod_tienda = $data['ccod_tienda'] ?? '';
        $DatTurno->ccod_usuario = $data['ccod_usuario'] ?? '';
        $DatTurno->ccod_caja = $data['ccod_caja'] ?? '';
        $DatTurno->nmonto_ini = floatval($data['nmonto_ini'] ?? 0);
        // Convertir fecha de formato d/m/Y H:i:s a Y-m-d H:i:s que acepta SQL Server
        // Nota: el JS envía fechas SIN cero-padding (ej: "4/5/2026 17:30:5")
        $rawFecha = $data['dfchdoc_ini'] ?? '';
        if ($rawFecha) {
            $dt = DateTime::createFromFormat('j/n/Y H:i:s', $rawFecha)  // sin padding
               ?: DateTime::createFromFormat('d/m/Y H:i:s', $rawFecha)  // con padding
               ?: DateTime::createFromFormat('j/n/Y', $rawFecha)         // solo fecha
               ?: DateTime::createFromFormat('d/m/Y', $rawFecha)
               ?: new DateTime();
            $DatTurno->dfchdoc_ini = $dt->format('Y-m-d H:i:s');
        } else {
            $DatTurno->dfchdoc_ini = date('Y-m-d H:i:s');
        }
        
        $rows = $objBL->AperturarCaja($DatTurno, $objUsuario);
        $lst = array();
        foreach ($rows as $f) { 
            $lst[] = array('id_turno' => S($f[0])); 
        }
        if (isset($lst[0]['id_turno']) && ctype_digit($lst[0]['id_turno'])) {
            $_SESSION['id_turno'] = $lst[0]['id_turno'];
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarTienda':
        // VB: AperturaCaja.aspx.vb -> BLConsultaDocumento.ConsultaTienda
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
        // Mapeo idéntico al VB: AperturaCaja.aspx.vb -> ConsultarIdCierreCaja
        // SP webDatpos_consultarIdCierreCaja devuelve:
        //   0:id_turno  1:cdsc_tienda  2:cdsc_usuario  3:cdsc_caja
        //   4:nmonto_ini 5:nmonto_fin  6:dfchdoc_ini  7:dfchdoc_fin
        //   8:ccod_tienda 9:ccod_usuario 10:ccod_caja
        //   11:ntot_entreg 12:ndiferencia
        $input = getJsonInput();
        $idTurno = $input['id_turno'] ?? '';
        if (!ctype_digit(strval($idTurno))) {
            $turnos = $objBL->ConsultarCierreCaja($objUsuario);
            $idTurno = isset($turnos[0][0]) ? $turnos[0][0] : 0;
        }
        $rows = $objBL->ConsultarIdCierreCaja($idTurno, $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_turno' => S($f[0]),
                'cdsc_tienda' => S($f[1]),
                'cdsc_usuario' => S($f[2]),
                'cdsc_caja' => S($f[3]),
                'nmonto_ini' => S($f[4]),
                'nmonto_fin' => S($f[5]),
                'dfchdoc_ini' => S($f[6]),
                'dfchdoc_fin' => S($f[7]),
                'ccod_tienda' => S($f[8]),
                'ccod_usuario' => S($f[9]),
                'ccod_caja' => S($f[10]),
                'ntot_entreg' => S($f[11]),
                'ndiferencia' => S($f[12])
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Editar':
        $input = getJsonInput();
        $data = $input['DatTurno'][0] ?? array();
        $idTurno = intval($data['id_turno'] ?? $input['id_turno'] ?? 0);

        // Convertir fecha dd/mm/yyyy → yyyy-mm-dd
        $rawFecha = $data['dfchdoc_ini'] ?? '';
        if ($rawFecha) {
            $dt = DateTime::createFromFormat('j/n/Y H:i:s', $rawFecha)
               ?: DateTime::createFromFormat('d/m/Y H:i:s', $rawFecha)
               ?: DateTime::createFromFormat('j/n/Y', $rawFecha)
               ?: DateTime::createFromFormat('d/m/Y', $rawFecha)
               ?: new DateTime();
            $fechaIni = $dt->format('Y-m-d H:i:s');
        } else {
            $fechaIni = date('Y-m-d H:i:s');
        }

        $rows = Database::selectStoredTenant('webDatpos_editarTurno', array(
            '@id_turno'     => $idTurno,
            '@ccod_cia'     => $objUsuario->ccod_empresa,
            '@ccod_tienda'  => $data['ccod_tienda'] ?? '',
            '@ccod_usuario' => $data['ccod_usuario'] ?? '',
            '@ccod_caja'    => $data['ccod_caja'] ?? '',
            '@nmonto_ini'   => floatval($data['nmonto_ini'] ?? 0),
            '@dfchdoc_ini'  => $fechaIni
        ), $objUsuario);

        $resultado = (isset($rows[0][0])) ? S($rows[0][0]) : 'Error';
        if ($resultado === 'OK') {
            jsonResponse(array('d' => array(array('id_turno' => 'OK'))));
        } elseif ($resultado === 'TurnoCerrado') {
            jsonResponse(array('d' => array(array('id_turno' => 'TurnoCerrado'))));
        } else {
            jsonResponse(array('d' => false));
        }
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $idTurno = intval($input['id_turno'] ?? 0);

        $rows = Database::selectStoredTenant('webDatpos_eliminarTurno', array(
            '@id_turno' => $idTurno,
            '@ccod_cia' => $objUsuario->ccod_empresa
        ), $objUsuario);

        $resultado = (isset($rows[0][0])) ? S($rows[0][0]) : 'Error';
        if ($resultado === 'OK') {
            jsonResponse(array('d' => true));
        } elseif ($resultado === 'TieneFacturas') {
            jsonResponse(array('d' => 'TieneFacturas'));
        } elseif ($resultado === 'TurnoCerrado') {
            jsonResponse(array('d' => 'TurnoCerrado'));
        } else {
            jsonResponse(array('d' => false));
        }
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>