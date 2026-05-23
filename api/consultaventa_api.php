<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'ReporteVentaPrincipal':
        $input = getJsonInput();
        $_SESSION['objReportVenta'] = $input['ReportVenta'][0] ?? array();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'InformeVentaDatos':
        $data = $_SESSION['objReportVenta'] ?? array();
        if (empty($data)) { jsonResponse(array('d' => array('filtros' => null, 'rows' => array(), 'total' => '0.00'))); }
        $rows = Database::selectStoredTenant('webDatpos_reporteVentaPrincipal', array(
            '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@fchDesde' => $data['dfch_desde'] ?? '',
            '@fchHasta' => $data['dfch_hasta'] ?? '',
            '@cdoc' => $data['cdoc'] ?? '',
            '@ccod_cia' => $o->ccod_empresa
        ), $o);
        $totalRows = Database::selectStoredTenant('webDatpos_reporteVentaImporteTotal', array(
            '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@fchDesde' => $data['dfch_desde'] ?? '',
            '@fchHasta' => $data['dfch_hasta'] ?? '',
            '@cdoc' => $data['cdoc'] ?? '',
            '@ccod_cia' => $o->ccod_empresa
        ), $o);
        jsonResponse(array('d' => array('filtros' => $data, 'rows' => $rows, 'total' => strval($totalRows[0][0] ?? '0.00'))));
        break;

    // === ConsultasVenta.php ===
    // JS de ConsultaVentas.js envia
    //   { ConsultaArticulo: [ { ccod_articulo, ccod_tienda, ccod_coa,
    //     n_fchDesde, n_fchHasta, cobser_variante } ] }
    // El SP webDatpos_ConsultasVentaPricipal devuelve 12 columnas en
    // este orden: id_cbfact (oculto), ccod_coa (nombre del cliente),
    // ccod_articulo, cdsc_articulo, ncantidad, nprecio, nimpuesto, nisc,
    // ndescuento, nimporte_neto, dfch_doc, cobser_variante.
    case 'ConsultasVentaPricipal':
        $input = getJsonInput();
        $data = $input['ConsultaArticulo'][0]
             ?? ($input['ConsultarVenta'][0] ?? array());
        $rows = Database::selectStoredTenant('webDatpos_ConsultasVentaPricipal', array(
            '@ccod_tienda'     => $data['ccod_tienda']     ?? '',
            '@ccod_coa'        => $data['ccod_coa']        ?? '',
            '@ccod_articulo'   => $data['ccod_articulo']   ?? '',
            '@cobser_variante' => $data['cobser_variante'] ?? '',
            '@fchDesde'        => $data['n_fchDesde']      ?? ($data['fchDesde'] ?? ''),
            '@fchHasta'        => $data['n_fchHasta']      ?? ($data['fchHasta'] ?? ''),
            '@CodCia'          => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_cbfact'       => strval($f[0] ?? ''),
                'ccod_coa'        => strval($f[1] ?? ''),
                'ccod_articulo'   => strval($f[2] ?? ''),
                'cdsc_articulo'   => strval($f[3] ?? ''),
                'ncantidad'       => strval($f[4] ?? ''),
                'nprecio'         => strval($f[5] ?? ''),
                'nimpuesto'       => strval($f[6] ?? ''),
                'nisc'            => strval($f[7] ?? ''),
                'ndescuento'      => strval($f[8] ?? ''),
                'nimporte_neto'   => strval($f[9] ?? ''),
                'dfch_doc'        => strval($f[10] ?? ''),
                'cobser_variante' => strval($f[11] ?? ''),
                'cstatus'         => '',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    // ConsultasVenta.php -> tab "Datos Adicionales".
    // El JS envia { VentasPorArticulo: <array de filas tal cual mostradas> }
    // y necesita sumar 6 totales. Resolvemos en PHP a partir del array
    // (sin llamar al SP) para evitar duplicacion de logica.
    case 'DatosAdicionales':
        $input = getJsonInput();
        $items = $input['VentasPorArticulo'] ?? array();
        if (!is_array($items)) { $items = array(); }
        $tot = array(
            'ncantidad'      => 0.0,
            'nimporte_bruto' => 0.0,
            'nimpuesto'      => 0.0,
            'nisc'           => 0.0,
            'ndescuento'     => 0.0,
            'nimporte_neto'  => 0.0,
        );
        foreach ($items as $it) {
            $qty   = floatval($it['ncantidad']      ?? 0);
            $price = floatval($it['nprecio']        ?? 0);
            $tot['ncantidad']      += $qty;
            $tot['nimporte_bruto'] += $qty * $price;
            $tot['nimpuesto']      += floatval($it['nimpuesto']     ?? 0);
            $tot['nisc']           += floatval($it['nisc']          ?? 0);
            $tot['ndescuento']     += floatval($it['ndescuento']    ?? 0);
            $tot['nimporte_neto']  += floatval($it['nimporte_neto'] ?? 0);
        }
        foreach ($tot as $k => $v) {
            $tot[$k] = number_format($v, 2, '.', '');
        }
        jsonResponse(array('d' => $tot));
        break;
    case 'CargarGraBarConsVent':
        $input = getJsonInput(); $data = $input['Consultar'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_cargarGraBarConsVent', array(
            '@Codigo' => '', '@cliente' => $data['cliente'] ?? '',
            '@fechadesde' => $data['fchDesde'] ?? '', '@fechahasta' => $data['fchHasta'] ?? '',
            '@ccod_tienda' => $data['ccod_tienda'] ?? '', '@ccod_cia' => $o->ccod_empresa
        ), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('name' => strval($f[0] ?? ''), 'y' => floatval($f[1] ?? 0));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarGraBarConsVentMenos':
        $input = getJsonInput(); $data = $input['Consultar'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_cargarGraBarConsVentMenos', array(
            '@Codigo' => '', '@cliente' => $data['cliente'] ?? '',
            '@fechadesde' => $data['fchDesde'] ?? '', '@fechahasta' => $data['fchHasta'] ?? '',
            '@ccod_tienda' => $data['ccod_tienda'] ?? '', '@ccod_cia' => $o->ccod_empresa
        ), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('name' => strval($f[0] ?? ''), 'y' => floatval($f[1] ?? 0));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarArticuloVentas':
        $input = getJsonInput(); $data = $input['Consultar'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_cargarArticuloVentas', array(
            '@cliente' => '', '@fechadesde' => $data['fchDesde'] ?? '',
            '@fechahasta' => $data['fchHasta'] ?? '',
            '@ccod_tienda' => $data['ccod_tienda'] ?? '', '@ccod_cia' => $o->ccod_empresa
        ), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'ncantidad' => strval($f[1] ?? ''),
                'nimporte' => strval($f[2] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    // ConsultasVenta.php -> ModalBuscarDoc -> ConsultaListArticulos
    // JS envia { id_fact: "<id_cbfact>" } (o cdoc/cdoc_serie/cdoc_nro).
    // El SP devuelve 8 columnas: ccod_articulo, cdsc_articulo,
    // ncantidad, nprecio, nimpuesto, nisc, ndescuento, nimporte_neto.
    case 'ConsultaListArticulos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaListArticulos', array(
            '@id_cbfact' => intval($input['id_fact']    ?? ($input['id_cbfact'] ?? 0)),
            '@cdoc'       => $input['cdoc']       ?? '',
            '@cdoc_serie' => $input['cdoc_serie'] ?? '',
            '@cdoc_nro'   => $input['cdoc_nro']   ?? '',
            '@CodCia'     => $o->ccod_empresa,
        ), $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_articulo' => strval($f[0] ?? ''),
                'cdsc_articulo' => strval($f[1] ?? ''),
                'ncantidad'     => strval($f[2] ?? ''),
                'nprecio'       => strval($f[3] ?? ''),
                'nimpuesto'     => strval($f[4] ?? ''),
                'nisc'          => strval($f[5] ?? ''),
                'ndescuento'    => strval($f[6] ?? ''),
                'nimporte_neto' => strval($f[7] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarCliente':
        $rows = Database::selectStoredTenant('sp_cargarcliente', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_coa' => strval($f[0] ?? ''), 'ccod_coa' => strval($f[0] ?? ''), 'cdsc_coa' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarTienda':
        $rows = Database::selectStoredTenant('sp_cargartienda', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_tiend' => strval($f[0] ?? ''), 'cnombr' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    default: jsonResponse(array('d' => array()));
}
?>
