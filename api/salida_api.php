<?php
/**
 * DatPOS - API: Salidas directas
 * Reemplaza: Operaciones/Salida.aspx.vb (WebMethods)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../DA/DASalida.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$m = $_GET['method'] ?? '';
$DA = new DASalida();

// Helper para normalizar fecha dd/mm/yyyy o yyyy-mm-dd → yyyymmdd (compatible con SQL Server locale-agnostic)
function normFecha($f) {
    $f = trim((string)$f);
    if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $f, $m)) return sprintf('%04d%02d%02d', $m[3], $m[2], $m[1]);
    if (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $f, $m)) return $m[1].$m[2].$m[3];
    return $f;
}

switch ($m) {

case 'ConsultarSalidas':
    $rows = $DA->ConsultarSalidas($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $id = strval($f[0] ?? '');
        $lst[] = array(
            'item' => "<input id='".$id."' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
            'id_cbinve'   => $id,
            'cdsc_tienda' => strval($f[1] ?? ''),
            'cdsc_alm'    => strval($f[2] ?? ''),
            'dfecha'      => strval($f[3] ?? ''),
            'vserie'      => strval($f[4] ?? ''),
            'nnumero'     => strval($f[5] ?? ''),
            'ctipo'       => strval($f[6] ?? ''),
            'vobservacion'=> strval($f[7] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarSalida':
    // SP webDatpos_consultarSalida → SELECT C.*, L.* (igual que ingreso)
    $in = getJsonInput();
    $rows = $DA->ConsultarSalida($o->ccod_empresa, $in['codigo'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $fecha = strval($f[4] ?? '');
        if (preg_match('#^(\d{4})-(\d{2})-(\d{2})#', $fecha, $mm)) {
            $fecha = $mm[3] . '/' . $mm[2] . '/' . $mm[1];
        }
        $lst[] = array(
            'id_cbinve'   => strval($f[0] ?? ''),
            'ccod_tienda' => strval($f[2] ?? ''),
            'ccod_alm'    => strval($f[3] ?? ''),
            'dfecha'      => $fecha,
            'ctipo'       => strval($f[5] ?? ''),
            'vserie'      => strval($f[6] ?? ''),
            'nnumero'     => strval($f[7] ?? ''),
            'vobservacion'=> strval($f[8] ?? ''),
        );
        break; // sólo cabecera
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarInventarioDetalleSalida':
    $in = getJsonInput();
    $rows = $DA->ConsultarInventarioDetalleSalida($o->ccod_empresa, $in['id'] ?? 0, $o);
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

case 'ConsultarArticulosSalida':
    $in = getJsonInput();
    $rows = $DA->ConsultarArticulosSalida($o->ccod_empresa, $in['almacen'] ?? '', $o);
    // SP devuelve: ccod_articulo, cdsc_articulo, ncantidad, ncosto
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cbx'           => '', // placeholder — el JS lo render-iza como radio
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'linea'         => '', // no devuelto por el SP de salida
            'ncantidad'     => strval($f[2] ?? ''),
            'ncosto'        => strval($f[3] ?? ''),
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

case 'ConsultarNumeradorSalida':
    $in = getJsonInput();
    $rows = $DA->ConsultarNumeradorSalida($o->ccod_empresa, $in['almacen'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('cserie' => strval($f[0] ?? ''), 'nnumero' => strval($f[1] ?? ''));
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

case 'ValidarListArticulo':
    // Llamado por Transferencias.js antes de Guardar.
    // Recibe { inventario:[{ ccod_alm, listArticulo: "ART001,ART003,..." }] }
    $in   = getJsonInput();
    $invArr = $in['inventario'] ?? array();
    $inv  = isset($invArr[0]) ? $invArr[0] : array();
    $ccod_alm    = $inv['ccod_alm'] ?? '';
    $listArticulo = $inv['listArticulo'] ?? '';

    $rows = Database::selectStoredTenant('appDatpos_validarStockArticulos', array(
        '@ccod_cia' => $o->ccod_empresa,
        '@ccod_alm' => $ccod_alm,
        '@producto' => $listArticulo
    ), $o);

    // Devolver solo artículos sin stock suficiente — el frontend muestra modal de error.
    // Para Transferencias, el JS espera columnas: cdsc_articulo, ncantidad, ncantidad_actual, ncantidad_faltante.
    // Aquí devolvemos array vacío si todos tienen stock; sino con detalle.
    // Nota: la lógica fina de "faltante" la hace el frontend; aquí solo retornamos los que tengan ncantidad=0.
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
    // Llamado desde el modal "Agregar" cuando el usuario escribe el código del artículo y presiona Enter.
    // El JS espera: response.d = [{ccod_articulo, cdsc_articulo, ncantidad, ncosto}]
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

case 'Guardar':
    $in       = getJsonInput();
    $cabArr   = $in['inventario'] ?? array();
    $detArr   = $in['detalleinventario'] ?? array();
    if (count($cabArr) === 0) {
        jsonResponse(array('d' => array(false, 'ERR', 'Falta cabecera (inventario)')));
    }
    $cab = $cabArr[0];
    $cab['dfecha']    = normFecha($cab['dfecha'] ?? '');
    $cab['ntotal']    = floatval($cab['ntotal'] ?? 0);
    $cab['id_cbinve'] = intval($cab['id_cbinve'] ?? 0);

    $r = $DA->InsertarInventarioSalidas($cab, $detArr, $o);
    if ($r['ok']) {
        // El JS lee response.d como array POSICIONAL: [bool, code, message]
        jsonResponse(array('d' => array(true, 'OK', '')));
    } else {
        error_log('Salida.Guardar error: '.$r['error']);
        jsonResponse(array('d' => array(false, 'ERR', strval($r['error'] ?? 'Error desconocido'))));
    }
    break;

case 'Eliminar':
    $in = getJsonInput();
    $id = $in['id'] ?? ($in['id_cbinve'] ?? 0);
    $DA->EliminarInventarioDetalleTodo($id, $o);
    $resp = $DA->EliminarInventario($id, $o);
    jsonResponse(array('d' => (bool)$resp));
    break;

case 'DatosGenerales':
    $lst = array(array(
        'cdsc_tienda'             => strval($o->cdsc_tiend ?? ''),
        'cdsc_alm'                => strval($o->cdsc_alm ?? ''),
        'cdsc_caja'               => strval($o->cdsc_caja ?? ''),
        'nlista_pre_normal'       => strval($o->nlista_pre_normal ?? ''),
        'nlista_pre_preferencial' => strval($o->nlista_pre_preferencial ?? ''),
        'cdsc_listpreNorm'        => strval($o->cdsc_listpreNorm ?? ''),
        'cdsc_listprePref'        => strval($o->cdsc_listprePref ?? ''),
    ));
    jsonResponse(array('d' => $lst));
    break;

default:
    jsonResponse(array('d' => array()));
}
?>
