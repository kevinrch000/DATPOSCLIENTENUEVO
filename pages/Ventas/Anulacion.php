<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Anulación | DATPOS';
$pageScript = 'Anulacion.js';
$showCrudButtons = false;
$showConsultButtons = true;
$loadConsultAssets = false;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1018'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Anulacion] VerificarAccesos rol 1018 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Anulacion] VerificarAccesos rol 1018 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-show-password/1.0.3/bootstrap-show-password.min.js"></script>
<link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css"/>
<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">

<script src="<?= basePath() ?>/assets/Javascript/Filtros.js" type="text/javascript"></script>
<input id="hhd_vTienda" type="hidden" value="<?= e($o->ccod_tiend ?? '') ?>"/>
    <input id="hhd_vAlmacen" type="hidden" value="<?= e($o->ccod_almacen ?? '') ?>"/>
    <input id="hhd_vCaja" type="hidden" value="<?= e($o->ccod_caja ?? '') ?>"/>

    <!--Diseño de Texto Flotante-->
    <link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />
    <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
    <!--Diseño de Botones-->
    <link href="<?= basePath() ?>/assets/Styles/disenoBotones.css" rel="stylesheet" type="text/css" />


<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/> 

 <input id="idfact" type="hidden"/> 

<div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
        <!-- DATOS -->
            <div id="Datos" class="tab-pane in active "  >
            <!-- Buscadores --> 
              <div class="row" >
              <div class="col-sm-4" style="padding-top:10px;" >
                      
                          <div class="floating-label">  
                           <select class="limpiar form-control moderno_tb floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="txtTienda"   >
                                </select>
                            <label class="floating-select2">Tienda*</label>
                      
                           </div>
                    </div>
                <div class="col-sm-4" style="padding-top:10px;" >

                      <span  class="has-float-label"   >
                                <input id="txtfchDesde"   maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                 <label for="txtfchDesde"  >Fecha Desde*</label>
                             </span>
                           </div>
         <div class="col-sm-4" style="padding-top:10px;" >

                             <span  class="has-float-label"   >
                                <input id="txtfchHasta"   maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                 <label for="txtfchHasta"  >Fecha Hasta*</label>
                             </span> 
                    </div> 
    </div>

            <div class="row">
                  

            <div class="col-sm-4" style="padding-top:10px;" >

                      <div class="floating-label">  
                <select class="limpiar form-control moderno_tb floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="txtCodDocumento"   >
                            <option value ="NV">Nota de venta</option>
                            <option value ="BV">Boleta</option>
                            <option value ="FV">Factura</option>
                                </select>
                        
                      <label class="floating-select2">Código Doc.*</label>
                       
                      </div>

                </div>
                <div class="col-sm-4" style="padding-top:10px;" >

                       <span  class="has-float-label"   >
                        <input id="txtSerieDoc"   maxlength="4" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                        <label for="txtSerieDoc">Serie Doc.</label>
                        </span>

                </div>
                 <div class="col-sm-4" style="padding-top:10px;" >

                       <span  class="has-float-label"   >
                        <input id="txtNroDoc"   maxlength="8" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                        <label for="txtNroDoc">Nro. Doc.</label>
                        </span>

                </div>
          
