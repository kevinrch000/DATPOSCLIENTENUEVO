<?php
/**
 * DatPOS - Verificacion de Sesion con JWT
 * Reemplaza: Session("objBEUsuario") Is Nothing → Redirect
 * 
 * Flujo de autenticacion:
 * 1. Login (LogOn.php) valida credenciales y emite JWT en cookie HttpOnly
 * 2. En cada request, auth.php lee el JWT de la cookie
 * 3. Si el JWT es valido, reconstruye $_SESSION['objBEUsuario'] desde el payload
 * 4. Si el JWT esta expirado/invalido, redirige al login
 * 
 * IMPORTANTE: Carga BEUsuario.php ANTES de session_start()
 * para que PHP pueda deserializar el objeto de sesion correctamente.
 * 
 * Incluir al inicio de cada pagina protegida:
 *   require_once __DIR__ . '/../includes/auth.php';
 */

// Cargar dependencias
require_once __DIR__ . '/../BE/BEUsuario.php';
require_once __DIR__ . '/../config/jwt_config.php';
require_once __DIR__ . '/jwt.php';

// Inicializar JWT con la clave secreta
JWT::init(JWT_SECRET);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================================
// Intentar restaurar sesion desde JWT si no existe en $_SESSION
// ============================================================
if (!isset($_SESSION['objBEUsuario']) || $_SESSION['objBEUsuario'] === null) {
    $token = $_COOKIE[JWT_COOKIE_NAME] ?? null;
    if ($token) {
        $payload = JWT::decode($token);
        if ($payload !== null && isset($payload['usr'])) {
            // Reconstruir BEUsuario desde el payload JWT
            $_SESSION['objBEUsuario'] = _rebuildUsuarioFromJwt($payload);
        }
    }
}

/**
 * Verificar si el usuario esta autenticado.
 * Si no, redirige al login.
 */
function requireAuth() {
    if (!isset($_SESSION['objBEUsuario']) || $_SESSION['objBEUsuario'] === null) {
        // Limpiar cookie invalida si existe
        if (isset($_COOKIE[JWT_COOKIE_NAME])) {
            _clearJwtCookie();
        }
        header('Location: /pages/migcliente/LogOn.php');
        exit;
    }
}

/**
 * Obtener el objeto usuario de la sesion.
 * Equivale a: Session("objBEUsuario")
 */
function getUsuarioSesion() {
    return isset($_SESSION['objBEUsuario']) ? $_SESSION['objBEUsuario'] : null;
}

/**
 * Cerrar sesion: destruir session + eliminar cookie JWT.
 */
function cerrarSesion() {
    _clearJwtCookie();
    session_destroy();
    header('Location: /pages/migcliente/LogOn.php');
    exit;
}

/**
 * Verificar si el usuario ya esta logueado (para el login).
 * Si ya esta logueado, redirige al Home.
 */
function redirectIfAuthenticated() {
    if (isset($_SESSION['objBEUsuario']) && $_SESSION['objBEUsuario'] !== null) {
        header('Location: /pages/Interfaces/Home.php');
        exit;
    }
}

/**
 * Emitir un JWT y guardarlo en cookie HttpOnly.
 * Llamado desde LogOn.php tras login exitoso.
 *
 * @param BEUsuario $objBE  Objeto usuario autenticado
 * @return string           Token JWT generado
 */
function emitirJwt($objBE) {
    $payload = [
        'usr'  => $objBE->ccod_usuario,
        'name' => $objBE->cdsc_usuario,
        'emp'  => $objBE->ccod_empresa,
        'db'   => $objBE->cnombre_bd,
        'srv'  => $objBE->cnomser,
        'desc' => $objBE->cdescripcion,
        'ruc'  => $objBE->cnum_tribu,
        'te'   => $objBE->ntienda_extra,
        'ue'   => $objBE->nusuario_extra,
        'tar'  => $objBE->ctarifas,
        'mon'  => $objBE->cnombre_moneda,
        'sym'  => $objBE->csimbolo_moneda,
        'dom'  => $objBE->cdomicilio,
        'prv'  => $objBE->cprovincia,
        'dst'  => $objBE->cdistrito,
        'dpt'  => $objBE->cdepartamento,
        'fac'  => $objBE->ctip_facturador,
        'fvn'  => $objBE->dfch_vencimiento,
        'cem'  => $objBE->ccod_cliente_emis,
        'tkn'  => $objBE->ctoken,
        'rm'   => $objBE->rolMaster,
        'ict'  => $objBE->id_ctusu,
        // Datos del tenant (consultaUsuario)
        'dir'  => $objBE->cdirec,
        'rol'  => $objBE->cdsc_rol,
        'est'  => $objBE->estado,
        'tid'  => $objBE->ccod_tiend,
        'alm'  => $objBE->ccod_almacen,
        'caj'  => $objBE->ccod_caja,
        'idr'  => $objBE->id_rol,
        'dti'  => $objBE->cdirc_tienda,
        'pti'  => $objBE->cprovincia_tienda,
        'dti2' => $objBE->cdistrito_tienda,
        'dpti' => $objBE->cdepartamento_tienda,
        'tti'  => $objBE->ctelf_tienda,
        'nti'  => $objBE->cdsc_tienda,
    ];

    $token = JWT::encode($payload, JWT_TTL);
    _setJwtCookie($token);
    return $token;
}

