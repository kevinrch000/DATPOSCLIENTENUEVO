<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Configuración General | DATPOS'; $pageScript = 'ConfigGeneral.js'; $showCrudButtons = true; $showConsultButtons = false;
$u = getUsuarioSesion();
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="ctipo_doc_salida" type="hidden"/>
 <input id="ctipo_doc_ingreso" type="hidden"/>

<label id="dfchvencimiento" style="display:none;" ><?php echo e($u->dfch_vencimiento ?? ''); ?></label>

   <label id="lNomMoneda" style="display:none;" ><?php echo e($u->cnombre_moneda ?? ''); ?></label>
    <label id="lSimMoneda" style="display:none;" ><?php echo e($u->csimbolo_moneda ?? ''); ?></label>
    <label id="lTarifa" style="display:none;" ><?php echo e($u->ctarifas ?? ''); ?></label>
    <label id="Cantienda" style="display:none;" ><?php echo e($u->ntienda_extra ?? ''); ?></label>
    <label id="CanUsuario" style="display:none;" ><?php echo e($u->nusuario_extra ?? ''); ?></label>
    <label id="NumTributario" style="display:none;" ><?php echo e($u->cnum_tribu ?? ''); ?></label>
    <label id="FactElectronica" style="display:none;" ><?php echo e($u->ctip_facturador ?? ''); ?></label>
<div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
        <!-- DATOS -->
            <div id="Datos" class="tab-pane in active "  >
            <!-- Buscadores --> 

             <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width:60%;">Configuración de Empresa</h4>

            <div class="row">
            <div class="col-sm-6" style="padding-top:10px;" > 
            <div class="col-sm-6"  >
             <div class="input-group">
                       <span  class="has-float-label"   >
                        <input id="txtCodCliBol"   type="text" disabled class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" >
                         
                        <label for="txtCodCliBol">Código Cliente Boleta*</label>
                        </span>
                        <a class="disabled input-group-addon" disabled data-toggle="modal" data-target="#ModalClientes" onclick="ModalConsultarClientes();" style="background-color: #ffffff;border:0px">
                          <i disabled class="disabled fa fa-search color-popup" disabled aria-hidden="true"></i>
                         </a>  
                     </div>
                </div>
                  <div class="col-sm-6"  >
                  <label id="txtNomCliBol" style="padding-top:10px"    ></label>
                    </div>
                </div>
         <div class="col-sm-6" style="padding-top:10px;" >
                      
                      <div class="col-sm-6"  > 
                       <span  class="has-float-label"   >
                        <input id="txtMoneda"    type="text" disabled class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />  
                        <label for="txtMoneda"  >Moneda</label> 
                        </span> 
                          </div>

                </div>  
                 </div>

                <div class="row"> 
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtTarifa" type="text" disabled class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtTarifa">
                                    Tarifa</label>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtNumTri" type="text" disabled class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtNumTri">
                                    Numero Tributario</label>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtCanUsuMax" type="text" disabled class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtCanUsuMax">
                                   Cantidad de Usuario</label>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtCanTieMax" type="text" disabled class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtCanTieMax">
                                    Cantidad de Tienda</label>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtMaxBol" type="text" disabled  class="disabled form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtMaxBol">
                                   Monto maximo en Boleta</label>
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtFactElect" type="text" disabled  class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtFactElect">
                                   Facturación Electronica</label>
                            </span>
                        </div>
                    </div>
                </div>
                 <div class="row">
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <span class="has-float-label">
                                <input id="txtdfchvencimiento" type="text" disabled  class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                <label for="txtdfchvencimiento">
                                   Fecha de Facturación</label>
                            </span>
                        </div>
                    </div> 
                    <div class="col-sm-6" style="padding-top: 10px;">
                        <div class="col-sm-6">
                            <button type="button" class="btn btn-primary" onclick="DescargarManual()" >Manual DATPOS</button>
                        </div>
                    </div>
                </div>
                    <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 60px; width:60%;">Operaciones Logisticas</h4>
                
        <div class="row">
            <div class="col-sm-6" style="padding-top:10px;" >
           <div class="col-sm-6"  >
            <div class="floating-label">  
            <select class="disabled limpiar form-control moderno_tb floating-select" disabled onchange="FunctionOperIngreso()" oninput="this.setAttribute('value', this.value);" onclick="ObtenerNombreColumna(this)" value=" " id="txtCodOpeIng"  >
                         
                </select>
                <label  id="blAutoDevol" class="floating-disable">Oper. Auto. para Devoluciones*</label>
                                 
            
            </div>
            </div>
            <div class="col-sm-6"  >
               <label  id="blCodOpeIngreso"  style="padding-top:10px" ></label>
             </div> 
                 
                 </div>
            <div class="col-sm-6" style="padding-top:10px;" >
                      <div class="col-sm-6"  >
        <div class="floating-label"  >  
            <select class="disabled limpiar form-control moderno_tb floating-select"  disabled onchange="FunctionOperSalida()"  oninput="this.setAttribute('value', this.value);" onclick="ObtenerNombreColumna(this)" value=" " id="txtCodOpeSal"   >
                            
                </select>
            <label id="blAutoSalida" class="floating-disable">Oper. Auto. para Salida*</label>
        </div>
            </div>


                           <div class="col-sm-6"  >
                            <label  id="blCodOpeSalida"  style="padding-top:10px" ></label>
                            </div>
                </div>
               

</div>

 

   <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 60px; width:60%;">Tributos</h4>
    <div class="row">
            <div class="col-sm-6" style="padding-top:10px;" > 
            <div class="col-sm-6"  > 
                       <span  class="has-float-label"   >
                        <input id="txtIGV"   maxlength="14" type="text" disabled class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                        <label for="txtIGV" >Tasa de impuesto a las ventas*</label>
                        </span>
                        
                          </div>
                
                <div class="col-sm-6"  >
                  <label id="Label1" style="padding-top:10px"    ></label>
                    </div>
                </div>
          <div class="col-sm-6" style="padding-top:10px;display: none;" > 
            <div class="col-sm-6"  > 
                       <span  class="has-float-label"   >
                        <input id="txtISC"   maxlength="14" type="text" disabled class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
                        <label for="txtISC"  >Tasa de impuesto selectivo al consumo*      </label>
                        </span>
                        
                          </div>
                
                <div class="col-sm-6"  >
                  <label id="Label2" style="padding-top:10px"    ></label>
                    </div>
                </div>
               
                 </div>


                   <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 60px; width:60%;">Logo de la Empresa</h4>
   <div class="row">
         
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
                 <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
              <div id="tableExportarConsultarCliente" style="display:none;" > 
                <table id="table_secundariaConsultarCliente" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                 
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

          <div class="modal" id="ModalClientes" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true"   >
                    <div class="modal-dialog modal-dialog-centered">
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
           
  </div>
    </div>
      </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
