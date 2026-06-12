<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Nota de debito | DATPOS';
$pageScript = 'NotaDebito.js';
$showCrudButtons = false;
$showConsultButtons = true;
$loadConsultAssets = false;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '25'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[NotaDebito] VerificarAccesos rol 25 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[NotaDebito] VerificarAccesos rol 25 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script src="<?= basePath() ?>/assets/Javascript/Filtros.js" type="text/javascript"></script>
<link href="<?= basePath() ?>/assets/Styles/css/switcher.css" rel="stylesheet" type="text/css" />
     <script src="<?= basePath() ?>/assets/Scripts/jquery.switcher.js" type="text/javascript"></script>

     <script src="<?= basePath() ?>/assets/Javascript/Numerosaletras.js" type="text/javascript"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
     <script src="<?= basePath() ?>/assets/Scripts/html2canvas.js" type="text/javascript"></script>
     <script src="<?= basePath() ?>/assets/Scripts/qrcode.js" type="text/javascript"></script>

    <!--Diseño de Texto Flotante-->
    <link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />
    <!--Libreria para General Exel-->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
    <!--Diseño de Botones-->
    <link href="<?= basePath() ?>/assets/Styles/disenoBotones.css" rel="stylesheet" type="text/css" />

    <input id="idfact" type="hidden"/> 
 



<input id="hhd_empresa" type="hidden" value="<?= e($o->cdescripcion ?? '') ?>"/>
<input id="hhd_direccionE" type="hidden" value="<?= e($o->cdomicilio ?? '') ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e($ubigeoEmpresa) ?>"/>
<input id="hdd_ruc" type="hidden" value="<?= e($o->cnum_tribu ?? '') ?>"/>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($o->ctelf_tienda ?? '') ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($o->cdsc_tienda ?? '') ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e($ubigeoTienda) ?>"/>
<input id="TipDoc" type="hidden"  /> 
<input id="hdd_numerofilas" type="hidden"/>

<div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
        <!-- DATOS -->
            <div id="Datos" class="tab-pane in active "  >
            <!-- Buscadores --> 
                <div class="row" >
                <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="limpiar form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtTienda">
                            </select>
                            <label class="floating-select2">
                                Tienda*</label>
                        </div>
                    </div>  
                     <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtfchDesde" maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtfchDesde">Fecha Desde*</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top:10px;" >
                        <span class="has-float-label">
                            <input id="txtfchHasta" maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtfchHasta">Fecha Hasta*</label>
                        </span>
                    </div> 


                     
                </div>
                <div class="row" >
                <div class="col-sm-4" style="padding-top:10px;" > 
                      <div class="floating-label">  
                      <select class="limpiar form-control moderno_tb floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="tb_doc" >
                         <option value=""></option>
                         <option value="BV">Boleta</option>
                         <option value="FV">Factura</option>
                         </select> 
                      <label class="floating-select2">Código Doc.*</label> 
                      </div> 
                    </div> 
                    <div class="col-sm-4" style="padding-top:10px;" >
                        <span class="has-float-label">
                            <input id="tb_serie" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)"/>
                            <label for="tb_serie">Serie.Ref.</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top:10px;" >
                        <span class="has-float-label">
                            <input id="tb_numero" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)"/>
                            <label for="tb_numero">Num.Ref.</label>
                        </span>
                    </div> 
 
                  
                    
                </div>
                   <div class="row" style="padding-bottom: 30px;">
                 <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="tb_cliente" disabled type="text" class="limpiar form-control moderno_tb"
                                    placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="tb_cliente">
                                    Cliente</label>
                            </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes"
                                onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                    </div>
                  
                    
                </div>
                
           <!-- Tabla para Visible -->
                <table id="table_id" class="display" style="width: 100%">
                    <colgroup>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 13%"></col>
                        <col style="width: 20%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 4%"></col> 
                        <col style="width: 4%"></col> 
                    </colgroup>
                    <thead id="thTablaVisible">
                        <tr>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cod. Doc.</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Serie Doc.</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nro. Doc.</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Num. Doc.</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cod. Almacen</th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th> 
                        </tr>
                    </thead>
                
                     <tbody >

                    </tbody>
                </table>
       <!-- Modal Resumen Venta-->
       <div id="modalResumenVenta" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
         <div class="modal-dialog">
         <div class="modal-content">
             <div class="modal-header" style="border-bottom: 0px;">
                 <div class="col-md-12" style="text-align: center;">
                     <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 33px;">
                     <h4>Operación Completada</h4>
                 </div>
             </div>
             <div class="modal-body" style="padding: 20px 30px;">
                 <div class="row" style="margin-bottom: 8px;">
                     <div class="col-md-6">Num. Documento:</div>
                     <div class="col-md-6" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_numdoc"></label></div>
                 </div>
                 <div class="row" style="margin-bottom: 8px;">
                     <div class="col-md-6">Monto Total:</div>
                     <div class="col-md-6" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_total"></label></div>
                 </div>
                 <div class="row" style="margin-bottom: 8px;">
                     <div class="col-md-6">Monto Entregado:</div>
                     <div class="col-md-6" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_entregado"></label></div>
                 </div>
                 <div class="row" style="margin-bottom: 8px;">
                     <div class="col-md-6">Vuelto:</div>
                     <div class="col-md-6" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_vuelto">0.00</label></div>
                 </div>

                  
                 <div class="row" style="margin-bottom: 8px; align-items: center; display: flex;">
                     <div class="col-md-6" style="padding-top: 5px;">Imprimir</div>
                     <div class="col-md-6" style="text-align: right;">
                         <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ckb_Imprimir" value="option1"></div>
                     </div>
                 </div>
                 <div class="row" style="margin-top: 12px; align-items: center; display: flex;">
                     <div class="col-md-3" style="padding-top: 5px;">Email:</div>
                     <div class="col-md-6">
                         <input id="input_email_cliente" type="email" class="limpiar form-control moderno_tb" placeholder="correo@ejemplo.com" style="font-size: 12px; height: 32px;" />
                     </div>
                     <div class="col-md-3" style="text-align: right;">
                         <div class="form-check form-check-inline">
                             <input class="form-check-input" type="checkbox" id="ckb_Correo" value="option2">
                         </div>
                     </div>
                 </div>    </div>
            <div class="modal-footer" style="border-top: 0px;">
                <div class="col-md-12" style="text-align: center;">
                    <button type="button" class="btn btn-primary" id="btn_confirmar_resumen" onclick="FinalizarResumenDoc();">
                        <span id="btn_confirmar_texto">Confirmar</span>
                        <span id="btn_confirmar_spinner" style="display:none;">
                            <i class="fa fa-spinner fa-spin"></i> Enviando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
        </div>
    </div>
 <!-- Modal Nota de Debito-->
