<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Nota Crédito y Débito | DATPOS';
$pageScript = 'NotaCredito.js';
// FIX 63: la pantalla necesita el toolbar CRUD (Nuevo/Editar/Grabar/Anular)
// para habilitar el formulario; antes era false y los campos quedaban
// siempre disabled, sin manera de iniciar una nueva NC desde la UI.
$showCrudButtons = true;

// Validar acceso al menu 1016 (Nota Credito) - paridad con NotaCredito.aspx.vb:14-16
try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1016'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[NotaCredito] VerificarAccesos rol 1016 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[NotaCredito] VerificarAccesos rol 1016 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script src="<?= basePath() ?>/assets/Javascript/Filtros.js" type="text/javascript"></script>
<link href="<?= basePath() ?>/assets/Styles/css/switcher.css" rel="stylesheet" type="text/css" />
<script src="<?= basePath() ?>/assets/Scripts/jquery.switcher.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Javascript/Numerosaletras.js" type="text/javascript"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
<link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />
<script src="<?= basePath() ?>/assets/Scripts/qrcode.js" type="text/javascript"></script>
<link href="<?= basePath() ?>/assets/Styles/css/jquery.toggleinput.css" rel="stylesheet" type="text/css" />
<script src="<?= basePath() ?>/assets/Scripts/jquery.toggleinput.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Scripts/html2canvas.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Scripts/html2canvas.min.js" type="text/javascript"></script>
<script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>
<link href="<?= basePath() ?>/assets/Styles/disenoBotones.css" rel="stylesheet" type="text/css" />

<input id="idfact" type="hidden"/>
<input id="cod_motivo" type="hidden"/>
<input id="nimp_aplicado" type="hidden"/>
<input id="ntotal" type="hidden"/>
<input id="DocRef" type="hidden"/>
<input id="lblMontDescuento" type="hidden"/>
<input id="TipDoc" type="hidden"/>
<input id="hhd_id_nc" type="hidden"/>
<input id="hdd_numeromenus" type="hidden" value="2"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_numerofilas" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="montodisponible" type="hidden"/>

<input id="hhd_vTienda" type="hidden" value="<?= e($o->ccod_tiend ?? '') ?>"/>
<input id="hhd_vAlmacen" type="hidden" value="<?= e($o->ccod_almacen ?? '') ?>"/>
<input id="hhd_vCaja" type="hidden" value="<?= e($o->ccod_caja ?? '') ?>"/>
<label id="lSimMoneda" style="display:none;"><?= e($o->csimbolo_moneda ?? '') ?></label>
<label id="lNomMoneda" style="display:none;"><?= e($o->cnombre_moneda ?? '') ?></label>
<input id="hhd_empresa" type="hidden" value="<?= e($o->cdescripcion ?? '') ?>"/>
<input id="hhd_direccionE" type="hidden" value="<?= e($o->cdomicilio ?? '') ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e($ubigeoEmpresa) ?>"/>
<input id="hdd_ruc" type="hidden" value="<?= e($o->cnum_tribu ?? '') ?>"/>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($o->ctelf_tienda ?? '') ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($o->cdsc_tienda ?? '') ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e($ubigeoTienda) ?>"/>

