<?php
/**
 * DatPOS - API: Home (Dashboard)
 * Reemplaza los WebMethods de Interfaces/Home.aspx.vb:
 * - CargarRoles (menú lateral)
 * - CargarFotoUsuario
 * - DatosGenerales
 * - ActualizarTimeOut
 * - CambiarContrasena
 * - ConsultaColumnas
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/password_helper.php';
require_once __DIR__ . '/../BL/BLUsuario.php';
require_once __DIR__ . '/../BE/BEMenu.php';

// ========================================================================
// FUNCIÓN PARA NORMALIZAR FECHAS A FORMATO ESTÁNDAR SQL (YYYY-MM-DD)
// ========================================================================
function normalizarFechaSQL($fechaStr) {
    if (empty($fechaStr) || $fechaStr === 'null' || $fechaStr === 'undefined') {
        return date('Y-m-d'); // Si viene vacío, devuelve la fecha de hoy
    }
    
    // Cambiar barras por guiones para evitar confusión en strtotime
    $fechaLimpia = str_replace('/', '-', $fechaStr);
    $timestamp = strtotime($fechaLimpia);
    
    if ($timestamp) {
        return date('Y-m-d', $timestamp);
    }
    
    return $fechaStr; // Retorna original si no se pudo convertir
}
// ========================================================================

// Verificar sesión
if (!isset($_SESSION['objBEUsuario'])) {
    jsonResponse(array('d' => '-1'));
}

$objUsuario = $_SESSION['objBEUsuario'];

// Determinar el método
$method = $_GET['method'] ?? '';

switch ($method) {

    case 'CargarRoles':
        // 1. ESPÍA: Imprimir en la consola negra exactamente qué datos tiene la sesión
        error_log("====== DATPOS DEBUG MENU ======");
        error_log("CIA: [" . $objUsuario->ccod_empresa . "]");
        error_log("USU: [" . $objUsuario->ccod_usuario . "]");
        error_log("ROL: [" . $objUsuario->id_rol . "]");
        error_log("===============================");

        // 2. Ejecutar la consulta (Le ponemos un "parche salvavidas" al rol por si viene vacío)
        $idRolFiltro = !empty($objUsuario->id_rol) ? $objUsuario->id_rol : 1; 

        $rows = Database::selectStoredTenant(
            'webDatpos_cargarRol',
            array(
                '@ccod_cia'     => $objUsuario->ccod_empresa,
                '@ccod_usuario' => $objUsuario->ccod_usuario,
                '@id_rol'       => $idRolFiltro
            ),
            $objUsuario
        );

        $menus = array();
        foreach ($rows as $fila) {
            $menu = new BEMenu();
            $menu->cdsc_menu     = strval($fila[0] ?? '');
            $menu->curl_href     = strval($fila[1] ?? '');
            $menu->nid_menupadre = strval($fila[2] ?? '');
            $menu->cli_menu      = strval($fila[3] ?? '');
            $menu->cul_menu      = strval($fila[4] ?? '');
            $menu->cstatus       = strval($fila[5] ?? '');
            $menu->corden        = intval($fila[6] ?? 0);
            $menu->id_menu       = strval($fila[7] ?? '');
            $menu->curl_src      = strval($fila[8] ?? '');
            $menus[] = $menu;
        }

        jsonResponse(array('d' => $menus));
        break;

    case 'GuardarFotoUsuario':
    try {
        if (!isset($_FILES['file-input']) || !$_FILES['file-input']['tmp_name']) {
            throw new Exception("No se envió imagen");
        }

        $foto = file_get_contents($_FILES['file-input']['tmp_name']);

        Database::executeStoredTenant( 
            'webDatpos_cargarFotoUsuario',
            array(
                '@ccod_empresa' => $objUsuario->ccod_empresa,
                '@ccod_usuario' => $objUsuario->ccod_usuario,
                '@ifoto' => $foto
            ),
            $objUsuario
        );

        jsonResponse(array('d' => 'OK'));

    } catch (Exception $e) {
        jsonResponse(array('d' => $e->getMessage()));
    }
    break;    
    
    case 'CargarFotoUsuario':
    $rows = Database::selectStoredTenant(
        'webDatpos_obtenerFotoUsuario',
        array(
            '@ccod_cia'     => $objUsuario->ccod_empresa,
            '@ccod_usuario' => $objUsuario->ccod_usuario
        ),
        $objUsuario
    );

    $lst = array();
    foreach ($rows as $f) {
        $lst[] = array(
            'ifoto' => (!empty($f[0])) ? base64_encode($f[0]) : '',
            'ilogo' => (!empty($f[1])) ? base64_encode($f[1]) : ''
        );
    }

    jsonResponse(array('d' => $lst));
    break;

    case 'DatosGenerales':
        $rows = Database::selectStoredTenant(
            'webDatpos_datosGenerales',
            array(
                '@ccod_cia'     => $objUsuario->ccod_empresa,
                '@ccod_usuario' => $objUsuario->ccod_usuario
            ),
            $objUsuario
        );
        
        $result = array();
        if (count($rows) > 0) {
            $obj = new stdClass();
            $obj->cdsc_tienda             = strval($rows[0][0] ?? '');
            $obj->cdsc_alm                = strval($rows[0][1] ?? '');
            $obj->cdsc_caja               = strval($rows[0][2] ?? '');
            $obj->nlista_pre_normal       = strval($rows[0][3] ?? '');
            $obj->nlista_pre_preferencial = strval($rows[0][4] ?? '');
            $obj->cdsc_listpreNorm        = strval($rows[0][5] ?? '');
            $obj->cdsc_listprePref        = strval($rows[0][6] ?? '');
            $obj->cdescripcion            = strval($rows[0][7] ?? '');
            $result[] = $obj;
        }
        
        jsonResponse(array('d' => $result));
        break;

    case 'ActualizarTimeOut':
        $_SESSION['last_activity'] = time();
        jsonResponse(array('d' => 'OK'));
        break;

    case 'CambiarContrasena':
        $input = getJsonInput();
        $blUsuario = new BLUsuario();
        
        $oldPass = $input['CambioClave'][0]['cpassw'] ?? '';
        $newPass = $input['CambioClave'][0]['cpasswordnueva'] ?? '';

        $userRows = $blUsuario->consultarUsuario($objUsuario->ccod_usuario, $objUsuario);
        if (count($userRows) > 0) {
            $md5Stored    = strval($userRows[0][8] ?? '');  // cpassw (MD5)
            $bcryptStored = strval($userRows[0][18] ?? ''); // cpassw_bcrypt

            $check = PasswordHelper::verifyWithMigration($oldPass, $md5Stored, $bcryptStored ?: null);
            if ($check['valid']) {
                $newMd5    = md5($newPass);
                $newBcrypt = PasswordHelper::hash($newPass);

                Database::executeStoredTenant('webDatpos_cambiarContrasena_v2', array(
                    '@ccod_cia'        => $objUsuario->ccod_empresa,
                    '@ccod_usuario'    => $objUsuario->ccod_usuario,
                    '@cpassw'          => $newMd5,
                    '@cpassw_bcrypt'   => $newBcrypt,
                ), $objUsuario);

                jsonResponse(array('d' => array(array('respuesta' => 'OK'))));
            } else {
                jsonResponse(array('d' => array(array('respuesta' => 'ERROR'))));
            }
        } else {
            jsonResponse(array('d' => array(array('respuesta' => 'ERROR'))));
        }
        break;

    case 'ConsultaColumnas':
        jsonResponse(array('d' => array()));
        break;

    // === Dashboard Methods (Bashboard.js) ===
    case 'CargarProductosDelDia':
        $rows = Database::selectStoredTenant('webDatpos_CargarProductosDelDia', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'nimporte_neto' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarVendedoresDelDia':
        $rows = Database::selectStoredTenant('webDatpos_CargarVendedoresDelDia', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cusu_crea' => strval($f[0] ?? ''), 'nimporte_neto' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'DiasRestantes':
        $rows = Database::selectStoredTenant('webDatpos_DiasRestantes', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cdescripcion' => strval($f[0] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'ArticuloSinStock':
        $rows = Database::selectStoredTenant('webDatpos_ArticuloSinStock', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cdescripcion' => strval($f[0] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarDatosCajero':
        $rows = Database::selectStoredTenant('webDatpos_CargarDatosCajero', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $objUsuario->ccod_tiend,
            '@ccod_usuario' => $objUsuario->ccod_usuario, '@ccod_caja' => $objUsuario->ccod_caja
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ImporteCaja' => strval($f[0] ?? ''), 'TotVentTurn' => strval($f[1] ?? ''),
                'TotDescTurn' => strval($f[2] ?? ''), 'DocAnulado' => strval($f[3] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarProductoConDescuento':
        $rows = Database::selectStoredTenant('webDatpos_CargarProductoConDescuento', array('@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $objUsuario->ccod_tiend), $objUsuario);
        $lst = array(); foreach ($rows as $f) { 
            $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'ndescuento' => strval($f[1] ?? ''), 'ndes_max' => strval($f[1] ?? '')); 
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarDiagramaPastelDatos':
        $rows = Database::selectStoredTenant('webDatpos_cargarDiagramaPastel', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_tienda' => $objUsuario->ccod_tiend,
            '@ccod_caja' => $objUsuario->ccod_caja
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('name' => strval($f[0] ?? ''), 'y' => floatval($f[1] ?? 0));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarTiendaDashboard':
        $rows = Database::selectStoredTenant('webDatpos_CargarTiendaDashboard', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_tiend' => strval($f[0] ?? ''), 'cnombr' => strval($f[1] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'ConsultarDashboard':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_consultaBashboard', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ImporteCaja' => strval($f[0] ?? '0'), 'VentaDelDia' => strval($f[1] ?? '0'),
                'CantUsuarios' => strval($f[2] ?? '0'), 'UsuRegistrados' => strval($f[3] ?? '0'));
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarDiagrama':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarDiagramaCaja', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cusu_crea' => strval($f[0] ?? ''), 'ccod_caja' => strval($f[1] ?? ''), 'ntotal' => strval($f[2] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'CargarCaja':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarDCaja', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('ccod_caja' => strval($f[0] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarUsuario':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarUsuario', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $lst = array(); foreach ($rows as $f) { $lst[] = array('cusu_crea' => strval($f[0] ?? '')); }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarOperacionesClientes':
        $input = getJsonInput(); $data = $input['OperCliente'][0] ?? array();
        $fchDesde = normalizarFechaSQL($data['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($data['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('sp_cargaroperacionesclientes', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta, '@ccod_tienda' => $data['ccod_tienda'] ?? ''), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_coa' => strval($f[0] ?? ''), 'cdsc_usuario' => strval($f[1] ?? ''),
                'DocRef' => strval($f[2] ?? ''), 'cnom_tarje' => strval($f[3] ?? ''),
                'dfch_crea' => strval($f[4] ?? ''), 'nmonto' => strval($f[5] ?? ''));
        } jsonResponse(array('d' => $lst)); break;

    case 'ReporteKardexPrincipal':
        // FIX 73 / BUG 1.3: tableKardex del Home declara 12 columnas:
        // DocRef, FchDoc, cdsc_articulo, Entrada{Cantidad,Costo,Total},
        // Salida{Cantidad,Costo,Total}, Saldo{Cantidad,Costo,Total}.
        // Antes solo se mapeaban 6, asi que la mayoria salia vacia.
        // El SP webDatpos_ConsultaKardex se reescribio en MODIFY_924
        // para devolverlas en este mismo orden con saldo acumulado.
        $input = getJsonInput();
        $data = $input['ReporteKardex'][0] ?? $input;
        $fchDesde = normalizarFechaSQL($data['fchDesde'] ?? ($data['n_fchDesde'] ?? ''));
        $fchHasta = normalizarFechaSQL($data['fchHasta'] ?? ($data['n_fchHasta'] ?? ''));

        $rows = Database::selectStoredTenant('webDatpos_ConsultaKardex', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@ccod_articulo' => $data['ccod_articulo'] ?? '',
            '@ccod_alm' => $data['ccod_alm'] ?? '',
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array(
                'DocRef'          => strval($f[0] ?? ''),
                'FchDoc'          => strval($f[1] ?? ''),
                'cdsc_articulo'   => strval($f[2] ?? ''),
                'EntradaCantidad' => strval($f[3] ?? ''),
                'EntradaCosto'    => strval($f[4] ?? ''),
                'EntradaTotal'    => strval($f[5] ?? ''),
                'SalidaCantidad'  => strval($f[6] ?? ''),
                'SalidaCosto'     => strval($f[7] ?? ''),
                'SalidaTotal'     => strval($f[8] ?? ''),
                'SaldoCantidad'   => strval($f[9] ?? ''),
                'SaldoCosto'      => strval($f[10] ?? ''),
                'SaldoTotal'      => strval($f[11] ?? ''),
            );
        }
        jsonResponse(array('d' => $lst)); break;

    case 'DiagramaCaja':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarDiagramaCaja', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $grouped = array();
        foreach ($rows as $f) {
            $caja = strval($f[1] ?? 'Sin Caja');
            if (!isset($grouped[$caja])) $grouped[$caja] = 0;
            $grouped[$caja] += floatval($f[2] ?? 0);
        }
        $lst = array();
        foreach ($grouped as $name => $total) {
            $lst[] = array('name' => $name, 'y' => round($total, 2));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'DiagramaUsuario':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarDiagramaUsuario', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $grouped = array();
        foreach ($rows as $f) {
            $user = strval($f[0] ?? 'Sin Usuario');
            if (!isset($grouped[$user])) $grouped[$user] = 0;
            $grouped[$user] += floatval($f[2] ?? 0);
        }
        $lst = array();
        foreach ($grouped as $name => $total) {
            $lst[] = array('name' => $name, 'y' => round($total, 2));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarDiagramaUsuario':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarDiagramaUsuario', array(
            '@ccod_cia' => $objUsuario->ccod_empresa, '@ccod_tienda' => $input['ccod_tienda'] ?? '',
            '@fchDesde' => $fchDesde, '@fchHasta' => $fchHasta), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('name' => strval($f[0] ?? ''), 'y' => floatval($f[1] ?? 0));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarProductoSinStock':
        $input = getJsonInput();
        $fchDesde = normalizarFechaSQL($input['fchDesde'] ?? '');
        $fchHasta = normalizarFechaSQL($input['fchHasta'] ?? '');
        
        $rows = Database::selectStoredTenant('webDatpos_CargarProductoSinStock', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cdsc_articulo' => strval($f[0] ?? ''), 'nstock' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarListaUsuario':
        $rows = Database::selectStoredTenant('webDatpos_CargarListaUsuario', array('@ccod_empresa' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_usuario' => strval($f[0] ?? ''), 'cdsc_usuario' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarAlmacenes':
        $rows = Database::selectStoredTenant('sp_consultaalmacenes', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'AlmacenAsignado':
        $rows = Database::selectStoredTenant('sp_consultaalmempactivos', array(
            '@ccod_tiend' => $objUsuario->ccod_tiend,
            '@ccod_cia' => $objUsuario->ccod_empresa
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('ccod_alm' => strval($f[0] ?? ''), 'cdsc_alm' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'CargarRolesUsuario':
        $rows = Database::selectStoredTenant('webDatpos_consultarRoles', array(), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('id_rol' => strval($f[0] ?? ''), 'cdsc_rol' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'VerificarAccesos':
        $rows = Database::selectStoredTenant('webDatpos_verificarAccesos', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_rol' => $objUsuario->id_rol ?? 0,
            '@id_menu' => 0
        ), $objUsuario);
        $lst = array(); foreach ($rows as $f) {
            $lst[] = array('cpermiso' => strval($f[0] ?? ''));
        }
        jsonResponse(array('d' => $lst)); break;

    case 'DesbloquearTiempo':
        jsonResponse(array('d' => 'OK')); break;

    default:
        jsonResponse(array('d' => array()));
        break;
}
?>