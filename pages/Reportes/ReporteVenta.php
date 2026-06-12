<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Reporte Venta | DATPOS'; $pageScript = 'ReporteVenta.js'; $showCrudButtons = false; $showConsultButtons = true;
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

            <div class="row" style="padding-bottom: 30px;">
                <div class="col-sm-4" style="padding-top: 10px;">
                      <div class="floating-label">   
                       <select class="floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="txtCodDocumento"  >
                       <option value=""></option>
                       <option value="BV">Boleta</option>
                       <option value="FV">Factura</option>
                       <option value="NV">Nota de Venta</option>
                       <option value="NC">Nota de Credito</option>
                       <option value="ND">Nota de Debito</option>
                       </select> 
                       <label class="floating-select2">Código Doc.</label> 
                      </div>  
                </div>
                
            </div>

            </div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