<div class="modal fade" id="modalNotaDebito" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5  class="modal-title" id="txtDocRefNotaDebito"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
            <span class="has-float-label">
                <input id="txtMonto" type="number" min="0" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this);" />
                <label for="txtMonto">Monto de Nota de Débito</label>
            </span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button onclick="GenerarNotaDebito()" type="button" class="btn btn-primary" >Confirmar</button>
      </div>
    </div>
  </div>
</div>
  
<div class="modal fade" id="modalAnulacion" tabindex="-1" role="dialog" aria-hidden="true"></div>
<div class="modal fade" id="modalDescuento" tabindex="-1" role="dialog" aria-hidden="true"></div>

<!-- Modal Documento REf-->
                <div class="modal fade" id="modalBuscarDoc" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel"
                    aria-hidden="true" style="margin-top: -50px;">
                    <div class="modal-dialog" role="document" style="width: 800px;">
                        <div class="modal-content" style="width: 800px; background-color: #e4e2e2;">
                            <div class="modal-header" style="background: #d6d5d5;">
                                <div class="col-sm-6">
                                    <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                        <tbody>
                                            <tr>
                                                <td id="upComprobante" style="border: 0px; solid #fff; font-weight: bold;">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-sm-6">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-body" style="max-height: calc(110vh - 250px); overflow-y: auto;">
                                <table class="table" style="border: 0px; solid #fff;">
                                    <colgroup>
                                        <col style="width: 20%"></col>
                                        <col style="width: 20%"></col>
                                        <col style="width: 60%"></col>
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <td style="border: 0px; solid #fff;">
                                                Fecha Doc.:
                                            </td>
                                            <td id="upFecha" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                            <td style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                        </tr>
                                         <tr>
                                            <td style="border: 0px; solid #fff;">
                                                Cobranza Doc.:
                                            </td>
                                            <td id="upDocumentoCombranza" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                            <td style="text-align: right; border: 0px; solid #fff;">
                                                <a class="disabled input-group-addon" data-toggle="modal" data-target="#ModalCobranza"
                                                     style="background-color: #e4e2e2; border: 0px; text-align: left;">
                                                    <i id="btnIrComprobante" class="disabled fa fa-arrow-right color-popup-verde" aria-hidden="true">
                                                    </i></a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td>
                                                Tienda :
                                            </td>
                                            <td id="upCodTienda">
                                                -
                                            </td>
                                            <td id="upNomTienda">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Caja :
                                            </td>
                                            <td id="upCodCaja">
                                                -
                                            </td>
                                            <td id="upNomCaja">
                                                -
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td>
                                                Vendedor :
                                            </td>
                                            <td id="upCodVendedor">
                                                -
                                            </td>
                                            <td id="upNomVendedor">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Cliente :
                                            </td>
                                            <td id="upCodCliente">
                                                -
                                            </td>
                                            <td id="upNomCliente">
                                                -
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" style="border: 0px; solid #fff;">
                                    <tbody>
                                        <tr>
                                            <td id="Td1" style="border: 0px; solid #fff;">
                                                Articulos vendidos
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" id="tbArticulo" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 10%"></col>
                                    <col style="width: 40%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 12%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    </colgroup>
                                    <thead id="thTablaDetalleArticulos">
                                        <tr>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Artículo
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
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
                                             <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                ISC
                                            </th> 
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
                               
                                <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                    <thead id="thTablaArticulo">
                                        <tr>
                                            <td style="border: 0px; solid #fff; width: 75%;">
                                            </td>
                                            <td style="border: 0px; solid #fff;">
                                                Importe Total :
                                            </td>
                                            <td id="upTotal" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                        </tr>
                                      
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

    <div id="tableExportarDetalleArticulo" style="display:none;" >
                <table id="table_secundariaDetalleArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Nombre Artículo</th>
                            <th>Cantidad</th>
                            <th>Precio Uni.</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
                </div>

                <!--MODAL COBRANZA-->
                <div class="modal fade" id="ModalCobranza" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true"   style="margin-top:-50px;"  >
                    <div class="modal-dialog" role="document" style="width: 800px;" >
                         <div class="modal-content" style="width: 800px;background-color:#e4e2e2;">
                          <div class="modal-header" style="background: #d6d5d5;" >
                          <div class="col-sm-6" >
                              <table class="table" style="border:0px; solid #fff;margin-bottom: 0px;">
                              <tbody>
                                <tr> 
                                  <td id="CobranDocumentoFac" style="border:0px; solid #fff;font-weight: bold;"></td>
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
                              <col style="width:20%"></col>
                                    <col style="width:20%"></col> 
                                    <col style="width:60%"></col> 
                              <tbody>
                                 <tr> 
                                  <td style="border:0px; solid #fff;">Fecha Doc.: </td>
                                  <td id="CobranFecha" style="text-align:right;border:0px; solid #fff;""></td>
                                  <td style="text-align:right;border:0px; solid #fff;"></td>
                                </tr> 
                               
                                
                              </tbody>
                            </table>
                             
                            <table class="table"> 
                              <tbody>
                              
                                <tr> 
                                  <td>Tienda : </td>
                                  <td id="CobranCodTienda"  >-</td>
                                  <td id="CobranNomTienda"  >-</td>
                                </tr> 
                                 <tr> 
                                  <td>Caja : </td>
                                  <td id="CobranCodCaja"   >-</td>
                                  <td id="CobranNomCaja"  >-</td> 
                                </tr> 
                                 <tr> 
                                  <td>Vendedor : </td>
                                  <td id="CobranCodVendedor"  >-</td>
                                  <td id="CobranNomVendedor"  >-</td>  
                                </tr> 
                                <tr> 
                                  <td>Cliente : </td>
                                  <td id="CobranCodCliente"  >-</td>
                                  <td id="CobranNomCliente"  >-</td> 
                                </tr> 
                              </tbody>
                            <table class="table" style="border: 0px; solid #fff;">
                                    <tbody>
                                        <tr>
                                            <td id="Td2" style="border: 0px; solid #fff;">
                                                Lista de cobranza
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" id="tbCobranza" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 25%"></col>
                                        <col style="width: 15%"></col>
                                        <col style="width: 15%"></col>
                                        <col style="width: 15%"></col>
                                    </colgroup>
                                    <thead id="thTablaDetalletbCobranza">
                                        <tr>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Forma Pago
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Operación
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Num. tarjeta
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Monto
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                              

                                  <table class="table" style="border:0px; solid #fff;margin-bottom:0px;"> 
                              <tbody>
                                <tr>
                                    <td style="border: 0px; solid #fff; width: 75%;">
                                    </td>
                                    <td style="border: 0px; solid #fff;">
                                        Importe Total :
                                    </td>
                                    <td id="upImpTotCobraza" style="text-align: right; border: 0px; solid #fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 0px; solid #fff; width: 75%;">
                                    </td>
                                    <td style="border: 0px; solid #fff;">
                                        Total Entregado :
                                    </td>
                                    <td id="upTotalEntregado" style="text-align: right; border: 0px; solid #fff;">
                                    </td>
                                </tr>
                                <tr>
                                    <td style="border: 0px; solid #fff; width: 75%;">
                                    </td>
                                    <td style="border: 0px; solid #fff;">
                                        Vuelto :
                                    </td>
                                    <td id="upVuelto" style="text-align: right; border: 0px; solid #fff;">
                                    </td>
                                </tr>
                                
                              </tbody>
                            </table>
                      
                            </div>
                          
                        </div>  
                    </div>
                </div>
               <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color: #d4e1e4;">
                            <div class="modal-header">
                                <div class="col-sm-6">
                                    <h5 class="modal-title">
                                        Seleccione Cliente</h5>
                                </div>
                                <div class="col-sm-6">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-body" style="margin: 10px;">
                                <table id="tableVisibleConsulClientes" class="display" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 10%"></col>
                                        <col style="width: 30%"></col>
                                        <col style="width: 60%"></col>
                                    </colgroup>
                                    <thead id="thTablaConsultarCliente">
                                        <tr>
                                            <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4;
                                                background-color: rgb(33, 182, 215); color: White;">
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Cliente
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Nombre del Cliente
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodCliente();">
                                    Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cerrar</button>
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




                <div id="modalDevolucion" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-header">
                            Ingrese Motvo de devolución:
                            </div>
                            <div class="modal-body">
                                <textarea id="ta_motivo" rows="3" cols="35"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="GrabarDevolucion();">
                                    Confirmar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cancelar</button>
                            </div>
                        </div>
                    </div>
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
                                Cliente
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Tienda
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               Importe
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Doc.
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Almacén
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

    <div id="zona-imprimir" style="width: 280px; display:none">

        <div style="width: 280px;font-size: 10px;">
            <div style="text-align: center;">
                <image src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 50px;margin-top: 10px;"></image>
                <div class="col-xs-12" id="nombre_empresa1"></div>
                <div class="col-xs-12" id="direccion_empresa"></div>
                <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                <div>
                    <div class="col-xs-6" id="ruc_empresa"></div>
                    <div class="col-xs-6" id="telefono_tienda"></div>
                </div>
                <div class="col-xs-12" id="nombre_tienda"></div>
                <div class="col-xs-12" id="direccion_tienda"></div>
                <div class="col-xs-12" id="ubigeo_tienda"></div>
                <div class="col-xs-12">===========================================</div>
                <div class="col-xs-12" id="nombre_documento"></div>
                <div class="col-xs-12" id="codigo_documento"></div>
                <div class="col-xs-12">===========================================</div>
            </div>
            <div>
                <div class="col-xs-6" id="fecha_documento"></div>
                <div class="col-xs-6" style="text-align: right;" id="hora_documento"></div>
            </div>
            <div class="col-xs-12" id="nombre_cliente"></div>
            <div class="col-xs-12" id="direccion_cliente"></div>
            <div class="col-xs-12" id="ruc_cliente"></div>
            <div class="col-xs-12">===========================================</div>
            <div style="text-align: center;">
                <div class="col-xs-3">Descrip.</div>
                <div class="col-xs-3">Cant.</div>
                <div class="col-xs-3">P.Unit</div>
                <div class="col-xs-3">Monto</div>
            </div>
            <div class="col-xs-12">===========================================</div>
            <div id="div_articlosdocumento"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-4">Op.Grabada</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="opgrabada_documento" style="text-align: right;"></div>
            <div class="col-xs-4">IGV</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="igv_documento" style="text-align: right;"></div>
            <div class="col-xs-4">ISC</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="isc_documento" style="text-align: right;"></div>
            <div class="col-xs-4">Total a Pagar</div>
            <div class="col-xs-4" style="text-align: right;">S/</div>
            <div class="col-xs-4" id="total_documento" style="text-align: right;"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" id="son_documento"></div>
            <div class="col-xs-12">===========================================</div>
            <div id="div_cobranzadocumento"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" id="vendedor"></div>
            <div class="col-xs-12" id="codigo_caja"></div>
            <div class="col-xs-12">===========================================</div>
            <div class="col-xs-12" style="text-align: center;">Cuéntanos tu experiencia en:</div>
            <div class="col-xs-12" style="text-align: center;">www.datpos.com</div>
            <div class="col-xs-12" style="text-align: center;">Para Consultar El Documento Ingrese</div>
            <div class="col-xs-12" style="text-align: center;">https://comprobantes.msgsac.net:453/documentos</div>
            <div class="col-xs-12" style="text-align: center;" id="qrcode"></div>
                
        </div>
        <div style="color: white;">.</div>
    </div>
    <div id="ponercanvas" style="margin-top: 0px !important;"></div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
