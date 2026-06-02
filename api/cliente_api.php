<?php
/**
 * DatPOS - API: Clientes (Ventas)
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/dni_lookup.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

switch ($method) {
    case 'ConsultarClientes':
        $rows = Database::selectStoredTenant('sp_consultaclientes', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'item' => "<input id='" . strval($f[2]) . "' type='checkbox' class='limpiar_checked' onclick='checked_click(this)'>",
                'id_coa' => strval($f[0] ?? ''), 'ccod_cia' => strval($f[1] ?? ''), 'ccod_coa' => strval($f[2] ?? ''),
                'cdoc_coa' => strval($f[3] ?? ''), 'cdsc_coa' => strval($f[4] ?? ''), 'ctelf' => strval($f[5] ?? ''),
                'cmail' => strval($f[6] ?? ''), 'destipo_coa' => strval($f[7] ?? ''),
                'cdirc_coa' => ($f[8] !== null) ? strval($f[8]) : '', 'cdistrito' => ($f[9] !== null) ? strval($f[9]) : '',
                'cprovincia' => ($f[10] !== null) ? strval($f[10]) : '', 'cdepartamento' => ($f[11] !== null) ? strval($f[11]) : '',
                'cpais' => ($f[12] !== null) ? strval($f[12]) : '', 'estado' => strval($f[13] ?? ''),
                'cproveedor' => strval($f[14] ?? ''), 'ctip_doc' => strval($f[15] ?? ''), 'cruc_coa' => strval($f[16] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarCliente':
        // webDatpos_ConsultaCliente hace `SELECT * FROM Coa`, asi que los
        // indices siguen el orden FISICO de la tabla Coa (NO el orden
        // proyectado por sp_consultaclientes). Mapeo correcto:
        //  [0]  id_coa        [1]  ccod_cia      [2]  ccod_coa
        //  [3]  cdoc_coa      [4]  cdsc_coa      [5]  ctelf
        //  [6]  cmail         [7]  ctipo_coa     [8]  cpais
        //  [9]  cdepartamento [10] cprovincia    [11] cdistrito
        //  [12] cdirc_coa     [13] cstatus       [14] cproveedor
        //  [15] cruc_coa      [16] ccod_usuario  [17] dfch_crea
        // El bug anterior usaba la proyeccion de sp_consultaclientes, por lo
        // que CompletarCampos recibia distrito en cdepartamento, ruc en
        // cstatus, ccod_usuario en cproveedor, etc. -> los dropdowns de Estado,
        // Asociado, Departamento, Provincia, Distrito quedaban sin seleccion.
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_ConsultaCliente', array('@ccod_cia' => $objUsuario->ccod_empresa, '@codigo' => $input['codigo'] ?? ''), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_coa'        => strval($f[0]  ?? ''),
                'ccod_cia'      => strval($f[1]  ?? ''),
                'ccod_coa'      => strval($f[2]  ?? ''),
                'cdoc_coa'      => strval($f[3]  ?? ''),
                'cdsc_coa'      => strval($f[4]  ?? ''),
                'ctelf'         => strval($f[5]  ?? ''),
                'cmail'         => strval($f[6]  ?? ''),
                'ctipo_coa'     => strval($f[7]  ?? ''),
                'destipo_coa'   => strval($f[7]  ?? ''),
                'cpais'         => ($f[8]  !== null) ? strval($f[8])  : '',
                'cdepartamento' => ($f[9]  !== null) ? strval($f[9])  : '',
                'cprovincia'    => ($f[10] !== null) ? strval($f[10]) : '',
                'cdistrito'     => ($f[11] !== null) ? strval($f[11]) : '',
                'cdirc_coa'     => ($f[12] !== null) ? strval($f[12]) : '',
                'cstatus'       => strval($f[13] ?? ''),
                'estado'        => strval($f[13] ?? ''),
                'cproveedor'    => strval($f[14] ?? ''),
                'cruc_coa'      => strval($f[15] ?? ''),
                'ctip_doc'      => strval($f[3]  ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarDni':
        $input = getJsonInput();
        $dni = trim(strval($input['dni'] ?? $input['documento'] ?? ''));
        jsonResponse(array('d' => lookupDni($dni)));
        break;

    case 'Guardar':
        $input = getJsonInput();
        $data = $input['cliente'][0] ?? array();
        $op = $input['operacion'] ?? '';
        $sp = ($op === 'nuevo') ? 'webDatpos_insertarclientes' : 'webDatpos_editarclientes';
        $cs = strval($data['cstatus'] ?? '');
        Database::executeStoredTenant($sp, array(
            '@ccod_cia'      => $objUsuario->ccod_empresa,
            '@ccod_coa'      => $data['ccod_coa'] ?? '',
            '@cdoc_coa'      => $data['cdoc_coa'] ?? '',
            '@cdsc_coa'      => $data['cdsc_coa'] ?? '',
            '@ctelf'         => $data['ctelf'] ?? '',
            '@cmail'         => $data['cmail'] ?? '',
            '@ctipo_coa'     => $data['ctipo_coa'] ?? '',
            '@cpais'         => $data['cpais'] ?? '',
            '@cdepartamento' => $data['cdepartamento'] ?? '',
            '@cprovincia'    => $data['cprovincia'] ?? '',
            '@cdistrito'     => $data['cdistrito'] ?? '',
            '@cdirc_coa'     => $data['cdirc_coa'] ?? '',
            '@cstatus'       => ($cs === '1' || $cs === 'A') ? 'A' : 'I',
            '@cproveedor'    => $data['cproveedor'] ?? '',
            '@ccod_usuario'  => $objUsuario->ccod_usuario,
            '@ctip_doc'      => $data['ctip_doc'] ?? '',
            '@cruc_coa'      => $data['cruc_coa'] ?? '',
        ), $objUsuario);
        jsonResponse(array('d' => array(array('ccod_coa' => $data['ccod_coa'] ?? ''))));
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $resp = Database::executeStoredTenant('sp_eliminarcliente', array('@ccod_coa' => $input['cliente'] ?? '', '@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        jsonResponse(array('d' => $resp));
        break;

    case 'EnsureCoaByRuc':
        // Upsert idempotente de un registro Coa identificado por RUC.
        // El convenio es Coa.ccod_coa = Coa.cruc_coa = RUC SUNAT.
        // Esto evita que la FK FK_CbGuia_Coa (y FK_CbFactura_Coa) falle al
        // guardar una Guia de Remision o Factura usando un RUC todavia no
        // registrado en la tabla Coa.
        $input = getJsonInput();
        $ruc   = trim(strval($input['ruc'] ?? ''));
        if ($ruc === '') {
            jsonResponse(array('d' => array('ccod_coa' => '')));
            break;
        }
        $razon = trim(strval($input['razon_social'] ?? ''));
        $direc = trim(strval($input['direccion'] ?? ''));
        $ubi   = trim(strval($input['ubigeo'] ?? ''));
        $prov  = strval($input['cproveedor'] ?? '2');
        $tipo  = isset($input['ctipo_coa']) ? strval($input['ctipo_coa']) : '';

        Database::executeStoredTenant('webDatpos_EnsureCoaByRuc', array(
            '@ccod_cia'     => $objUsuario->ccod_empresa,
            '@ruc'          => $ruc,
            '@razon_social' => $razon,
            '@direccion'    => $direc,
            '@ubigeo'       => $ubi,
            '@ccod_usuario' => $objUsuario->ccod_usuario,
            '@cproveedor'   => ($prov === '') ? '2' : $prov,
            '@ctipo_coa'    => ($tipo === '') ? null : $tipo,
        ), $objUsuario);
        jsonResponse(array('d' => array('ccod_coa' => $ruc)));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>
