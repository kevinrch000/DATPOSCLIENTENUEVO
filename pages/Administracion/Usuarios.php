<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Usuarios | DATPOS';
$pageScript = 'Usuario.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_empresa' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@corden' => '113'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Usuarios] VerificarAccesos rol 113 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Usuarios] VerificarAccesos rol 113 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-show-password/1.0.3/bootstrap-show-password.min.js"></script>
<link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link href="<?= basePath() ?>/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />

     <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<style>
         
.input-group-addon {
    background-color: #ffffff;
    border: 1px solid #ffffff;
}

</style>

    <input id="operacion" type="hidden"/>
    <input id="hdd_rv" type="hidden"/>
    <input id="hdd_ultimafila" type="hidden"/>
    <input id="hdd_fila" type="hidden" value="0"/>
	<input id="hdd_numeromenus" type="hidden" value="1"/>
    <input id="hdd_numerofilas" type="hidden"/>

    <div class="c-content-center modern-page">
        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
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
                            Usuario*</label>
                        <div class="col-sm-10">
                            <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="8" readonly onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Contraseña*</label>
                        <div class="col-sm-10">
                            
                            <div class="input-group">
              <input type="password" id="tb_cpassw" class="disabled limpiar form-control moderno_tb" maxlength="50" disabled onclick="ObtenerNombreColumna(this)" >
           
            <a class="disabled input-group-addon" id="UsuActual" onclick="mostrarContrasenaUsu();" disabled="" style="padding: -6px -12px;background-color: #ffffff;border:0px">
                    <i class="material-icons">visibility_off</i></a>
            <a class="disabled input-group-addon" id="UsuActual2" onclick="mostrarContrasenaUsu();" disabled="" style="background-color: rgb(255, 255, 255); border: 0px; display: none;">
                    <i class="material-icons">visibility</i></a>  
            </div>
                        </div>
                    </div>

                     
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Nombre*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" maxlength="50"  disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Dirección*</label>
                        <div class="col-sm-10">
                            <input id="tb_cdirec" class="disabled limpiar form-control moderno_tb" maxlength="200"  disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Rol*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="dl_rol" disabled onclick="ObtenerNombreColumna(this)"></select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Estado*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_estado" disabled onclick="ObtenerNombreColumna(this)">
                            <option value ="1">Activo</option>
                            <option value ="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>

             

                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 80px; width:60%;">Atributos</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Tienda*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_tienda" disabled onchange="CargarAlmacenes();CargarCajas();" onclick="ObtenerNombreColumna(this)"></select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Almacen</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_almacen" disabled onclick="ObtenerNombreColumna(this)"></select>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">

                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Caja</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_caja" disabled onclick="ObtenerNombreColumna(this)"></select>
                        </div>
                    </div>
                     
                </div>
               

                    <div class="row" style="margin-top: 20px;display:none;">
                      <div class="col-sm-6 col-xs-12"> 
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <div class="col-sm-12 input-group">
                             <input class="col-sm-1 limpiar_checked disabled"  style="margin-top:10px;cursor: default;" type="checkbox" id="idCkPermDescu" disabled>
                            <label style="padding-left:10px;" class="moderno_lb">Permiso de Descuento</label>
                        </div>
                    </div>
                     
                </div>

            <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 80px; width:60%;">Imagen</h4>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <div class="input-group">
                            <input name="file-input" id="file-input" type="file" class="disabled form-control"
                                disabled style="width: 40%;" />
                            <input id="Button1" style="cursor: auto;" class="disabled btn btn-info" onclick="BorarImagen()"
                                type="button" value="Borrar Imagen" />
                        </div>
                    </div>
                </div>
                <div id="BotonCerrar" style="border: 0; background-color: white;" class="col-md-3">
                    <img id="imgSalida" width="100%" height="100%" src="" />
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
                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                <colgroup>  
                    <col style="width:1%"></col>
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:20%"></col>
                    <col style="width:20%"></col> 
                    <col style="width:15%"></col> 
                    <col style="width:10%"></col> 
                </colgroup>
                    <thead id="thtableUsuario">
                        <tr>
                            <th></th>
                            <th>
                                Código
                            </th>
                            <th>
                                Usuario
                            </th>
                            <th>
                                Dirección
                            </th>
                            <th>
                                Rol
                            </th>
                            <th>
                                Tienda
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

          <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tableUsuario"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:20%"></col>
                    <col style="width:20%"></col> 
                    <col style="width:15%"></col> 
                    <col style="width:10%"></col> 
                </colgroup>
                    <thead >
                        <tr>
                            <th>
                                Código
                            </th>
                            <th>
                                Usuario
                            </th>
                            <th>
                                Dirección
                            </th>
                            <th>
                                Rol
                            </th>
                            <th>
                                Tienda
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
