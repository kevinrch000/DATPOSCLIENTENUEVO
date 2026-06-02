/* =====================================================================
   FIX 42 — MENÚ DEFINITIVO compatible con FK_Menus_MenuPadre
   
   Problema: Script 240 inserta nid_menupadre=0 para menús raíz.
   Script 690 agregó FK_Menus_MenuPadre (nid_menupadre → id_menu).
   0 no existe como id_menu → INSERT falla → Menus queda vacío.
   
   Solución: igual que script 240 pero nid_menupadre=NULL para
   los 3 menús raíz (Almacén, Ventas, Administración).
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* Deshabilitar FK temporalmente para poder limpiar y reinsertar */
IF OBJECT_ID('FK_Menus_MenuPadre') IS NOT NULL
    ALTER TABLE Menus NOCHECK CONSTRAINT FK_Menus_MenuPadre;
GO

DELETE FROM Accesos;
DELETE FROM Menus;
DBCC CHECKIDENT ('Menus', RESEED, 0);
GO

/* Re-habilitar FK */
IF OBJECT_ID('FK_Menus_MenuPadre') IS NOT NULL
    ALTER TABLE Menus CHECK CONSTRAINT FK_Menus_MenuPadre;
GO

-- NIVEL 1: menús raíz — nid_menupadre=NULL (compatible con FK)
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'ALMACEN','#','/Styles/img/icon/icono_almacen.png',NULL,'1_li_Almacen','1_ul_Almacen','1',1,'A'),
(NULL,'VENTAS','#','/Styles/img/icon/icono_ventas.png',NULL,'1_li_Ventas','1_ul_Ventas','1',2,'A'),
(NULL,'ADMINISTRACION','#','/Styles/img/icon/icono_adm.png',NULL,'1_li_Administracion','1_ul_Administracion','1',3,'A');
GO

DECLARE @A INT=(SELECT id_menu FROM Menus WHERE cli_menu='1_li_Almacen');
DECLARE @V INT=(SELECT id_menu FROM Menus WHERE cli_menu='1_li_Ventas');
DECLARE @D INT=(SELECT id_menu FROM Menus WHERE cli_menu='1_li_Administracion');

-- NIVEL 2 sub-categorías
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'TABLAS','#','',@A,'2_li_Tablas','2_ul_Tablas','2',150,'A'),
(NULL,'OPERACIONES','#','',@A,'2_li_Operaciones','2_ul_Operaciones','2',151,'A'),
(NULL,'CONSULTAS','#','',@A,'2_li_ConsultaAlmacen','2_ul_ConsultaAlmacen','2',152,'A'),
(NULL,'REPORTES','#','',@A,'2_li_ReporteAlmacen','2_ul_ReporteAlmacen','2',153,'A'),
(NULL,'TABLAS','#','',@V,'2_li_TablasVentas','2_ul_TablasVentas','2',160,'A'),
(NULL,'OPERACIONES','#','',@V,'2_li_Ventas_Operaciones','2_ul_Ventas_Operaciones','2',161,'A'),
(NULL,'CONSULTAS','#','',@V,'2_li_ConsultaVenta','2_ul_ConsultaVenta','2',162,'A'),
(NULL,'REPORTES','#','',@V,'2_li_ReporteVenta','2_ul_ReporteVenta','2',163,'A'),
-- Admin hijos directos (nivel 2)
(NULL,'CONFIG. GENERALES','/Consultas/ConfigGeneral.aspx','',@D,'2_li_ConfGeneral','','2',109,'A'),
(NULL,'TIENDAS','/Administracion/Tiendas.aspx','',@D,'2_li_Tiendas','','2',110,'A'),
(NULL,'CAJAS','/Administracion/Cajas.aspx','',@D,'2_li_Cajas','','2',111,'A'),
(NULL,'ROLES','/Administracion/Roles.aspx','',@D,'2_li_Roles','','2',112,'A'),
(NULL,'USUARIOS','/Administracion/Usuarios.aspx','',@D,'2_li_Usuarios','','2',113,'A');
GO

