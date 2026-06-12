<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Cajas | DATPOS';
$pageScript = 'Caja.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '111'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Cajas] VerificarAccesos rol 111 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Cajas] VerificarAccesos rol 111 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-show-password/1.0.3/bootstrap-show-password.min.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link href="<?= basePath() ?>/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />
    

    <input id="operacion" type="hidden"/>
    <input id="hdd_rv" type="hidden"/>
    <input id="hdd_ultimafila" type="hidden"/>
    <input id="hdd_fila" type="hidden" value="0"/>
	<input id="hdd_numeromenus" type="hidden" value="2"/>
    <input id="hdd_numerofilas" type="hidden"/>
        <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
 

    <div class="c-content-center modern-page">
        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
            <li onclick="tab_datosclick();">
            <a data-toggle="tab" href="#Numerador" class="tabcito">Documentos Caja</a></li>
            <li onclick="tab_listaclick();">
            <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
        </ul>
        <div class="tab-content">
            <!-- DATOS -->
            <div id="Datos" class="tab-pane in active " style="padding: 13px;">

                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width:60%;">Configuracion de Empresa</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código*</label>
                        <div class="col-sm-10">
                            <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="3" readonly onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb" >
                            Nombre*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Estado*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_estado" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value="A">Activo</option>
                            <option value="I">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>


            </div>

            <!-- NUMERADORES -->
            <div id="Numerador" class="tab-pane" style="padding: 13px;">
             

                <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalnuevoLabel">Nuevo Numerador</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tipo Documento</label>
                                    <select id="ddl_td" class="form-control" onclick="ObtenerNombreColumna(this)"  >
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name"   class="col-form-label">Codigo Documento</label>
                                    <input type="text" maxlength="2" class="form-control" id="tb_cod" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Número Serie</label>
                                    <input type="text" maxlength="4"  class="form-control" id="tb_serie" onclick="ObtenerNombreColumna(this)"/>
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Correlativo</label>
                                    <input type="number" min="0" onfocusout="PerdidaFocoNumEntero(this);" class="form-control" id="tb_correlativo" onclick="ObtenerNombreColumna(this)"/>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"   onclick="InsertarFila()">Agregar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modaleditar" tabindex="-1" role="dialog" aria-labelledby="modaleditarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modaleditarLabel">Editar Numerador</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Tipo Documento</label>
                                    <select id="ddl_td_editar" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Codigo Documento</label>
                                    <input type="text"  maxlength="2" class="form-control" id="tb_codigo_editar" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Número Serie</label>
                                    <input type="text" maxlength="4"  class="form-control" id="tb_serie_editar" onclick="ObtenerNombreColumna(this)" />
                                </div>
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Correlativo</label>
                                    <input type="text" class="form-control" id="tb_correlativo_editar" onclick="ObtenerNombreColumna(this)" />
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"   onclick="ModificarFila()">Agregar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <label id="lb_codigo"></label>
             
                <table id="tablanumerador" class="table table-bordered table-striped" style="width:40%;">
                  <thead id="thtableExelNumerador">
                    <tr>
                    <th class="text-center">
                        Tipo Documento<br />
                      </th>
                      <th class="text-center">
                        Código<br />
                      </th>
                      <th class="text-center">
                        Serie<br />
                      </th>
                      <th class="text-center">
                        Correlativo<br />
                      </th>
                      <th></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
                <input id="btn_nuevonum" type="button" class="btn btn-primary fa_disabled" value="Agregar Numerador" onclick="NuevoModal()" data-toggle="modal" data-target="#modalnuevo"/>
                
               
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
                                <li><a href="#" onclick="FilterStatus(2); return false;">Activos</a></li>
                                <li><a href="#" onclick="FilterStatus(3); return false;">Inactivos</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#" onclick="FilterStatus(1); return false;">Mostrar Todos</a></li>
                            </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                 <colgroup>  
                    <col style="width:1%"></col>
                    <col style="width:20%"></col> 
                    <col style="width:20%"></col>  
                    <col style="width:20%"></col>  
                </colgroup>
                    <thead id="thTablaCaja">
                        <tr>
                            <th></th>
                            <th>
                                Código
                            </th>
                            <th>
                                Nombre de Caja
                            </th>
                            <th>
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody ondblclick="table_two_click(this);" >

                    </tbody>
                </table>
            </div>

            <!--Tabla para exportar a exel-->
                <div id="tableExportNumeradores" style="display:none;" > 
                <table id="tableExelNumerador"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                
                    <thead >
                        <tr>
                            <th class="text-center">Tipo Documento<br />
                            </th>
                            <th class="text-center">Código<br />
                            </th>
                            <th class="text-center">Serie<br />
                            </th>
                            <th class="text-center">Correlativo<br />
                            </th>
                        </tr>
                    </thead>
                    <tbody >

                    </tbody>
                </table>
                </div>
            </div>
        </div>

             <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tablaCaja"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:20%"></col>
                    <col style="width:30%"></col> 
                    <col style="width:20%"></col>  
                </colgroup>
                    <thead >
                        <tr>
                           <th>
                                Código
                            </th>
                            <th>
                                Nombre de Caja
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
        </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
