/* =====================================================================
   FIX 08 — RECONSTRUIR MENÚ COMPLETO (3 NIVELES)
   Basado en los IDs reales que usan los JS: cli_menu y cul_menu
   
   Lógica del JS (Comun.js):
     Nivel 1: corden < 100     → módulos principales (Almacen, Ventas, Administracion)
     Nivel 2: corden < 1000    → sub-categorías (Tablas, Operaciones, Consultas)
     Nivel 3: corden > 1000    → ítems finales (Artículos, Familias, etc.)
   
   cli_menu = ID del <li> en el HTML
   cul_menu = ID del <ul> colapsable hijo
   curl_src = ruta de imagen PNG para nivel 1
   curl_href = ruta relativa de la página para niveles 2 y 3
   nid_menupadre = id_menu del padre (0 para nivel 1)
===================================================================== */
USE DatPos_EMP01;
GO

-- Limpiar todo y reconstruir
DELETE FROM Accesos;
DELETE FROM Menus;
GO

-- Resetear IDENTITY
DBCC CHECKIDENT ('Menus', RESEED, 0);
GO

/* =========================
   NIVEL 1 — MÓDULOS PRINCIPALES (corden < 100)
   ========================= */
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus)
VALUES
(NULL, 'ALMACEN',        '#', '/Styles/img/icon/icono_almacen.png', 0, '1_li_Almacen',        '1_ul_Almacen',        '1', 1, 'A'),
(NULL, 'VENTAS',         '#', '/Styles/img/icon/icono_ventas.png',  0, '1_li_Ventas',          '1_ul_Ventas',         '1', 2, 'A'),
(NULL, 'ADMINISTRACION', '#', '/Styles/img/icon/icono_adm.png',    0, '1_li_Administracion',  '1_ul_Administracion', '1', 3, 'A');
GO

-- Obtener IDs generados
DECLARE @id_Almacen INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Almacen');
DECLARE @id_Ventas INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Ventas');
DECLARE @id_Admin INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Administracion');

/* =========================
   NIVEL 2 — SUB-CATEGORÍAS (corden 100-999)
   ========================= */
-- Bajo ALMACEN
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'TABLAS',        '#', '', @id_Almacen, '2_li_Tablas',           '2_ul_Tablas',           '2', 101, 'A'),
(NULL, 'OPERACIONES',   '#', '', @id_Almacen, '2_li_Operaciones',      '2_ul_Operaciones',      '2', 102, 'A'),
(NULL, 'CONSULTAS',     '#', '', @id_Almacen, '2_li_ConsultaAlmacen',  '2_ul_ConsultaAlmacen',  '2', 103, 'A'),
(NULL, 'REPORTES',      '#', '', @id_Almacen, '2_li_ReporteAlmacen',   '2_ul_ReporteAlmacen',   '2', 104, 'A');

-- Bajo VENTAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'TABLAS',        '#', '', @id_Ventas, '2_li_TablasVentas',        '2_ul_TablasVentas',        '2', 201, 'A'),
(NULL, 'OPERACIONES',   '#', '', @id_Ventas, '2_li_Ventas_Operaciones',  '2_ul_Ventas_Operaciones',  '2', 202, 'A'),
(NULL, 'CONSULTAS',     '#', '', @id_Ventas, '2_li_ConsultaVenta',       '2_ul_ConsultaVenta',       '2', 203, 'A'),
(NULL, 'REPORTES',      '#', '', @id_Ventas, '2_li_ReporteVenta',        '2_ul_ReporteVenta',        '2', 204, 'A');

-- Bajo ADMINISTRACION (nivel 2, sus hijos son directos — no tiene nivel 3)
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'CONFIG. GENERALES', '/Consultas/ConfigGeneral.aspx',       '', @id_Admin, '2_li_ConfGeneral', '', '2', 301, 'A'),
(NULL, 'TIENDAS',           '/Administracion/Tiendas.aspx',        '', @id_Admin, '2_li_Tiendas',     '', '2', 302, 'A'),
(NULL, 'CAJAS',             '/Administracion/Cajas.aspx',          '', @id_Admin, '2_li_Cajas',       '', '2', 303, 'A'),
(NULL, 'ROLES',             '/Administracion/Roles.aspx',          '', @id_Admin, '2_li_Roles',       '', '2', 304, 'A'),
(NULL, 'USUARIOS',          '/Administracion/Usuarios.aspx',       '', @id_Admin, '2_li_Usuarios',    '', '2', 305, 'A');
GO

