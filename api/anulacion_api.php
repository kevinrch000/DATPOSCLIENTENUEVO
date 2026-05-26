<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLMovimientoCabecera.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$objBL = new BLMovimientoCabecera();
$m = $_GET['method'] ?? '';

function S($v) { return ($v !== null) ? strval($v) : ''; }

switch ($m) {
    case 'AnulacionPricipal':
        $input = getJsonInput(); $data = $input['anulacion'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_anulacionPricipal', array(
            '@cdoc_seri' => $data['cdoc'] ?? '', '@serie' => $data['cdoc_serie'] ?? '',
            '@correlativo' => $data['cdoc_nro'] ?? '', '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@ccod_coa' => $data['ccod_coa'] ?? '', '@fchDesde' => $data['n_fchDesde'] ?? '',
            '@fchHasta' => $data['n_fchHasta'] ?? '', '@CodCia' => $o->ccod_empresa), $o);
        // FIX 74 / BUG 3.13: MODIFY_930 amplio webDatpos_anulacionPricipal
        // de 8 a 15 cols (orden estable: id_cbfact, cdoc, cserie, nnumero,
        // dfch_doc, cdsc_coa, ntotal, ccod_coa, ccod_tienda, cdsc_tienda,
        // ccod_caja, cdsc_caja, cusu_crea, cdsc_usuario, cstatus_doc).
        // Mantenemos compatibilidad con la version vieja (<=8 cols) sin
        // perder los nuevos campos: si la columna no existe se mapea ''.
        $lst = array(); foreach ($rows as $f) {
            $id = S($f[0] ?? '');
            $lst[] = array(
                'cdoc'         => S($f[1] ?? ''),
                'cdoc_serie'   => S($f[2] ?? ''),
                'cdoc_nro'     => S($f[3] ?? ''),
                'cdoc_coa'     => S($f[7] ?? ''),  // RUC del cliente
                'cdsc_coa'     => S($f[5] ?? ''),  // Nombre del cliente
                'ntotal'       => S($f[6] ?? ''),
                'dfch_doc'     => S($f[4] ?? ''),
                'ccod_tienda'  => S($f[8] ?? ''),
                'cdsc_tienda'  => S($f[9] ?? ''),
                'ccod_caja'    => S($f[10] ?? ''),
                'cdsc_caja'    => S($f[11] ?? ''),
                'cusu_crea'    => S($f[12] ?? ''),
                'cdsc_usuario' => S($f[13] ?? ''),
                'cstatus_doc'  => S($f[14] ?? ''),
                'DocFact'      => "<td class='text-center'><i title='Documento Ref.' id='".$id."' onclick='ModalBuscarDoc(this);' data-toggle='modal' data-target='#modalBuscarDoc' class='fa fa-arrow-right color-popup-verde' aria-hidden='true'></i></td>",
                'Anulacion'    => "<td class='text-center'><i title='Anulación' id='".$id."' onclick='ModalAnularDoc(this);' data-toggle='modal' data-target='#modalDarDeBaja' class='fa fa-file-excel-o color-popup' aria-hidden='true'></i></td>",
                'id_cbfact'    => $id,
                // Alias para JS que use ccod_coa (legacy)
                'ccod_coa'     => S($f[7] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'AnulacionDoc':
        $input = getJsonInput();
        $result = $objBL->AnularDocumento(
            intval($input['id_cbfact'] ?? 0),
            $input['motivo'] ?? '',
            $o
        );
        $lst = array();
        foreach ($result as $f) {
            $lst[] = array('cdoc_seri' => S($f[0]), 'cdoc_nro' => S($f[1]));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultaDatosDocumento':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaDatosDocumento', array('@CodCia' => $o->ccod_empresa,
            '@cdoc' => $input['cdoc'] ?? '', '@cdoc_serie' => $input['cdoc_serie'] ?? '', '@cdoc_nro' => $input['cdoc_nro'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdoc' => S($f[0]), 'cdoc_seri' => S($f[1]), 'cdoc_nro' => S($f[2]),
                'dfch_doc' => S($f[3]), 'ccod_tienda' => S($f[4]), 'cdsc_tienda' => S($f[5]),
                'ccod_caja' => S($f[6]), 'cdsc_caja' => S($f[7]), 'cusu_crea' => S($f[8]),
                'cdsc_usuario' => S($f[9]), 'ccod_coa' => S($f[10]), 'ccoa_dsc' => S($f[11]),
                'ccod_articulo' => S($f[12]), 'cdsc_articulo' => S($f[13]), 'ncantidad' => S($f[14]),
                'nprecio' => S($f[15]), 'nimporte_neto' => S($f[16]), 'ntotal' => S($f[17]),
                'cdocInve' => S($f[18]), 'cdoc_serieInve' => S($f[19]), 'cdoc_nroInve' => S($f[20]),
                'id_cbfact' => S($f[21]));
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarNumerador':
        $rows = Database::selectStoredTenant('webDatpos_cargarCodigoDocumentos', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cdoc_tipo' => S($f[0]), 'cdsc_numer' => S($f[1])); }
        jsonResponse(array('d' => $lst)); break;
    case 'ConsultaListArticulos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaListArticulos', array(
            '@cdoc' => $input['cdoc'] ?? '', '@cdoc_serie' => $input['cdoc_serie'] ?? '',
            '@cdoc_nro' => $input['cdoc_nro'] ?? '', '@CodCia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_articulo' => S($f[0]), 'cdsc_articulo' => S($f[1]),
                'ncantidad' => S($f[2]), 'nimporte_neto' => S($f[3]),
                'nprecio' => S($f[4]), 'nimpuesto' => S($f[5]),
                'nisc' => S($f[6]), 'ndescuento' => S($f[7]));
        }
        jsonResponse(array('d' => $lst)); break;
    case 'ConsultaListArticulosPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaListArticulosPorId', array(
            '@id_cbfact' => $input['id_cbfact'] ?? '', '@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_articulo' => S($f[0]), 'cdsc_articulo' => S($f[1]),
                'ncantidad' => S($f[2]), 'nimporte_neto' => S($f[3]),
                'nprecio' => S($f[4]), 'nimpuesto' => S($f[5]),
                'nisc' => S($f[6]), 'ndescuento' => S($f[7]));
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarTienda':
        $rows = Database::selectStoredTenant('webDatpos_consultaTienda', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_tiend' => S($f[0]), 'cnombr' => S($f[1])); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarCliente':
        $rows = Database::selectStoredTenant('webDatpos_ConsultarClientes', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'ccod_coa' => S($f[0]), 'cdsc_coa' => S($f[1])); }
        jsonResponse(array('d' => $lst)); break;
    case 'DatosGenerales':
        $rows = Database::selectStoredTenant('webDatpos_DatosGenerales', array(
            '@ccod_cia' => $o->ccod_empresa, '@ccod_usuario' => $o->ccod_usuario), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_tienda' => S($f[0]), 'cdsc_alm' => S($f[1]),
                'cdsc_caja' => S($f[2]), 'nlista_pre_normal' => S($f[3]),
                'nlista_pre_preferencial' => S($f[4]), 'cdsc_listpreNorm' => S($f[5]),
                'cdsc_listprePref' => S($f[6]));
        }
        jsonResponse(array('d' => $lst)); break;
    default: jsonResponse(array('d' => array()));
}
?>
