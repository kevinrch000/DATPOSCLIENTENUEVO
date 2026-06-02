<?php
/**
 * DatPOS - Layout Master
 * Reemplaza: Site.Master + Site.Master.vb
 * 
 * Uso en páginas:
 *   $pageTitle = "Facturación | DATPOS";
 *   $pageScript = "Facturacion6.js";
 *   ob_start();
 *     // ... contenido de la página ...
 *   $pageContent = ob_get_clean();
 *   require_once __DIR__ . '/layout_master.php';
 */

// auth.php ya fue incluido por la página que llama a este layout
// y maneja session_start() con BEUsuario pre-cargado
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/auth.php';
}
require_once __DIR__ . '/helpers.php';

// Verificar autenticación
requireAuth();

// Obtener datos del usuario
$objUsuario = getUsuarioSesion();

// Variables que la página puede definir antes de incluir este layout
$pageTitle = isset($pageTitle) ? $pageTitle : 'DATPOS';
$pageScript = isset($pageScript) ? $pageScript : '';
$pageContent = isset($pageContent) ? $pageContent : '';
$showCrudButtons = isset($showCrudButtons) ? $showCrudButtons : true;
$showConsultButtons = isset($showConsultButtons) ? $showConsultButtons : false;
$loadConsultAssets = isset($loadConsultAssets) ? $loadConsultAssets : true;
$pageScriptPatch = isset($pageScriptPatch) ? $pageScriptPatch : '';

