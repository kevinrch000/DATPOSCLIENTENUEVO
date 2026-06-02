<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Informe Almacén | DATPOS';
$pageScript = '';
$showCrudButtons = false;
$filtros = $_SESSION['objReportAlmacen'] ?? array();
$rows = array();

if (!empty($filtros)) {
    $rows = Database::selectStoredTenant('webDatpos_reporteAlmacenPrincipal', array(
        '@ccod_almacen' => $filtros['ccod_almacen'] ?? '',
        '@fchDesde' => fechaToISO($filtros['dfch_desde'] ?? ''),
        '@fchHasta' => fechaToISO($filtros['dfch_hasta'] ?? ''),
        '@ctipo' => $filtros['ctipo'] ?? '',
        '@ccod_articulo' => $filtros['ccod_articulo'] ?? '',
        '@ccod_cia' => $o->ccod_empresa
    ), $o);
}

$headers = array('Documento', 'Tipo Op.', 'Artículo', 'Cantidad', 'Costo', 'Total', 'Fecha');
ob_start();
?>
<div class="c-content-center modern-page" style="padding:20px;background:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid #ddd;padding-bottom:12px;margin-bottom:15px;">
        <div>
            <h3 style="margin:0;color:#046bb4;">Reporte de Almacén</h3>
            <div><?= e($o->cdescripcion ?? '') ?></div>
            <div>RUC: <?= e($o->cnum_tribu ?? '') ?></div>
        </div>
        <button type="button" class="btn btn-primary" onclick="window.print();">Imprimir</button>
    </div>

    <?php if (empty($filtros)): ?>
        <div style="padding:30px;text-align:center;color:#666;">Seleccione parámetros desde Reporte de Almacén y ejecute la consulta.</div>
    <?php else: ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-3"><b>Almacén:</b> <?= e($filtros['cdsc_alm'] ?? $filtros['ccod_almacen'] ?? '') ?></div>
            <div class="col-sm-3"><b>Desde:</b> <?= e($filtros['dfch_desde'] ?? '') ?></div>
            <div class="col-sm-3"><b>Hasta:</b> <?= e($filtros['dfch_hasta'] ?? '') ?></div>
            <div class="col-sm-3"><b>Tipo:</b> <?= e($filtros['ctipo'] ?? '') ?></div>
        </div>

        <table class="table table-bordered table-striped" style="font-size:12px;">
            <thead><tr><?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($headers) ?>" style="text-align:center;">Sin datos</td></tr>
                <?php else: foreach ($rows as $row): ?>
                    <tr><?php for ($i = 0; $i < count($headers); $i++): ?><td><?= e($row[$i] ?? '') ?></td><?php endfor; ?></tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
