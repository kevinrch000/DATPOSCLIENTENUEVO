<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Roles | DATPOS';
$pageScript = 'Roles.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '112'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Roles] VerificarAccesos rol 112 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Roles] VerificarAccesos rol 112 fallo: ' . $e->getMessage());
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
  

   
    <style>
     .row {
        margin-right: 0px; 
        margin-left: 0px; 
        margin-bottom: 0px;
    }
  
    </style>

    <div class="c-content-center modern-page">
        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
            <li onclick="tab_datosclick();">
            <a data-toggle="tab" href="#Accesos" class="tabcito">Accesos</a></li>
            <li onclick="tab_listaclick();">
            <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
        </ul>
        <div class="tab-content">
            <!-- DATOS -->
            <div id="Datos" class="tab-pane in active " style="padding: 13px;">

                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width:60%;">Información General</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código</label>
                        <div class="col-sm-10">
                            <input id="tb_codigo" onclick="ObtenerNombreColumna(this)" class=" limpiar form-control moderno_tb" maxlength="3" disabled />
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
                            <option value ="1">Activo</option>
                            <option value ="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>


            </div>

            <!-- ACCESOS -->
            <div id="Accesos" class="tab-pane" style="padding: 13px;">
             
                <label id="lb_codigo"></label>
                
                 
                <div class="input-group">
                        <input  class="limpiar_checked disabled" onclick="CkMarcarTodo()" style="margin-top:10px;cursor: default;" type="checkbox" id="idCkMarcarTodo" disabled>
                    <label style="padding-left:10px;" id="">Marcar todo</label>
                </div>
                <div class="input-group">
                        <input  class="disabled" onclick="CkDesmarcarTodo()" style="margin-top:10px;cursor: default;" type="checkbox" id="idCkDesmarcarTodo" disabled>
                    <label style="padding-left:10px;" id="">Desmarcar todo</label>
                </div>

              <div id="ColumnaRoles" >
                    
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
                    <col style="width:20%"></col>
                    <col style="width:40%"></col> 
                    <col style="width:20%"></col>  
                </colgroup>
                    <thead id="thTablaRoles">
                        <tr>
                            <th></th>
                            <th>
                                Código
                            </th>
                            <th>
                                Nombre
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
 
            </div>
        </div>

             <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tablaRoles"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                    <thead >
                        <tr>
                           <th>
                                Código
                            </th>
                            <th>
                                Nombre
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
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