</div>

    
     <div class="row"  style="padding-bottom:30px;">
     <div class="col-sm-4" style="padding-top:10px;" >
             
                          <div class="input-group">
                           <span  class="has-float-label"   >
                            <input id="txtCliente"  type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                            <label for="txtCliente">Cliente</label>
                            </span>

                            <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes" onclick="ModalConsultarClientes();" style="background-color: #ffffff;border:0px">
                            <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
                             </a>  
                   
                        </div>
                         
                    </div>
   
        </div>



    <div class="modal fade" id="modalBuscarDoc" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true"   style="margin-top:-50px;"  >
                    <div class="modal-dialog" role="document" style="width: 800px;" >
                         <div class="modal-content" style="width: 800px;background-color:#e4e2e2;">
                          <div class="modal-header" style="background: #d6d5d5;" >
                          <div class="col-sm-6" >
                              <table class="table" style="border:0px; solid #fff;margin-bottom: 0px;">
                              <tbody>
                                <tr> 
                                  <td id="upComprobante" style="border:0px; solid #fff;font-weight: bold;"></td>
                                </tr> 
                              </tbody>
                            </table>
                             </div>
                            <div class="col-sm-6" >
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                </div>
                            </div>
                            <div class="modal-body"  style="max-height: calc(110vh - 250px);overflow-y: auto;" >
 
                           

                             <table class="table" style="border:0px; solid #fff;">
                              <tbody>
                                 <tr> 
                                  <td style="border:0px; solid #fff;">Fecha : </td>
                                  <td id="upFecha" style="text-align:right;border:0px; solid #fff;""></td>
                                  <td style="border:0px; solid #fff;width:68%;"></td> 
                                </tr> 
                              </tbody>
                            </table>
                             
                            <table class="table"> 
                              <tbody>
                              
                                <tr> 
                                  <td>Tienda : </td>
                                  <td id="upCodTienda"  >-</td>
                                  <td id="upNomTienda"  >-</td>
                                </tr> 
                                 <tr> 
                                  <td>Caja : </td>
                                  <td id="upCodCaja"   >-</td>
                                  <td id="upNomCaja"  >-</td> 
                                </tr> 
                                 <tr> 
                                  <td>Vendedor : </td>
                                  <td id="upCodVendedor"  >-</td>
                                  <td id="upNomVendedor"  >-</td>  
                                </tr> 
                                <tr> 
                                  <td>Cliente : </td>
                                  <td id="upCodCliente"  >-</td>
                                  <td id="upNomCliente"  >-</td> 
                                </tr> 
                              </tbody>
                            </table>
                             

                              <table class="table" style="border:0px; solid #fff;"> 
                    
                              <tbody>
                                <tr> 
                                  <td id="Td1" style="border:0px; solid #fff;">Articulos vendidos</td>
                                </tr> 
                              </tbody>
                            </table>


                            <table  class="table" id="tbArticulo" style="width:100%;"    >
                                <colgroup>  
                                    <col style="width: 10%"></col>
                                    <col style="width: 40%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 12%"></col>
                                    <col style="width: 8%"></col>
                                    <!--<col style="width: 8%"></col>-->
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                </colgroup>
                                 <thead id="thTablaDetalleArticulos"> 
                                   <tr> 
                                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Artículo
                                            </th >
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Nombre Artículo  
                                            </th> 
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Cant.
                                            </th> 
                                             <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Precio Uni.
                                            </th> 
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                IGV
                                            </th> 
                                             <!--<th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                ISC
                                            </th>--> 
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Desc.
                                            </th> 
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Importe
                                            </th> 
                                        </tr>
                                  </thead>
                                    <tbody> 
                                    </tbody>
                                </table>
                                 

                                  <table class="table" style="border:0px; solid #fff;margin-bottom:0px;"> 
                              <tbody>
                                <tr> 
                                   <td style="border:0px; solid #fff;width:75%;"></td> 
                                  <td style="border:0px; solid #fff;">Importe Total : </td>
                                  <td id="upTotal" style="text-align:right;border:0px; solid #fff;"></td> 
                                </tr> 
                                
                              </tbody>
                            </table>
                      
                            </div>
                          
                        </div>  
                    </div>
                </div>

 


          <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"   >
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color:#d4e1e4;" >
                            <div class="modal-header"  >
                                 <div class="col-sm-6"  >
                                <h5 class="modal-title"  >Seleccione Cliente</h5>
                                </div>
                                <div class="col-sm-6"  >
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                 </div>
                            </div>
 

                            <div class="modal-body" style="margin: 10px;" >
                                <table  id="tableVisibleConsulClientes" class="display" style="width:100%;"    >
                                     <colgroup>  
                                     <col style="width:10%"></col> 
                                    <col style="width:30%"></col> 
                                    <col style="width:60%"></col>
                                </colgroup>
                                    <thead  id="thTablaConsultarCliente"   >
                                        <tr>
                                         <th class="text-center" style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                  
                                            </th>
                                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Cliente
                                            </th >
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Nombre del Cliente
                                            </th> 
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodCliente();">Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
           <!-- Tabla para Visible -->
                <table id="table_visibleDoc" class="display" style="width:100%"  >
                 <colgroup>
                  <col style="width:8%"></col>
                  <col style="width:8%"></col>
                  <col style="width:8%"></col>
                     <col style="width:13%"></col>
                    <col style="width:20%"></col>
                    <col style="width:10%"></col>
                    <col style="width:15%"></col> 
                    <col style="width:5%"></col> 
                    <col style="width:5%"></col> 
                                </colgroup>
                    <thead id="thTablaVisible"   >
                        <tr>
                         <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Serie Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nro. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Num. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cliente
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               Importe
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Creación
                            </th> 
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                             
                        </tr>
                    </thead>
                
                     <tbody >

                    </tbody>
                </table>

                 <!-- Modal Nota de Debito-->
<div class="modal fade" id="modalDarDeBaja" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5  class="modal-title" id="txtDocRefDarDeBaja"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <span class="has-float-label">
                <input id="txtMotivoDarDeBaja" maxlength="50" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this);" />
                <label for="txtMotivoDarDeBaja">Motivo de anulación</label>
            </span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button onclick="GenerarDarDeBaja()" type="button" class="btn btn-primary" >Confirmar</button>
      </div>
    </div>
  </div>
</div>
        <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
              <div id="tableExportarConsultarCliente" style="display:none;" > 
                <table id="table_secundariaConsultarCliente" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                <colgroup> 
                 <col style="width:10%"></col> 
                                    <col style="width:30%"></col> 
                                    <col style="width:60%"></col>
                </colgroup> 
                    <thead>
                        <tr> 
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Cliente
                        </th >
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Nombre del Cliente
                        </th>

                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
                </div>

                 <!-- Tabla para Exportar Detalle de Articulo-->
               <div id="tableExportarDetalleArticulo" style="display:none;" > 
                <table id="table_secundariaDetalleArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
                <colgroup>
               <col style="width:10%"></col>
                                     <col style="width:60%"></col> 
                                    <col style="width:8%"></col> 
                                     <col style="width:15%"></col> 
                                    <col style="width:15%"></col>
                </colgroup>
                    <thead  >
                        <tr>
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Artículo
                        </th >
                        <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Nombre Artículo  
                        </th> 
                        <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Cantidad
                        </th> 
                        <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                              Precio Uni.
                            </th> 
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                Importe
                            </th>
                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
           </div>

            <!-- Tabla para Exportar Principal-->
               <div id="tableExport" style="display:none;" > 
                <table id="table_principalDoc" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
            
                    <thead  >
                        <tr>
                         <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Serie Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nro. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Num. Doc.
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cliente
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               Importe
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Creación
                            </th> 
                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
           </div>
               

  </div>
    </div>
      </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
