<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLNotaCredito.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$objBL = new BLNotaCredito();
$method = $_GET['method'] ?? '';

function S($v) { return ($v !== null) ? strval($v) : ''; }

switch ($method) {
    case 'ConsultarDocumentosNotaCredito':
        $input = getJsonInput();
        $data = $input['notacredito'][0] ?? array();
        // FIX 59: NotaCredito.js envia las claves cdoc / cdoc_serie /
        // cdoc_nro / n_fchDesde / n_fchHasta; aceptamos ambos nombres.
        $rows = $objBL->ConsultarDocumentosNotaCredito(
            $data['cdoc_seri'] ?? ($data['cdoc'] ?? ''),
            $data['serie'] ?? ($data['cdoc_serie'] ?? ''),
            $data['correlativo'] ?? ($data['cdoc_nro'] ?? ''),
            $data['ccod_tienda'] ?? '',
            $data['ccod_coa'] ?? '',
            $data['fchDesde'] ?? ($data['n_fchDesde'] ?? ''),
            $data['fchHasta'] ?? ($data['n_fchHasta'] ?? ''),
            $objUsuario->ccod_empresa,
            $objUsuario
        );
        $lst = array();
        foreach ($rows as $f) {
            if (count($f) <= 8) {
                $id = S($f[0] ?? '');
                // FIX 59: DataTable de NotaCredito.php declara las columnas
                // cdoc, cdoc_serie, cdoc_nro, cdoc_coa, ccoa_dsc, ntotal,
                // dfch_doc, cdsc_usuario, impresion; faltaba cdsc_usuario.
                $lst[] = array('DocFact' => "<input id='" . $id . "'  type='checkbox'  class='limpiar_checked'  onclick='checked_click(this)'>",
                    'cdoc' => S($f[1] ?? ''), 'cdoc_serie' => S($f[2] ?? ''),
                    'cdoc_nro' => S($f[3] ?? ''), 'ccoa_dsc' => S($f[7] ?? ''),
                    'cdsc_tienda' => '', 'ntotal' => S($f[5] ?? ''), 'dfch_doc' => S($f[4] ?? ''),
                    'ccod_alm' => '', 'cdsc_usuario' => 'ADMIN',
                    'impresion' => "<td class='text-center'><i title='Impresión' id='" . $id . "' onclick='ImprimirPDF(this);' class='fa fa-print' aria-hidden='true'></i></td>",
                    'id_cbfact' => $id, 'cdoc_coa' => '');
            } else {
                $id = S($f[8] ?? '');
                $lst[] = array('DocFact' => "<input id='" . $id . "'  type='checkbox'  class='limpiar_checked'  onclick='checked_click(this)'>",
                    'cdoc' => S($f[0]), 'cdoc_serie' => S($f[1]),
                    'cdoc_nro' => S($f[2]), 'ccoa_dsc' => S($f[3]),
                    'cdsc_tienda' => S($f[4]), 'ntotal' => S($f[5]),
                    'dfch_doc' => S($f[6]), 'ccod_alm' => S($f[7]),
                    'impresion' => "<td class='text-center'><i title='Impresión' id='" . $id . "' onclick='ImprimirPDF(this);' class='fa fa-print' aria-hidden='true'></i></td>",
                    'id_cbfact' => $id, 'cod_motivo' => S($f[9] ?? ''),
                    'ccod_coa' => S($f[10] ?? ''), 'ccod_tienda' => S($f[11] ?? ''),
                    'ccod_caja' => S($f[12] ?? ''), 'cdsc_caja' => S($f[13] ?? ''),
                    'cusu_crea' => S($f[14] ?? ''), 'cdsc_usuario' => S($f[15] ?? ''),
                    'Doc' => S($f[16] ?? ''), 'ntot_entreg' => S($f[17] ?? ''),
                    'nvuelto' => S($f[18] ?? ''), 'cdsc_alm' => S($f[19] ?? ''),
                    'cdocCobr' => S($f[20] ?? ''), 'cdoc_coa' => S($f[21] ?? ''),
                    'id_cbinve' => S($f[22] ?? ''), 'montodisponible' => S($f[23] ?? ''),
                    'ctip_doc' => S($f[24] ?? ''));
            }
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'BuscarDocRef':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_buscardocref', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@codigo' => $input['codigo'] ?? '',
            '@serie' => $input['serie'] ?? '', '@correlativo' => $input['correlativo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ntotal' => S($f[0]), 'dfch_crea' => S($f[1]),
                'ccoa_dsc' => S($f[2]), 'ccod_coa' => S($f[3]),
                'id_cbfact' => S($f[4]), 'cod_motivo' => S($f[5]),
                'montodisponible' => S($f[6]), 'id_cbinve' => S($f[7]));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarNotaCredito':
        $input = getJsonInput();
        $rows = $objBL->ConsultarNotaCredito($input['codigo'] ?? '', $objUsuario->ccod_empresa, $objUsuario);
        jsonResponse(array('d' => $rows));
        break;

    case 'GenerarNotaCredito':
        $input = getJsonInput();
        $data = $input['AnulacionNC'][0] ?? $input;
        $result = $objBL->GenerarNotaCredito(
            intval($data['id_cbfact'] ?? 0),
            floatval($data['nimp_aplicado'] ?? 0),
            $data['cdsc_movito'] ?? '',
            $objUsuario->ccod_usuario,
            $objUsuario->ccod_empresa,
            $objUsuario
        );
        $lst = array();
        foreach ($result as $f) {
            $lst[] = array('Doc' => S($f[0] ?? ''), 'cdoc' => S($f[1] ?? ''),
                'cdoc_serie' => S($f[2] ?? ''), 'cdoc_nro' => S($f[3] ?? ''),
                'cmail' => S($f[4] ?? ''), 'dfch_crea' => S($f[5] ?? ''),
                'dhra_crea' => S($f[6] ?? ''), 'id_cbfact' => S($f[7] ?? ($data['id_cbfact'] ?? '')));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'GenerarNotaCreditoDescuento':
        $input = getJsonInput();
        $result = $objBL->GenerarNotaCreditoDescuento(
            intval($input['id_cbfact'] ?? 0),
            floatval($input['nimp_aplicado'] ?? 0),
            $input['cdsc_movito'] ?? '',
            $objUsuario->ccod_usuario,
            $objUsuario->ccod_empresa,
            $objUsuario
        );
        jsonResponse(array('d' => $result));
        break;

    case 'GenerarNotaCreditoDevolucion':
        $input = getJsonInput();
        $data = $input['AnulacionNC'][0] ?? $input;
        $result = $objBL->GenerarNotaCreditoDevolucion(
            $objUsuario->ccod_empresa,
            intval($data['id_cbfact'] ?? 0),
            $data['cdsc_movito'] ?? $data['motivo'] ?? '',
            $objUsuario->ccod_usuario,
            $objUsuario
        );
        // FIX 58: MODIFY_860 hace que el SP devuelva una sola fila con
        //   [id_orig, estado, numdoc, credito, id_nc].
        // NotaCredito.js espera response.d[1]=='OK', d[2]=numdoc,
        // d[3]=credito, d[4]=id_nc, asi que aplanamos directamente
        // la primera fila a la respuesta.
        $row = $result[0] ?? array('', 'ERR', '', '0', '');
        jsonResponse(array('d' => array(
            S($row[0] ?? ''),
            S($row[1] ?? ''),
            S($row[2] ?? ''),
            S($row[3] ?? '0'),
            S($row[4] ?? ''),
        )));
        break;

    case 'InsertarNotaCredito':
        $input = getJsonInput();
        $result = $objBL->InsertarNotaCredito(
            intval($input['id_cbfact'] ?? 0),
            $input['cod_motivo'] ?? '',
            floatval($input['nimp_aplicado'] ?? 0),
            $input['cdsc_movito'] ?? '',
            $objUsuario->ccod_usuario,
            $objUsuario->ccod_empresa,
            $objUsuario
        );
        jsonResponse(array('d' => $result));
        break;

    case 'DetalleNotaCredito':
        $input = getJsonInput();
        $rows = $objBL->DetalleNotaCredito(intval($input['id_cbfact'] ?? 0), $objUsuario);
        jsonResponse(array('d' => $rows));
        break;

    case 'CargarCliente':
        $rows = Database::selectStoredTenant('webDatpos_ConsultarClientes', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'ccod_coa' => S($f[0]), 'cdsc_coa' => S($f[1])); }
        jsonResponse(array('d' => $lst)); break;

    case 'NCMontoRestante':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_NCMontoRestante', array(
            '@id_cbfact' => $input['id_cbfact'] ?? 0,
            '@ccod_cia' => $objUsuario->ccod_empresa
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ntotal' => S($f[0])); }
        jsonResponse(array('d' => $lst)); break;

    case 'ConsultaListArticulosPorId':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultaListArticulosPorId', array(
            '@id_cbfact' => $input['id_cbfact'] ?? 0,
            '@ccod_cia' => $objUsuario->ccod_empresa
        ), $objUsuario);
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
            '@id_cbfact' => $input['id_cbfact'] ?? 0,
            '@ccod_cia' => $objUsuario->ccod_empresa
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cnom_tarje' => S($f[0]), 'cnum_opera' => S($f[1]),
                'cnum_tarje' => S($f[2]), 'nmonto' => S($f[3]));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'ConsultarDocumentoDetalle':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultardocumentodetalle', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbfact' => $input['id_cbfact'] ?? 0
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_articulo' => S($f[0]), 'ncantidad' => S($f[1]), 'npre_uni' => S($f[2]), 'nimporte_neto' => S($f[3]));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'ConsultarDocumentoCobranza':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultardocumentocobranza', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbfact' => $input['id_cbfact'] ?? 0
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cnom_tarje' => S($f[0]), 'nmonto' => S($f[1]), 'cnum_tarje' => S($f[2]));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'ConsultarDocumentoCabecera':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultardocumentocabecera', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbfact' => $input['id_cbfact'] ?? 0
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdoc' => S($f[0]), 'cdoc_serie' => S($f[1]), 'cdoc_nro' => S($f[2]),
                'ccoa_dsc' => S($f[3]), 'ntotal' => S($f[4]), 'dfch_doc' => S($f[5]));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'ListaDeArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_listadearticulo', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbfact' => $input['id_cbfact'] ?? 0
        ), $objUsuario);
        // FIX 57: NotaCredito.js (CargarTablaDetalle/CargarTablaDetalleVer)
        // usa id_lnfact, ccod_articulo y nprecio en los <td>, no solo id_articulo.
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'id_articulo'   => S($f[0]),
                'cdsc_articulo' => S($f[1]),
                'ncantidad'     => S($f[2]),
                'id_lnfact'     => S($f[3] ?? ''),
                'ccod_articulo' => S($f[4] ?? ''),
                'nprecio'       => S($f[5] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;

    case 'ListaDeBienes':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_listadebienes', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_cbfact' => $input['id_cbfact'] ?? 0
        ), $objUsuario);
        // FIX 57: NotaCredito.js (CargarTablaDetalle) usa id_lnfact y
        // ccod_articulo en los <td>; antes salian "undefined".
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'id_articulo'   => S($f[0]),
                'cdsc_articulo' => S($f[1]),
                'ncantidad'     => S($f[2]),
                'id_lnfact'     => S($f[3] ?? ''),
                'ccod_articulo' => S($f[4] ?? ''),
                'nprecio'       => S($f[5] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;

    default:
        jsonResponse(array('d' => array()));
}
?>
