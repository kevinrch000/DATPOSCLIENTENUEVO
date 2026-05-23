<?php
/**
 * DatPOS - Página Home / Dashboard
 * Reemplaza: Interfaces/Home.aspx + Home.aspx.vb
 */
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
requireAuth();
$objUsuario = getUsuarioSesion();
$pageTitle = 'Inicio | DATPOS';
$pageScript = 'Bashboard.js';
$showCrudButtons = false;
$showConsultButtons = false;
ob_start();
?>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-show-password/1.0.3/bootstrap-show-password.min.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/font-awesome-4.7.0/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons">
    <input id="operacion" type="hidden" />

    
    <link href="<?= basePath() ?>/assets/css/material-dashboard.css?v=2.1.2" rel="stylesheet">
    <!-- Libreria para General Exel -->
    <script src="<?= basePath() ?>/assets/Javascript/FileSaver.js" type="text/javascript"></script>

    <link href="<?= basePath() ?>/assets/Diagramas/DiagramaPastel.css" rel="stylesheet" type="text/css" />
    <script src="<?= basePath() ?>/assets/Diagramas/highcharts.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Diagramas/accessibility.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Diagramas/data.js" type="text/javascript"></script>
    <script src="<?= basePath() ?>/assets/Diagramas/exporting.js" type="text/javascript"></script>
    <label id="lblid_rol" style="display: none;"><?= htmlspecialchars($objUsuario->rolMaster ?? "") ?></label>


    <div class="c-content-center modern-page">
        <div class="modern-page-header">
            <div class="mph-icon"><i class="material-icons">dashboard</i></div>
            <div class="mph-text">
                <h1>Panel de Control</h1>
                <p>Resumen ejecutivo de ventas, kardex, productos y clientes.</p>
            </div>
            <div class="mph-spacer"></div>
            <span class="mph-chip"><i class="material-icons">storefront</i><?= htmlspecialchars($objUsuario->cdescripcion ?? '') ?></span>
        </div>
        <ul class="nav nav-tabs dp-tabs" id="ulOpciones">
            <li onclick="tab_Reporte();" class="active">
                <a data-toggle="tab" class="tabcito" href="#Reporte">Reporte</a></li>
            <li onclick="tab_DelDia();">
                <a data-toggle="tab" href="#DelDia" class="tabcito">Del Día</a></li>
            <li onclick="tab_PorProducto();">
                <a data-toggle="tab" href="#PorProducto" class="tabcito">Ventas por Artículo</a></li>
            <li onclick="tab_kardex();">
                <a data-toggle="tab" href="#Kardex" class="tabcito">Kardex</a></li>
            <li onclick="tab_Clientes();">
                <a data-toggle="tab" href="#Clientes" class="tabcito">Clientes</a></li>
        </ul>
        <div class="tab-content" id="divPestanas">
            <!-- Reporte -->
            <div id="Reporte" class="tab-pane in active" style="padding: 16px 4px;">
                <!-- Toolbar de filtros (Tienda + Fecha Desde + Fecha Hasta + acciones) -->
                <div class="dp-filters" id="DivFiltrosReporte">
                    <div class="dp-filter">
                        <label for="txtTiendaReporte">Tienda</label>
                        <select class="disabled limpiar form-control moderno_tb" id="txtTiendaReporte"></select>
                    </div>
                    <div class="dp-filter">
                        <label for="txtfchDesdeReporte">Fecha Desde</label>
                        <input id="txtfchDesdeReporte" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                            autocomplete="off" placeholder="dd/mm/aaaa" />
                    </div>
                    <div class="dp-filter">
                        <label for="txtfchHastaReporte">Fecha Hasta</label>
                        <input id="txtfchHastaReporte" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                            autocomplete="off" placeholder="dd/mm/aaaa" />
                    </div>
                    <div class="dp-filter-actions">
                        <a href="#" class="dp-btn-icon is-warning" title="Aplicar / Ejecutar" onclick="EjecutarReporte(); return false;">
                            <i class="material-icons">play_arrow</i>
                        </a>
                        <a href="#" class="dp-btn-icon is-accent" title="Limpiar" onclick="LimpiarReporte(); return false;">
                            <i class="material-icons">refresh</i>
                        </a>
                    </div>
                </div>

                <!-- KPIs principales -->
                <div id="DivOpcionAdmin" class="dp-kpi-grid">
                    <div class="dp-kpi-card" data-color="blue">
                        <div class="dp-kpi-icon"><i class="material-icons">groups</i></div>
                        <div class="dp-kpi-body">
                            <p class="dp-kpi-label">Clientes Guardados</p>
                            <p id="txtCantUsuario" class="dp-kpi-value">0</p>
                        </div>
                    </div>
                    <div class="dp-kpi-card" data-color="teal">
                        <div class="dp-kpi-icon"><i class="material-icons">payments</i></div>
                        <div class="dp-kpi-body">
                            <p class="dp-kpi-label">Importe de Ventas</p>
                            <p id="txtVentaDelDia" class="dp-kpi-value">0</p>
                        </div>
                    </div>
                    <div class="dp-kpi-card" data-color="amber">
                        <div class="dp-kpi-icon"><i class="material-icons">local_offer</i></div>
                        <div class="dp-kpi-body">
                            <p class="dp-kpi-label">Importe de Descuento</p>
                            <p id="txtCantUsuarios" class="dp-kpi-value">0</p>
                        </div>
                    </div>
                    <div class="dp-kpi-card" data-color="green">
                        <div class="dp-kpi-icon"><i class="material-icons">description</i></div>
                        <div class="dp-kpi-body">
                            <p class="dp-kpi-label">Documentos Generados</p>
                            <p id="txtUsuariosRegistrados" class="dp-kpi-value">0</p>
                        </div>
                    </div>
                </div>

                <!-- Gráficos comparativos como barras horizontales -->
                <div id="DivDashboardAdmin" class="dp-dashboard-split">
                    <div class="dp-section" id="sectionVentasCaja">
                        <div class="dp-section-head">
                            <h3 class="dp-section-title">Ventas por Caja</h3>
                            <a href="#" class="dp-section-link" onclick="EjecutarReporte(); return false;">Ver más</a>
                        </div>
                        <div id="container" class="dp-bars-container">
                            <div class="dp-bars-empty">Aplica un filtro para ver datos.</div>
                        </div>
                    </div>
                    <div class="dp-section" id="sectionVentasUsuario">
                        <div class="dp-section-head">
                            <h3 class="dp-section-title">Ventas por Usuario</h3>
                            <a href="#" class="dp-section-link" onclick="EjecutarReporte(); return false;">Ver más</a>
                        </div>
                        <div id="containerUsuario" class="dp-bars-container">
                            <div class="dp-bars-empty">Aplica un filtro para ver datos.</div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- DelDia -->
            <div id="DelDia" class="tab-pane" style="padding: 13px;">
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">

                        <h4 style="font-size: 16PX; font-weight: bold; margin-bottom: 30px; margin-top: 30px; text-align: center;">Productos más vendidos </h4>
                        <table id="tbProductosDelDia" class="display" style="width: 100%;">
                            <colgroup>
                                <col style="width: 10%"></col>
                                <col style="width: 5%"></col>
                            </colgroup>
                            <thead id="thProductosDelDia">
                                <tr>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Producto</th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <h4 style="font-size: 16PX; font-weight: bold; margin-bottom: 30px; margin-top: 30px; text-align: center;">Ranking de Vendedores</h4>
                        <table id="tbVendedoresDelDia">
                            <colgroup>
                                <col style="width: 10%"></col>
                                <col style="width: 5%"></col>
                            </colgroup>
                            <thead id="thVendedoresDelDia">
                                <tr>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Vendedor</th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>

                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                    </div>
                </div>

            </div>

            <!-- Tabla para Exportar -->
            <div id="DivtableKardexExel" style="display: none;">
                <table id="tableKardexExel" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Ingreso</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Salida</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Saldo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                        </tr>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Referencia</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nombre de Artículo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
        </table>
                </table>
            </div>

            <div id="DivtableClienteExel" style="display: none;">
                <table id="tableClienteExel" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <thead>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Usuario</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Ref</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Forma de Pago</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                        <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Monto</th>
                    </thead>
                    <tbody>
                    </tbody>
        </table>
                </table>
            </div>

            <div id="Divtable_visibleDocPorProductoExel" style="display: none;">
                <table id="table_visibleDocPorProductoExel" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Ref.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Artículo
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cant.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Precio Uni.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">IGV
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">ISC
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Desc.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe Tot.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.
                            </th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Variante
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
        </table>
                </table>
            </div>

            <div id="DivtbProductosDelDiaExel" style="display: none;">
                <table id="tbProductosDelDiaExel" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th>Producto
                            </th>
                            <th>Importe total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
        </table>
                </table>
            </div>
            <div id="DivtbVendedoresDelDiaExel" style="display: none;">
                <table id="tbVendedoresDelDiaExel" class="table table-bordered TablaIndex table-striped dataTable no-footer"
                    border="2px" cellspacing="0" width="2000">
                    <thead>
                        <tr>
                            <th>Vendedor
                            </th>
                            <th>Importe total
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <!-- PorProducto -->
            <div id="PorProducto" class="tab-pane" style="padding: 16px;">
                <div class="row">
                    <div class="col-sm-12 modern-toolbar">
                        <a href="#" class="action-pill ap-warning" title="Ejecutar" onclick="EjecutarPorProducto();">
                            <i class="material-icons">play_arrow</i>
                        </a>
                        <a href="#" class="action-pill ap-accent" title="Limpiar" onclick="LimpiarPorProducto();">
                            <i class="material-icons">refresh</i>
                        </a>
                        <a href="#" class="action-pill ap-success" title="Exportar Excel" onclick="ExelPorProducto();">
                            <i class="material-icons">file_download</i>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Tienda</label>
                        <div class="col-sm-9">
                            <select class="disabled limpiar form-control moderno_tb" id="txtTiendaPorProducto">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Desde</label>
                        <div class="col-sm-9">
                            <input id="txtfchDesdePorProducto" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Hasta</label>
                        <div class="col-sm-9">
                            <input id="txtfchHastaPorProducto" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Cliente</label>
                        <div class="col-sm-7">
                            <input id="txtClientePorProducto" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                        <a class="disabled input-group-addon" onclick="ModalConsultarClientes();"
                            style="background-color: #ffffff; border: 0px"><i class="fa fa-search color-buscadores"
                                aria-hidden="true"></i></a>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Artículo</label>
                        <div class="col-sm-7">
                            <input id="txtArticuloPorProducto" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                        <a class="disabled input-group-addon" onclick="ModalConsultarArticulos();"
                            style="background-color: #ffffff; border: 0px"><i class="fa fa-search color-buscadores"
                                aria-hidden="true"></i></a>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Detalle de Variante</label>
                        <div class="col-sm-7">
                            <input id="txtVariantePorProducto" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                    </div>
                </div>

                <ul class="nav nav-tabs" class="active">
                    <li onclick="">
                        <a data-toggle="tab" href="#Lista" class="tabcito">Lista</a></li>
                    <li onclick="">
                        <a data-toggle="tab" href="#Estadisticas" class="tabcito">Datos Adicionales</a></li>
                </ul>
                <div class="tab-content" style="padding-bottom: 30px;">
                    <!-- LISTADO -->
                    <div id="Lista" class="tab-pane in active " style="padding: 13px;">

                        <!-- Tabla para Visible -->
                        <table id="table_visibleDocPorProducto" class="display" style="width: 100%">
                            <colgroup>
                                <col style="width: 8%"></col>
                                <col style="width: 10%"></col>
                                <col style="width: 10%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 5%"></col>
                                <col style="width: 10%"></col>
                                <col style="width: 10%"></col>
                            </colgroup>
                            <thead id="thtable_visibleDocPorProducto">
                                <tr>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Ref.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Artículo
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cant.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Precio Uni.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">IGV
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">ISC
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Desc.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Importe Tot.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Variante
                                    </th>

                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
        </table>
                        </table>
                    </div>

                    <!-- Estadisticos -->
                    <div id="Estadisticas" class="tab-pane tabcito" style="padding: 13px;">
                        <div class="row">
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    Cantidad total</label>
                                <div class="col-sm-7">
                                    <input id="txtCantTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    Importe Bruto total</label>
                                <div class="col-sm-7">
                                    <input id="txtImpBrutoTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    IGV total</label>
                                <div class="col-sm-7">
                                    <input id="txtIgvTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>

                        </div>
                        <div class="row">
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    ISC total</label>
                                <div class="col-sm-7">
                                    <input id="txtIscTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    Descuento total</label>
                                <div class="col-sm-7">
                                    <input id="txtDescTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>
                            <div class="col-sm-4" style="padding-top: 10px;">
                                <label class="col-sm-3 moderno_lb">
                                    Importe Neto total</label>
                                <div class="col-sm-7">
                                    <input id="txtImpNetoTotPorProducto" disabled maxlength="25" type="text" class="form-control moderno_tb" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Kardex -->
            <div id="Kardex" class="tab-pane" style="padding: 16px;">
                <div class="row">
                    <div class="col-sm-12 modern-toolbar">
                        <a href="#" class="action-pill ap-warning" title="Ejecutar" onclick="EjecutarKardex();">
                            <i class="material-icons">play_arrow</i>
                        </a>
                        <a href="#" class="action-pill ap-accent" title="Limpiar" onclick="LimpiarKardex();">
                            <i class="material-icons">refresh</i>
                        </a>
                        <a href="#" class="action-pill ap-success" title="Exportar Excel" onclick="ExelKardex();">
                            <i class="material-icons">file_download</i>
                        </a>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Almacén*</label>
                        <div class="col-sm-9">
                            <select class="disabled limpiar form-control moderno_tb" id="txtAlmacenKardex">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Desde*</label>
                        <div class="col-sm-9">
                            <input id="txtfchDesdeKardex" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Hasta*</label>
                        <div class="col-sm-9">
                            <input id="txtfchHastaKardex" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">

                        <label class="col-sm-3 moderno_lb">
                            Artículo</label>
                        <div class="col-sm-7">
                            <input id="txtCodArticuloKardex" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                        <a class="disabled input-group-addon" onclick="ModalConsultarArticulos();"
                            style="background-color: #ffffff; border: 0px"><i class="fa fa-search color-buscadores"
                                aria-hidden="true"></i></a>
                    </div>
                </div>

                <table id="tableKardex" class="display" style="width: 100%">
                    <colgroup>
                        <col style="width: 10%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 15%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                    </colgroup>
                    <thead id="thtableKardex">
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Ingreso</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Salida</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Saldo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;"></th>
                        </tr>
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Referencia</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Nombre de Artículo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cantidad</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Costo</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Total</th>
                        </tr>
                    </thead>

                    <tbody ondblclick="table_two_click(this);">
                    </tbody>
                </table>
            </div>
            <!-- Clientes -->
            <div id="Clientes" class="tab-pane" style="padding: 16px;">
                <div class="row">
                    <div class="col-sm-12 modern-toolbar">
                        <a href="#" class="action-pill ap-warning" title="Ejecutar" onclick="EjecutarClientes();">
                            <i class="material-icons">play_arrow</i>
                        </a>
                        <a href="#" class="action-pill ap-accent" title="Limpiar" onclick="LimpiarClientes();">
                            <i class="material-icons">refresh</i>
                        </a>
                        <a href="#" class="action-pill ap-success" title="Exportar Excel" onclick="ExelClientes();">
                            <i class="material-icons">file_download</i>
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Tienda</label>
                        <div class="col-sm-9">
                            <select class="disabled limpiar form-control moderno_tb" id="txtTiendaClientes">
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Desde</label>
                        <div class="col-sm-9">
                            <input id="txtfchDesdeClientes" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Fecha Hasta</label>
                        <div class="col-sm-9">
                            <input id="txtfchHastaClientes" maxlength="10" type="text" class="limpiar form-control moderno_tb"
                                autocomplete="off" placeholder=" " />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Cliente</label>
                        <div class="col-sm-7">
                            <input id="txtClienteClientes" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                        <a class="disabled input-group-addon" onclick="ModalConsultarClientes();"
                            style="background-color: #ffffff; border: 0px"><i class="fa fa-search color-buscadores"
                                aria-hidden="true"></i></a>
                    </div>
                    <div class="col-sm-4" style="padding-top: 10px;">
                        <label class="col-sm-3 moderno_lb">
                            Usuario</label>
                        <div class="col-sm-7">
                            <input id="txtUsuarioClientes" maxlength="25" type="text" class="form-control moderno_tb" />
                        </div>
                        <a class="disabled input-group-addon" onclick="ModalConsultarUsuarios();"
                            style="background-color: #ffffff; border: 0px"><i class="fa fa-search color-buscadores"
                                aria-hidden="true"></i></a>
                    </div>
                </div>

                <table id="tableCliente" class="display" style="width: 100%">
                    <colgroup>
                        <col style="width: 17%"></col>
                        <col style="width: 17%"></col>
                        <col style="width: 10%"></col>
                        <col style="width: 6%"></col>
                        <col style="width: 5%"></col>
                        <col style="width: 5%"></col>
                    </colgroup>
                    <thead id="">
                        <tr>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Cliente</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Usuario</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Doc. Ref</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Forma de Pago</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Fecha Doc.</th>
                            <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: #999;">Monto</th>
                        </tr>
                    </thead>

                    <tbody>
                    </tbody>
        </table>
                </table>
            </div>
        </div>




        <div class="modal fade" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="testModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background-color: #d4e1e4;">
                    <div id="modalConsultarClientes2" class="modal-header">
                        <div class="col-sm-6">
                            <h5 class="modal-title">Seleccione Cliente</h5>
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
                                    <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;"></th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Cliente
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Nombre del Cliente
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
        </table>
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
        <!-- DivOpcionCliente y DivDashboardCliente removidos: unificados en DivOpcionAdmin/DivDashboardAdmin -->
        <div class="modal" id="modalConsultarArticulos" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content" style="background-color: #d4e1e4;">
                    <div class="modal-header">
                        <div class="col-sm-6">
                            <h5 class="modal-title">Seleccione Artículo</h5>
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
                                    <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;"></th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Artículo
                                    </th>
                                    <th style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4; background-color: rgb(33, 182, 215); color: White;">Nombre de Artículo
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
        </table>
                        </table>

                    </div>
                    <div class="modal-footer" style="margin: 10px;">
                        <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="PasaDatosCodArticulo();">Seleccionar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <table id="datatable" style="display:none;">
        <thead>
            <tr>
                <th></th>
                <th>Efectivo</th>
                <th>Tarjeta</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
        </table>
    </div>

