<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Informe Tributario | DATPOS'; $pageScript = ''; $showCrudButtons = false;
ob_start(); ?>
<div class="c-content-center modern-page" style="padding:20px;">
    <h4 style="border-bottom:groove;margin-bottom:20px;">Informe Tributario</h4>
    <div id="reportViewer" style="padding:13px;text-align:center;color:#666;">
        <i class="fa fa-print" style="font-size:48px;color:#046bb4;"></i>
        <p style="margin-top:15px;">Seleccione los parámetros y genere el informe.</p>
    </div>
</div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
