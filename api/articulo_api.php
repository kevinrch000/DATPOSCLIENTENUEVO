<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'ConsultarArticulos':
        // sp_consultararticulos -> A.id_articulo(0), A.ccod_articulo(1), A.cdsc_articulo(2), A.ccod_lin(3),
        //   F.cdsc_lin(4), A.uni_medi(5), A.cstatus(6), A.ctip_articulo(7), A.cigv(8), A.cisc(9), A.ccod_artSunat(10), A.bprefer(11), ...
        $rows = Database::selectStoredTenant('sp_consultararticulos', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $cs = strval($f[6] ?? '');
            $lst[] = array(
                'item'          => "<input id='".strval($f[1] ?? '')."' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_articulo'   => strval($f[0] ?? ''),
                'ccod_articulo' => strval($f[1] ?? ''),
                'cdsc_articulo' => strval($f[2] ?? ''),
                'linea'         => strval($f[4] ?? ''),     // descripción de familia (lo que el JS pinta)
                'cdsc_lin'      => strval($f[4] ?? ''),     // alias
                'ccod_lin'      => strval($f[3] ?? ''),
                'uni_medi'      => strval($f[5] ?? ''),
                'cdsc_umd'      => strval($f[5] ?? ''),     // alias
                'ctip_articulo' => strval($f[7] ?? ''),
                'cigv'          => strval($f[8] ?? ''),
                'cisc'          => strval($f[9] ?? ''),
                'estado'        => ($cs === 'A' || $cs === '1') ? 'Activo' : 'Inactivo',
            );
        } jsonResponse(array('d' => $lst)); break;
    case 'ConsultarArticulo':
        $input = getJsonInput();
        // sp_consultararticulo => SELECT * FROM Articulos:
        //   id_articulo(0), ccod_cia(1), ccod_articulo(2), cdsc_articulo(3), ccod_lin(4), uni_medi(5),
        //   cstatus(6), ctip_articulo(7), cigv(8), cisc(9), iimage(10), ccod_artSunat(11), nstock_max(12), nstock_min(13),
        //   ctipo_isc(14), nporcentaje_isc(15), nmonto_isc(16)
        $rows = Database::selectStoredTenant('sp_consultararticulo', array('@ccod_cia' => $o->ccod_empresa, '@ccod_articulo' => $input['codigo'] ?? ''), $o);
        $lst = array();
        foreach ($rows as $f) {
            $cs = strval($f[6] ?? '');
            $lst[] = array(
                'id_articulo'    => strval($f[0] ?? ''),
                'ccod_articulo'  => strval($f[2] ?? ''),
                'cdsc_articulo'  => strval($f[3] ?? ''),
                'ccod_lin'       => strval($f[4] ?? ''),
                'uni_medi'       => strval($f[5] ?? ''),
                'ccod_umd'       => strval($f[5] ?? ''),
                'cstatus'        => ($cs === 'A') ? '1' : (($cs === 'I') ? '0' : $cs),
                'ctip_articulo'  => strval($f[7] ?? ''),
                'cigv'           => strval($f[8] ?? ''),
                'cisc'           => strval($f[9] ?? ''),
                'iimage'         => (!empty($f[10])) ? base64_encode($f[10]) : '',
                'ccod_artSunat'  => strval($f[11] ?? ''),
                'nstock_max'     => strval($f[12] ?? '0'),
                'nstock_min'     => strval($f[13] ?? '0'),
                'ctipo_isc'      => strval($f[14] ?? ''),
                'nporcentaje_isc'=> strval($f[15] ?? '0'),
                'nmonto_isc'     => strval($f[16] ?? '0'),
            );
        }
        jsonResponse(array('d' => $lst));
        break;
    case 'ConsultarVarianteArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultarVarianteArticulo', array('@ccod_cia' => $o->ccod_empresa, '@id_articulo' => $input['codigo'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('id_cbvariante' => strval($f[0] ?? ''), 'cdsc_variante' => strval($f[1] ?? ''),
                'cstatus' => strval($f[2] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'ConsultarDetalleVarianteArticulo':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_consultarDetalleVarianteArticulo', array('@ccod_cia' => $o->ccod_empresa, '@ccod_articulo' => $input['ccod_articulo'] ?? ''), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('id_cbdetvariante' => strval($f[0] ?? ''), 'id_cbvariante' => strval($f[1] ?? ''),
                'cdsc_subvariante' => strval($f[2] ?? ''), 'cstatus' => strval($f[3] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'Guardar':
        $input = getJsonInput(); $data = $input['articulo'][0] ?? array(); $op = $input['operacion'] ?? '';
        $cs = strval($data['cstatus'] ?? '1');
        // SP shape (idéntico para insertar / editar):
        // @ccod_cia, @ccod_articulo, @cdsc_articulo, @ccod_lin, @uni_medi, @cstatus, @ctip_articulo,
        // @cigv, @cisc, @iimage, @ccod_artSunat, @nstock_max, @nstock_min, @ctipo_isc, @nporcentaje_isc, @nmonto_isc, @ccod_usuario
        $sp = ($op === 'nuevo') ? 'webDatpos_insertar_Articulo' : 'webDatpos_editarArticulo';

        // Decodificar imagen base64 -> binario, NULL si vacia
        $iimg = $data['iimage'] ?? null;
        if (is_string($iimg) && $iimg !== '') {
            // soporta "data:image/png;base64,..." o base64 plano
            if (strpos($iimg, ',') !== false) {
                $iimg = explode(',', $iimg, 2)[1];
            }
            $iimg = base64_decode($iimg, true);
            if ($iimg === false) { $iimg = null; }
        } else {
            $iimg = null;
        }

        $conn = Database::getTenantConnection($o);
        if (!$conn) {
            jsonResponse(array('d' => array(array('ccod_articulo' => 'ERR', 'cdsc_articulo' => 'No hay conexion a BD'))));
        }

        $sql = "EXEC {$sp} @ccod_cia=?, @ccod_articulo=?, @cdsc_articulo=?, @ccod_lin=?, @uni_medi=?, @cstatus=?, "
             . "@ctip_articulo=?, @cigv=?, @cisc=?, @iimage=?, @ccod_artSunat=?, @nstock_max=?, @nstock_min=?, "
             . "@ctipo_isc=?, @nporcentaje_isc=?, @nmonto_isc=?, @ccod_usuario=?";

        $params = array(
            strval($o->ccod_empresa ?? ''),
            strval($data['ccod_articulo'] ?? ''),
            strval($data['cdsc_articulo'] ?? ''),
            strval($data['ccod_lin'] ?? ''),
            strval($data['ccod_umd'] ?? $data['uni_medi'] ?? ''),
            ($cs === '1' || $cs === 'A') ? 'A' : 'I',
            strval($data['ctip_articulo'] ?? 'P'),
            strval($data['cigv'] ?? 'S'),
            strval($data['cisc'] ?? 'N'),
            // bind tipado: VARBINARY(MAX) — soluciona "Implicit conversion varchar->varbinary"
            array($iimg, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')),
            strval($data['ccod_artSunat'] ?? ''),
            floatval($data['nstock_max'] ?? 0),
            floatval($data['nstock_min'] ?? 0),
            strval($data['ctipo_isc'] ?? ''),
            floatval($data['nporcentaje_isc'] ?? 0),
            floatval($data['nmonto_isc'] ?? 0),
            strval($o->ccod_usuario ?? ''),
        );

        // Si iimg es null no podemos pasar SQLSRV_PHPTYPE_STREAM (no hay stream) -> bind NULL plano
        if ($iimg === null) {
            $params[9] = array(null, SQLSRV_PARAM_IN, null, SQLSRV_SQLTYPE_VARBINARY('max'));
        } else {
            // crear stream desde el string binario
            $h = fopen('php://memory', 'r+');
            fwrite($h, $iimg);
            rewind($h);
            $params[9] = array($h, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'));
        }

        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt === false) {
            $errs = sqlsrv_errors();
            $msg = is_array($errs) ? ($errs[0]['message'] ?? 'sqlsrv_query fallo') : 'sqlsrv_query fallo';
            $code = 'ERR';
            if (stripos($msg, '2627') !== false) { $code = '2627'; }
            elseif (stripos($msg, 'ExisteSaldo') !== false) { $code = 'ExisteSaldo'; }
            error_log('[articulo_api Guardar] '.$msg);
            sqlsrv_close($conn);
            // El JS lee response.d como array POSICIONAL: [bool, code, message]
            jsonResponse(array('d' => array(false, $code, $msg)));
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        jsonResponse(array('d' => array(true, 'OK', '')));
        break;
    case 'Eliminar':
        $input = getJsonInput();
        $cod = $input['articulo'] ?? $input['codigo'] ?? $input['id'] ?? '';
        $conn = Database::getTenantConnection($o);
        if (!$conn) {
            jsonResponse(array('d' => array(false, 'ERR', 'No hay conexion a BD')));
        }
        $stmt = sqlsrv_query($conn, "EXEC sp_eliminararticulo @ccod_cia=?, @ccod_articulo=?", array(strval($o->ccod_empresa), strval($cod)));
        if ($stmt === false) {
            $errs = sqlsrv_errors();
            $msg = is_array($errs) ? ($errs[0]['message'] ?? 'sqlsrv_query fallo') : 'sqlsrv_query fallo';
            $code = (stripos($msg, '547') !== false) ? '547' : 'ERR';
            error_log('[articulo_api Eliminar] '.$msg);
            sqlsrv_close($conn);
            // El JS lee response.d como array POSICIONAL: [bool, code, message]
            jsonResponse(array('d' => array(false, $code, $msg)));
        }
        sqlsrv_free_stmt($stmt);
        sqlsrv_close($conn);
        jsonResponse(array('d' => array(true, 'OK', '')));
        break;
    case 'GetNextCodigoArticulo':
        $input = getJsonInput();
        $ccod_lin = trim(strval($input['ccod_lin'] ?? ''));
        if ($ccod_lin === '') {
            jsonResponse(array('d' => ''));
            break;
        }
        $conn = Database::getTenantConnection($o);
        if (!$conn) {
            jsonResponse(array('d' => ''));
            break;
        }
        $likePattern = $ccod_lin . '[0-9][0-9][0-9][0-9][0-9]';
        $sql = "SELECT TOP 1 ccod_articulo 
                FROM Articulos 
                WHERE ccod_cia = ? AND ccod_lin = ? AND ccod_articulo LIKE ? 
                ORDER BY ccod_articulo DESC";
        $stmt = sqlsrv_query($conn, $sql, array($o->ccod_empresa, $ccod_lin, $likePattern));
        $nextCode = '';
        if ($stmt) {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC);
            if ($row) {
                $lastCode = strval($row[0] ?? '');
                $correlativoStr = substr($lastCode, -5);
                $correlativo = intval($correlativoStr);
                $nextCorrelativo = $correlativo + 1;
                $nextCode = $ccod_lin . str_pad($nextCorrelativo, 5, '0', STR_PAD_LEFT);
            } else {
                $nextCode = $ccod_lin . '00001';
            }
            sqlsrv_free_stmt($stmt);
        } else {
            $nextCode = $ccod_lin . '00001';
        }
        sqlsrv_close($conn);
        jsonResponse(array('d' => $nextCode));
        break;
    case 'DatosGenerales':
        $rows = Database::selectStoredTenant('webDatpos_datosGenerales', array('@ccod_cia' => $o->ccod_empresa, '@ccod_usuario' => $o->ccod_usuario), $o);
        $lst = array(); if (count($rows) > 0) { $f = $rows[0];
            $lst[] = array('cdsc_tienda' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''),
                'cdsc_caja' => strval($f[2] ?? ''), 'nlista_pre_normal' => strval($f[3] ?? ''),
                'cdsc_listpreNorm' => strval($f[4] ?? ''), 'nlista_pre_preferencial' => strval($f[5] ?? ''),
                'cdsc_listprePref' => strval($f[6] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    default: jsonResponse(array('d' => array()));
}
?>
