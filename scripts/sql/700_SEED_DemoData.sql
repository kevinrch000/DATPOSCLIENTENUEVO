/* =====================================================================
   SEED 41 — DATOS DEMO COMPLETOS
   
   Hace que el sistema funcione al 100% con datos realistas.
   
   CAMBIOS:
   1. ConfigGeneral — coper_ingreso/salida + ccod_clibol  (CRITICO)
   2. Corregir Coa.CLI001.ctipo_coa  '1' → 'CL'
   3. Sincronizar DatPosAdmin.Usuarios (admin) con los códigos de EMP01
   4. Numeradores: CAJ01 y transferencias en ALM001
   5. Nuevas familias (8 total)
   6. Nuevos artículos (20 total, productos bodega realistas Peru)
   7. Stock para todos los artículos en ALM001
   8. Precios en ambas listas (L001 normal / L002 preferencial)
   9. Clientes y proveedores reales para facturas
  10. Usuario cajero adicional (DatPosAdmin + EMP01)
   ===================================================================== */

/* ------------------------------------------------------------------ */
PRINT '========================================================'
PRINT ' SEED 41 - DATOS DEMO DATPOS'
PRINT '========================================================'
PRINT ''

/* ====================================================================
   BLOQUE 1: ConfigGeneral (CRITICO)
   Sin estos valores: los SPs de ingreso/salida retornan NULL y el
   sistema no puede generar movimientos de almacen ni boletas.
   ==================================================================== */
PRINT 'BLOQUE 1: ConfigGeneral ...'

USE DatPos_EMP01;
GO

UPDATE ConfigGeneral
SET
    coper_ingreso      = 'COMP',   -- TipoOperacion COMP = COMPRA / INGRESO
    coper_salida       = 'VENT',   -- TipoOperacion VENT = VENTA / SALIDA
    ccod_clibol        = 'CLI000', -- Consumidor Final (para boletas sin RUC)
    ctipo_flag_ingreso = 'I',
    ctipo_flag_salida  = 'S',
    nisc               = 0
WHERE ccod_cia = 'EMP01';

PRINT '  -> ConfigGeneral actualizado.'
GO

/* ====================================================================
   BLOQUE 2: Correccion Coa.CLI001
   CLI001 tenia ctipo_coa='1' (invalido). El sistema espera 'CL'.
   ==================================================================== */
PRINT 'BLOQUE 2: Correccion Coa.CLI001 ...'

USE DatPos_EMP01;
GO

UPDATE Coa SET ctipo_coa = 'CL' WHERE ccod_cia = 'EMP01' AND ccod_coa = 'CLI001' AND ctipo_coa <> 'CL';
PRINT '  -> Coa.CLI001.ctipo_coa corregido.'
GO

/* ====================================================================
   BLOQUE 3: Sincronizar DatPosAdmin.Usuarios (admin)
   El usuario admin en DatPosAdmin tenia ALM01/CAJ01 pero en EMP01
   usa ALM001/CAJ01.  Sincronizamos para que la sesion sea consistente.
   ==================================================================== */
PRINT 'BLOQUE 3: Sincronizar DatPosAdmin.Usuarios admin ...'

USE DatPosAdmin;
GO

UPDATE Usuarios
SET ccod_almacen = 'ALM001',
    ccod_caja    = 'CAJ01',
    ccod_tiend   = 'T001'
WHERE ccod_usuario = 'admin' AND ccod_empresa = 'EMP01';

PRINT '  -> DatPosAdmin.Usuarios admin sincronizado (ALM001 / CAJ01).'
GO

/* ====================================================================
   BLOQUE 4: Numeradores para CAJ01
   CAJ01 existe en TiendaCaja pero sin numeradores.
   ==================================================================== */
PRINT 'BLOQUE 4: Numeradores CAJ01 ...'

USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='BV')
    INSERT INTO NumeradorCaja (ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro,cdsc_numer,dfch_crea)
    VALUES ('EMP01','CAJ01','BV','B002',1,'BOLETA DE VENTA',GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='FA')
    INSERT INTO NumeradorCaja (ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro,cdsc_numer,dfch_crea)
    VALUES ('EMP01','CAJ01','FA','F002',1,'FACTURA',GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='NV')
    INSERT INTO NumeradorCaja (ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro,cdsc_numer,dfch_crea)
    VALUES ('EMP01','CAJ01','NV','T002',1,'NOTA DE VENTA',GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='NC')
    INSERT INTO NumeradorCaja (ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro,cdsc_numer,dfch_crea)
    VALUES ('EMP01','CAJ01','NC','NC02',1,'NOTA DE CREDITO',GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='ND')
    INSERT INTO NumeradorCaja (ccod_cia,ccod_caja,cdoc_tipo,cdoc_serie,cdoc_nro,cdsc_numer,dfch_crea)
    VALUES ('EMP01','CAJ01','ND','ND02',1,'NOTA DE DEBITO',GETDATE());

