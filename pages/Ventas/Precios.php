<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Precios | DATPOS';
$pageScript = 'Precio1.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1013'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Precios] VerificarAccesos rol 1013 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Precios] VerificarAccesos rol 1013 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<!--<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>-->
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<!--<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />-->
<link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

    <input id="ValorIgv" type="hidden"/>
    <input id="operacion" type="hidden"/>
    <input id="hdd_rv" type="hidden"/>
    <input id="hdd_ultimafila" type="hidden"/>
    <input id="hdd_fila" type="hidden" value="0"/>
    <input id="hdd_numeromenus" type="hidden" value="2"/>
    <input id="hdd_numerofilas" type="hidden"/>

 

    <div class="c-content-center modern-page">
        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
            <li onclick="tab_datosclick();">
            <a data-toggle="tab" href="#Precios" class="tabcito">Precios</a></li>
            <li onclick="tab_listaclick();">
            <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
        </ul>
        <div class="tab-content">
            <!-- DATOS -->
            <div id="Datos" class="tab-pane in active " style="padding: 13px;">

                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px;width: 60%;">Información General</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código*</label>
                        <div class="col-sm-10">
                            <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="8" readonly onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Descripción*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                 
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Ini.Vigencia*</label>
                        <div class="col-sm-10"> 
                            <input id="dp_Ini" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" /> 
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Estado*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_estado" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="1">Activo</option>
                            <option value ="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Fin.Vigencia*</label>
                        <div class="col-sm-10">
                            <input type="text" id="dp_Fin" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" /> 
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;">

                </div>
 
            </div>

            <!-- PRECIOS -->
            <div id="Precios" class="tab-pane" style="padding: 13px;">
                <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalnuevoLabel">Nuevo Precio</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Código Artículo</label>
                                    <div class="input-group">
                                        <input id="tb_cod" type="text" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                         <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalArticulos" onclick="ModalArticulos();" style="background-color: #ffffff;border:0px">
                                          <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
                                         </a> 
                                    </div>
                                </div>
                                <div  class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descripción Artículo</label>
                                        <input id="tb_articulo" type="text" class="form-control" readonly onclick="ObtenerNombreColumna(this)" >
                                </div>
                                <!--<div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Costo</label>
                                        <input id="tb_costo" type="text" class="form-control" readonly>
                                </div> -->
                               

                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Precio Unitario</label>
                                    <input type="number" class="form-control" id="tb_pu" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descuento Máximo</label>
                                    <input type="number" class="form-control" id="tb_dma" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div style="display:none;" class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descuento Mínimo</label>
                                    <input type="number" class="form-control" id="tb_dmi" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>
                               
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"  onclick="InsertarFila()">Agregar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalArticulos" tabindex="-1" role="dialog" aria-labelledby="modalArticuloLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalArticuloLabel">Artículos</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <table id="table_Articulos" class="display" style="width:100%;">
                                    <thead>
                                        <tr>
                                            <th class="text-center">
                                                  
                                            </th>
                                            <th>
                                                Código
                                            </th>
                                            <th>
                                                Nombre de Artículo
                                            </th>
                                            <th>
                                                Familia
                                            </th>
                                            <th>
                                                Unidad de medida
                                            </th>
                                            <th>
                                                Estado
                                            </th>
                                            <!--<th>
                                                Costo
                                            </th>-->
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasarArticulo();">Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="H1">Editar Precio</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Código Artículo</label>
                                    <input type="text" class="form-control" id="tb_cod_editar" readonly onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descripción Artículo</label>
                                        <input id="tb_articulo_editar" type="text" class="form-control" readonly onclick="ObtenerNombreColumna(this)" >
                                </div>
 

                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Precio Unitario</label>
                                    <input type="number" class="form-control" id="tb_pu_editar" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descuento Máximo</label>
                                    <input type="number" class="form-control" id="tb_dma_editar" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div style="display:none;" class="form-group">
                                    <label for="recipient-name" class="col-form-label">Descuento Mínimo</label>
                                    <input type="number" class="form-control" id="tb_dmi_editar" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)" />
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" onclick="EditarFila()">Grabar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <label id="lb_codigo"></label>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-4 col-xs-12">
                        <div class="col-sm-12">
                            <select class="disabled limpiar form-control moderno_tb" id="cboTipFiltro" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="1">Top 50</option>
                            <option value ="2">Sin Filtro</option>
                            <option value ="3">Articulo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-12"> 
                            <input id="txtArticulo" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" /> 
                        </div>
                    </div>
                    <div class="col-sm-2 col-xs-12">
                        <div class="col-sm-12"> 
                            <button type="button" class="btn btn-primary" onclick="CargarTablaPrecios()" >Buscar</button> 
                        </div>
                    </div>
                </div>

                <table id="tablaprecios" class="table table-bordered table-striped">
                <colgroup>  
                    <col style="width:10%"></col> 
                    <col style="width:20%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col> 
                    <col style="width:2%"></col>  
                    <col style="width:2%"></col>
                    <col ></col>
                </colgroup>
                  <thead id="thtableDetalleListPrec">
                    <tr>
                      <th class="text-center">
                        Código<br />
                      </th>
                      <th class="text-center">
                        Artículo<br />
                      </th>
                      <th class="text-center">
                        Precio Unitario<br />
                      </th>
                      <th class="text-center">
                        Descuento Máximo<br />
                      </th>
                      <th style="display:none;" class="text-center">
                        Descuento Mínimo<br />
                      </th>
                      <th></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>

                  </tbody>
                </table>

                <input onclick="LimpiarModal();" id="btn_nuevonum" type="button" class="btn btn-primary fa_disabled" value="Agregar Precio"  />

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
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true"><img src="<?= basePath() ?>/assets/Styles/img/filtro.png" style="WIDTH:14PX;MARGIN-RIGHT:5PX;" />FILTROS <span class="caret"></span></a>
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
                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                 <col style="width:1%"></col>
                        <col style="width:15%"></col>
                        <col style="width:20%"></col> 
                        <col style="width:15%"></col> 
                        <col style="width:15%"></col> 
                        <col style="width:10%"></col>
                    <thead id="thtablePrecio">
                        <tr>
                            <th></th>
                            <th>
                                Código
                            </th>
                            <th>
                                Descripción
                            </th>
                            <th>
                                Desde
                            </th>
                            <th>
                                Hasta
                            </th>
                            <th>
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody ondblclick="table_two_click(this);"  >
                    </tbody>
                </table>
            </div>
        </div>

         <!--Tabla Detalle de la lista de precios para exportar a exel-->
                <div id="tableDetalleListPrecExel" style="display:none;" > 
                <table id="tableDetalleListPrec"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:10%"></col> 
                    <col style="width:20%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>    
                </colgroup>
                    <thead >
                        <tr>
                           <th class="text-center">
                        Código<br />
                      </th>
                      <th class="text-center">
                        Artículo<br />
                      </th>
                      <th class="text-center">
                        Precio Unitario<br />
                      </th>
                      <th class="text-center">
                        Descuento Máximo<br />
                      </th>
                      <th style="display:none;" class="text-center">
                        Descuento Mínimo<br />
                      </th>
                        </tr>
                    </thead>
                    <tbody >

                    </tbody>
                </table>
                </div>

         <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tablePrecio"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:5%"></col> 
                    <col style="width:40%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col>  
                </colgroup>
                    <thead >
                        <tr>
                           <th>
                                Código
                            </th>
                            <th>
                                Descripción
                            </th>
                            <th>
                                Desde
                            </th>
                            <th>
                                Hasta
                            </th>
                            <th>
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody >

                    </tbody>
                </table>
                </div>
    </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
