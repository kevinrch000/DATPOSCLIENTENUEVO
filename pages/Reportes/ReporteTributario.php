<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Reporte Tributario | DATPOS'; $pageScript = 'ReporteTributario.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="operacion" type="hidden" />
    <input id="hdd_ultimafila" type="hidden" />
    <input id="hdd_fila" type="hidden" value="0" />
    <input id="hdd_numeromenus" type="hidden" value="1" />
    <input id="hdd_numerofilas" type="hidden" />

    <input id="hdd_url" type="hidden" value="/Reportes/ReporteVenta.aspx" />

    <div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
      <!-- DATOS --> 
            
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                      <div class="floating-label">   
                       <select class="floating-select"   onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"  value=""   id="txtTienda"  ></select> 
                        <label class="floating-select2">Tienda*</label> 
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

            <div class="row" >
            <div class="col-sm-4" style="padding-top: 10px;">
                <div class="floating-label">
                    <select class="limpiar form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                        value="" id="txtCodDocumento">
                        <option value="BV">Boleta</option>
                        <option value="FV">Factura</option>
                        <option value="NC">Nota de Crédito</option>
                        <option value="ND">Nota de Débito</option>
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
                        value="" id="txtEstTributario">
                        <option value=""></option>
                        <option value="4">Aceptado</option>
                        <option value="5">Aceptado con observaciones</option>
                        <option value="1">Pendiente de envío</option>
                        <option value="6">Error</option> 
                        <option value="8">Anulado</option> 
                    </select>
                    <label class="floating-select2">
                        Estado Tributario</label>
                </div>
            </div>

            <div class="col-sm-4" style="padding-top:10px;" > 
                <div class="input-group">
                    <span  class="has-float-label"   >
                        <input id="txtCliente"    type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                        <label for="txtCliente">Cliente</label>
                    </span> 
                    <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes" onclick="ModalConsultarClientes();" style="background-color: #ffffff;border:0px">
                        <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
                    </a>       
                </div>          
            </div>

                
      </div>
       <div class="tab-content" style="padding-bottom:30px;">
      <div id="Lista" class="tab-pane in active " style="padding: 13px;">
           <!-- Tabla para Visible -->
                <table id="table_id" class="display" style="width:100%"  >
                 <colgroup>
                    <col style="width:15%"></col>
                    <col style="width:5%"></col>
                    <col style="width:8%"></col>
                    <col style="width:8%"></col>
                    <col style="width:15%"></col>
                    <col style="width:15%"></col>
                    <col style="width:15%"></col>
                    <col style="width:5%"></col> 
                    <col style="width:5%"></col> 
                    <col style="width:5%"></col> 
                     </colgroup>
                    <thead id="thTablaVisible"   >
                        <tr>
                         <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cliente
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Documento
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Serie
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Correlativo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe Total
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Fecha Emisión
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Estado
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">PDF</th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">XML</th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">CDR</th>
                        </tr>
                    </thead>
                
                     <tbody >

                    </tbody>
                </table>
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
            </div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
