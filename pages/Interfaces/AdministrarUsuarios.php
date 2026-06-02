<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Administrar Usuarios | DATPOS'; $pageScript = 'Usuario.js'; $showCrudButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/>
<div class="c-content-center modern-page">
<ul class="nav nav-tabs"><li class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a></li>
<li><a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a></li></ul>
<div class="tab-content">
    <div id="Datos" class="tab-pane in active" style="padding:13px;"><h4 style="border-bottom:groove;margin-bottom:30px;margin-top:30px;width:60%;">Administrar Usuarios (Super Admin)</h4></div>
    <div id="Lista" class="tab-pane tabcito" style="padding:13px;"><table id="table_id" class="display" style="width:100%;"><thead><tr><th>Código</th><th>Usuario</th><th>Nombre</th><th>Estado</th></tr></thead><tbody></tbody></table></div>
</div></div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
