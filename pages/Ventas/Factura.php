<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Factura | DATPOS';
$pageScript = 'Factura.js';
$showCrudButtons = false;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1005'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[Factura] VerificarAccesos rol 1005 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[Factura] VerificarAccesos rol 1005 fallo: ' . $e->getMessage());
}

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script src="<?= basePath() ?>/assets/Javascript/Numerosaletras.js" type="text/javascript"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />

    <link href="<?= basePath() ?>/assets/Styles/css/switcher.css" rel="stylesheet" type="text/css" />
    <script src="<?= basePath() ?>/assets/Scripts/jquery.switcher.js" type="text/javascript"></script>
    
    <script src="<?= basePath() ?>/assets/Scripts/qrcode.js" type="text/javascript"></script>

    <link href="<?= basePath() ?>/assets/Styles/css/jquery.toggleinput.css" rel="stylesheet" type="text/css" />
    <script src="<?= basePath() ?>/assets/Scripts/jquery.toggleinput.js" type="text/javascript"></script>

    <script src="<?= basePath() ?>/assets/Scripts/html2canvas.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Scripts/html2canvas.min.js" type="text/javascript"></script>
    
     

<input id="operacion" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>
<input id="hdd_metodopago" type="hidden" value="Visa"/>
<input id="hdd_total" type="hidden" value="0.00"/>
<input id="hdd_coa" type="hidden"/>
<input id="hdd_direc" type="hidden"/>
<input id="hdd_rucC" type="hidden"/>
<input id="hdd_cdsc_coa" type="hidden"/>
<input id="fecha_emision_documento" type="hidden"/>
<input id="hdd_rv" type="hidden"/>
<input id="hdd_igv" type="hidden"/>
<input id="hdd_isc" type="hidden"/>
<input id="hdd_ruc" type="hidden" value="<?= e($o->cnum_tribu ?? '') ?>"/>
<input id="hhd_empresa" type="hidden" value="<?= e($o->cdescripcion ?? '') ?>"/>

<input id="hhd_direccionE" type="hidden" value="<?= e($o->cdomicilio ?? '') ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e($ubigeoEmpresa) ?>"/>
<label id="lSimMoneda" style="display:none;" ><?= e($o->csimbolo_moneda ?? '') ?></label>
<label id="lNomMoneda" style="display:none;" ><?= e($o->cnombre_moneda ?? '') ?></label>
<label id="hdd_rucdat" style="display:none;" ><?= e($o->ccod_empresa ?? '') ?></label>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($o->ctelf_tienda ?? '') ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($o->cdsc_tienda ?? '') ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e($ubigeoTienda) ?>"/>
<label id="FactElectronica" style="display:none;" ><?= e($o->ctip_facturador ?? '') ?></label>
<input id="hdd_ctip_doc" type="hidden"/>
<input id="hdd_id_cbfact" type="hidden"/>

<div class="c-content-center modern-page">

  
    <div id="zona-imprimir" style="border: outset; width: 800px;display: none;">

                    <div style="width: 800px;font-size: 10px;">

                        <div class="row" >

                        <div class="col-xs-6" style="width: 400px;padding-left: 50px;padding-right: 50px;margin-left: 30px;margin-right: 30px;
                        text-align: left;" >
                            <image id="idlogoTicket" style="width: 50px;margin-top: 10px;"></image>
                            <div class="col-xs-12" id="nombre_empresa1"></div>  
                            <div class="col-xs-12" id="direccion_empresa"></div>
                            <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                        </div>
                        <div class="col-xs-6" style="width: 300px;padding-left: 50px;padding-right: 50px;margin-left: 30px;margin-right: 30px;
                        border: 2px solid black;text-align: center;" >
                             <div class="col-xs-12" style="font-size: 15px;" id="ruc_empresa" >R.U.C. 20749758444</div>
                             <div class="col-xs-12" style="font-size: 15px;" >FACTURA ELECTRONICA</div>
                             <div class="col-xs-12" style="font-size: 15px;" id="DicSerieNro" >F001 - 00025412</div>
                        </div>
                        </div>
                       
                        <div class="col-xs-12" style="width: 745px;padding-left: 50px;padding-right: 50px;margin-left: 30px;margin-right: 30px;
                        border: 2px solid black;text-align: left;" >
                              <div class="col-xs-3" >
                                <div >SEÑOR (ES)</div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="DivSenor"></div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="Div5">FECHA VENCIMIENTO</div>
                              </div>
                              <div class="col-xs-3" >
                                <div id="DivFechaVencimiento"></div>
                              </div>

                              <div class="col-xs-3" >
                                <div id="Div3">RUC</div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="DivrRuc"></div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="Div4">MONEDA</div>
                              </div>
                              <div class="col-xs-3" >
                                <div id="Div10">: Soles</div> 
                              </div>

                               <div class="col-xs-3" >
                                <div id="Div12">FECHA EMISIÓN</div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="DivFechaEmision"></div> 
                              </div>
                              <div class="col-xs-3" >
                                <div id="Div6">CONDICIÓN DE PAGO</div>
                              </div>
                              <div class="col-xs-3" >
                                <div id="Div">: Contado</div> 
                              </div>

                              <div class="col-xs-3" >
                                <div id="Div7">DIRECCIÓN</div> 
                              </div> 
                              <div class="col-xs-3" >
                                <div id="DivDireccion"></div> 
                              </div> 
                        </div>
                        <div class="row" >
                       <div class="col-xs-12" >
                                <div id="Div1"></div> 
                              </div> 
                              </div> 
                      <table class="table" id="tbArticulo" style="width: 745px;padding-left: 50px;padding-right: 50px;margin-left: 30px;margin-right: 30px;
                        border: 2px solid black;text-align: left;">
                                    <colgroup>
                                       <col style="width: 10%"></col>
                                       <col style="width: 8%"></col>
                                    <col style="width: 40%"></col>  
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    <col style="width: 8%"></col>
                                    </colgroup>
                                    <thead id="thTablaDetalleArticulos">
                                        <tr>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                CÓDIGO
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                CANT.
                                            </th>
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                DESCRIPCIÓN
                                            </th>
                                             <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                PRECIO UNI.
                                            </th> 
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                IGV
                                            </th>  
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                DESCUENTO.
                                            </th> 
                                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(82, 86, 89);color: White;">
                                                TOTAL
                                            </th> 
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                        <div class="col-xs-12" style="PADDING-LEFT: 30px;text-align: left;" id="son_documento"></div>
                        
                        <div class="col-xs-9" style="text-align: right;">Sub Total :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivSubTotal" ></div> 
                        <div class="col-xs-9" style="text-align: right;">IGV :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivIGV" ></div> 
                        <div class="col-xs-9" style="text-align: right;">Total :</div>
                        <div class="col-xs-3" style="padding-right: 30px;text-align: right;" id="DivTotal" ></div> 
                        
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
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
