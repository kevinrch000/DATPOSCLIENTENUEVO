<?php
/**
 * DatPOS - Administración de Tiendas
 * Reemplaza: DatPOS1Web/Administracion/Tiendas.aspx
 * Estructura completa con 4 tabs (Datos / Almacenes / Cajas / Lista) que Tienda.js requiere.
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();
$pageTitle = 'Tiendas | DATPOS';
$pageScript = 'Tienda.js';
$showCrudButtons = true;
$showConsultButtons = false;

// Pre-cargar listas de precio activas (equivale a Tiendas.aspx.vb -> ConsultarListaPreciosActivos)
$_objU = $_SESSION['objBEUsuario'];
$listaPrecios = array();
try {
    $rowsLP = Database::selectStoredTenant(
        'sp_consultarlistaspreciosactivos',
        array('@ccod_cia' => $_objU->ccod_empresa),
        $_objU
    );
    // El SP devuelve: [0]ccod_cblistpre, [1]cdsc_cblistpre
    // Pero CbListaPrecio tiene id_cblistpre (INT) que es lo que necesita el JS.
    // Hacemos un fetch directo que incluya el id numerico.
    $conn = Database::getTenantConnection($_objU);
    if ($conn) {
        $stmt = sqlsrv_query($conn, "SELECT id_cblistpre, cdsc_cblistpre FROM CbListaPrecio WHERE ccod_cia=? AND cstatus='A' ORDER BY id_cblistpre", array($_objU->ccod_empresa));
        if ($stmt) {
            while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $listaPrecios[] = array('id' => strval($r[0] ?? ''), 'name' => strval($r[1] ?? ''));
            }
            sqlsrv_free_stmt($stmt);
        }
        sqlsrv_close($conn);
    }
} catch (Exception $e) { /* dropdowns quedaran vacios */ }

ob_start();
?>
<script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

<input id="operacion" type="hidden" />
<input id="hdd_ultimafila" type="hidden" />
<input id="hdd_fila" type="hidden" value="0" />
<input id="hdd_numeromenus" type="hidden" value="3" />
<input id="hdd_numerofilas" type="hidden" />
<input id="NombreColumna" type="hidden" />

