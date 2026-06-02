<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Margen Utilidad | DATPOS'; $pageScript = 'MargenUtilidad.js'; $showCrudButtons = false; $showConsultButtons = true;
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
            <div class="row">
                  
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
                 <div class="col-sm-4" style="padding-top: 10px;">
                    <div class="input-group">
                        <span class="has-float-label">
                            <input id="txtCliente"  type="text" class="limpiar form-control moderno_tb"
                                placeholder=" " onclick="ObtenerNombreColumna(this)"/>
                            <label for="txtCliente">
                                Cliente</label>
                        </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes"
                            onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                            <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="row" style="padding-bottom: 30px;">
                 <div class="col-sm-4" style="padding-top: 10px;">
                    <div class="floating-label">
                        <select class="limpiar form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);"
                            value="" id="txtCodDocumento" >
                            <option value="BV">Boleta</option>
                                <option value="FV">Factura</option>
                                <option value="NV">Nota de Venta</option>
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
            <ul class="nav nav-tabs" class="active">
                <li onclick=""><a data-toggle="tab" href="#Lista" class="tabcito" style="color: #228ac9;
                    font-size: 17px;">Lista</a></li>
                <li onclick=""><a data-toggle="tab" href="#Estadisticas" class="tabcito" style="color: #228ac9;
                    font-size: 17px;display:none;">Estadisticas</a></li>
            </ul>
            <div class="tab-content" style="padding-bottom: 30px;">
                <!-- LISTADO -->
                <div id="Lista" class="tab-pane in active " style="padding: 13px;">
                    <!-- Tabla para Visible -->
                    <table id="table_id" class="display" style="width: 100%;">
                        <colgroup>
                            <col style="width: 6%"></col>
                            <col style="width: 7%"></col>
                            <col style="width: 8%"></col>
                            <col style="width: 15%"></col>
                            <col style="width: 9%"></col>
                            <col style="width: 9%"></col>
                            <col style="width: 9%"></col>
                            <col style="width: 9%"></col>
                            <col style="width: 10%"></col>
                            <col style="width: 10%"></col>
                            <col style="width: 8%"></col>
                        </colgroup>
                        <thead id="thTablaArticulo">
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
                                    Cliente
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
                                    Doc. Ref.
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
                                    Fecha Doc.
                                </th>
                                <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">
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
                        <div class="col-sm-6">
                            <div id="containerBarras" style="padding-top: 30px;">
                            </div>
                            <table id="datatable" style="display: none;">
                                <thead>
                                    <tr>
                                        <th>
                                        </th>
                                        <th>
                                            Documentos
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-sm-6">
                            
                        </div>
                    </div>
                </div>
            </div>
            
            <div id="tablePrincipalExportExel" style="display: none;">
                <table id="tableArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                    </colgroup>
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
                                Cliente
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
                                    <tr>
                                        <td style="border: 0px; solid #fff;">
                                            Doc. Ref:
                                        </td>
                                        <td id="upDocRef" style="text-align: right; border: 0px; solid #fff;">
                                        </td>
                                        <td style="border: 0px; solid #fff; width: 68%;">
                                            <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalBuscarCodInve"
                                                onclick="ModalBuscarCodInve();" style="background-color: #e4e2e2; border: 0px;
                                                text-align: left;"><i class="fa fa-arrow-right color-popup-verde" aria-hidden="true">
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
                                            Lista de Articulos
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table" id="tbArticulo" style="width: 100%;">
                                <colgroup>
                                    <col style="width: 10%"></col>
                                    <col style="width: 30%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 10%"></col>
                                </colgroup>
                                <thead id="thTablaDetalleArticulos">
                                    <tr>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White; vertical-align: top;">
                                            Artículo
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White; vertical-align: top;">
                                            Nombre
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White; vertical-align: top;">
                                            Cantidad
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White;">
                                            Importe Tot.
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White;">
                                            Costo Tot.
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White;">
                                            Margen Util.
                                        </th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                            color: White;">
                                            Margen Util. %
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                            <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                <tbody>
                                    <tr>
                                        <td style="border: 0px; solid #fff; width: 54%;">
                                        </td>
                                        <td style="border: 0px; solid #fff;">
                                            Importe Total de Margen de Utilidad :
                                        </td>
                                        <td id="upTotal" style="text-align: right; border: 0px; solid #fff;">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
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
            <!-- Tabla para Exportar Detalle de Articulo-->
            <div id="tableExportarDetalleArticulo" style="display: none;">
                <table id="table_secundariaDetalleArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 30%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                        <col style="width: 8%"></col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Artículo
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Nombre
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Cantidad
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Importe Tot.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Costo Tot.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Margen Util.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215);
                                color: White;">
                                Margen Util. %
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
            <div id="tableExportarConsultarCliente" style="display: none;">
                <table id="table_secundariaConsultarCliente" class="table table-bordered TablaIndex table-striped dataTable no-footer"
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
            <!-- Tabla para Exportar Secuandaria Buscar Empresa-->
            <div id="tableExportarDatosInve" style="display: none;">
                <table id="table_secundariaDatosInve" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 30%"></col>
                        <col style="width: 20%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 15%"></col>
                        <col style="width: 15%"></col>
                    </colgroup>
                    <thead>
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
                                Costo
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
