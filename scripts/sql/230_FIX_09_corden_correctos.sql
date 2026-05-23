/* =====================================================================
   FIX 09 — CORREGIR corden PARA QUE COINCIDAN CON LOS VALORES
   HARDCODEADOS EN CADA PAGINA .aspx.vb (VerificarAccesos)
   
   Cada página verifica acceso con un corden específico. El SP 
   webDatpos_verificarAccesos busca ese corden en la tabla Accesos.
   Si no encuentra coincidencia → redirige a SinAcceso.aspx
===================================================================== */
USE DatPos_EMP01;
GO

-- Limpiar todo
DELETE FROM Accesos;
DELETE FROM Menus;
DBCC CHECKIDENT ('Menus', RESEED, 0);
GO

/* ===== NIVEL 1 — MÓDULOS PRINCIPALES ===== */
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'ALMACEN',        '#', '/Styles/img/icon/icono_almacen.png', 0, '1_li_Almacen',        '1_ul_Almacen',        '1', 1, 'A'),
(NULL, 'VENTAS',         '#', '/Styles/img/icon/icono_ventas.png',  0, '1_li_Ventas',          '1_ul_Ventas',         '1', 2, 'A'),
(NULL, 'ADMINISTRACION', '#', '/Styles/img/icon/icono_adm.png',    0, '1_li_Administracion',  '1_ul_Administracion', '1', 3, 'A');
GO

DECLARE @id_Alm INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Almacen');
DECLARE @id_Ven INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Ventas');
DECLARE @id_Adm INT = (SELECT id_menu FROM Menus WHERE cli_menu='1_li_Administracion');

/* ===== NIVEL 2 — SUB-CATEGORÍAS ===== */
-- ALMACEN sub-categorías
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'TABLAS',       '#', '', @id_Alm, '2_li_Tablas',          '2_ul_Tablas',          '2', 100, 'A'),
(NULL, 'OPERACIONES',  '#', '', @id_Alm, '2_li_Operaciones',     '2_ul_Operaciones',     '2', 101, 'A'),
(NULL, 'CONSULTAS',    '#', '', @id_Alm, '2_li_ConsultaAlmacen', '2_ul_ConsultaAlmacen', '2', 102, 'A'),
(NULL, 'REPORTES',     '#', '', @id_Alm, '2_li_ReporteAlmacen',  '2_ul_ReporteAlmacen',  '2', 103, 'A');

-- VENTAS sub-categorías
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'TABLAS',       '#', '', @id_Ven, '2_li_TablasVentas',       '2_ul_TablasVentas',       '2', 200, 'A'),
(NULL, 'OPERACIONES',  '#', '', @id_Ven, '2_li_Ventas_Operaciones', '2_ul_Ventas_Operaciones', '2', 201, 'A'),
(NULL, 'CONSULTAS',    '#', '', @id_Ven, '2_li_ConsultaVenta',      '2_ul_ConsultaVenta',      '2', 202, 'A'),
(NULL, 'REPORTES',     '#', '', @id_Ven, '2_li_ReporteVenta',       '2_ul_ReporteVenta',       '2', 203, 'A');

-- ADMINISTRACION (hijos directos nivel 2, corden = el valor del VB)
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'CONFIG. GENERALES', '/Consultas/ConfigGeneral.aspx',         '', @id_Adm, '2_li_ConfGeneral', '', '2', 109, 'A'),
(NULL, 'TIENDAS',           '/Administracion/Tiendas.aspx',          '', @id_Adm, '2_li_Tiendas',     '', '2', 110, 'A'),
(NULL, 'CAJAS',             '/Administracion/Cajas.aspx',            '', @id_Adm, '2_li_Cajas',       '', '2', 111, 'A'),
(NULL, 'ROLES',             '/Administracion/Roles.aspx',            '', @id_Adm, '2_li_Roles',       '', '2', 112, 'A'),
(NULL, 'USUARIOS',          '/Administracion/Usuarios.aspx',         '', @id_Adm, '2_li_Usuarios',    '', '2', 113, 'A');
GO

