<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../config/database.php';
requireAuth();

$o = getUsuarioSesion();
$pageTitle = 'Informe Turno | DATPOS';
$pageScript = '';
$showCrudButtons = false; $showConsultButtons = true;
$filtros = $_SESSION['objReporteTurno'] ?? array();
$rows = array();

if (!empty($filtros)) {
    $rows = Database::selectStoredTenant('webDatpos_ReporteTurno', array(
        '@ccod_cia' => $o->ccod_empresa,
        '@ccod_tienda' => $filtros['ccod_tienda'] ?? '',
        '@id_usuario' => $filtros['ccod_usuario'] ?? '',
        '@fchDesde' => fechaToISO($filtros['dfecha_ini'] ?? ''),
        '@fchHasta' => fechaToISO($filtros['dfecha_fin'] ?? '')
    ), $o);
}

$headers = array('Usuario', 'Nombre', 'Caja', 'Monto ini.', 'Entregado', 'Diferencia', 'Monto fin.', 'Fecha inicio', 'Fecha fin', 'Dirección tienda', 'Tienda');
ob_start();
?>
<div class="c-content-center modern-page" style="padding:20px;background:#fff;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:1px solid #ddd;padding-bottom:12px;margin-bottom:15px;">
        <div>
            <h3 style="margin:0;color:#046bb4;">Reporte de Turno</h3>
            <div><?= e($o->cdescripcion ?? '') ?></div>
            <div>RUC: <?= e($o->cnum_tribu ?? '') ?></div>
        </div>
        <div style="display:flex; gap:8px;">
            <button type="button" class="btn btn-default" onclick="window.history.back();"><i class="fa fa-arrow-left"></i> Volver</button>
            <button type="button" class="btn btn-primary" onclick="window.print();">Imprimir</button>
        </div>
    </div>

    <?php if (empty($filtros)): ?>
        <div style="padding:30px;text-align:center;color:#666;">Seleccione parámetros desde Reporte de Turno y ejecute la consulta.</div>
    <?php else: ?>
        <div class="row" style="margin-bottom:15px;">
            <div class="col-sm-3"><b>Tienda:</b> <?= e($filtros['cdsc_tienda'] ?? $filtros['ccod_tienda'] ?? '') ?></div>
            <div class="col-sm-3"><b>Usuario:</b> <?= e($filtros['ccod_usuario'] ?? 'Todos') ?></div>
            <div class="col-sm-3"><b>Desde:</b> <?= e($filtros['dfecha_ini'] ?? '') ?></div>
            <div class="col-sm-3"><b>Hasta:</b> <?= e($filtros['dfecha_fin'] ?? '') ?></div>
        </div>

        <table class="table table-bordered table-striped" style="font-size:12px;">
            <thead>
                <tr>
                    <?php foreach ($headers as $h): ?><th><?= e($h) ?></th><?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($headers) ?>" style="text-align:center;">Sin datos</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php for ($i = 0; $i < count($headers); $i++): ?>
                                <td><?= e($row[$i] ?? '') ?></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