<style>
    /* Dashboard: ajustes específicos (look base proviene de Modern-UI.css) */
    #ulOpciones { margin-top: 8px; }
    .card-stats { margin-top: 20px; }
    #container, #containerUsuario { min-height: 320px; padding-top: 20px; }
    /* Reset del fondo gris de las th heredadas para integrarlas al look moderno */
    #DivtableKardexExel th,
    #DivtableClienteExel th,
    #Divtable_visibleDocPorProductoExel th,
    #DivtbProductosDelDiaExel th,
    #DivtbVendedoresDelDiaExel th,
    #thProductosDelDia th,
    #thVendedoresDelDia th,
    #thtable_visibleDocPorProducto th,
    #thtableKardex th,
    .modern-page table.dataTable thead th[style*="#999"],
    .modern-page table.dataTable thead th[style*="#999"] { background-color: transparent !important; }
</style>

<script>
// Dashboard: forzar vista completa (5 tabs + KPIs + diagramas) para cualquier rol.
// Bashboard.js solo carga datos si rolMaster == 2; aquí forzamos la carga para todos.
$(document).ready(function () {
    setTimeout(function () {
        $('#ulOpciones').show();
        $('#divPestanas').show();
        $('#DivOpcionAdmin').show();
        $('#DivDashboardAdmin').show();
        // Asegurar que solo Reporte arranca activa
        $('#divPestanas > .tab-pane').removeClass('in active');
        $('#Reporte').addClass('in active');
        $('#ulOpciones li').removeClass('active').first().addClass('active');

        // Cargar datos para cualquier rol
        try { if (typeof CargarTienda === 'function') CargarTienda(); } catch(e) {}
        try { if (typeof CargarAlamcen === 'function') CargarAlamcen(); } catch(e) {}
        try { if (typeof CargarMesActual === 'function') CargarMesActual(); } catch(e) {}
        try { if (typeof EjecutarReporte === 'function') EjecutarReporte(); } catch(e) {}
    }, 250);
});
</script>

<?php
$pageContent = ob_get_clean();
require_once __DIR__ . '/../../includes/layout_master.php';
?>