DECLARE @AT INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_Tablas');
DECLARE @AO INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_Operaciones');
DECLARE @AC INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaAlmacen');
DECLARE @AR INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteAlmacen');
DECLARE @VT INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_TablasVentas');
DECLARE @VO INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_Ventas_Operaciones');
DECLARE @VC INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_ConsultaVenta');
DECLARE @VR INT=(SELECT id_menu FROM Menus WHERE cli_menu='2_li_ReporteVenta');

-- NIVEL 3: ALMACEN > TABLAS
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'ALMACENES','/Tablas/Almacenes.aspx','',@AT,'3_li_Almacenes','','3',1001,'A'),
(NULL,'UNIDAD DE MEDIDA','/Tablas/UnidadMedida.aspx','',@AT,'3_li_UnidadMediad','','3',1002,'A'),
(NULL,'OPERAC. ALMACEN','/Administracion/TiposOperacion.aspx','',@AT,'3_li_TiposOperacion','','3',1003,'A'),
(NULL,'FAMILIAS','/Tablas/Familias.aspx','',@AT,'3_li_Familias','','3',1004,'A'),
(NULL,'ARTICULOS','/Tablas/Articulos.aspx','',@AT,'3_li_Articulos','','3',1005,'A');
-- ALMACEN > OPERACIONES
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'INGRESOS DIRECTOS','/Operaciones/Ingresos.aspx','',@AO,'3_li_Ingresos','','3',1006,'A'),
(NULL,'SALIDAS DIRECTAS','/Operaciones/Salida.aspx','',@AO,'3_li_Salida','','3',1007,'A'),
(NULL,'TRANSFERENCIAS','/Operaciones/Transferencias.aspx','',@AO,'3_li_Transferencias','','3',1008,'A'),
(NULL,'GUIA DE REMISION','/Operaciones/GuiaRemision.aspx','',@AO,'3_li_GuiaRemision','','3',1025,'A');
-- ALMACEN > CONSULTAS
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'CONSULTA SALDO','/Consultas/ConsultasAlmacen.aspx','',@AC,'3_li_Saldo','','3',1009,'A'),
(NULL,'MOV. DE ALMACEN','/Consultas/ConsultaOperAlmacen.aspx','',@AC,'3_li_Almacen','','3',1010,'A'),
(NULL,'CONSULTA ARTICULOS','/Consultas/ConsultaArticulos.aspx','',@AC,'3_li_Articulo','','3',1011,'A'),
(NULL,'KARDEX','/Consultas/Kardex.aspx','',@AC,'3_li_Kardex','','3',1042,'A'),
(NULL,'ALERTA DE STOCK','/Consultas/ConsultaStockMinimo.aspx','',@AC,'3_li_StockMinimo','','3',1034,'A');
-- ALMACEN > REPORTES
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'REPORTE ALMACEN','/Reportes/ReporteAlmacen.aspx','',@AR,'3_li_ReporteAlmacen','','3',1027,'A'),
(NULL,'REPORTE KARDEX','/Reportes/ReporteKardex.aspx','',@AR,'3_li_ReporteKardex','','3',1043,'A'),
(NULL,'REPORTE SALDO','/Reportes/ReporteSaldo.aspx','',@AR,'3_li_ReporteSaldo','','3',1033,'A');
-- VENTAS > TABLAS
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'ASOCIADOS','/Ventas/Clientes.aspx','',@VT,'2_li_Clientes','','3',1012,'A'),
(NULL,'PRECIOS','/Ventas/Precios.aspx','',@VT,'2_li_Precios','','3',1013,'A');
-- VENTAS > OPERACIONES
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'APERTURA DE TURNO','/Ventas/AperturaCaja.aspx','',@VO,'3_Li_Apertura','','3',1014,'A'),
(NULL,'FACTURACION','/Ventas/Facturacion.aspx','',@VO,'3_li_Facturacion','','3',1015,'A'),
(NULL,'NOTA DE CREDITO','/Ventas/NotaCredito.aspx','',@VO,'3_li_NotaCredito','','3',1016,'A'),
(NULL,'ANULACION','/Ventas/Anulacion.aspx','',@VO,'3_Li_Anulacion','','3',1018,'A'),
(NULL,'CIERRE DE TURNO','/Ventas/CierreCaja.aspx','',@VO,'3_Li_CierreCaja','','3',1019,'A'),
(NULL,'NOTA DE DEBITO','/Ventas/NotaDebito.aspx','',@VO,'3_li_NotaDebito','','3',1036,'A'),
(NULL,'FACT. LISTA PRECIO','/Ventas/FacturaListaPrecio.aspx','',@VO,'3_li_FacturaListaPrecio','','3',1035,'A');
-- VENTAS > CONSULTAS
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'DOCUMENTOS','/Consultas/ConsultaDocumento.aspx','',@VC,'3_li_Documento','','3',1020,'A'),
(NULL,'COBRANZAS','/Consultas/ConsultaFormasPago.aspx','',@VC,'3_li_FormaPago','','3',1022,'A'),
(NULL,'MARGEN UTILIDAD DOC','/Consultas/MargenUtilidad.aspx','',@VC,'3_li_MargenUtilidadDoc','','3',1024,'A'),
(NULL,'VENTAS POR ARTICULO','/Consultas/ConsultasVenta.aspx','',@VC,'3_li_VentaS','','3',1040,'A'),
(NULL,'LISTA PRECIOS','/Consultas/ConsultaListPrecio.aspx','',@VC,'3_li_ListaPrecios','','3',1041,'A'),
(NULL,'DOC. ELECTRONICOS','/Reportes/ReporteTributario.aspx','',@VC,'3_li_ReporteTributario','','3',1030,'A'),
(NULL,'MARGEN UTILIDAD DIA','/Consultas/ConsultaMargenUtilidadDia.aspx','',@VC,'3_li_MargenUtilidadDia','','3',1031,'A'),
(NULL,'ART. MAS VENDIDOS','/Consultas/ConsultaArticulosMasVendidos.aspx','',@VC,'3_li_ArticulosMasVendidos','','3',1032,'A');
-- VENTAS > REPORTES
INSERT INTO Menus (ccod_empresa,cdsc_menu,curl_href,curl_src,nid_menupadre,cli_menu,cul_menu,nivel,corden,cstatus) VALUES
(NULL,'REPORTE VENTAS','/Reportes/ReporteVenta.aspx','',@VR,'3_li_ReporteVenta','','3',1028,'A'),
(NULL,'REPORTE TURNO','/Reportes/ReporteTurno.aspx','',@VR,'3_li_ReporteTurno','','3',1029,'A');
GO

