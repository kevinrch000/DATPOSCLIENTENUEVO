<?php
/**
 * DatPOS - Ingresos directos
 * Reemplaza: Operaciones/Ingresos.aspx + Ingresos.aspx.vb
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../DA/DAIngreso.php';
requireAuth();

$o = $_SESSION['objBEUsuario'];
$pageTitle = 'Ingresos Directos | DATPOS';
$pageScript = 'Ingresos.js';
$pageScriptPatch = 'operaciones_patch.js';
$showCrudButtons = true;

// Cargar tipos de operación de tipo Ingreso para el dropdown (server-side, como Page_Load del VB)
$DA = new DAIngreso();
$tiposOper = $DA->ConsultarTiposOperacionActivosIngresos($o->ccod_empresa, $o);

// IGV por defecto (renderizado al hidden ValorIgv)
$ivgRows = $DA->ObtenerIvg($o->ccod_empresa, $o);
$valorIgv = (count($ivgRows) > 0) ? strval($ivgRows[0][0] ?? '') : '';

ob_start();
?>
<link href="/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />
<link href="/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">

<input id="tbCodProveedor" class="limpiar" type="hidden"/>
<input id="ValorIgv" type="hidden" value="<?= e($valorIgv) ?>"/>
<input id="hdd_rv" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_id_cbinve" value="0" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>

<script>window.OPERACION_ASPX = 'Ingresos.aspx';</script>
<script src="/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<div class="c-content-center modern-page">
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a></li>
    </ul>

    <div class="tab-content">

        <!-- DATOS -->
        <div id="Datos" class="tab-pane in active">

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Almacén*</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_almacen" onchange="CargarNumerador();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Tipo Operación*</label>
                    <div class="col-sm-10">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_tipop" disabled onclick="ObtenerNombreColumna(this)">
                            <?php foreach ($tiposOper as $t) {
                                $cod = strval($t[0] ?? '');
                                $dsc = strval($t[1] ?? '');
                                echo "<option value=\"".e($cod)."\">".e($dsc)."</option>";
                            } ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Serie*</label>
                    <div class="col-sm-10"><input id="tb_serie" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Número</label>
                    <div class="col-sm-10"><input id="tb_num" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Proveedor*</label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <input id="tbProveedor" type="text" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)">
                            <a class="disabled input-group-addon" onclick="ConsultarProveedor();" style="background-color:#fff;border:0">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Fecha*</label>
                    <div class="col-sm-10"><input id="dp_fecha" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/></div>
                </div>
            </div>

            <!-- Modal Nuevo Detalle -->
            <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalnuevoLabel">Nuevo</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label class="col-form-label">Código</label>
                                <div class="input-group">
                                    <input id="tb_cod" type="text" class="disabled limpiar form-control moderno_tb" onclick="ObtenerNombreColumna(this)"/>
                                    <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalArticulos" onclick="ModalArticulos();" style="background-color:#fff;border:0"><i class="fa fa-search color-buscadores"></i></a>
                                </div>
                            </div>
                            <div class="form-group"><label class="col-form-label">Artículo</label><input type="text" class="form-control" id="tb_articulo" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Unidad de Medida</label><input type="text" class="form-control" id="tb_um" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Cantidad</label><input type="number" min="0" class="form-control" id="tb_cantidad" onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Costo</label><input type="number" min="0" class="form-control" id="tb_costo" onclick="ObtenerNombreColumna(this)"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="InsertarFila()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Artículos -->
            <div class="modal fade" id="modalArticulos" tabindex="-1" role="dialog" aria-labelledby="modalArticuloLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalArticuloLabel">Artículos</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <table id="table_Articulos" class="display" style="width:-webkit-fill-available;">
                                <thead id="thtable_Articulos"><tr><th></th><th>Código</th><th>Nombre</th><th>Familia</th><th>Uni. Med.</th><th>Estado</th></tr></thead>
                                <tbody></tbody>
                            </table>
                            <!-- nota: 6 columnas: cbx, ccod_articulo, cdsc_articulo, linea, uni_medi, estado -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasarArticulo();">Seleccionar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Editar Detalle -->
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modaleditarLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modaleditarLabel">Editar</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group"><label class="col-form-label">Código</label><input id="tb_cod_editar" type="text" class="form-control" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Artículo</label><input type="text" class="form-control" id="tb_articulo_editar" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Unidad de Medida</label><input type="text" class="form-control" id="tb_um_editar" readonly onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Cantidad</label><input type="number" min="0" class="form-control" id="tb_cantidad_editar" onclick="ObtenerNombreColumna(this)"/></div>
                            <div class="form-group"><label class="col-form-label">Costo</label><input type="number" min="0" class="form-control" id="tb_costo_editar" onclick="ObtenerNombreColumna(this)"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="EditarFila()">Editar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla Detalle -->
            <table id="tabla" class="table table-bordered table-striped">
                <colgroup>
                    <col style="width:10%"/><col style="width:20%"/><col style="width:5%"/><col style="width:10%"/><col style="width:10%"/><col style="width:3%"/><col style="width:3%"/>
                </colgroup>
                <thead id="thDetalleIngreso">
                    <tr><th class="text-center">Código</th><th class="text-center">Descripción</th><th class="text-center">Uni. Med.</th><th class="text-center">Cantidad</th><th class="text-center">Costo</th><th></th><th></th></tr>
                </thead>
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
                                    <li><a href="#" onclick="FilterTipo('COMP');">Compra / Ingreso</a></li>
                                    <li><a href="#" onclick="FilterTipo('AJIN');">Ajuste de Ingreso</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>

            <table id="table_id" class="display" style="width:100%;">
                <colgroup><col style="width:1%"/><col style="width:5%"/><col style="width:6%"/><col style="width:6%"/><col style="width:8%"/><col style="width:10%"/><col style="width:20%"/></colgroup>
                <thead id="thTablaIngresos">
                    <tr><th></th><th>Código</th><th>Tipo</th><th>Serie</th><th>Número</th><th>Fecha</th><th>Almacen</th></tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>

            <!-- Tablas auxiliares para exportar a Excel -->
            <div id="ExelDetalleIngreso" style="display:none;">
                <table id="tbDetalleIngreso" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2" cellspacing="0" width="2000">
                    <thead><tr><th class="text-center">Código</th><th class="text-center">Descripción</th><th class="text-center">Unidad Medida</th><th class="text-center">Cantidad</th><th class="text-center">Costo</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="tablePrincipalExportExel" style="display:none;">
                <table id="tableIngresos" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2" cellspacing="0" width="2000">
                    <colgroup><col style="width:5%"/><col style="width:6%"/><col style="width:6%"/><col style="width:8%"/><col style="width:10%"/><col style="width:20%"/></colgroup>
                    <thead><tr><th>Código</th><th>Tipo</th><th>Serie</th><th>Número</th><th>Fecha</th><th>Almacen</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Tablas auxiliares y modal proveedor -->
    <div id="divtable_Articulos" style="display:none;">
        <table id="tbtable_Articulos" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2" cellspacing="0" width="2000">
            <thead><tr><th>Código</th><th>Nombre</th><th>Familia</th><th>Uni. Med.</th><th>Estado</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <div id="divtableProveedor" style="display:none;">
        <table id="tbtableProveedor" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2" cellspacing="0" width="2000">
            <thead><tr><th style="background-color:rgb(33,182,215);color:#fff;">Proveedor</th><th style="background-color:rgb(33,182,215);color:#fff;">Nombre</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>

    <div class="modal" id="modalConsultarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background-color:#d4e1e4;">
                <div class="modal-header">
                    <div class="col-sm-6"><h5 class="modal-title">Seleccione Proveedor</h5></div>
                    <div class="col-sm-6"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div>
                </div>
                <div class="modal-body" style="margin:10px;">
                    <table id="tableProveedor" class="display" style="width:100%;">
                        <colgroup><col style="width:10%"/><col style="width:30%"/><col style="width:60%"/></colgroup>
                        <thead id="thtableProveedor">
                            <tr>
                                <th class="text-center" style="background-color:rgb(33,182,215);color:#fff;"></th>
                                <th style="background-color:rgb(33,182,215);color:#fff;">Proveedor</th>
                                <th style="background-color:rgb(33,182,215);color:#fff;">Nombre</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer" style="margin:10px;">
                    <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodProveedor();">Seleccionar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
