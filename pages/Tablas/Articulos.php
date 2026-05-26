<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../DA/DAFamilia.php';
require_once __DIR__ . '/../../DA/DAUnidadMedida.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Artículos | DATPOS';
$pageScript = 'Articulo.js';
$showCrudButtons = true;

// Pre-cargar combos (paridad con VB Articulos.aspx.vb -> CargarFamilias / CargarUnidadesMedidasActivas)
$familias = array();
try {
    foreach ((new DAFamilia())->consultarFamiliasActivas($o) as $f) {
        // sp_consultafamiliasactivas devuelve: ccod_lin, cdsc_lin (puede variar el shift)
        $code = strval($f[0] ?? '');
        $desc = strval($f[1] ?? '');
        if ($code === '') { continue; }
        $familias[] = array('ccod_lin' => $code, 'cdsc_lin' => $desc !== '' ? $desc : $code);
    }
} catch (Exception $e) {
    error_log('[Articulos] CargarFamilias fallo: '.$e->getMessage());
}

$unidades = array();
try {
    $rows = Database::selectStoredTenant('webDatpos_consultarUnidadMedida', array('@ccod_cia' => $o->ccod_empresa), $o);
    foreach ($rows as $f) {
        // webDatpos_consultarUnidadMedida (FIX_23): id, ccod, csim, cdsc, cstatus, ccod_tributario
        $cs = strval($f[4] ?? '');
        if ($cs !== '' && $cs !== 'A' && $cs !== 'Activo' && $cs !== '1') { continue; }
        $code = strval($f[1] ?? '');
        $desc = strval($f[3] ?? '');
        if ($code === '') { continue; }
        $unidades[] = array('ccod_unimed' => $code, 'cdsc_unimed' => $desc !== '' ? $desc : $code);
    }
} catch (Exception $e) {
    error_log('[Articulos] CargarUnidadesMedidasActivas fallo: '.$e->getMessage());
}

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1005'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Articulos] VerificarAccesos rol 1005 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Articulos] VerificarAccesos rol 1005 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-show-password/1.0.3/bootstrap-show-password.min.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
<link href="<?= basePath() ?>/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />


<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
     <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
 
 <style>

    .cuadrado{
        height: 75px;
        width: 75px;
        padding: 0px;        
        margin: 3px;
    }
    
    .cuadrado_desc{
        font-size: 10px;        
    }    
    
    .sombreado{
        box-shadow: 0 0 8px 4px rgba(51, 51, 51, 75%) !important;        
    }  
    
    .sombreado_mp{
        box-shadow: 0 0 8px 4px rgba(51, 51, 51, 75%);        
    }     
    
    .precio{
        position: absolute;
        font-size: 25px; 
        left: 230px;
        bottom: 3px;    
    }      
    
