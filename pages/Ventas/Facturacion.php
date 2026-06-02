<?php
require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
require_once __DIR__ . '/../../config/database.php';
$pageTitle = 'Facturación | DATPOS'; $pageScript = 'Facturacion6.js'; $pageScriptPatch = 'facturacion_features.js'; $showCrudButtons = false;
$objUsuario = getUsuarioSesion();

// === Page_Load logic from Facturacion.aspx.vb ===
$startupScript = '';

// 1. ValidarFacturacion - check if billing config is OK
$validParams = array(
    '@CodCia' => array('value' => $objUsuario->ccod_empresa),
    '@ccod_usuario' => array('value' => $objUsuario->ccod_usuario),
    '@resp' => array('value' => '', 'direction' => 'output')
);
// Try calling sp_validarfacturacion via output params
$validResult = Database::executeStoredTenantWithOutput('sp_validarfacturacion', $validParams, $objUsuario);
$resp = $validResult['@resp']['value'] ?? ($validResult['@resp'] ?? '');
if (strtoupper(trim($resp)) === 'OK') {
    $resp = '';
}

if ($resp == '') {
    // 2. ConsultarUsuarioTurno - get active shift
    $turnoRows = Database::selectStoredTenant('sp_consultarusuarioturno', array(
        '@ccod_cia' => $objUsuario->ccod_empresa,
        '@ccod_usuario' => $objUsuario->ccod_usuario
    ), $objUsuario);
    
    if (!empty($turnoRows)) {
        $_SESSION['id_turno'] = $turnoRows[0][0];
    } else {
        $startupScript = 'MensajeTurno();';
    }
} else {
    $startupScript = "MensajeValidacionFacturacion('" . addslashes($resp) . "');";
}

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

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js"></script>
    <link href="<?= basePath() ?>/assets/Styles/bootstrap-float-label.css" rel="stylesheet" type="text/css" />

    <link href="<?= basePath() ?>/assets/Styles/css/switcher.css" rel="stylesheet" type="text/css" />
        
    
    <link href="<?= basePath() ?>/assets/Styles/css/jquery.toggleinput.css" rel="stylesheet" type="text/css" />
    
        

<style>
/* ═══════════════════════════════════════════════════════════════════
   FACTURACIÓN — Rediseño visual completo
   ═══════════════════════════════════════════════════════════════════ */

/* ── Variables de color ─────────────────────────────────────────── */
:root {
    --fact-bg:        #f0f4f8;
    --fact-card:      #ffffff;
    --fact-border:    #dde4ed;
    --fact-accent:    #2563eb;
    --fact-accent2:   #1e40af;
    --fact-success:   #16a34a;
    --fact-danger:    #dc2626;
    --fact-text:      #1e293b;
    --fact-muted:     #64748b;
    --fact-cat-bg:    #1e3a5f;
    --fact-cat-hover: #2563eb;
    --fact-shadow:    0 1px 3px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.06);
}
/* Tarjetas aplanadas por _flattenArticulosGrid() — hijos directos del grid */
#div_articulos > div[id],
#div_articulos > div[onclick] {
    background: var(--fact-card) !important;
    border: 1px solid var(--fact-border) !important;
    border-radius: 12px !important;
    padding: 0 !important;
    height: 130px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: flex-start !important;
    overflow: hidden !important;
    cursor: pointer !important;
    transition: box-shadow .15s, transform .12s !important;
    box-shadow: var(--fact-shadow) !important;
    position: relative !important;
}

#div_articulos > div[id]:hover,
#div_articulos > div[onclick]:hover {
    box-shadow: 0 4px 18px rgba(37,99,235,.18) !important;
    transform: translateY(-2px) !important;
    border-color: var(--fact-accent) !important;
}
/* ── Layout principal ───────────────────────────────────────────── */
#fact-wrapper {
    display: flex;
    gap: 14px;
    height: calc(100vh - 130px);
    min-height: 520px;
    padding: 14px;
    background: var(--fact-bg);
    box-sizing: border-box;
}

/* ── Panel izquierdo: búsqueda + categorías + artículos ─────────── */
#fact-panel-left {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 1 1 55%;
    min-width: 0;
}

#fact-search-bar {
    background: var(--fact-card);
    border-radius: 10px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--fact-shadow);
    border: 1px solid var(--fact-border);
}
#fact-search-bar .material-icons { color: var(--fact-muted); font-size: 20px; }
#fact-catalog {
    display: flex;
    gap: 8px;
    flex: 1 1 auto;
    min-height: 0;
    overflow: hidden;
}

/* ── Columna de categorías ──────────────────────────────────────── */
#fact-cats {
    width: 110px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    gap: 0;
    overflow-y: auto;
    background: var(--fact-cat-bg);
    border-radius: 12px;
    padding: 8px 6px;
    box-shadow: var(--fact-shadow);
}
#fact-cats::-webkit-scrollbar { width: 3px; }
#fact-cats::-webkit-scrollbar-thumb { background: rgba(255,255,255,.2); border-radius: 2px; }

/* Botones de categoría: reestilizar los <td> que genera el JS */
#div_favoritos .cuadrado,
#div_categorias .cuadrado {
    height: auto !important;
    max-width: 100% !important;
    width: 100% !important;
    margin: 2px 0 !important;
    padding: 8px 4px !important;
    border-radius: 8px !important;
    border: none !important;
    background: rgba(255,255,255,.08) !important;
    color: #fff !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    text-align: center !important;
    cursor: pointer !important;
    transition: background .15s !important;
    line-height: 1.3 !important;
    white-space: normal !important;
    word-break: break-word !important;
}
#div_favoritos .cuadrado:hover,
#div_categorias .cuadrado:hover,
#div_favoritos .cuadrado.sombreado,
#div_categorias .cuadrado.sombreado {
    background: var(--fact-cat-hover) !important;
    box-shadow: none !important;
}
/* Tabla contenedora de categorías sin bordes */
#div_favoritos table,
#div_categorias table { width: 100%; border-collapse: collapse; }
#div_favoritos td,
#div_categorias td { padding: 0; }

/* ── Grid de artículos ──────────────────────────────────────────── */
#div_articulos {
    flex: 1 1 auto !important;
    height: auto !important;
    overflow-y: auto !important;
    background: transparent !important;
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)) !important;
    gap: 8px !important;
    align-content: start !important;
    padding: 4px 2px !important;
}
/* Filas del grid que genera CompletarArticulosCategoria */
#div_articulos .row { display: contents !important; margin: 0 !important; }
#div_articulos .col-md-3 {
    padding: 0 !important;
    width: auto !important;
    float: none !important;
}
/* Tarjeta de artículo */
#div_articulos > .row > .col-md-3 > div,
#div_articulos .col-md-3 > div {
    background: var(--fact-card) !important;
    border: 1px solid var(--fact-border) !important;
    border-radius: 12px !important;
    padding: 0 !important;
    height: 130px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: center !important;
    justify-content: flex-start !important;
    overflow: hidden !important;
    cursor: pointer !important;
    transition: box-shadow .15s, transform .12s !important;
    box-shadow: var(--fact-shadow) !important;
    position: relative !important;
}
#div_articulos .col-md-3 > div:hover {
    box-shadow: 0 4px 18px rgba(37,99,235,.18) !important;
    transform: translateY(-2px) !important;
    border-color: var(--fact-accent) !important;
}
/* Imagen de artículo */
#div_articulos article {
    width: 100% !important;
    height: 80px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #f8fafc !important;
    overflow: hidden !important;
    margin: 0 !important;
    padding: 0 !important;
}
#div_articulos article img {
    width: 100% !important;
    height: 80px !important;
    object-fit: contain !important;
    object-position: center !important;
    display: block !important;
}
#div_articulos article h1 { display: none !important; }  /* oculta el <h1> del precio del panel */