PRINT '  -> NumeradorCaja CAJ01 creado.'
GO

/* ====================================================================
   BLOQUE 5: Numerador de transferencias para ALM001
   ALM001 tenia solo I (ingreso) y S (salida).  Falta T (transferencia).
   ==================================================================== */
PRINT 'BLOQUE 5: Numerador transferencias ALM001 ...'

USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT 1 FROM NumeradorAlmacen WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ctip_doc='T')
    INSERT INTO NumeradorAlmacen (ccod_cia,ccod_alm,ctip_doc,cserie,nnumero,cdsc_numeralmacen,ccod_usuario,dfch_crea)
    VALUES ('EMP01','ALM001','T','T001',0,'Numerador Transferencia','ADMIN',GETDATE());

PRINT '  -> NumeradorAlmacen transferencia ALM001 creado.'
GO

/* ====================================================================
   BLOQUE 6: Familias adicionales
   ==================================================================== */
PRINT 'BLOQUE 6: Familias ...'

USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01' AND ccod_lin='FAM005')
    INSERT INTO Familias (ccod_cia,ccod_lin,cdsc_lin,cstatus,ccod_usuario,dfch_crea)
    VALUES ('EMP01','FAM005','LACTEOS Y DERIVADOS','A','ADMIN',GETDATE());

IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01' AND ccod_lin='FAM006')
    INSERT INTO Familias (ccod_cia,ccod_lin,cdsc_lin,cstatus,ccod_usuario,dfch_crea)
    VALUES ('EMP01','FAM006','HIGIENE PERSONAL','A','ADMIN',GETDATE());

IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01' AND ccod_lin='FAM007')
    INSERT INTO Familias (ccod_cia,ccod_lin,cdsc_lin,cstatus,ccod_usuario,dfch_crea)
    VALUES ('EMP01','FAM007','LIMPIEZA DEL HOGAR','A','ADMIN',GETDATE());

IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01' AND ccod_lin='FAM008')
    INSERT INTO Familias (ccod_cia,ccod_lin,cdsc_lin,cstatus,ccod_usuario,dfch_crea)
    VALUES ('EMP01','FAM008','PANADERIA Y REPOSTERIA','A','ADMIN',GETDATE());

PRINT '  -> 4 familias nuevas insertadas.'
GO

/* ====================================================================
   BLOQUE 7: Articulos (20 total)
   Estructura:  ccod_cia, ccod_articulo, cdsc_articulo, ccod_lin, uni_medi,
                cstatus, ctip_articulo, cigv, cisc, bprefer, dfch_crea
   cigv='18'  (18% IGV Peru), ctip_articulo='P' (producto)
   ==================================================================== */
PRINT 'BLOQUE 7: Articulos ...'

USE DatPos_EMP01;
GO

-- ---- BEBIDAS (FAM001) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART006')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART006','CERVEZA PILSEN 620ML','FAM001','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART007')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART007','GASEOSA PEPSI 1.5L','FAM001','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART008')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART008','JUGO GLORIA PIÑA 1L','FAM001','NIU','A','P','18','0',0,GETDATE());

-- ---- COMIDAS / ALIMENTOS (FAM002) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART009')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART009','ARROZ COSTEÑO 1KG','FAM002','KGM','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART010')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART010','ACEITE PRIMOR 1L','FAM002','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART011')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART011','FIDEOS LAVAGGI 500G','FAM002','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART012')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART012','AZUCAR RUBIA 1KG','FAM002','KGM','A','P','18','0',0,GETDATE());

-- ---- SNACKS (FAM003) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART013')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART013','GALLETAS OREO PACK','FAM003','PQT','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART014')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART014','CHIFLES KARINTO 80G','FAM003','NIU','A','P','18','0',0,GETDATE());

-- ---- LACTEOS (FAM005) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART015')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART015','LECHE GLORIA ENTERA 400G','FAM005','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART016')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART016','YOGURT GLORIA VAINILLA 1KG','FAM005','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART017')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART017','MANTEQUILLA LAIVE 200G','FAM005','NIU','A','P','18','0',0,GETDATE());

-- ---- HIGIENE PERSONAL (FAM006) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART018')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART018','SHAMPOO HEAD & SHOULDERS 200ML','FAM006','NIU','A','P','18','0',0,GETDATE());

IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART019')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART019','JABON CAMAY 125G','FAM006','NIU','A','P','18','0',0,GETDATE());

