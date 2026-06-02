<?php
/**
 * DatPOS - Página Familias
 * Reemplaza: Tablas/Familias.aspx + Familias.aspx.vb
 * 
 * CRUD completo de Familias de artículos
 * Usa layout_master.php como plantilla y Familia2.js como lógica JS
 */

// auth.php carga BEUsuario ANTES de session_start()
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';

requireAuth();

$objUsuario = getUsuarioSesion();

// Configuración de la página
$pageTitle = 'Familias | DATPOS';
$pageScript = 'Familia2.js';
$showCrudButtons = true;
$showConsultButtons = false;

// Contenido de la página
ob_start();
?>

<script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>

<div class="c-content-center modern-page">
    <div class="modern-page-header">
        <div class="mph-icon"><i class="material-icons">category</i></div>
        <div class="mph-text">
            <h1>Familias</h1>
            <p>Clasificación de artículos por familia, con color identificador.</p>
        </div>
        <div class="mph-spacer"></div>
        <span class="mph-chip"><i class="material-icons">table_chart</i>Tablas</span>
    </div>
    <ul class="nav nav-tabs" style="">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
    </ul>
    <div class="tab-content">
        <!-- DATOS -->
        <div id="Datos" class="tab-pane in active" style="padding: 13px;">

            <h4 class="modern-section-title"><i class="material-icons">info</i>Información General</h4>

            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Código*</label>
                    <div class="col-sm-10">
                        <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="5" readonly onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Nombre*</label>
                    <div class="col-sm-10">
                        <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" maxlength="50" disabled onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Estado*</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_estado" disabled onclick="ObtenerNombreColumna(this)">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Color</label>
                    <div class="col-sm-10">
                        <input id="send" type="color" style="width:60px;" oninput="txtCodColor.value = send.value" class="disabled limpiar form-control moderno_tb"/>
                        <input id="txtCodColor" style="visibility:hidden;type:text;width:40px;"/>
                    </div>
                </div>
            </div>

        </div>
        <!-- LISTADO -->
        <div id="Lista" class="tab-pane tabcito" style="padding: 13px;">
            <nav class="navbar navbar-default" style="margin-bottom: 0px;">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse navbar-right" style="margin-right: 4.5%;">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true">
                                    <img src="<?= basePath() ?>/assets/Styles/img/filtro.png" style="WIDTH:14PX;MARGIN-RIGHT:5PX;"/>FILTROS <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="FilterStatus(2);">Activos</a></li>
                                    <li><a href="#" onclick="FilterStatus(3);">Inactivos</a></li>
                                    <li><a href="#" onclick="FilterStatus(1);">Mostrar Todos</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <table id="table_id" class="display" style="width:100%;">
                <colgroup>
                    <col style="width:1%"></col>
                    <col style="width:20%"></col>
                    <col style="width:60%"></col>
                    <col style="width:20%"></col>
                </colgroup>
                <thead id="thTablaFamilia">
                    <tr>
                        <th></th>
                        <th>Código</th>
                        <th>Familia</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);">
                </tbody>
            </table>

            <!-- Tabla para exportar a Excel -->
            <div id="tablePrincipalExportExel" style="display:none;">
                <table id="tableFamilia" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Familia</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
