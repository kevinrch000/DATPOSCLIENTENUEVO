<?php
/**
 * DatPOS - API: Guías de Remisión
 * Reemplaza: Operaciones/GuiaRemision.aspx.vb
 * NOTA: La integración SUNAT (EnviarGuiaSunat → ITC) se difiere a Sprint D.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../DA/DAGuiaRemision.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$m = $_GET['method'] ?? '';
$DA = new DAGuiaRemision();

function normFechaG($f) {
    $f = trim((string)$f);
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $f, $mm)) return sprintf('%04d%02d%02d', $mm[3], $mm[2], $mm[1]);
    if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $f, $mm)) return $mm[1].$mm[2].$mm[3];
    return $f;
}

switch ($m) {

case 'ConsultarGuiaRemision':
    // Usamos appDatpos_consultarGuiaRemisionFull (FIX_31) que devuelve todas las columnas
    // que el JS espera: item, id_cbinve, ctipo, cod_tip_cpe, ccod_alm, cdomicilio_partida,
    // ccod_alm_ing, cdomicilio_llegada, dfecha, cdoc_ref, guia
    $rows = Database::selectStoredTenant('appDatpos_consultarGuiaRemisionFull', array('@ccod_cia' => $o->ccod_empresa), $o);
    $lst = array();
    foreach ($rows as $f) {
        $id = strval($f[0] ?? '');
        $lst[] = array(
            'item' => "<input id='".$id."' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
            'id_cbinve'         => $id,
            'ctipo'             => strval($f[1] ?? ''),
            'cod_tip_cpe'       => strval($f[2] ?? ''),
            'ccod_alm'          => strval($f[3] ?? ''),
            'cdomicilio_partida'=> strval($f[4] ?? ''),
            'ccod_alm_ing'      => strval($f[5] ?? ''),
            'cdomicilio_llegada'=> strval($f[6] ?? ''),
            'dfecha'            => strval($f[7] ?? ''),
            'cdoc_ref'          => strval($f[8] ?? ''),
            'guia'              => strval($f[9] ?? ''),
            // Extras para uso de detalle
            'cnum_ruc_dest'     => strval($f[10] ?? ''),
            'cnom_rzn_soc_dest' => strval($f[11] ?? ''),
            'ntotal'            => strval($f[12] ?? ''),
            'fchEmision'        => strval($f[13] ?? ''),
            'cserie'            => strval($f[14] ?? ''),
            'nnumero'           => strval($f[15] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ObtenerGuiaRemision':
    // SP devuelve SELECT * FROM CbGuia. Mapeo por nombres que CompletarCampos espera:
    $in = getJsonInput();
    $rows = $DA->ObtenerGuiaRemision($o->ccod_empresa, $in['id_cbinve'] ?? 0, $o);
    $lst = array();
    foreach ($rows as $f) {
        $fchEm = strval($f[36] ?? '');
        $dfecha = $fchEm;
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $fchEm, $mm)) {
            $dfecha = $mm[3] . '/' . $mm[2] . '/' . $mm[1];
        }
        $dfecFin = strval($f[29] ?? '');
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $dfecFin, $mm)) {
            $dfecFin = $mm[3] . '/' . $mm[2] . '/' . $mm[1];
        }
        $lst[] = array(
            'id_cbinve'           => strval($f[34] ?? ''),
            'ccod_guia'           => strval($f[2] ?? ''),
            'cserie_guia'         => strval($f[3] ?? ''),
            'cnro_guia'           => strval($f[35] ?? ''),
            'cod_tip_cpe'         => strval($f[31] ?? ''),
            'cnum_ruc_dest'       => strval($f[6] ?? ''),
            'cnom_rzn_soc_dest'   => strval($f[7] ?? ''),
            'cnum_ruc_proy'       => strval($f[8] ?? ''),
            'cdsc_coa'            => strval($f[9] ?? ''),
            'dfecha'              => $dfecha,
            'dfec_fin'            => $dfecFin,
            'cdoc_ref'            => strval($f[30] ?? ''),
            // Origen (CbGuia: ccod_almOrigen=27, ctipo=25, cserie=26, nnumero=35)
            'ccod_alm'            => strval($f[27] ?? ($f[24] ?? '')),
            'cdomicilio_partida'  => strval($f[10] ?? ''),
            'ccod_ubi_partida'    => strval($f[11] ?? ''),
            'ctipo'               => strval($f[25] ?? ''),
            'cserie'              => strval($f[26] ?? ''),
            'nnumero'             => strval($f[35] ?? ''),
            // Destino: ccod_almDestino=28; ctipoDestino/cserieDestino se agregaron en FIX_51
            // (indices 38/39 cuando 'SELECT * FROM CbGuia'). Hacemos fallback a origen
            // para registros antiguos que no tenian datos de destino persistidos.
            'ccod_alm_ing'        => strval(($f[28] ?? '') !== '' ? $f[28] : ''),
            'cdomicilio_llegada'  => strval($f[12] ?? ''),
            'ccod_ubi_llegada'    => strval($f[13] ?? ''),
            'ctipo_ing'           => strval(($f[38] ?? '') !== '' ? $f[38] : ($f[25] ?? '')),
            'cserie_ing'          => strval(($f[39] ?? '') !== '' ? $f[39] : ($f[26] ?? '')),
            'nnumero_ing'         => strval($f[35] ?? ''),
            // Transportista
            'ctrans_nombre'       => strval($f[14] ?? ''),
            'ctrans_ruc'          => strval($f[15] ?? ''),
            'ctrans_placa'        => strval($f[20] ?? ''),
            'ctrans_licencia'     => strval($f[21] ?? ''),
            'ccod_unid_peso_bruto'=> strval($f[16] ?? ''),
            'nmnt_tot_peso_bruto' => strval($f[17] ?? ''),
            // Otros
            'cdesc_motiv_tras'    => strval($f[18] ?? ''),
            'nobs'                => strval($f[19] ?? ''),
            'ntotal'              => strval($f[22] ?? ''),
            'ccod_coa'            => strval($f[32] ?? ''),
            'cnum_ruc_rem'        => strval($f[4] ?? ''),
            'cnom_rzn_soc_rem'    => strval($f[5] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ObtenerDetalleGuiaRemision':
    $in = getJsonInput();
    $rows = $DA->ObtenerDetalleGuiaRemision($o->ccod_empresa, $in['id'] ?? 0, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'id_lninve'     => strval($f[0] ?? ''),
            'ccod_articulo' => strval($f[3] ?? ''),
            'ccod_artSunat' => strval($f[4] ?? ''),
            'cdsc_articulo' => strval($f[5] ?? ''),
            'ncantidad'     => strval($f[6] ?? ''),
            'ncosto'        => strval($f[7] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ObtenerNumerador':
    // El JS espera objetos con: cdoc_tipo, cdoc_serie, cstatus
    // El SP webDatpos_ObtenerNumerador devuelve: id_ctalmac, cserie, nnumero, ctip_doc
    $in = getJsonInput();
    $rows = $DA->ObtenerNumerador($o->ccod_empresa, $in['tipo'] ?? 'RT', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cdoc_tipo'  => strval($f[3] ?? ''),  // ctip_doc
            'cdoc_serie' => strval($f[1] ?? ''),   // cserie
            'cstatus'    => '1',                    // activo
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarAlamcenes':
    // El JS CargarAlmacenes() espera: id_ctalmac, ccod_alm, cdsc_alm, cdirc_almac, cubigeo, cstatus, cserieDest, cserieOrig
    // El SP webDatpos_ConsultarAlamcenes devuelve 8 columnas en ese orden.
    $rows = $DA->ConsultarAlamcenes($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'id_ctalmac'  => strval($f[0] ?? ''),
            'ccod_alm'    => strval($f[1] ?? ''),
            'cdsc_alm'    => strval($f[2] ?? ''),
            'cdirc_almac' => strval($f[3] ?? ''),
            'cubigeo'     => strval($f[4] ?? ''),
            'cstatus'     => strval($f[5] ?? ''),
            'cserieDest'  => strval($f[6] ?? ''),
            'cserieOrig'  => strval($f[7] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarOperaciones':
    // El JS GuiaRemision.js filtra por cstatus=='1' y ctipo_flag in ('I','S').
    // Usamos el SP webDatpos_ConsultarOperaciones que ya devuelve las 6 columnas
    // que el JS espera: id_ctoper, ccod_toper, cdsc_toper, ctipo_flag, ctipo_transferencia, cstatus
    $rows = $DA->ConsultarOperaciones($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'id_ctoper'           => strval($f[0] ?? ''),
            'ccod_toper'          => strval($f[1] ?? ''),
            'cdsc_toper'          => strval($f[2] ?? ''),
            'ctipo_flag'          => strval($f[3] ?? ''),
            'ctipo_transferencia' => strval($f[4] ?? ''),
            'cstatus'             => strval($f[5] ?? '1'),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarCodigoAuxiliar':
    $in = getJsonInput();
    $rows = $DA->ConsultarCodigoAuxiliar($o->ccod_empresa, $in['cproveedor'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        // El JS de GuiaRemision espera: item, cruc_coa, cdsc_coa
        $cruc = strval($f[0] ?? '');
        $lst[] = array(
            'item' => "<input type='radio' name='radioProv' value='".e($cruc)."'>",
            'cruc_coa' => $cruc,
            'cdsc_coa' => strval($f[1] ?? ''),
            // Extras
            'ccod_coa' => $cruc
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarArticulosSalida':
    // El JS de GuiaRemision espera: cbx, ccod_articulo, ccod_artSunat, cdsc_articulo, ncantidad, ncosto
    // El SP webDatpos_consultarArticulosSalida devuelve:
    //   [0]=ccod_articulo, [1]=cdsc_articulo, [2]=ccod_lin, [3]=ncantidad, [4]=ncosto, [5]=ccod_artSunat
    require_once __DIR__ . '/../DA/DASalida.php';
    $sal = new DASalida();
    $in = getJsonInput();
    $rows = $sal->ConsultarArticulosSalida($o->ccod_empresa, $in['almacen'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cbx'           => '', // radio button placeholder
            'ccod_articulo' => strval($f[0] ?? ''),
            'ccod_artSunat' => strval($f[5] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'linea'         => '',
            'ncantidad'     => strval($f[3] ?? ''),
            'ncosto'        => strval($f[4] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarArticulos':
    $rows = Database::selectStoredTenant('sp_consultararticulosactivos', array('@ccod_cia' => $o->ccod_empresa), $o);
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

case 'TotalInventario':
    $in = getJsonInput();
    $obj = $in['obj'] ?? array();
    $tot = 0.0;
    foreach ($obj as $d) {
        if (intval($d['state'] ?? 0) === 3) continue;
        $tot += floatval($d['ncantidad'] ?? 0) * floatval($d['ncosto'] ?? 0);
    }
    jsonResponse(array('d' => array('ntotal' => round($tot, 2))));
    break;

case 'ValidarListArticulo':
    // Equivalente al de Salida — valida stock antes de Guardar
    $in   = getJsonInput();
    $invArr = $in['inventario'] ?? array();
    $inv  = isset($invArr[0]) ? $invArr[0] : array();
    $rows = Database::selectStoredTenant('appDatpos_validarStockArticulos', array(
        '@ccod_cia' => $o->ccod_empresa,
        '@ccod_alm' => $inv['ccod_alm'] ?? '',
        '@producto' => $inv['listArticulo'] ?? ''
    ), $o);
    $lst = array();
    foreach ($rows as $f) {
        $stock = floatval($f[2] ?? 0);
        if ($stock <= 0) {
            $lst[] = array(
                'cdsc_articulo'      => strval($f[1] ?? ''),
                'ncantidad'          => '0',
                'ncantidad_actual'   => strval($stock),
                'ncantidad_faltante' => strval($stock),
            );
        }
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ValidarArticuloAlmacenSalida':
    // Llamado al presionar Enter en el campo "Código" del modal "Agregar".
    // El JS espera: ccod_articulo, cdsc_articulo, ncantidad, ncosto, ccod_artSunat
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
            'ccod_artSunat' => strval($f[5] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
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

case 'CargarTiposOperacionTransferencia':
    $rows = Database::selectStoredTenant('webDatpos_consultarOperTransferencia', array('@ccod_cia' => $o->ccod_empresa), $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('ccod_toper' => strval($f[0] ?? ''), 'cdsc_toper' => strval($f[1] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'CargarTiposOperacionTransferenciaSalida':
    $rows = Database::selectStoredTenant('sp_consultarTiposOperacionSalisa', array('@ccod_cia' => $o->ccod_empresa), $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('ccod_toper' => strval($f[0] ?? ''), 'cdsc_toper' => strval($f[1] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarAlmEmpActivos':
    $rows = Database::selectStoredTenant('sp_consultaalmempactivos', array('@ccod_empresa' => $o->ccod_empresa), $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarNumerador':
    $in = getJsonInput();
    $rows = Database::selectStoredTenant('appDatpos_consultaNumeradorAlmacen', array(
        '@ccod_alm' => $in['almacen'] ?? '',
        '@ccod_cia' => $o->ccod_empresa
    ), $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('cserie' => strval($f[0] ?? ''), 'nnumero' => strval($f[1] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarNumeradorSalida':
    $in = getJsonInput();
    $rows = Database::selectStoredTenant('appDatpos_consultaNumeradorSalida', array(
        '@ccod_alm' => $in['almacen'] ?? '',
        '@ccod_cia' => $o->ccod_empresa
    ), $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('cserie' => strval($f[0] ?? ''), 'nnumero' => strval($f[1] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'Guardar':
    // operacion: '04'=Translado | '01'/'14'=Salida | '02'=Ingreso
    // modo: 'nuevo' (default) -> INSERT | 'editar' -> UPDATE cabecera (requiere id_cbinve > 0)
    $in       = getJsonInput();
    $cabArr   = $in['CabTranslado'] ?? array();
    $detArr   = $in['LnTranslado'] ?? array();
    $operacion= $in['operacion'] ?? '04';
    $modo     = strtolower(strval($in['modo'] ?? 'nuevo'));
    $idEdit   = intval($in['id_cbinve'] ?? 0);

    if (count($cabArr) === 0) { jsonResponse(array('d' => array(false, 'EMPTY', 'Sin cabecera', ''))); }
    $cab = $cabArr[0];
    $cab['dfec_fin'] = normFechaG($cab['dfec_fin'] ?? '');

    if ($modo === 'editar' && $idEdit > 0) {
        // UPDATE solo cabecera: los articulos no se editan en este flujo.
        $r = $DA->ActualizarGuia($idEdit, $cab, $o);
    } elseif ($operacion === '04') {
        $r = $DA->InsertarGuiaTranslado($cab, $detArr, $o);
    } elseif ($operacion === '01' || $operacion === '14') {
        $r = $DA->InsertarGuiaVentaCompraSalida($cab, $detArr, $o);
    } elseif ($operacion === '02') {
        $r = $DA->InsertarGuiaVentaCompraIngreso($cab, $detArr, $o);
    } else {
        $r = array(false, 'ERR', 'Operación no soportada: '.$operacion, '');
    }

    if (!$r[0]) {
        error_log('GuiaRemision.Guardar error: '.json_encode($r));
    }
    jsonResponse(array('d' => $r));
    break;

case 'InformeGuiaRemision':
    // Stub: la integración SUNAT se hace en Sprint D
    jsonResponse(array('d' => 'PENDING_SUNAT'));
    break;

case 'DatosGenerales':
    $lst = array(array(
        'cdsc_tienda' => strval($o->cdsc_tiend ?? ''),
        'cdsc_alm'    => strval($o->cdsc_alm ?? ''),
        'cdsc_caja'   => strval($o->cdsc_caja ?? ''),
    ));
    jsonResponse(array('d' => $lst));
    break;

default:
    jsonResponse(array('d' => array()));
}
?>