/* Chip de precio superpuesto */
#div_articulos .precio {
    position: absolute !important;
    top: 6px !important;
    right: 6px !important;
    left: auto !important;
    bottom: auto !important;
    background: var(--fact-accent) !important;
    color: #fff !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    padding: 2px 6px !important;
    border-radius: 20px !important;
    line-height: 1.4 !important;
    box-shadow: 0 2px 6px rgba(37,99,235,.3) !important;
}
/* Nombre del artículo */
#div_articulos .cuadrado_desc,
#div_articulos p.cuadrado_desc {
    font-size: 10.5px !important;
    font-weight: 600 !important;
    color: var(--fact-text) !important;
    text-align: center !important;
    padding: 4px 6px !important;
    line-height: 1.25 !important;
    width: 100% !important;
    overflow: hidden !important;
    display: -webkit-box !important;
    -webkit-line-clamp: 2 !important;
    -webkit-box-orient: vertical !important;
}

/* ── Panel derecho: ticket + totales ────────────────────────────── */
#fact-panel-right {
    display: flex;
    flex-direction: column;
    gap: 10px;
    flex: 0 0 42%;
    min-width: 300px;
}

/* Barra de añadir artículo por código */
#fact-add-bar {
    background: var(--fact-card);
    border-radius: 10px;
    padding: 8px 12px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--fact-shadow);
    border: 1px solid var(--fact-border);
}
#fact-add-bar .material-icons { color: var(--fact-muted); font-size: 20px; }

/* Tabla de artículos del ticket */
#fact-ticket {
    flex: 1 1 auto;
    background: var(--fact-card);
    border-radius: 12px;
    box-shadow: var(--fact-shadow);
    border: 1px solid var(--fact-border);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}
#fact-ticket-head {
    background: #f8fafc;
    border-bottom: 1px solid var(--fact-border);
    padding: 8px 12px;
    font-size: 11px;
    font-weight: 700;
    color: var(--fact-muted);
    text-transform: uppercase;
    letter-spacing: .04em;
}
#div_venta {
    flex: 1 1 auto;
    overflow-y: auto !important;
    height: auto !important;
    padding: 0 !important;
}
#div_venta::-webkit-scrollbar { width: 4px; }
#div_venta::-webkit-scrollbar-thumb { background: var(--fact-border); border-radius: 2px; }

/* Tabla del ticket */
#table_Articulos {
    width: 100% !important;
    border-collapse: collapse !important;
}
#table_Articulos thead th {
    background: #f8fafc !important;
    color: var(--fact-muted) !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .03em !important;
    padding: 8px 10px !important;
    border-bottom: 1px solid var(--fact-border) !important;
    white-space: nowrap !important;
}
#table_Articulos tbody tr {
    border-bottom: 1px solid #f1f5f9 !important;
    transition: background .1s !important;
}
#table_Articulos tbody tr:hover { background: #f8fafc !important; }
#table_Articulos tbody td {
    padding: 8px 10px !important;
    font-size: 13px !important;
    color: var(--fact-text) !important;
    vertical-align: middle !important;
}
/* Botones editar/eliminar en el ticket */
#table_Articulos .fa-pencil { color: var(--fact-accent); font-size: 13px; }
#table_Articulos .fa-trash  { color: var(--fact-danger);  font-size: 13px; }

/* ── Botones Guardar / Obtener cuenta ───────────────────────────── */
#fact-actions {
    display: flex;
    gap: 8px;
}
#tab_cliente{
    height: calc(100vh - 130px);
    overflow-y: auto;
}

.fact-btn {
    flex: 1;
    height: 38px;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: opacity .15s, box-shadow .15s;
}
.fact-btn-save {
    background: #f1f5f9;
    color: var(--fact-text);
    border: 1.5px solid var(--fact-border);
}
.fact-btn-save:hover { background: #e2e8f0; }
.fact-btn-pay {
    background: linear-gradient(135deg, var(--fact-accent2), var(--fact-accent));
    color: #fff;
    box-shadow: 0 3px 10px rgba(37,99,235,.3);
}
.fact-btn-pay:hover { opacity: .9; }
.fact-btn-checkout {
    width: 100%;
    height: 44px;
    background: linear-gradient(135deg, var(--fact-success), #15803d);
    color: #fff;
    border-radius: 10px;
    border: none;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 3px 12px rgba(22,163,74,.3);
    transition: opacity .15s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.fact-btn-checkout:hover { opacity: .9; }

/* ── Totales ────────────────────────────────────────────────────── */
#fact-totals {
    background: var(--fact-card);
    border-radius: 12px;
    box-shadow: var(--fact-shadow);
    border: 1px solid var(--fact-border);
    padding: 12px 14px;
}
.fact-total-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    font-size: 13px;
    color: var(--fact-muted);
}
.fact-total-row.is-total {
    border-top: 1px solid var(--fact-border);
    margin-top: 6px;
    padding-top: 8px;
    font-size: 16px;
    font-weight: 800;
    color: var(--fact-text);
}
.fact-total-row .fact-val { font-weight: 600; color: var(--fact-text); }
.fact-total-row.is-total .fact-val { color: var(--fact-accent); font-size: 18px; }

/* ── Tabs de la página ──────────────────────────────────────────── */
.c-content-center.modern-page > .nav-tabs { margin-bottom: 0 !important; }
.c-content-center.modern-page > .nav-tabs > li > a {
    font-size: 14px !important;
    font-weight: 600 !important;
    color: var(--fact-muted) !important;
    padding: 10px 20px !important;
}
.c-content-center.modern-page > .nav-tabs > li.active > a {
    color: var(--fact-accent) !important;
    border-top: 3px solid var(--fact-accent) !important;
}

/* Layout reemplazado — el .row legacy fue eliminado del HTML */
#fact-wrapper { display: flex !important; }

/* ── Selector de tipo de doc / cliente (barra superior) ────────── */
#fact-topbar {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px 0;
    background: var(--fact-bg);
    flex-wrap: wrap;
}

/* legacy overrides: ocultar el sombreado azul oscuro */
.sombreado { box-shadow: 0 0 0 2px var(--fact-accent) !important; }
.sombreado_mp { box-shadow: 0 0 0 2px var(--fact-accent) !important; }

/* Scrollbar general */
* { scrollbar-width: thin; scrollbar-color: var(--fact-border) transparent; }


/* ═══════════════════════════════════════════════════════════════════
   COBRANZA — Botones pago, tabla, totales y finalizar
   ═══════════════════════════════════════════════════════════════════ */

/* ── Botones Efectivo / Tarjeta / Nota de Crédito ───────────────── */
#tab_cliente .row .col-md-4 .btn.btn-primary {
    width: 100% !important;
    height: 42px !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 13px !important;
    font-weight: 600 !important;
    background: linear-gradient(135deg, var(--fact-accent2), var(--fact-accent)) !important;
    color: #fff !important;
    box-shadow: 0 3px 10px rgba(37,99,235,.28) !important;
    transition: opacity .15s !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
}
#tab_cliente .row .col-md-4 .btn.btn-primary:hover { opacity: .9 !important; }

/* ── Contenedor tabla de pagos ──────────────────────────────────── */
#tab_cliente div[style*="height:238px"],
#tab_cliente div[style*="height: 238px"] {
    border: 1px solid var(--fact-border) !important;
    border-radius: 10px !important;
    background: var(--fact-card) !important;
    box-shadow: var(--fact-shadow) !important;
    overflow-y: auto !important;
    margin-top: 10px !important;
}

#tab_cliente #tabla_pago {
    width: 100% !important;
    border-collapse: collapse !important;
}
#tab_cliente #tabla_pago thead th {
    background: #f8fafc !important;
    color: var(--fact-muted) !important;
    font-size: 11px !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: .03em !important;
    padding: 8px 10px !important;
    border-bottom: 1px solid var(--fact-border) !important;
    white-space: nowrap !important;
}
#tab_cliente #tabla_pago tbody tr {
    border-bottom: 1px solid #f1f5f9 !important;
    transition: background .1s !important;
}
#tab_cliente #tabla_pago tbody tr:hover { background: #f8fafc !important; }
#tab_cliente #tabla_pago tbody td {
    padding: 7px 10px !important;
    font-size: 13px !important;
    color: var(--fact-text) !important;
    vertical-align: middle !important;
}

