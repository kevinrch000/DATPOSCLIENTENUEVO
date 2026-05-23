<?php
require_once __DIR__ . '/../config/dni_provider.php';

function emptyDniLookupResponse($dni = '', $message = '')
{
    return array(
        'dni' => $dni,
        'nombres' => '',
        'apellido_paterno' => '',
        'apellido_materno' => '',
        'nombre_completo' => '',
        'direccion' => '',
        'ubigeo' => '',
        'departamento' => '',
        'provincia' => '',
        'distrito' => '',
        'genero' => '',
        'fecha_nacimiento' => '',
        'codigo_verificacion' => '',
        'success' => false,
        'mensaje' => $message,
    );
}

function lookupDni($dni)
{
    $dni = trim(strval($dni));
    if (!preg_match('/^\d{8}$/', $dni)) {
        return emptyDniLookupResponse($dni, 'El DNI debe tener 8 dígitos.');
    }

    // ── Caché: devolver resultado almacenado si existe y no ha expirado ──
    $cached = dniCacheGet($dni);
    if ($cached !== null) {
        return $cached;
    }

    $config = dniProviderConfig();
    $provider = strtolower(trim(strval($config['provider'] ?? '')));
    $result = null;
    if ($provider === 'consultadni') {
        $result = lookupDniConsultaDni($dni, $config);
    } elseif ($provider === 'consultas_peru') {
        $result = lookupDniConsultasPeru($dni, $config);
    } else {
        return emptyDniLookupResponse($dni, 'Proveedor DNI no configurado.');
    }

    // ── Guardar en caché solo si la consulta fue exitosa ──
    if ($result && !empty($result['success'])) {
        dniCacheSet($dni, $result);
    }

    return $result;
}

/**
 * Directorio de caché para consultas DNI.
 * Se crea automáticamente en /tmp/datpos_dni_cache/ o dentro del proyecto.
 */
function dniCacheDir()
{
    $dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'datpos_dni_cache';
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    return $dir;
}

/**
 * Lee un resultado cacheado. Devuelve null si no existe o expiró.
 * TTL por defecto: 30 días (los datos de DNI rara vez cambian).
 */
function dniCacheGet($dni, $ttlSeconds = 2592000)
{
    $file = dniCacheDir() . DIRECTORY_SEPARATOR . $dni . '.json';
    if (!file_exists($file)) {
        return null;
    }
    $age = time() - filemtime($file);
    if ($age > $ttlSeconds) {
        @unlink($file);
        return null;
    }
    $content = @file_get_contents($file);
    if ($content === false) {
        return null;
    }
    $data = json_decode($content, true);
    if (!is_array($data) || empty($data['success'])) {
        @unlink($file);
        return null;
    }
    $data['_from_cache'] = true;
    return $data;
}

/**
 * Guarda un resultado exitoso en caché como archivo JSON.
 */
function dniCacheSet($dni, $result)
{
    $file = dniCacheDir() . DIRECTORY_SEPARATOR . $dni . '.json';
    $json = json_encode($result, JSON_UNESCAPED_UNICODE);
    @file_put_contents($file, $json, LOCK_EX);
}


function lookupDniConsultaDni($dni, $config)
{
    $url = trim(strval($config['base_url'] ?? ''));
    $token = trim(strval($config['token'] ?? ''));
    $timeout = intval($config['timeout'] ?? 8);

    if ($url === '' || $token === '' || $token === 'PEGA_TU_TOKEN_AQUI') {
        return emptyDniLookupResponse($dni, 'Token de ConsultaDNI no configurado.');
    }

    $separator = (strpos($url, '?') === false) ? '?' : '&';
    $requestUrl = $url . $separator . 'dni=' . urlencode($dni) . '&api_key=' . urlencode($token);
    $result = httpJsonGet($requestUrl, $timeout);
    if ($result['body'] === '') {
        return emptyDniLookupResponse($dni, buildHttpErrorMessage('ConsultaDNI', $result));
    }

    $data = json_decode($result['body'], true);
    if (!is_array($data)) {
        return emptyDniLookupResponse($dni, 'Respuesta inválida de ConsultaDNI. HTTP ' . $result['status']);
    }

    return normalizeDniResponse($dni, $data);
}