// ============================================================
// SPA Navigation: si es petición AJAX, devolver solo el contenido
// ============================================================
if (!empty($_SERVER['HTTP_X_SPA_NAV'])) {
    header('Content-Type: application/json; charset=utf-8');
    $bp = basePath();
    echo json_encode(array(
        'html'              => $pageContent,
        'title'             => $pageTitle,
        'pageScript'        => !empty($pageScript)      ? $bp . '/assets/Javascript/' . $pageScript        : '',
        'pageScriptPatch'   => !empty($pageScriptPatch)  ? $bp . '/assets-patch/' . $pageScriptPatch        : '',
        'showCrudButtons'   => (bool) $showCrudButtons,
        'showConsultButtons'=> (bool) $showConsultButtons,
        'loadConsultAssets'  => (bool) $loadConsultAssets,
    ));
    exit;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="es" lang="es">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>

    <link rel="shortcut icon" href="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" type="image/x-icon">

    <!-- CSS del proyecto original -->
    <link href="<?= basePath() ?>/assets/Styles/Site.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/MenuVer.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/css/jquery-confirm.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/css/alertify.core.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/css/alertify.default.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Scripts/jquery-ui-1.12.1.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/ddl_autocomplete.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets/Styles/Modern-UI.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets-patch/datpos-theme-dark.css" rel="stylesheet" type="text/css" />
    <link href="<?= basePath() ?>/assets-patch/datpos-responsive.css" rel="stylesheet" type="text/css" />

    <!-- CDN -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css"
        href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- JS del proyecto original -->
    <script src="<?= basePath() ?>/assets/Scripts/jquery-2.1.1.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/bootstrap.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/chart.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/highcharts.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/data.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/jquery-confirm.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/alertify.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/jquery-ui-1.12.1.js" type="text/javascript"></script>
    <script type="text/javascript" charset="utf8"
        src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.js"></script>
    <script src="<?= basePath() ?>/assets/Styles/SweetAlert2/SweetAlert2.js" type="text/javascript"></script>
    <script src="https://cdn.jsdelivr.net/npm/promise-polyfill"></script>

    <!-- JS Custom -->
    <script src="<?= basePath() ?>/assets/Javascript/Comun.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Javascript/ddl_autocomplete.js" type="text/javascript"></script>

    <!-- Adapter: intercepta llamadas .aspx y las redirige a API PHP -->
    <script>window.DATPOS_BASE_PATH = '<?= basePath() ?>';</script>
    <script src="<?= basePath() ?>/assets/Javascript/facturacion_adapter.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets-patch/modal_fix.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets-patch/datpos-responsive.js" type="text/javascript"></script>

    <style>
        /* ================================================================
           OVERRIDES MODERNOS — Mantienen la esencia pero con pulido
           ================================================================ */
        input:focus,
        input.form-control:focus,
        select:focus,
        select.form-control:focus {
            outline: none !important;
            outline-width: 0 !important;
            box-shadow: none;
        }

        .navbar-default {
            background-color: transparent;
            border-color: transparent;
        }

        .dataTables_wrapper .dataTables_filter input {
            border: none;
            padding: 8px 15px 8px 13px;
            border-bottom: 1px solid #228ac9;
        }

        .navbar-nav>li>a {
            padding-top: 1px;
        }

        .navbar {
            min-height: 0px;
        }

        /* Session Timeout Overlay */
        #timeOutWarningOverlay {
            position: fixed;
            display: none;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            cursor: pointer;
        }

        /* Toques modernos sutiles */
        .nav-side-menu {
            transition: all 0.3s ease;
        }

        .c-content {
            transition: margin-left 0.3s ease;
        }

        /* Botones CRUD con hover mejorado */
        .botonesnuevos a {
            transition: all 0.2s ease !important;
        }

        .botonesnuevos a:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
        }

        /* Fix cerrar sesión */
        #Ul1 li,
        #Ul1 li::before,
        #Ul1 li::after {
            transition: none !important;
            animation: none !important;
        }

        #Ul1 li {
            display: flex !important;
            align-items: center !important;
            height: 40px !important;
            transform: none !important;
        }

        #Ul1 li::before,
        #Ul1 li::after {
            display: none !important;
        }

        #Ul1 li a {
            display: flex !important;
            align-items: center !important;
            padding-left: 15px !important;
            gap: 10px !important;
            width: 100% !important;
            height: 100% !important;
            line-height: normal !important;
            position: static !important;
            color: rgb(190, 190, 190) !important;
            transition: padding-left 0.18s ease, color 0.18s ease !important;
        }

        #Ul1 li:hover a {
            padding-left: 23px !important;
            color: white !important;
            background-color: transparent !important;
        }

        #Ul1 li:hover {
            background-color: #262C31 !important;
            transform: none !important;
        }

        #Ul1 li a img {
            position: static !important;
            display: inline-block !important;
            float: none !important;
            transform: none !important;
            width: 21px !important;
            height: 21px !important;
            flex-shrink: 0 !important;
            top: auto !important;
            left: auto !important;
            margin: 0 !important;
        }

        .modal-backdrop {
            z-index: 1000000000 !important;
        }

        .modal {
            z-index: 1000000010 !important;
        }

        .modal-dialog {
            z-index: 1000000020 !important;
        }

        .c-content-center {
            background: #fff;
            border: 1px solid #e5edf5;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(34, 138, 201, 0.08);
            padding: 16px;
        }

        .nav-tabs {
            border-bottom: 1px solid #dbe8f3;
        }

        .nav-tabs>li>a {
            border-radius: 8px 8px 0 0;
            font-weight: 600;
        }

        .nav-tabs>li.active>a,
        .nav-tabs>li.active>a:focus,
        .nav-tabs>li.active>a:hover {
            border-top: 3px solid #228ac9;
            color: #145f8a !important;
        }

        .form-control,
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select {
            border-radius: 8px !important;
            border: 1px solid #cfdce8 !important;
        }

        .btn,
        button,
        input[type="button"] {
            border-radius: 8px !important;
        }

        table.dataTable,
        .table {
            border-collapse: separate !important;
            border-spacing: 0;
        }

        table.dataTable thead th,
        .table thead th {
            background: #f3f8fc;
            color: #28556f;
        }

        .dataTables_wrapper {
            overflow-x: auto;
            padding-bottom: 8px;
        }
    </style>
</head>

