<?php
/**
 * DatPOS - Pagina de Login
 * Reemplaza: migcliente/LogOn.aspx + LogOn.aspx.vb
 * 
 * Autenticacion con JWT + contrasenas bcrypt (con migracion desde MD5).
 * - Las credenciales se verifican en PHP (no en el SP)
 * - Se emite un JWT HttpOnly como token de sesion
 * - Las contrasenas MD5 legacy se migran automaticamente a bcrypt
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../BL/BLUsuario.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/password_helper.php';

// Si ya esta logueado, ir al Home
redirectIfAuthenticated();

$error = '';

// Procesar formulario de login (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['UserName'] ?? '');
    $password = trim($_POST['Password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Ingrese usuario y contrasena.';
    } else {
        $blUsuario = new BLUsuario();

        // 1. Buscar usuario en BD Admin por username (sp_buscarusuario_login)
        //    Retorna datos del usuario SIN filtrar por contrasena
        $listDT = $blUsuario->buscarUsuarioLogin($username);

        if (count($listDT) > 0) {
            $fila = $listDT[0];

            // Columnas del SP sp_buscarusuario_login:
            // [0]id_ctusu [1]ccod_usuario [2]cdsc_usuario [3]rolMaster [4]ccod_empresa
            // [5]cnombre_bd [6]cnomser [7]cdescripcion [8]cnum_tribu [9]ntienda_extra
            // [10]nusuario_extra [11]ctarifas [12]cnombre_moneda [13]csimbolo_moneda
            // [14]cdomicilio [15]cprovincia [16]cdistrito [17]cdepartamento
            // [18]ctip_facturador [19]dfch_vencimiento [20]estado [21]ccod_cliente_emis
            // [22]ctoken [23]cpassw (MD5) [24]cpassw_bcrypt

            // Verificar si esta habilitado (columna 20)
            if (isset($fila[20]) && $fila[20] === 'Habilitado') {

                // Obtener hashes de contrasena
                $md5Hash    = strval($fila[23] ?? '');
                $bcryptHash = strval($fila[24] ?? '');

                // Verificar contrasena (bcrypt o MD5 legacy con migracion)
                $check = PasswordHelper::verifyWithMigration($password, $md5Hash, $bcryptHash ?: null);

                if ($check['valid']) {

                    // Migrar contrasena a bcrypt si es necesario
                    // Tambien reemplaza cpassw con MD5 (elimina texto plano)
                    if ($check['needs_migration']) {
                        $newBcrypt = PasswordHelper::hash($password);
                        $newMd5    = md5($password);
                        $blUsuario->migrarPasswordBcrypt(strval($fila[1]), $newBcrypt, $newMd5);
                    }

                    $objBE = new BEUsuario();

                    // Lectura del Login (Admin)
                    $objBE->id_ctusu          = intval($fila[0]);
                    $objBE->ccod_usuario      = strval($fila[1]);
                    $objBE->cdsc_usuario      = strval($fila[2]);
                    $objBE->rolMaster         = intval($fila[3]);
                    $objBE->ccod_empresa      = strval($fila[4]);
                    // FIX: si la BD Admin devuelve cnombre_bd/cnomser vacíos,
                    // usar las variables de entorno del servidor como fallback.
                    $cnombre_bd_login = strval($fila[5] ?? '');
                    $cnomser_login    = strval($fila[6] ?? '');
                    if ($cnombre_bd_login === '') $cnombre_bd_login = getenv('DATPOS_TENANT_DATABASE') ?: '';
                    if ($cnomser_login    === '') $cnomser_login    = getenv('DATPOS_TENANT_SERVER')   ?: '';
                    $objBE->cnombre_bd        = $cnombre_bd_login;
                    $objBE->cnomser           = $cnomser_login;
                    $objBE->cdescripcion      = strval($fila[7]);
                    $objBE->cnum_tribu        = strval($fila[8] ?? '');
                    $objBE->ntienda_extra     = intval($fila[9] ?? 0);
                    $objBE->nusuario_extra    = intval($fila[10] ?? 0);
                    $objBE->ctarifas          = strval($fila[11] ?? '');
                    $objBE->cnombre_moneda    = strval($fila[12] ?? '');
                    $objBE->csimbolo_moneda   = strval($fila[13] ?? '');
                    $objBE->cdomicilio        = strval($fila[14] ?? '');
                    $objBE->cprovincia        = strval($fila[15] ?? '');
                    $objBE->cdistrito         = strval($fila[16] ?? '');
                    $objBE->cdepartamento     = strval($fila[17] ?? '');
                    $objBE->ctip_facturador   = strval($fila[18] ?? '');
                    $objBE->dfch_vencimiento  = ($fila[19] !== null) ? strval($fila[19]) : '';
                    $objBE->ccod_cliente_emis = strval($fila[21] ?? '');
                    $objBE->ctoken            = strval($fila[22] ?? '');

                    // 2. Lectura del Usuario en BD Tenant (webDatpos_consultaUsuario)
                    $listDT_U = $blUsuario->consultarUsuario($objBE->ccod_usuario, $objBE);

                    if (count($listDT_U) > 0) {
                        $fila_U = $listDT_U[0];

                        $objBE->ccod_usuario       = strval($fila_U[0] ?? '');
                        $objBE->cdsc_usuario       = strval($fila_U[1] ?? '');
                        $objBE->cdirec             = strval($fila_U[2] ?? '');
                        $objBE->cdsc_rol           = strval($fila_U[3] ?? '');
                        $objBE->estado             = intval($fila_U[4] ?? 0);
                        $objBE->ccod_tiend         = strval($fila_U[5] ?? '');
                        $objBE->ccod_almacen       = strval($fila_U[6] ?? '');
                        $objBE->ccod_caja          = strval($fila_U[7] ?? '');
                        $objBE->cpassw             = strval($fila_U[8] ?? '');
                        $objBE->id_rol             = intval($fila_U[9] ?? 0);
                        $objBE->cdirc_tienda       = strval($fila_U[12] ?? '');
                        $objBE->cprovincia_tienda  = strval($fila_U[13] ?? '');
                        $objBE->cdistrito_tienda   = strval($fila_U[14] ?? '');
                        $objBE->cdepartamento_tienda = strval($fila_U[15] ?? '');
                        $objBE->ctelf_tienda       = strval($fila_U[16] ?? '');
                        $objBE->cdsc_tienda        = strval($fila_U[17] ?? '');
                    }

                    // Guardar en sesion y emitir JWT
                    $_SESSION['objBEUsuario'] = $objBE;
                    emitirJwt($objBE);
                    header('Location: ' . basePath() . '/pages/Interfaces/Home.php');
                    exit;

                } else {
                    $error = 'Usuario o Contrasena incorrecta.';
                }

            } else {
                $error = 'Tu suscripcion ha expirado. Contacte con soporte para mayor informacion.';
            }
        } else {
            $error = 'Usuario o Contrasena incorrecta.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <title>DATPOS Cliente</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Portal de Cliente DATPOS - Sistema de Punto de Venta">
    <link rel="shortcut icon" href="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" type="image/x-icon">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    
    <style>
        /* ================================================================
           RESET & BASE — Esencia del login original + toques modernos
           ================================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body, html {
            height: 100%;
            font-family: 'Inter', 'Open Sans', sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        /* ================================================================
           LOGIN CONTAINER — Fondo con gradiente sutil
           ================================================================ */
        .container-login {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: linear-gradient(135deg, #0b1220 0%, #111827 45%, #1f2937 100%);
        }

        /* ================================================================
           LOGIN CARD — Misma forma que el original + sombra mejorada
           ================================================================ */
        .wrap-login {
            width: 400px;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            padding: 50px 45px 35px 45px;
            box-shadow:
                0 24px 60px rgba(0, 0, 0, 0.45),
                0 4px 14px rgba(0, 0, 0, 0.18);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .wrap-login:hover {
            transform: translateY(-2px);
            box-shadow:
                0 28px 70px rgba(0, 0, 0, 0.5),
                0 8px 20px rgba(0, 0, 0, 0.22);
        }

        /* ================================================================
           TÍTULO Y LOGO
           ================================================================ */
        .login-title {
            display: block;
            font-weight: 700;
            font-size: 28px;
            color: #1a2332;
            text-align: center;
            letter-spacing: 2px;
            margin-bottom: 8px;
        }

        .login-logo {
            display: block;
            text-align: center;
            margin-bottom: 35px;
        }

        .login-logo img {
            width: 80px;
            height: 80px;
            transition: transform 0.3s ease;
        }

        .login-logo img:hover {
            transform: scale(1.05);
        }

        /* ================================================================
           INPUTS — Misma estética con borde inferior + foco mejorado
           ================================================================ */
        .wrap-input {
            width: 100%;
            position: relative;
            margin-bottom: 28px;
        }

        .input-field {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 500;
            color: #333333;
            line-height: 1.2;
            display: block;
            width: 100%;
            height: 48px;
            background: transparent;
            padding: 0 12px;
            border: none;
            border-bottom: 2px solid #d4d9e0;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .input-field:focus {
            border-bottom-color: #046bb4;
        }

        .input-field::placeholder {
            color: #9ca3af;
            font-weight: 400;
        }

        .input-field:focus::placeholder {
            color: transparent;
        }

        /* Icono del campo */
        .input-icon {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .wrap-input:focus-within .input-icon {
            color: #046bb4;
        }

        /* ================================================================
           CAPS LOCK WARNING
           ================================================================ */
        .caps-warning {
            visibility: hidden;
            color: #e67e22;
            font-size: 12px;
            font-weight: 500;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .caps-warning.visible {
            visibility: visible;
        }

        /* ================================================================
           BOTÓN — Mismo color #046bb4 con hover mejorado
           ================================================================ */
        .login-btn-container {
            display: flex;
            justify-content: center;
            padding-top: 10px;
            margin-bottom: 25px;
        }

        .login-btn {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 600;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            width: 100%;
            height: 50px;
            border-radius: 25px;
            border: none;
            background: linear-gradient(135deg, #046bb4, #0388d1);
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(4, 107, 180, 0.3);
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #035a99, #046bb4);
            box-shadow: 0 6px 20px rgba(4, 107, 180, 0.45);
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(4, 107, 180, 0.3);
        }

        /* ================================================================
           ERROR MESSAGE
           ================================================================ */
        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            text-align: center;
            margin-bottom: 20px;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ================================================================
           SOCIAL LINKS — Misma disposición del original
           ================================================================ */
        .social-links {
            text-align: center;
            padding-top: 20px;
        }

        .social-links a {
            display: inline-block;
            margin: 0 5px;
            opacity: 0.15;
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .social-links a:hover {
            opacity: 0.6;
            transform: translateY(-2px);
        }

        .social-links a img {
            width: 50px;
            padding: 5px;
        }

        /* ================================================================
           TOGGLE PASSWORD
           ================================================================ */
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #9ca3af;
            font-size: 16px;
            background: none;
            border: none;
            padding: 5px;
            transition: color 0.3s ease;
        }

        .toggle-password:hover {
            color: #046bb4;
        }

        /* ================================================================
           RESPONSIVE
           ================================================================ */
        @media (max-width: 576px) {
            .wrap-login {
                padding: 40px 20px 25px 20px;
                margin: 10px;
            }
        }
    </style>
</head>

<body>

<script type="text/javascript">
    document.addEventListener('keydown', function (event) {
        var mayus = event.getModifierState && event.getModifierState('CapsLock');
        var el = document.getElementById("divMayus");
        if (mayus) {
            el.classList.add('visible');
        } else {
            el.classList.remove('visible');
        }
    });
</script>

<div class="container-login">
    <div class="wrap-login">
        <form id="form1" method="POST" action="">
            
            <span class="login-title">DATPOS</span>

            <span class="login-logo">
                <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" alt="DatPOS Logo">
            </span>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fa fa-exclamation-circle"></i> <?= e($error) ?>
                </div>
            <?php endif; ?>

            <!-- Usuario -->
            <div class="wrap-input">
                <input class="input-field" type="text" id="UserName" name="UserName" 
                       placeholder="Usuario" autocomplete="username"
                       value="<?= e($_POST['UserName'] ?? '') ?>" required>
                <i class="fa fa-user input-icon"></i>
            </div>

            <!-- Contraseña -->
            <div class="wrap-input">
                <input class="input-field" type="password" id="Password" name="Password" 
                       placeholder="Contraseña" autocomplete="current-password" required>
                <button type="button" class="toggle-password" onclick="togglePassword()" title="Mostrar/ocultar contraseña">
                    <i class="fa fa-eye-slash" id="toggleIcon"></i>
                </button>
            </div>

            <div id="divMayus" class="caps-warning">
                <i class="fa fa-exclamation-triangle"></i> Bloq Mayús está activada
            </div>

            <!-- Botón Login -->
            <div class="login-btn-container">
                <button type="submit" class="login-btn" id="btnlogin">Ingresar</button>
            </div>

            <!-- Social Links -->
            <div class="social-links">
                <a href="https://www.facebook.com/microsigLat/" target="_blank" title="Facebook">
                    <img src="<?= basePath() ?>/assets/Styles/img/icon/facebook.png" alt="Facebook">
                </a>
                <a href="https://twitter.com/MicrosigLAT" target="_blank" title="Twitter">
                    <img src="<?= basePath() ?>/assets/Styles/img/icon/twitter.png" alt="Twitter">
                </a>
                <a href="https://www.youtube.com/channel/UCbbVUvc7IIV_Cr7AhfLCXhA" target="_blank" title="YouTube">
                    <img src="<?= basePath() ?>/assets/Styles/img/icon/youtube.png" alt="YouTube">
                </a>
            </div>

        </form>
    </div>
</div>

<script>
function togglePassword() {
    var input = document.getElementById('Password');
    var icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye-slash';
    }
}
</script>

</body>
</html>
