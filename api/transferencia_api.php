<?php
/**
 * DatPOS - API: Transferencias entre almacenes
 * Reemplaza: Operaciones/Transferencias.aspx.vb (WebMethods)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../DA/DATransferencia.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$m = $_GET['method'] ?? '';
$DA = new DATransferencia();

function normFechaT($f) {
    $f = trim((string)$f);
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $f, $mm)) return sprintf('%04d%02d%02d', $mm[3], $mm[2], $mm[1]);
    if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $f, $mm)) return $mm[1].$mm[2].$mm[3];
    return $f;
}

switch ($m) {

case 'ConsultarTransferencias':
    // Usamos appDatpos_consultarTransferenciasFull (FIX_31) que devuelve origen+destino
    $rows = Database::selectStoredTenant('appDatpos_consultarTransferenciasFull', array('@ccod_cia' => $o->ccod_empresa), $o);
    $lst = array();
    foreach ($rows as $f) {
        $id = strval($f[0] ?? '');
        $lst[] = array(
            'item' => "<input id='".$id."' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
            'id_cbinve'        => $id,
            'ccod_almOrigen'   => strval($f[1] ?? ''),
            'cdsc_almOrigen'   => strval($f[2] ?? ''),
            'ctipoOrigen'      => strval($f[3] ?? ''),
            'vserieOrigen'     => strval($f[4] ?? ''),
            'nnumeroOrigen'    => strval($f[5] ?? ''),
            'ccod_almDestino'  => strval($f[6] ?? ''),
            'cdsc_almDestino'  => strval($f[7] ?? ''),
            'ctipoDestino'     => strval($f[8] ?? ''),
            'vserieDestino'    => strval($f[9] ?? ''),
            'nnumeroDestino'   => strval($f[10] ?? ''),
            'dfecha'           => strval($f[11] ?? ''),
            'vobservacion'     => strval($f[12] ?? ''),
            'ntotal'           => strval($f[13] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarTransferencia':
    // SP webDatpos_consultarTransferencia devuelve:
    //  0..12: CbInventario.* (id_cbinve,ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,...)
    //  13: ccod_articulo, 14: cdsc_articulo, 15: ncantidad, 16: ncosto
    //  17: ccod_alm (origen del detalle), 18: ccod_alm_ingreso (destino)
    // El JS de Transferencias hace findIndex(option.value === obj.cdsc_almOrigen) — espera el CÓDIGO en cdsc_almOrigen.
    $in = getJsonInput();
    $rows = $DA->ConsultarTransferencia($o->ccod_empresa, $in['codigo'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $fecha = strval($f[4] ?? '');
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $fecha, $mm)) {
            $fecha = $mm[3] . '/' . $mm[2] . '/' . $mm[1];
        }
        $lst[] = array(
            'id_cbinve'       => strval($f[0] ?? ''),
            'ccod_tienda'     => strval($f[2] ?? ''),
            'dfecha'          => $fecha,
            // Origen (datos cabecera + detalle)
            'cdsc_almOrigen'  => strval($f[3] ?? ''), // código origen — para findIndex
            'ccod_almOrigen'  => strval($f[3] ?? ''),
            'ctipoOrigen'     => strval($f[5] ?? ''),
            'vserieOrigen'    => strval($f[6] ?? ''),
            'cserieOrigen'    => strval($f[6] ?? ''),
            'nnumeroOrigen'   => strval($f[7] ?? ''),
            // Destino (del detalle)
            'cdsc_almDestino' => strval($f[18] ?? ''), // código destino — para findIndex
            'ccod_almDestino' => strval($f[18] ?? ''),
            'ctipoDestino'    => strval($f[5] ?? ''),  // misma cabecera
            'vserieDestino'   => strval($f[6] ?? ''),
            'cserieDestino'   => strval($f[6] ?? ''),
            'nnumeroDestino'  => strval($f[7] ?? ''),
            'vobservacion'    => strval($f[8] ?? ''),
        );
        break; // solo cabecera
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarInventarioDetalle':
    $in = getJsonInput();
    $rows = $DA->ConsultarInventarioDetalle($o->ccod_empresa, $in['id'] ?? 0, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'id_lninve'         => strval($f[0] ?? ''),
            'ccod_articulo'     => strval($f[1] ?? ''),
            'cdsc_articulo'     => strval($f[2] ?? ''),
            'csim_unidadmedida' => strval($f[3] ?? ''),
            'ncantidad'         => strval($f[4] ?? ''),
            'ncosto'            => strval($f[5] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarArticulos':
    $rows = $DA->ConsultarArticulosActivos($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'linea'         => strval($f[2] ?? ''),
            'uni_medi'      => strval($f[3] ?? ''),
            'estado'        => '',
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarArticulosSalida':
    $in = getJsonInput();
    $rows = $DA->ConsultarArticulosSalida($o->ccod_empresa, $in['almacen'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cbx'           => '', // placeholder — el JS lo render-iza como radio
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'linea'         => strval($f[2] ?? ''), // ccod_lin / familia — col[2] del SP (5 cols)
            'ncantidad'     => strval($f[3] ?? ''),
            'ncosto'        => strval($f[4] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarAlmEmpActivos':
    $rows = $DA->ConsultarAlmEmpActivos($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarNumerador':
case 'ConsultarNumeradorSalida':
    $in = getJsonInput();
    $rows = $DA->ConsultarNumerador($o->ccod_empresa, $in['almacen'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('cserie' => strval($f[0] ?? ''), 'nnumero' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'CargarTiposOperacionTransferencia':
    $rows = $DA->CargarTiposOperacionTransferencia($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('ccod_toper' => strval($f[0] ?? ''), 'cdsc_toper' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'CargarTiposOperacionTransferenciaSalida':
    $rows = $DA->CargarTiposOperacionTransferenciaSalida($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('ccod_toper' => strval($f[0] ?? ''), 'cdsc_toper' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'TotalInventario':
    $in  = getJsonInput();
    $obj = $in['obj'] ?? array();
    $tot = 0.0;
    foreach ($obj as $d) {
        if (intval($d['state'] ?? 0) === 3) continue;
        $tot += floatval($d['ncantidad'] ?? 0) * floatval($d['ncosto'] ?? 0);
    }
    jsonResponse(array('d' => array('ntotal' => round($tot, 2))));
    break;

case 'VerificarCantaArti':
    // Recibe array con 1 elemento: [{ccod_articulo, ncantidad, ccod_alm}]
    // El JS espera obj[0].ccod_articulo == 'OK' si hay stock suficiente,
    // o el saldo real (ej. "5") si no alcanza.
    $in   = getJsonInput();
    $arti = $in['ArticuloCantaArti'] ?? array();
    $a    = isset($arti[0]) ? $arti[0] : array();
    $ccod_articulo = $a['ccod_articulo'] ?? '';
    $ncantidad     = floatval($a['ncantidad'] ?? 0);
    $ccod_alm      = $a['ccod_alm'] ?? '';

    // webDatpos_articuloCantaArti retorna fila solo si Stock.ncantidad >= @ncantidad
    $rows = Database::selectStoredTenant('webDatpos_articuloCantaArti', array(
        '@ccod_articulo' => $ccod_articulo,
        '@ncantidad'     => $ncantidad,
        '@ccod_cia'      => $o->ccod_empresa,
        '@ccod_alm'      => $ccod_alm
    ), $o);

    if (!empty($rows)) {
        // Stock suficiente
        jsonResponse(array('d' => array(array('ccod_articulo' => 'OK'))));
    } else {
        // Stock insuficiente — obtener saldo real para mostrar en mensaje
        $rowsStock = Database::selectStoredTenant('webDatpos_validarArticuloAlmacen', array(
            '@ccod_cia'      => $o->ccod_empresa,
            '@ccod_articulo' => $ccod_articulo,
            '@almacen'       => $ccod_alm
        ), $o);
        $saldo = '0';
        foreach ($rowsStock as $f) { $saldo = strval($f[0] ?? '0'); break; }
        jsonResponse(array('d' => array(array('ccod_articulo' => $saldo))));
    }
    break;

case 'ValidarArticuloAlmacenSalida':
    // Llamado al presionar Enter en el campo "Código" del modal "Agregar" en Transferencias.
    $in = getJsonInput();
    $rows = Database::selectStoredTenant('appDatpos_validarArticuloEnAlm', array(
        '@ccod_cia'      => $o->ccod_empresa,
        '@ccod_articulo' => $in['ccod_articulo'] ?? '',
        '@ccod_alm'      => $in['ccod_alm'] ?? ''
    ), $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'uni_medi'      => strval($f[2] ?? ''),
            'ncantidad'     => strval($f[3] ?? '0'),
            'ncosto'        => strval($f[4] ?? '0'),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'Guardar':
    // Payload: { CabTranslado: [{ ccod_almOrigen, ctipoOrigen, vserieOrigen, nnumeroOrigen,
    //                             ccod_almDestino, ctipoDestino, vserieDestino, nnumeroDestino,
    //                             dfecha, vobservacion, ntotal }],
    //            LnTranslado: [{ state, ccod_articulo, ncantidad, ncosto }],
    //            operacion: "nuevo" }
    $in       = getJsonInput();
    $cabArr   = $in['CabTranslado'] ?? array();
    $detArr   = $in['LnTranslado'] ?? array();
    if (count($cabArr) === 0) { jsonResponse(array('d' => array(false, 'EMPTY', 'Sin cabecera', ''))); }
    $cab = $cabArr[0];
    $cab['dfecha'] = normFechaT($cab['dfecha'] ?? '');

    $r = $DA->InsertarTransferencia($cab, $detArr, $o);
    if ($r['ok']) {
        // El JS revisa response.d[1] == 'OK'
        jsonResponse(array('d' => array(true, 'OK', '', '')));
    } else {
        error_log('Transferencia.Guardar error: '.$r['error']);
        jsonResponse(array('d' => array(false, 'ERR', $r['error'], '')));
    }
    break;

case 'DatosGenerales':
    $lst = array(array(
        'cdsc_tienda'             => strval($o->cdsc_tiend ?? ''),
        'cdsc_alm'                => strval($o->cdsc_alm ?? ''),
        'cdsc_caja'               => strval($o->cdsc_caja ?? ''),
        'nlista_pre_normal'       => strval($o->nlista_pre_normal ?? ''),
        'nlista_pre_preferencial' => strval($o->nlista_pre_preferencial ?? ''),
    ));
    jsonResponse(array('d' => $lst));
    break;

default:
    jsonResponse(array('d' => array()));
}
?>
