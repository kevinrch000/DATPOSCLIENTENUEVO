<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Margen Utilidad por Día | DATPOS'; $pageScript = 'ConsultaMargenUtilidadDia.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
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
                <div class="col-sm-4" style="padding-top:10px;" >
                      <div class="floating-label"> 
                       <select  class="floating-select limpiar form-control moderno_tb" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="txtTienda"   >
                            </select>
                        <label class="floating-select2"  >Tienda*</label>
                          
                </div>
             </div>
             <div class="col-sm-4" style="padding-top:10px;" >
             <div class="floating-label"> 
                       <select  class="floating-select limpiar form-control moderno_tb" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="txtCaja"   >
                            </select>
                        <label class="floating-select2"  >Caja*</label> 
                </div>
                 </div>
                
                 <div class="col-sm-4" style="padding-top:10px;" >

                      <span  class="has-float-label"   >
                                <input id="txtfchDesde"   maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                 <label for="txtfchDesde"  >Fecha Desde*</label>
                             </span>
                           </div>
                
                 
            </div>    
            <div class="row" style="padding-bottom:30px;">
                  
         <div class="col-sm-4" style="padding-top:10px;" >

                             <span  class="has-float-label"   >
                                <input id="txtfchHasta"   maxlength="10" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                                 <label for="txtfchHasta"  >Fecha Hasta*</label>
                             </span>
                    </div>
                       
</div>    
 

        <ul class="nav nav-tabs"  > 
            <li onclick=""><a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li> 
            <li onclick=""><a data-toggle="tab" href="#DatosAdicionales" class="tabcito">Datos Adicionales</a></li>
         </ul>
         <div class="tab-content" style="padding-bottom:30px;">
         <!-- LISTADO -->
        <div id="Lista" class="tab-pane in active " style="padding: 13px;">
         <!-- Tabla para Visible -->
                <table id="table_visibleDoc" class="display" style="width:100%"  >
                 <colgroup>
                    <col style="width: 4%"></col>
                    <col style="width: 10%"></col>
                    <col style="width: 4%"></col>
                    <col style="width: 10%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 8%"></col> 
                    <col style="width: 8%"></col>
                </colgroup>
                    <thead id="thTablaVisible"   >
                        <tr> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Tienda
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Nombre Tienda
                            </th>
                              <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Caja
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nombre Caja
                            </th>  
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe Total
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Costo Total
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Margen Utilidad
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Margen Utilidad %
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
         <!-- Estadisticos -->
        <div id="DatosAdicionales" class="tab-pane tabcito" style="padding: 13px;">
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txtImpTotA" disabled type="text" class="limpiar form-control moderno_tb"
                            placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txtImpTotA">
                            Importe Total</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txtCosTotA" disabled type="text" class="limpiar form-control moderno_tb"
                            placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txtCosTotA">
                            Costo Total</label>
                    </span>
                </div> 
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txtMarUtiA" disabled type="text" class="limpiar form-control moderno_tb"
                            placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txtMarUtiA">
                            Margen Utilidad</label>
                    </span>
                </div> 
            </div>

        </div>
           
 
  </div>
        
         

           
            <!-- Tabla para Exportar Principal-->
               <div id="tableExport" style="display:none;" > 
                <table id="table_principalDoc" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px"  cellspacing="0" width="2000"   >
                 <thead>
                       <tr> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Tienda
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Nombre Tienda
                            </th>
                              <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Caja
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nombre Caja
                            </th>  
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Importe Total
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Costo Total
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Margen Utilidad
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Margen Utilidad %
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
 
    

                 
  </div>
    </div>
      </div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