<div class="c-content-center modern-page">
    <ul class="nav nav-tabs">
        <li onclick="tab_datosclick();" class="active">
            <a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a>
        </li>
        <li onclick="tab_datosclick();">
            <a data-toggle="tab" href="#Almacenes" class="tabcito" style="color:#228ac9;font-size:17px;">Almacenes</a>
        </li>
        <li onclick="tab_datosclick();">
            <a data-toggle="tab" href="#Cajas" class="tabcito" style="color:#228ac9;font-size:17px;">Cajas</a>
        </li>
        <li onclick="tab_listaclick();">
            <a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- ===================== TAB: DATOS ===================== -->
        <div id="Datos" class="tab-pane in active" style="padding:13px;">
            <h4 style="border-bottom:groove;margin-bottom:30px;margin-top:30px;width:60%;">Información General</h4>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Código*</label>
                    <div class="col-sm-10">
                        <input id="tb_codigo" class="readonl limpiar form-control moderno_tb" maxlength="10" readonly onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Nombre*</label>
                    <div class="col-sm-10">
                        <input id="tb_descripcion" class="disabled limpiar form-control moderno_tb" disabled maxlength="50" onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Teléfono*</label>
                    <div class="col-sm-10">
                        <input id="tb_telefono" class="disabled limpiar form-control moderno_tb" disabled maxlength="25" onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Mail</label>
                    <div class="col-sm-10">
                        <input id="tb_mail" class="disabled limpiar form-control moderno_tb" disabled maxlength="50" onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Contraseña Mail</label>
                    <div class="col-sm-10">
                        <input type="password" id="tb_clave" class="disabled limpiar form-control moderno_tb" maxlength="50" disabled onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Estado*</label>
                    <div class="col-sm-10">
                        <select id="ddl_estado" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)">
                            <option value=""></option>
                            <option value="A">Activo</option>
                            <option value="I">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Domicilio*</label>
                    <div class="col-sm-10">
                        <input id="tb_direccion" class="disabled limpiar form-control moderno_tb" disabled maxlength="100" onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Urbanización</label>
                    <div class="col-sm-10">
                        <input id="td_urbanizacion" class="disabled limpiar form-control moderno_tb" disabled maxlength="100" onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Departamento*</label>
                    <div class="col-sm-10">
                        <select id="txtDepartamento" class="disabled limpiar form-control moderno_tb" onchange="CargarProvincia();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Provincia*</label>
                    <div class="col-sm-10">
                        <select id="txtProvincia" class="disabled limpiar form-control moderno_tb" onchange="CargarDistrito();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Distrito*</label>
                    <div class="col-sm-10">
                        <select id="txtDistrito" class="disabled limpiar form-control moderno_tb" onchange="CargarUbigeo();" disabled onclick="ObtenerNombreColumna(this)"></select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Ubigeo*</label>
                    <div class="col-sm-10">
                        <input id="txtUbigeo" class="limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Código Tienda Sunat*</label>
                    <div class="col-sm-10">
                        <input id="txtCodLocEmi" maxlength="3" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)" />
                    </div>
                </div>
            </div>

            <h4 style="border-bottom:groove;margin-bottom:30px;margin-top:30px;width:60%;">Atributos</h4>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Lista de Precios Normal</label>
                    <div class="col-sm-10">
                        <select id="ddl_lpn" class="disabled form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)">
                            <option value=""></option>
                            <?php foreach ($listaPrecios as $lp): ?>
                                <option value="<?= htmlspecialchars($lp['id']) ?>"><?= htmlspecialchars($lp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Lista de Precios Preferencial</label>
                    <div class="col-sm-10">
                        <select id="ddl_lpp" class="disabled limpiar form-control moderno_tb" disabled onclick="ObtenerNombreColumna(this)">
                            <option value=""></option>
                            <?php foreach ($listaPrecios as $lp): ?>
                                <option value="<?= htmlspecialchars($lp['id']) ?>"><?= htmlspecialchars($lp['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row" style="margin-top:20px;">
                <div class="col-sm-6 col-xs-12">
                    <label class="col-sm-2 moderno_lb">Cliente Boleta</label>
                    <div class="col-sm-10">
                        <input id="txtCodCliBol" type="hidden" />
                        <input id="txtNomCliBol" class="disabled limpiar form-control moderno_tb" disabled />
                    </div>
                </div>
            </div>
        </div>

        <!-- ===================== TAB: ALMACENES ===================== -->
        <div id="Almacenes" class="tab-pane tabcito" style="padding:13px;">

            <!-- Modal Asignar Almacen -->
            <div class="modal fade" id="modalAlmacenNuevo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Asignar Almacén</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="tab-pane tabcito" style="padding:13px;">
                                <table id="tablaAsignarAlmacenes" class="display" style="width:100%;">
                                    <thead id="thtableExportAsignarAlmacenes">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Código</th>
                                            <th class="text-center">Nombre</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="InsertarFilaAlmacen()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <label id="lb_codigo_a"></label>

            <table id="TablaAlmacenes" class="table table-bordered table-striped" style="width:40%;">
                <thead id="thtableExportAlmacenes">
                    <tr>
                        <th class="text-center">Código</th>
                        <th class="text-center">Nombre</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <!-- Tablas para exportar a excel (ocultas) -->
            <div id="DivtableExportAsignarAlmacenes" style="display:none;">
                <table id="TablaExportAsignarAlmacenes" class="table table-bordered" border="2px" cellspacing="0">
                    <thead><tr><th>Código</th><th>Nombre</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="DivtableExportAlmacenes" style="display:none;">
                <table id="tableExportAlmacenes" class="table table-bordered" border="2px" cellspacing="0">
                    <thead><tr><th>Código</th><th>Nombre</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>

            <input type="button" class="btn btn-primary fa_disabled" value="Asignar Almacén" onclick="CargarAlmacenesDisponibles()" />
        </div>

        <!-- ===================== TAB: CAJAS ===================== -->
        <div id="Cajas" class="tab-pane tabcito" style="padding:13px;">

            <!-- Modal Asignar Caja -->
            <div class="modal fade" id="modalCajaNuevo" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Asignar Caja</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="tab-pane tabcito" style="padding:13px;">
                                <table id="tablaAsignarCaja" class="display" style="width:100%;">
                                    <thead id="thtableExportAsignarCaja">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">Código</th>
                                            <th class="text-center">Nombre</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="InsertarFilaCaja()">Agregar</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <label id="lb_codigo_c"></label>

            <table id="TablaCaja" class="table table-bordered table-striped" style="width:40%;">
                <thead id="thtableExportCaja">
                    <tr>
                        <th class="text-center">Código</th>
                        <th class="text-center">Nombre</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div id="DivtableExportAsignarCajas" style="display:none;">
                <table id="TablaExportAsignarCaja" class="table table-bordered" border="2px" cellspacing="0">
                    <thead><tr><th>Código</th><th>Nombre</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="DivtableExportCajas" style="display:none;">
                <table id="TablaExportCaja" class="table table-bordered" border="2px" cellspacing="0">
                    <thead><tr><th>Código</th><th>Nombre</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>

            <input type="button" class="btn btn-primary fa_disabled" value="Asignar Caja" onclick="CargarCajasDisponibles()" />
        </div>

        <!-- ===================== TAB: LISTA ===================== -->
        <div id="Lista" class="tab-pane tabcito" style="padding:13px;">
            <table id="table_id" class="display" style="width:-webkit-fill-available;">
                <colgroup>
                    <col style="width:1%"><col style="width:5%"><col style="width:10%">
                    <col style="width:10%"><col style="width:10%"><col style="width:10%"><col style="width:5%">
                </colgroup>
                <thead id="thtableTienda">
                    <tr>
                        <th></th>
                        <th>Código</th>
                        <th>Nombre de Tienda</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Mail</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody ondblclick="table_two_click(this);"></tbody>
            </table>
        </div>

    </div>

    <!-- Tabla oculta para export Excel -->
    <div id="tablePrincipalExportExel" style="display:none;">
        <table id="tableTienda" class="table table-bordered" border="2px" cellspacing="0">
            <thead>
                <tr>
                    <th>Código</th><th>Nombre de Tienda</th><th>Dirección</th>
                    <th>Teléfono</th><th>Mail</th><th>Estado</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