<body>

    <!-- Hidden input (usado por JavaScript original) -->
    <input id="NombreColumna" value="" type="hidden" />

    <!-- Session Timeout Warning -->
    <div id="timeOutWarningOverlay">
        <div
            style="border-radius: 15px;height:auto; width:400px; background-color:white; position: fixed;top: 50%;left: 50%; transform: translate(-50%, -50%);padding:10px; text-align:center;">
            <div class="swal2-icon swal2-warning swal2-icon-show" style="display: flex;">
                <div class="swal2-icon-content">!</div>
            </div>
            <div id="div_msg">
                <b>Su sesión esta por expirar. Por favor click en el botón para continuar con la sesión actual.</b>
                <b><span style="background-color:yellow;">00:</span><span id="time"
                        style="background-color:yellow;">59</span></b>
            </div>
            <input style="background-color:#337ab7; margin-top:21px;margin-bottom:7px;" type="button"
                class="btn btn-primary" id="keep" value="Mantener sesión" />
            <input onclick="window.location.href = '<?= basePath() ?>/pages/migcliente/LogOn.php';"
                style="display: none; background-color:#337ab7; margin-top:21px;margin-bottom:7px;" type="button"
                class="btn btn-primary" id="out" value="Ok" />
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MENÚ LATERAL — Mismo estructura que Site.Master -->
    <!-- ============================================================ -->
    <div id="menuver" class="nav-side-menu" onmouseover="MostrarMenu();">
        <div class="brand c-logo-menu">
            <a href="<?= basePath() ?>/pages/Interfaces/Home.php" title="Inicio">
                <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" alt="DATPOS">
            </a>
            <label>DATPOS</label>
        </div>

        <span class="glyphicon glyphicon-align-justify toggle-btn" style="font-size: 18px; margin-top: 5px;"
            data-toggle="collapse" data-target="#menu-content"></span>
        <div id="listOpciones" class="menu-list">
            <!-- FIX BUG 1: collapse in para que el menú sea visible por defecto -->
            <ul id="menu-content" class="menu-content collapse in">
                <!-- Ítem estático Dashboard (siempre primero) -->
                <li id="dp-static-dashboard" class="dp-static-item<?= (basename($_SERVER['SCRIPT_NAME'] ?? '') === 'Home.php') ? ' dp-active' : '' ?>">
                    <a href="<?= basePath() ?>/pages/Interfaces/Home.php">
                        <i class="material-icons">dashboard</i>Dashboard
                    </a>
                </li>
            </ul>
            <ul id="Ul1" class="menu-content collapse in">
                <li>
                    <a href="<?= basePath() ?>/pages/migcliente/logout.php">
                        <img src="<?= basePath() ?>/assets/Styles/img/cerrarsesion.png" style="WIDTH: 21px;" />Cerrar
                        Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- CONTENIDO PRINCIPAL -->
    <!-- ============================================================ -->
    <div id="content" class="c-content" style="background-color:White">
        <header>
            <span class="c-menu-toggle" id="btnMenu" onclick="mostrar();"></span>
            <span class="header-title" id="id_titulo">Administración</span>

            <div class="c-menu-user">
                <div style="border-radius: 0px; display: block;">
                    <img src="<?= basePath() ?>/assets/Styles/img/avatar.png" width="100%" height="100%" alt="Avatar"
                        id="idlogo" style="width:50px;border-radius: 0px;">
                </div>
                <img src="<?= basePath() ?>/assets/Styles/img/avatar.png" alt="Avatar" id="idfoto">
                <div class="info-name-type">
                    <span id="nombre_empresa"><?= e($objUsuario->cdescripcion) ?></span>
                    <span id="nombre_usuario"><?= e($objUsuario->cdsc_usuario) ?></span>
                </div>
                <li class="dropdown" style="list-style: none;TOP: -4px;">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                        <span class="glyphicon glyphicon-cog" style="font-size:17px;"></span>
                    </a>
                    <ul class="dropdown-menu" style="left:-161px;">
                        <li><a href="#" data-toggle="modal" data-target="#ModalDatosPersonales"
                                onclick="DatosGenerales();">Mi perfil</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#ModalAcerdaDe">Acerca de</a></li>
                        <li><a href="#" data-toggle="modal" data-target="#ModalCanbiarContrasena"
                                onclick="LimpiarCambiarContrasena();">Cambiar Contraseña</a></li>
                    </ul>
                </li>
            </div>
        </header>

        <!-- SPA: zona dinámica que se reemplaza sin recargar -->
        <div id="spa-content-area">

            <!-- Botones CRUD -->
            <div id="spa-crud-buttons" style="<?= $showCrudButtons ? '' : 'display:none;' ?>">
                <div class="adminActions content">
                    <div class="adminButtons">
                        <div class="botonesnuevos">
                            <a href="#" class="botones_hab" id="btn_p_nuevo" style="background-color: #5bc0de;" title="Nuevo"
                                onclick="Nuevo();">
                                <i class="material-icons">add</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_editar" style="background-color: #f0ad4e;" title="Editar"
                                onclick="Editar();">
                                <i class="material-icons">edit</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_grabar" style="background-color: #5cb85c;" title="Grabar"
                                onclick="Guardar();">
                                <i class="material-icons">save</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_eliminar" style="background-color: #f44336;"
                                title="Eliminar" onclick="Eliminar();">
                                <i class="material-icons">delete</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_back"
                                style="background-color: #727292;" title="Deshacer" onclick="Deshacer();">
                                <i class="material-icons">undo</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_imprimir"
                                style="background-color: #b3962d;" title="Imprimir">
                                <i class="material-icons">print</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div id="spa-consult-buttons" style="<?= $showConsultButtons ? '' : 'display:none;' ?>">
                <div id="divColsulta" class="adminActions content" style="visibility:hidden;">
                    <div class="adminButtons">
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_ejecutar" style="background-color: #f0ad4e;"
                                title="Ejecutar" onclick="Ejecutar();">
                                <i class="material-icons">play_arrow</i>
                            </a>
                        </div>
                        <div class="botonesnuevos">
                            <a href="#" class="botones_des" id="btn_p_limpiar" style="background-color: #21b5d6;"
                                title="Limpiar" onclick="Limpiar();">
                                <i class="material-icons">refresh</i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CONTENIDO DE LA PÁGINA (equivale a ContentPlaceHolder) -->
            <div id="spa-page-html">
                <?= $pageContent ?>
            </div>

        </div><!-- /spa-content-area -->

    </div>

    <!-- ============================================================ -->
    <!-- MODALES (idénticos al Site.Master original) -->
    <!-- ============================================================ -->

    <!-- Modal Acerca De -->
    <div class="modal" id="ModalAcerdaDe" tabindex="-1" role="dialog">
        <div class="modal-dialog" style="padding: 60px;">
            <div class="modal-content">
                <div class="modal-body">
                    <div style="height:70%;text-align:center;">
                        <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png"
                            style="width:50PX;display: block;margin: 0 auto;margin-top: 90px;">
                        <p style="margin-top: 4%;">Portal de Cliente - DATPOS</p>
                        <p>Versión: 2.0.0-PHP</p>
                        <p>2026</p>
                        <p>© Copyright 2026 - Todos los Derechos reservados DATPOS</p>
                        <p>Soporte TELF. (511) 225-7622, (511) 224-5241</p>
                        <p style="margin-top: 46px;"><b>Advertencia:</b> Todos los derechos reservados DATPOS SAC.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Cambiar Contraseña -->
    <div class="modal fade" id="ModalCanbiarContrasena" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="margin: 90px;">
                <div class="modal-header">
                    <div class="col-sm-11">
                        <h3 class="modal-title">Cambiar Contraseña</h3>
                    </div>
                    <div class="col-sm-1">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-11">
                            <div class="input-group">
                                <input id="inContraActual" type="password" class="limpiar moderno_tb" maxlength="50"
                                    style="width: 300px;" placeholder="Contraseña actual">
                                <a class="disabled input-group-addon" id="AActual" onclick="mostrarContrasenaActual();"
                                    style="padding: -6px -12px;background-color: #ffffff;border:0px">
                                    <i class="material-icons">visibility_off</i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-11">
                            <div class="input-group">
                                <input id="inContraNueva" type="password" class="limpiar moderno_tb" maxlength="50"
                                    style="width: 300px;" placeholder="Nueva contraseña">
                                <a class="disabled input-group-addon" id="ANueva" onclick="mostrarContrasenaNueva();"
                                    style="padding: -6px -12px;background-color: #ffffff;border:0px">
                                    <i class="material-icons">visibility_off</i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-sm-1"></div>
                        <div class="col-sm-11">
                            <div class="input-group">
                                <input id="inContraRepetir" type="password" class="limpiar moderno_tb" maxlength="50"
                                    style="width: 300px;" placeholder="Repetir contraseña">
                                <a class="disabled input-group-addon" id="ARepetir"
                                    onclick="mostrarContrasenaRepetir();"
                                    style="padding: -6px -12px;background-color: #ffffff;border:0px">
                                    <i class="material-icons">visibility_off</i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="CambiarContrasena();">Guardar
                        Contraseña</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Datos Personales -->
    <div class="modal fade" id="ModalDatosPersonales" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document" style="padding: 60px;">
            <div class="modal-content" style="background-color:#ddd;">
                <div class="modal-header" style="background: #d6d5d5;">
                    <div class="col-sm-6">
                        <h3 class="modal-title">Datos Generales</h3>
                    </div>
                    <div class="col-sm-6">
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                </div>
                <div class="modal-body">
                    <table class="table" style="border:0px;">
                        <tr>
                            <td style="border:0px;font-weight: bold;color: #333;"><?= e($objUsuario->cdescripcion) ?>
                            </td>
                        </tr>
                    </table>
                    <table class="table" style="border:0px;">
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Usuario: </td>
                            <td style="border:0px;width:60%;color:#333;"><?= e($objUsuario->cdsc_usuario) ?>
                                <?= e($objUsuario->ccod_usuario) ?></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Rol de usuario: </td>
                            <td style="border:0px;width:20%;color:#333;"><label id="idRolDescripcion"></label></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Tienda: </td>
                            <td style="border:0px;width:20%;color:#333;"><label id="sitNomTienda"></label> <label
                                    id="td_codtienda"><?= e($objUsuario->ccod_tiend) ?></label></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Almacen: </td>
                            <td style="border:0px;width:60%;color:#333;"><label id="sitNomAlmacen"></label>
                                <?= e($objUsuario->ccod_almacen) ?></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Caja: </td>
                            <td style="border:0px;width:60%;color:#333;"><label id="sitNomCaja"></label> <label
                                    id="td_caja"><?= e($objUsuario->ccod_caja) ?></label></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Lista de precio Normal: </td>
                            <td style="border:0px;width:60%;color:#333;"><label id="sitNomListPreNor"></label> <label
                                    id="sitIdListPreNor"></label></td>
                        </tr>
                        <tr>
                            <td style="border:0px;width:20%;color:#333;">Lista de precio Preferido: </td>
                            <td style="border:0px;width:60%;color:#333;"><label id="sitNomListPrePre"></label> <label
                                    id="sitIdListPrePre"></label></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Excel Context Menu -->
    <ul id="contextMenu" class="dropdown-menu" role="menu" style="display:none">
        <div class="input-group">
            <a><img src="<?= basePath() ?>/assets/Styles/images/icon_exel_c.png"
                    style="width:14px;margin-right:8px;margin-left:5px" />Exportar a Excel</a>
        </div>
    </ul>

    <!-- ============================================================ -->
    <!-- SCRIPTS DE SESIÓN (idénticos al Site.Master) -->
    <!-- ============================================================ -->
    <script type="text/javascript">
        $(document).ready(function () {
            var counter = 60;
            var idleTime = 0;
            var countdown;
            var sessionTimeOutValue = 40; // Timeout en minutos (equivale a Session.Timeout)

            var idleInterval = setInterval(timerIncrement, 60000);

            $('#keep').click(function () {
                idleTime = 0;
                $('#timeOutWarningOverlay').hide();
                // PHP: renovar sesión via AJAX
                $.ajax({
                    url: '<?= basePath() ?>/api/session_refresh.php',
                    type: 'POST',
                    success: function () { console.log('Sesión renovada'); }
                });
            });

            function timerIncrement() {
                if (typeof BLOQUER !== 'undefined' && BLOQUER == "OK") {
                    idleTime = idleTime + 1;
                    if (idleTime > sessionTimeOutValue - 1) {
                        $('#timeOutWarningOverlay').show();
                        startTimer();
                    }
                    if (idleTime > sessionTimeOutValue) {
                        $('#keep').hide();
                        $('#out').show();
                        $("#div_msg")[0].innerText = "Su sesión expiró debido a inactividad.";
                    }
                }
            }

            function startTimer() {
                countdown = setInterval(countDownClock, 1000);
            }

            function countDownClock() {
                counter = counter - 1;
                if (counter < 10) {
                    $('#time').text("0" + counter);
                } else {
                    $('#time').text(counter);
                }
                if (counter == 0) {
                    counter = 60;
                    clearInterval(countdown);
                }
            }
        });

        function MostrarMenu() {
            $("#menuver").removeClass("hiddenmenuvertical-menu");
            $("#content").removeClass("hiddenmenuvertical-header");
        }

        function OcultarMenu() {
            $("#menuver").addClass("hiddenmenuvertical-menu");
            $("#content").addClass("hiddenmenuvertical-header");
        }

        // c-loader eliminado — navegación SPA sin pantalla de carga

        // Cargar menú lateral dinámicamente (equivale a Site.Master.vb Page_Load)
        try { CargarMenu(); } catch(e) { console.warn('CargarMenu:', e); }

        // FIX BUG 1: forzar expansión del menú aunque Bootstrap lo haya colapsado
        try {
            $('#menu-content').addClass('in').removeClass('out');
            $('#Ul1').addClass('in').removeClass('out');
        } catch(e) {}

        // Garantizar que el ítem estático "Dashboard" siempre quede como primera
        // opción del sidebar, incluso después de que CargarRoles() recargue el menú.
        (function ensureStaticDashboard() {
            var $menu = $('#menu-content');
            if (!$menu.length) return;
            var isHome = (location.pathname.split('/').pop() || '').toLowerCase() === 'home.php';
            var html = '<li id="dp-static-dashboard" class="dp-static-item' + (isHome ? ' dp-active' : '') + '">' +
                '<a href="<?= basePath() ?>/pages/Interfaces/Home.php">' +
                '<i class="material-icons">dashboard</i>Dashboard</a></li>';
            function reinsert() {
                $('#dp-static-dashboard').remove();
                $menu.prepend(html);
            }
            reinsert();
            // Observa cambios futuros (ej. recargas dinámicas del menú)
            if (window.MutationObserver) {
                var observer = new MutationObserver(function () {
                    if (!document.getElementById('dp-static-dashboard')) {
                        reinsert();
                    }
                });
                observer.observe($menu[0], { childList: true });
            }
        })();

        function mostrar() {
            if ($("#menuver").hasClass("hiddenmenuvertical-menu") == true) {
                $("#menuver").removeClass("hiddenmenuvertical-menu");
                $("#content").removeClass("hiddenmenuvertical-header");
            } else {
                $("#menuver").addClass("hiddenmenuvertical-menu");
                $("#content").addClass("hiddenmenuvertical-header");
            }
        }

        /**
         * Función global para AJAX calls a la API PHP
         * Reemplaza los $.ajax({ url: "Page.aspx/WebMethod" }) del código JS original
         */
        function callAPI(endpoint, data, successCallback, errorCallback) {
            $.ajax({
                type: "POST",
                url: "<?= basePath() ?>/api/" + endpoint,
                data: JSON.stringify(data),
                contentType: "application/json; charset=utf-8",
                dataType: "json",
                success: function (response) {
                    if (successCallback) successCallback(response);
                },
                error: function (xhr, status, error) {
                    console.error("API Error:", endpoint, error);
                    if (errorCallback) errorCallback(xhr, status, error);
                }
            });
        }
    </script>

    <!-- Assets compartidos para páginas legacy (precargados para que SPA pueda navegar a cualquiera) -->
    <?php if ($loadConsultAssets): ?>
        <script src="<?= basePath() ?>/assets/Javascript/Filtros.js" type="text/javascript"></script>
        <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
        <script src="<?= basePath() ?>/assets/Diagramas/highcharts.js" type="text/javascript"></script>
        <script src="<?= basePath() ?>/assets/Diagramas/data.js" type="text/javascript"></script>
        <script src="<?= basePath() ?>/assets/Diagramas/exporting.js" type="text/javascript"></script>
        <script src="<?= basePath() ?>/assets/Diagramas/accessibility.js" type="text/javascript"></script>
    <?php endif; ?>

    <!-- Script específico de la página inicial -->
    <?php if (!empty($pageScript)): ?>
        <script src="<?= basePath() ?>/assets/Javascript/<?= $pageScript ?>" type="text/javascript" data-spa-page-script></script>
    <?php endif; ?>

    <!-- Patch JS opcional: corrige bugs del JS original sin tocarlo -->
    <?php if (!empty($pageScriptPatch)): ?>
        <script src="<?= basePath() ?>/assets-patch/<?= $pageScriptPatch ?>" type="text/javascript" data-spa-page-script-patch></script>
    <?php endif; ?>

    <!-- SPA Navigation: AJAX navigation entre módulos sin recargar la página -->
    <script src="<?= basePath() ?>/assets/Javascript/spa_navigation.js" type="text/javascript"></script>

</body>

</html>