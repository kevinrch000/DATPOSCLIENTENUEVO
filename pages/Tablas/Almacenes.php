<?php
/**
 * DatPOS - Página Almacenes
 * Reemplaza: Tablas/Almacenes.aspx
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAuth();
$objUsuario = getUsuarioSesion();

$pageTitle = 'Almacenes | DATPOS';
$pageScript = 'Almacen1.js';
$showCrudButtons = true;
$showConsultButtons = false;

ob_start();
?>

<script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<input id="operacion" type="hidden"/>
<input id="hdd_rv" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="2"/>
<input id="hdd_numerofilas" type="hidden"/>

<div class="c-content-center modern-page">
    <div class="modern-page-header">
        <div class="mph-icon"><i class="material-icons">warehouse</i></div>
        <div class="mph-text">
            <h1>Almacenes</h1>
            <p>Gestión de almacenes, numeradores y datos para guía de remisión.</p>
        </div>
        <div class="mph-spacer"></div>
        <span class="mph-chip"><i class="material-icons">table_chart</i>Tablas</span>
    </div>
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
        <li><a data-toggle="tab" href="#Numerador" class="tabcito">Documentos Almacén</a></li>
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
                        <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="20" readonly onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Nombre*</label>
                    <div class="col-sm-10">
                        <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" maxlength="40" disabled onclick="ObtenerNombreColumna(this)"/>
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
            </div>

            <h4 class="modern-section-title" style="margin-top: 36px;"><i class="material-icons">local_shipping</i>Datos para Guía de Remisión</h4>
            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Domicilio</label>
                    <div class="col-sm-10">
                        <input id="tb_direccion" class="disabled limpiar form-control moderno_tb" disabled maxlength="100" onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Urbanización</label>
                    <div class="col-sm-10">
                        <input id="td_urbanizacion" class="disabled limpiar form-control moderno_tb" disabled maxlength="100" onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top: 20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Departamento</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" onchange="CargarProvincia();" id="txtDepartamento" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Provincia</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" onchange="CargarDistrito();" id="txtProvincia" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Distrito</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" onchange="CargarUbigeo();" id="txtDistrito" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Ubigeo</label>
                    <div class="col-sm-10">
                        <input id="txtUbigeo" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/>
                    </div>
                </div>
            </div>
        </div>

        <!-- NUMERADORES -->
        <div id="Numerador" class="tab-pane" style="padding: 13px;">
            <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Nuevo Numerador</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                        <div class="modal-body">
                            <div class="form-group"><label>Tipo Documento</label><select id="tb_tipDoc" class="form-control"></select></div>
                            <div class="form-group"><label>Número Serie</label><input type="text" maxlength="5" class="form-control" id="tb_serie"/></div>
                            <div class="form-group"><label>Correlativo</label><input type="number" min="0" class="form-control" id="tb_correlativo"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="InsertarFila()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modaleditar" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5 class="modal-title">Editar Numerador</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                        <div class="modal-body">
                            <div class="form-group"><label>Tipo Documento</label><select id="tb_tipDoc_editar" class="form-control"><option value=""></option><option value="I">Ingreso</option><option value="S">Salida</option></select></div>
                            <div class="form-group"><label>Número Serie</label><input type="text" maxlength="5" class="form-control" id="tb_serie_editar"/></div>
                            <div class="form-group"><label>Correlativo</label><input type="number" min="0" class="form-control" id="tb_correlativo_editar"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="ModificarFila()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
            <label id="lb_codigo"></label>
            <table id="tablanumerador" class="table table-bordered table-striped" style="width:40%;">
                <thead id="thTablaNumeradores">
                    <tr><th class="text-center">Tipo Doc</th><th class="text-center">Serie</th><th class="text-center">Correlativo</th><th></th><th></th></tr>
                </thead>
                <tbody></tbody>
            </table>
            <input id="btn_nuevonum" type="button" class="btn btn-primary fa_disabled" value="Agregar Numerador" onclick="NuevoModal()" data-toggle="modal" data-target="#modalnuevo"/>
        </div>

        <!-- LISTADO -->
        <div id="Lista" class="tab-pane tabcito" style="padding: 13px;">
            <nav class="navbar navbar-default" style="margin-bottom: 0px;">
                <div class="container-fluid">
                    <div class="navbar-header"><button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"><span class="sr-only">Toggle</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button></div>
                    <div id="navbar" class="navbar-collapse collapse navbar-right" style="margin-right: 4.5%;">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown"><img src="<?= basePath() ?>/assets/Styles/img/filtro.png" style="WIDTH:14PX;MARGIN-RIGHT:5PX;"/>FILTROS <span class="caret"></span></a>
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
                <colgroup><col style="width:3%"/><col style="width:10%"/><col style="width:40%"/><col style="width:10%"/></colgroup>
                <thead id="thTablaAlmacenes"><tr><th></th><th>Código</th><th>Almacen</th><th>Estado</th></tr></thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>
            <div id="tablePrincipalExportExel" style="display:none;">
                <table id="tableAlmacen" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead><tr><th>Código</th><th>Almacen</th><th>Estado</th></tr></thead>
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