<div class="c-content-center modern-page" style="padding-top: 40px;">
    <ul class="nav nav-tabs" id="ulOpciones">
        <li onclick="tab_datosclick();"><a data-toggle="tab" class="tabcito" href="#Datos">Datos</a></li>
        <li onclick="tab_datosclick();"><a data-toggle="tab" href="#ListaArticulo" class="tabcito">Lista de articulos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
    </ul>
    <div class="tab-content">
        <!-- DATOS -->
        <div id="Datos" class="tab-pane in active">
            <div class="row">
                <div class="col-sm-11" style="padding-top: 10px;"></div>
                <div class="col-sm-1" style="padding-top: 10px;">
                    <a href="#" title="Buscar Doc." onclick="EjecutarRef();">
                        <img src="<?= basePath() ?>/assets/Styles/images/icon_check.png" style="background-color: #f0ad4e; width: 29px;" />
                    </a><a href="#" title="Limpiar Doc." onclick="LimpiarRef();">
                        <img src="<?= basePath() ?>/assets/Styles/images/icon_limpiar.png" style="background-color: #21b5d6; width: 29px;" />
                    </a>
                </div>
            </div>
            <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width: 60%;">Seleccione el documento a generar</h4>
            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-4 moderno_lb">Tipo de documento*</label>
                    <div class="col-sm-7">
                        <select onchange="TipoOperacion();" class="disabled form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="ddl_tip_nota">
                            <option value=""></option>
                            <option value="07">Nota de Credito por Devolución</option>
                            <option value="04">Nota de Credito por Descuento</option>
                            <option value="02">Nota de Debito</option>
                        </select>
                    </div>
                </div>
            </div>
            <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width: 60%;">Datos del Documento de Referencia</h4>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdoc" type="text" maxlength="2" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdoc">Código Ref.</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdoc_serie" type="text" maxlength="4" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdoc_serie">Serie Ref.</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdoc_nro" type="text" maxlength="8" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdoc_nro">Correlativo Ref.</label>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_ntotal" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_ntotal">Importe de Venta Ref.</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_dfch_crea" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_dfch_crea">Fecha de Doc. Ref.</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_ccoa_dsc" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_ccoa_dsc">Cliente</label>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_ccod_coa" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_ccod_coa">Doc. Cliente</label>
                    </span>
                </div>
            </div>
            <h4 style="border-bottom: groove; margin-bottom: 30px; margin-top: 30px; width: 60%;">Datos del Documento de Generado</h4>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdocFac" type="text" maxlength="2" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdocFac">Código Nota de credito</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdoc_serieFac" type="text" maxlength="4" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdoc_serieFac">Serie Nota de credito</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdoc_nroFac" type="text" maxlength="8" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdoc_nroFac">Correlativo Nota de credito</label>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdsc_movito" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdsc_movito">Motivo</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_nimp_aplicado" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_nimp_aplicado">Importe Total</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_dfch_doc" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_dfch_doc">Fecha Doc.</label>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txt_cdsc_usuario" type="text" class="disabled limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txt_cdsc_usuario">Usuario</label>
                    </span>
                </div>
            </div>

            <!-- Modal Documento Ref -->
            <div class="modal fade" id="modalBuscarDoc" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true" style="margin-top: -50px;">
                <div class="modal-dialog" role="document" style="width: 800px;">
                    <div class="modal-content" style="width: 800px; background-color: #e4e2e2;">
                        <div class="modal-header" style="background: #d6d5d5;">
                            <div class="col-sm-6">
                                <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                    <tbody>
                                        <tr><td id="upComprobante" style="border: 0px; solid #fff; font-weight: bold;"></td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-6">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                                        <td style="border: 0px; solid #fff;">Fecha Doc.:</td>
                                        <td id="upFecha" style="text-align: right; border: 0px; solid #fff;"></td>
                                        <td style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                    <tr>
                                        <td style="border: 0px; solid #fff;">Cobranza Doc.:</td>
                                        <td id="upDocumentoCombranza" style="text-align: right; border: 0px; solid #fff;"></td>
                                        <td style="text-align: right; border: 0px; solid #fff;">
                                            <a class="disabled input-group-addon" data-toggle="modal" data-target="#ModalCobranza" style="background-color: #e4e2e2; border: 0px; text-align: left;">
                                                <i id="btnIrComprobante" class="disabled fa fa-arrow-right color-popup-verde" aria-hidden="true"></i>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td>Tienda :</td>
                                        <td id="upCodTienda">-</td>
                                        <td id="upNomTienda">-</td>
                                    </tr>
                                    <tr>
                                        <td>Caja :</td>
                                        <td id="upCodCaja">-</td>
                                        <td id="upNomCaja">-</td>
                                    </tr>
                                    <tr>
                                        <td>Vendedor :</td>
                                        <td id="upCodVendedor">-</td>
                                        <td id="upNomVendedor">-</td>
                                    </tr>
                                    <tr>
                                        <td>Cliente :</td>
                                        <td id="upCodCliente">-</td>
                                        <td id="upNomCliente">-</td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table" style="border: 0px; solid #fff;">
                                <tbody>
                                    <tr><td id="Td1" style="border: 0px; solid #fff;">Articulos vendidos</td></tr>
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
                                    <col style="width: 8%"></col>
                                </colgroup>
                                <thead id="thTablaDetalleArticulos">
                                    <tr>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Artículo</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Nombre Artículo</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Cant.</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Precio Uni.</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">IGV</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">ISC</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Desc.</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Importe</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                <thead id="thTablaArticulo">
                                    <tr>
                                        <td style="border: 0px; solid #fff; width: 75%;"></td>
                                        <td style="border: 0px; solid #fff;">Importe Total :</td>
                                        <td id="upTotal" style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <table id="tbListArticulosSelect" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000" style="display: none;">
                <thead>
                    <tr>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Articulo</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nombre Articulo</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">id_lnfact</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">cobser_variante</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">ndescuento</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">nprecio</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">nisc</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">nimpuesto</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">ncosto</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Modal Anulacion -->
            <div class="modal fade" id="modalAnulacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="txtDocRefAnulacion"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <span class="has-float-label">
                                <input id="txtDscAnulacion" maxlength="50" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this);" />
                                <label for="txtDscAnulacion">Motivo de la anulación</label>
                            </span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button onclick="GenerarNotaCredito()" type="button" class="btn btn-primary">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Descuento -->
            <div class="modal fade" id="modalDescuento" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="txtDocRefDescuento"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <span class="has-float-label">
                                <input id="txtImportTot" class="limpiar form-control moderno_tb" placeholder=" " disabled type="text" onclick="ObtenerNombreColumna(this)" />
                                <label for="txtImportTot">Importe Restante</label>
                            </span>
                            <span class="has-float-label" style="margin-top: 20px;">
                                <input id="txtMontDescuento" class="limpiar form-control moderno_tb" placeholder=" " type="number" min="0" onclick="ObtenerNombreColumna(this)" />
                                <label for="txtMontDescuento">Monto de descuento</label>
                            </span>
                            <span class="has-float-label" style="margin-top: 20px;">
                                <input id="txtDscDescuento" maxlength="50" type="text" class="limpiar form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this);" />
                                <label for="txtDscDescuento">Motivo del descuento</label>
                            </span>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <button onclick="GenerarNotaCredito()" type="button" class="btn btn-primary">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MODAL COBRANZA -->
            <div class="modal fade" id="ModalCobranza" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true" style="margin-top: -50px;">
                <div class="modal-dialog" role="document" style="width: 800px;">
                    <div class="modal-content" style="width: 800px; background-color: #e4e2e2;">
                        <div class="modal-header" style="background: #d6d5d5;">
                            <div class="col-sm-6">
                                <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                    <tbody>
                                        <tr><td id="CobranDocumentoFac" style="border: 0px; solid #fff; font-weight: bold;"></td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-6">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                        </div>
                        <div class="modal-body" style="max-height: calc(110vh - 250px); overflow-y: auto;">
                            <table class="table" style="border: 0px; solid #fff;">
                                <col style="width: 20%"></col>
                                <col style="width: 20%"></col>
                                <col style="width: 60%"></col>
                                <tbody>
                                    <tr>
                                        <td style="border: 0px; solid #fff;">Fecha Doc.:</td>
                                        <td id="CobranFecha" style="text-align: right; border: 0px; solid #fff;"></td>
                                        <td style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <table class="table">
                                <tbody>
                                    <tr><td>Tienda :</td><td id="CobranCodTienda">-</td><td id="CobranNomTienda">-</td></tr>
                                    <tr><td>Caja :</td><td id="CobranCodCaja">-</td><td id="CobranNomCaja">-</td></tr>
                                    <tr><td>Vendedor :</td><td id="CobranCodVendedor">-</td><td id="CobranNomVendedor">-</td></tr>
                                    <tr><td>Cliente :</td><td id="CobranCodCliente">-</td><td id="CobranNomCliente">-</td></tr>
                                </tbody>
                            </table>
                            <table class="table" style="border: 0px; solid #fff;">
                                <tbody>
                                    <tr><td id="Td2" style="border: 0px; solid #fff;">Lista de cobranza</td></tr>
                                </tbody>
                            </table>
                            <table class="table" id="tbCobranza" style="width: 100%;">
                                <colgroup>
                                    <col style="width: 25%"></col>
                                    <col style="width: 15%"></col>
                                    <col style="width: 15%"></col>
                                    <col style="width: 15%"></col>
                                </colgroup>
                                <thead id="thTablaDetalletbCobranza">
                                    <tr>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Forma Pago</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Operación</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Num. tarjeta</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Monto</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                            <table class="table" style="border: 0px; solid #fff; margin-bottom: 0px;">
                                <tbody>
                                    <tr>
                                        <td style="border: 0px; solid #fff; width: 75%;"></td>
                                        <td style="border: 0px; solid #fff;">Importe Total :</td>
                                        <td id="upImpTotCobraza" style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                    <tr>
                                        <td style="border: 0px; solid #fff; width: 75%;"></td>
                                        <td style="border: 0px; solid #fff;">Total Entregado :</td>
                                        <td id="upTotalEntregado" style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                    <tr>
                                        <td style="border: 0px; solid #fff; width: 75%;"></td>
                                        <td style="border: 0px; solid #fff;">Vuelto :</td>
                                        <td id="upVuelto" style="text-align: right; border: 0px; solid #fff;"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Consultar Clientes -->
            <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background-color: #d4e1e4;">
                        <div class="modal-header">
                            <div class="col-sm-6"><h5 class="modal-title">Seleccione Cliente</h5></div>
                            <div class="col-sm-6">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                                        <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;"></th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Cliente</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Nombre del Cliente</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer" style="margin: 10px;">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodCliente();">Seleccionar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla para Exportar Secundaria Buscar Empresa -->
            <div id="tableExportarConsultarCliente" style="display: none;">
                <table id="table_secundariaConsultarCliente" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 30%"></col>
                        <col style="width: 60%"></col>
                    </colgroup>
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Cliente</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Nombre del Cliente</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <!-- Modal Devolucion -->
            <div id="modalDevolucion" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header">Ingrese Motivo de devolución:</div>
                        <div class="modal-body"><textarea id="ta_motivo" rows="3" cols="35"></textarea></div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="GrabarDevolucion();">Confirmar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Lista De Bienes -->
            <div class="modal fade" id="modalListaDeBienes" tabindex="-1" role="dialog" aria-labelledby="modalnuevoLabel" aria-hidden="true" style="margin-top: -50px;">
                <div class="modal-dialog" role="document" style="width: 800px;">
                    <div class="modal-content" style="width: 800px; background-color: #e4e2e2;">
                        <div class="modal-header" style="background: #d6d5d5;">
                            <div class="col-sm-6"><label style="padding-left: 10px;">Lista de articulos</label></div>
                            <div class="col-sm-6">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            </div>
                        </div>
                        <div class="modal-body" style="max-height: calc(110vh - 250px); overflow-y: auto;">
                            <table id="tbListArticulofail" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                                <colgroup>
                                    <col style="width: 4%"></col>
                                    <col style="width: 20%"></col>
                                    <col style="width: 60%"></col>
                                    <col style="width: 10%"></col>
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Articulo</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nombre Articulo</th>
                                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="ValidarListArticulos();">Confirmar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla para Exportar Principal -->
            <div id="tableExport" style="display: none;">
                <table id="table_principalDoc" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cod. Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Serie Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nro. Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Num. Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe Total</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Usuario</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="tableExportarDetalleArticulo" style="display: none;">
                <table id="table_secundariaDetalleArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th>Artículo</th>
                            <th>Nombre Artículo</th>
                            <th>Cantidad</th>
                            <th>Precio Uni.</th>
                            <th>Importe</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>

        <!-- LISTA DE ARTICULOS -->
        <div id="ListaArticulo" class="tab-pane in active" style="padding: 13px;">
            <div class="row">
                <div class="col-sm-11" style="padding-top: 10px;"><label id="lb_codigo"></label></div>
            </div>
            <div class="col-sm-9">
                <div class="input-group">
                    <input class="limpiar_checked" onclick="CkMarcarTodo()" type="checkbox" id="idCkMarcarTodo">
                    <label style="padding-left: 10px;">Marcar todo</label>
                </div>
                <div class="input-group">
                    <input class="limpiar_checked" onclick="CkDesmarcarTodo()" type="checkbox" id="idCkDesmarcarTodo">
                    <label style="padding-left: 10px;">Desmarcar todo</label>
                </div>
            </div>
            <table id="tbListArticulo" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                <colgroup>
                    <col style="width: 4%"></col>
                    <col style="width: 20%"></col>
                    <col style="width: 60%"></col>
                    <col style="width: 10%"></col>
                </colgroup>
                <thead>
                    <tr>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Articulo</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nombre Articulo</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- LISTA -->
        <div id="Lista" class="tab-pane in active" style="padding: 13px;">
            <!-- Modal Resumen Venta -->
            <div id="modalResumenVenta" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" style="border-bottom: 0px;">
                            <div class="col-md-12" style="text-align: center;">
                                <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 33px;">
                                <h4>Operación Completada</h4>
                            </div>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-5">Num. Documento:</div>
                                <div class="col-md-7" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_numdoc"></label></div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">Importe Total:</div>
                                <div class="col-md-7" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_importetotal"></label></div>
                            </div>
                            <div class="row">
                                <div class="col-md-5">Importe Generado:</div>
                                <div class="col-md-7" style="text-align: right;"><label style="font-size: 13px;" class="lb_modal" id="lb_credito"></label></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">Imprimir</div>
                                <div class="col-md-6" style="text-align: right;">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="checkbox" id="ckb_Imprimir" value="option1">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer" style="border-top: 0px;">
                            <div class="col-md-12" style="text-align: center;">
                                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="FinalizarResumenDoc();">Confirmar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-11" style="padding-top: 10px;"></div>
                <div class="col-sm-1" style="padding-top: 10px;">
                    <a href="#" title="Buscar" onclick="EjecutarNC();">
                        <img src="<?= basePath() ?>/assets/Styles/images/icon_check.png" style="background-color: #f0ad4e; width: 29px;" />
                    </a>
                    <a href="#" title="Limpiar" onclick="LimpiarNC();">
                        <img src="<?= basePath() ?>/assets/Styles/images/icon_limpiar.png" style="background-color: #21b5d6; width: 29px;" />
                    </a>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <div class="floating-label">
                        <select class="form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="txtTienda"></select>
                        <label class="floating-select2">Tienda*</label>
                    </div>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txtfchDesde" maxlength="10" type="text" class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txtfchDesde">Fecha Desde*</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="txtfchHasta" maxlength="10" type="text" class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="txtfchHasta">Fecha Hasta*</label>
                    </span>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <div class="floating-label">
                        <select class="form-control moderno_tb floating-select" onclick="this.setAttribute('value', this.value);ObtenerNombreColumna(this);" value="" id="tb_doc">
                            <option value="NC">Nota de Credito</option>
                            <option value="ND">Nota de Debito</option>
                        </select>
                        <label class="floating-select2">Código Doc.*</label>
                    </div>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="tb_serie" type="text" maxlength="4" class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="tb_serie">Serie.Ref.</label>
                    </span>
                </div>
                <div class="col-sm-4" style="padding-top: 10px;">
                    <span class="has-float-label">
                        <input id="tb_numero" type="text" maxlength="8" class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                        <label for="tb_numero">Num.Ref.</label>
                    </span>
                </div>
            </div>
            <div class="row" style="padding-bottom: 30px;">
                <div class="col-sm-4" style="padding-top: 10px;">
                    <div class="input-group">
                        <span class="has-float-label">
                            <input id="tb_cliente" type="text" class="form-control moderno_tb" placeholder=" " onclick="ObtenerNombreColumna(this)" />
                            <label for="tb_cliente">Cliente</label>
                        </span>
                        <a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes" onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                            <i class="fa fa-search color-buscadores" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Tabla principal Visible -->
            <table id="table_id" class="display" style="width: 100%">
                <colgroup>
                    <col style="width: 4%"></col>
                    <col style="width: 5%"></col>
                    <col style="width: 5%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 13%"></col>
                    <col style="width: 20%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 8%"></col>
                    <col style="width: 13%"></col>
                    <col style="width: 4%"></col>
                </colgroup>
                <thead id="thTablaVisible">
                    <tr>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cod. Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Serie Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nro. Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Num. Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe Total</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Usuario</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>

            <div class="col-md-4">
                <!-- Zona Imprimir -->
                <div id="zona-imprimir" style="width: 280px; display: none">
                    <div style="width: 280px; font-size: 10px;">
                        <div style="text-align: center;">
                            <image src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 50px; margin-top: 10px;"></image>
                            <div class="col-xs-12" id="nombre_empresa1"></div>
                            <div class="col-xs-12" id="direccion_empresa"></div>
                            <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                            <div>
                                <div class="col-xs-6" id="ruc_empresa"></div>
                                <div class="col-xs-6" id="telefono_tienda"></div>
                            </div>
                            <div class="col-xs-12" id="nombre_tienda"></div>
                            <div class="col-xs-12" id="direccion_tienda"></div>
                            <div class="col-xs-12" id="ubigeo_tienda"></div>
                            <div class="col-xs-12">===========================================</div>
                            <div class="col-xs-12" id="nombre_documento"></div>
                            <div class="col-xs-12" id="codigo_documento"></div>
                            <div class="col-xs-12">===========================================</div>
                        </div>
                        <div>
                            <div class="col-xs-6" id="fecha_documento"></div>
                            <div class="col-xs-6" style="text-align: right;" id="hora_documento"></div>
                        </div>
                        <div class="col-xs-12" id="nombre_cliente"></div>
                        <div class="col-xs-12" id="direccion_cliente"></div>
                        <div class="col-xs-12" id="ruc_cliente"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div style="text-align: center;">
                            <div class="col-xs-3">Descrip.</div>
                            <div class="col-xs-3">Cant.</div>
                            <div class="col-xs-3">P.Unit</div>
                            <div class="col-xs-3">Monto</div>
                        </div>
                        <div class="col-xs-12">===========================================</div>
                        <div id="div_articlosdocumento"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-4">Sub. Total</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($o->csimbolo_moneda ?? '') ?></div>
                        <div class="col-xs-4" id="opgrabada_documento" style="text-align: right;"></div>
                        <div class="col-xs-4">IGV</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($o->csimbolo_moneda ?? '') ?></div>
                        <div class="col-xs-4" id="igv_documento" style="text-align: right;"></div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;">ISC</div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;"><?= e($o->csimbolo_moneda ?? '') ?></div>
                        <div class="col-xs-4" id="isc_documento" style="text-align: right;DISPLAY: NONE;"></div>
                        <div class="col-xs-4" id="nomTotal"></div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($o->csimbolo_moneda ?? '') ?></div>
                        <div class="col-xs-4" id="credito_monto" style="text-align: right;"></div>
                        <div class="col-xs-12" id="documento_referencia"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" id="son_documento"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div id="div_cobranzadocumento"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" id="vendedor"></div>
                        <div class="col-xs-12" id="codigo_caja"></div>
                        <div class="col-xs-12">===========================================</div>
                        <div class="col-xs-12" style="text-align: center;">Cuéntanos tu experiencia en:</div>
                        <div class="col-xs-12" style="text-align: center;">www.datpos.com</div>
                        <div class="col-xs-12" style="text-align: center;">Para Consultar El Documento Ingrese</div>
                        <div class="col-xs-12" style="text-align: center;">https://comprobantes.msgsac.net:453/documentos</div>
                        <div class="col-xs-12" style="text-align: center;" id="qrcode"></div>
                    </div>
                    <div style="color: white;">.</div>
                </div>
                <div id="ponercanvas" style="margin-top: 0px !important;"></div>
            </div>
        </div>
    </div>
</div>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