function lookupDniConsultasPeru($dni, $config)
{
    $url = trim(strval($config['base_url'] ?? ''));
    $token = trim(strval($config['token'] ?? ''));
    $timeout = intval($config['timeout'] ?? 8);

    if ($url === '' || $token === '' || $token === 'PEGA_TU_TOKEN_AQUI') {
        return emptyDniLookupResponse($dni, 'Token de consulta DNI no configurado.');
    }

    $payload = json_encode(array(
        'token' => $token,
        'type_document' => 'dni',
        'document_number' => $dni,
    ));

    $result = httpJsonPost($url, $payload, $timeout);
    if ($result['body'] === '') {
        return emptyDniLookupResponse($dni, buildHttpErrorMessage('servicio DNI', $result));
    }

    $data = json_decode($result['body'], true);
    if (!is_array($data)) {
        return emptyDniLookupResponse($dni, 'Respuesta inválida del servicio DNI. HTTP ' . $result['status']);
    }

    return normalizeDniResponse($dni, $data);
}

function httpJsonPost($url, $payload, $timeout)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(2, max(1, $timeout)));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, defined('CURL_HTTP_VERSION_2_0') ? CURL_HTTP_VERSION_2_0 : CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/json',
        ));
        $body = curl_exec($ch);
        if ($body === false) {
            error_log('[DNI Lookup] curl error: ' . curl_error($ch));
            $body = '';
        }
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $error = curl_error($ch);
        $errno = intval(curl_errno($ch));
        curl_close($ch);
        if ($body !== '') {
            return array('body' => strval($body), 'status' => $status, 'error' => $error, 'errno' => $errno);
        }
        $fallback = httpJsonStreamRequest($url, 'POST', $payload, $timeout);
        if ($fallback['body'] !== '') return $fallback;
        return mergeHttpFailures($status, $error, $errno, $fallback);
    }

    return httpJsonStreamRequest($url, 'POST', $payload, $timeout);
}

function httpJsonGet($url, $timeout)
{
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(2, max(1, $timeout)));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 300);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, defined('CURL_HTTP_VERSION_2_0') ? CURL_HTTP_VERSION_2_0 : CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));
        $body = curl_exec($ch);
        if ($body === false) {
            error_log('[DNI Lookup] curl error: ' . curl_error($ch));
            $body = '';
        }
        $status = intval(curl_getinfo($ch, CURLINFO_HTTP_CODE));
        $error = curl_error($ch);
        $errno = intval(curl_errno($ch));
        curl_close($ch);
        if ($body !== '') {
            return array('body' => strval($body), 'status' => $status, 'error' => $error, 'errno' => $errno);
        }
        $fallback = httpJsonStreamRequest($url, 'GET', '', $timeout);
        if ($fallback['body'] !== '') return $fallback;
        return mergeHttpFailures($status, $error, $errno, $fallback);
    }

    return httpJsonStreamRequest($url, 'GET', '', $timeout);
}

function httpJsonStreamRequest($url, $method, $payload, $timeout)
{
    $header = "Accept: application/json\r\n";
    if ($method === 'POST') {
        $header = "Content-Type: application/json\r\n" . $header;
    }
    $context = stream_context_create(array(
        'http' => array(
            'method' => $method,
            'header' => $header,
            'content' => $payload,
            'timeout' => $timeout,
            'ignore_errors' => true,
        ),
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
        ),
    ));
    $lastError = '';
    set_error_handler(function ($severity, $message) use (&$lastError) {
        $lastError = $message;
    });
    $body = file_get_contents($url, false, $context);
    restore_error_handler();
    return array(
        'body' => ($body === false) ? '' : strval($body),
        'status' => statusFromHeaders($http_response_header ?? array()),
        'error' => ($body === false) ? ($lastError ?: 'file_get_contents no pudo conectar') : '',
        'errno' => 0,
    );
}

function mergeHttpFailures($curlStatus, $curlError, $curlErrno, $fallback)
{
    $fallbackError = trim(strval($fallback['error'] ?? ''));
    $error = trim(strval($curlError));
    if ($error !== '' && $fallbackError !== '') {
        $error .= ' | fallback: ' . $fallbackError;
    } elseif ($fallbackError !== '') {
        $error = $fallbackError;
    }
    return array(
        'body' => '',
        'status' => $curlStatus > 0 ? $curlStatus : intval($fallback['status'] ?? 0),
        'error' => $error,
        'errno' => intval($curlErrno),
    );
}

function buildHttpErrorMessage($serviceName, $result)
{
    $status = intval($result['status'] ?? 0);
    $error = trim(strval($result['error'] ?? ''));
    $errno = intval($result['errno'] ?? 0);

    if ($status === 401 || $status === 403) {
        return 'Token inválido, sin créditos o sin permiso en ' . $serviceName . '. HTTP ' . $status . '.';
    }
    if ($status === 404) {
        return 'Endpoint no encontrado en ' . $serviceName . '. HTTP 404.';
    }
    if ($status >= 500) {
        return $serviceName . ' respondió con error HTTP ' . $status . '.';
    }
    if ($error !== '') {
        return 'No se obtuvo respuesta de ' . $serviceName . '. ' . classifyHttpFailure($error, $errno);
    }
    if ($status > 0) {
        return 'No se obtuvo respuesta de ' . $serviceName . '. HTTP ' . $status . '.';
    }
    return 'No se obtuvo respuesta de ' . $serviceName . '. Verifica conexión HTTPS/cURL del servidor.';
}

