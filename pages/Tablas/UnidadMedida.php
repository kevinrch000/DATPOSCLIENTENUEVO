<?php
/**
 * DatPOS - Página Unidad de Medida
 * Reemplaza: Tablas/UnidadMedida.aspx
 */

require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAuth();
$objUsuario = getUsuarioSesion();

$pageTitle = 'Unidad de Medida | DATPOS';
$pageScript = 'UnidadMedida1.js';
$showCrudButtons = true;
$showConsultButtons = false;

ob_start();
?>

<script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<input id="operacion" type="hidden"/>
<input id="hdd_id" value="0" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>

<div class="c-content-center modern-page">
    <div class="modern-page-header">
        <div class="mph-icon"><i class="material-icons">straighten</i></div>
        <div class="mph-text">
            <h1>Unidad de Medida</h1>
            <p>Mantenimiento de unidades de medida y su equivalencia con SUNAT.</p>
        </div>
        <div class="mph-spacer"></div>
        <span class="mph-chip"><i class="material-icons">table_chart</i>Tablas</span>
    </div>
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
    </ul>
    <div class="tab-content">
        <!-- DATOS -->
        <div id="Datos" class="tab-pane in active" style="padding: 13px;">
            <h4 class="modern-section-title"><i class="material-icons">info</i>Información General</h4>
            <div class="row" style="margin-top: 20px;">
    <div class="col-sm-6 col-xs-12">
        <div class="form-group" style="display:flex; align-items:center; margin-bottom:0;">
            <label class="moderno_lb" style="min-width:120px; margin-bottom:0; white-space:nowrap;">Código*</label>
            <input id="tb_UMcodigo" class="readonl limpiar form-control moderno_tb" maxlength="5" readonly onclick="ObtenerNombreColumna(this)"/>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12">
        <div class="form-group" style="display:flex; align-items:center; margin-bottom:0;">
            <label class="moderno_lb" style="min-width:120px; margin-bottom:0; white-space:nowrap;">Abreviatura*</label>
            <input id="tb_UMetiqueta" class="disabled limpiar form-control moderno_tb" maxlength="5" disabled onclick="ObtenerNombreColumna(this)"/>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 20px;">
    <div class="col-sm-6 col-xs-12">
        <div class="form-group" style="display:flex; align-items:center; margin-bottom:0;">
            <label class="moderno_lb" style="min-width:120px; margin-bottom:0; white-space:nowrap;">Cód. SUNAT*</label>
            <input id="tb_codtribu" class="disabled limpiar form-control moderno_tb" maxlength="3" disabled onclick="ObtenerNombreColumna(this)"/>
        </div>
    </div>
    <div class="col-sm-6 col-xs-12">
        <div class="form-group" style="display:flex; align-items:center; margin-bottom:0;">
            <label class="moderno_lb" style="min-width:120px; margin-bottom:0; white-space:nowrap;">Descripción*</label>
            <input id="tb_UMnombre" class="disabled limpiar form-control moderno_tb" maxlength="50" disabled onclick="ObtenerNombreColumna(this)"/>
        </div>
    </div>
</div>
<div class="row" style="margin-top: 20px;">
    <div class="col-sm-6 col-xs-12">
        <div class="form-group" style="display:flex; align-items:center; margin-bottom:0;">
            <label class="moderno_lb" style="min-width:120px; margin-bottom:0; white-space:nowrap;">Estado*</label>
            <select class="disabled limpiar form-control moderno_tb" id="tb_UMestado" disabled onclick="ObtenerNombreColumna(this)">
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
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
                            <span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>
                        </button>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse navbar-right" style="margin-right: 4.5%;">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><img src="<?= basePath() ?>/assets/Styles/img/filtro.png" style="WIDTH:14PX;MARGIN-RIGHT:5PX;"/>FILTROS <span class="caret"></span></a>
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
                    <col style="width:1%"/><col style="width:15%"/><col style="width:15%"/>
                    <col style="width:15%"/><col style="width:15%"/><col style="width:15%"/>
                </colgroup>
                <thead id="thTablaUnidadMedida">
                    <tr>
                        <th></th><th>Código</th><th>Abreviatura</th>
                        <th>Código Tributario</th><th>Descripción</th><th>Estado</th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>

            <div id="tablePrincipalExportExel" style="display:none;">
                <table id="tableUnidadMedida" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr><th>Código</th><th>Etiqueta</th><th>Código Tributario</th><th>Descripción</th><th>Estado</th></tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>