/* === ACCESOS: todos los corden de Menus + extras para páginas con corden diferente en VB === */
DECLARE @rol INT=(SELECT TOP 1 id_rol FROM Roles WHERE cdsc_rol LIKE '%ADMIN%' ORDER BY id_rol);
IF @rol IS NULL SET @rol=1;

-- Todos los menús
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) SELECT 'EMP01',@rol,corden,'1' FROM Menus;

-- Extras: páginas VB que verifican con corden diferente al del menú
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) VALUES('EMP01',@rol,25,'1'); -- NotaDebito
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) VALUES('EMP01',@rol,30,'1'); -- ConsultasVenta
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) VALUES('EMP01',@rol,39,'1'); -- ConsultaListPrecio
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) VALUES('EMP01',@rol,42,'1'); -- Kardex
INSERT INTO Accesos (ccod_empresa,id_rol,corden,cstatus) VALUES('EMP01',@rol,43,'1'); -- ReporteKardex
GO

/* Asegurar usuarios con id_rol correcto */
UPDATE Usuarios
SET id_rol=1, cperm_descn='100'
WHERE ccod_empresa='EMP01' AND (id_rol IS NULL OR id_rol <> 1 OR cperm_descn IS NULL);
GO

/* VERIFICACIÓN */
SELECT COUNT(*) AS total_menus   FROM Menus;
SELECT COUNT(*) AS total_accesos FROM Accesos WHERE ccod_empresa='EMP01' AND id_rol=1;
SELECT ccod_usuario, id_rol, cperm_descn FROM Usuarios WHERE ccod_empresa='EMP01';
GO

PRINT 'OK - FIX 42: Menu definitivo compatible con FK_Menus_MenuPadre.';
GO
