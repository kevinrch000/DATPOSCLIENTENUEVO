<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'DatosConfigGenreal':
        $rows = Database::selectStoredTenant('webDatpos_datosConfigGenreal', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_clibol' => strval($f[0] ?? ''), 'cnom_clibol' => strval($f[1] ?? ''),
                'ccod_OperIngreso' => strval($f[2] ?? ''), 'ccod_OperSalida' => strval($f[3] ?? ''),
                'cnom_OperIngreso' => strval($f[4] ?? ''), 'cnom_OperSalida' => strval($f[5] ?? ''),
                'nigv' => strval($f[6] ?? ''), 'nisc' => strval($f[7] ?? ''), 'nmonto_maxboleta' => strval($f[8] ?? ''),
                'ilogo' => (!empty($f[9])) ? base64_encode($f[9]) : '');
        } jsonResponse(array('d' => $lst)); break;
    case 'CargarDepartamento':
        // Tablas Departamento / Provincia / Distrito están en DatPosAdmin (no en el tenant).
        $conn = Database::getAdminConnection();
        $lst = array();
        if ($conn) {
            $stmt = sqlsrv_query($conn, "SELECT id_departamento, cdescripcion FROM Departamento ORDER BY cdescripcion");
            if ($stmt) { while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) { $lst[] = array('id' => strval($f[0] ?? ''), 'name' => strval($f[1] ?? '')); } sqlsrv_free_stmt($stmt); }
            sqlsrv_close($conn);
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarProvincia':
        $input = getJsonInput();
        $idDep = strval($input['id_departamento'] ?? '');
        $conn = Database::getAdminConnection();
        $lst = array();
        if ($conn && $idDep !== '') {
            $stmt = sqlsrv_query($conn, "SELECT id_provincia, cdescripcion FROM Provincia WHERE id_departamento=? ORDER BY cdescripcion", array($idDep));
            if ($stmt) { while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) { $lst[] = array('id' => strval($f[0] ?? ''), 'name' => strval($f[1] ?? '')); } sqlsrv_free_stmt($stmt); }
            sqlsrv_close($conn);
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarDistrito':
        $input = getJsonInput();
        $idProv = strval($input['id_provincia'] ?? '');
        $conn = Database::getAdminConnection();
        $lst = array();
        if ($conn && $idProv !== '') {
            $stmt = sqlsrv_query($conn, "SELECT id_distrito, cdescripcion FROM Distrito WHERE id_provincia=? ORDER BY cdescripcion", array($idProv));
            if ($stmt) { while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) { $lst[] = array('id' => strval($f[0] ?? ''), 'name' => strval($f[1] ?? '')); } sqlsrv_free_stmt($stmt); }
            sqlsrv_close($conn);
        }
        jsonResponse(array('d' => $lst)); break;
    case 'AlmacenAsignado': jsonResponse(array('d' => $o->ccod_almacen ?? '')); break;
    case 'TiendaAsignada': jsonResponse(array('d' => $o->ccod_tiend ?? '')); break;
    case 'CajaAsignada': jsonResponse(array('d' => $o->ccod_caja ?? '')); break;
    case 'ConsultaColumnas':
        // SP sin parámetros en BD del cliente
        $rows = Database::selectStoredTenant('webDatpos_consultaColumnas', array(), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('TipoDato' => strval($f[0] ?? ''), 'longitud' => strval($f[1] ?? ''),
                'CantEnteros' => strval($f[2] ?? ''), 'CantDecimales' => strval($f[3] ?? ''),
                'DscColumna' => strval($f[4] ?? ''), 'DscTabla' => strval($f[5] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'CargarTienda':
        $rows = Database::selectStoredTenant('webDatpos_consultaTienda', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_tiend' => strval($f[0] ?? ''), 'cnombr' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarCaja':
        $rows = Database::selectStoredTenant('webDatpos_consultaCaja', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_caja' => strval($f[0] ?? ''), 'cdsc_caja' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarFamilia':
        $rows = Database::selectStoredTenant('webDatpos_consultaFamilia', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_lin' => strval($f[0] ?? ''), 'cdsc_lin' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarUnidadMedida':
        $rows = Database::selectStoredTenant('webDatpos_consultaUnidadMedida', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_unidadmedida' => strval($f[0] ?? ''), 'cdsc_unidadmedida' => strval($f[1] ?? ''),
                           'ccod_umd' => strval($f[0] ?? ''), 'cdsc_umd' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarAlmacenes':
        // ConsultarAlmEmpActivos usa webDatpos_consultaAlmacen @ccod_cia
        $rows = Database::selectStoredTenant('webDatpos_consultaAlmacen', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarCliente':
        // sp_consultaclientes tiene 2 versiones desplegadas en distintos tenants:
        //   17 columnas (FIX 20): [0]id_coa,[1]ccod_cia,[2]ccod_coa,[3]cdoc_coa,[4]cdsc_coa,...
        //    9 columnas (legacy): [0]id_coa,[1]ccod_coa,[2]cdoc_coa,[3]cdsc_coa,...
        // Detectamos por cantidad de columnas para mapear sin requerir migracion DB.
        $rows = Database::selectStoredTenant('sp_consultaclientes', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $isFix = (is_array($f) && count($f) >= 17);
            $codIdx = $isFix ? 2 : 1;
            $dscIdx = $isFix ? 4 : 3;
            $lst[] = array(
                'cbx'      => '',
                'id_coa'   => strval($f[0] ?? ''),
                'ccod_coa' => strval($f[$codIdx] ?? ''),
                'cdsc_coa' => strval($f[$dscIdx] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarClientePredeterminado':
        $rows = Database::selectStoredTenant('sp_clientepordefecto', array('@ccod_cia' => $o->ccod_empresa, '@ccod_tiend' => $o->ccod_tiend), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ctip_doc' => strval($f[0] ?? ''), 'cdoc_coa' => strval($f[1] ?? ''),
                'cdsc_coa' => strval($f[2] ?? ''), 'cdirc_coa' => strval($f[3] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    case 'CargarListaUsuario':
        $rows = Database::selectStoredTenant('webDatpos_CargarListaUsuario', array('@ccod_empresa' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'ccod_usuario' => strval($f[0] ?? ''), 'cdsc_usuario' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarNumeradorFactura':
        $rows = Database::selectStoredTenant('webDatpos_cargarCodigoDocumentos', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cdoc_tipo' => strval($f[0] ?? ''), 'cdsc_numer' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarNumeradorTipoOper':
        $rows = Database::selectStoredTenant('webDatpos_cargarTiposOperacionIngreso', array('@ccod_cia' => $o->ccod_empresa), $o);
        // SP devuelve: ccod_tipoper(0), cdsc_tipoper(1)
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_toper' => strval($f[0] ?? ''), 'cdsc_toper' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'CodigoOperacionIngreso':
        // SP devuelve 3 columnas: ccod_toper(0), cdsc_toper(1), ctipo_flag_Oper(2)
        $rows = Database::selectStoredTenant('webDatpos_codigoOperacionIngreso', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'ccod_toper'      => strval($f[0] ?? ''),
                'cdsc_toper'      => strval($f[1] ?? ''),
                'ctipo_flag_Oper' => strval($f[2] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CodigoOperacionSalida':
        // SP devuelve 3 columnas: ccod_toper(0), cdsc_toper(1), ctipo_flag_Oper(2)
        $rows = Database::selectStoredTenant('webDatpos_codigoOperacionSalida', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'ccod_toper'      => strval($f[0] ?? ''),
                'cdsc_toper'      => strval($f[1] ?? ''),
                'ctipo_flag_Oper' => strval($f[2] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;
    case 'CargarArticulo':
        // ConsultaArticulos.aspx CargarArticulo usa sp_consultararticulos
        // SP devuelve: [0]id_articulo,[1]ccod_articulo,[2]cdsc_articulo,...
        $rows = Database::selectStoredTenant('sp_consultararticulos', array('@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_articulo' => strval($f[0] ?? ''), 'ccod_articulo' => strval($f[1] ?? ''), 'cdsc_articulo' => strval($f[2] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'DatosRucApi':
        // Consulta el RUC en el servicio externo apifacturador.msgsac.net y devuelve
        // los datos normalizados (nombre_o_razon_social, domicilio_fiscal, ubigeo, etc.).
        // El servicio responde XML (RespSunatConsRuc3) por defecto, aunque pidamos
        // Accept: application/json. Por eso parseamos XML primero y caemos a JSON
        // como fallback.
        $input = getJsonInput();
        $ruc = trim(strval($input['ruc'] ?? $input['doc'] ?? $input['vRuc'] ?? ''));
        $resp = array(
            'ruc'                  => $ruc,
            'nombre_o_razon_social'=> '',
            'domicilio_fiscal'     => '',
            'ubigeo'               => '',
            'estado'               => '',
            'condicion'            => '',
            'tipo'                 => '',
            'success'              => false,
            'mensaje'              => '',
        );
        if (preg_match('/^\d{11}$/', $ruc)) {
            // El servicio en apifacturador.msgsac.net:441 NO acepta TLS en ese
            // puerto (el handshake falla con "wrong version number"); responde
            // unicamente HTTP plano sobre el puerto 441 con JSON. Probamos HTTP
            // primero (caso real) y dejamos HTTPS como fallback por si en el
            // futuro el proveedor habilita TLS en el mismo puerto.
            $apiUrls = array(
                'http://apifacturador.msgsac.net:441/api/ConsultaRuc3?vRuc=' . urlencode($ruc),
                'https://apifacturador.msgsac.net:441/api/ConsultaRuc3?vRuc=' . urlencode($ruc),
            );
            $body = '';
            $lastErr = '';
            foreach ($apiUrls as $apiUrl) {
                if (function_exists('curl_init')) {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $apiUrl);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 8);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 4);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
                    $resBody = curl_exec($ch);
                    if ($resBody === false || $resBody === '') {
                        $lastErr = curl_error($ch);
                    } else {
                        $body = $resBody;
                        curl_close($ch);
                        break;
                    }
                    curl_close($ch);
                } else {
                    $ctx = stream_context_create(array(
                        'http' => array('timeout' => 8, 'ignore_errors' => true, 'header' => "Accept: application/json\r\n"),
                        'ssl'  => array('verify_peer' => false, 'verify_peer_name' => false),
                    ));
                    $resBody = @file_get_contents($apiUrl, false, $ctx);
                    if ($resBody !== false && $resBody !== '') {
                        $body = $resBody;
                        break;
                    }
                }
            }
            if ($body === '' && $lastErr !== '') {
                error_log('[DatosRucApi] todas las URLs fallaron. Ultimo error: ' . $lastErr);
            }

            // Mapa unificado de campos relevantes, independientemente del formato.
            $razon = ''; $direc = ''; $ubigeo = '';
            $estado = ''; $cond = ''; $tipo = '';
            $success = false; $mensaje = '';

            if ($body !== '' && stripos(ltrim($body), '<') === 0) {
                // Respuesta XML (RespSunatConsRuc3). Limpia namespaces para
                // que las claves del SimpleXML sean accesibles tal cual.
                $clean = preg_replace('/\sxmlns(:[a-z0-9]+)?="[^"]*"/i', '', $body);
                $xml = @simplexml_load_string($clean);
                if ($xml !== false) {
                    $get = function ($node, $key) {
                        return isset($node->$key) ? trim(strval($node->$key)) : '';
                    };
                    $razon  = $get($xml, 'nombre_o_razon_social');
                    $direc  = $get($xml, 'domicilio_fiscal');
                    $ubigeo = $get($xml, 'ubigeo');
                    $estado = $get($xml, 'contribuyente_estado');
                    $cond   = $get($xml, 'contribuyente_condicion');
                    $tipo   = $get($xml, 'contribuyente_tipo');
                    $mensaje = $get($xml, 'MensajeRuc');
                    $rawSucc = strtolower($get($xml, 'success'));
                    $success = ($rawSucc === 'true' || $rawSucc === '1');
                }
            } else {
                $data = ($body !== '') ? json_decode($body, true) : null;
                if (is_array($data)) {
                    if (isset($data['data']) && is_array($data['data'])) $data = $data['data'];
                    elseif (isset($data['result']) && is_array($data['result'])) $data = $data['result'];
                    $razon = $data['nombre_o_razon_social']
                        ?? $data['razonSocial']
                        ?? $data['razon_social']
                        ?? $data['RazonSocial']
                        ?? $data['RAZONSOCIAL']
                        ?? $data['vRazonSocial']
                        ?? '';
                    $direc = $data['domicilio_fiscal']
                        ?? $data['direccion']
                        ?? $data['Direccion']
                        ?? $data['DomicilioFiscal']
                        ?? $data['domicilioFiscal']
                        ?? $data['vDireccion']
                        ?? '';
                    $ubigeo = $data['ubigeo'] ?? $data['Ubigeo'] ?? $data['vUbigeo'] ?? '';
                    $estado = $data['contribuyente_estado'] ?? $data['estado'] ?? $data['Estado'] ?? '';
                    $cond   = $data['contribuyente_condicion'] ?? $data['condicion'] ?? $data['Condicion'] ?? '';
                    $tipo   = $data['contribuyente_tipo'] ?? $data['tipo'] ?? $data['Tipo'] ?? '';
                    $mensaje = $data['MensajeRuc'] ?? $data['mensaje'] ?? '';
                    $rawSucc = strtolower(strval($data['success'] ?? ''));
                    $success = ($rawSucc === 'true' || $rawSucc === '1');
                    // Si la API JSON no envió 'success' pero sí razón social, asumir success.
                    if (!$success && trim(strval($razon)) !== '') $success = true;
                }
            }

            $resp['nombre_o_razon_social'] = trim(strval($razon));
            $resp['domicilio_fiscal']      = trim(strval($direc));
            $resp['ubigeo']                = trim(strval($ubigeo));
            $resp['estado']                = trim(strval($estado));
            $resp['condicion']             = trim(strval($cond));
            $resp['tipo']                  = trim(strval($tipo));
            $resp['success']               = $success;
            $resp['mensaje']               = trim(strval($mensaje));
            // Fallback robusto: si la API envuelve un 'success=false' pero igual
            // devolvió razón social no vacía, considerarlo encontrado.
            if (!$resp['success'] && $resp['nombre_o_razon_social'] !== '') {
                $resp['success'] = true;
            }
        }
        jsonResponse(array('d' => $resp)); break;
    case 'Guardar':
        $input = getJsonInput();
        // El JS envía: { ConfigGeneral: [{ ccod_clibol, ..., ilogo }], operacion: "nuevo"|"editar" }
        $data = (isset($input['ConfigGeneral']) && is_array($input['ConfigGeneral']))
                ? $input['ConfigGeneral'][0] : $input;
        $operacion = $input['operacion'] ?? 'editar';

        // Decodificar logo base64 a bytes binarios (si se envió)
        $logoBase64 = $data['ilogo'] ?? '';
        $logoBytes = (!empty($logoBase64)) ? base64_decode($logoBase64) : null;

        $conn = Database::getTenantConnection($o);
        if (!$conn) { jsonResponse(array('d' => false)); break; }

        if ($operacion === 'nuevo') {
            // SP: webDatpos_insertarConfigGeneral (sin @ilogo)
            $sql = "EXEC webDatpos_insertarConfigGeneral @ccod_clibol=?, @coper_ingreso=?, @coper_salida=?, "
                 . "@ccod_cia=?, @ctipo_flag_ingreso=?, @ctipo_flag_salida=?, "
                 . "@nigv=?, @nisc=?, @nmonto_maxboleta=?, @ccod_usuario=?";
            $params = array(
                $data['ccod_clibol'] ?? '',
                $data['ccod_OperIngreso'] ?? '',
                $data['ccod_OperSalida'] ?? '',
                $o->ccod_empresa,
                $data['ctipo_OperIngreso'] ?? '',
                $data['ctipo_OperSalida'] ?? '',
                floatval($data['nigv'] ?? 0),
                floatval($data['nisc'] ?? 0),
                floatval($data['nmonto_maxboleta'] ?? 0),
                $o->ccod_usuario
            );
            $stmt = sqlsrv_query($conn, $sql, $params);
            $resp = ($stmt !== false);
            if ($stmt) sqlsrv_free_stmt($stmt);

            // Si hay logo, actualizarlo por separado
            if ($resp && $logoBytes !== null) {
                $sqlLogo = "UPDATE ConfigGeneral SET ilogo=? WHERE ccod_cia=?";
                $paramsLogo = array(
                    array($logoBytes, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max')),
                    $o->ccod_empresa
                );
                $stmtLogo = sqlsrv_query($conn, $sqlLogo, $paramsLogo);
                if ($stmtLogo) sqlsrv_free_stmt($stmtLogo);
            }
        } else {
            // SP: webDatpos_editarConfigGeneral (incluye @ilogo VARBINARY)
            $sql = "EXEC webDatpos_editarConfigGeneral @ccod_clibol=?, @coper_ingreso=?, @coper_salida=?, "
                 . "@ccod_cia=?, @ctipo_flag_ingreso=?, @ctipo_flag_salida=?, "
                 . "@nigv=?, @nisc=?, @nmonto_maxboleta=?, @ccod_usuario=?, @ilogo=?";
            $params = array(
                $data['ccod_clibol'] ?? '',
                $data['ccod_OperIngreso'] ?? '',
                $data['ccod_OperSalida'] ?? '',
                $o->ccod_empresa,
                $data['ctipo_OperIngreso'] ?? '',
                $data['ctipo_OperSalida'] ?? '',
                floatval($data['nigv'] ?? 0),
                floatval($data['nisc'] ?? 0),
                floatval($data['nmonto_maxboleta'] ?? 0),
                $o->ccod_usuario,
                array($logoBytes, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STRING(SQLSRV_ENC_BINARY), SQLSRV_SQLTYPE_VARBINARY('max'))
            );
            $stmt = sqlsrv_query($conn, $sql, $params);
            $resp = ($stmt !== false);
            if (!$stmt) error_log("Error Guardar ConfigGeneral: " . print_r(sqlsrv_errors(), true));
            if ($stmt) sqlsrv_free_stmt($stmt);
        }

        sqlsrv_close($conn);
        jsonResponse(array('d' => $resp));
        break;
    case 'Eliminar':
        $input = getJsonInput();
        $resp = Database::executeStoredTenant('webDatpos__eliminarConfigGenral', array('@ccod_cia' => $o->ccod_empresa), $o);
        jsonResponse(array('d' => $resp));
        break;
    default: jsonResponse(array('d' => array()));
}
?>
