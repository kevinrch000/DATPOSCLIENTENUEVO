<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();
$o = getUsuarioSesion();
$pageTitle = 'Apertura de turno | DATPOS';
$pageScript = 'AperturaCaja.js';
$showCrudButtons = true;

try {
    $accesoRows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
        '@ccod_cia' => $o->ccod_empresa ?? '',
        '@id_rol' => $o->id_rol ?? 0,
        '@id_menu' => '1014'
    ), $o);
    if (empty($accesoRows)) {
        error_log('[AperturaCaja] VerificarAccesos rol 1014 sin filas; acceso permitido temporalmente durante migracion');
    }
} catch (Exception $e) {
    error_log('[AperturaCaja] VerificarAccesos rol 1014 fallo: ' . $e->getMessage());
}

ob_start();
?>
<input id="operacion" type="hidden"/>
<input id="hdd_id" value="0" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="1"/>
<input id="hdd_numerofilas" type="hidden"/>
<input id="hdd_id_turno" value="0" type="hidden"/>

<div class="c-content-center modern-page">
<ul class="nav nav-tabs">
    <li onclick="tab_datosclick();" class="active">
        <a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a>
    </li>
    <li onclick="tab_listaclick();">
        <a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a>
    </li>
</ul>

<div class="tab-content">
    <!-- DATOS -->
    <div id="Datos" class="tab-pane in active" style="padding:13px;">
        <h4 style="border-bottom:groove;margin-bottom:30px;margin-top:30px;width:60%;">Información General</h4>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-12 col-xs-12">
                <label class="col-sm-1 moderno_lb">Fecha Inicial*</label>
                <div class="col-sm-4">
                    <input id="lbFchInicial" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"/>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-12 col-xs-12">
                <label class="col-sm-1 moderno_lb">*Tienda</label>
                <div class="col-sm-4">
                    <select id="SlcCodTienda" onchange="cambiartienda()" class="disabled form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"></select>
                </div>
                <label id="lbcDscTienda" class="col-sm-7 moderno_lb limpiar"></label>
            </div>
        </div>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-12 col-xs-12">
                <label class="col-sm-1 moderno_lb">*Usuario</label>
                <div class="col-sm-4">
                    <select id="SlcCodUsuario" onchange="SelectDscUsuario()" class="disabled form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)"></select>
                </div>
                <label id="lbcDscUsuario" class="col-sm-7 moderno_lb limpiar"></label>
            </div>
        </div>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-12 col-xs-12">
                <label class="col-sm-1 moderno_lb">Caja</label>
                <div class="col-sm-4">
                    <label id="SlcCodCaja" class="moderno_lb limpiar"></label>
                </div>
                <label id="lbcDscCaja" class="col-sm-7 moderno_lb limpiar"></label>
            </div>
        </div>

        <div class="row" style="margin-top:20px;">
            <div class="col-sm-12 col-xs-12">
                <label class="col-sm-1 moderno_lb">Monto Inicial*</label>
                <div class="col-sm-4">
                    <input id="tb_MontIni" class="disabled limpiar form-control moderno_tb" disabled type="number" min="0" onfocusout="PerdidaFoco(this);" onclick="ObtenerNombreColumna(this)"/>
                </div>
            </div>
        </div>
    </div>

    <!-- LISTA -->
    <div id="Lista" class="tab-pane tabcito" style="padding:13px;">
        <table id="table_id" class="display" style="width:100%;">
            <colgroup>
                <col style="width:1%">
                <col style="width:1%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:10%">
                <col style="width:2%">
                <col style="width:12%">
                <col style="width:2%">
            </colgroup>
            <thead>
                <tr>
                    <th></th>
                    <th>Id turno</th>
                    <th>Tienda</th>
                    <th>Usuario</th>
                    <th>Caja</th>
                    <th>Monto Ini.</th>
                    <th>Fecha Apertura</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody ondblclick="table_two_click(this);"></tbody>
        </table>

        <div id="tablePrincipalExportExel" style="display:none;">
            <table id="tableCierreCaja" class="table table-bordered TablaIndex table-striped dataTable no-footer" border="2px" cellspacing="0" width="2000">
                <colgroup>
                    <col style="width:1%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:10%">
                    <col style="width:2%">
                    <col style="width:12%">
                    <col style="width:2%">
                </colgroup>
                <thead>
                    <tr>
                        <th>Id turno</th>
                        <th>Tienda</th>
                        <th>Usuario</th>
                        <th>Caja</th>
                        <th>Monto Ini.</th>
                        <th>Fecha Apertura</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>