/* ===== NIVEL 3 — corden EXACTOS de cada .aspx.vb ===== */
DECLARE @id_Alm_Tab INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Tablas');
DECLARE @id_Alm_Op  INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Operaciones');
DECLARE @id_Alm_Con INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaAlmacen');
DECLARE @id_Alm_Rep INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteAlmacen');
DECLARE @id_Ven_Tab INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_TablasVentas');
DECLARE @id_Ven_Op  INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_Ventas_Operaciones');
DECLARE @id_Ven_Con INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaVenta');
DECLARE @id_Ven_Rep INT = (SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteVenta');

-- ALMACEN > TABLAS (corden del VB)
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'ALMACENES',        '/Tablas/Almacenes.aspx',                '', @id_Alm_Tab, '3_li_Almacenes',      '', '3', 1001, 'A'),
(NULL, 'UNIDAD DE MEDIDA', '/Tablas/UnidadMedida.aspx',             '', @id_Alm_Tab, '3_li_UnidadMediad',   '', '3', 1002, 'A'),
(NULL, 'OPERAC. ALMACEN',  '/Administracion/TiposOperacion.aspx',   '', @id_Alm_Tab, '3_li_TiposOperacion', '', '3', 1003, 'A'),
(NULL, 'FAMILIAS',         '/Tablas/Familias.aspx',                 '', @id_Alm_Tab, '3_li_Familias',       '', '3', 1004, 'A'),
(NULL, 'ARTICULOS',        '/Tablas/Articulos.aspx',                '', @id_Alm_Tab, '3_li_Articulos',      '', '3', 1005, 'A');

-- ALMACEN > OPERACIONES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'INGRESOS DIRECTOS',  '/Operaciones/Ingresos.aspx',          '', @id_Alm_Op, '3_li_Ingresos',       '', '3', 1006, 'A'),
(NULL, 'SALIDAS DIRECTAS',   '/Operaciones/Salida.aspx',            '', @id_Alm_Op, '3_li_Salida',         '', '3', 1007, 'A'),
(NULL, 'TRANSFERENCIAS',     '/Operaciones/Transferencias.aspx',     '', @id_Alm_Op, '3_li_Transferencias', '', '3', 1008, 'A'),
(NULL, 'GUIA DE REMISION',   '/Operaciones/GuiaRemision.aspx',      '', @id_Alm_Op, '3_li_GuiaRemision',   '', '3', 1008, 'A');

-- ALMACEN > CONSULTAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'CONSULTA SALDO',     '/Consultas/ConsultasAlmacen.aspx',    '', @id_Alm_Con, '3_li_Saldo',       '', '3', 1009, 'A'),
(NULL, 'MOV. DE ALMACEN',    '/Consultas/ConsultaOperAlmacen.aspx', '', @id_Alm_Con, '3_li_Almacen',     '', '3', 1010, 'A'),
(NULL, 'CONSULTA ARTICULOS', '/Consultas/ConsultaArticulos.aspx',   '', @id_Alm_Con, '3_li_Articulo',    '', '3', 1011, 'A');

-- ALMACEN > REPORTES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'REPORTE ALMACEN', '/Reportes/ReporteAlmacen.aspx', '', @id_Alm_Rep, '3_li_ReporteAlmacen', '', '3', 1027, 'A'),
(NULL, 'REPORTE SALDO',   '/Reportes/ReporteSaldo.aspx',   '', @id_Alm_Rep, '3_li_ReporteSaldo',   '', '3', 1033, 'A');

-- VENTAS > TABLAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'ASOCIADOS', '/Ventas/Clientes.aspx', '', @id_Ven_Tab, '2_li_Clientes', '', '3', 1012, 'A'),
(NULL, 'PRECIOS',   '/Ventas/Precios.aspx',  '', @id_Ven_Tab, '2_li_Precios',  '', '3', 1013, 'A');

-- VENTAS > OPERACIONES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'APERTURA DE TURNO',    '/Ventas/AperturaCaja.aspx',      '', @id_Ven_Op, '3_Li_Apertura',          '', '3', 1014, 'A'),
(NULL, 'FACTURACION',          '/Ventas/Facturacion.aspx',       '', @id_Ven_Op, '3_li_Facturacion',       '', '3', 1015, 'A'),
(NULL, 'NOTA DE CREDITO',      '/Ventas/NotaCredito.aspx',       '', @id_Ven_Op, '3_li_NotaCredito',       '', '3', 1016, 'A'),
(NULL, 'ANULACION',            '/Ventas/Anulacion.aspx',         '', @id_Ven_Op, '3_Li_Anulacion',         '', '3', 1018, 'A'),
(NULL, 'CIERRE DE TURNO',      '/Ventas/CierreCaja.aspx',        '', @id_Ven_Op, '3_Li_CierreCaja',        '', '3', 1019, 'A'),
(NULL, 'FACT. LISTA PRECIO',   '/Ventas/FacturaListaPrecio.aspx','', @id_Ven_Op, '3_li_FacturaListaPrecio','', '3', 1035, 'A'),
(NULL, 'NOTA DE DEBITO',       '/Ventas/NotaDebito.aspx',        '', @id_Ven_Op, '3_li_NotaDebito',        '', '3', 1036, 'A');