-- ---- LIMPIEZA HOGAR (FAM007) ----
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01' AND ccod_articulo='ART020')
    INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,ctip_articulo,cigv,cisc,bprefer,dfch_crea)
    VALUES ('EMP01','ART020','DETERGENTE ARIEL 500G','FAM007','NIU','A','P','18','0',0,GETDATE());

PRINT '  -> 15 articulos nuevos insertados (ART006-ART020).'
GO

/* ====================================================================
   BLOQUE 8: Stock para articulos nuevos en ALM001
   ncantidad = cantidad actual, ncosto = precio de costo promedio
   ==================================================================== */
PRINT 'BLOQUE 8: Stock en ALM001 ...'

USE DatPos_EMP01;
GO

-- Bebidas
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART006')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART006',120.00,4.20,GETDATE());    -- Cerveza Pilsen

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART007')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART007',60.00,3.50,GETDATE());     -- Pepsi 1.5L

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART008')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART008',80.00,2.90,GETDATE());     -- Jugo Gloria

-- Alimentos
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART009')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART009',200.00,3.20,GETDATE());    -- Arroz

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART010')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART010',50.00,6.00,GETDATE());     -- Aceite Primor

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART011')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART011',100.00,2.00,GETDATE());    -- Fideos

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART012')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART012',150.00,2.40,GETDATE());    -- Azucar

-- Snacks
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART013')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART013',75.00,2.80,GETDATE());     -- Galletas Oreo

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART014')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART014',90.00,1.00,GETDATE());     -- Chifles

-- Lacteos
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART015')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART015',60.00,2.50,GETDATE());     -- Leche Gloria

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART016')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART016',40.00,5.50,GETDATE());     -- Yogurt Gloria

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART017')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART017',30.00,5.20,GETDATE());     -- Mantequilla

-- Higiene
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART018')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART018',25.00,9.00,GETDATE());     -- Shampoo H&S

IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART019')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART019',48.00,1.80,GETDATE());     -- Jabon Camay

-- Limpieza
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01' AND ccod_alm='ALM001' AND ccod_articulo='ART020')
    INSERT INTO Stock (ccod_cia,ccod_alm,ccod_articulo,ncantidad,ncosto,dfch_crea)
    VALUES ('EMP01','ALM001','ART020',35.00,6.50,GETDATE());     -- Detergente Ariel

PRINT '  -> Stock en ALM001 para ART006-ART020 cargado.'
GO

/* ====================================================================
   BLOQUE 9: Precios en Listas (LnListaPrecio)
   L001 = precio normal  |  L002 = precio preferencial (descuento ~10%)
   Columnas: ccod_cia, ccod_cblistpre, ccod_articulo, npre_uni
   ==================================================================== */
PRINT 'BLOQUE 9: Precios en listas ...'

USE DatPos_EMP01;
GO

-- Helper para no repetir codigo:
-- L001 precios normales
IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART006')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART006',5.50,0,0);   -- Cerveza Pilsen

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART007')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART007',4.50,0,0);   -- Pepsi 1.5L

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART008')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART008',3.80,0,0);   -- Jugo Gloria

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART009')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART009',4.50,0,0);   -- Arroz

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART010')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART010',8.00,0,0);   -- Aceite Primor

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART011')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART011',2.80,0,0);   -- Fideos

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART012')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART012',3.20,0,0);   -- Azucar

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART013')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART013',4.00,0,0);   -- Galletas Oreo

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART014')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART014',1.50,0,0);   -- Chifles

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART015')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART015',3.50,0,0);   -- Leche Gloria

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART016')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART016',7.50,0,0);   -- Yogurt Gloria

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART017')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART017',7.00,0,0);   -- Mantequilla

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART018')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART018',12.50,0,0);  -- Shampoo H&S

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART019')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART019',2.50,0,0);   -- Jabon Camay

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP001' AND ccod_articulo='ART020')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP001','ART020',8.50,0,0);   -- Detergente Ariel

-- L002 precios preferenciales (~10% menos)
IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART006')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART006',5.00,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART007')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART007',4.00,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART008')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART008',3.50,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART009')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART009',4.00,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART010')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART010',7.50,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART011')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART011',2.50,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART012')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART012',2.90,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART013')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART013',3.60,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART014')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART014',1.30,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART015')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART015',3.20,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART016')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART016',7.00,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART017')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART017',6.50,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART018')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART018',11.50,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART019')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART019',2.20,0,0);

IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01' AND ccod_cblistpre='LP002' AND ccod_articulo='ART020')
    INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min)
    VALUES ('EMP01','LP002','ART020',8.00,0,0);

PRINT '  -> Precios en L001 y L002 para ART006-ART020 cargados.'
GO

