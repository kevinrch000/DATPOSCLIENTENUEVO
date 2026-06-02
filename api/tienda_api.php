<?php
/**
 * DatPOS - API: Tiendas (Administración)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

switch ($method) {
case 'ConsultarTiendas':
        // SP webDatpos_consultarTiendas devuelve:
        //   [0]id_tienda, [1]ccod_tiend, [2]cnombr, [3]cdirec, [4]cmail, [5]ctelef, [6]cstatus
        // El JS Tienda.js (CargarTabla) espera columnas: item, ccod_tiend, cnombr, cdirec, ctelef, cmail, estado
        $rows = Database::selectStoredTenant('webDatpos_consultarTiendas', array('@ccod_empresa' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $cod = strval($f[1] ?? '');
            // Filtrar filas vacias / sin codigo
            if ($cod === '') continue;
            $lst[] = array(
                'item'       => "<input id='" . htmlspecialchars($cod, ENT_QUOTES) . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'ccod_tiend' => $cod,
                'cnombr'     => strval($f[2] ?? ''),
                'cdirec'     => strval($f[3] ?? ''),
                'cmail'      => strval($f[4] ?? ''),
                'ctelef'     => strval($f[5] ?? ''),
                'estado'     => (strval($f[6] ?? '') === 'A') ? 'Activo' : 'Inactivo',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarTienda':
        $input = getJsonInput();
        // sp_consultartienda => columnas:
        //   [0]id_tienda, [1]ccod_cia, [2]ccod_tiend, [3]cnombr, [4]cdirec, [5]cmail,
        //   [6]ctelef, [7]cpassw, [8]cstatus, [9]nlista_pre_normal, [10]nlista_pre_preferencial,
        //   [11]cdepartamento, [12]cprovincia, [13]cdistrito, [14]cubigeo, [15]curba_tienda,
        //   [16]ccod_loc_emis, [17]ccod_usuario, [18]dfch_crea, [19]almacenes, [20]cajas
        // El JS Tienda.js usa: ccod_tiend, cnombr, cdirec, ctelef, cmail, cstatus,
        //   cdepartamento, cprovincia, cdistrito, cubigeo, nlista_pre_normal, nlista_pre_preferencial
        $rows = Database::selectStoredTenant('sp_consultartienda', array('@ccod_empresa' => $objUsuario->ccod_empresa, '@ccod_tiend' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_tiend'              => strval($f[2] ?? ''),
                'cnombr'                  => strval($f[3] ?? ''),
                'cdirec'                  => strval($f[4] ?? ''),
                'cmail'                   => strval($f[5] ?? ''),
                'ctelef'                  => strval($f[6] ?? ''),
                'cpassw'                  => strval($f[7] ?? ''),
                'cstatus'                 => strval($f[8] ?? ''),
                'nlista_pre_normal'       => strval($f[9] ?? '0'),
                'nlista_pre_preferencial' => strval($f[10] ?? '0'),
                'cdepartamento'           => strval($f[11] ?? ''),
                'cprovincia'              => strval($f[12] ?? ''),
                'cdistrito'               => strval($f[13] ?? ''),
                'cubigeo'                 => strval($f[14] ?? ''),
                'curba_tienda'            => strval($f[15] ?? ''),
                'ccod_loc_emis'           => strval($f[16] ?? ''),
                // ccod_clibol/cnom_clibol pertenecen a ConfigGeneral, no a Tiendas. Se devuelven
                // vacios para evitar 'undefined' en el JS (CompletarCampos los lee).
                'ccod_clibol'             => '',
                'cnom_clibol'             => '',
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        // Alias de GuardarAjax. El JS Tienda.js usa GuardarAjax como nombre estandar.
        // Mantenido por compatibilidad si algun JS antiguo llama 'Guardar'.
        // Se delega al mismo case 'GuardarAjax' mas abajo via fall-through.
        // Para evitar duplicar logica, pasamos el control:
        $_GET['method'] = 'GuardarAjax';
        // re-include usando require? Mejor: reusamos el codigo abajo. Hacemos goto via include.
        // PHP no tiene goto seguro entre cases sin break, asi que hacemos copia minima:
        $input = getJsonInput();
        $data = $input['tienda'][0] ?? array();
        $op = $input['operacion'] ?? '';
        $sp = ($op === 'nuevo') ? 'sp_insertartienda' : 'sp_editartienda';
        // El JS envia: cnombr, cdirec, ctelef, cmail, cpassw, cstatus, etc.
        Database::executeStoredTenant($sp, array(
            '@ccod_empresa'           => $objUsuario->ccod_empresa,
            '@ccod_tiend'             => $data['ccod_tiend'] ?? ($data['ccod_tienda'] ?? ''),
            '@cnombr'                 => $data['cnombr'] ?? ($data['cdsc_tienda'] ?? ''),
            '@cdirec'                 => $data['cdirec'] ?? ($data['cdirc_tienda'] ?? ''),
            '@cmail'                  => $data['cmail'] ?? '',
            '@ctelef'                 => $data['ctelef'] ?? ($data['ctelf_tienda'] ?? ''),
            '@cpassw'                 => $data['cpassw'] ?? '',
            '@cstatus'                => $data['cstatus'] ?? 'A',
            '@cdepartamento'          => $data['cdepartamento'] ?? '',
            '@cprovincia'             => $data['cprovincia'] ?? '',
            '@cdistrito'              => $data['cdistrito'] ?? '',
            '@cubigeo'                => $data['cubigeo'] ?? '',
            '@curba_tienda'           => $data['curba_tienda'] ?? '',
            '@ccod_loc_emis'          => $data['ccod_loc_emis'] ?? '',
            '@nlista_pre_normal'      => intval($data['nlista_pre_normal'] ?? 0),
            '@nlista_pre_preferencial'=> intval($data['nlista_pre_preferencial'] ?? 0),
            '@ccod_usuario'           => $objUsuario->ccod_usuario,
        ), $objUsuario);
        jsonResponse(array('d' => array(array(
            'ccod_tiend' => $data['ccod_tiend'] ?? ($data['ccod_tienda'] ?? ''),
            'cnombr'     => $data['cnombr'] ?? ($data['cdsc_tienda'] ?? '')
        ))));
        break;

    case 'Eliminar':
        $input = getJsonInput();
        Database::executeStoredTenant('sp_eliminartienda', array(
            '@ccod_empresa' => $objUsuario->ccod_empresa,
            '@ccod_tiend'   => $input['tienda'] ?? ''
        ), $objUsuario);
        jsonResponse(array('d' => array(array('ccod_tienda' => $input['tienda'] ?? ''))));
        break;

    case 'ConsultarCargarAlmacenesDisponibles':
        $rows = Database::selectStoredTenant('sp_consultaalmacenesdispo', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarTiendaAlmacenes':
        $input = getJsonInput();
        // SP en BD usa @ccod_empresa (no @ccod_cia)
        $rows = Database::selectStoredTenant('sp_consultartiendaalmacenes', array('@ccod_empresa' => $objUsuario->ccod_empresa, '@ccod_tiend' => $input['tienda'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''), 'cbx' => strval($f[2] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarTiendaCajas':
        $input = getJsonInput();
        // SP en BD usa @ccod_empresa (no @ccod_cia)
        $rows = Database::selectStoredTenant('sp_consultartiendaCajas', array('@ccod_empresa' => $objUsuario->ccod_empresa, '@ccod_tiend' => $input['tienda'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('ccod_caja' => strval($f[0] ?? ''), 'cdsc_caja' => strval($f[1] ?? ''), 'cbx' => strval($f[2] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCargarCajasDisponibles':
        $rows = Database::selectStoredTenant('sp_consultacajasdispo', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('ccod_caja' => strval($f[0] ?? ''), 'cdsc_caja' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarCliente':
        $rows = Database::selectStoredTenant('sp_cargarcliente', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) { $lst[] = array('cbx' => '', 'id_coa' => strval($f[0] ?? ''), 'ccod_coa' => strval($f[0] ?? ''), 'cdsc_coa' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst));
        break;

    case 'GuardarAjax':
        // Tienda.js Guardar() envia: { tienda:[{...}], operacion, almacen:[{ccod_alm}], caja:[{ccod_caja}] }
        // Formato de respuesta esperado por el JS (compatibilidad con DATienda.vb):
        //   d = [bool, "OK"|"2627"|"LIMITETIENDA"|errCode, mensajeError, ""]
        $input = getJsonInput();
        $data = $input['tienda'][0] ?? array();
        $op = $input['operacion'] ?? '';
        $almacenes = $input['almacen'] ?? array();
        $cajas = $input['caja'] ?? array();
        $codTiend = $data['ccod_tiend'] ?? ($data['ccod_tienda'] ?? '');

        try {
            // 1. Insertar / Editar Tienda
            //    sp_insertartienda / sp_editartienda esperan tambien @cpassw y
            //    @cstatus (antes faltaban; por eso Contrasena Mail / Estado nunca
            //    se persistian).
            $sp = ($op === 'nuevo') ? 'sp_insertartienda' : 'sp_editartienda';
            Database::executeStoredTenant($sp, array(
                '@ccod_empresa'           => $objUsuario->ccod_empresa,
                '@ccod_tiend'             => $codTiend,
                '@cnombr'                 => $data['cnombr'] ?? ($data['cdsc_tienda'] ?? ''),
                '@cdirec'                 => $data['cdirec'] ?? ($data['cdirc_tienda'] ?? ''),
                '@cmail'                  => $data['cmail'] ?? '',
                '@ctelef'                 => $data['ctelef'] ?? ($data['ctelf_tienda'] ?? ''),
                '@cpassw'                 => $data['cpassw'] ?? '',
                '@cstatus'                => $data['cstatus'] ?? 'A',
                '@cdepartamento'          => $data['cdepartamento'] ?? '',
                '@cprovincia'             => $data['cprovincia'] ?? '',
                '@cdistrito'              => $data['cdistrito'] ?? '',
                '@cubigeo'                => $data['cubigeo'] ?? '',
                '@curba_tienda'           => $data['curba_tienda'] ?? '',
                '@ccod_loc_emis'          => $data['ccod_loc_emis'] ?? '',
                '@nlista_pre_normal'      => intval($data['nlista_pre_normal'] ?? 0),
                '@nlista_pre_preferencial'=> intval($data['nlista_pre_preferencial'] ?? 0),
                '@ccod_usuario'           => $objUsuario->ccod_usuario,
            ), $objUsuario);

            // 2. Reasignar almacenes
            //    SPs reales: sp_limpiartiendasalmacen / sp_asignartiendaalmacen
            //    (los nombres sp_limpiarasignaciontiendaalmacen NO existen en BD).
            //    sp_asignartiendaalmacen acepta solo 3 params (sin @ccod_usuario).
            if ($codTiend !== '') {
                try {
                    Database::executeStoredTenant('sp_limpiartiendasalmacen', array(
                        '@ccod_empresa' => $objUsuario->ccod_empresa,
                        '@ccod_tiend'   => $codTiend
                    ), $objUsuario);
                } catch (Exception $e) { /* SP opcional */ }
                foreach ($almacenes as $alm) {
                    $codAlm = $alm['ccod_alm'] ?? '';
                    if ($codAlm === '') continue;
                    try {
                        Database::executeStoredTenant('sp_asignartiendaalmacen', array(
                            '@ccod_empresa' => $objUsuario->ccod_empresa,
                            '@ccod_tiend'   => $codTiend,
                            '@ccod_alm'     => $codAlm
                        ), $objUsuario);
                    } catch (Exception $e) { /* duplicado u otro error no critico */ }
                }

                // 3. Reasignar cajas
                //    SPs reales: sp_limpiartiendascaja / sp_asignartiendacaja
                //    Ambos esperan @ccod_empresa (no @ccod_cia).
                try {
                    Database::executeStoredTenant('sp_limpiartiendascaja', array(
                        '@ccod_empresa' => $objUsuario->ccod_empresa,
                        '@ccod_tiend'   => $codTiend
                    ), $objUsuario);
                } catch (Exception $e) { /* SP opcional */ }
                foreach ($cajas as $caja) {
                    $codCaja = $caja['ccod_caja'] ?? '';
                    if ($codCaja === '') continue;
                    try {
                        Database::executeStoredTenant('sp_asignartiendacaja', array(
                            '@ccod_empresa' => $objUsuario->ccod_empresa,
                            '@ccod_tiend'   => $codTiend,
                            '@ccod_caja'    => $codCaja,
                            '@ccod_usuario' => $objUsuario->ccod_usuario
                        ), $objUsuario);
                    } catch (Exception $e) { /* duplicado u otro error no critico */ }
                }
            }

            // Exito: formato esperado por JS [true, "OK", "", ""]
            jsonResponse(array('d' => array(true, 'OK', '', '')));
        } catch (Exception $e) {
            $msg = $e->getMessage();
            // Detectar duplicate key (codigo 2627 en SQL Server)
            $errCode = (strpos($msg, '2627') !== false) ? '2627' : 'ERROR';
            jsonResponse(array('d' => array(false, $errCode, $msg, '')));
        }
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