-- VENTAS > CONSULTAS
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'DOCUMENTOS',          '/Consultas/ConsultaDocumento.aspx',          '', @id_Ven_Con, '3_li_Documento',           '', '3', 1020, 'A'),
(NULL, 'COBRANZAS',           '/Consultas/ConsultaFormasPago.aspx',         '', @id_Ven_Con, '3_li_FormaPago',            '', '3', 1022, 'A'),
(NULL, 'MARGEN UTILIDAD DOC', '/Consultas/MargenUtilidad.aspx',             '', @id_Ven_Con, '3_li_MargenUtilidadDoc',    '', '3', 1024, 'A'),
(NULL, 'DOC. ELECTRONICOS',   '/Reportes/ReporteTributario.aspx',           '', @id_Ven_Con, '3_li_ReporteTributario',    '', '3', 1030, 'A'),
(NULL, 'MARGEN UTILIDAD DIA', '/Consultas/ConsultaMargenUtilidadDia.aspx',  '', @id_Ven_Con, '3_li_MargenUtilidadDia',    '', '3', 1031, 'A'),
(NULL, 'ART. MAS VENDIDOS',   '/Consultas/ConsultaArticulosMasVendidos.aspx','',@id_Ven_Con, '3_li_ArticulosMasVendidos', '', '3', 1032, 'A'),
(NULL, 'ALERTA DE STOCK',     '/Consultas/ConsultaStockMinimo.aspx',        '', @id_Ven_Con, '3_li_StockMinimo',          '', '3', 1034, 'A');

-- Páginas con corden < 100 en VB (especiales)
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'NOTA DEBITO VB25',       '/Ventas/NotaDebito.aspx',          '', @id_Ven_Op,  '3_li_NotaDebito2',   '', '3', 25, 'A'),
(NULL, 'VENTAS POR ARTICULO',    '/Consultas/ConsultasVenta.aspx',   '', @id_Ven_Con, '3_li_VentaS',        '', '3', 30, 'A'),
(NULL, 'LISTA PRECIOS',          '/Consultas/ConsultaListPrecio.aspx','',@id_Ven_Con, '3_li_ListaPrecios',   '', '3', 39, 'A'),
(NULL, 'KARDEX',                 '/Consultas/Kardex.aspx',           '', @id_Alm_Con, '3_li_Kardex',         '', '3', 42, 'A'),
(NULL, 'REPORTE KARDEX',         '/Reportes/ReporteKardex.aspx',     '', @id_Alm_Rep, '3_li_ReporteKardex',  '', '3', 43, 'A');

-- VENTAS > REPORTES
INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus) VALUES
(NULL, 'REPORTE VENTAS', '/Reportes/ReporteVenta.aspx', '', @id_Ven_Rep, '3_li_ReporteVenta', '', '3', 1028, 'A'),
(NULL, 'REPORTE TURNO',  '/Reportes/ReporteTurno.aspx', '', @id_Ven_Rep, '3_li_ReporteTurno', '', '3', 1029, 'A');
GO

/* ===== RECREAR ACCESOS — TODOS los corden para el rol ADMIN ===== */
DECLARE @id_rol_admin INT = (SELECT TOP 1 id_rol FROM Roles WHERE cdsc_rol LIKE '%ADMIN%' ORDER BY id_rol);
IF @id_rol_admin IS NULL SET @id_rol_admin = 1;

INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT 'EMP01', @id_rol_admin, corden, '1' FROM Menus;
GO

-- Verificación
SELECT COUNT(*) AS total_menus FROM Menus;
SELECT COUNT(*) AS total_accesos FROM Accesos;
SELECT id_menu, cdsc_menu, corden, nivel, nid_menupadre FROM Menus ORDER BY corden;
GO

PRINT '✓ Menú reconstruido con corden correctos del código VB.';
GO
