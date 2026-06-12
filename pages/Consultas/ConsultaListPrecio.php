<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Consulta Lista de Precios | DATPOS'; $pageScript = 'ConsultaListPrecio.js'; $showCrudButtons = false; $showConsultButtons = true;
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
         
 <div class="row"  >
 <div class="col-sm-4" style="padding-top:10px;" >
        <div class="floating-label">   
        <select class="floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="slListPrec" >
        </select>
        <label  class="floating-select2"  >Lista de precios*</label>
        </div>
    </div> 
    <div class="col-sm-4" style="padding-top:10px;" > 
        <div class="floating-label">   
        <select class="floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="txtFamilia" >
        </select>
        <label  class="floating-select2"  >Familia*</label>
        </div>
    </div>
    <div class="col-sm-4" style="padding-top:10px;" > 
        <div class="floating-label">   
        <select class="floating-select"  onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value=""  id="slUniMedida"  >
        </select>
        <label  class="floating-select2"  >Unidad de Medida*</label>
        </div>
    </div>  
     

    <div class="col-sm-4" style="padding-top:10px;display:none;" >       
        <span  class="has-float-label"   >
        <input id="txtNomAticulo"   maxlength="100" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
        <label for="txtNomAticulo">Nombre de Artículo</label>
        </span>            
    </div>
     
         
</div>

 <div class="row"  style="padding-bottom:30px;" >
  <div class="col-sm-4" style="padding-top:10px;" >
        <div class="input-group">
        <span  class="has-float-label"   >
        <input id="txtCodArticulo" disabled type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" /> 
        <label for="txtCodArticulo">Código de Artículo</label>
        </span>
        <a class="disabled input-group-addon"  onclick="ModalConsultarArticulos();" style="background-color: #ffffff;border:0px">
        <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
        </a>          
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
            <table id="table_id"   class="display" style="width:100%;">
                 <colgroup>  
                    <col style="width:10%"></col>
                    <col style="width:20%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    </colgroup>
                    <thead id="thTablaArticulo">
                        <tr>
                           <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Lista Precio
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nom. Lista Precio
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nom. Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Familia
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Uni. Med.
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               Costo
                            </th> 
                        </tr>
                    </thead>
                    <tbody ondblclick="table_two_click(this);" onclick="table_one_click(this);">

                    </tbody>
                </table>
                  </div>
              <!-- Estadisticos -->
                <div id="Estadisticas" class="tab-pane tabcito" style="padding: 13px;">
                 <div class="row"> 
                    <div class="col-sm-6"   >
                        <div id="containerCemiCirculo" style="padding-top: 30px;" ></div>
                     </div>
                     <div class="col-sm-6" >
                        
                    </div>
                  </div>
                </div>
            </div>
                
                <div id="tablePrincipalExportExel" style="display:none;" > 
                <table id="tableArticulo"  class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" >
                <colgroup>  
                     <col style="width:10%"></col>
                    <col style="width:20%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col> 
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                    <col style="width:10%"></col>
                </colgroup>
                    <thead >
                        <tr>
                           <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Lista Precio
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nom. Lista Precio
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Cod. Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Nom. Artículo
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Familia
                            </th>
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                Uni. Med.
                            </th> 
                            <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                               Costo
                            </th> 
                        </tr>
                    </thead>
                    <tbody >

                    </tbody>
                </table>
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
              <div id="tableExportarConsultarArticulos" style="display:none;" > 
                <table id="table_secundariaConsultarArticulos" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000"  >
                <colgroup>
                    
                                    <col style="width:30%"></col> 
                                    <col style="width:60%"></col>
                </colgroup> 
                    <thead>
                        <tr> 
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
                             Artículo
                        </th >
                        <th style="padding: 6px 5px;text-align: left; border: solid 1px #e8eef4; background-color:rgb(33, 182, 215);color: White;">
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