</style>
 <input id="hdd_numerofilas" type="hidden"/>
  <input id="NumFilaSelc" type="hidden"/>


 <input id="hdd_numerofilasVariante" type="hidden"/>
  <input id="hdd_ultimafilaVariante" type="hidden"/>
  <input id="hdd_filaVariante" type="hidden" value="0"/>


    <input id="operacion" type="hidden"/>
    <input id="hdd_rv" type="hidden"/>
    <input id="hdd_ultimafila" type="hidden"/>

    <input id="hdd_fila" type="hidden" value="0"/>
	<input id="hdd_numeromenus" type="hidden" value="2"/>

    <div class="c-content-center modern-page">

        <div class="modern-page-header">
            <div class="mph-icon"><i class="material-icons">inventory_2</i></div>
            <div class="mph-text">
                <h1>Artículos</h1>
                <p>Mantenimiento del catálogo: familia, unidad de medida, variantes e impuestos.</p>
            </div>
            <div class="mph-spacer"></div>
            <span class="mph-chip"><i class="material-icons">table_chart</i>Tablas</span>
        </div>

        <ul class="nav nav-tabs" style="">
            <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
            <li onclick="tab_datosclick();"><a data-toggle="tab" href="#Variantes" class="tabcito">Variantes</a></li>
            <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
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
                            <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="15" readonly onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Código Articulo Sunat</label>
                        <div class="col-sm-10">
                            <input id="tb_codigoSunat" class="disabled limpiar form-control moderno_tb" maxlength="8" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                     
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Nombre*</label>
                        <div class="col-sm-10">
                            <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" maxlength="100"  disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">Familia*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_familia" disabled onclick="ObtenerNombreColumna(this)">
                                <option value=""></option>
                                <?php foreach ($familias as $fa): ?>
                                    <option value="<?= e($fa['ccod_lin']) ?>"><?= e($fa['cdsc_lin']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                     
                </div>
                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">Unidad Medida*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_um" disabled onclick="ObtenerNombreColumna(this)">
                                <option value=""></option>
                                <?php foreach ($unidades as $u): ?>
                                    <option value="<?= e($u['ccod_unimed']) ?>"><?= e($u['cdsc_unimed']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">Estado*</label>
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
                            Stock Minimo</label>
                        <div class="col-sm-10">
                            <input id="tb_stock_min" class="disabled limpiar form-control moderno_tb" type="number" min="0" disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Stock Maximo</label>
                        <div class="col-sm-10">
                            <input id="tb_stock_max" class="disabled limpiar form-control moderno_tb" type="number" min="0" isabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                     
                 </div>

                <div class="row" style="margin-top: 20px;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Tipo de Artículo*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ddl_tipArticulo" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="B">Bien</option>
                            <option value ="P">Producto</option>
                            <option value ="S">Servicio</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">IGV*</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ckbigv" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="18">Habilitado</option>
                            <option value ="0">Deshabilitado</option>
                            </select>
                        </div>
                    </div> 
                </div>

                <h4 class="modern-section-title" style="margin-top: 36px; display: none;"><i class="material-icons">attach_money</i>Impuestos del Artículo</h4>

                <div class="row" style="margin-top: 20px;DISPLAY: NONE;">
                     
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">ISC</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="ckbisc" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="1">Habilitado</option>
                            <option value ="0">Deshabilitado</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;DISPLAY: NONE;">
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">Tipo ISC</label>
                        <div class="col-sm-10">
                            <select class="disabled limpiar form-control moderno_tb" id="txtTipoISC" disabled onclick="ObtenerNombreColumna(this)" >
                            <option value ="01">Al Valor</option>
                            <option value ="02">Especifico</option>
                            <option value ="03">Al Valor según precio de venta al publico</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Porcentaje ISC</label>
                        <div class="col-sm-10">
                            <input id="txtPorcentajeISC" class="disabled limpiar form-control moderno_tb"   disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 20px;DISPLAY: NONE;"> 
                    <div class="col-sm-6 col-xs-12">
                        <label class="col-sm-2 moderno_lb">
                            Monto ISC</label>
                        <div class="col-sm-10">
                            <input id="txtMontoISC" class="disabled limpiar form-control moderno_tb"  disabled onclick="ObtenerNombreColumna(this)" />
                        </div>
                    </div>
                </div>

                <h4 class="modern-section-title" style="margin-top: 36px;"><i class="material-icons">image</i>Imagen del Artículo</h4>
                <div class="row" style="margin-top: 20px;">
                     
                   
                   <div class="col-sm-6 col-xs-12">
                    <div class="col-sm-12">
                        <!--<div class="input-group">
                           <input class="disabled"  style="margin-top:10px;cursor: default;" type="checkbox" id="ckbigv" disabled>
                         
                        <label style="padding-left:10px;" class="moderno_lb">Afecto al impuesto a las ventas</label>
                        </div>
                      
                        <div class="input-group">
                            <input class="disabled"  style="margin-top:10px;cursor: default;" type="checkbox" id="ckbisc" disabled>
                            <label style="padding-left:10px;" class="moderno_lb">Afecto al impuesto selectivo al consumo</label>
                        </div>-->
                      </div>
                    </div>


                </div>

                 
                 
                 <div class="row" style="margin-top: -40px;">
                    <div class="col-sm-8">
                    <div class="input-group">
                    <input name="file-input" id="file-input" type="file" class="disabled form-control" disabled style="width:100%;" />
                       
                    </div>
                    </div>
                   <div class="col-sm-6 col-xs-12">
                    
                    </div>
                </div>
                <input id="Button1" class="disabled btn btn-info" onclick="BorarImagen()" type="button"  style="cursor: auto;" value="Borrar Imagen" />

 
   <br />

   <div id="BotonCerrar" style="border: 0;background-color: white;" class="col-md-3">
   <!--<article style="text-align: -webkit-center;">-->
   <!--<h1 class="disabled"><a  style="background-color: #ffffff;border:0px;margin:10px" class="disabled precio" disabled title="" onclick="BorarImagen()" ><i class="fa fa-times disabled" aria-hidden="true"></i></a>
   </h1>-->
   <img id="imgSalida" width="30%" height="30%" src="" />
   <!--</article>-->
   </div>
</div>

            <!-- VARIANTES -->
            <div id="Variantes" class="tab-pane in active " style="padding: 13px;">
            <label id="lb_codigoVari"></label>
            <div class="row">
            <div class="col-md-6">
             <table id="tblVariantes" class="table table-bordered table-striped" style="width:70%;">
                  <thead id="thVariantes">
                    <tr>  
                      <th class="text-center">
                        Variante<br />
                      </th>
                      <th></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody onclick="table_one_clickVariante(this);">
                  </tbody>
                </table>
                 <input onclick="NuevoVariante()" id="btnNuevoVariante" type="button" class="btn btn-primary fa_disabled" value="Agregar Variante" data-toggle="modal" data-target="#modalVariante"/>
            </div>
              
            <div class="col-md-6">
               <table id="tblDetalleVariantes" class="table table-bordered table-striped" style="width:70%;">
                  <thead id="thDetalleVariantes">
                    <tr>  
                      <th class="text-center">
                        Detalle Variante<br />
                      </th>
                      <th></th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody  onclick="table_one_clickDetalleV(this);">
                  </tbody>
                </table>
                <input onclick="NuevoDetalleVariante()" id="btnNuevoDetalleV" type="button" class="btn btn-primary fa_disabled" value="Agregar Detalle" data-toggle="modal" data-target="#modalDetalleVariante"/>
            </div>
          </div>

          <!--Tabla Variante para exportar a exel-->
                <div id="tableVarianteExportarExel" style="display:none;" > 
                <table id="tableVariante"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:20%"></col> 
                </colgroup>
                    <thead >
                        <tr>
                            <th class="text-center">Variante</th>
                        </tr>
                    </thead>
                    <tbody >
                    </tbody>
                </table>
                </div>
                <!--Tabla DetalleVariante para exportar a exel-->
                <div id="tableDetalleVarianteExportarExel" style="display:none;" > 
                <table id="tableDetalleVariante"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:20%"></col> 
                </colgroup>
                    <thead >
                        <tr>
                            <th class="text-center">Detalle Variante</th>
                        </tr>
                    </thead>
                    <tbody >
                    </tbody>
                </table>
                </div>
           <!-- Modal Variante -->
                <div class="modal fade" id="modalVariante" tabindex="-1" role="dialog" aria-labelledby="modalvariante_nuevoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="H1">Nueva Variante</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body"> 
                               
                                 <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Variante</label>
                                        <input id="txtCodVari" maxlength="25"  type="text" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"   onclick="InsertarVariante()" >Agregar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Editar Variante -->
                <div class="modal fade" id="modalEditarVariante" tabindex="-1" role="dialog" aria-labelledby="modalvariante_nuevoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="H3">Editar Variante</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body"> 
                                 <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Variante</label>
                                        <input id="txtEdtCodVari" maxlength="25" type="text" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"   onclick="BtnEditarVariante()" >Editar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            


                 <!-- Modal Detalle Variante -->
                <div class="modal fade" id="modalDetalleVariante" tabindex="-1" role="dialog" aria-labelledby="modalvariante_nuevoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                        <div class="modal-header">
                                <h5 class="modal-title" id="H2">Nuevo Detalle de Variante</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                           <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Variante</label>
                                        <input id="txtRefNomVari" disabled type="text" class="readonl form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                               
                                 <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Detalle Variante</label>
                                        <input id="txtConDetVari" maxlength="25" type="text" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"   onclick="InsertarDetalleVariante()">Agregar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Editar Detalle Variante -->
                <div class="modal fade" id="modalEditarDetalleVariante" tabindex="-1" role="dialog" aria-labelledby="modalvariante_nuevoLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                        <div class="modal-header">
                                <h5 class="modal-title" id="H4">Editar Detalle de Variante</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                           <div class="modal-body">
                                <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Variante</label>
                                        <input id="txtEdtRefNomVari" disabled type="text" class="readonl form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                               
                                 <div class="form-group">
                                    <label for="recipient-name" class="col-form-label">Nombre Detalle Variante</label>
                                        <input id="txtEdtConDetVari"  maxlength="25" type="text" class="form-control" onclick="ObtenerNombreColumna(this)" >
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary"  onclick="BtnEditarDetalleVariante()">Editar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
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
                                <li><a href="#" onclick="FilterStatus(2);">Activos</a></li>
                                <li><a href="#" onclick="FilterStatus(3);">Inactivos</a></li>
                                <li><a href="#" onclick="FilterStatus(1);">Mostrar Todos</a></li>
                            </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
                <table id="table_id"  class="display" style="width:100%;">
                 <colgroup>  
                    <col style="width:1%"></col>
                    <col style="width:15%"></col>
                    <col style="width:30%"></col> 
                    <col style="width:15%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:5%"></col>
                    </colgroup>
                    <thead id="thTablaArticulo">
                        <tr>
                            <th>
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
                                Uni. Med.
                            </th>
                            <th>
                                Tip. Art.
                            </th>
                            <th>
                                Estado
                            </th>
                             <th>
                                IGV
                            </th>
                        </tr>
                    </thead>
                    <tbody ondblclick="table_two_click(this);" >

                    </tbody>
                </table>


                 



                <!--Tabla para exportar a exel-->
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tableArticulo"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                    <col style="width:15%"></col>
                    <col style="width:30%"></col> 
                    <col style="width:15%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:5%"></col>
                </colgroup>
                    <thead >
                        <tr>
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
                                Tipo Artículo
                            </th>
                            <th>
                                Estado
                            </th>
                             <th>
                                IGV
                            </th>
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
