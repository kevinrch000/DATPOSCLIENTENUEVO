<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Consulta Stock Mínimo | DATPOS'; $pageScript = 'ConsultaStockMinimo1.js'; $showCrudButtons = false; $showConsultButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/><input id="hdd_numerofilas" type="hidden"/>
<input id="operacion" type="hidden" />
    <input id="hdd_ultimafila" type="hidden" />
    <input id="hdd_fila" type="hidden" value="0" />
    <input id="hdd_numeromenus" type="hidden" value="1" />
    <input id="hdd_numerofilas" type="hidden" />
    <div class="c-content-center modern-page" style="padding-top: 40px;">
        <div class="tab-content">
            <!-- DATOS -->
            <div id="Datos" class="tab-pane in active ">
                <!-- Buscadores -->
                <div class="row" >
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtAlmacen">
                            </select>
                            <label class="floating-select2">
                                Almacén*</label>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="floating-label">
                            <select class="floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtFamilia">
                            </select>
                            <label class="floating-select2">
                                Familia*</label>
                        </div>
                    </div>
                     
                    <div class="col-sm-4" style="padding-top:10px;" > 
                      <div class="floating-label">
                            <select class="floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                                value="" id="txtStock"> 
                                <option value="Minimo">Minimo</option>
                                <option value="Maximo">Maximo</option>
                            </select>
                            <label class="floating-select2">
                                Stock</label>
                        </div>
                    </div>
                </div>

                <div class="row" style="padding-bottom: 30px;">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="txtCodArticulo"  type="text"   class="limpiar form-control moderno_tb" onclick="ObtenerNombreColumna(this)"
                                    placeholder=" " />
                                <label for="txtCodArticulo">
                                    Artículo</label>
                            </span><a class="disabled input-group-addon"  
                                onclick="ModalConsultarArticulos();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" class="active">
                    <li onclick=""><a data-toggle="tab" href="#Lista" class="tabcito" style="color: #228ac9;
                        font-size: 17px;">Lista</a></li>
                </ul>
                <div class="modal" id="modalConsultarArticulos" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content" style="background-color: #d4e1e4;">
                            <div class="modal-header">
                                <div class="col-sm-6">
                                    <h5 class="modal-title">
                                        Seleccione Artículo</h5>
                                </div>
                                <div class="col-sm-6">
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            </div>
                            <div class="modal-body" style="margin: 10px;">
                                <table id="table_visible_ConsultarArticulos" class="display" style="width: 100%;">
                                    <colgroup>
                                        <col style="width: 10%"></col>
                                        <col style="width: 30%"></col>
                                        <col style="width: 60%"></col>
                                    </colgroup>
                                    <thead id="thTablaConsultarArticulos">
                                        <tr>
                                            <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4;
                                                background-color: rgb(33, 182, 215); color: White;">
                                            </th>
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
                            <div class="modal-footer" style="margin: 10px;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodEmpresa();">
                                    Seleccionar</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-content" style="padding-bottom: 30px;">
                    <!-- LISTADO -->
                    <div id="Lista" class="tab-pane in active " style="padding: 13px;">
                        <!-- Tabla para Visible -->
                        <table id="table_visible" class="display" style="width: 100%;">
                            <colgroup>
                                <col style="width: 10%"></col>
                                <col style="width: 15%"></col>
                                <col style="width: 20%"></col>
                                <col style="width: 15%"></col>
                                <col style="width: 10%"></col>
                                <col style="width: 10%"></col>
                                <col style="width: 10%"></col> 
                            </colgroup>
                            <thead id="thTablaVisible">
                                <tr>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Almacén
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Artículo
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Nombre de Artículo
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Familia
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Cantidad
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Cantidad Minimo
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                        Cantidad Maximo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                   
                </div>
                <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
                <div id="tableExportarConsultarArticulos" style="display: none;">
                    <table id="table_secundariaConsultarArticulos" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                        border="2px" cellspacing="0" width="2000">
                        <colgroup>
                            <col style="width: 10%"></col>
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
                <!-- Tabla para Exportar Principal-->
                <div id="tableExport" style="display: none;">
                    <table id="table_principal" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                        border="2px" cellspacing="0" width="2000"> 
                        <thead>
                            <tr>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Almacén
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Artículo
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Nombre de Artículo
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Familia
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Cantidad
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Stock Minimo
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Stock Maximo
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
