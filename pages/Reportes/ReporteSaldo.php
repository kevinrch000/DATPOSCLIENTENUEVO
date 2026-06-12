<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Reporte Saldo | DATPOS'; $pageScript = 'ReporteSaldo.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="operacion" type="hidden" />
    <input id="hdd_ultimafila" type="hidden" />
    <input id="hdd_fila" type="hidden" value="0" />
    <input id="hdd_numeromenus" type="hidden" value="1" />
    <input id="hdd_numerofilas" type="hidden" />
    

<div class="c-content-center modern-page" style="padding-top:40px;"  >
        <div class="tab-content"> 
      <!-- DATOS -->
         
            <div class="row">
                 
                <div class="col-sm-4" style="padding-top: 10px;">
                <div class="floating-label">  
                       <select class="floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="txtAlmacen"  ></select> 
                        <label class="floating-select2">Almacén*</label> 
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
                    <div class="input-group">
                        <span class="has-float-label">
                            <input id="txtCodArticulo"  type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="txtCodArticulo">
                                Artículo</label>
                        </span><a class="disabled input-group-addon" 
                            onclick="ModalConsultarArticulos();" style="background-color: #ffffff; border: 0px">
                            <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>

 
  
         
               
        <div class="modal" id="modalConsultarArticulos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"   >
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color:#d4e1e4;">
                            <div class="modal-header" >
                                  <div class="col-sm-6"  >
                                <h5 class="modal-title"  >Seleccione Artículo</h5>
                                </div>
                                <div class="col-sm-6"  >
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                 </div>
                            </div>
 

                            <div class="modal-body" style="margin: 10px;" >
                                <table  id="table_visible_ConsultarArticulos" class="display" style="width:100%;"    >
                                     <colgroup>
                                     <col style="width:10%"></col> 
                                    <col style="width:30%"></col> 
                                    <col style="width:60%"></col>
                                </colgroup>
                                    <thead  id="thTablaConsultarArticulos"   >
                                        <tr>
                                         <th class="text-center" style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                  
                                            </th>
                                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                 Artículo
                                            </th >
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Nombre de Artículo
                                            </th> 
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>

                            </div>
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodEmpresa();">Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
            <div id="tableExportarConsultarArticulos" style="display: none;">
                <table id="table_secundariaConsultarArticulos" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <colgroup>
                        <col style="width: 30%"></col>
                        <col style="width: 60%"></col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Artículo
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Nombre de Artículo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
</div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