/* ── Totales: Monto Ingresado / Faltante / Vuelto ───────────────── */
#tab_cliente .col-md-12[style*="border: groove"] {
    border: 1px solid var(--fact-border) !important;
    border-radius: 12px !important;
    background: var(--fact-card) !important;
    box-shadow: var(--fact-shadow) !important;
    padding: 10px 14px !important;
    margin-top: 10px !important;
    box-sizing: border-box !important;
}

#tab_cliente .col-md-12[style*="border: groove"] .row {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    margin: 0 !important;
    padding: 6px 0 !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
#tab_cliente .col-md-12[style*="border: groove"] .row:last-child {
    border-bottom: none !important;
}

/* Etiqueta (col-md-6) */
#tab_cliente .col-md-12[style*="border: groove"] .col-md-6 {
    flex: 1 1 auto !important;
    float: none !important;
    width: auto !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    color: var(--fact-muted) !important;
    padding: 0 !important;
}

/* Símbolo moneda (col-md-4) */
#tab_cliente .col-md-12[style*="border: groove"] .col-md-4 {
    flex: 0 0 auto !important;
    float: none !important;
    width: auto !important;
    font-size: 13px !important;
    color: var(--fact-muted) !important;
    padding: 0 6px !important;
    text-align: right !important;
}

/* Valor numérico (col-md-2) */
#tab_cliente .col-md-12[style*="border: groove"] .col-md-2 {
    flex: 0 0 auto !important;
    float: none !important;
    width: auto !important;
    min-width: 50px !important;
    text-align: right !important;
    font-size: 14px !important;
    font-weight: 700 !important;
    color: var(--fact-accent) !important;
    padding: 0 !important;
}

#tab_cliente input.btn[value="Finalizar Cobranza"] {
    display: block !important;
    width: 100% !important;
    height: 46px !important;
    margin-top: 140px !important;
    border: none !important;
    border-radius: 10px !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    background: linear-gradient(135deg, var(--fact-success), #15803d) !important;
    color: #fff !important;
    box-shadow: 0 3px 12px rgba(22,163,74,.3) !important;
    cursor: pointer !important;
    transition: opacity .15s !important;
    letter-spacing: .02em !important;
}
#tab_cliente input.btn[value="Finalizar Cobranza"]:hover { opacity: .9 !important; }
/* ═══════════════════════════════════════════════════════════════════
   IMPRESIÓN FINAL — Corrección real del comprobante lleno
   ═══════════════════════════════════════════════════════════════════ */

#zona-imprimir,
#zona-imprimir > div:first-child {
    width: 280px !important;
    max-width: 280px !important;
    font-family: Arial, Helvetica, sans-serif !important;
    font-size: 11px !important;
    line-height: 1.15 !important;
    color: #000 !important;
    box-sizing: border-box !important;
}

/* Reset de columnas Bootstrap dentro del ticket */
#zona-imprimir .col-xs-12,
#zona-imprimir .col-xs-8,
#zona-imprimir .col-xs-6,
#zona-imprimir .col-xs-4,
#zona-imprimir .col-xs-3 {
    box-sizing: border-box !important;
    padding-left: 2px !important;
    padding-right: 2px !important;
    margin: 0 !important;
    min-height: 1px !important;
}

/* Cabecera */
#idlogoTicket {
    display: block !important;
    width: 50px !important;
    margin: 6px auto 8px !important;
}

#nombre_empresa1,
#direccion_empresa,
#direccionubigeo_empresa,
#nombre_tienda,
#direccion_tienda,
#ubigeo_tienda,
#nombre_documento,
#codigo_documento,
#son_documento,
#vendedor,
#codigo_caja {
    width: 100% !important;
    float: left !important;
    text-align: center !important;
    margin-bottom: 2px !important;
}

#ruc_empresa,
#fecha_documento {
    text-align: left !important;
}
#telefono_tienda,
#hora_documento {
    text-align: right !important;
}

#nombre_cliente,
#direccion_cliente,
#ruc_cliente {
    width: 100% !important;
    float: left !important;
    text-align: left !important;
    margin-bottom: 2px !important;
}

/* Encabezado del detalle */
#zona-imprimir div[style*="text-align: center;"] .col-xs-3 {
    text-align: center !important;
    font-size: 10px !important;
    font-weight: 700 !important;
    line-height: 1.05 !important;
}

/* ── DETALLE DE ARTÍCULOS: convertirlo en filas tipo tabla ──────── */
#div_articlosdocumento {
    width: 100% !important;
    float: left !important;
    clear: both !important;
}

#div_articlosdocumento > div,
#div_articlosdocumento > .row {
    width: 100% !important;
    float: left !important;
    clear: both !important;
    display: table !important;
    table-layout: fixed !important;
    margin: 0 !important;
    padding: 0 !important;
}

#div_articlosdocumento > div .col-xs-3,
#div_articlosdocumento > .row .col-xs-3 {
    float: none !important;
    display: table-cell !important;
    vertical-align: top !important;
    width: 25% !important;
    padding: 1px 2px !important;
    line-height: 1.15 !important;
}

/* Columna descripción a la izquierda, resto a la derecha */
#div_articlosdocumento > div .col-xs-3:nth-child(1),
#div_articlosdocumento > .row .col-xs-3:nth-child(1) {
    text-align: left !important;
}
#div_articlosdocumento > div .col-xs-3:nth-child(2),
#div_articlosdocumento > div .col-xs-3:nth-child(3),
#div_articlosdocumento > div .col-xs-3:nth-child(4),
#div_articlosdocumento > .row .col-xs-3:nth-child(2),
#div_articlosdocumento > .row .col-xs-3:nth-child(3),
#div_articlosdocumento > .row .col-xs-3:nth-child(4) {
    text-align: right !important;
}

/* Si el JS mete columnas fuera de row */
#div_articlosdocumento .col-xs-12 {
    width: 100% !important;
    float: left !important;
    clear: both !important;
}

/* ── COBRANZA DINÁMICA: efectivo/tarjeta/etc. en dos columnas ───── */
#div_cobranzadocumento {
    width: 100% !important;
    float: left !important;
    clear: both !important;
}

#div_cobranzadocumento > div,
#div_cobranzadocumento > .row {
    width: 100% !important;
    float: left !important;
    clear: both !important;
    display: table !important;
    table-layout: fixed !important;
    margin: 0 !important;
    padding: 0 !important;
}

#div_cobranzadocumento > div .col-xs-8,
#div_cobranzadocumento > .row .col-xs-8 {
    float: none !important;
    display: table-cell !important;
    width: 65% !important;
    text-align: left !important;
    vertical-align: top !important;
    padding: 1px 2px !important;
}

#div_cobranzadocumento > div .col-xs-4,
#div_cobranzadocumento > .row .col-xs-4 {
    float: none !important;
    display: table-cell !important;
    width: 35% !important;
    text-align: right !important;
    vertical-align: top !important;
    padding: 1px 2px !important;
}

/* ── RESUMEN MONETARIO: Subtotal / IGV / Total / Vuelto ─────────── */
#opgrabada_documento,
#igv_documento,
#isc_documento,
#total_documento,
#vuelto {
    text-align: right !important;
    font-weight: 700 !important;
}

/* Cada bloque de 3 columnas debe comportarse como tabla */
#zona-imprimir .col-xs-4 {
    line-height: 1.15 !important;
}

#zona-imprimir .col-xs-4:nth-child(3n+1) {
    text-align: left !important;
}
#zona-imprimir .col-xs-4:nth-child(3n+2),
#zona-imprimir .col-xs-4:nth-child(3n+3) {
    text-align: right !important;
}

/* Condición de pago */
#zona-imprimir .col-xs-8 {
    text-align: left !important;
}
#zona-imprimir .col-xs-8 + .col-xs-4 {
    text-align: right !important;
    font-weight: 700 !important;
}

