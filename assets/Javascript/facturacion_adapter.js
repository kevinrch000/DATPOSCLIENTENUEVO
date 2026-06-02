/**
 * facturacion_adapter.js — Adaptador Universal .aspx → PHP
 * 
 * Intercepta TODAS las llamadas $.ajax que apuntan a .aspx/Metodo
 * y las redirige al API PHP correcto según el nombre de la página.
 * 
 * Debe cargarse DESPUÉS de jQuery y Comun.js, ANTES de scripts de página.
 */

(function ($) {
    var _ajax = $.ajax;

    // ============================================================
    // Mapa: NombrePagina.aspx → archivo API PHP
    // ============================================================
    var API_MAP = {
        'Home':               { api: 'home_api.php',              param: 'method' },
        'ConfigGeneral':       { api: 'configgeneral_api.php',     param: 'method' },
        'Facturacion':        { api: 'facturacion_api.php',       param: 'method' },
        'Facturacion6':       { api: 'facturacion_api.php',       param: 'method' },
        'Almacen':            { api: 'almacen_api.php',           param: 'method' },
        'Almacenes':          { api: 'almacen_api.php',           param: 'method' },
        'Articulo':           { api: 'articulo_api.php',          param: 'method' },
        'Articulos':          { api: 'articulo_api.php',          param: 'method' },
        'Cliente':            { api: 'cliente_api.php',           param: 'method' },
        'Clientes':           { api: 'cliente_api.php',           param: 'method' },
        'Familia':            { api: 'familia_api.php',           param: 'method' },
        'Familias':           { api: 'familia_api.php',           param: 'method' },
        'UnidadMedida':       { api: 'unidadmedida_api.php',      param: 'method' },
        'UnidadesMedida':     { api: 'unidadmedida_api.php',      param: 'method' },
        'Roles':              { api: 'roles_api.php',             param: 'method' },
        'Tienda':            { api: 'tienda_api.php',            param: 'method' },
        'Tiendas':            { api: 'tienda_api.php',            param: 'method' },
        'Caja':               { api: 'caja_api.php',              param: 'method' },
        'Cajas':              { api: 'caja_api.php',              param: 'method' },
        'AperturaCaja':       { api: 'aperturacaja_api.php',      param: 'method' },
        'Apertura':           { api: 'aperturacaja_api.php',      param: 'method' },
        'CierreCaja':        { api: 'cierrecaja_api.php',        param: 'method' },
        'Ingresos':          { api: 'ingreso_api.php',           param: 'method' },
        'Salida':             { api: 'salida_api.php',            param: 'method' },
        'Transferencias':     { api: 'transferencia_api.php',     param: 'method' },
        'TiposOperacion':     { api: 'tipooperacion_api.php',    param: 'method' },
        'Usuario':            { api: 'usuario_api.php',           param: 'method' },
        'Usuarios':           { api: 'usuario_api.php',           param: 'method' },
        'Precio':             { api: 'precio_api.php',            param: 'method' },
        'Precios':            { api: 'precio_api.php',            param: 'method' },
        'ListaPrecio':        { api: 'precio_api.php',            param: 'method' },
        'NotaCredito':       { api: 'notacredito_api.php',      param: 'method' },
        'NotaDebito':        { api: 'notadebito_api.php',       param: 'method' },
        'Anulacion':         { api: 'anulacion_api.php',         param: 'method' },
        'ConsultaDocumento':  { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaDocumento5': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaVentas':     { api: 'consultaventa_api.php',     param: 'method' },
        'GuiaRemision':      { api: 'guiaremision_api.php',     param: 'method' },
        'Bashboard':         { api: 'home_api.php',             param: 'method' },
        'Dashboard':          { api: 'home_api.php',             param: 'method' },
        'Factura':           { api: 'facturacion_api.php',      param: 'method' },
        'ConsultaFormaPago':  { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaFormasPago': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaOperAlmacen':{ api: 'consultadocumento_api.php', param: 'method' },
        'ConsultasAlmacen':   { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaStockMinimo': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaArticulos':  { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaArticulosMasVendidos': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaListPrecio': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaListPrecios':{ api: 'consultadocumento_api.php', param: 'method' },
        'ConsultaMargenUtilidadDia': { api: 'consultadocumento_api.php', param: 'method' },
        'ConsultasVenta':     { api: 'consultaventa_api.php',     param: 'method' },
        'Kardex':             { api: 'consultadocumento_api.php', param: 'method' },
        'MargenUtilidad':     { api: 'consultadocumento_api.php', param: 'method' },
        'MargenUtilidadDia':  { api: 'consultadocumento_api.php', param: 'method' },
        // Reportes (todos delegan a consultadocumento_api por defecto)
        'ReporteAlmacen':     { api: 'consultadocumento_api.php', param: 'method' },
        'ReporteKardex':      { api: 'consultadocumento_api.php', param: 'method' },
        'ReporteSaldo':       { api: 'consultadocumento_api.php', param: 'method' },
        'ReporteTributario':  { api: 'consultadocumento_api.php', param: 'method' },
        'ReporteTurno':       { api: 'consultadocumento_api.php', param: 'method' },
        'ReporteVenta':       { api: 'consultaventa_api.php',     param: 'method' }
    };

    // ============================================================
    // Interceptar $.ajax — redirigir .aspx → PHP API
    // ============================================================
    $.ajax = function (options) {
        if (options && options.url && typeof options.url === 'string') {
            var originalUrl = options.url;
            var basePath = window.DATPOS_BASE_PATH || '';

            // Caso 1: URLs .aspx → convertir a PHP
            var match = options.url.match(/(?:.*\/)?(\w+)\.aspx\/(\w+)/);
            if (match) {
                var pageName = match[1];
                var methodName = match[2];
                var mapping = API_MAP[pageName];

                if (!mapping) {
                    console.warn('[Adapter] Sin mapeo para:', pageName + '.aspx/' + methodName, '→ usando home_api.php');
                    mapping = { api: 'home_api.php', param: 'method' };
                }

                options.url = basePath + '/api/' + mapping.api + '?' + mapping.param + '=' + methodName;
                console.log('[Adapter] .aspx →', originalUrl, '→', options.url);
                return _ajax.apply(this, arguments);
            }

            // Caso 2: URLs con nombreApi_api.php?method=XXX (sin prefijo /api/)
            // Coincide: aperturacaja_api.php?method=XXX
            if (options.url.indexOf('_api.php?method=') !== -1) {
                var apiMatch = options.url.match(/^([a-z_]+_api\.php)\?method=(\w+)/i);
                if (apiMatch) {
                    options.url = basePath + '/api/' + options.url;
                    console.log('[Adapter] API relative →', originalUrl, '→', options.url);
                    return _ajax.apply(this, arguments);
                }
            }

            // Caso 3: URLs directas a PHP API con /api/ prefix
            if (options.url.indexOf('/api/') !== -1) {
                // Ya es una URL de API directa con prefijo, no modificar
                return _ajax.apply(this, arguments);
            }
        }
        return _ajax.apply(this, arguments);
    };

    // ============================================================
    // Traducir URLs de navegación .aspx → .php
    // Usado por DatosPendientes() y otros
    // ============================================================
    // Mapa explicito de rutas legacy ASP.NET (~/Interfaces/X.aspx) a paginas PHP reales
    var URL_MAP = {
        // Home / Dashboard
        '/Interfaces/Home.aspx':                       '/Interfaces/Home.php',
        '/Interfaces/Dashboard.aspx':                  '/Interfaces/Home.php',
        // Ventas / Facturacion
        '/Interfaces/Facturacion.aspx':                '/Ventas/Facturacion.php',
        '/Interfaces/Factura.aspx':                    '/Ventas/Factura.php',
        '/Interfaces/NotaCredito.aspx':                '/Ventas/NotaCredito.php',
        '/Interfaces/NotaDebito.aspx':                 '/Ventas/NotaDebito.php',
        '/Interfaces/Anulacion.aspx':                  '/Ventas/Anulacion.php',
        '/Interfaces/AperturaCaja.aspx':               '/Ventas/AperturaCaja.php',
        '/Interfaces/CierreCaja.aspx':                 '/Ventas/CierreCaja.php',
        '/Interfaces/AdministrarClientes.aspx':        '/Ventas/Clientes.php',
        '/Interfaces/AdministrarListaPrecio.aspx':     '/Ventas/Precios.php',
        '/Interfaces/Cuenta.aspx':                     '/Ventas/Factura.php',
        // Operaciones / Inventario
        '/Interfaces/AdministrarInventario.aspx':      '/Operaciones/Ingresos.php',
        '/Interfaces/AdministrarSalidas.aspx':         '/Operaciones/Salida.php',
        '/Interfaces/AdministrarTransferencia.aspx':   '/Operaciones/Transferencias.php',
        '/Interfaces/AdministrarGuia.aspx':            '/Operaciones/GuiaRemision.php',
        // Catalogos / Tablas
        '/Interfaces/AdministrarArticulos.aspx':       '/Tablas/Articulos.php',
        '/Interfaces/AdministrarFamilias.aspx':        '/Tablas/Familias.php',
        '/Interfaces/AdministrarUnidadMedida.aspx':    '/Tablas/UnidadMedida.php',
        '/Interfaces/AdministrarAlmacenes.aspx':       '/Tablas/Almacenes.php',
        '/Interfaces/AdministrarVariantes.aspx':       '/Tablas/Articulos.php',
        // Administracion
        '/Interfaces/AdministrarTiendas.aspx':         '/Administracion/Tiendas.php',
        '/Interfaces/AdministrarCajas.aspx':           '/Administracion/Cajas.php',
        '/Interfaces/AdministrarUsuarios.aspx':        '/Administracion/Usuarios.php',
        '/Interfaces/AdministrarRoles.aspx':           '/Administracion/Roles.php',
        '/Interfaces/AdministrarTipoOperacion.aspx':   '/Administracion/TiposOperacion.php',
        '/Interfaces/AdministrarCompanias.aspx':       '/Interfaces/AdministrarCompanias.php',
        // Consultas
        '/Interfaces/ConsultaVenta.aspx':              '/Consultas/ConsultasVenta.php',
        '/Interfaces/ConsultasVenta.aspx':             '/Consultas/ConsultasVenta.php',
        '/Interfaces/ConsultaAlmacen.aspx':            '/Consultas/ConsultasAlmacen.php',
        '/Interfaces/ConsultaKardex.aspx':             '/Consultas/Kardex.php',
        '/Interfaces/Kardex.aspx':                     '/Consultas/Kardex.php',
        '/Interfaces/ConsultaDocumento.aspx':          '/Consultas/ConsultaDocumento.php',
        '/Interfaces/ConsultaArticulos.aspx':          '/Consultas/ConsultaArticulos.php',
        '/Interfaces/ConsultaArticulosMasVendidos.aspx':'/Consultas/ConsultaArticulosMasVendidos.php',
        '/Interfaces/ConsultaListPrecio.aspx':         '/Consultas/ConsultaListPrecio.php',
        '/Interfaces/ConsultaListPrecios.aspx':        '/Consultas/ConsultaListPrecio.php',
        '/Interfaces/ConsultaMargenUtilidadDia.aspx':  '/Consultas/ConsultaMargenUtilidadDia.php',
        '/Interfaces/ConsultaStockMinimo.aspx':        '/Consultas/ConsultaStockMinimo.php',
        '/Interfaces/ConsultaFormasPago.aspx':         '/Consultas/ConsultaFormasPago.php',
        '/Interfaces/ConsultaFormaPago.aspx':          '/Consultas/ConsultaFormasPago.php',
        '/Interfaces/ConsultaOperAlmacen.aspx':        '/Consultas/ConsultaOperAlmacen.php',
        '/Interfaces/ConsultasAlmacen.aspx':           '/Consultas/ConsultasAlmacen.php',
        '/Interfaces/ConsultaTributaria.aspx':         '/Reportes/ReporteTributario.php',
        '/Interfaces/MargenUtilidad.aspx':             '/Consultas/MargenUtilidad.php',
        '/Interfaces/ConfigGeneral.aspx':              '/Consultas/ConfigGeneral.php',
        // Reportes
        '/Interfaces/ReporteVenta.aspx':               '/Reportes/ReporteVenta.php',
        '/Interfaces/ReporteAlmacen.aspx':             '/Reportes/ReporteAlmacen.php',
        '/Interfaces/ReporteSaldo.aspx':               '/Reportes/ReporteSaldo.php',
        '/Interfaces/ReporteTurno.aspx':               '/Reportes/ReporteTurno.php',
        '/Interfaces/ReporteKardex.aspx':              '/Reportes/ReporteKardex.php',
        '/Interfaces/ReporteTributario.aspx':          '/Reportes/ReporteTributario.php'
    };

    window.DATPOS_translateUrl = function (aspxPath) {
        if (!aspxPath || aspxPath === '#' || aspxPath === '') return '#';
        var basePath = window.DATPOS_BASE_PATH || '';

        // Quitar prefijo ASP.NET ~/  (ej. ~/Interfaces/X.aspx)
        var clean = aspxPath.replace(/^~/, '');
        // Asegurar que arranca con /
        if (clean.charAt(0) !== '/') clean = '/' + clean;

        // 1) Mapeo explicito (preferido): /Interfaces/AdministrarTiendas.aspx -> /Administracion/Tiendas.php
        if (URL_MAP[clean]) {
            return basePath + '/pages' + URL_MAP[clean];
        }

        // 2) Fallback: cambio simple .aspx -> .php manteniendo ruta
        var phpPath = clean.replace(/\.aspx/gi, '.php');
        return basePath + '/pages' + phpPath;
    };

    // ============================================================
    // Traducir rutas de íconos del menú
    // DB guarda: /Styles/img/icon/icono_ventas.png
    // PHP necesita: basePath/assets/Styles/img/icon/icono_ventas.png
    // ============================================================
    window.DATPOS_fixIconPath = function (iconPath) {
        if (!iconPath) return '';
        var basePath = window.DATPOS_BASE_PATH || '';
        // Si ya tiene /assets/, no duplicar
        if (iconPath.indexOf('/assets/') === 0) return basePath + iconPath;
        // Si empieza con /Styles/ o similar, añadir /assets
        if (iconPath.indexOf('/') === 0) return basePath + '/assets' + iconPath;
        return basePath + '/assets/' + iconPath;
    };

})(jQuery);