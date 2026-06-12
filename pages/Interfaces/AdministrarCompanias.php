<?php require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/helpers.php'; requireAuth();
$pageTitle = 'Administrar Compañías | DATPOS'; $pageScript = ''; $showCrudButtons = true;
ob_start(); ?>
<input id="operacion" type="hidden"/><input id="hdd_fila" type="hidden" value="0"/><input id="hdd_numeromenus" type="hidden" value="1"/>
<div class="c-content-center modern-page">
<ul class="nav nav-tabs"><li class="active"><a data-toggle="tab" class="tabcito" href="#Datos" style="color:#228ac9;font-size:17px;">Datos</a></li>
<li><a data-toggle="tab" href="#Lista" class="tabcito" style="color:#228ac9;font-size:17px;">Lista</a></li></ul>
<div class="tab-content">
    <div id="Datos" class="tab-pane in active" style="padding:13px;">
        <h4 style="border-bottom:groove;margin-bottom:30px;margin-top:30px;width:60%;">Administrar Compañías</h4>
        <div class="row" style="margin-top:20px;">
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">Código</label><div class="col-sm-9"><input id="txtcodigo" class="disabled limpiar form-control moderno_tb" disabled/></div></div>
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">Nombre</label><div class="col-sm-9"><input id="txtnombre" class="disabled limpiar form-control moderno_tb" disabled/></div></div>
        </div>
        <div class="row" style="margin-top:20px;">
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">Tipo Doc.</label><div class="col-sm-9"><select id="ddl_td" class="disabled limpiar form-control moderno_tb" disabled></select></div></div>
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">RUC</label><div class="col-sm-9"><input id="txtRuc" class="disabled limpiar form-control moderno_tb" disabled/></div></div>
        </div>
        <div class="row" style="margin-top:20px;">
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">Servidor</label><div class="col-sm-9"><input id="tb_NombreServidor" class="disabled limpiar form-control moderno_tb" disabled/></div></div>
            <div class="col-sm-6"><label class="col-sm-3 moderno_lb">Base de Datos</label><div class="col-sm-9"><input id="txtBD" class="disabled limpiar form-control moderno_tb" disabled/></div></div>
        </div>
    </div>
    <div id="Lista" class="tab-pane tabcito" style="padding:13px;">
        <table id="table_id" class="display" style="width:100%;"><thead><tr><th>ID</th><th>Código</th><th>Nombre</th><th>Doc</th><th>RUC</th><th>Servidor</th><th>BD</th></tr></thead><tbody></tbody></table>
    </div>
</div></div>
<?php $pageContent = ob_get_clean(); require_once __DIR__ . '/../../includes/layout_master.php'; ?>
