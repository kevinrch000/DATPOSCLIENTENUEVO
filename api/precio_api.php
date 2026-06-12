<?php
/**
 * DatPOS - API: Precios/Lista de Precios (Ventas)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

/**
 * Convierte fecha dd/mm/yyyy (datepicker español) → yyyy-mm-dd (SQL Server ymd).
 * Acepta también dd/mm/yy y formatos con guión. Si ya está en formato ISO, no la toca.
 */
function _precioConvertDate($dateStr) {
    $d = trim(strval($dateStr));
    if ($d === '') return $d;
    // Si ya tiene formato yyyy-mm-dd, no convertir
    if (preg_match('/^\d{4}[-\/]\d{2}[-\/]\d{2}/', $d)) return $d;
    // dd/mm/yyyy o dd-mm-yyyy
    if (preg_match('#^(\d{1,2})[/\-](\d{1,2})[/\-](\d{2,4})$#', $d, $m)) {
        $year = intval($m[3]);
        if ($year < 100) $year += 2000;
        return sprintf('%04d-%02d-%02d', $year, intval($m[2]), intval($m[1]));
    }
    return $d;
}

switch ($method) {
    case 'ConsultarListaPrecios':
        // FIX 73 / BUG 3.1: traducir cstatus a texto para la columna
        // Estado de tableOperAlmacen / table_id (Precios.js).
        // El SP devuelve 'A'/'I' crudo en col [6].
        $rows = Database::selectStoredTenant('sp_consultarlistasprecios', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $cs = strval($f[6] ?? '');
            $est = ($cs === 'A' || $cs === '1') ? 'Activo'
                 : (($cs === 'I' || $cs === '0') ? 'Inactivo' : $cs);
            $lst[] = array(
                'item' => "<input id='" . strval($f[2]) . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_cblistpre' => strval($f[0] ?? ''), 'ccod_cia' => strval($f[1] ?? ''), 'ccod_cblistpre' => strval($f[2] ?? ''),
                'cdsc_cblistpre' => strval($f[3] ?? ''), 'dfch_ini' => strval($f[4] ?? ''), 'dfch_fin' => strval($f[5] ?? ''),
                'estado' => $est,
                'cstatus' => $cs, // codigo crudo por compatibilidad
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarListaPrecio':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultalistaprecio', array('@ccod_cia' => $objUsuario->ccod_empresa, '@codigo' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('id_cblistpre' => strval($f[0] ?? ''), 'ccod_cia' => strval($f[1] ?? ''), 'ccod_cblistpre' => strval($f[2] ?? ''),
                'cdsc_cblistpre' => strval($f[3] ?? ''), 'dfch_ini' => strval($f[4] ?? ''), 'dfch_fin' => strval($f[5] ?? ''), 'cstatus' => strval($f[6] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarPrecios':
        $input = getJsonInput();
        $articulo = $input['Articulo'] ?? '';
        if (($input['TipFiltro'] ?? '') === '1') {
            $articulo = '';
        }
        $rows = Database::selectStoredTenant('sp_consultarprecios', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_cblistpre' => $input['listaprecio'] ?? '',
            '@TipFiltro' => $input['TipFiltro'] ?? '', '@Articulo' => $articulo
        ), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('id_lnlistpre' => strval($f[0] ?? ''), 'ccod_cia' => $objUsuario->ccod_empresa, 'id_cblistpre' => strval($f[1] ?? ''),
                'ccod_articulo' => strval($f[2] ?? ''), 'cdsc_articulo' => strval($f[3] ?? ''), 'npre_uni' => strval($f[4] ?? ''),
                'ndes_max' => strval($f[5] ?? ''), 'ndes_min' => strval($f[6] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ObtenerIvg':
        $rows = Database::selectStoredTenant('appDatpos_ObtenerIGV', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('igv' => strval($f[0] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarArticulos':
        // FIX 73 / BUG 3.1: traducir estado a texto.
        $rows = Database::selectStoredTenant('sp_consultararticulos', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $cs  = strval($f[4] ?? '');
            $est = ($cs === 'A' || $cs === '1') ? 'Activo'
                 : (($cs === 'I' || $cs === '0') ? 'Inactivo' : $cs);
            $lst[] = array('ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? ''),
                'linea' => strval($f[2] ?? ''), 'uni_medi' => strval($f[3] ?? ''), 'estado' => $est);
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCostosArticulos':
        // FIX 73 / BUG 3.1: traducir estado a texto.
        $rows = Database::selectStoredTenant('webDatpos_consultarCostosArticulos', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $cs  = strval($f[4] ?? '');
            $est = ($cs === 'A' || $cs === '1') ? 'Activo'
                 : (($cs === 'I' || $cs === '0') ? 'Inactivo' : $cs);
            $lst[] = array(
                'cbx'           => '',
                'ccod_articulo' => strval($f[0] ?? ''),
                'cdsc_articulo' => strval($f[1] ?? ''),
                'linea'         => strval($f[2] ?? ''),
                'uni_medi'      => strval($f[3] ?? ''),
                'estado'        => $est
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCostoArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultarCostoArticulo', array('@ccod_cia' => $objUsuario->ccod_empresa, '@codigo' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? ''), 'ncosto' => strval($f[2] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultararticulo', array('@ccod_cia' => $objUsuario->ccod_empresa, '@codigo' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_articulo' => strval($f[0] ?? ''), 'cdsc_articulo' => strval($f[1] ?? ''),
                'ccod_lin' => strval($f[2] ?? ''), 'uni_medi' => strval($f[3] ?? ''), 'cstatus' => intval($f[4] ?? 0));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $lista = $input['listaprecio'][0] ?? array();
        $precios = $input['precios'] ?? array();
        $operacion = $input['operacion'] ?? '';
        $spLista = ($operacion === 'editar') ? 'sp_editarlistaprecio' : 'sp_insertarlistaprecio';

        // Convertir fechas dd/mm/yyyy → yyyy-mm-dd para SQL Server (DATEFORMAT ymd)
        $fchIni = _precioConvertDate($lista['dfch_ini'] ?? '');
        $fchFin = _precioConvertDate($lista['dfch_fin'] ?? '');

        $spParams = array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_cblistpre' => trim($lista['ccod_cblistpre'] ?? ''),
            '@cdsc_cblistpre' => $lista['cdsc_cblistpre'] ?? '',
            '@dfch_ini' => $fchIni,
            '@dfch_fin' => $fchFin,
            '@cstatus' => $lista['cstatus'] ?? ''
        );

        error_log("[precio_api/Guardar] SP={$spLista} params=" . json_encode($spParams));
        $resp = Database::executeStoredTenant($spLista, $spParams, $objUsuario);

        if ($resp) {
            foreach ($precios as $precio) {
                $state = strval($precio['state'] ?? (($operacion === 'nuevo') ? '1' : '0'));
                if ($state === '0') {
                    continue;
                }

                if ($state === '3') {
                    $resp = Database::executeStoredTenant('sp_eliminarprecio', array(
                        '@id_lnlistpre' => intval($precio['id_lnlistpre'] ?? 0)
                    ), $objUsuario);
                } else {
                    $spPrecio = ($state === '2') ? 'sp_editarprecio' : 'sp_insertarprecio';
                    $params = array(
                        '@ccod_articulo' => $precio['ccod_articulo'] ?? '',
                        '@npre_uni' => floatval($precio['npre_uni'] ?? 0),
                        '@ndes_max' => floatval($precio['ndes_max'] ?? 0),
                        '@ndes_min' => floatval($precio['ndes_min'] ?? 0)
                    );
                    if ($spPrecio === 'sp_editarprecio') {
                        $params = array('@id_lnlistpre' => intval($precio['id_lnlistpre'] ?? 0)) + $params;
                    } else {
                        $params = array(
                            '@ccod_cia' => $objUsuario->ccod_empresa,
                            '@ccod_cblistpre' => $lista['ccod_cblistpre'] ?? ''
                        ) + $params;
                    }
                    $resp = Database::executeStoredTenant($spPrecio, $params, $objUsuario);
                }

                if (!$resp) {
                    break;
                }
            }
        }

        jsonResponse(array('d' => $resp));
        break;

    case 'Editar':
        $input = getJsonInput();
        $resp = Database::executeStoredTenant('sp_editarprecio', array(
            '@id_lnlistpre' => intval($input['id_lnlistpre'] ?? 0),
            '@ccod_articulo' => $input['articulo'] ?? '',
            '@npre_uni' => floatval($input['npre_uni'] ?? 0),
            '@ndes_max' => floatval($input['ndes_max'] ?? 0),
            '@ndes_min' => floatval($input['ndes_min'] ?? 0)
        ), $objUsuario);
        jsonResponse(array('d' => $resp));
        break;

    case 'EliminarPrecio':
        $input = getJsonInput();
        $resp = Database::executeStoredTenant('sp_eliminarprecio', array(
            '@id_lnlistpre' => intval($input['id_lnlistpre'] ?? 0)
        ), $objUsuario);
        jsonResponse(array('d' => $resp));
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $resp = Database::executeStoredTenant('sp_eliminarlistaprecio', array('@ccod_cblistpre' => $input['listaprecio'] ?? '', '@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        jsonResponse(array('d' => $resp));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
