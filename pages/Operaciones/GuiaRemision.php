<?php
/**
 * DatPOS - Guía de Remisión
 * Reemplaza: Operaciones/GuiaRemision.aspx + GuiaRemision.aspx.vb
 *
 * NOTA: Versión funcional MVP. La integración SUNAT (envío vía ITC) se hará en Sprint D.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = $_SESSION['objBEUsuario'];
$pageTitle = 'Guía de Remisión | DATPOS';
$pageScript = 'GuiaRemision.js';
$pageScriptPatch = 'operaciones_patch.js';
$showCrudButtons = true;

// Pre-cargar combos server-side (paridad con VB Page_Load)
$almacenes = array();
try {
    $rows = Database::selectStoredTenant('sp_consultaalmacenesactivos', array(
        '@ccod_cia' => $o->ccod_empresa
    ), $o);
    foreach ($rows as $f) {
        $almacenes[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
    }
} catch (Exception $e) { error_log('[GuiaRemision] CargarAlmacenes: '.$e->getMessage()); }

$tiposIngreso = array();
$tiposSalida  = array();
try {
    $conn = Database::getTenantConnection($o);
    if ($conn) {
        $sql = "SELECT ccod_tipoper, cdsc_tipoper, ISNULL(ctipo_flag,'')
                FROM TipoOperacion
                WHERE ccod_cia=? AND cstatus='A'
                ORDER BY ccod_tipoper";
        $stmt = sqlsrv_query($conn, $sql, array(strval($o->ccod_empresa)));
        if ($stmt) {
            while ($f = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $row = array(
                    'ccod_toper' => strval($f[0] ?? ''),
                    'cdsc_toper' => strval($f[1] ?? ''),
                    'flag'       => strval($f[2] ?? ''),
                );
                if ($row['flag'] === 'I') $tiposIngreso[] = $row;
                elseif ($row['flag'] === 'S') $tiposSalida[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        }
        sqlsrv_close($conn);
    }
} catch (Exception $e) { error_log('[GuiaRemision] TiposOperacion: '.$e->getMessage()); }

$numeradoresGuia = array();
try {
    // webDatpos_ObtenerNumerador devuelve: id_ctalmac[0], cserie[1], nnumero[2], ctip_doc[3]
    $rows = Database::selectStoredTenant('webDatpos_ObtenerNumerador', array(
        '@ccod_cia' => $o->ccod_empresa,
        '@tipo'     => 'RT'
    ), $o);
    foreach ($rows as $f) {
        $tipo = strval($f[3] ?? '');
        if ($tipo === '') continue;
        $numeradoresGuia[] = array('cdoc_tipo' => $tipo, 'cdoc_serie' => strval($f[1] ?? ''));
    }
} catch (Exception $e) { /* el JS sigue intentando con su propio SP, no es bloqueante */ }

ob_start();
?>
<link href="/assets/Styles/Moderno.css" rel="stylesheet" type="text/css" />
<link href="/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />

<input id="hdd_rv" type="hidden"/>
<input id="operacion" type="hidden"/>
<input id="hdd_id_cbinve" value="0" type="hidden"/>
<input id="hdd_ultimafila" type="hidden"/>
<input id="hdd_fila" type="hidden" value="0"/>
<input id="hdd_numeromenus" type="hidden" value="2"/>
<input id="hdd_numerofilas" type="hidden"/>

<!-- Datos de sesión que el JS espera -->
<span id="txtccod_empresa" style="display:none;"><?= e($o->ccod_empresa ?? '') ?></span>
<span id="txtcusu_crea" style="display:none;"><?= e($o->ccod_usuario ?? '') ?></span>
<span id="ccod_cliente_emis" style="display:none;"><?= e($o->ccod_cliente_emis ?? '') ?></span>
<span id="ctoken" style="display:none;"><?= e($o->ctoken ?? '') ?></span>
<span id="IdRemitente" style="display:none;"><?= e($o->cnum_ruc ?? '') ?></span>
<span id="txtnom_rzn_soc_rem" style="display:none;"><?= e($o->cnom_rzn_soc ?? '') ?></span>

