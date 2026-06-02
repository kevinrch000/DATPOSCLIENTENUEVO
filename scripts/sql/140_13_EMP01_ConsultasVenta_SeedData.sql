/* PARTE 13: Consultas Venta, Tipo Documento, Datos Empresa, Seed Data */
USE DatPos_EMP01;
GO

/* Consulta ventas (pantalla principal de ventas) */
IF OBJECT_ID('webDatpos_consultarVentasTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarVentasTienda; 
GO
CREATE PROCEDURE webDatpos_consultarVentasTienda @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.cstatus,C.cdsc_coa,C.cdoc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@ccod_caja='' OR F.ccod_caja=@ccod_caja) ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('sp_consultarventadocumento','P') IS NOT NULL DROP PROCEDURE sp_consultarventadocumento; 
GO
CREATE PROCEDURE sp_consultarventadocumento @ccod_cia VARCHAR(20),@cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.nsubtotal,F.nimpuesto,F.nisc,F.ndescuento,F.cstatus,C.cdsc_coa,C.cdoc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@cdoc_seri='' OR F.cdoc=@cdoc_seri) AND (@serie='' OR F.cserie=@serie) AND (@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo) AND (@ccod_coa='' OR F.ccod_coa=@ccod_coa) ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('sp_consultartiposdocumentopago','P') IS NOT NULL DROP PROCEDURE sp_consultartiposdocumentopago; 
GO
CREATE PROCEDURE sp_consultartiposdocumentopago @ccod_cia VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT DISTINCT cdoc_tipo,cdoc_serie FROM NumeradorCaja WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja; END
GO

IF OBJECT_ID('sp_lsconsultartiposdocumentopago','P') IS NOT NULL DROP PROCEDURE sp_lsconsultartiposdocumentopago; 
GO
CREATE PROCEDURE sp_lsconsultartiposdocumentopago @ccod_cia VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT DISTINCT cdoc_tipo,cdoc_serie FROM NumeradorCaja WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja; END
GO

/* Datos empresa para impresión de tickets */
IF OBJECT_ID('webDatpos_DatosEmpresa','P') IS NOT NULL DROP PROCEDURE webDatpos_DatosEmpresa; 
GO
CREATE PROCEDURE webDatpos_DatosEmpresa @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.cnombr,T.cdirec,T.ctelef,T.cmail,CG.ilogo,CG.nigv
    FROM Tiendas T LEFT JOIN ConfigGeneral CG ON CG.ccod_cia=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia;
END
GO

/* webDatpos_DiasRestantes — consulta la licencia del tenant en DatPosAdmin */
-- NOTA: Este SP ya existe en DatPosAdmin; el cliente lo llama via selectstored (no OtraConexion).

/* Consultas para SUNAT / Factura electrónica */
IF OBJECT_ID('sp_actualizarestadotributario','P') IS NOT NULL DROP PROCEDURE sp_actualizarestadotributario; 
GO
CREATE PROCEDURE sp_actualizarestadotributario @id_cbfact INT,@ccod_cia VARCHAR(20),@cstatus_tributario VARCHAR(5),@xml VARBINARY(MAX),@xml_cdr VARBINARY(MAX)
AS BEGIN SET NOCOUNT ON; UPDATE CbFactura SET cstatus_tributario=@cstatus_tributario,xml=ISNULL(@xml,xml),xml_cdr=ISNULL(@xml_cdr,xml_cdr) WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; END
GO

/* ===================================================================
   SEED DATA: datos iniciales para que el sistema funcione desde login
=================================================================== */

/* Unidades de medida SUNAT base */
IF NOT EXISTS (SELECT 1 FROM UnidadMedida WHERE ccod_cia='EMP01')
INSERT INTO UnidadMedida(ccod_cia,ccod_unimed,cdsc_unimed,cstatus) VALUES
('EMP01','NIU','UNIDAD','A'),('EMP01','KGM','KILOGRAMO','A'),('EMP01','LTR','LITRO','A'),
('EMP01','MTR','METRO','A'),('EMP01','BLT','BOLSA','A'),('EMP01','CJA','CAJA','A'),
('EMP01','DZN','DOCENA','A'),('EMP01','GRM','GRAMO','A'),('EMP01','PQT','PAQUETE','A');
GO

/* Tipo de operación base */
IF NOT EXISTS (SELECT 1 FROM TipoOperacion WHERE ccod_cia='EMP01')
INSERT INTO TipoOperacion(ccod_cia,ccod_tipoper,cdsc_tipoper,ctipo_flag,cstatus) VALUES
('EMP01','COMP','COMPRA / INGRESO','I','A'),
('EMP01','AJIN','AJUSTE DE INGRESO','I','A'),
('EMP01','VENT','VENTA / SALIDA','S','A'),
('EMP01','AJER','AJUSTE DE EGRESO','S','A'),
('EMP01','TRAN','TRANSFERENCIA ENTRE ALMACENES','T','A');
GO

