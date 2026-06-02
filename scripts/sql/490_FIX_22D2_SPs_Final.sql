/* FIX 22D2 — Anulación, DatosReferencia, Ubigeo, Empresa master — DatPos_EMP01 */
USE DatPos_EMP01;
GO

/* Anulación */
IF OBJECT_ID('appDatpos_anulacion','P') IS NOT NULL DROP PROCEDURE appDatpos_anulacion;
GO
CREATE PROCEDURE appDatpos_anulacion @id_cbfact VARCHAR(20),@motivo VARCHAR(500),@ccod_usu VARCHAR(50),@ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_almacen VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
UPDATE CbFactura SET cstatus='A',cobs=@motivo WHERE id_cbfact=CAST(@id_cbfact AS INT) AND ccod_cia=@ccod_cia;
SELECT 'OK' AS resultado,'Documento anulado correctamente' AS mensaje; END
GO

IF OBJECT_ID('webDatpos_anulacionPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_anulacionPricipal;
GO
CREATE PROCEDURE webDatpos_anulacionPricipal @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal,F.cstatus
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@CodCia AND F.cstatus='A' AND(@cdoc_seri='' OR F.cdoc=@cdoc_seri)AND(@serie='' OR F.cserie=@serie)AND(@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
ORDER BY F.fecha_emision DESC; END
GO

/* Nota Crédito devolución */
IF OBJECT_ID('webDatpos_generarNotaCreditoDevolucion','P') IS NOT NULL DROP PROCEDURE webDatpos_generarNotaCreditoDevolucion;
GO
CREATE PROCEDURE webDatpos_generarNotaCreditoDevolucion @ccod_cia VARCHAR(20),@id_cbfact INT,@motivo VARCHAR(500),@ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
UPDATE CbFactura SET cstatus='A',cobs=@motivo WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia;
SELECT @id_cbfact AS id_cbfact,'OK' AS resultado; END
GO

/* DatosReferencia (SP con OUTPUT params) */
IF OBJECT_ID('webDatpos_DatosReferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_DatosReferencia;
GO
CREATE PROCEDURE webDatpos_DatosReferencia @ccod_empresa VARCHAR(20),@id_cbinve VARCHAR(20),@id_cbfact VARCHAR(20),
@FactRef NVARCHAR(25) OUTPUT,@FactFch NVARCHAR(25) OUTPUT,@FactTotal NVARCHAR(25) OUTPUT,
@ccod_tienda NVARCHAR(25) OUTPUT,@cdsc_tienda NVARCHAR(100) OUTPUT,@ccod_alm NVARCHAR(25) OUTPUT,@cdsc_alm NVARCHAR(100) OUTPUT,
@cusu_crea NVARCHAR(25) OUTPUT,@cdsc_usuario NVARCHAR(100) OUTPUT,
@ccod_coa_cliente NVARCHAR(25) OUTPUT,@ccoa_dsc_cliente NVARCHAR(100) OUTPUT,
@ccod_coa_proveedor NVARCHAR(25) OUTPUT,@ccoa_dsc_proveedor NVARCHAR(100) OUTPUT,
@InveTotal NVARCHAR(25) OUTPUT,@InveRef NVARCHAR(100) OUTPUT,@InveFch NVARCHAR(25) OUTPUT,
@ccod_caja NVARCHAR(100) OUTPUT,@cdsc_caja NVARCHAR(25) OUTPUT,
@ccod_tienda_inve NVARCHAR(25) OUTPUT,@cdsc_tienda_inve NVARCHAR(100) OUTPUT,
@nvuelto NVARCHAR(25) OUTPUT,@ntot_entreg NVARCHAR(25) OUTPUT,@CobranRef NVARCHAR(100) OUTPUT,
@Factcusu_crea NVARCHAR(25) OUTPUT,@Factcdsc_usuario NVARCHAR(100) OUTPUT,
@obs NVARCHAR(100) OUTPUT,@ccoa_doc_cliente NVARCHAR(100) OUTPUT,@ccoa_dir_cliente NVARCHAR(100) OUTPUT,
@FactSubTotal NVARCHAR(100) OUTPUT,@FactIGV NVARCHAR(100) OUTPUT,
@Factcdoc_serie NVARCHAR(100) OUTPUT,@Factcdoc_nro NVARCHAR(100) OUTPUT,@Factcdoc NVARCHAR(100) OUTPUT
AS BEGIN SET NOCOUNT ON;
SET @FactRef=''; SET @FactFch=''; SET @FactTotal='0'; SET @ccod_tienda=''; SET @cdsc_tienda='';
SET @ccod_alm=''; SET @cdsc_alm=''; SET @cusu_crea=''; SET @cdsc_usuario='';
SET @ccod_coa_cliente=''; SET @ccoa_dsc_cliente=''; SET @ccod_coa_proveedor=''; SET @ccoa_dsc_proveedor='';
SET @InveTotal='0'; SET @InveRef=''; SET @InveFch=''; SET @ccod_caja=''; SET @cdsc_caja='';
SET @ccod_tienda_inve=''; SET @cdsc_tienda_inve=''; SET @nvuelto='0'; SET @ntot_entreg='0';
SET @CobranRef=''; SET @Factcusu_crea=''; SET @Factcdsc_usuario=''; SET @obs='';
SET @ccoa_doc_cliente=''; SET @ccoa_dir_cliente=''; SET @FactSubTotal='0'; SET @FactIGV='0';
SET @Factcdoc_serie=''; SET @Factcdoc_nro=''; SET @Factcdoc='';
-- Factura
IF @id_cbfact<>'' AND @id_cbfact<>'0' BEGIN
  SELECT @FactRef=ISNULL(F.cdoc+'-'+F.cserie+'-'+CAST(F.nnumero AS VARCHAR),''),@FactFch=ISNULL(CONVERT(VARCHAR,F.fecha_emision,103),''),@FactTotal=ISNULL(CAST(F.ntotal AS VARCHAR),'0'),@ccod_tienda=ISNULL(F.ccod_tiend,''),@Factcusu_crea=ISNULL(F.ccod_usuario,''),@ccod_coa_cliente=ISNULL(F.ccod_coa,''),@ccod_caja=ISNULL(F.ccod_caja,''),@nvuelto=ISNULL(CAST(F.nvuelto AS VARCHAR),'0'),@ntot_entreg=ISNULL(CAST(F.ntot_entreg AS VARCHAR),'0'),@obs=ISNULL(F.cobs,''),@FactSubTotal=ISNULL(CAST(F.nsubtotal AS VARCHAR),'0'),@FactIGV=ISNULL(CAST(F.nimpuesto AS VARCHAR),'0'),@Factcdoc_serie=ISNULL(F.cserie,''),@Factcdoc_nro=ISNULL(CAST(F.nnumero AS VARCHAR),''),@Factcdoc=ISNULL(F.cdoc,'')
  FROM CbFactura F WHERE F.id_cbfact=CAST(@id_cbfact AS INT) AND F.ccod_cia=@ccod_empresa;
  SELECT @ccoa_dsc_cliente=ISNULL(cdsc_coa,''),@ccoa_doc_cliente=ISNULL(cdoc_coa,''),@ccoa_dir_cliente=ISNULL(cdirc_coa,'') FROM Coa WHERE ccod_coa=@ccod_coa_cliente AND ccod_cia=@ccod_empresa;
  SELECT @cdsc_tienda=ISNULL(cnombr,'') FROM Tiendas WHERE ccod_tiend=@ccod_tienda AND ccod_cia=@ccod_empresa;
  SELECT @cdsc_caja=ISNULL(cdsc_caja,'') FROM Cajas WHERE ccod_caja=@ccod_caja AND ccod_cia=@ccod_empresa;
END
-- Inventario
IF @id_cbinve<>'' AND @id_cbinve<>'0' BEGIN
  SELECT @InveRef=ISNULL(I.ctipo+'-'+I.vserie+'-'+CAST(I.nnumero AS VARCHAR),''),@InveFch=ISNULL(CONVERT(VARCHAR,I.dfecha,103),''),@InveTotal=ISNULL(CAST(I.ntotal AS VARCHAR),'0'),@ccod_tienda_inve=ISNULL(I.ccod_tienda,''),@ccod_alm=ISNULL(I.ccod_alm,''),@cusu_crea=ISNULL(I.ccod_usuario,''),@ccod_coa_proveedor=ISNULL(I.ccod_coa,'')
  FROM CbInventario I WHERE I.id_cbinve=CAST(@id_cbinve AS INT) AND I.ccod_cia=@ccod_empresa;
  SELECT @cdsc_tienda_inve=ISNULL(cnombr,'') FROM Tiendas WHERE ccod_tiend=@ccod_tienda_inve AND ccod_cia=@ccod_empresa;
  SELECT @cdsc_alm=ISNULL(cdsc_alm,'') FROM Almacenes WHERE ccod_alm=@ccod_alm AND ccod_cia=@ccod_empresa;
  SELECT @ccoa_dsc_proveedor=ISNULL(cdsc_coa,'') FROM Coa WHERE ccod_coa=@ccod_coa_proveedor AND ccod_cia=@ccod_empresa;
END
END
GO

/* webDatpos_validarArticuloAlmacen (comentado en DA pero por si acaso) */
IF OBJECT_ID('webDatpos_validarArticuloAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_validarArticuloAlmacen;
GO
CREATE PROCEDURE webDatpos_validarArticuloAlmacen @ccod_cia VARCHAR(20),@ccod_articulo VARCHAR(50),@almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(S.ncantidad,0) AS ncantidad FROM Stock S WHERE S.ccod_cia=@ccod_cia AND S.ccod_articulo=@ccod_articulo AND S.ccod_alm=@almacen; END
GO

/* webDatpos_contadorEmpresa (comentado en DA pero creamos por si) */
IF OBJECT_ID('webDatpos_contadorEmpresa','P') IS NOT NULL DROP PROCEDURE webDatpos_contadorEmpresa;
GO
CREATE PROCEDURE webDatpos_contadorEmpresa
AS BEGIN SET NOCOUNT ON; SELECT COUNT(*) AS total FROM CbFactura; END
GO

/* webDatpos_cargarSolesDocVentGraBarConMovAlm (comentado pero creamos) */
IF OBJECT_ID('webDatpos_cargarSolesDocVentGraBarConMovAlm','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarSolesDocVentGraBarConMovAlm;
GO
CREATE PROCEDURE webDatpos_cargarSolesDocVentGraBarConMovAlm @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.cdoc,SUM(F.ntotal) AS total FROM CbFactura F WHERE F.ccod_cia=@CodCia AND F.cstatus<>'A' AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59') GROUP BY F.cdoc; END
GO

/* webDatpos_consultarArticulosConStockyCosto (comentado) */
IF OBJECT_ID('webDatpos_consultarArticulosConStockyCosto','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticulosConStockyCosto;
GO
CREATE PROCEDURE webDatpos_consultarArticulosConStockyCosto @ccod_cia VARCHAR(20),@almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(S.ncantidad,0) AS ncantidad,ISNULL(S.ncosto,0) AS ncosto FROM Articulos A INNER JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@almacen AND S.ccod_cia=A.ccod_cia WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND S.ncantidad>0; END
GO

/* service SPs (ITC) */
IF OBJECT_ID('service_consultarpendientesenvioITC','P') IS NOT NULL DROP PROCEDURE service_consultarpendientesenvioITC;
GO
CREATE PROCEDURE service_consultarpendientesenvioITC
AS BEGIN SET NOCOUNT ON; SELECT 0 AS id WHERE 1=0; END
GO

IF OBJECT_ID('service_consultardetalleenvioITC','P') IS NOT NULL DROP PROCEDURE service_consultardetalleenvioITC;
GO
CREATE PROCEDURE service_consultardetalleenvioITC
AS BEGIN SET NOCOUNT ON; SELECT 0 AS id WHERE 1=0; END
GO

PRINT '✓ FIX 22D2: Anulación, DatosRef, extras (10 SPs).';
GO

/* ── PARTE MASTER DB: sp_consultarempresas, sp_insertarempresas, sp_editarempresa, sp_eliminarempresa ── */
USE DatPosAdmin;
GO

/* Agregar columnas faltantes a Empresas si no existen */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cdoc')
    ALTER TABLE Empresas ADD cdoc VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cnomser')
    ALTER TABLE Empresas ADD cnomser VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cnum_tribu')
    ALTER TABLE Empresas ADD cnum_tribu VARCHAR(20) NULL;
GO

IF OBJECT_ID('sp_consultarempresas','P') IS NOT NULL DROP PROCEDURE sp_consultarempresas;
GO
CREATE PROCEDURE sp_consultarempresas
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_empresa, cdsc_empresa AS cdescripcion, ISNULL(cdoc,'') AS cdoc,
           ISNULL(cnum_tribu,'') AS cnum_tribu, ISNULL(cnomser,'') AS cnomser, cnombre_bd
    FROM Empresas;
END
GO

IF OBJECT_ID('sp_insertarempresas','P') IS NOT NULL DROP PROCEDURE sp_insertarempresas;
GO
CREATE PROCEDURE sp_insertarempresas @ccod_empresa VARCHAR(20),@cdescripcion VARCHAR(200),@cdoc VARCHAR(20),@cnum_tribu VARCHAR(20),@cnomser VARCHAR(100),@cnombre_bd VARCHAR(100)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO Empresas(ccod_empresa,cdsc_empresa,cdoc,cnum_tribu,cnomser,cnombre_bd)
    VALUES(@ccod_empresa,@cdescripcion,@cdoc,@cnum_tribu,@cnomser,@cnombre_bd);
END
GO

IF OBJECT_ID('sp_editarempresa','P') IS NOT NULL DROP PROCEDURE sp_editarempresa;
GO
CREATE PROCEDURE sp_editarempresa @ccod_empresa VARCHAR(20),@cdescripcion VARCHAR(200),@cdoc VARCHAR(20),@cnum_tribu VARCHAR(20),@cnomser VARCHAR(100),@cnombre_bd VARCHAR(100)
AS BEGIN SET NOCOUNT ON;
    UPDATE Empresas SET cdsc_empresa=@cdescripcion,cdoc=@cdoc,cnum_tribu=@cnum_tribu,cnomser=@cnomser,cnombre_bd=@cnombre_bd
    WHERE ccod_empresa=@ccod_empresa;
END
GO

IF OBJECT_ID('sp_eliminarempresa','P') IS NOT NULL DROP PROCEDURE sp_eliminarempresa;
GO
CREATE PROCEDURE sp_eliminarempresa @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON; DELETE FROM Empresas WHERE ccod_empresa=@ccod_empresa; END
GO

PRINT '✓ FIX 22D2: Empresa master (4 SPs en DatPosAdmin).';
GO

/* Ubigeo tables + SPs en DatPosAdmin */
IF OBJECT_ID('Departamento','U') IS NULL CREATE TABLE Departamento(id_departamento VARCHAR(10) PRIMARY KEY,cdescripcion VARCHAR(100));
GO
IF OBJECT_ID('Provincia','U') IS NULL CREATE TABLE Provincia(id_provincia VARCHAR(10) PRIMARY KEY,id_departamento VARCHAR(10),cdescripcion VARCHAR(100));
GO
IF OBJECT_ID('Distrito','U') IS NULL CREATE TABLE Distrito(id_distrito VARCHAR(10) PRIMARY KEY,id_provincia VARCHAR(10),cdescripcion VARCHAR(100));
GO

IF OBJECT_ID('webDatpos_cargarDepartamentos','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDepartamentos;
GO
CREATE PROCEDURE webDatpos_cargarDepartamentos AS BEGIN SET NOCOUNT ON; SELECT id_departamento,cdescripcion FROM Departamento ORDER BY cdescripcion; END
GO

IF OBJECT_ID('webDatpos_cargarProvincias','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarProvincias;
GO
CREATE PROCEDURE webDatpos_cargarProvincias @id_departamento VARCHAR(10) AS BEGIN SET NOCOUNT ON; SELECT id_provincia,cdescripcion FROM Provincia WHERE id_departamento=@id_departamento ORDER BY cdescripcion; END
GO

IF OBJECT_ID('webDatpos_cargarDistritos','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDistritos;
GO
CREATE PROCEDURE webDatpos_cargarDistritos @id_provincia VARCHAR(10) AS BEGIN SET NOCOUNT ON; SELECT id_distrito,cdescripcion FROM Distrito WHERE id_provincia=@id_provincia ORDER BY cdescripcion; END
GO

PRINT '✓ FIX 22D2: Ubigeo (3 tablas + 3 SPs en DatPosAdmin). COMPLETO.';
GO
