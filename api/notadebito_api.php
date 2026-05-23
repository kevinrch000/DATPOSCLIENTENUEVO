<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLNotaDebito.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$objBL = new BLNotaDebito();
$m = $_GET['method'] ?? '';

function S($v) { return ($v !== null) ? strval($v) : ''; }

switch ($m) {
    case 'ConsultarDocumentos':
        $input = getJsonInput(); $data = $input['notacredito'][0] ?? array();
        $rows = Database::selectStoredTenant('webDatpos_NotaCreditoPricipal', array(
            '@cdoc_seri' => $data['cdoc'] ?? '', '@serie' => $data['cdoc_serie'] ?? '',
            '@correlativo' => $data['cdoc_nro'] ?? '', '@ccod_tienda' => $data['ccod_tienda'] ?? '',
            '@ccod_coa' => $data['ccod_coa'] ?? '', '@fchDesde' => $data['n_fchDesde'] ?? '',
            '@fchHasta' => $data['n_fchHasta'] ?? '', '@CodCia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            if (count($f) <= 8) {
                $id = S($f[0] ?? '');
                $lst[] = array('id_cbfact' => $id, 'cdoc' => S($f[1] ?? ''),
                    'cdoc_serie' => S($f[2] ?? ''), 'cdoc_nro' => S($f[3] ?? ''),
                    'ccoa_dsc' => S($f[6] ?? ''), 'cdsc_tienda' => '',
                    'ntotal' => S($f[5] ?? ''), 'dfch_doc' => S($f[4] ?? ''),
                    'DocFact' => "<td class='text-center'><i title='Documento Ref.' id='" . $id . "' onclick='ModalDocFac(this);' data-toggle='modal' data-target='#modalBuscarDoc'  class='fa fa-arrow-right color-popup-verde' aria-hidden='true'></i></td>",
                    'NotaDebito' => "<td class='text-center'><i title='Nota de Débito' id='" . $id . "' onclick='ModalGenerarNotaDebito(this);' data-toggle='modal' data-target='#modalNotaDebito'  class='fa fa-file-text' aria-hidden='true'></i></td>",
                    'cdoc_coa' => S($f[7] ?? ''), 'ccod_alm' => '');
            } else {
                $lst[] = array('id_cbfact' => S($f[0]), 'cdoc' => S($f[1]),
                    'cdoc_serie' => S($f[2]), 'cdoc_nro' => S($f[3]),
                    'ccoa_dsc' => S($f[4]), 'cdsc_tienda' => S($f[5]),
                    'ntotal' => S($f[6]), 'dfch_doc' => S($f[7]),
                    'DocFact' => "<td class='text-center'><i title='Documento Ref.' id='" . S($f[0]) . "' onclick='ModalDocFac(this);' data-toggle='modal' data-target='#modalBuscarDoc'  class='fa fa-arrow-right color-popup-verde' aria-hidden='true'></i></td>",
                    'NotaDebito' => "<td class='text-center'><i title='Nota de Débito' id='" . S($f[0]) . "' onclick='ModalGenerarNotaDebito(this);' data-toggle='modal' data-target='#modalNotaDebito'  class='fa fa-file-text' aria-hidden='true'></i></td>",
                    'ccod_coa' => S($f[8] ?? ''), 'ccod_tienda' => S($f[9] ?? ''), 'ccod_caja' => S($f[10] ?? ''),
                    'cdsc_caja' => S($f[11] ?? ''), 'cusu_crea' => S($f[12] ?? ''), 'cdsc_usuario' => S($f[13] ?? ''),
                    'Doc' => S($f[14] ?? ''), 'ntot_entreg' => S($f[15] ?? ''), 'nvuelto' => S($f[16] ?? ''),
                    'ccod_alm' => S($f[17] ?? ''), 'cdsc_alm' => S($f[18] ?? ''), 'cdocCobr' => S($f[19] ?? ''),
                    'cdoc_coa' => S($f[20] ?? ''), 'ctip_doc' => S($f[21] ?? ''));
            }
        } jsonResponse(array('d' => $lst)); break;

    case 'GenerarNotaDebito':
        $input = getJsonInput(); $data = $input['notadebito'][0] ?? array();
        $result = $objBL->GenerarNotaDebito(
            intval($data['id_cbfact'] ?? 0),
            $o->ccod_usuario,
            floatval($data['nimp_aplicado'] ?? 0),
            $o->ccod_empresa,
            $o
        );
        $lst = array();
        foreach ($result as $f) {
            $lst[] = array('Doc' => S($f[0]), 'cdoc' => S($f[1]),
                'cdoc_serie' => S($f[2]), 'cdoc_nro' => S($f[3]),
                'cmail' => S($f[4]), 'dfch_crea' => S($f[5]),
                'dhra_crea' => S($f[6]), 'id_cbfact' => S($f[7]));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarCliente':
        $rows = Database::selectStoredTenant('webDatpos_ConsultarClientes', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'ccod_coa' => S($f[0] ?? ''), 'cdsc_coa' => S($f[1] ?? '')); }
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
    case 'ConsultaListCobranzaPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaListCobranzaPorId', array(
            '@id_cbfact' => $input['id_cbfact'] ?? '', '@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cnom_tarje' => S($f[0]), 'cnum_opera' => S($f[1]),
                'cnum_tarje' => S($f[2]), 'nmonto' => S($f[3]));
        }
        jsonResponse(array('d' => $lst)); break;
    default: jsonResponse(array('d' => array()));
}
?>
