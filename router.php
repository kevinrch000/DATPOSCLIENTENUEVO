<?php
/**
 * DatPOS - Router para el servidor PHP embebido
 * 
 * Uso: php -S localhost:8080 router.php
 * 
 * Intercepta URLs .aspx del JavaScript original y las redirige a PHP.
 */

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Route clean /api/documentos/* URLs to their .php files
if (strpos($path, '/api/documentos/') === 0 && substr($path, -4) !== '.php') {
    $phpFile = __DIR__ . $path . '.php';
    if (file_exists($phpFile)) {
        require $phpFile;
        return true;
    }
}

// ============================================================
// 1. AJAX: Interceptar llamadas .aspx/WebMethod
// ============================================================
if (preg_match('/\.aspx\/([A-Za-z]+)$/', $path, $matches)) {
    $method = $matches[1];
    $_GET['method'] = $method;

    // Mapa de .aspx → api PHP
    $apiMap = array(
        'Home.aspx'           => '/api/home_api.php',
        // Tablas
        'Familias.aspx'       => '/api/familia_api.php',
        'UnidadMedida.aspx'   => '/api/unidadmedida_api.php',
        'Almacenes.aspx'      => '/api/almacen_api.php',
        'Articulos.aspx'      => '/api/articulo_api.php',
        // Administración
        'Usuarios.aspx'       => '/api/usuario_api.php',
        'Roles.aspx'          => '/api/roles_api.php',
        'Tiendas.aspx'        => '/api/tienda_api.php',
        'Cajas.aspx'          => '/api/caja_api.php',
        'TiposOperacion.aspx' => '/api/tipooperacion_api.php',
        // Ventas
        'Clientes.aspx'       => '/api/cliente_api.php',
        'Precios.aspx'        => '/api/precio_api.php',
        'Facturacion.aspx'    => '/api/facturacion_api.php',
        'Factura.aspx'        => '/api/facturacion_api.php',
        'AperturaCaja.aspx'   => '/api/aperturacaja_api.php',
        'CierreCaja.aspx'     => '/api/cierrecaja_api.php',
        'NotaCredito.aspx'    => '/api/notacredito_api.php',
        'NotaDebito.aspx'     => '/api/notadebito_api.php',
        'Anulacion.aspx'      => '/api/anulacion_api.php',
        'Factura.aspx'        => '/api/facturacion_api.php',
        // Operaciones
        'Ingresos.aspx'       => '/api/ingreso_api.php',
        'Salida.aspx'         => '/api/salida_api.php',
        'Transferencias.aspx' => '/api/transferencia_api.php',
        'GuiaRemision.aspx'   => '/api/guiaremision_api.php',
        // Consultas
        'ConfigGeneral.aspx'  => '/api/configgeneral_api.php',
        'ConsultasVenta.aspx' => '/api/consultaventa_api.php',
        'ConsultaDocumento.aspx' => '/api/consultadocumento_api.php',
        'ConsultaFormasPago.aspx' => '/api/consultadocumento_api.php',
        'ConsultaArticulos.aspx' => '/api/consultadocumento_api.php',
        'ConsultaArticulosMasVendidos.aspx' => '/api/consultadocumento_api.php',
        'ConsultaListPrecio.aspx' => '/api/consultadocumento_api.php',
        'ConsultaMargenUtilidadDia.aspx' => '/api/consultadocumento_api.php',
        'ConsultaOperAlmacen.aspx' => '/api/consultadocumento_api.php',
        'ConsultasAlmacen.aspx' => '/api/consultaalmacen_api.php',
        'ConsultaStockMinimo.aspx' => '/api/consultadocumento_api.php',
        'Kardex.aspx'         => '/api/consultadocumento_api.php',
        'MargenUtilidad.aspx' => '/api/consultadocumento_api.php',
        // Reportes (usan mismo API de consultas)
        'ReporteAlmacen.aspx' => '/api/consultadocumento_api.php',
        'ReporteKardex.aspx'  => '/api/consultadocumento_api.php',
        'ReporteSaldo.aspx'   => '/api/consultadocumento_api.php',
        'ReporteTributario.aspx' => '/api/consultadocumento_api.php',
        'ReporteTurno.aspx'   => '/api/consultadocumento_api.php',
        'ReporteVenta.aspx'   => '/api/consultaventa_api.php',
    );

    foreach ($apiMap as $aspx => $apiFile) {
        if (strpos($path, $aspx) !== false) {
            $fullPath = __DIR__ . $apiFile;
            if (file_exists($fullPath)) {
                require $fullPath;
            } else {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(array('d' => array()));
            }
            return true;
        }
    }

    // Fallback AJAX
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(array('d' => array()));
    return true;
}

// ============================================================
// 2. NAVEGACIÓN: Redirigir páginas .aspx → .php
// ============================================================
$pageMap = array(
    'LogOn.aspx'           => '/pages/migcliente/LogOn.php',
    'Home.aspx'            => '/pages/Interfaces/Home.php',
    // Tablas
    'Familias.aspx'        => '/pages/Tablas/Familias.php',
    'UnidadMedida.aspx'    => '/pages/Tablas/UnidadMedida.php',
    'Almacenes.aspx'       => '/pages/Tablas/Almacenes.php',
    'Articulos.aspx'       => '/pages/Tablas/Articulos.php',
    // Administración
    'Usuarios.aspx'        => '/pages/Administracion/Usuarios.php',
    'Roles.aspx'           => '/pages/Administracion/Roles.php',
    'Tiendas.aspx'         => '/pages/Administracion/Tiendas.php',
    'Cajas.aspx'           => '/pages/Administracion/Cajas.php',
    'TiposOperacion.aspx'  => '/pages/Administracion/TiposOperacion.php',
    // Ventas
    'Clientes.aspx'        => '/pages/Ventas/Clientes.php',
    'Precios.aspx'         => '/pages/Ventas/Precios.php',
    'Facturacion.aspx'     => '/pages/Ventas/Facturacion.php',
    'AperturaCaja.aspx'    => '/pages/Ventas/AperturaCaja.php',
    'CierreCaja.aspx'      => '/pages/Ventas/CierreCaja.php',
    'NotaCredito.aspx'     => '/pages/Ventas/NotaCredito.php',
    'NotaDebito.aspx'      => '/pages/Ventas/NotaDebito.php',
    'Anulacion.aspx'       => '/pages/Ventas/Anulacion.php',
    'Factura.aspx'         => '/pages/Ventas/Factura.php',
    // Operaciones
    'Ingresos.aspx'        => '/pages/Operaciones/Ingresos.php',
    'Salida.aspx'          => '/pages/Operaciones/Salida.php',
    'Transferencias.aspx'  => '/pages/Operaciones/Transferencias.php',
    'GuiaRemision.aspx'    => '/pages/Operaciones/GuiaRemision.php',
    // Consultas
    'ConfigGeneral.aspx'   => '/pages/Consultas/ConfigGeneral.php',
    'ConsultaArticulos.aspx' => '/pages/Consultas/ConsultaArticulos.php',
    'ConsultaArticulosMasVendidos.aspx' => '/pages/Consultas/ConsultaArticulosMasVendidos.php',
    'ConsultaDocumento.aspx' => '/pages/Consultas/ConsultaDocumento.php',
    'ConsultaFormasPago.aspx' => '/pages/Consultas/ConsultaFormasPago.php',
    'ConsultaListPrecio.aspx' => '/pages/Consultas/ConsultaListPrecio.php',
    'ConsultaMargenUtilidadDia.aspx' => '/pages/Consultas/ConsultaMargenUtilidadDia.php',
    'ConsultaOperAlmacen.aspx' => '/pages/Consultas/ConsultaOperAlmacen.php',
    'ConsultasAlmacen.aspx' => '/pages/Consultas/ConsultasAlmacen.php',
    'ConsultaStockMinimo.aspx' => '/pages/Consultas/ConsultaStockMinimo.php',
    'ConsultasVenta.aspx'  => '/pages/Consultas/ConsultasVenta.php',
    'Kardex.aspx'          => '/pages/Consultas/Kardex.php',
    'MargenUtilidad.aspx'  => '/pages/Consultas/MargenUtilidad.php',
    // Reportes
    'ReporteAlmacen.aspx'  => '/pages/Reportes/ReporteAlmacen.php',
    'ReporteKardex.aspx'   => '/pages/Reportes/ReporteKardex.php',
    'ReporteSaldo.aspx'    => '/pages/Reportes/ReporteSaldo.php',
    'ReporteTributario.aspx' => '/pages/Reportes/ReporteTributario.php',
    'ReporteTurno.aspx'    => '/pages/Reportes/ReporteTurno.php',
    'ReporteVenta.aspx'    => '/pages/Reportes/ReporteVenta.php',
    // Informes (Report Viewers)
    'InformeAlmacen.aspx'  => '/pages/Reportes/InformeAlmacen.php',
    'InformeGuiaRemision.aspx' => '/pages/Reportes/InformeGuiaRemision.php',
    'InformeKardex.aspx'   => '/pages/Reportes/InformeKardex.php',
    'InformeSaldo.aspx'    => '/pages/Reportes/InformeSaldo.php',
    'InformeTributario.aspx' => '/pages/Reportes/InformeTributario.php',
    'InformeTurno.aspx'    => '/pages/Reportes/InformeTurno.php',
    'InformeVenta.aspx'    => '/pages/Reportes/InformeVenta.php',
    // Administración - Utilidades
    'Enconstruccion.aspx'  => '/pages/Administracion/Enconstruccion.php',
    'SinAcceso.aspx'       => '/pages/Administracion/SinAcceso.php',
    'Documentos.aspx'      => '/pages/Consultas/Documentos.php',
    // Interfaces - Super Admin
    'AdministrarCompanias.aspx' => '/pages/Interfaces/AdministrarCompanias.php',
    'AdministrarUsuarios.aspx'  => '/pages/Interfaces/AdministrarUsuarios.php',
);

// Solo redirigir si NO es una llamada AJAX (sin /Method)
if (preg_match('/\.aspx$/i', $path)) {
    foreach ($pageMap as $aspx => $phpPage) {
        if (strpos($path, $aspx) !== false) {
            $fullPath = __DIR__ . $phpPage;
            if (file_exists($fullPath)) {
                header('Location: ' . $phpPage);
            } else {
                // Página no migrada aún, usar layout_master para no perder el menú
                ob_start();
                echo '<div style="display:flex;justify-content:center;align-items:center;height:80vh;width:100%;">';
                echo '<div style="text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">';
                echo '<h2 style="color:#046bb4;"><i class="fa fa-wrench"></i> Módulo en Construcción</h2>';
                echo '<p style="color:#666;">La página <b>' . htmlspecialchars($aspx) . '</b> aún no ha sido migrada.</p>';
                echo '<a href="/pages/Interfaces/Home.php" class="btn btn-primary" style="margin-top:15px;border-radius:25px;">Volver al Inicio</a>';
                echo '</div></div>';
                $pageContent = ob_get_clean();
                require __DIR__ . '/includes/layout_master.php';
            }
            return true;
        }
    }
    
    // Cualquier otro .aspx
    ob_start();
    echo '<div style="display:flex;justify-content:center;align-items:center;height:80vh;width:100%;">';
    echo '<div style="text-align:center;padding:40px;background:#fff;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">';
    echo '<h2 style="color:#046bb4;"><i class="fa fa-wrench"></i> Módulo en Construcción</h2>';
    echo '<p style="color:#666;">Esta página aún no ha sido migrada a PHP.</p>';
    echo '<a href="/pages/Interfaces/Home.php" class="btn btn-primary" style="margin-top:15px;border-radius:25px;">Volver al Inicio</a>';
    echo '</div></div>';
    $pageContent = ob_get_clean();
    require __DIR__ . '/includes/layout_master.php';
    return true;
}

// ============================================================
// 3. Servir archivos estáticos/PHP normalmente (soporte para rutas legacy)
// ============================================================
if (strpos($path, '/Styles/') === 0 || strpos($path, '/Scripts/') === 0) {
    $assetsPath = '/assets' . $path;
    $fullPath = __DIR__ . $assetsPath;
    if (file_exists($fullPath)) {
        $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = array(
            'css'   => 'text/css',
            'js'    => 'application/javascript',
            'png'   => 'image/png',
            'jpg'   => 'image/jpeg',
            'jpeg'  => 'image/jpeg',
            'gif'   => 'image/gif',
            'svg'   => 'image/svg+xml',
            'woff'  => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf'   => 'font/ttf',
            'eot'   => 'application/vnd.ms-fontobject',
            'otf'   => 'font/otf',
            'map'   => 'application/json'
        );
        $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        readfile($fullPath);
        return true;
    }
}

if (file_exists(__DIR__ . $path)) {
    return false;
}

// 404
return false;
?>