/* Evitar que se rompa en varias líneas de forma rara */
#zona-imprimir * {
    word-break: break-word !important;
    overflow-wrap: break-word !important;
    box-sizing: border-box !important;
}

/* QR y pie */
#qrcode {
    width: 100% !important;
    float: left !important;
    text-align: center !important;
    margin-top: 6px !important;
}
#qrcode img,
#qrcode canvas {
    margin: 0 auto !important;
}
</style>


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
<input id="hdd_ruc" type="hidden" value="<?= e($objUsuario->cnum_tribu ?? "") ?>"/>
<input id="hhd_empresa" type="hidden" value="<?= e($objUsuario->cdescripcion ?? "") ?>"/>

<input id="impuesto_actual" type="hidden" />

<input id="hhd_direccionE" type="hidden" value="<?= e($objUsuario->cdomicilio ?? "") ?>"/>
<input id="hhd_ubigeoE" type="hidden" value="<?= e($objUsuario->cdepartamento ?? "") . "-" . e($objUsuario->cprovincia ?? "") . "-" . e($objUsuario->cdistrito ?? "") ?>"/>
<label id="lSimMoneda" style="display:none;" ><?= e($objUsuario->csimbolo_moneda ?? "") ?></label>
<label id="lNomMoneda" style="display:none;" ><?= e($objUsuario->cnombre_moneda ?? "") ?></label>
<label id="hdd_rucdat" style="display:none;" ><?= e($objUsuario->ccod_empresa ?? "") ?></label>
<input id="hdd_telefono_tienda" type="hidden" value="<?= e($objUsuario->ctelf_tienda ?? "") ?>"/>
<input id="hdd_nombre_tienda" type="hidden" value="<?= e($objUsuario->cdsc_tienda ?? "") ?>"/>
<input id="hdd_ubigeo_tienda" type="hidden" value="<?= e($objUsuario->cdepartamento_tienda ?? "") . "-" . e($objUsuario->cprovincia_tienda ?? "") . "-" . e($objUsuario->cdistrito_tienda ?? "") ?>"/>
<label id="FactElectronica" style="display:none;" ><?= e($objUsuario->ctip_facturador ?? "") ?></label>
<input id="hdd_ctip_doc" type="hidden"/>
<input id="hdd_id_cbfact" type="hidden"/>

