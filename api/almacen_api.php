<?php
/**
 * DatPOS - API: Almacenes (Tablas)
 *
 * JS Almacen1.js usa:
 *   - ConsultarAlmacenes  -> [{item, ccod_alm, cdsc_alm, estado}]
 *   - ConsultarAlmacen    -> [{ccod_alm, cdsc_alm, cstatus(int 1/0), cdepartamento, cprovincia, cdistrito, cubigeo, cdirc_almac, curba_almac}]
 *   - ConsultarNumeradoresAlmacen -> [{cdoc_tipo, cdsc_numer, cdoc_serie, cdoc_nro}]
 *   - Guardar  -> response.d es ARRAY POSICIONAL: [bool, ErrorNumber, ErrorMessage, NumeradorConflicto]
 *   - Eliminar -> response.d[0].ccod_alm == 'OK' / '547', cdsc_alm = mensaje
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BL/BLAlmacen.php';
require_once __DIR__ . '/../BE/BEAlmacen.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$o = $_SESSION['objBEUsuario'];
$m = $_GET['method'] ?? '';

switch ($m) {

    case 'ConsultarAlmacenes':
        // sp_consultaalmacenes -> ccod_alm(0), cdsc_alm(1), cstatus(2='Activo'/'Inactivo')
        $bl = new BLAlmacen();
        $rows = $bl->consultarAlmacenes($o);
        $lst = array();
        foreach ($rows as $f) {
            $code = strval($f[0] ?? '');
            $lst[] = array(
                'item'     => "<input id='" . $code . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'ccod_alm' => $code,
                'cdsc_alm' => strval($f[1] ?? ''),
                'estado'   => strval($f[2] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarAlmacen':
        // sp_consultaalmacen -> ccod_alm(0), cdsc_alm(1), cstatus(2 int 1/0), cdepartamento(3),
        //                      cprovincia(4), cdistrito(5), cdirc_almac(6), curba_almac(7), cubigeo(8)
        $input = getJsonInput();
        $bl = new BLAlmacen();
        $rows = $bl->consultarAlmacen($input['codigo'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_alm'      => strval($f[0] ?? ''),
                'cdsc_alm'      => strval($f[1] ?? ''),
                'cstatus'       => intval($f[2] ?? 0),
                'cdepartamento' => strval($f[3] ?? ''),
                'cprovincia'    => strval($f[4] ?? ''),
                'cdistrito'     => strval($f[5] ?? ''),
                'cdirc_almac'   => strval($f[6] ?? ''),
                'curba_almac'   => strval($f[7] ?? ''),
                'cubigeo'       => strval($f[8] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarNumeradoresAlmacen':
        // webDatpos_consultarNumeradoresAlmacen -> cdoc_tipo(0), cdoc_serie(1), cdoc_nro(2), cdsc_numer(3)
        $input = getJsonInput();
        $rows = Database::selectStoredTenant(
            'webDatpos_consultarNumeradoresAlmacen',
            array('@ccod_cia' => $o->ccod_empresa, '@ccod_alm' => $input['almacen'] ?? ''),
            $o
        );
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdoc_tipo'  => strval($f[0] ?? ''),
                'cdoc_serie' => strval($f[1] ?? ''),
                'cdoc_nro'   => strval($f[2] ?? ''),
                'cdsc_numer' => strval($f[3] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['almacen'][0] ?? array();
        $op   = $input['operacion'] ?? '';
        $numeradores = $input['numerador'] ?? array();   // OJO: JS envía 'numerador' (singular)

        // JS envía cstatus = "1" / "0"  → SP espera "A" / "I"
        $cs = strval($data['cstatus'] ?? '1');
        $statusChar = ($cs === '1' || $cs === 'A') ? 'A' : 'I';

        $objBE = new BEAlmacen();
        $objBE->ccod_alm      = $data['ccod_alm'] ?? '';
        $objBE->cdsc_alm      = $data['cdsc_alm'] ?? '';
        $objBE->cstatus       = $statusChar;
        $objBE->cdepartamento = $data['cdepartamento'] ?? '';
        $objBE->cprovincia    = $data['cprovincia'] ?? '';
        $objBE->cdistrito     = $data['cdistrito'] ?? '';
        $objBE->cdirc_almac   = $data['cdirc_almac'] ?? '';
        $objBE->curba_almac   = $data['curba_almac'] ?? '';
        $objBE->cubigeo       = $data['cubigeo'] ?? '';

        // Convertir numeradores (arrays asociativos) a objetos para que la DA pueda usar -> sintaxis
        $numerObjs = array();
        foreach ($numeradores as $n) {
            $no = new stdClass();
            $no->cdoc_tipo  = $n['cdoc_tipo'] ?? '';
            $no->cdoc_serie = $n['cdoc_serie'] ?? '';
            $no->cdoc_nro   = $n['cdoc_nro'] ?? '';
            $no->cdsc_numer = $n['cdsc_numer'] ?? '';
            $numerObjs[] = $no;
        }

        $bl = new BLAlmacen();
        $result = ($op === 'nuevo')
            ? $bl->insertarAlmacen($objBE, $numerObjs, $o)
            : $bl->editarAlmacen($objBE, $numerObjs, $o);

        // El JS lee response.d como ARRAY posicional: [bool, ErrorNumber, ErrorMessage, NumeradorConflicto]
        jsonResponse(array('d' => $result));
        break;

    case 'Eliminar':
        // sp_eliminaralmacen retorna SELECT con ccod_alm='OK'/'547' y cdsc_alm=mensaje
        $input = getJsonInput();
        $bl = new BLAlmacen();
        $rows = $bl->eliminarAlmacen($input['almacen'] ?? '', $o);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'ccod_alm' => strval($f[0] ?? ''),
                'cdsc_alm' => strval($f[1] ?? ''),
            );
        }
        if (empty($lst)) $lst[] = array('ccod_alm' => 'OK', 'cdsc_alm' => '');
        jsonResponse(array('d' => $lst));
        break;

    case 'DatosGenerales':
        $rows = Database::selectStoredTenant('webDatpos_datosGenerales', array('@ccod_cia' => $o->ccod_empresa, '@ccod_usuario' => $o->ccod_usuario), $o);
        $lst = array();
        if (count($rows) > 0) {
            $f = $rows[0];
            $lst[] = array(
                'cdsc_tienda' => strval($f[0] ?? ''),
                'cdsc_alm'    => strval($f[1] ?? ''),
                'cdsc_caja'   => strval($f[2] ?? ''),
                'nlista_pre_normal'        => strval($f[3] ?? ''),
                'cdsc_listpreNorm'         => strval($f[4] ?? ''),
                'nlista_pre_preferencial'  => strval($f[5] ?? ''),
                'cdsc_listprePref'         => strval($f[6] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