<script>window.OPERACION_ASPX = 'GuiaRemision.aspx';</script>
<script src="/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<div class="c-content-center modern-page">
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a></li>
        <li onclick="tab_listaclick();"><a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a></li>
    </ul>

    <div class="tab-content">
        <div id="Datos" class="tab-pane in active">

            <h4 style="border-bottom:groove;margin:30px 0;">Tipo de operación</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Modo*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="operacion_select" onchange="SelecModo()" disabled>
                            <option value="04">Translado entre almacenes (04)</option>
                            <option value="01">Venta — Salida (01)</option>
                            <option value="14">Venta — Salida exportación (14)</option>
                            <option value="02">Compra — Ingreso (02)</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Fecha emisión</label>
                    <div class="col-sm-9"><input id="txtdfecha" type="date" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Origen / Destino</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Almacén Origen*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_almacenOrig" onchange="SelecAlmacenOrigen()" disabled>
                            <option value=""></option>
                            <?php foreach ($almacenes as $a): ?>
                                <option value="<?= e($a['ccod_alm']) ?>"><?= e($a['cdsc_alm']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Tipo Op. Salida*</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_tipOperSalida" disabled>
                            <option value=""></option>
                            <?php foreach ($tiposSalida as $t): ?>
                                <option value="<?= e($t['ccod_toper']) ?>">(<?= e($t['ccod_toper']) ?>) <?= e($t['cdsc_toper']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Serie Origen</label>
                    <div class="col-sm-9"><input id="txtSerieOrig" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Almacén Destino</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_almacenDest" onchange="SelecAlmacenDestino()" disabled>
                            <option value=""></option>
                            <?php foreach ($almacenes as $a): ?>
                                <option value="<?= e($a['ccod_alm']) ?>"><?= e($a['cdsc_alm']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Tipo Op. Ingreso</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="ddl_tipOperIngreso" disabled>
                            <option value=""></option>
                            <?php foreach ($tiposIngreso as $t): ?>
                                <option value="<?= e($t['ccod_toper']) ?>">(<?= e($t['ccod_toper']) ?>) <?= e($t['cdsc_toper']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Serie Destino</label>
                    <div class="col-sm-9"><input id="txtSerieDest" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Datos del documento</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Tipo CPE</label>
                    <div class="col-sm-9">
                        <select class="disabled limpiar form-control moderno_tb" id="txtCodDocumento" disabled>
                            <option value="09">Guía de Remisión Remitente</option>
                            <option value="31">Guía de Remisión Transportista</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">N° Guía</label>
                    <div class="col-sm-9">
                        <select id="txtccod_guia" class="disabled limpiar form-control moderno_tb" onchange="SelecNumerador()" disabled onclick="ObtenerNombreColumna(this)">
                            <option value=""></option>
                            <?php foreach ($numeradoresGuia as $n): ?>
                                <option value="<?= e($n['cdoc_tipo']) ?>"><?= e($n['cdoc_tipo']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Serie Guía</label>
                    <div class="col-sm-9"><input id="txtcserie_guia" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Doc. Referencia</label>
                    <div class="col-sm-9"><input id="txtcdoc_ref" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Destinatario</h4>
            <p class="text-muted" style="margin:-15px 0 15px 0;font-size:13px;">
                <i class="fa fa-info-circle"></i>
                Si los productos salen del almacén con una <b>Factura</b>, no es necesario emitir Guía de Remisión.
                Use esta página sólo cuando el traslado <b>no</b> está respaldado por una factura.
            </p>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">RUC/DNI</label>
                    <div class="col-sm-9"><input id="IdDestino" class="disabled limpiar form-control moderno_tb" disabled onchange="BuscarDatosRucDestino()"/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Razón Social</label>
                    <div class="col-sm-9"><input id="txtnom_rzn_soc_dest" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">RUC Tercero</label>
                    <div class="col-sm-9">
                        <div class="input-group">
                            <input id="IdProveedor" class="disabled limpiar form-control moderno_tb" disabled onchange="BuscarDatosRucTercero()"/>
                            <a class="disabled input-group-addon" onclick="ConsultarCodigoAuxiliar()" style="background-color:#fff;border:0"><i class="fa fa-search color-buscadores"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Razón Social Tercero</label>
                    <div class="col-sm-9"><input id="txtnom_rzn_soc_prov" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>
            <input id="ccod_coa" type="hidden"/>

            <h4 style="border-bottom:groove;margin:30px 0;">Direcciones</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Dir. Partida*</label>
                    <div class="col-sm-9"><input id="txtDircOrig" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Ubigeo Partida</label>
                    <div class="col-sm-9"><input id="txtUbigeoOrig" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Dir. Llegada*</label>
                    <div class="col-sm-9"><input id="txtDircDest" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Ubigeo Llegada</label>
                    <div class="col-sm-9"><input id="txtUbigeoDest" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Transportista</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">Razón Social</label>
                    <div class="col-sm-9"><input id="txtnom_rzn_trans" class="disabled limpiar form-control moderno_tb" disabled/></div>
                </div>
                <div class="col-sm-6">
                    <label class="col-sm-3 moderno_lb">RUC</label>
                    <div class="col-sm-9"><input id="IdTransportista" class="disabled limpiar form-control moderno_tb" disabled onchange="BuscarDatosRucTransportista()"/></div>
                </div>
            </div>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-3">
                    <label class="moderno_lb">Placa</label>
                    <input id="txtplaca" class="disabled limpiar form-control moderno_tb" disabled/>
                </div>
                <div class="col-sm-3">
                    <label class="moderno_lb">Licencia</label>
                    <input id="txtLicencia" class="disabled limpiar form-control moderno_tb" disabled/>
                </div>
                <div class="col-sm-3">
                    <label class="moderno_lb">Peso (kg)</label>
                    <input id="txtmnt_tot_peso_bruto" type="number" min="0" step="0.01" class="disabled limpiar form-control moderno_tb" disabled/>
                </div>
                <div class="col-sm-3">
                    <label class="moderno_lb">Unid. Peso</label>
                    <input id="txtcod_unid_peso_bruto" class="disabled limpiar form-control moderno_tb" value="KGM" disabled/>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Motivo / Observaciones</h4>
            <div class="row" style="margin-top:20px;">
                <div class="col-sm-12">
                    <label class="moderno_lb">Motivo del traslado</label>
                    <textarea id="txtdesc_motiv_tras" class="disabled limpiar form-control moderno_tb" rows="2" disabled></textarea>
                </div>
                <div class="col-sm-12">
                    <label class="moderno_lb">Observaciones</label>
                    <textarea id="txtnobs" class="disabled limpiar form-control moderno_tb" rows="2" disabled></textarea>
                </div>
                <input id="txtdfec_fin" type="hidden"/>
            </div>

            <h4 style="border-bottom:groove;margin:30px 0;">Detalle de Artículos</h4>
            <table id="tabla" class="table table-bordered table-striped">
                <thead><tr><th>Código</th><th>Descripción</th><th>Cantidad</th><th>Costo</th><th></th><th></th></tr></thead>
                <tbody></tbody>
            </table>
            <div class="col-sm-12">
                <input type="button" value="Agregar artículo" class="btn btn-primary fa_disabled" data-toggle="modal" data-target="#modalnuevo"/>
            </div>

            <!-- Modal Nuevo Detalle -->
            <div class="modal fade" id="modalnuevo" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Agregar artículo</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <div class="form-group"><label>Código</label>
                                <div class="input-group">
                                    <input id="tb_cod" type="text" class="form-control"/>
                                    <a class="input-group-addon" data-toggle="modal" data-target="#modalArticulos" onclick="ModalArticulos();" style="background-color:#fff;border:0"><i class="fa fa-search"></i></a>
                                </div>
                            </div>
                            <div class="form-group"><label>SUNAT</label><input type="text" class="form-control" id="tb_codSunat" readonly/></div>
                            <div class="form-group"><label>Artículo</label><input type="text" class="form-control" id="tb_articulo" readonly/></div>
                            <div class="form-group"><label>Cant. disponible</label><input type="text" class="form-control" id="tb_cantActual" readonly/></div>
                            <div class="form-group"><label>Costo</label><input type="text" class="form-control" id="tb_costo" readonly/></div>
                            <div class="form-group"><label>Cantidad</label><input type="number" min="0" class="form-control" id="tb_cantidad"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="InsertarFila()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Artículos -->
            <div class="modal fade" id="modalArticulos" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Artículos</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <table id="table_Articulos" class="display" style="width:100%;">
                                <thead><tr><th></th><th>Código</th><th>SUNAT</th><th>Nombre</th><th>Cantidad</th><th>Costo</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasarArticulo();">Seleccionar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Editar -->
            <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Editar</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <input id="tb_cod_editar" type="hidden"/>
                            <input id="tb_codSunat_editar" type="hidden"/>
                            <div class="form-group"><label>Artículo</label><input type="text" class="form-control" id="tb_articulo_editar" readonly/></div>
                            <div class="form-group"><label>Costo</label><input type="text" class="form-control" id="tb_costo_editar" readonly/></div>
                            <div class="form-group"><label>Cantidad</label><input type="number" min="0" class="form-control" id="tb_cantidad_editar"/></div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" onclick="EditarFila()">Editar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal validación stock -->
            <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header"><h5>Artículos sin stock</h5><button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
                        <div class="modal-body">
                            <table id="tbLisArticuloError" class="display" style="width:100%;">
                                <thead><tr><th>Artículo</th><th>Solicitado</th><th>Actual</th><th>Faltante</th></tr></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LISTA -->
        <div id="Lista" class="tab-pane tabcito">
            <nav class="navbar navbar-default" style="margin-bottom:0;">
                <div class="container-fluid">
                    <div class="navbar-header">
                        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
                    </div>
                    <div id="navbar" class="navbar-collapse collapse navbar-right" style="margin-right:4.5%;">
                        <ul class="nav navbar-nav">
                            <li class="dropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button"><img src="/assets/Styles/img/filtro.png" style="width:14px;margin-right:5px;"/>FILTROS <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="#" onclick="FilterTipo('');">Mostrar Todos</a></li>
                                    <li><a href="#" onclick="FilterTipo('04');">Translado</a></li>
                                    <li><a href="#" onclick="FilterTipo('01');">Venta - Salida</a></li>
                                    <li><a href="#" onclick="FilterTipo('02');">Compra - Ingreso</a></li>
                                </ul>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
            <table id="table_id" class="display" style="width:100%;">
                <thead id="thTablaGuia">
                    <tr>
                        <th></th>
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Tipo CPE</th>
                        <th>Alm. Origen</th>
                        <th>Domicilio Partida</th>
                        <th>Alm. Destino</th>
                        <th>Domicilio Llegada</th>
                        <th>Fecha</th>
                        <th>Doc. Ref.</th>
                        <th>Guía</th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Los combos (almacenes, tipos op, numeradores) se cargan server-side desde PHP.
// El JS GuiaRemision.js todavia intenta llenarlos via AJAX y agrega un atributo `disabled`
// a cada <option>, lo que hace que dejen de ser seleccionables. Limpiamos eso en
// document.ready y cada vez que el usuario interactua con los combos.
function _datpos_limpiarOpciones() {
    var sels = ['#ddl_almacenOrig','#ddl_almacenDest','#ddl_tipOperSalida','#ddl_tipOperIngreso','#txtccod_guia','#txtCodDocumento'];
    sels.forEach(function (sel) {
        $(sel + ' option').removeAttr('disabled').removeClass('disabled');
    });
}
$(document).ready(function () {
    _datpos_limpiarOpciones();
    setTimeout(_datpos_limpiarOpciones, 800);
    setTimeout(_datpos_limpiarOpciones, 2000);
    $(document).on('click focus', '#btn_p_nuevo, .nav-tabs a, select.form-control', function () {
        setTimeout(_datpos_limpiarOpciones, 50);
    });
});
</script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