<div class="c-content-center modern-page">





    <ul class="nav nav-tabs" style="">
        <li onclick="" class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color: #228ac9;
            font-size: 17px;">Factura</a></li>
        <li onclick="Cambiar_Cobranza();"><a data-toggle="tab" class="tabcito" href="#tab_cliente" style="color: #228ac9;
            font-size: 17px;">Cobranza</a></li>
        <li onclick="tab_listaclick();">
        <a data-toggle="tab" href="#Lista" class="tabcito" style="color: #228ac9; font-size: 17px;display:none;">Lista</a></li>
    </ul>
    <div class="tab-content">
        <div id="Datos" class="tab-pane in active" style="padding:0;">

            <!-- NUEVO LAYOUT FACTURACION -->
            <div id="fact-wrapper">

                <!-- Panel izquierdo -->
                <div id="fact-panel-left">
                    <div id="fact-search-bar">
                        <i class="material-icons">search</i>
                        <input id="tb_articulo" onkeyup="BuscarArticulos();" class="limpiar" placeholder="Buscar articulos..." autocomplete="off"/>
                    </div>
                    <div id="fact-catalog">
                        <div id="fact-cats">
                            <div id="div_favoritos"></div>
                            <div id="div_categorias"></div>
                        </div>
                        <div id="div_articulos"></div>
                    </div>
                </div>

                <!-- Panel derecho -->
                <div id="fact-panel-right">
                    <div id="fact-ticket">
                        <div id="fact-ticket-head">
                            <i class="material-icons" style="font-size:13px;vertical-align:middle;margin-right:4px;">receipt_long</i>
                            Articulos del pedido
                        </div>
                        <div id="div_venta">
                            <table id="table_Articulos">
                                <colgroup>
                                    <col style="width:38%">
                                    <col style="width:11%">
                                    <col style="width:13%">
                                    <col style="width:14%">
                                    <col style="width:7%">
                                    <col style="width:7%">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>Articulo</th>
                                        <th>Cant.</th>
                                        <th>Precio</th>
                                        <th>Importe</th>
                                        <th></th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div id="fact-actions">
                        <button type="button" class="fact-btn fact-btn-save" onclick="ValidarCuenta()">
                            <i class="material-icons" style="font-size:15px;vertical-align:middle;margin-right:4px;">save</i>Guardar cuenta
                        </button>
                        <button type="button" class="fact-btn fact-btn-pay" onclick="CargarCuentas();" data-toggle="modal" data-target="#modalObtenerCuenta">
                            <i class="material-icons" style="font-size:15px;vertical-align:middle;margin-right:4px;">folder_open</i>Obtener cuenta
                        </button>
                    </div>
                    <div id="fact-totals">
                        <div class="fact-total-row">
                            <span>Descuento</span>
                            <span class="fact-val"><?= e($objUsuario->csimbolo_moneda ?? "") ?> <span id="div_desc">0.00</span></span>
                        </div>
                        <div class="fact-total-row">
                            <span>Subtotal</span>
                            <span class="fact-val"><?= e($objUsuario->csimbolo_moneda ?? "") ?> <span id="div_subtotal">0.00</span></span>
                        </div>
                        <div class="fact-total-row">
                            <span>IGV</span>
                            <span class="fact-val"><?= e($objUsuario->csimbolo_moneda ?? "") ?> <span id="div_igv">0.00</span></span>
                        </div>
                        <div style="display:none"><span id="div_isc">0.00</span></div>
                        <div class="fact-total-row is-total">
                            <span>Total</span>
                            <span class="fact-val"><?= e($objUsuario->csimbolo_moneda ?? "") ?> <span id="div_total">0.00</span></span>
                        </div>
                    </div>
                    <button type="button" class="fact-btn-checkout" onclick="Cambiar_Cobranza();">
                        <i class="material-icons" style="font-size:18px;">payments</i>
                        Ir a Cobranza
                    </button>
                </div>
            </div>
            <!-- Fin nuevo layout -->

            <!-- Modales legacy (deben permanecer en el DOM) -->
            <div id="modalGuardarCuenta" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-sm"><div class="modal-content">
                  <div class="modal-body">
                    <div class="input-group"><span class="has-float-label">
                        <input id="tb_etiqueta" onkeyup="if($('#tb_etiqueta').val().length==0) $('#btn_guardarcuenta').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_guardarcuenta').removeClass('fa_disabled').addClass('fa_enabled');" type="text" class="limpiar form-control moderno_tb" placeholder=" "/>
                        <label for="tb_etiqueta">Nombre de cuenta</label>
                    </span></div>
                  </div>
                  <div class="modal-footer" style="border-top:0;">
                    <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="GuardarCuenta();" id="btn_guardarcuenta">Confirmar</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  </div>
              </div></div>
            </div>

            <div onclick="CambiarFavoritos();" id="modalfavoritos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
              <div id="modalfavoritos_position" class="modal-dialog modal-sm"><div class="modal-content">
                  <div id="div_favtext">añadir a favoritos</div>
              </div></div>
            </div>

            <div id="modalObtenerCuenta" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
              <div class="modal-dialog modal-sm"><div class="modal-content">
                  <div class="modal-body">
                    <table id="tablacuentas" class="table table-bordered table-striped">
                      <thead><tr><th class="text-center">Cuenta</th><th class="text-center">Fecha</th></tr></thead>
                      <tbody></tbody>
                    </table>
                  </div>
                  <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button></div>
              </div></div>
            </div>

        </div>


        <div id="tab_cliente" class="tab-pane" style="padding: 13px;">
            <div class="col-md-4">
                <div id="zona-imprimir" style="border: outset; width: 280px;">
                    <div style="width: 280px;font-size: 10px;">
                        <div style="text-align: center;">
                            <image id="idlogoTicket" style="width: 50px;margin-top: 10px;"></image>
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
                            <div class="col-xs-12">==========================================</div>
                            <div class="col-xs-12" id="nombre_documento"></div>
                            <div class="col-xs-12" id="codigo_documento"></div>
                            <div class="col-xs-12">==========================================</div>
                        </div>
                        <div>
                            <div class="col-xs-6" id="fecha_documento"></div>
                            <div class="col-xs-6" style="text-align: right;" id="hora_documento"></div>
                        </div>
                        <div class="col-xs-12" id="nombre_cliente"></div>
                        <div class="col-xs-12" id="direccion_cliente"></div>
                        <div class="col-xs-12" id="ruc_cliente"></div>
                        <div class="col-xs-12">==========================================</div>
                        <div style="text-align: center;">
                            <div class="col-xs-3">Descrip.</div>
                            <div class="col-xs-3">Cant.</div>
                            <div class="col-xs-3">P.Unit</div>
                            <div class="col-xs-3">Monto</div>
                        </div>
                        <div class="col-xs-12">==========================================</div>
                        <div id="div_articlosdocumento"></div>
                        <div class="col-xs-12">==========================================</div>
                        <div class="col-xs-4">Sub. Total</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>
                        <div class="col-xs-4" id="opgrabada_documento" style="text-align: right;"></div>
                        <div class="col-xs-4">IGV</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>
                        <div class="col-xs-4" id="igv_documento" style="text-align: right;"></div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;">ISC</div>
                        <div class="col-xs-4" style="text-align: right;DISPLAY: NONE;"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>
                        <div class="col-xs-4" id="isc_documento" style="text-align: right;DISPLAY: NONE;"></div>
                        <div class="col-xs-4">Total a Pagar</div>
                        <div class="col-xs-4" style="text-align: right;"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>
                        <div class="col-xs-4" id="total_documento" style="text-align: right;"></div>
                        <div class="col-xs-12">==========================================</div>
                        <div class="col-xs-12" id="son_documento"></div>
                        <div class="col-xs-12">==========================================</div>
                        <div id="div_cobranzadocumento"></div>
                        <div class="col-xs-4">Vuelto</div> 
                        <div class="col-xs-4" style="text-align: right;"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>                
                        <div class="col-xs-4" id="vuelto" style="text-align: right;"></div>
                        <div class="col-xs-8">Condición de Pago</div>                
                        <div class="col-xs-4" style="text-align: right;">CONTADO</div>
                        <div class="col-xs-12">==========================================</div>
                        <div class="col-xs-12" id="vendedor"></div>
                        <div class="col-xs-12" id="codigo_caja"></div>
                        <div class="col-xs-12">==========================================</div>
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

            <div class="col-md-8">

                
                <div class="col-md-12 row">
                    <div class="col-md-9 ui-widget">
                        <div class="input-group">
                            <span class="has-float-label">
                                <input id="tb_clientes" onkeyup="BuscarClientes();"  type="text"  class="limpiar form-control moderno_tb"
                                    placeholder=" " autocomplete="off"/>
                                <label for="txtCliente">
                                    Buscar Clientes</label>
                            </span><a class="disabled input-group-addon" data-toggle="modal" data-target="#modalConsultarClientes"
                                onclick="ModalConsultarClientes();" style="background-color: #ffffff; border: 0px">
                                <i class="fa fa-search color-buscadores" aria-hidden="true"></i></a>
                        </div>
                        <div id="sugerencias_clientes" style="display: none; width: 752px; height:519px; overflow-y: auto;z-index: 9999;position: absolute;background-color: white;"></div>
                         
                    </div>
                    <div class="col-md-3 row" style="PADDING: 0PX; margin-left: 15px;" >
                        <input type="radio" id="rb_boleta" name="tipo" value="BV" checked>
                        <label id="ll_boleta" for="male">Boleta</label><br>

                        <input type="radio" id="rb_factura" name="tipo" value="FV" onchange="$('#hdd_coa').val('');$('#tb_clientes').val('')">
                        <label id="ll_factura" for="female">Factura</label><br>

                        <input type="radio" id="rb_notaventa" name="tipo" value="NV">
                        <label for="female">Nota de Venta</label><br>
                   </div>
                </div>

                 
                


                <div class="row">
                    <div class="col-md-4" style="text-align: center;">
                        
                        
                        <button   type="button" class="btn btn-primary" style="width: -webkit-fill-available;"  onclick="EfectivoNuevo();">
                            <i class="fa fa-money" style="font-size: 18px;"></i> Efectivo
                        </button>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        
                        
                        <button  type="button" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="TarjetaNuevo();">
                            <i class="fa fa-credit-card" style="font-size: 18px;"></i> Tarjeta
                        </button>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        
                        
                        <button  type="button" class="btn btn-primary" style="width: -webkit-fill-available;" onclick="NuevoNC();">
                            <i class="fa fa-sticky-note-o" style="font-size: 18px;"></i> Nota de Crédito
                        </button>
                    </div>
                </div>



                <div style="height:238px; overflow-y: auto;">
                    <table id="tabla_pago" style="width: -webkit-fill-available;">
                        <colgroup>  
                            <col style="width:20%"></col> 
                            <col style="width:15%"></col> 
                            <col style="width:15%"></col>
                            <col style="width:15%"></col>
                            <col style="width:5%"></col>
                            <col style="width:5%"></col>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">Pago</th>
                                <th class="text-center">Número Tarjeta</th>
                                <th class="text-center">Número Ref.</th>
                                <th class="text-center">Monto</th>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>

                            </tr>
                        </tbody>
                    </table>
                </div>
                
                

                <div class="col-md-12" style="border: groove;/*margin-top: 350px;*/">
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Monto Ingresado:</div>         
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div>        
                        <div id="div_totalcobranza" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                    </div>
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Faltante:</div>                
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div id="div_faltante" class="col-md-2" style="text-align-last: end;">0.00</div>                                   
                    </div>
                    <div class="row" style="margin-bottom: 2px;">
                        <div class="col-md-6">Vuelto:</div>                
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div id="div_vuelto" class="col-md-2" style="text-align-last: end;">0.00</div>    
                    </div>
                </div>

                <input type="button" class="btn btn-primary" value="Finalizar Cobranza" onclick="Cobrar();" style="margin-top: 10px;width: -webkit-fill-available;"/>

            </div>

        </div>

            <!-- LISTADO -->
            <div id="Lista" class="tab-pane tabcito" style="padding: 13px;">

                <div class="row" style="padding-top: 10px;">
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="tb_cliente" type="text" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_cliente">Cliente</label>
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="tb_ruc" type="number" maxlength="11" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_ruc">Ruc</label>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-4">
                        <div class="floating-label">
                            <select class="floating-select" onclick="this.setAttribute('value', this.value);" value="" id="ddl_tipodoc">
                                <option value=""></option>
                                <option value="BV">Boleta</option>
                                <option value="FV">Factura</option>
                                <option value="NC">Nota de credito</option>
                            </select>
                            <label class="floating-select2">Tipo Documento</label>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <span class="has-float-label">
                            <input id="tb_serie" type="number" maxlength="8" class="limpiar form-control moderno_tb" placeholder=" "/>
                            <label for="tb_serie">Serie</label>
                        </span>
                    </div>
                    <div class="col-sm-4">
                        <span class="has-float-label">
                            <input id="dp_fechadoc" type="text" class="limpiar form-control moderno_tb" placeholder=""  />
                            <label for="dp_fechadoc">Fecha Documento</label>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="floating-label">
                            <select class="floating-select" value="" id="ddl_estado">
                                <option value=""></option>
                                <option value="1">Por enviar</option>
                                <option value="2">Enviando</option>
                                <option value="3">Enviado a Sunat</option>
                                <option value="4">Declarado Sunat - Aceptado</option>
                                <option value="5">Declarado Sunat - Aceptado con Obs</option>
                                <option value="6">Declarado Sunat - Rechazado</option>
                            </select>
                            <label class="floating-select2">Estado Documento</label>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <span class="has-float-label">
                            <input id="dp_fechasunat" type="text" class="limpiar form-control moderno_tb" placeholder=""/>
                            <label for="dp_fechasunat">Fecha Sunat</label>
                        </span>
                    </div>
                </div>

                <table id="table_id" class="display" style="width: -webkit-fill-available;">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Tipo Documento</th>
                            <th>Serie y Número</th>
                            <th>Importe Total</th>
                            <th>Fecha Documento</th>
                            <th>Estado</th>
                            <th>Fecha Sunat</th>
                            <th>Error Sunat</th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>



    </div>





    <div id="modalEfectivoNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div class="col-md-1" id="modal_divTotalEfectivoNuevo"></div>
                </div>
            </div>
            <div class="modal-body">
                <input id="tb_montonuevoefectivo" type="number" placeHolder="Ingrese Monto" class="moderno_tb" onkeyup="if($('#tb_montonuevoefectivo').val().length==0) $('#btn_confirmarmontonuevoefectivo').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_confirmarmontonuevoefectivo').removeClass('fa_disabled').addClass('fa_enabled');"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="PasarPagoEfectivo();" id="btn_confirmarmontonuevoefectivo">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>
    
    <div id="modalEditarCantidad" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
      
        <div class="modal-content">
            <div class="modal-header">
            </div>
            <div class="modal-body">

                <div class="row">

                    <input type="hidden" id="hdd_descmax"/>
                    <input type="hidden" id="hdd_precio"/>

                    <div class="col-md-12" style="margin-bottom: 20px;">
                        <div class="row col-md-8">
                            <div class="col-md-3">Producto:</div>
                            <div class="col-md-9"><input disabled id="tb_nombre" class="moderno_tb"/></div>    
                        </div>
                        <div class="row col-md-4">
                            <div class="col-md-4">Precio:</div>
                            <div class="col-md-8"><input  id="tb_precio" class="moderno_tb" style="width: -webkit-fill-available;"/></div>
                        </div>
                    </div>

                    <div class="col-md-12">

                        <div class="row col-md-5">
                            <div class="col-md-4" style="margin-bottom: 5px;">Cantidad:</div>
                            <div class="col-md-8"><input id="tb_cantidad" onfocus="this.select();" type="number" class="moderno_tb" style="width: inherit;"/></div>  
                        </div>
                        <div class="row col-md-7">
                            <div class="col-md-3" style="margin-bottom: 5px;">Descuento:</div>
                            <div class="col-md-5">
                                <input id="tb_descuento" type="number" class="moderno_tb" style="width: -webkit-fill-available;"/>
                            </div>
                            <div class="col-md-4">
		                        <div class="radio-toggle">
			                        <div class="form-check">
			  	                        <label class="form-check-label" id="lb_moneda">
				                            <input class="form-check-input" type="radio" name="exampleRadios" id="cb_moneda" checked>
				                            <?= e($objUsuario->csimbolo_moneda ?? "") ?>
			  	                        </label>
			  	                        <label class="form-check-label" id="lb_porcentaje">
				                            <input class="form-check-input" type="radio" name="exampleRadios" id="cb_porcentaje">
				                            %
			  	                        </label>
			                        </div>
		                        </div>
                            </div>
                        </div>
                    </div>

                    <div id="div_variantes">
                        <div class="col-md-12">
                            <a class="" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample" style="margin-left: 14px;">
                                Variantes
                            </a>
                        </div>
                        <div class="collapse" id="collapseExample">

                            <div class="col-md-12">
                                <div class="col-md-5"><select class="form-control moderno_tb" id="ddl_variante" onchange="CargarSubvariantes();"></select></div>
                                <div class="col-md-5"><select class="form-control moderno_tb" id="ddl_subvariante"></select></div>
                                <div class="col-md-2"><a class="fa fa-plus" style="font-size: 20pt;margin-top: 11px;" onclick="PasarVariante();"></a></div>
                            </div>    
                            <div id="div_eraser" class="col-md-12" style="display:none">
                                <div class="col-md-1" style="margin-top: 15px;"><a class="fa fa-eraser" onclick="$('#cadena_variantes').text('');$('#div_eraser').hide();"></a></div>
                                <div class="col-md-11" style="margin-top: 15px;"><label id="cadena_variantes"></label></div>
                            </div>    

                            <input id="hdd_subvariantes" type="hidden" />
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="CambiarCantidad();">Aceptar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalEfectivoEditar" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div class="col-md-1" id="modal_divTotalEfectivoEditar"></div>
                </div>
            </div>
            <div class="modal-body">
                <input id="tb_montoeditarefectivo" type="number" placeHolder="Ingrese Monto" class="moderno_tb" onkeyup="if($('#tb_montoeditarefectivo').val().length==0) $('#btn_editarpagoefectivo').removeClass('fa_enabled').addClass('fa_disabled'); else $('#btn_editarpagoefectivo').removeClass('fa_disabled').addClass('fa_enabled');"/>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="EditarPagoEfectivo();" id="btn_editarpagoefectivo">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalNCNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-8">Importe total:</div> 
                        <div class="col-md-2" id="txtITNC"></div>
                </div>
            </div>
            <div class="modal-body">
                 
                <table id="tableNC" class="display" style="width: -webkit-fill-available;">
                   <colgroup>  
                        <col style="width:5%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col>
                        <col style="width:10%"></col> 
                    </colgroup>
                    <thead id="thtableClientes">
                        <tr> 
                            <th> 
                            </th>
                            <th>
                                Doc. Ref.
                            </th>
                             <th>
                                Credito
                            </th>
                            <th>
                                Fecha Doc.
                            </th>  
                        </tr>
                    </thead>
                    <tbody  >
                    </tbody>
                </table>
               
            </div>
            <div class="modal-footer"> 
                <button type="button" class="btn btn-primary"   onclick="PasarPagoNC();">
                        Seleccionar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>


    <div id="modalTarjetaNuevo" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div class="col-md-1" id="modal_divTotalNuevoTarjeta"></div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 40px;">
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" class="sombreado_mp" src="<?= basePath() ?>/assets/Styles/img/logo_visa.png"  onclick="PagoVisa(this)"/>
                    </div>
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" src="<?= basePath() ?>/assets/Styles/img/logo_mastercard.png"  onclick="PagoMasterCard(this)"/>
                    </div>  
                    <div class="col-md-4" style="text-align: center;">
                        <img style="inline-size: 35%;" src="<?= basePath() ?>/assets/Styles/img/otrastarjetas.png" onclick="PagoOtraTarjeta(this)"/>
                    </div>                          
                </div>
                <div  class="row" id="div_tarjetanuevo" style="display: none;text-align: center;">
                    <select id="ddl_tarjetanuevo" class="moderno_tb" onchange="$('#hdd_metodopago').val(this.value);">
                        <option>Seleccione tipo de Tarjeta</option>
                        <option>Diners Club</option>
                        <option>American Express</option>
                        <option>Transferencia</option>
                        <option>Yape</option>
                        <option>Plin</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        Monto de Operación
                    </div>
                    <div class="col-md-6">
                        <input type="number" onfocusout="PerdidaFoco(this);" id="tb_montonuevotarjeta" class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Últimos 4 dígitos
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_tarjeta" maxlength="4"class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Número de referencia
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_referencia" maxlength="8" class="moderno_tb" onkeyup="if( $('#tb_montonuevotarjeta').val()>0 &&  $('#tb_tarjeta').val().length>0 && $('#tb_referencia').val().length>0) $('#btn_confirmartarjeta').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjeta').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="PasarPagoTarjeta();" id="btn_confirmartarjeta">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalTarjetaEditar" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row">
                        <div class="col-md-5">Importe total:</div>
                        <div class="col-md-4 text-right"><?= e($objUsuario->csimbolo_moneda ?? "") ?></div> 
                        <div class="col-md-1" id="modal_divTotalEditarTarjeta"></div>
                </div>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 40px;">
                    <div class="col-md-4">
                        <img id="img_visa" class="sombreado_mp" src="<?= basePath() ?>/assets/Styles/img/logo_visa.png"  onclick="PagoVisa(this)"/>
                    </div>
                    <div class="col-md-4">
                        <img id="img_mastercard" src="<?= basePath() ?>/assets/Styles/img/logo_mastercard.png"  onclick="PagoMasterCard(this)"/>
                    </div>  
                    <div class="col-md-4">
                        <img id="img_otra" src="<?= basePath() ?>/assets/Styles/img/otrastarjetas.png" onclick="PagoOtraTarjeta(this)"/>
                    </div>                          
                </div>
                <div  class="row" id="div_tarjetaeditar" style="display: none">
                    <select id="ddl_tarjetas" class="moderno_tb"  onchange="$('#hdd_metodopago').val(this.value);">
                        <option>Seleccione tipo de Tarjeta</option>
                        <option>Diners Club</option>
                        <option>American Express</option>
                        <option>Transferencia</option>
                        <option>Yape</option>
                        <option>Plin</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        Monto de Operación
                    </div>
                    <div class="col-md-6">
                        <input type="number" onfocusout="PerdidaFoco(this);" id="tb_montoeditartarjeta" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Últimos 4 dígitos
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_tarjetaeditar" maxlength="4" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
                <div class="row">
                    <div class="col-md-6">
                        Número de referencia
                    </div>
                    <div class="col-md-6">
                        <input type="text" id="tb_referenciaeditar" maxlength="8" class="moderno_tb" onkeyup="if( $('#tb_montoeditartarjeta').val()>0 && $('#tb_tarjetaeditar').val().length>0 && $('#tb_referenciaeditar').val().length>0) $('#btn_confirmartarjetaeditar').removeClass('fa_disabled').addClass('fa_enabled'); else $('#btn_confirmartarjetaeditar').removeClass('fa_enabled').addClass('fa_disabled');"/>
                    </div>
                </div> 
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary fa_disabled" data-dismiss="modal" onclick="EditarPagoTarjeta();" id="btn_confirmartarjetaeditar">Confirmar</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
      </div>
    </div>

    <div id="modalResumenVenta" class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 0px;">
                <div class="col-md-12" style="text-align: center;">
                    <img src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" style="width: 33px;">
                    <h4>Operación Completada</h1>
                </div>
            </div>
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-md-6">Num. Documento:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_doc"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Monto Total:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_total"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Monto Entregado:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_entregado"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-6">Vuelto:</div>
                    <div class="col-md-6" style="text-align: right;"><label class="lb_modal" id="lb_vuelto"></label></div>
                </div>
                <div class="row">
                    <div class="col-md-9">Imprimir</div>
                    <div class="col-md-2" style="text-align: right;">
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ckb_Imprimir" value="option1"></div>
                    </div>
                </div>
                <div class="row" style="DISPLAY: NONE;">
                    <div class="col-md-9">Enviar Correo:</div>
                    <div class="col-md-2" style="text-align: right;">
                        <div class="form-check form-check-inline"><input class="form-check-input" type="checkbox" id="ckb_Correo" value="option2"></div>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-4">Whatsapp: 
                    </div>
                     <div class="col-md-5"> 
                     <input id="txtnumwhatsapp" class="limpiar form-control moderno_tb" />
                    </div>
                    <div class="col-md-2" style="text-align: right;">
                        <div class="form-check form-check-inline"><input class="form-check-input" onclick="whatsapp()" type="checkbox" id="ckbwhatsapp" value="option2"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-top: 0px;"> 
                <div class="col-md-12" style="text-align: center;">
                    <button type="button" class="btn btn-primary" onclick="FinalizarResumenDoc();">Confirmar</button>
                </div>
            </div>
        </div>
        </div>
    </div>

    <ul id="MenuFavoritos" class="dropdown-menu" role="menu" style="display:none" >
        <div class="input-group">
            <a><img src="<?= basePath() ?>/assets/Styles/images/icon_exel_c.png" style="width:14px;margin-right:8px;margin-left:5px" />Añadir a Favoritos</a> 
        </div>
    </ul>

 
    <div class="modal" id="modalConsultarClientes" tabindex="-1" role="dialog" aria-labelledby="modalLabel"  aria-hidden="true">
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
                            
                            <col style="width: 60%"></col>
                        </colgroup>
                        <thead id="thTablaConsultarCliente">
                            <tr>
                                <th class="text-center" style="padding: 6px 5px; text-align: left; border: solid 1px #e8eef4;
                                    background-color: rgb(33, 182, 215); color: White;">
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