-- Obtener IDs de nivel 2
DECLARE @id_Alm_Tablas INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Tablas');
DECLARE @id_Alm_Oper INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Operaciones');
DECLARE @id_Alm_Cons INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaAlmacen');
DECLARE @id_Alm_Rep INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteAlmacen');
DECLARE @id_Ven_Tablas INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_TablasVentas');
DECLARE @id_Ven_Oper INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Ventas_Operaciones');
DECLARE @id_Ven_Cons INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaVenta');
DECLARE @id_Ven_Rep INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteVenta');

/* =========================
   NIVEL 3 — ÍTEMS FINALES (corden > 1000)
   ========================= */
-- ALMACEN > TABLAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'ALMACENES',        '/Tablas/Almacenes.aspx',          '', @id_Alm_Tablas, '3_li_Almacenes',      '', '3', 1011, 'A'),
(NULL, 'UNIDAD DE MEDIDA', '/Tablas/UnidadMedida.aspx',       '', @id_Alm_Tablas, '3_li_UnidadMediad',   '', '3', 1012, 'A'),
(NULL, 'FAMILIAS',         '/Tablas/Familias.aspx',           '', @id_Alm_Tablas, '3_li_Familias',       '', '3', 1014, 'A'),
(NULL, 'ARTICULOS',        '/Tablas/Articulos.aspx',          '', @id_Alm_Tablas, '3_li_Articulos',      '', '3', 1015, 'A'),
(NULL, 'OPERAC. ALMACEN',  '/Administracion/TiposOperacion.aspx', '', @id_Alm_Tablas, '3_li_TiposOperacion', '', '3', 1016, 'A');

-- ALMACEN > OPERACIONES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'INGRESOS DIRECTOS',  '/Operaciones/Ingresos.aspx',        '', @id_Alm_Oper, '3_li_Ingresos',       '', '3', 1021, 'A'),
(NULL, 'SALIDAS DIRECTAS',   '/Operaciones/Salida.aspx',          '', @id_Alm_Oper, '3_li_Salida',         '', '3', 1022, 'A'),
(NULL, 'TRANSFERENCIAS',     '/Operaciones/Transferencias.aspx',   '', @id_Alm_Oper, '3_li_Transferencias', '', '3', 1023, 'A'),
(NULL, 'GUIA DE REMISION',   '/Operaciones/GuiaRemision.aspx',    '', @id_Alm_Oper, '3_li_GuiaRemision',   '', '3', 1024, 'A');

-- ALMACEN > CONSULTAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'MOV. DE ALMACEN',    '/Consultas/ConsultaOperAlmacen.aspx',  '', @id_Alm_Cons, '3_li_Almacen',     '', '3', 1031, 'A'),
(NULL, 'CONSULTA ARTICULOS', '/Consultas/ConsultaArticulos.aspx',    '', @id_Alm_Cons, '3_li_Articulo',    '', '3', 1032, 'A'),
(NULL, 'CONSULTA SALDO',     '/Consultas/ConsultasAlmacen.aspx',     '', @id_Alm_Cons, '3_li_Saldo',       '', '3', 1033, 'A'),
(NULL, 'KARDEX',             '/Consultas/Kardex.aspx',               '', @id_Alm_Cons, '3_li_Kardex',      '', '3', 1034, 'A'),
(NULL, 'ALERTA DE STOCK',    '/Consultas/ConsultaStockMinimo.aspx',  '', @id_Alm_Cons, '3_li_StockMinimo', '', '3', 1035, 'A');

-- ALMACEN > REPORTES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'REPORTE ALMACEN', '/Reportes/ReporteAlmacen.aspx',  '', @id_Alm_Rep, '3_li_ReporteAlmacen', '', '3', 1041, 'A'),
(NULL, 'REPORTE KARDEX',  '/Reportes/ReporteKardex.aspx',   '', @id_Alm_Rep, '3_li_ReporteKardex',  '', '3', 1042, 'A'),
(NULL, 'REPORTE SALDO',   '/Reportes/ReporteSaldo.aspx',    '', @id_Alm_Rep, '3_li_ReporteSaldo',   '', '3', 1043, 'A');

-- VENTAS > TABLAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'ASOCIADOS',      '/Ventas/Clientes.aspx',  '', @id_Ven_Tablas, '2_li_Clientes', '', '3', 2011, 'A'),
(NULL, 'PRECIOS',         '/Ventas/Precios.aspx',   '', @id_Ven_Tablas, '2_li_Precios',  '', '3', 2012, 'A');

