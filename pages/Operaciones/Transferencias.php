<?php
/**
 * DatPOS - Transferencias entre almacenes
 * Reemplaza: Operaciones/Transferencias.aspx + Transferencias.aspx.vb
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../DA/DATransferencia.php';
requireAuth();

$o = $_SESSION['objBEUsuario'];
$pageTitle = 'Transferencias entre Almacenes | DATPOS';
$pageScript = 'Transferencias.js';
$pageScriptPatch = 'operaciones_patch.js';
$showCrudButtons = true;

ob_start();
?>
<link href="/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />
<link href="/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

<input id="hdd_rv" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_id_cbinve" value="0" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="2"/>
<input id="hdd_numerofilas" type="hidden"/>

<script>window.OPERACION_ASPX = 'Transferencias.aspx';</script>
<script src="/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<div class="c-content-center modern-page">
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a></li>
    </ul>

    <div class="tab-content">
        <!-- DATOS -->
        <div id="Datos" class="tab-pane in active">

            <h4 style="border-bottom:groove;margin:30px 0;">Datos Generales</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Fecha*</label>
                    <div class="col-sm-9"><input id="dp_fecha" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Origen (Salida)</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Almacén Origen*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_almacenOrig" onchange="CargarNumeradorSalida();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Tipo Operación*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_tipOperSalida" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Serie*</label>
                    <div class="col-sm-9"><input id="tb_serieOrigen" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Número</label>
                    <div class="col-sm-9"><input id="tb_numOrigen" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Destino (Ingreso)</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Almacén Destino*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_almacenDest" onchange="CargarNumeradorIngreso();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Tipo Operación*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_tipOperIngreso" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Serie*</label>
                    <div class="col-sm-9"><input id="tb_serieDest" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-3 moderno_lb">Número</label>
                    <div class="col-sm-9"><input id="tb_numDest" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Detalle de Artículos</h4>

            <!-- Modal Nuevo Detalle -->
            <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Agregar Artículo</h5>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group"><label>Código</label>
                                <div class="input-group">
                                    <input id="tb_cod" type="text" class="disabled limpiar form-control moderno_tb" onclick="ObtenerNombreColumna(this)">
                                    <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalArticulos" onclick="ModalArticulos();" style="background-color:#fff;border:0"><i class="fa fa-search color-buscadores"></i></a>
                                </div>
                            </div>
                            <div class="form-group"><label>Artículo</label><input type="text" class="form-control" id="tb_articulo" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Cantidad disponible</label><input type="text" class="form-control" id="tb_cantActual" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Costo</label><input type="text" class="form-control" id="tb_costo" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Cantidad a transferir</label><input type="number" min="0" class="form-control" id="tb_cantidad" onclick="ObtenerNombreColumna(this)"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="InsertarFila()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Artículos disponibles -->
            <div class="modal fade" id="modalArticulos" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Artículos disponibles en almacén origen</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <table id="table_Articulos" class="display" style="width:-webkit-fill-available;">
                                <thead id="thtable_Articulos"><tr><th></th><th>Código</th><th>Nombre</th><th>Familia</th><th>Cantidad</th><th>Costo</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasarArticulo();">Seleccionar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Detalle -->
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Editar</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <div class="form-group"><label>Código</label><input id="tb_cod_editar" type="text" class="form-control" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Artículo</label><input type="text" class="form-control" id="tb_articulo_editar" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Costo</label><input type="text" class="form-control" id="tb_costo_editar" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label>Cantidad</label><input type="number" min="0" class="form-control" id="tb_cantidad_editar" onclick="ObtenerNombreColumna(this)"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="EditarFila()">Editar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal validación stock -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Artículos sin stock suficiente</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <table id="tbLisArticuloError" class="display" style="width:100%;">
                                <thead><tr><th>Artículo</th><th>Cant. solicitada</th><th>Cant. actual</th><th>Faltante</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Detalle -->
            <table id="tabla" class="table table-bordered table-striped">
                <thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Costo</th><th></th><th></th></tr></thead>
                <tbody></tbody>
            </table>

            <div class="col-sm-12">
                <input type="button" value="Agregar" class="btn btn-primary fa_disabled" onclick="NuevoModal()"/>
            </div>

            <div class="col-sm-12"><label class="moderno_lb">Observaciones</label></div>
            <div class="col-sm-12">
                <textarea id="tb_observacion" class="disabled limpiar form-control moderno_tb" maxlength="50" cols="20" rows="2" disabled onclick="ObtenerNombreColumna(this)"></textarea>
            </div>
        </div>

        <!-- LISTA -->
        <div id="Lista" class="tab-pane tabcito">
            <nav class="navbar navbar-default" style="margin-bottom:0;">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse navbar-right" style="margin-right:4.5%;">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><img src="/assets/Styles/img/filtro.png" style="width:14px;margin-right:5px;"/>FILTROS <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="FilterTipo('');">Mostrar Todos</a></li>
                                    <li><a href="#" onclick="FilterTipo('TRAN');">Transferencia</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <table id="table_id" class="display" style="width:100%;">
                <thead id="thTablaTransferencias">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Alm. Origen</th>
                        <th>Tipo Orig.</th>
                        <th>Serie Orig.</th>
                        <th>N° Orig.</th>
                        <th>Alm. Destino</th>
                        <th>Tipo Dest.</th>
                        <th>Serie Dest.</th>
                        <th>N° Dest.</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