/* ====================================================================
   BLOQUE 10: Clientes y Proveedores (Coa)
   Columnas clave: ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, ctipo_coa,
                   cproveedor, cstatus, cruc_coa, ccod_usuario, dfch_crea
   ctipo_coa = 'CL' (cliente), 'PN' (persona natural), 'JR' (juridica)
   cproveedor = '1' (es proveedor) / '0' (no es proveedor)
   ==================================================================== */
PRINT 'BLOQUE 10: Clientes y proveedores ...'

USE DatPos_EMP01;
GO

-- Cliente persona juridica (para facturas)
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='CLI002')
    INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cproveedor,cstatus,cruc_coa,ccod_usuario,dfch_crea)
    VALUES ('EMP01','CLI002','RUC','EMPRESA DISTRIBUIDORA SAC','CL','0','A','20123456789','ADMIN',GETDATE());

-- Cliente persona natural (para facturas con DNI)
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='CLI003')
    INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cproveedor,cstatus,cruc_coa,ccod_usuario,dfch_crea)
    VALUES ('EMP01','CLI003','DNI','JUAN PEREZ GARCIA','PN','0','A','12345678','ADMIN',GETDATE());

IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='CLI004')
    INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cproveedor,cstatus,cruc_coa,ccod_usuario,dfch_crea)
    VALUES ('EMP01','CLI004','DNI','MARIA GARCIA LOPEZ','PN','0','A','87654321','ADMIN',GETDATE());

-- Proveedores
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='PRV001')
    INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cproveedor,cstatus,cruc_coa,ccod_usuario,dfch_crea)
    VALUES ('EMP01','PRV001','RUC','DISTRIBUIDORA BEBIDAS SRL','CL','1','A','20987654321','ADMIN',GETDATE());

IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='PRV002')
    INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctipo_coa,cproveedor,cstatus,cruc_coa,ccod_usuario,dfch_crea)
    VALUES ('EMP01','PRV002','RUC','ALIMENTOS PERU SAC','CL','1','A','20111222333','ADMIN',GETDATE());

PRINT '  -> 4 clientes/proveedores nuevos insertados.'
GO

/* ====================================================================
   BLOQUE 11: Usuario CAJERO
   Se agrega a DatPosAdmin (para login) y a DatPos_EMP01 (permisos).
   Usa las mismas credenciales: usuario=cajero / clave=123456
   ==================================================================== */
PRINT 'BLOQUE 11: Usuario cajero ...'

USE DatPosAdmin;
GO

IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_usuario='cajero' AND ccod_empresa='EMP01')
    INSERT INTO Usuarios (ccod_usuario,cpassw,cdsc_usuario,id_rol,ccod_empresa,id_estado,
                          ccod_tiend,ccod_almacen,ccod_caja,dfch_crea)
    VALUES ('cajero','123456','CAJERO',1,'EMP01',1,'T001','ALM001','CAJ01',GETDATE());

PRINT '  -> DatPosAdmin: usuario cajero creado.'
GO

USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa='EMP01' AND ccod_usuario='cajero')
    INSERT INTO Usuarios (ccod_empresa,ccod_usuario,cdsc_usuario,cpassw,id_rol,
                          ccod_tiend,ccod_almacen,ccod_caja,cperm_descn,id_estado,dfch_crea)
    VALUES ('EMP01','cajero','CAJERO','123456',1,'T001','ALM001','CAJ01','100',1,GETDATE());

PRINT '  -> DatPos_EMP01: usuario cajero creado.'
GO

/* ====================================================================
   VERIFICACION FINAL
   ==================================================================== */
PRINT ''
PRINT '=== VERIFICACION FINAL ==='

USE DatPos_EMP01;
GO

SELECT 'Articulos'    AS tabla, COUNT(*) AS filas FROM Articulos    WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Stock',                  COUNT(*)          FROM Stock        WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'LnListaPrecio',          COUNT(*)          FROM LnListaPrecio WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Familias',               COUNT(*)          FROM Familias     WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Coa (cli+prv)',          COUNT(*)          FROM Coa          WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'NumeradorCaja',          COUNT(*)          FROM NumeradorCaja WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'NumeradorAlmacen',       COUNT(*)          FROM NumeradorAlmacen WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Usuarios EMP01',         COUNT(*)          FROM Usuarios     WHERE ccod_empresa='EMP01';

SELECT 'ConfigGeneral' AS check_cfg,
    coper_ingreso, coper_salida, ccod_clibol, nigv
FROM ConfigGeneral WHERE ccod_cia='EMP01';
GO

USE DatPosAdmin;
GO
SELECT 'Usuarios Admin' AS tabla, COUNT(*) AS filas FROM Usuarios WHERE ccod_empresa='EMP01';
GO

PRINT '=== SEED 41 COMPLETADO ==='
