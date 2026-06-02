<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Informe Kardex | DATPOS';
$pageScript = '';
$showCrudButtons = false;
$filtros = $_SESSION['objReporteKardex'] ?? array();
$inicio = array();
$kardex = array();

if (!empty($filtros)) {
    $params = array(
        '@ccod_articulo' => $filtros['ccod_articulo'] ?? '',
        '@ccod_alm' => $filtros['ccod_alm'] ?? '',
        '@fchDesde' => fechaToISO($filtros['n_fchDesde'] ?? ''),
        '@fchHasta' => fechaToISO($filtros['n_fchHasta'] ?? ''),
        '@ccod_cia' => $o->ccod_empresa
    );
    $inicio = Database::selectStoredTenant('webDatpos_ReporteKardexInicio', $params, $o);
    $kardex = Database::selectStoredTenant('webDatpos_ReporteKardexArticulos', $params, $o);
}

$headers = array('Doc.', 'Articulo', 'Ent. Cant.', 'Ent. Costo', 'Ent. Total', 'Sal. Cant.', 'Sal. Costo', 'Sal. Total', 'Saldo Cant.', 'Saldo Costo', 'Saldo Total', 'Fecha');
ob_start();
?>
<div class="c-content-center modern-page" style="padding:20px;background:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid #ddd;padding-bottom:12px;margin-bottom:15px;">
        <div>
            <h3 style="margin:0;color:#046bb4;">Reporte Kardex</h3>
            <div><?= e($o->cdescripcion ?? '') ?></div>
            <div>RUC: <?= e($o->cnum_tribu ?? '') ?></div>
        </div>
        <button type="button" class="btn btn-primary" onclick="window.print();">Imprimir</button>
    </div>

    <?php if (empty($filtros)): ?>
        <div style="padding:30px;text-align:center;color:#666;">Seleccione parámetros desde Reporte Kardex y ejecute la consulta.</div>
    <?php else: ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-3"><b>Almacen:</b> <?= e($filtros['cdsc_alm'] ?? $filtros['ccod_alm'] ?? '') ?></div>
            <div class="col-sm-3"><b>Desde:</b> <?= e($filtros['n_fchDesde'] ?? '') ?></div>
            <div class="col-sm-3"><b>Hasta:</b> <?= e($filtros['n_fchHasta'] ?? '') ?></div>
            <div class="col-sm-3"><b>Articulo:</b> <?= e($filtros['ccod_articulo'] ?? '') ?></div>
        </div>

        <table class="table table-bordered table-striped" style="font-size:11px;">
            <thead><tr><?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?></tr></thead>
            <tbody>
                <?php if (empty($inicio)): ?>
                    <tr><td colspan="<?= count($headers) ?>" style="text-align:center;">Sin datos</td></tr>
                <?php else: foreach ($inicio as $ini): ?>
                    <tr style="background:#f0f8ff;">
                        <td><b>Saldo inicial</b></td><td></td>
                        <td></td><td></td><td></td>
                        <td></td><td></td><td></td>
                        <td><?= e($ini[1] ?? '') ?></td>
                        <td><?= e($ini[2] ?? '') ?></td>
                        <td><?= e($ini[3] ?? '') ?></td>
                        <td></td>
                    </tr>
                    <?php foreach ($kardex as $row): ?>
                        <?php if (($row[0] ?? null) == ($ini[0] ?? null)): ?>
                            <tr>
                                <td><?= e($row[1] ?? '') ?></td>
                                <td><?= e($row[2] ?? '') ?></td>
                                <td><?= e($row[3] ?? '') ?></td>
                                <td><?= e($row[4] ?? '') ?></td>
                                <td><?= e($row[5] ?? '') ?></td>
                                <td><?= e($row[6] ?? '') ?></td>
                                <td><?= e($row[7] ?? '') ?></td>
                                <td><?= e($row[8] ?? '') ?></td>
                                <td><?= e($row[9] ?? '') ?></td>
                                <td><?= e($row[10] ?? '') ?></td>
                                <td><?= e($row[11] ?? '') ?></td>
                                <td><?= e($row[12] ?? '') ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