/**
 * Renovar el JWT actual (extender expiracion).
 *
 * @return bool true si se renovo exitosamente
 */
function renovarJwt() {
    $token = $_COOKIE[JWT_COOKIE_NAME] ?? null;
    if (!$token) return false;

    $newToken = JWT::refresh($token, JWT_TTL);
    if ($newToken === null) return false;

    _setJwtCookie($newToken);
    return true;
}

// ============================================================
// Funciones internas
// ============================================================

/**
 * Reconstruir BEUsuario desde un payload JWT decodificado.
 */
function _rebuildUsuarioFromJwt(array $p) {
    $obj = new BEUsuario();
    $obj->ccod_usuario       = $p['usr']  ?? '';
    $obj->cdsc_usuario       = $p['name'] ?? '';
    $obj->ccod_empresa       = $p['emp']  ?? '';
    // FIX: si el JWT no trae db/srv (sesiones antiguas), usar env vars como fallback
    $db  = $p['db']  ?? '';
    $srv = $p['srv'] ?? '';
    if ($db  === '') $db  = getenv('DATPOS_TENANT_DATABASE') ?: '';
    if ($srv === '') $srv = getenv('DATPOS_TENANT_SERVER')   ?: '';
    $obj->cnombre_bd         = $db;
    $obj->cnomser            = $srv;
    $obj->cdescripcion       = $p['desc'] ?? '';
    $obj->cnum_tribu         = $p['ruc']  ?? '';
    $obj->ntienda_extra      = intval($p['te'] ?? 0);
    $obj->nusuario_extra     = intval($p['ue'] ?? 0);
    $obj->ctarifas           = $p['tar']  ?? '';
    $obj->cnombre_moneda     = $p['mon']  ?? '';
    $obj->csimbolo_moneda    = $p['sym']  ?? '';
    $obj->cdomicilio         = $p['dom']  ?? '';
    $obj->cprovincia         = $p['prv']  ?? '';
    $obj->cdistrito          = $p['dst']  ?? '';
    $obj->cdepartamento      = $p['dpt']  ?? '';
    $obj->ctip_facturador    = $p['fac']  ?? '';
    $obj->dfch_vencimiento   = $p['fvn']  ?? '';
    $obj->ccod_cliente_emis  = $p['cem']  ?? '';
    $obj->ctoken             = $p['tkn']  ?? '';
    $obj->rolMaster          = intval($p['rm'] ?? 0);
    $obj->id_ctusu           = intval($p['ict'] ?? 0);
    // Datos del tenant
    $obj->cdirec             = $p['dir']  ?? '';
    $obj->cdsc_rol           = $p['rol']  ?? '';
    $obj->estado             = intval($p['est'] ?? 0);
    $obj->ccod_tiend         = $p['tid']  ?? '';
    $obj->ccod_almacen       = $p['alm']  ?? '';
    $obj->ccod_caja          = $p['caj']  ?? '';
    $obj->id_rol             = intval($p['idr'] ?? 0);
    $obj->cdirc_tienda       = $p['dti']  ?? '';
    $obj->cprovincia_tienda  = $p['pti']  ?? '';
    $obj->cdistrito_tienda   = $p['dti2'] ?? '';
    $obj->cdepartamento_tienda = $p['dpti'] ?? '';
    $obj->ctelf_tienda       = $p['tti']  ?? '';
    $obj->cdsc_tienda        = $p['nti']  ?? '';
    return $obj;
}

/**
 * Setear la cookie JWT con opciones seguras.
 */
function _setJwtCookie($token) {
    $options = [
        'expires'  => time() + JWT_TTL,
        'path'     => '/',
        'secure'   => JWT_COOKIE_SECURE,
        'httponly'  => JWT_COOKIE_HTTPONLY,
        'samesite' => JWT_COOKIE_SAMESITE,
    ];
    setcookie(JWT_COOKIE_NAME, $token, $options);
    // Tambien setear en $_COOKIE para la request actual
    $_COOKIE[JWT_COOKIE_NAME] = $token;
}

/**
 * Eliminar la cookie JWT.
 */
function _clearJwtCookie() {
    $options = [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => JWT_COOKIE_SECURE,
        'httponly'  => JWT_COOKIE_HTTPONLY,
        'samesite' => JWT_COOKIE_SAMESITE,
    ];
    setcookie(JWT_COOKIE_NAME, '', $options);
    unset($_COOKIE[JWT_COOKIE_NAME]);
}
?>
