<?php
/**
 * api/menu.php
 * Carga el menú lateral según el rol del usuario
 * Reemplaza: Site.Master.vb → CargarMenu() / webDatpos_obtenerIdMenu
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../config/database.php';

requireAuth();
$objUsuario = getUsuarioSesion();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'ObtenerMenu':
        // Obtener menús a los que el rol del usuario tiene acceso
        $rows = Database::selectStoredTenant('webDatpos_obtenerIdMenu', [
            '@ccod_empresa' => $objUsuario->ccod_empresa,
            '@id_rol' => (int) ($objUsuario->id_rol ?? 0),
        ], $objUsuario);

        if (empty($rows)) {
            // Si no hay rol asignado, cargar menú completo (admin)
            $rows = Database::selectStoredTenant('webDatpos_cargarTablaMenu', [
                '@ccod_empresa' => $objUsuario->ccod_empresa,
            ], $objUsuario);
        }

        $menus = [];
        foreach ($rows as $r) {
            $menus[] = [
                'id_menu' => $r['id_menu'] ?? 0,
                'cdsc_menu' => $r['cdsc_menu'] ?? '',
                'curl_href' => $r['curl_href'] ?? '',
                'curl_src' => $r['curl_src'] ?? 'fa-circle-o',
                'nid_menupadre' => $r['nid_menupadre'] ?? 0,
                'nivel' => $r['nivel'] ?? '1',
                'corden' => $r['corden'] ?? 0,
            ];
        }

        echo json_encode(['d' => $menus]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Acción no encontrada']);
}