</div>
<?php if (!empty($startupScript)): ?>
<script type="text/javascript">
    $(document).ready(function() {
        <?= $startupScript ?>
    });
</script>
<?php endif; ?>

<!-- ============================================================
     FEATURE 1: Modal de cantidad al agregar desde panel categorías
     FEATURE 2: Imagen placeholder + object-fit en tarjetas producto
     FEATURE 3: Botón de guía paso a paso
     ============================================================ -->

<!-- ── Modal: Ingresar Cantidad ─────────────────────────────────────── -->
<style>
/* ── Modal cantidad: reset y diseño propio ── */
#modalCantidadRapida .modal-dialog   { margin: 60px auto; max-width: 340px; }
#modalCantidadRapida .modal-content  { border: none; border-radius: 18px; overflow: hidden;
                                        box-shadow: 0 20px 60px rgba(15,76,117,.25); }

/* Cabecera degradado */
#mqr-header {
    background: linear-gradient(135deg, #0d3d6e 0%, #1565a8 60%, #228ac9 100%);
    padding: 20px 20px 24px;
    text-align: center;
    position: relative;
}
#mqr-header .mqr-close {
    position: absolute; top: 14px; right: 16px;
    background: rgba(255,255,255,.15); border: none; border-radius: 50%;
    width: 28px; height: 28px; color: #fff; font-size: 18px; line-height: 28px;
    cursor: pointer; transition: background .15s;
}
#mqr-header .mqr-close:hover { background: rgba(255,255,255,.28); }

