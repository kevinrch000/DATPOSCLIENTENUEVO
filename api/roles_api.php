<?php
/**
 * DatPOS - API: Roles (Administración)
 * Reemplaza los WebMethods de Roles.aspx.vb
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['objBEUsuario'])) { jsonResponse(array('d' => '-1')); }
$objUsuario = $_SESSION['objBEUsuario'];
$method = $_GET['method'] ?? '';

function normalizarEstado(string $val): string {
    $v = strtoupper(trim($val));
    if ($v === 'A' || $v === '1') return '1';
    if ($v === 'I' || $v === '0') return '0';
    return $v;
}

switch ($method) {

    case 'ConsultarRoles':
        $rows = Database::selectStoredTenant('webDatpos_consultarRoles', array('@ccod_empresa' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'item'         => "<input id='" . strval($f[0]) . "' type='checkbox' class='limpiar_item_checked' onclick='checked_click(this)'>",
                'id_rol'       => strval($f[0] ?? ''),
                'cdescripcion' => strval($f[2] ?? ''),
                'cstatus'      => normalizarEstado(strval($f[3] ?? ''))
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ConsultarIdRol':
        $input = getJsonInput();
        $id = $input['id'] ?? $input['Id_rol'] ?? $input['id_rol'] ?? '';

        if ($id === '') {
            jsonResponse(array('d' => array()));
            break;
        }

        $rows = Database::selectStoredTenant(
            'webDatpos_consultarIdRol',
            array('@id_rol' => $id),   // SP solo recibe @id_rol
            $objUsuario
        );

        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'id_rol'       => strval($f[0] ?? ''),
                'cdescripcion' => strval($f[1] ?? ''),   // cdsc_rol → cdescripcion
                'cstatus'      => normalizarEstado(strval($f[2] ?? ''))
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarTablaMenu':
        $rows = Database::selectStoredTenant('webDatpos_cargarTablaMenu', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdsc_menu'    => strval($f[0] ?? ''),
                'curl_href'    => strval($f[1] ?? ''),
                'nid_menupadre'=> strval($f[2] ?? ''),
                'cli_menu'     => strval($f[3] ?? ''),
                'cul_menu'     => strval($f[4] ?? ''),
                'cstatus'      => strval($f[5] ?? ''),
                'corden'       => strval($f[6] ?? ''),
                'id_menu'      => strval($f[7] ?? ''),
                'curl_src'     => strval($f[8] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarAccesos':
        $rows = Database::selectStoredTenant('webDatpos_cargarTablaMenu', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('corden' => strval($f[6] ?? ''), 'cstatus' => '1');
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'CargarTablaMenuIdAccesos':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_cargarTablaMenuIdAccesos', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_rol'   => $input['Id_rol'] ?? ''
        ), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array(
                'cdsc_menu'    => strval($f[0] ?? ''),
                'curl_href'    => strval($f[1] ?? ''),
                'nid_menupadre'=> strval($f[2] ?? ''),
                'cli_menu'     => strval($f[3] ?? ''),
                'cul_menu'     => strval($f[4] ?? ''),
                'corden'       => strval($f[5] ?? ''),
                'id_menu'      => strval($f[6] ?? ''),
                'curl_src'     => strval($f[7] ?? ''),
                'cstatus'      => strval($f[8] ?? '')
            );
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ObtenerIdPadre':
        $rows = Database::selectStoredTenant('webDatpos_obtenerIdPadre', array('@ccod_cia' => $objUsuario->ccod_empresa), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('nid_menupadre' => strval($f[0] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'ObtenerIdMenu':
        $input = getJsonInput();
        $rows = Database::selectStoredTenant('webDatpos_obtenerIdMenu', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@corden'   => $input['Orden'] ?? ''
        ), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('corden' => strval($f[0] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    case 'Guardar':
        $input    = getJsonInput();
        $rol      = isset($input['rol'][0]) ? $input['rol'][0] : null;
        $menu     = isset($input['menu'])   ? $input['menu']   : array();
        $modulo   = isset($input['modulo']) ? $input['modulo'] : array();
        $operacion = $input['operacion'] ?? '';

        if (!$rol) jsonResponse(array('d' => array(false, 'Sin datos', '', '')));

        $connStr = Database::buildTenantConnectionString($objUsuario);
        $conn    = sqlsrv_connect($connStr['server'], $connStr['connectionInfo']);
        if (!$conn) jsonResponse(array('d' => array(false, 'Error de conexión', '', '')));

        sqlsrv_begin_transaction($conn);
        try {
            $errorNumber  = 'OK';
            $errorMessage = '';
            $id_rol       = '';

            if ($operacion === 'nuevo') {
                // SP: webDatpos_insertarRol(@ccod_empresa, @cdsc_rol, @ccod_usuario)
                // Devuelve: SELECT SCOPE_IDENTITY() AS id_rol
                $sql  = "EXEC webDatpos_insertarRol @ccod_empresa=?, @cdsc_rol=?, @ccod_usuario=?";
                $stmt = sqlsrv_query($conn, $sql, array(
                    $objUsuario->ccod_empresa,
                    $rol['cdescripcion'] ?? '',
                    $objUsuario->ccod_usuario
                ));
                if (!$stmt) {
                    $errors = sqlsrv_errors();
                    throw new Exception(json_encode($errors));
                }
                if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $id_rol = strval($row['id_rol'] ?? '');
                }
                sqlsrv_free_stmt($stmt);

            } else {
                // Editar — ajusta parámetros según tu SP webDatpos_editarRol
                $id_rol = $rol['id_rol'] ?? '';
                $sql  = "EXEC webDatpos_editarRol @ccod_empresa=?, @cdsc_rol=?, @cstatus=?, @ccod_usuario=?, @id_rol=?";
                $stmt = sqlsrv_query($conn, $sql, array(
                    $objUsuario->ccod_empresa,
                    $rol['cdescripcion'] ?? '',
                    $rol['cstatus'] ?? '1',
                    $objUsuario->ccod_usuario,
                    $id_rol
                ));
                if (!$stmt) {
                    $errors = sqlsrv_errors();
                    throw new Exception(json_encode($errors));
                }
                sqlsrv_free_stmt($stmt);

                $sqlDel  = "EXEC webDatpos_eliminarIDAcceso @ccod_empresa=?, @id_rol=?";
                $stmtDel = sqlsrv_query($conn, $sqlDel, array($objUsuario->ccod_empresa, $id_rol));
                if ($stmtDel) sqlsrv_free_stmt($stmtDel);
            }

            // Insertar accesos si el id_rol se obtuvo correctamente.
            // El JS envia cstatus como boolean JSON (true/false), no como string 'True'.
            // Toleramos boolean / 'true' / 'True' / '1' / 1.
            //
            // Se guarda un acceso por CADA casilla marcada (cabecera o detalle),
            // sin filtrar por 'nivel'. Antes solo se guardaban los detalles
            // (nivel='Si'), por lo que los menus principales (TABLAS, OPERACIONES)
            // nunca quedaban en Accesos y sus ramas no se mostraban. El SP
            // webDatpos_cargarRol completa los modulos/ancestros automaticamente.
            $cordenesGuardados = array();
            if ($id_rol !== '') {
                foreach ($menu as $m) {
                    $st = $m['cstatus'] ?? false;
                    $isChecked = ($st === true || $st === 1 || $st === '1'
                        || (is_string($st) && strtolower($st) === 'true'));
                    $corden = strval($m['corden'] ?? '');
                    if ($isChecked && $corden !== '' && !isset($cordenesGuardados[$corden])) {
                        $cordenesGuardados[$corden] = true;
                        $sqlAcc  = "EXEC webDatpos_insertarAcceso @ccod_empresa=?, @id_rol=?, @corden=?";
                        $stmtAcc = sqlsrv_query($conn, $sqlAcc, array(
                            $objUsuario->ccod_empresa,
                            $id_rol,
                            $corden
                        ));
                        if ($stmtAcc) sqlsrv_free_stmt($stmtAcc);
                    }
                }
                // Compatibilidad: si algun cliente antiguo aun envia 'modulo',
                // tambien se guardan (sin duplicar los ya insertados arriba).
                foreach ($modulo as $mod) {
                    $st = $mod['cstatus'] ?? false;
                    $isChecked = ($st === true || $st === 1 || $st === '1'
                        || (is_string($st) && strtolower($st) === 'true'));
                    $corden = strval($mod['corden'] ?? '');
                    if ($isChecked && $corden !== '' && !isset($cordenesGuardados[$corden])) {
                        $cordenesGuardados[$corden] = true;
                        $sqlAcc2  = "EXEC webDatpos_insertarAcceso @ccod_empresa=?, @id_rol=?, @corden=?";
                        $stmtAcc2 = sqlsrv_query($conn, $sqlAcc2, array(
                            $objUsuario->ccod_empresa,
                            $id_rol,
                            $corden
                        ));
                        if ($stmtAcc2) sqlsrv_free_stmt($stmtAcc2);
                    }
                }
            }

            sqlsrv_commit($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => array(true, 'OK', '', $id_rol)));

        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            sqlsrv_close($conn);
            jsonResponse(array('d' => array(false, 'Error', $e->getMessage(), '')));
        }
        break;

    case 'Eliminar':
        $input = getJsonInput();
        $rows  = Database::selectStoredTenant('webDatpos_eliminarRol', array(
            '@ccod_cia' => $objUsuario->ccod_empresa,
            '@id_rol'   => $input['Id_rol'] ?? ''
        ), $objUsuario);
        $lst = array();
        foreach ($rows as $f) {
            $lst[] = array('ccod_rol' => strval($f[0] ?? ''), 'ccod_cia' => strval($f[1] ?? ''));
        }
        jsonResponse(array('d' => $lst));
        break;

    default:
        jsonResponse(array('d' => array()));
}
?>