<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Artículos Más Vendidos | DATPOS'; $pageScript = 'ConsultaArticulosMasVendidos.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>
<div class="c-content-center modern-page">
    <div class="modern-page-header">
        <div class="mph-icon"><i class="material-icons">trending_up</i></div>
        <div class="mph-text">
            <h1>Artículos Más Vendidos</h1>
            <p>Consulta de artículos con mayor volumen de ventas por tienda y período.</p>
        </div>
        <div class="mph-spacer"></div>
        <span class="mph-chip"><i class="material-icons">search</i>Consultas</span>
    </div>

    <div class="dp-filters" style="margin-bottom:16px;">
        <div class="dp-filter">
            <label for="txtTienda">Tienda*</label>
            <select class="limpiar form-control moderno_tb" id="txtTienda" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"></select>
        </div>
        <div class="dp-filter">
            <label for="txtfchDesde">Fecha Desde*</label>
            <input id="txtfchDesde" maxlength="10" type="text" class="limpiar form-control moderno_tb" autocomplete="off" placeholder="dd/mm/aaaa" onclick="ObtenerNombreColumna(this)"/>
        </div>
        <div class="dp-filter">
            <label for="txtfchHasta">Fecha Hasta*</label>
            <input id="txtfchHasta" maxlength="10" type="text" class="limpiar form-control moderno_tb" autocomplete="off" placeholder="dd/mm/aaaa" onclick="ObtenerNombreColumna(this)"/>
        </div>
        <div class="dp-filter">
            <label for="txtFamilia">Familia</label>
            <select class="form-control moderno_tb" id="txtFamilia" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"></select>
        </div>
        <div class="dp-filter">
            <label for="txtCodArticulo">Artículo</label>
            <div class="input-group">
                <input id="txtCodArticulo" type="text" class="limpiar form-control moderno_tb" placeholder="Código" onclick="ObtenerNombreColumna(this)"/>
                <a class="disabled input-group-addon" onclick="ModalConsultarArticulo();" style="background-color:#fff;border:0;">
                    <i class="fa fa-search color-buscadores"></i>
                </a>
            </div>
        </div>
    </div>

        <div class="tab-content">
        <div id="Datos" class="tab-pane in active">
                

          <div class="modal" id="modalConsultarArticulo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true"   >
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color:#d4e1e4;" >
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
                                <table  id="tableVisibleConsultaArticulo" class="display" style="width:100%;"    >
                                     <colgroup>  
                                     <col style="width:10%"></col> 
                                    <col style="width:30%"></col> 
                                    <col style="width:60%"></col>
                                </colgroup>
                                    <thead  id="thTablaConsultarArticulo"   >
                                        <tr>
                                         <th class="text-center" style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                  
                                            </th>
                                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Artículo
                                            </th >
                                            <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                                                Nombre del Artículo
                                            </th> 
                                        </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodArticulo();">Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

          <ul class="nav nav-tabs"   class="active">
            <li onclick="">
            <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
            <li onclick="">
            <a data-toggle="tab" href="#Estadisticas" class="tabcito" style="color: #228ac9; font-size: 17px;display:none;">Estadisticas</a></li>
         </ul>
         <div class="tab-content" style="padding-bottom:30px;">
         <!-- LISTADO -->
        <div id="Lista" class="tab-pane in active " style="padding: 13px;">
       
           <!-- Tabla para Visible -->
                <table id="table_visibleDoc" class="display" style="width:100%"  >
                 <colgroup>
                    <col style="width:10%"></col>
                    <col style="width:20%"></col>
                    <col style="width:15%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:20%"></col>
                    <col style="width:10%"></col>
                 </colgroup>
                    <thead id="thTablaVisible" >
                        <tr>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Cod. Caja
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Nombre Caja
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Familia
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Cod. Artículo
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nombre Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cantidad
                            </th>  
                        </tr>
                    </thead>
                     <tbody >
                    </tbody>
                </table>
        </div>

              <!-- Estadisticos -->
                <div id="Estadisticas" class="tab-pane tabcito" style="padding: 13px;">
                 <div class="row"> 
                    <div class="col-sm-6"   >
                        <div id="containerDona" style="padding-top: 30px;" ></div>
                     </div>
                     <div class="col-sm-6" >
                         <div id="containerDonaMenos" style="padding-top: 30px;" ></div>
                    </div>
                  </div>
                </div>
            </div>

        <!-- Tabla para Exportar Secuandaria Buscar Articulo-->
              <div id="tableExportarConsultarArticulo" style="display:none;" > 
                <table id="table_secundariaConsultarArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                <colgroup>  
               
                    <col style="width:30%"></col> 
                    <col style="width:60%"></col> 
                </colgroup> 
                    <thead>
                        <tr> 
                         <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                             Artículo
                        </th >
                        <th  style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                            Nombre del Artículo
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
                         <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Cod. Caja
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Nombre Caja
                            </th>
                             <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Familia
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                 Cod. Artículo
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nombre Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cantidad
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
