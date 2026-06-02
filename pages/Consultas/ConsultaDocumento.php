<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Consulta Documentos | DATPOS'; $pageScript = 'ConsultaDocumento5.js'; $showCrudButtons = false; $showConsultButtons = true;
$o = getUsuarioSesion();
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<script src="<?= basePath() ?>/assets/Javascript/Numerosaletras.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Scripts/qrcode.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Scripts/html2canvas.min.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script><input id="Opcion" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/> 

 
<input id="hdd_metodopago" type="hidden" value="Visa"/>
<input id="hdd_total" type="hidden" value="0.00"/>
<input id="hdd_coa" type="hidden"/>
<input id="hdd_direc" type="hidden"/>
<input id="hdd_rucC" type="hidden"/>
<input id="hdd_cdsc_coa" type="hidden"/>
<input id="fecha_emision_documento" type="hidden"/>
<input id="hdd_rv" type="hidden"/>
<input id="hdd_igv" type="hidden"/>
<input id="hdd_isc" type="hidden"/>
<input id="hdd_ruc" type="hidden" value="<?= e($o->cnum_tribu ?? '') ?>"/>
<input id="hhd_empresa" type="hidden" value="<?= e($o->cdescripcion ?? '') ?>"/>

<input id="hhd_direccionE" type="hidden" value="<?= e($o->cdomicilio ?? '') ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e(trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-')) ?>"/>
<label id="lSimMoneda" style="display:none;" ><?= e($o->csimbolo_moneda ?? '') ?></label>
<label id="lNomMoneda" style="display:none;" ><?= e($o->cnombre_moneda ?? '') ?></label>
<label id="hdd_rucdat" style="display:none;" ><?= e($o->ccod_empresa ?? '') ?></label>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($o->ctelf_tienda ?? '') ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($o->cdsc_tienda ?? '') ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e(trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-')) ?>"/>
<label id="FactElectronica" style="display:none;" ><?= e($o->ctip_facturador ?? '') ?></label>
<input id="hdd_ctip_doc" type="hidden"/>
<input id="hdd_id_cbfact" type="hidden"/>

<div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
        <!-- DATOS -->
            <div id="Datos" class="tab-pane in active "  >
            <!-- Buscadores --> 
                <div class="row">
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
                            <input id="txtfchDesde" maxlength="10" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtfchDesde">
                                Fecha Desde*</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtfchHasta" maxlength="10" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
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
                                <option value="BV">Boleta</option>
                                <option value="FV">Factura</option>
                                <option value="NV">Nota de Venta</option>
                                <option value="%">Todos</option>
                            </select>
                            <label class="floating-select2">
                                Código Doc.*</label>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtSerieDoc" maxlength="4" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtSerieDoc">
                                Serie Doc.</label>
                        </span>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtNroDoc" maxlength="8" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtNroDoc">
                                Nro. Doc.</label>
                        </span>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="txtCliente" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
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
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtcobs" maxlength="50" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtcobs">
                                Observación</label>
                        </span>
                    </div>
                </div>

                <div class="row" style="padding-bottom: 30px;">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtVariante" maxlength="50" type="text" class="limpiar form-control moderno_tb" autocomplete="off"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtVariante">
                                Variante</label>
                        </span>
                    </div>
                    
                </div>

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
                                        <tr id="idDocInvRef" style="display:contents;" > 
                                            <td style="border: 0px; solid #fff;">
                                                Doc. Inventario:
                                            </td>
                                            <td id="upDocumentoCombranza" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                            <td style="text-align: right; border: 0px; solid #fff;">
                                                <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalBuscarCodInve"
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
                                    <tbody>
                                        <tr>
                                            <td style="border: 0px; solid #fff; width: 75%;">
                                            </td>
                                            <td id="trImportTot" style="border: 0px; solid #fff;">
                                                Importe Total :
                                            </td>
                                            <td id="upTotal" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                        </tr>
                                        <tr id="trCredito" style="display:none;" >
                                            <td style="border: 0px; solid #fff; width: 75%;">
                                            </td>
                                            <td style="border: 0px; solid #fff;">
                                                Consumido :
                                            </td>
                                            <td id="upCredito" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                 <textarea id="tb_observacion" class="disabled limpiar form-control moderno_tb" disabled maxlength="50" cols="20" rows="2" style="margin-top: 10px;"></textarea>
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
                <table id="table_visibleDoc" class="display" style="width:100%"  >
                 <colgroup>
                    <col style="width:2%"></col>
                    <col style="width:4%"></col>
                    <col style="width:8%"></col>
                    <col style="width:15%"></col>
                    <col style="width:15%"></col>
                    <col style="width:10%"></col>
                    <col style="width:9%"></col>
                    <col style="width:10%"></col>
                    <col style="width:5%"></col> 
                    <col style="width:5%"></col> 
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
                                Usuario
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
                                Estado
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                        </tr>
                    </thead> 
                     <tbody >

                    </tbody>
                </table>


                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtITotalP" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtITotalP">
                                Importe total</label>
                        </span>
                    </div>
                     
                </div>


               </div>
              <!-- Estadisticos -->
                <div id="Detallado" class="tab-pane tabcito" style="padding: 13px;">
                  
                   <table id="table_visibleDocDetallado" class="display" style="width:100%"  >
                 <colgroup>
                    <col style="width:2%"></col>
                    <col style="width:4%"></col>
                    <col style="width:8%"></col>
                    <col style="width:15%"></col>
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:5%"></col>  
                    <col style="width:5%"></col
                    </colgroup>
                    <thead id="ThtableDetallado"   >
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
                                Articulo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cantidad
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Precio Uni
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Impuesto
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Doc.
                            </th>  
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Variante
                            </th> 
                        </tr>
                    </thead> 
                     <tbody >

                    </tbody>
                </table>

                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <span class="has-float-label">
                            <input id="txtITotalD" disabled type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtITotalD">
                                Importe total</label>
                        </span>
                    </div>
                     
                </div>
                </div>
            </div>


              

                    <table id="datatableCantidad" style="display:none;" >
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Documentos</th>
                                </tr>
                            </thead> 
                        <tbody> 
                        </tbody>
                    </table>
                    <table id="datatableImporteTotal" style="display:none;" >
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Documentos</th>
                                </tr>
                            </thead> 
                        <tbody> 
                        </tbody>
                    </table>
        <!-- Tabla para Exportar Secuandaria Buscar cliente-->
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

           <div class="modal fade" id="modalBuscarCodInve" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel"
                    aria-hidden="true" style="margin-top: -50px;">
                    <div class="modal-dialog" role="document" style="width: 800px;">
                        <div class="modal-content" style="width: 800px; background-color: #e4e2e2;">
                            <div class="modal-header" style="background: #d6d5d5;">
                                <div class="col-sm-6">
                                    <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                        <tbody>
                                            <tr>
                                                <td id="upComprobanteInve" style="border: 0px; solid #fff; font-weight: bold;">
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
                                <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                    <tbody>
                                        <tr>
                                            <td style="border: 0px; solid #fff;">
                                                Fecha Doc.:
                                            </td>
                                            <td id="upFechaInve" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                            <td style="border: 0px; solid #fff; width: 68%;">
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
                                            <td id="upCodTiendaInve">
                                                -
                                            </td>
                                            <td id="upNomTiendaInve">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Almacén :
                                            </td>
                                            <td id="upCodAlmacenInve">
                                                -
                                            </td>
                                            <td id="upNomAlmacenInve">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Vendedor :
                                            </td>
                                            <td id="upCodVendedorInve">
                                                -
                                            </td>
                                            <td id="upNomVendedorInve">
                                                -
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                Cliente :
                                            </td>
                                            <td id="upCodClienteInve">
                                                -
                                            </td>
                                            <td id="upNomClienteInve">
                                                -
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" style="border: 0px; solid #fff;">
                                    <tbody>
                                        <tr>
                                            <td id="Td2" style="border: 0px; solid #fff;">
                                                Lista de Articulos
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table class="table" id="table_visible_DatosInve" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 10%"></col>
                                        <col style="width: 30%"></col>
                                        <col style="width: 20%"></col>
                                        <col style="width: 10%"></col>
                                        <col style="width: 15%"></col>
                                        <col style="width: 15%"></col>
                                    </colgroup>
                                    <thead id="thTablaDatosInve">
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
                                                Unidad de Medida
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Cantidad
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Costo Uni.
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                                color: White;">
                                                Costo Total
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                                 
                                <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                    <tbody>
                                        <tr>
                                            <td style="border: 0px; solid #fff; width: 84%;">
                                            </td>
                                            <td style="border: 0px; solid #fff;">
                                                Total :
                                            </td>
                                            <td id="upTotalInve" style="text-align: right; border: 0px; solid #fff;">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table> 
                                
                            </div>
                        </div>
                    </div>
                </div>

                  <div id="zona-imprimir" style="width: 800px;display: none;">

                    <div style="width: 800px;font-size: 10px;">
                     <div class="row" style="height: 40px;" >
                      </div>
                        <div class="row" >

                        <div class="col-xs-6" style="width: 400px;margin-left: 30px;margin-right: 30px;
                        text-align: left;" >
                            <image id="idlogoTicket" style="width: 80px;margin-top: 10px;"></image>
                            <div class="col-xs-12" id="nombre_empresa1"></div>  
                            <div class="col-xs-12" id="direccion_empresa"></div>
                            <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                        </div>
                        <div class="col-xs-6" style="width: 300px;margin-left: 30px;margin-right: 30px;
                        border: 1px solid black;text-align: center;" >
                             <div class="col-xs-12" style="font-size: 15px;" id="ruc_empresa" >R.U.C. 20749758444</div>
                             <div class="col-xs-12" style="font-size: 15px;" id="DicDoc" >FACTURA ELECTRONICA</div>
                             <div class="col-xs-12" style="font-size: 15px;" id="DicSerieNro" >F001 - 00025412</div>
                        </div>
                        </div>
                       
                        <div class="col-xs-12" style="width: 745px;margin-left: 30px;margin-right: 30px;
                        border: 1px solid black;text-align: left;" >
                              <div class="col-xs-2" style="padding: 0px;">
                                <div >SEÑOR (ES)</div> 
                              </div>
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="DivSenor"></div> 
                              </div>
                              <div class="col-xs-2" style="padding: 0px;">
                                <div id="Div5">FECHA VENCIMIENTO</div>
                              </div>
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="DivFechaVencimiento"></div>
                              </div>

                              <div class="col-xs-2" style="padding: 0px;">
                                <div id="Div3">RUC</div> 
                              </div>
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="DivrRuc"></div> 
                              </div>
                              <div class="col-xs-2" style="padding: 0px;">
                                <div id="Div4">MONEDA</div>
                              </div>
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="Div10">: Soles</div> 
                              </div>

                               <div class="col-xs-2" style="padding: 0px;" >
                                <div id="Div12">FECHA EMISIÓN</div> 
                              </div>
                              <div class="col-xs-4" style="padding: 0px;" >
                                <div id="DivFechaEmision"></div> 
                              </div>
                              <div class="col-xs-2" style="padding: 0px;">
                                <div id="Div6">CONDICIÓN DE PAGO</div>
                              </div>
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="Div">: Contado</div> 
                              </div>

                              <div class="col-xs-2" style="padding: 0px;">
                                <div id="Div7">DIRECCIÓN</div> 
                              </div> 
                              <div class="col-xs-4" style="padding: 0px;">
                                <div id="DivDireccion"></div> 
                              </div> 
                        </div>
                        <div class="row" >
                       <div class="col-xs-12" >
                                <div id="Div1"></div> 
                              </div> 
                              </div> 
                      <table class="table" id="Table1" style="width: 745px;margin-left: 30px;margin-right: 30px;font-size: 10px;">
                                    <colgroup>
                                       <col style="width: 10%"></col>
                                       <col style="width: 8%"></col>
                                    <col style="width: 40%"></col>  
                                    <col style="width: 12%"></col>
                                    <col style="width: 8%"></col> 
                                    <col style="width: 8%"></col>
                                    </colgroup>
                                    <thead id="Thead2">
                                        <tr>
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                CÓDIGO
                                            </th>
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                CANT.
                                            </th>
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                DESCRIPCIÓN
                                            </th>
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                PRECIO UNI.
                                            </th>  
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                DESCUENTO.
                                            </th> 
                                            <th style="text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                TOTAL
                                            </th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                        <div class="col-xs-12" style="PADDING-LEFT: 30px;text-align: left;" id="son_documento"></div>
                        
                        <div class="col-xs-9" style="text-align: right;">Sub Total :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivSubTotal" ></div> 
                        <div class="col-xs-9" style="text-align: right;">IGV :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivIGV" ></div> 
                        <div class="col-xs-9" style="text-align: right;">Total :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivTotal" ></div> 
                        
                        <div class="col-xs-12" style="text-align: center;">Cuéntanos tu experiencia en:</div>
                        <div class="col-xs-12" style="text-align: center;">www.datpos.com</div> 
                        <div class="col-xs-12" style="text-align: center;">Para Consultar El Documento Ingrese</div>
                        <div class="col-xs-12" style="text-align: center;">https://comprobantes.msgsac.net:453/documentos</div>
                        <div class="col-xs-12" style="text-align: center;" id="qrcode"></div>
                
                    </div>

                    <div style="color: white;">.</div>

                </div>
  <div id="ponercanvas" style="margin-top: 0px !important;"></div>

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
                                Usuario
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cliente
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Telef
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
                                Estado
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Observación
                            </th>
                        </tr>
                    </thead>
                     <tbody>
                    </tbody>
                </table>
           </div>
           <div id="Div_DetalladoExpor" style="display:none;" > 
                 <table id="table_DetalladoExpor" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
                 
                    <thead id="Thead1"   >
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
                                Usuario
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cliente
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Telef
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Articulo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Dsc. Articulo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cantidad
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Precio Uni
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Descuento
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Impuesto
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe Bruto
                            </th>
                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe Neto
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Doc.
                            </th>  
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Observación
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Variante
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
