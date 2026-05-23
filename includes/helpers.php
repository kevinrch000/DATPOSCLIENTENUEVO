<?php
/**
 * DatPOS - Funciones Helper
 * Funciones utilitarias usadas en todo el proyecto
 */

// Si la request es API (XHR), suprimir display_errors para que warnings/notices
// no rompan los responses JSON. Errores siguen en error_log.
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    @ini_set('display_errors', '0');
    @ini_set('html_errors', '0');
}

/**
 * Obtener la ruta base del proyecto PHP
 * Si usas php -S localhost:8080 desde DatPOS_PHP/, retorna ''
 * Si despliegas en un subdirectorio, cambia a '/DatPOS_PHP'
 */
function basePath() {
    return '';
}

/**
 * Obtener ruta a assets (JS, CSS, imágenes) del proyecto original
 * Los assets se referencian desde el proyecto ASP.NET original
 */
function assetPath($path = '') {
    return basePath() . '/assets' . $path;
}

/**
 * Escapar HTML para prevenir XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Retornar JSON y terminar ejecución
 * Equivale a los WebMethod que retornan JSON
 */
function jsonResponse($data) {
    // Descartar cualquier salida previa (warnings/notices) que romperia el JSON.
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Leer input JSON del body (para AJAX POST)
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    if (is_array($data)) return $data;

    $data = array();
    if (preg_match_all('/([A-Za-z_][A-Za-z0-9_]*)\s*:\s*(?:"([^"]*)"|\'([^\']*)\'|([^,}\s]+))/', $input, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $m) {
            $v1 = $m[2] ?? '';
            $v2 = $m[3] ?? '';
            $v3 = $m[4] ?? '';
            $data[$m[1]] = $v1 !== '' ? $v1 : ($v2 !== '' ? $v2 : $v3);
        }
    }
    return $data;
}

/**
 * Verificar si la request es AJAX
 */
function isAjax() {
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Formatear valor de BD que puede ser NULL
 * Equivale a: If(IsDBNull(fila.ItemArray(N)), default, fila.ItemArray(N))
 */
function dbVal($value, $default = '') {
    return ($value === null || $value === false) ? $default : $value;
}

/**
 * Convertir fecha dd/mm/yyyy a yyyy-mm-dd (formato ISO/SQL).
 * El datepicker JS envía dd/mm/yyyy pero SET DATEFORMAT ymd necesita ISO.
 * Si la fecha ya es ISO o no se puede parsear, se devuelve tal cual.
 */
function fechaToISO($fecha) {
    if (empty($fecha)) return '';
    // dd/mm/yyyy
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $fecha, $m)) {
        return $m[3] . '-' . str_pad($m[2], 2, '0', STR_PAD_LEFT) . '-' . str_pad($m[1], 2, '0', STR_PAD_LEFT);
    }
    return $fecha;
}

/**
 * Convertir imagen binaria a base64
 * Equivale a: Convert.ToBase64String(fila.ItemArray(N))
 */
function imageToBase64($binaryData) {
    if (empty($binaryData)) return '';
    return base64_encode($binaryData);
}
?>
