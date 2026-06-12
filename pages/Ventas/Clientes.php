<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Asociados | DATPOS';
$pageScript = 'Cliente1.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1012'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Clientes] VerificarAccesos rol 1012 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Clientes] VerificarAccesos rol 1012 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

    <input id="operacion" type="hidden"/>
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

                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px;width: 60%;">Información General</h4>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código RUC SUNAT*</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input id="tb_codigo" type="text" inputmode="numeric" class="disabled limpiar form-control moderno_tb" maxlength="11" disabled title="RUC (11 dígitos) o DNI (8 dígitos)" onchange="BuscarDatosSunatPorCodigo()" onclick="ObtenerNombreColumna(this)" />
                                <span class="input-group-btn">
                                    <button id="btn_buscar_sunat" class="btn btn-lookup btn-lookup-sunat" type="button" title="Consultar SUNAT con el código ingresado" onclick="BuscarDatosSunatPorCodigo()">
                                        <i class="fa fa-search"></i> SUNAT
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Nombre*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" type="text" maxlength="50" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <!--<div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Tip. Doc.*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="tb_tipDoc" disabled   onclick="ObtenerNombreColumna(this)" > 
                            <option value="-">OTROS</option>
                            <option value="1">DNI</option>
                            </select>
                        </div>
                    </div>-->

                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            DNI</label>
                        <div class="col-sm-10">
                            <div class="input-group">
                                <input id="tb_numdoc" type="text" inputmode="numeric" maxlength="8" class="disabled limpiar form-control moderno_tb" disabled onchange="BuscarDatosDni()" onclick="ObtenerNombreColumna(this)" />
                                <span class="input-group-btn">
                                    <button id="btn_buscar_dni" class="btn btn-lookup btn-lookup-dni" type="button" title="Consultar DNI" onclick="BuscarDatosDni()">
                                        <i class="fa fa-search"></i> DNI
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Teléfono</label>
                        <div class="col-sm-10">
                            <input id="tb_telefono" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Correo</label>
                        <div class="col-sm-10">
                            <input id="tb_mail" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
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
                    <div class="col-sm-6 col-xs-12"> 
                       <label class="col-sm-2 moderno_lb">
                            Asociado*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="cbProveedor" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="1">Proveedor</option>
                            <option value ="0">Cliente</option>
                            <option value ="2">Otros</option>
                            </select>
                        </div>
                    </div>
                </div> 
                <div class="row" style="margin-top: 20px;">
                      
                    <div class="col-sm-6 col-xs-12"> 
                       <label class="col-sm-2 moderno_lb">
                            Persona*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="tb_tipo_coa" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="1">Natural</option>
                            <option value ="0">Juridica</option> 
                            </select>
                        </div>
                    </div>
                </div> 
                <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 80px; width:60%;">Direcciones</h4>

                <div class="row" style="margin-top: 20px;">
                  <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Dirección*</label>
                        <div class="col-sm-10">
                            <input id="tb_direccion" type="text" maxlength="50" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Departamento</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" onchange="CargarProvincia();" id="txtDepartamento" disabled onclick="ObtenerNombreColumna(this)" >
                            </select>
                        </div>
                    </div> 
                     </div>
                     <div class="row" style="margin-top: 20px;">
                        <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Provincia</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" onchange="CargarDistrito();" id="txtProvincia" disabled onclick="ObtenerNombreColumna(this)" >
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Distrito</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" onchange="CargarUbigeo();" id="txtDistrito" disabled onclick="ObtenerNombreColumna(this)" >
                            </select>
                        </div>
                    </div> 
                </div>
                <div class="row" style="margin-top: 20px;">
                   
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            País</label>
                        <div class="col-sm-10">
                            <input id="tb_pais" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
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
                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                   <colgroup>
                        <col style="width:1%"></col>
                        <col style="width:10%"></col>
                        <col style="width:25%"></col>
                        <col style="width:10%"></col>
                        <col style="width:25%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col>
                    </colgroup>
                    <thead id="thtableClientes">
                        <tr>
                            <th></th>
                            <th>
                                Código (RUC)
                            </th>
                             <th>
                                Descripción
                            </th>
                            <th>
                                DNI
                            </th>
                            <th>
                                Dirección
                            </th>
                            <th>
                                Asociado
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
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tableClientes"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
              
                    <thead >
                        <tr>
                           <th>
                                Código (RUC)
                            </th>
                             <th>
                                Descripción
                            </th>
                            <th>
                                DNI
                            </th>
                            <th>
                                Dirección
                            </th>
                            <th>
                                Asociado
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