function classifyHttpFailure($error, $errno)
{
    $lower = strtolower($error);
    if ($errno === 6 || strpos($lower, 'could not resolve') !== false || strpos($lower, 'getaddrinfo') !== false) {
        return 'DNS no resuelve el dominio. Detalle: ' . $error;
    }
    if ($errno === 7 || strpos($lower, 'connection refused') !== false || strpos($lower, 'failed to connect') !== false) {
        return 'No hay salida de red o el firewall/proxy bloquea la conexión. Detalle: ' . $error;
    }
    if ($errno === 28 || strpos($lower, 'timed out') !== false) {
        return 'Timeout conectando al proveedor. Detalle: ' . $error;
    }
    if (strpos($lower, 'openssl') !== false || strpos($lower, 'ssl') !== false || strpos($lower, 'certificate') !== false) {
        return 'Problema SSL/OpenSSL en PHP. Detalle: ' . $error;
    }
    if (strpos($lower, 'no suitable wrapper') !== false || strpos($lower, 'https') !== false) {
        return 'PHP no tiene wrapper HTTPS/OpenSSL habilitado. Detalle: ' . $error;
    }
    return 'cURL ' . $errno . ': ' . $error;
}

function statusFromHeaders($headers)
{
    foreach ($headers as $header) {
        if (preg_match('/^HTTP\/\S+\s+(\d+)/', $header, $matches)) {
            return intval($matches[1]);
        }
    }
    return 0;
}

function normalizeDniResponse($dni, $data)
{
    if (isset($data['resultado']) && is_array($data['resultado'])) {
        $source = $data['resultado'];
    } elseif (isset($data['data']) && is_array($data['data'])) {
        $source = $data['data'];
    } else {
        $source = $data;
    }
    $success = filter_var($data['success'] ?? $data['estado'] ?? false, FILTER_VALIDATE_BOOLEAN);

    $nombres = pickFirst($source, array('name', 'nombres', 'nombres_completos'));
    $apellidoPaterno = pickFirst($source, array('apellido_paterno', 'paternal_surname', 'paterno'));
    $apellidoMaterno = pickFirst($source, array('apellido_materno', 'maternal_surname', 'materno'));
    $apellidos = trim(pickFirst($source, array('surname', 'apellidos')));
    if (($apellidoPaterno === '' || $apellidoMaterno === '') && $apellidos !== '') {
        $parts = preg_split('/\s+/', $apellidos);
        if ($apellidoPaterno === '') $apellidoPaterno = $parts[0] ?? '';
        if ($apellidoMaterno === '') $apellidoMaterno = implode(' ', array_slice($parts, 1));
    }

    $fullName = pickFirst($source, array('full_name', 'nombre_completo', 'nombre', 'razon_social'));
    if ($fullName === '') {
        $fullName = trim($nombres . ' ' . $apellidoPaterno . ' ' . $apellidoMaterno);
    }

    if (!$success && $fullName !== '') $success = true;

    $message = strval($data['message'] ?? $data['mensaje'] ?? '');
    return array(
        'dni' => pickFirst($source, array('number', 'dni', 'document_number', 'id', 'numero')) ?: $dni,
        'nombres' => $nombres,
        'apellido_paterno' => $apellidoPaterno,
        'apellido_materno' => $apellidoMaterno,
        'nombre_completo' => $fullName,
        'direccion' => pickFirst($source, array('address', 'direccion')),
        'ubigeo' => pickFirst($source, array('ubigeo')),
        'departamento' => pickFirst($source, array('department', 'departamento')),
        'provincia' => pickFirst($source, array('province', 'provincia')),
        'distrito' => pickFirst($source, array('district', 'distrito')),
        'genero' => pickFirst($source, array('genero', 'gender', 'sexo')),
        'fecha_nacimiento' => pickFirst($source, array('fecha_nacimiento', 'date_of_birth', 'nacimiento')),
        'codigo_verificacion' => pickFirst($source, array('codigo_verificacion', 'verification_code', 'cod_verifica')),
        'success' => $success,
        'mensaje' => $message,
    );
}

function pickFirst($source, $keys)
{
    foreach ($keys as $key) {
        if (isset($source[$key]) && trim(strval($source[$key])) !== '') {
            return trim(strval($source[$key]));
        }
    }
    return '';
}
?>