-- VENTAS > OPERACIONES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'FACTURACION',          '/Ventas/Facturacion.aspx',      '', @id_Ven_Oper, '3_li_Facturacion',      '', '3', 2021, 'A'),
(NULL, 'FACT. LISTA PRECIO',   '/Ventas/FacturaListaPrecio.aspx','', @id_Ven_Oper, '3_li_FacturaListaPrecio','', '3', 2022, 'A'),
(NULL, 'NOTA DE CREDITO',      '/Ventas/NotaCredito.aspx',      '', @id_Ven_Oper, '3_li_NotaCredito',      '', '3', 2023, 'A'),
(NULL, 'NOTA DE DEBITO',       '/Ventas/NotaDebito.aspx',       '', @id_Ven_Oper, '3_li_NotaDebito',       '', '3', 2024, 'A'),
(NULL, 'ANULACION',            '/Ventas/Anulacion.aspx',        '', @id_Ven_Oper, '3_Li_Anulacion',        '', '3', 2025, 'A'),
(NULL, 'APERTURA DE TURNO',    '/Ventas/AperturaCaja.aspx',     '', @id_Ven_Oper, '3_Li_Apertura',         '', '3', 2026, 'A'),
(NULL, 'CIERRE DE TURNO',      '/Ventas/CierreCaja.aspx',       '', @id_Ven_Oper, '3_Li_CierreCaja',       '', '3', 2027, 'A');

-- VENTAS > CONSULTAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'DOCUMENTOS',             '/Consultas/ConsultaDocumento.aspx',           '', @id_Ven_Cons, '3_li_Documento',           '', '3', 2031, 'A'),
(NULL, 'VENTAS POR ARTICULO',    '/Consultas/ConsultasVenta.aspx',              '', @id_Ven_Cons, '3_li_VentaS',              '', '3', 2032, 'A'),
(NULL, 'COBRANZAS',              '/Consultas/ConsultaFormasPago.aspx',          '', @id_Ven_Cons, '3_li_FormaPago',            '', '3', 2033, 'A'),
(NULL, 'LISTA PRECIOS',          '/Consultas/ConsultaListPrecio.aspx',          '', @id_Ven_Cons, '3_li_ListaPrecios',         '', '3', 2034, 'A'),
(NULL, 'ART. MAS VENDIDOS',      '/Consultas/ConsultaArticulosMasVendidos.aspx','', @id_Ven_Cons, '3_li_ArticulosMasVendidos', '', '3', 2035, 'A'),
(NULL, 'MARGEN UTILIDAD DOC',    '/Consultas/MargenUtilidad.aspx',              '', @id_Ven_Cons, '3_li_MargenUtilidadDoc',    '', '3', 2036, 'A'),
(NULL, 'MARGEN UTILIDAD DIA',    '/Consultas/ConsultaMargenUtilidadDia.aspx',   '', @id_Ven_Cons, '3_li_MargenUtilidadDia',    '', '3', 2037, 'A'),
(NULL, 'DOC. ELECTRONICOS',      '/Reportes/ReporteTributario.aspx',            '', @id_Ven_Cons, '3_li_ReporteTributario',    '', '3', 2038, 'A');

-- VENTAS > REPORTES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'REPORTE VENTAS', '/Reportes/ReporteVenta.aspx', '', @id_Ven_Rep, '3_li_ReporteVenta', '', '3', 2041, 'A'),
(NULL, 'REPORTE TURNO',  '/Reportes/ReporteTurno.aspx',  '', @id_Ven_Rep, '3_li_ReporteTurno', '', '3', 2042, 'A');
GO

/* =========================
   RECREAR ACCESOS PARA ROL ADMIN (id_rol=1)
   ========================= */
DECLARE @id_rol_admin INT = (SELECT TOP 1 id_rol FROM Roles WHERE cdsc_rol LIKE '%ADMIN%' ORDER BY id_rol);
IF @id_rol_admin IS NULL SET @id_rol_admin = 1;

INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT 'EMP01', @id_rol_admin, corden, '1'
FROM Menus;
GO

-- Verificar resultado
SELECT COUNT(*) AS total_menus FROM Menus;
SELECT COUNT(*) AS total_accesos FROM Accesos;
GO

-- Verificar estructura
SELECT id_menu, cdsc_menu, cli_menu, cul_menu, nid_menupadre, corden, nivel
FROM Menus ORDER BY corden;
GO

PRINT '✓ Menú reconstruido correctamente con 3 niveles.';
GO