/* Imagen del producto (círculo con sombra) */
#mqr-img-wrap {
    width: 80px; height: 80px; border-radius: 50%;
    background: #fff; margin: 0 auto 12px;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 20px rgba(0,0,0,.18);
    overflow: hidden;
}
#mqr_img { width: 72px; height: 72px; object-fit: contain; display: block; }

#mqr_nombre {
    color: #fff; font-size: 15px; font-weight: 700;
    line-height: 1.3; margin: 0; padding: 0 30px;
    text-shadow: 0 1px 4px rgba(0,0,0,.18);
}

/* Cuerpo */
#mqr-body { padding: 22px 24px 16px; background: #fff; }

/* Chip de precio */
#mqr-precio-chip {
    display: flex; align-items: center; justify-content: center;
    gap: 8px; margin-bottom: 20px;
}
#mqr-precio-label {
    font-size: 12px; font-weight: 600; letter-spacing: .04em;
    color: #888; text-transform: uppercase;
}
#mqr_precio {
    font-size: 22px; font-weight: 800; color: #0d3d6e;
    letter-spacing: -.01em;
}

/* Sección cantidad */
#mqr-cant-row {
    display: flex; align-items: center; justify-content: center; gap: 12px;
    margin-bottom: 16px;
}
.mqr-adj {
    width: 40px; height: 40px; border-radius: 10px;
    border: 1.5px solid #d0dbe8; background: #f4f7fb;
    font-size: 22px; line-height: 38px; text-align: center;
    color: #1565a8; cursor: pointer; flex-shrink: 0;
    transition: background .12s, border-color .12s;
    user-select: none;
}
.mqr-adj:hover { background: #e3ecf7; border-color: #1565a8; }
#mqr_cantidad {
    width: 80px; height: 48px; text-align: center;
    font-size: 24px; font-weight: 700; color: #0d3d6e;
    border: 2px solid #228ac9; border-radius: 12px;
    outline: none; -moz-appearance: textfield;
}
#mqr_cantidad::-webkit-inner-spin-button,
#mqr_cantidad::-webkit-outer-spin-button { display: none; }
#mqr_cantidad:focus { border-color: #0d3d6e; box-shadow: 0 0 0 3px rgba(34,138,201,.18); }

