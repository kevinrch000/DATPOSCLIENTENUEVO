<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Consulta Formas de Pago | DATPOS'; $pageScript = 'ConsultaFormaPago.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="Opcion" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/> 

 
<div class="c-content-center modern-page" style="padding-top:40px;"  >

        <div class="tab-content"> 
        <!-- DATOS -->
            <div id="Datos" class="tab-pane in active "  >
            <!-- Buscadores --> 
                <div class="row">
                    
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="floating-select limpiar form-control moderno_tb" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtCaja">
                            </select>
                            <label class="floating-select2">
                                Caja*</label>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtfchDesde" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtfchDesde">
                                Fecha Desde*</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtfchHasta" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtfchHasta">
                                Fecha Hasta*</label>
                        </span>
                    </div> 
                </div>
                <div class="row">
                     <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="limpiar form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtCodDocumento">
                            </select>
                            <label class="floating-select2">
                                Código Doc.*</label>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtSerieDoc" maxlength="4" type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtSerieDoc">
                                Serie Doc.</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtNroDoc" maxlength="8" type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtNroDoc">
                                Nro. Doc.</label>
                        </span>
                    </div>

                     
                </div>
                <div class="row" style="padding-bottom: 30px;">
                     <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="limpiar form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtTipoTarjeta">
                                <option value=""></option>
                                <option value="Efectivo">Efectivo</option>
                                <option value="NotaCredito">NotaCredito</option>
                                <option value="Visa">Visa</option> 
                                <option value="Mastercard">Mastercard</option> 
                                <option value="Diners Club">Diners Club</option> 
                                <option value="American Express">American Express</option> 
                                <option value="Transferencia">Transferencia</option> 
                                <option value="Yape">Yape</option> 
                                <option value="Plin">Plin</option>  
                            </select>
                            <label class="floating-select2">
                                Medio de Pago</label>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="txtCliente"  type="text" class="limpiar form-control moderno_tb"
                                    placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtCliente">
                                    Cliente</label>
                            </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes"
                                onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="txtUsuario" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                    placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtUsuario">
                                    Usuario</label>
                            </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarUsuarios"
                                onclick="ModalConsultarUsuarios();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

        <ul class="nav nav-tabs"   class="active">
            <li onclick="">
            <a data-toggle="tab" id="TLista" href="#Lista" onclick="Opcion()" class="tabcito">Lista</a></li>
            <li onclick="">
            <a data-toggle="tab" id="TDetallado" href="#Detallado" onclick="Opcion2()" class="tabcito">Detallado</a></li>
         </ul>
           <div class="tab-content" style="padding-bottom:30px;">
         <!-- LISTADO -->
               <div id="Lista" class="tab-pane in active " style="padding: 13px;">
                   <!-- Tabla para Visible -->
                   <table id="table_visibleDoc" class="display" style="width: 100%">
                       <colgroup>
                           <col style="width: 5%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 8%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 8%"></col>
                           <col style="width: 6%"></col>
                           <col style="width: 3%"></col>
                       </colgroup>
                       <thead id="thTablaVisible">
                           <tr>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cod. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Serie Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nro. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Usuario
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cliente
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Efectivo
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Otros
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nota Crédito
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nota Débito
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Importe Total
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Fecha Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               </th>
                           </tr>
                       </thead>
                       <tbody>
                       </tbody>
                   </table>

                       <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_IPEfectivo" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_IPEfectivo">
                                Importe total en efectivo</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_IPTarjeta" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_IPTarjeta">
                                Importe total en Otros</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_IPNC" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_IPNC">
                                Importe total en nota de crédito</label>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_IPND" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_IPND">
                                Importe total en nota de débito</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_IPV" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_IPV">
                                Importe total de ingresos</label>
                        </span>
                    </div>
                </div>
               </div>
              <!-- Estadisticos -->
               <div id="Detallado" class="tab-pane tabcito" style="padding: 13px;">
                   <table id="table_visibleDocDetallado" class="display" style="width: 100%">
                       <colgroup>
                           <col style="width: 5%"></col>
                           <col style="width: 5%"></col>
                           <col style="width: 8%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 10%"></col>
                           <col style="width: 10%"></col> 
                       </colgroup>
                       <thead id="ThtableDetallado">
                           <tr>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cod. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Serie Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nro. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Usuario
                               </th> 
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Forma de Pago
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Monto
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Doc. Ref.
                               </th> 
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Fecha Doc.
                               </th> 
                           </tr>
                       </thead>
                       <tbody>
                       </tbody>
                   </table>

                   <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txt_MontoTT" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txt_MontoTT">
                                Monto total</label>
                        </span>
                    </div>
                     
                </div>

               </div>
           </div>
             
                <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"   >
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color:#d4e1e4;" >
                            <div class="modal-header" >
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

                   <!-- Tabla para Exportar Secuandaria Buscar Usuario-->
              <div id="tableExportarConsultarUsuario" style="display:none;" > 
                <table id="table_secundariaConsultarUsuario" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                <colgroup> 
                 <col style="width:10%"></col> 
                <col style="width:30%"></col> 
                <col style="width:60%"></col>
                </colgroup> 
                    <thead>
                        <tr> 
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Usuario
                        </th >
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Nombre del Usuario
                        </th>

                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
                </div>


                 
                <div class="modal" id="modalConsultarUsuarios" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color: #d4e1e4;">
                            <div class="modal-header">
                                <div class="col-sm-6">
                                    <h5 class="modal-title">
                                        Seleccione Usuario</h5>
                                </div>
                                <div class="col-sm-6">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-body" style="margin: 10px;">
                                <table id="tableVisibleConsulUsuario" class="display" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 10%"></col>
                                        <col style="width: 30%"></col>
                                        <col style="width: 60%"></col>
                                    </colgroup>
                                    <thead id="thTablaConsultarUsuario">
                                        <tr>
                                            <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4;
                                                background-color: rgb(33, 182, 215); color: White;">
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Usuario
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Nombre del Usuario
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodUsuario();">
                                    Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                 <!-- Tabla para Exportar Secuandaria Buscar Cliente-->
              <div id="tableExportarConsultarCliente" style="display:none;" > 
                <table id="table_secundariaConsultarCliente" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                <colgroup>  
               
                    <col style="width:30%"></col> 
                    <col style="width:60%"></col> 
                </colgroup> 
                    <thead>
                        <tr> 
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

           
            <!-- Tabla para Exportar Principal-->
               <div id="tableExport" style="display:none;" > 
                <table id="table_principalDoc" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
                  <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cod. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Serie Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nro. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Usuario
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cliente
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Efectivo
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Otros
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nota Crédito
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nota Débito
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Importe Total
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Fecha Doc.
                               </th>
                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
           </div>

           
 <div class="modal fade" id="MdFacturacion" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true"  style="margin-top:-50px;z-index: 9000;"  >
    <div class="modal-dialog" role="document" style="width: 800px;" >
            <div class="modal-content" style="width: 800px;background-color:#e4e2e2;">
            <div class="modal-header" style="background: #d6d5d5;" >
            <div class="col-sm-6" >
                <table class="table" style="border:0px; solid #fff;margin-bottom: 0px;">
                <tbody>
                <tr> 
                    <td id="MdFactCodDoc" style="border:0px; solid #fff;font-weight: bold;"></td>
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
                 <colgroup>  
                                    <col style="width:20%"></col>
                                    <col style="width:20%"></col> 
                                    <col style="width:60%"></col> 
                                </colgroup>
                <tbody>
                    <tr> 
                    <td style="border:0px; solid #fff;">Fecha Doc : </td>
                    <td id="MdFactFecha" style="text-align:right;border:0px; solid #fff;""></td>
                    <td style="border:0px; solid #fff;"></td> 
                </tr> 
                 
                </tbody>
            </table>
                             
            <table class="table"> 
                <tbody>
                              
                <tr> 
                    <td>Tienda : </td>
                    <td id="MdFactCodTienda"  >-</td>
                    <td id="MdFactNomTienda"  >-</td>
                </tr> 
                    <tr> 
                    <td>Caja : </td>
                    <td id="MdFactCodCaja"   >-</td>
                    <td id="MdFactNomCaja"  >-</td> 
                </tr> 
                    <tr> 
                    <td>Vendedor : </td>
                    <td id="MdFactCodVendedor"  >-</td>
                    <td id="MdFactNomVendedor"  >-</td>  
                </tr> 
                <tr> 
                    <td>Cliente : </td>
                    <td id="MdFactCodCliente"  >-</td>
                    <td id="MdFactNomCliente"  >-</td> 
                </tr> 
                </tbody>
            </table>
                             

                <table class="table" style="border:0px; solid #fff;"> 
                    
                <tbody>
                <tr> 
                    <td style="border:0px; solid #fff;">Articulos vendidos</td>
                </tr> 
                </tbody>
            </table>


                <table class="table" id="tbFactArticulo" style="width: 100%;">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 40%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 12%"></col>
                        <col style="width: 8%"></col>
                        
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                    </colgroup>
                    <thead id="thFactDetalleArticulos">
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Artículo
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Nombre Artículo
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Cant.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Precio Uni.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                IGV
                            </th>
                            
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Desc.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
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
                    <td id="MdFactTotal" style="text-align:right;border:0px; solid #fff;"></td> 
                </tr> 
                                
                </tbody>
            </table>
                      
            </div>
                          
        </div>  
    </div>
