<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Oper. de Almacen | DATPOS';
$pageScript = 'TiposOperacion.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1003'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[TiposOperacion] VerificarAccesos rol 1003 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[TiposOperacion] VerificarAccesos rol 1003 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
 

    <input id="operacion" type="hidden"/>
    <input id="hdd_id" value="0" type="hidden"/>
    <input id="hdd_ultimafila" type="hidden"/>
    <input id="hdd_fila" type="hidden" value="0"/>
	<input id="hdd_numeromenus" type="hidden" value="1"/>
    <input id="hdd_numerofilas" type="hidden"/>

    <div class="c-content-center modern-page">
        <div class="modern-page-header">
            <div class="mph-icon"><i class="material-icons">swap_horiz</i></div>
            <div class="mph-text">
                <h1>Operaciones de Almacén</h1>
                <p>Tipos de operación (ingresos, salidas y transferencias) para el módulo de almacén.</p>
            </div>
            <div class="mph-spacer"></div>
            <span class="mph-chip"><i class="material-icons">tune</i>Administración</span>
        </div>
        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
            <li onclick="tab_listaclick();">
            <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
        </ul>
        <div class="tab-content">
            <!-- DATOS -->
            <div id="Datos" class="tab-pane in active " style="padding: 13px;">

                <h4 class="modern-section-title"><i class="material-icons">info</i>Información General</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código*</label>
                        <div class="col-sm-10">
                            <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="20" readonly onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb" >
                            Nombre*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" maxlength="25" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Tipo*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_flagtipo" disabled onclick="ObtenerNombreColumna(this)"> 
                            <option value ="I">Ingresos</option>
                            <option value ="S">Salidas</option> 
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Estado*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_estado" disabled onclick="ObtenerNombreColumna(this)"> 
                            <option value ="A">Activo</option>
                            <option value ="I">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                    <div class="col-sm-6">
                        <div class="input-group">
                          <input class="disabled"  style="margin-top:10px;cursor: default;" type="checkbox" id="chkTipTransf">
                         <label style="padding-left:10px;" class="moderno_lb">Tipo Transferencia</label>
                         </div>
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
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="true"><img src="<?= basePath() ?>/assets/Styles/img/filtro.png" style="WIDTH:14PX;MARGIN-RIGHT:5PX;" />FILTROS <span class="caret"></span></a>
                            <ul class="dropdown-menu">

                            </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
                <table id="table_id" class="display" style="width:100%;">
                <colgroup>  
                     <col style="width:1%"></col> 
                    <col style="width:15%"></col> 
                    <col style="width:40%"></col> 
                    <col style="width:15%"></col>
                    <col style="width:15%"></col> 
                </colgroup>
                    <thead id="thTablaOperAlmacen">
                        <tr>
                            <th></th>
                            <th>Código</th>
                            <th>Descripcion</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody ondblclick="table_two_click(this);" >

                    </tbody>
                </table>

                <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tableOperAlmacen"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:15%"></col> 
                    <col style="width:40%"></col> 
                    <col style="width:15%"></col>
                    <col style="width:15%"></col> 
                </colgroup>
                    <thead >
                        <tr>
                            <th>Código</th>
                            <th>Descripcion</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody >

                    </tbody>
                </table>
                </div>
            </div>

        </div>
    </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
