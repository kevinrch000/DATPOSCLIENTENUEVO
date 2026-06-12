<?php
require_once __DIR__ . '/../includes/auth.php'; require_once __DIR__ . '/../includes/helpers.php'; require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLUsuario.php'; require_once __DIR__ . '/../BE/BEUsuario.php';
require_once __DIR__ . '/../includes/password_helper.php';
if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario']; $m = $_GET['method'] ?? '';
switch ($m) {
    case 'ConsultarUsuarios':
    $bl = new BLUsuario();
    $rows = $bl->consultarUsuarios($o);
    $lst = array();
    foreach ($rows as $f) {
        $obj = new BEUsuario();
        $obj->item = "";
        $obj->ccod_usuario = strval($f[0] ?? '');
        $obj->cdsc_usuario = strval($f[1] ?? '');
        $obj->cdirec = strval($f[2] ?? '');
        $obj->cdsc_rol = strval($f[3] ?? '');
        $obj->estado = intval($f[4] ?? 0);
        $obj->ccod_tiend = strval($f[5] ?? '');
        $lst[] = $obj;
    }jsonResponse(array('d' => $lst)); break;
    case 'ConsultarUsuario':
        $input = getJsonInput(); $bl = new BLUsuario(); $rows = $bl->consultarUsuarioDetalle($input['codigo'] ?? '', $o);
if (!empty($rows)) {
    error_log(print_r($rows[0], true)); // Ver estructura real del primer row
}
        $lst = array(); foreach ($rows as $f) {
            $obj = new BEUsuario(); $obj->ccod_usuario = strval($f[0] ?? '');
             $obj->cdsc_usuario = strval($f[1] ?? '');
            $obj->cdirec = strval($f[2] ?? '');
             $obj->cdsc_rol = strval($f[3] ?? '');
            $obj->estado = intval($f[4] ?? 0);
             $obj->ccod_tiend = strval($f[5] ?? '');
            $obj->ccod_almacen = strval($f[6] ?? '');
             $obj->ccod_caja = strval($f[7] ?? '');
            $obj->cpassw = strval($f[8] ?? '');
             $obj->id_rol = intval($f[9] ?? 0);
              $lst[] = $obj;
        } jsonResponse(array('d' => $lst)); break;
    case 'ConsultarAlmEmpActivos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultaalmempactivos', array('@ccod_tiend' => $input['tienda'] ?? '', '@ccod_cia' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'ConsultarCajasEmpActivos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('sp_consultacajasempactivos', array('@ccod_empresa' => $o->ccod_empresa), $o);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_caja' => strval($f[0] ?? ''), 'cdsc_caja' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;
    case 'Guardar':
        $input = getJsonInput(); $data = $input['usuario'][0] ?? array(); $op = $input['operacion'] ?? '';
        $cs = strval($data['cstatus'] ?? $data['id_estado'] ?? $data['estado'] ?? '1');
        // El JS (Usuario.js) envia el id del rol bajo la clave 'cdsc_rol'
        // (el value del combo dl_rol ES el id_rol). Sin este fallback el rol
        // se guardaba como 0 y el empleado terminaba con acceso total.
        $rolId = intval($data['id_rol'] ?? $data['cdsc_rol'] ?? 0);

        // Hashear contrasena: guardar MD5 (compat legacy) + bcrypt (seguro)
        $plainPass = $data['cpassw'] ?? '';
        $md5Pass   = !empty($plainPass) ? md5($plainPass) : '';
        $bcryptPass = !empty($plainPass) ? PasswordHelper::hash($plainPass) : null;

        $bl = new BLUsuario();
        if ($op === 'nuevo') {
            // Evitar duplicados: el JS espera 'UsuRep' si el codigo ya existe.
            $existe = $bl->consultarUsuarioDetalle($data['ccod_usuario'] ?? '', $o);
            if (count($existe) > 0) {
                jsonResponse(array('d' => 'UsuRep'));
            }
            $ok = Database::executeStoredTenant('sp_insertarusuarios', array(
                '@ccod_empresa'     => $o->ccod_empresa,
                '@ccod_usuario'     => $data['ccod_usuario'] ?? '',
                '@cdsc_usuario'     => $data['cdsc_usuario'] ?? '',
                '@cpassw'           => $md5Pass,
                '@cdirec'    => $data['cdirec'] ?? '', 
                '@cmail'            => $data['cmail'] ?? '',
                '@ctelf'            => $data['ctelf'] ?? '',
                '@ccelular'         => $data['ccelular'] ?? '',
                '@id_rol'           => $rolId,
                '@ccod_tiend'       => $data['ccod_tiend'] ?? '',
                '@ccod_almacen'     => $data['ccod_almacen'] ?? '',
                '@ccod_caja'        => $data['ccod_caja'] ?? '',
                '@cperm_descn'      => $data['cperm_descn'] ?? '',
                '@ccod_usuariocrea' => $o->ccod_usuario,
                '@cpassw_bcrypt'    => $bcryptPass,
            ), $o);
        } else {
            $ok = Database::executeStoredTenant('webDatpos_editarUsuario', array(
                '@ccod_cia'      => $o->ccod_empresa,
                '@usu_crea'      => $o->ccod_usuario,
                '@ccod_usuario'  => $data['ccod_usuario'] ?? '',
                '@cdirc_usuario' => $data['cdirec'] ?? $data['cdirc_usuario'] ?? '',
                '@cdsc_usuario'  => $data['cdsc_usuario'] ?? '',
                '@cpassw'        => $md5Pass,
                '@rol'           => $rolId,
                '@cstatus'       => ($cs === '1' || $cs === 'A') ? 'A' : 'I',
                '@cmail'         => $data['cmail'] ?? '',
                '@ctelf'         => $data['ctelf'] ?? '',
                '@ccelular'      => $data['ccelular'] ?? '',
                '@ErrorNumber'   => '',
                '@ErrorMessage'  => '',
                '@cpassw_bcrypt' => $bcryptPass,
            ), $o);
        }
        // El JS (Usuario.js :: Guardar) espera 'OK' / 'FALLIDO' / 'UsuRep'.
        jsonResponse(array('d' => $ok ? 'OK' : 'FALLIDO')); break;
    case 'Eliminar':
        $input = getJsonInput();
        // El JS (Usuario.js) espera response.d booleano (true/false).
        $ok = Database::executeStoredTenant('webDatpos_eliminarUsuario', array('@ccod_usuario' => $input['usuario'] ?? ''), $o);
        jsonResponse(array('d' => (bool)$ok)); break;
    default: jsonResponse(array('d' => array()));
}
?>