/* Configuración general base */
IF NOT EXISTS (SELECT 1 FROM ConfigGeneral WHERE ccod_cia='EMP01')
INSERT INTO ConfigGeneral(ccod_cia,ccod_clibol,coper_ingreso,coper_salida,ctipo_flag_ingreso,ctipo_flag_salida,nigv,nisc,nmonto_maxboleta,ccod_usuario)
VALUES('EMP01','CLI001','COMP','VENT','I','S',18,0,700,'ADMIN');
GO

/* Cliente por defecto (para boletas sin identificar) */
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='CLI001')
INSERT INTO Coa(ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cstatus,cproveedor,cruc_coa)
VALUES('EMP01','CLI001','00000000','CLIENTES VARIOS','1','A','0','00000000');
GO

/* Rol Administrador base */
IF NOT EXISTS (SELECT 1 FROM Roles WHERE ccod_empresa='EMP01')
INSERT INTO Roles(ccod_empresa,cdsc_rol,cstatus) VALUES('EMP01','ADMINISTRADOR','A'),('EMP01','CAJERO','A'),('EMP01','ALMACEN','A');
GO

/* Accesos totales para rol Administrador (id_rol=1 = todos los menús) */
INSERT INTO Accesos(ccod_empresa,id_rol,corden,cstatus)
SELECT 'EMP01',1,corden,'1' FROM Menus WHERE (ccod_empresa='EMP01' OR ccod_empresa IS NULL)
AND NOT EXISTS (SELECT 1 FROM Accesos A2 WHERE A2.ccod_empresa='EMP01' AND A2.id_rol=1 AND A2.corden=Menus.corden);
GO

/* Tienda, almacén y caja demo */
IF NOT EXISTS (SELECT 1 FROM Tiendas WHERE ccod_cia='EMP01' AND ccod_tiend='T001')
INSERT INTO Tiendas(ccod_cia,ccod_tiend,cnombr,cdirec,cstatus,nlista_pre_normal,nlista_pre_preferencial) VALUES('EMP01','T001','TIENDA PRINCIPAL','AV. PRINCIPAL 123','A',1,1);
GO
IF NOT EXISTS (SELECT 1 FROM Almacenes WHERE ccod_cia='EMP01' AND ccod_alm='ALM01')
INSERT INTO Almacenes(ccod_cia,ccod_alm,cdsc_alm,cstatus) VALUES('EMP01','ALM01','ALMACEN PRINCIPAL','A');
GO
IF NOT EXISTS (SELECT 1 FROM Cajas WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01')
INSERT INTO Cajas(ccod_cia,ccod_caja,cdsc_caja,cstatus) VALUES('EMP01','CAJ01','CAJA 1','A');
GO
IF NOT EXISTS (SELECT 1 FROM TiendaAlmacen WHERE ccod_cia='EMP01' AND ccod_tiend='T001' AND ccod_alm='ALM01')
INSERT INTO TiendaAlmacen(ccod_cia,ccod_tiend,ccod_alm) VALUES('EMP01','T001','ALM01');
GO
IF NOT EXISTS (SELECT 1 FROM TiendaCaja WHERE ccod_cia='EMP01' AND ccod_tiend='T001' AND ccod_caja='CAJ01')
INSERT INTO TiendaCaja(ccod_cia,ccod_tiend,ccod_caja,ccod_usuario) VALUES('EMP01','T001','CAJ01','ADMIN');
GO

/* Numeradores de caja demo (boleta y factura) */
IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01')
INSERT INTO NumeradorCaja(ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro) VALUES
('EMP01','CAJ01','B','B001',0),('EMP01','CAJ01','F','F001',0),('EMP01','CAJ01','NC','BC01',0);
GO

/* Numerador de almacén demo */
IF NOT EXISTS (SELECT 1 FROM NumeradorAlmacen WHERE ccod_cia='EMP01' AND ccod_alm='ALM01')
INSERT INTO NumeradorAlmacen(ccod_cia,ccod_alm,ctip_doc,cserie,nnumero) VALUES
('EMP01','ALM01','I','ALM',0),('EMP01','ALM01','S','ALM',0),('EMP01','ALM01','T','ALM',0);
GO

/* Usuario ADMIN del tenant */
IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa='EMP01' AND ccod_usuario='ADMIN')
INSERT INTO Usuarios(ccod_empresa,ccod_usuario,cdsc_usuario,cpassw,id_rol,ccod_tiend,ccod_almacen,ccod_caja,cperm_descn,id_estado)
VALUES('EMP01','ADMIN','ADMINISTRADOR','e10adc3949ba59abbe56e057f20f883e',1,'T001','ALM01','CAJ01','100',1);
-- cpassw = MD5('123456') = e10adc3949ba59abbe56e057f20f883e
GO

/* Lista de precios base */
IF NOT EXISTS (SELECT 1 FROM CbListaPrecio WHERE ccod_cia='EMP01')
INSERT INTO CbListaPrecio(ccod_cia,ccod_cblistpre,cdsc_cblistpre,dfch_ini,dfch_fin,cstatus) VALUES
('EMP01','LP001','LISTA DE PRECIO NORMAL','2024-01-01','2099-12-31','A'),
('EMP01','LP002','LISTA DE PRECIO PREFERENCIAL','2024-01-01','2099-12-31','A');
GO

PRINT '✓ Script FINAL completado. Sistema listo para operar.';
GO
