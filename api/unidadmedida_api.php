<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLUnidadMedida.php'; require_once __DIR__ . '/../BE/BEUnidadMedida.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'ConsultarUnidadMedida':
        // SP devuelve: id(0), ccod_unidadmedida(1), csim(2), cdsc(3), cstatus(4='Activo'/'Inactivo'), ccod_tributario(5)
        $bl = new BLUnidadMedida(); $rows = $bl->consultarUnidadMedida($o);
        $lst = array(); foreach ($rows as $f) {
            $code = strval($f[1] ?? '');
            // Devolvemos las llaves que el JS (UnidadMedida1.js) espera:
            //   ccod_unidadmedida / cdsc_unidadmedida / cstatus / item
            $lst[] = array(
                'item'              => "<input id='".$code."' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'ccod_unidadmedida' => $code,
                'cdsc_unidadmedida' => strval($f[3] ?? ''),
                'csim_unidadmedida' => strval($f[2] ?? ''),
                'ccod_tributario'   => strval($f[5] ?? ''),
                'cstatus'           => strval($f[4] ?? ''),
                // alias para compatibilidad
                'ccod_umd'          => $code,
                'cdsc_umd'          => strval($f[3] ?? ''),
                'estado'            => strval($f[4] ?? ''),
            );
        } jsonResponse(array('d' => $lst)); break;
    case 'ConsultarCodigoUnidadMedida':
        // webDatpos_consultarCodigoUnidadMedida @ccod_cia, @ccod_unidadmedida → mismas 6 columnas
        $input = getJsonInput(); $bl = new BLUnidadMedida(); $rows = $bl->consultarCodigoUnidadMedida($input['codigo'] ?? '', $o);
        $lst = array(); foreach ($rows as $f) {
            $cs = strval($f[4] ?? '');
            $cstatusInt = ($cs === 'Activo' || $cs === 'A' || $cs === '1') ? 1 : 0;
            $lst[] = array(
                'ccod_unidadmedida' => strval($f[1] ?? ''),
                'cdsc_unidadmedida' => strval($f[3] ?? ''),
                'csim_unidadmedida' => strval($f[2] ?? ''),
                'ccod_tributario'   => strval($f[5] ?? ''),
                'cstatus'           => $cstatusInt,
                // alias
                'ccod_umd'          => strval($f[1] ?? ''),
                'cdsc_umd'          => strval($f[3] ?? ''),
            );
        } jsonResponse(array('d' => $lst)); break;
    case 'Guardar':
        $input = getJsonInput(); $data = $input['UnidadMedida'][0] ?? array(); $op = $input['operacion'] ?? '';
        $cs = strval($data['cstatus'] ?? '1');
        $objBE = new BEUnidadMedida();
        // El JS suele enviar ccod_umd / cdsc_umd; la BE/DA esperan ccod_unidadmedida / cdsc_unidadmedida
        $objBE->ccod_unidadmedida = $data['ccod_unidadmedida'] ?? $data['ccod_umd'] ?? '';
        $objBE->cdsc_unidadmedida = $data['cdsc_unidadmedida'] ?? $data['cdsc_umd'] ?? '';
        $objBE->csim_unidadmedida = $data['csim_unidadmedida'] ?? $data['csim_umd'] ?? '';
        $objBE->ccod_tributario   = $data['ccod_tributario'] ?? '';
        $objBE->cstatus = ($cs === '1' || $cs === 'A') ? 'A' : 'I';
        $bl = new BLUnidadMedida();
        $rows = ($op === 'nuevo') ? $bl->insertarUnidadMedida($objBE, $o) : $bl->editarUnidadMedida($objBE, $o);
        // El JS lee response.d[0].ccod_unidadmedida / cdsc_unidadmedida → devolvemos AMBAS llaves.
        $lst = array();
        foreach ($rows as $f) {
            $code = strval($f[0] ?? '');
            $msg  = strval($f[1] ?? '');
            $lst[] = array(
                'ccod_unidadmedida' => $code,
                'cdsc_unidadmedida' => $msg,
                'ccod_umd'          => $code,
                'cdsc_umd'          => $msg,
            );
        }
        if (empty($lst)) $lst[] = array('ccod_unidadmedida' => 'OK', 'cdsc_unidadmedida' => '', 'ccod_umd' => 'OK', 'cdsc_umd' => '');
        jsonResponse(array('d' => $lst)); break;
    case 'Eliminar':
        $input = getJsonInput(); $bl = new BLUnidadMedida(); $rows = $bl->eliminarUnidadMedida($input['codigo'] ?? $input['unidadmedida'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $code = strval($f[0] ?? '');
            $msg  = strval($f[1] ?? '');
            $lst[] = array(
                'ccod_unidadmedida' => $code,
                'cdsc_unidadmedida' => $msg,
                'ccod_umd'          => $code,
                'cdsc_umd'          => $msg,
            );
        }
        if (empty($lst)) $lst[] = array('ccod_unidadmedida' => 'OK', 'cdsc_unidadmedida' => '', 'ccod_umd' => 'OK', 'cdsc_umd' => '');
        jsonResponse(array('d' => $lst)); break;
    case 'DatosGenerales':
        $rows = Database::selectStoredTenant('webDatpos_datosGenerales', array('@ccod_cia' => $o->ccod_empresa, '@ccod_usuario' => $o->ccod_usuario), $o);
        $lst = array(); if (count($rows) > 0) { $f = $rows[0];
            $lst[] = array('cdsc_tienda' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''),
                'cdsc_caja' => strval($f[2] ?? ''), 'nlista_pre_normal' => strval($f[3] ?? ''),
                'cdsc_listpreNorm' => strval($f[4] ?? ''), 'nlista_pre_preferencial' => strval($f[5] ?? ''),
                'cdsc_listprePref' => strval($f[6] ?? ''));
        } jsonResponse(array('d' => $lst)); break;
    default: jsonResponse(array('d' => array()));
}
?>