/* Subtotal */
#mqr-subtotal-row {
    text-align: center; margin-bottom: 20px;
    font-size: 13px; color: #888;
}
#mqr_subtotal { font-size: 18px; font-weight: 700; color: #1565a8; }

/* Footer */
#mqr-footer {
    display: flex; gap: 10px; padding: 0 24px 22px; background: #fff;
}
#mqr-btn-cancel {
    flex: 1; height: 44px; border: 1.5px solid #d0dbe8;
    border-radius: 12px; background: #f4f7fb; color: #555;
    font-size: 14px; font-weight: 600; cursor: pointer;
    transition: background .12s;
}
#mqr-btn-cancel:hover { background: #e8eef5; }
#mqr_confirmar {
    flex: 2; height: 44px; border: none; border-radius: 12px;
    background: linear-gradient(135deg, #0d3d6e, #228ac9);
    color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    box-shadow: 0 4px 14px rgba(34,138,201,.35);
    transition: opacity .15s, box-shadow .15s;
}
#mqr_confirmar:hover { opacity: .92; box-shadow: 0 6px 18px rgba(34,138,201,.45); }
#mqr_confirmar .material-icons { font-size: 18px; }
</style>

<div class="modal fade" id="modalCantidadRapida" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <!-- Cabecera con imagen + nombre -->
            <div id="mqr-header">
                <button type="button" class="mqr-close" data-dismiss="modal" aria-label="Cerrar">&times;</button>
                <div id="mqr-img-wrap">
                    <img id="mqr_img" src="<?= basePath() ?>/assets/Styles/img/icon/icon_LogoCircle.png" alt="producto"/>
                </div>
                <p id="mqr_nombre">Artículo</p>
            </div>

            <!-- Cuerpo -->
            <div id="mqr-body">

                <!-- Precio unitario -->
                <div id="mqr-precio-chip">
                    <span id="mqr-precio-label">Precio unitario</span>
                    <span id="mqr_precio">S/ 0.00</span>
                </div>

                <!-- Selector de cantidad -->
                <div id="mqr-cant-row">
                    <button type="button" class="mqr-adj" onclick="mqrAjustar(-1)" aria-label="Restar">−</button>
                    <input id="mqr_cantidad" type="number" min="1" value="1" onfocus="this.select();" aria-label="Cantidad"/>
                    <button type="button" class="mqr-adj" onclick="mqrAjustar(1)" aria-label="Sumar">+</button>
                </div>

                <!-- Subtotal -->
                <div id="mqr-subtotal-row">
                    Subtotal &nbsp;<span id="mqr_subtotal">S/ 0.00</span>
                </div>

            </div>

            <!-- Footer -->
            <div id="mqr-footer">
                <button type="button" id="mqr-btn-cancel" data-dismiss="modal">Cancelar</button>
                <button type="button" id="mqr_confirmar">
                    <i class="material-icons">add_shopping_cart</i> Agregar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Modal: Guía paso a paso -->
<div class="modal fade" id="modalGuiaFacturacion" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="max-width:480px;">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header" style="background:linear-gradient(135deg,#0f4c75,#228ac9);padding:16px 20px;">
                <h5 class="modal-title" style="color:#fff;font-size:16px;font-weight:700;margin:0;">
                    <i class="material-icons" style="vertical-align:middle;font-size:20px;margin-right:8px;">help_outline</i>
                    Guía de Facturación
                </h5>
                <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:.8;">&times;</button>
            </div>
            <div class="modal-body" style="padding:20px;">
                <ol style="padding-left:20px;margin:0;list-style:none;">
                    <?php
                    $pasos = [
                        ['receipt','Seleccionar tipo de documento','Elige Factura, Boleta o Nota de Venta en la parte superior izquierda.'],
                        ['person','Seleccionar cliente','Busca el cliente por nombre o RUC. Si no existe, usa el cliente por defecto.'],
                        ['category','Agregar artículos','Haz clic en una categoría del panel izquierdo, luego en el producto. Ingresa la cantidad en el popup y confirma.'],
                        ['edit','Ajustar cantidades y descuentos','Usa el ícono ✏️ en cada fila para modificar cantidad, precio o descuento.'],
                        ['save','Guardar o ir a Cobranza','Pulsa "Guardar Cuenta" para guardar sin cobrar, o "Obtener Cuenta" para ir directamente al cobro.'],
                        ['payments','Registrar pago','En la pestaña Cobranza, selecciona Efectivo, Tarjeta u otro método. Ingresa el monto y confirma.'],
                        ['check_circle','Finalizar venta','Pulsa "Finalizar" para emitir el comprobante y cerrar la venta.'],
                    ];
                    foreach ($pasos as $i => [$icon, $titulo, $desc]):
                    ?>
                    <li style="display:flex;gap:12px;margin-bottom:14px;align-items:flex-start;">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#0f4c75,#228ac9);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;margin-top:1px;">
                            <?= $i+1 ?>
                        </div>
                        <div style="flex:1;">
                            <div style="font-size:13px;font-weight:600;color:#1a3a5c;display:flex;align-items:center;gap:5px;">
                                <i class="material-icons" style="font-size:15px;color:#228ac9;"><?= $icon ?></i>
                                <?= $titulo ?>
                            </div>
                            <div style="font-size:12px;color:#666;margin-top:2px;line-height:1.4;"><?= $desc ?></div>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </div>
            <div class="modal-footer" style="padding:10px 20px 16px;border:none;">
                <button type="button" class="btn btn-primary" data-dismiss="modal"
                        style="background:linear-gradient(135deg,#0f4c75,#228ac9);border:none;border-radius:8px;padding:8px 24px;">
                    Entendido
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante de guía -->
<button type="button" onclick="$('#modalGuiaFacturacion').modal('show');"
        title="Guía paso a paso"
        style="position:fixed;bottom:24px;right:24px;width:48px;height:48px;border-radius:50%;
               background:linear-gradient(135deg,#0f4c75,#228ac9);color:#fff;border:none;
               box-shadow:0 4px 16px rgba(15,76,117,.4);cursor:pointer;z-index:9999;
               display:flex;align-items:center;justify-content:center;font-size:22px;
               transition:transform .18s ease,box-shadow .18s ease;" id="btnGuiaFact"
        onmouseenter="this.style.transform='scale(1.12)';this.style.boxShadow='0 6px 24px rgba(15,76,117,.55)'"
        onmouseleave="this.style.transform='scale(1)';this.style.boxShadow='0 4px 16px rgba(15,76,117,.4)'">
    <i class="material-icons" style="font-size:22px;">help_outline</i>
</button>

<style>
/* ── Tarjetas de producto: imagen con placeholder y object-fit ── */
#div_articulos article img,
#div_articulos img {
    width: 100% !important;
    height: 80px !important;
    object-fit: contain !important;
    object-position: center !important;
    background: #f5f8fb;
    border-radius: 4px;
    display: block;
    max-width: 100% !important;
    max-height: none !important;
}

/* Ocultar imágenes rotas/vacías; el JS las reemplaza con el placeholder */
#div_articulos article img[src="data:image/png;base64,"],
#div_articulos article img:not([src]) {
    display: none;
}

/* Altura de la tarjeta un poco más generosa para la imagen */
#div_articulos [style*="height: 150px"] {
    height: 160px !important;
}
#div_articulos article {
    height: 100px !important;
}

/* ── Modal cantidad: spinner numérico limpio ── */
#mqr_cantidad::-webkit-inner-spin-button,
#mqr_cantidad::-webkit-outer-spin-button { opacity: 1; }
</style>

<!-- El JS de las 3 features vive en assets-patch/facturacion_features.js
     cargado como $pageScriptPatch DESPUÉS de Facturacion6.js —
     esto garantiza que funcione tanto en carga directa como en SPA. -->

<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