</div>
 
   <div id="Div_DetalladoExpor" style="display:none;" > 
                 <table id="table_DetalladoExpor" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
                 
                    <thead id="Thead1"   >
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cod. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Serie Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Nro. Doc.
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Usuario
                               </th> 
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Cliente
                               </th> 
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Forma de Pago
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Monto
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Vuelto
                               </th>
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Doc. Ref.
                               </th> 
                               <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                   Fecha Doc.
                               </th> 
                        </tr>
                    </thead> 
                     <tbody >

                    </tbody>
                </table>
           </div>
  
               

<div class="modal fade" id="modalBuscarDoc" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true"   style="margin-top:-50px;z-index: 8000;"   >
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
                              <colgroup>  
                                    <col style="width:20%"></col>
                                    <col style="width:20%"></col> 
                                    <col style="width:60%"></col> 
                                </colgroup>
                              <tbody>
                                 <tr> 
                                  <td style="border:0px; solid #fff;">Fecha Doc.: </td>
                                  <td id="upFecha" style="text-align:right;border:0px; solid #fff;""></td>
                                  <td style="border:0px; solid #fff;"></td> 
                                </tr> 
                                <tr> 
                                  <td style="border:0px; solid #fff;">Doc. Venta: </td>
                                  <td id="upDocumentoFac" style="text-align:right;border:0px; solid #fff;">    </td>  
                                   <td style="border:0px; solid #fff;"><a class="disabled input-group-addon"    data-toggle="modal" data-target="#MdFacturacion"  style="background-color: #e4e2e2;border:0px;text-align:left;">
                       <i id="btnIrComprobante" class="disabled fa fa-arrow-right color-popup-verde" aria-hidden="true"></i>
                         </a></td>
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
                                  <td id="Td1" style="border:0px; solid #fff;">Lista de formas de pago</td>
                                </tr> 
                              </tbody>
                            </table>


                            <table  class="table" id="tbCobranza" style="width:100%;"    >
                                 <colgroup>  
                                    <col style="width:25%"></col> 
                                    <col style="width:15%"></col> 
                                    <col style="width:15%"></col>
                                    <col style="width:15%"></col>
                                </colgroup>
                                 <thead id="thTablaDetalletbCobranza"> 
                                   <tr> 
                                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Forma Pago
                                            </th >
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Operación 
                                            </th> 
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Num. tarjeta
                                            </th> 
                                             <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
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
                                  <td style="border:0px; solid #fff;width:64%;"></td> 
                                  <td style="border:0px; solid #fff;">Importe de la factura : </td>
                                  <td id="upImporFac" style="text-align:right;border:0px; solid #fff;"></td> 
                                </tr>
                                <tr> 
                                  <td style="border:0px; solid #fff;width:64%;"></td> 
                                  <td style="border:0px; solid #fff;">Total entregado : </td>
                                  <td id="upTotalEntregado" style="text-align:right;border:0px; solid #fff;"></td> 
                                </tr> 
                                <tr> 
                                  <td style="border:0px; solid #fff;width:64%;"></td> 
                                  <td style="border:0px; solid #fff;">Vuelto : </td>
                                  <td id="upVuelto" style="text-align:right;border:0px; solid #fff;"></td> 
                                </tr> 
                                
                              </tbody>
                            </table>
                      
                            </div>
                          
                        </div>  
                    </div>
                </div>
 
  </div>
    </div>
      </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
