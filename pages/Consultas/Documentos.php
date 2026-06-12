<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Documentos | DATPOS';
$pageScript = 'Factura.js';
$showCrudButtons = false;

$ubigeoEmpresa = trim(($o->cdepartamento ?? '') . '-' . ($o->cprovincia ?? '') . '-' . ($o->cdistrito ?? ''), '-');
$ubigeoTienda  = trim(($o->cdepartamento_tienda ?? '') . '-' . ($o->cprovincia_tienda ?? '') . '-' . ($o->cdistrito_tienda ?? ''), '-');

ob_start();
?>
<script src="<?= basePath() ?>/assets/Javascript/Comun.js" type="text/javascript"></script>
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
<label id="lSimMoneda" style="display:none;"><?= e($o->csimbolo_moneda ?? '') ?></label>
<label id="lNomMoneda" style="display:none;"><?= e($o->cnombre_moneda ?? '') ?></label>
<label id="hdd_rucdat" style="display:none;"><?= e($o->ccod_empresa ?? '') ?></label>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($o->ctelf_tienda ?? '') ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($o->cdsc_tienda ?? '') ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e($ubigeoTienda) ?>"/>
<label id="FactElectronica" style="display:none;"><?= e($o->ctip_facturador ?? '') ?></label>
<input id="hdd_ctip_doc" type="hidden"/>
<input id="hdd_id_cbfact" type="hidden"/>

<div class="c-content-center modern-page">
    <div id="zona-imprimir" style="border:outset;width:800px;">
        <div style="width:800px;font-size:10px;">
            <div class="row">
                <div class="col-xs-6" style="width:400px;padding:0 50px;margin:0 30px;text-align:left;">
                    <image id="idlogoTicket" style="width:50px;margin-top:10px;"></image>
                    <div class="col-xs-12" id="nombre_empresa1"></div>
                    <div class="col-xs-12" id="direccion_empresa"></div>
                    <div class="col-xs-12" id="direccionubigeo_empresa"></div>
                </div>
                <div class="col-xs-6" style="width:300px;padding:0 50px;margin:0 30px;border:2px solid black;text-align:center;">
                    <div class="col-xs-12" style="font-size:15px;" id="ruc_empresa">R.U.C.</div>
                    <div class="col-xs-12" style="font-size:15px;">FACTURA ELECTRONICA</div>
                    <div class="col-xs-12" style="font-size:15px;" id="DicSerieNro"></div>
                </div>
            </div>

            <div class="col-xs-12" style="width:745px;padding:0 50px;margin:0 30px;border:2px solid black;text-align:left;">
                <div class="col-xs-3"><div>SEÑOR (ES)</div></div>
                <div class="col-xs-3"><div id="DivSenor"></div></div>
                <div class="col-xs-3"><div>FECHA VENCIMIENTO</div></div>
                <div class="col-xs-3"><div id="DivFechaVencimiento"></div></div>
                <div class="col-xs-3"><div>RUC</div></div>
                <div class="col-xs-3"><div id="DivrRuc"></div></div>
                <div class="col-xs-3"><div>MONEDA</div></div>
                <div class="col-xs-3"><div>: <?= e($o->cnombre_moneda ?? 'Soles') ?></div></div>
                <div class="col-xs-3"><div>FECHA EMISIÓN</div></div>
                <div class="col-xs-3"><div id="DivFechaEmision"></div></div>
                <div class="col-xs-3"><div>CONDICIÓN DE PAGO</div></div>
                <div class="col-xs-3"><div>: Contado</div></div>
                <div class="col-xs-3"><div>DIRECCIÓN</div></div>
                <div class="col-xs-3"><div id="DivDireccion"></div></div>
            </div>

            <table class="table" id="tbArticulo" style="width:745px;padding:0 50px;margin:0 30px;border:2px solid black;text-align:left;">
                <colgroup>
                    <col style="width:10%"><col style="width:8%"><col style="width:40%"><col style="width:8%">
                    <col style="width:8%"><col style="width:8%"><col style="width:8%">
                </colgroup>
                <thead id="thTablaDetalleArticulos">
                    <tr>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">CÓDIGO</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">CANT.</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">DESCRIPCIÓN</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">PRECIO UNI.</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">IGV</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">DESCUENTO</th>
                        <th style="padding:6px 5px;text-align:left;background-color:#525659;color:white;">TOTAL</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="col-xs-12" style="padding-left:30px;text-align:left;" id="son_documento"></div>
            <div class="col-xs-9" style="text-align:right;">Sub Total :</div>
            <div class="col-xs-3" style="padding-right:30px;text-align:right;" id="DivSubTotal"></div>
            <div class="col-xs-9" style="text-align:right;">IGV :</div>
            <div class="col-xs-3" style="padding-right:30px;text-align:right;" id="DivIGV"></div>
            <div class="col-xs-9" style="text-align:right;">Total :</div>
            <div class="col-xs-3" style="padding-right:30px;text-align:right;" id="DivTotal"></div>
            <div class="col-xs-12" style="text-align:center;" id="qrcode"></div>
        </div>
        <div style="color:white;">.</div>
    </div>
    <div id="ponercanvas" style="margin-top:0px !important;"></div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
