<?php
/**
 * DatPOS - API: Ingresos directos
 * Reemplaza: Operaciones/Ingresos.aspx.vb (WebMethods)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../DA/DAIngreso.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$m = $_GET['method'] ?? '';
$DA = new DAIngreso();

switch ($m) {

case 'ConsultarIngresos':
    $rows = $DA->ConsultarIngresos($o->ccod_empresa, $o);
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

case 'ConsultarIngreso':
    // SP: sp_consultaringreso → SELECT C.*,L.ccod_articulo,L.cdsc_articulo,L.ncantidad,L.ncosto
    // Columnas CbInventario (0-based): 0=id_cbinve, 1=ccod_cia, 2=ccod_tienda, 3=ccod_alm,
    // 4=dfecha, 5=ctipo, 6=vserie, 7=nnumero, 8=vobservacion, 9=ccod_usuario,
    // 10=ntotal, 11=ccod_coa, 12=dfch_crea
    $in = getJsonInput();
    $rows = $DA->ConsultarIngreso($o->ccod_empresa, $in['codigo'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $fecha = strval($f[4] ?? '');
        // Convertir yyyy-mm-dd hh:mm:ss → dd/mm/yyyy para el frontend
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
            'ccod_coa'    => strval($f[11] ?? ''),
        );
        break; // El SP devuelve N filas (una por línea de detalle); solo necesitamos cabecera
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
    $rows = $DA->ConsultarArticulos($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cbx'           => '', // radio button placeholder
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'linea'         => strval($f[2] ?? ''),
            'uni_medi'      => strval($f[3] ?? ''),
            'estado'        => strval($f[4] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarProveedor':
    $rows = $DA->ConsultarProveedor($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'cbx'      => '', // radio button placeholder
            'ccod_coa' => strval($f[0] ?? ''),
            'cdsc_coa' => strval($f[1] ?? '')
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ValidarArticulo':
    $in = getJsonInput();
    $rows = $DA->ValidarArticulo($o->ccod_empresa, $in['ccod_articulo'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'ccod_articulo' => strval($f[0] ?? ''),
            'cdsc_articulo' => strval($f[1] ?? ''),
            'uni_medi'      => strval($f[2] ?? ''),
        );
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarAlmEmpActivos':
    $in = getJsonInput();
    $rows = $DA->ConsultarAlmEmpActivos($o->ccod_empresa, $in['tienda'] ?? $o->ccod_tiend, $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ConsultarNumerador':
    $in = getJsonInput();
    $rows = $DA->ConsultarNumerador($o->ccod_empresa, $in['almacen'] ?? '', $o);
    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array('cserie' => strval($f[0] ?? ''), 'nnumero' => strval($f[1] ?? ''));
    }
    jsonResponse(array('d' => $lst));
    break;

case 'ObtenerIvg':
    $rows = $DA->ObtenerIvg($o->ccod_empresa, $o);
    $lst = array();
    foreach ($rows as $f) { $lst[] = array('igv' => strval($f[0] ?? '')); }
    jsonResponse(array('d' => $lst));
    break;

case 'TotalInventario':
    // Suma de cantidad*costo de las líneas del detalle (cálculo en servidor)
    $in = getJsonInput();
    $obj = $in['obj'] ?? array();
    $tot = 0.0;
    foreach ($obj as $d) {
        if (intval($d['state'] ?? 0) === 3) continue; // ignorar eliminadas
        $tot += floatval($d['ncantidad'] ?? 0) * floatval($d['ncosto'] ?? 0);
    }
    jsonResponse(array('d' => array('ntotal' => round($tot, 2))));
    break;

case 'Guardar':
    // Payload: { inventario:[{...}], detalleinventario:[{state, id_lninve, ccod_articulo, ncantidad, ncosto, ...}], operacion:"nuevo|editar" }
    $in        = getJsonInput();
    $cabArr    = $in['inventario'] ?? array();
    $detArr    = $in['detalleinventario'] ?? array();
    $operacion = $in['operacion'] ?? 'nuevo';

    if (count($cabArr) === 0) { jsonResponse(array('d' => false)); }
    $cab = $cabArr[0];

    // Normalizar fecha (puede venir como dd/mm/yyyy desde el JS, o yyyy-mm-dd desde otros)
    // SQL Server con locale español interpreta yyyy-mm-dd ambiguo → forzamos ISO yyyymmdd.
    if (!empty($cab['dfecha'])) {
        $f = trim($cab['dfecha']);
        if (preg_match('#^(\d{1,2})/(\d{1,2})/(\d{4})$#', $f, $mm)) {
            $cab['dfecha'] = sprintf('%04d%02d%02d', $mm[3], $mm[2], $mm[1]);
        } elseif (preg_match('#^(\d{4})-(\d{2})-(\d{2})$#', $f, $mm)) {
            $cab['dfecha'] = $mm[1] . $mm[2] . $mm[3];
        }
    }
    // Normalizar numéricos
    $cab['ntotal']    = floatval($cab['ntotal'] ?? 0);
    $cab['id_cbinve'] = intval($cab['id_cbinve'] ?? 0);
    if (!empty($cab['nnumero'])) $cab['nnumero'] = intval($cab['nnumero']);

    $resp = false;

    if ($operacion === 'nuevo') {
        $newId = $DA->InsertarInventario($cab, $o);
        if ($newId > 0) {
            $cab['id_cbinve'] = $newId;
            $resp = true;
            foreach ($detArr as $d) {
                if (intval($d['state'] ?? 0) === 3) continue; // marcadas para eliminar antes de guardar
                $resp = $DA->InsertarInventarioDetalle($cab, $d, $o);
                if (!$resp) break;
            }
        }
    } elseif ($operacion === 'editar') {
        $resp = $DA->EditarInventario($cab, $o);
        if ($resp) {
            foreach ($detArr as $d) {
                $st = intval($d['state'] ?? 0);
                switch ($st) {
                    case 0: break; // sin cambios
                    case 1: $resp = $DA->InsertarInventarioDetalle($cab, $d, $o); break; // nueva
                    case 2: $resp = $DA->EditarInventarioDetalle($d, $o); break;          // modificada
                    case 3: $resp = $DA->EliminarInventarioDetalle($d['id_lninve'] ?? 0, $o); break; // eliminada
                }
                if (!$resp) break;
            }
        }
    }

    jsonResponse(array('d' => (bool)$resp));
    break;

case 'Eliminar':
    $in = getJsonInput();
    $id = $in['id'] ?? ($in['id_cbinve'] ?? 0);
    $DA->EliminarInventarioDetalleTodo($id, $o);
    $resp = $DA->EliminarInventario($id, $o);
    jsonResponse(array('d' => (bool)$resp));
    break;

case 'DatosGenerales':
    // Equivalente a BL.BLDashboard().DatosGenerales — datos del usuario logueado
    $lst = array(array(
        'cdsc_tienda'           => strval($o->cdsc_tiend ?? ''),
        'cdsc_alm'              => strval($o->cdsc_alm ?? ''),
        'cdsc_caja'             => strval($o->cdsc_caja ?? ''),
        'nlista_pre_normal'     => strval($o->nlista_pre_normal ?? ''),
        'nlista_pre_preferencial' => strval($o->nlista_pre_preferencial ?? ''),
        'cdsc_listpreNorm'      => strval($o->cdsc_listpreNorm ?? ''),
        'cdsc_listprePref'      => strval($o->cdsc_listprePref ?? ''),
    ));
    jsonResponse(array('d' => $lst));
    break;

default:
    jsonResponse(array('d' => array()));
}
?>
