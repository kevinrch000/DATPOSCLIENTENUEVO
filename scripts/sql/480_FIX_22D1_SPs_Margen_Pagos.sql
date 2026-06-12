/* FIX 22D1 — Margen Utilidad, Formas Pago, Gráficos Doc — DatPos_EMP01 */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_margenUtilidad','P') IS NOT NULL DROP PROCEDURE webDatpos_margenUtilidad;
GO
CREATE PROCEDURE webDatpos_margenUtilidad @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20),@ccoa_dsc VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal,F.costo AS ncosto,(F.ntotal-F.costo) AS margen
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@CodCia AND F.cstatus<>'A' AND(@cdoc_seri='' OR F.cdoc=@cdoc_seri)AND(@serie='' OR F.cserie=@serie)AND(@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')AND(@ccoa_dsc='' OR C.cdsc_coa LIKE '%'+@ccoa_dsc+'%')
ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_MargenUtilidadDiaPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_MargenUtilidadDiaPricipal;
GO
CREATE PROCEDURE webDatpos_MargenUtilidadDiaPricipal @ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.costo AS ncosto,(F.ntotal-F.costo) AS margen
FROM CbFactura F WHERE F.ccod_cia=@CodCia AND F.cstatus<>'A' AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@ccod_caja='' OR F.ccod_caja=@ccod_caja)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_cargarEstadisticasMargenUtilidad','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarEstadisticasMargenUtilidad;
GO
CREATE PROCEDURE webDatpos_cargarEstadisticasMargenUtilidad @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20),@ccoa_dsc VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
SELECT COUNT(*) AS total_docs,ISNULL(SUM(F.ntotal),0) AS total_venta,ISNULL(SUM(F.costo),0) AS total_costo,ISNULL(SUM(F.ntotal-F.costo),0) AS total_margen
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@CodCia AND F.cstatus<>'A' AND(@cdoc_seri='' OR F.cdoc=@cdoc_seri)AND(@serie='' OR F.cserie=@serie)AND(@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')AND(@ccoa_dsc='' OR C.cdsc_coa LIKE '%'+@ccoa_dsc+'%'); END
GO

IF OBJECT_ID('webDatpos_consultarMargenUtilidadArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarMargenUtilidadArticulo;
GO
CREATE PROCEDURE webDatpos_consultarMargenUtilidadArticulo @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT L.id_articulo,L.cdsc_articulo,L.ncantidad,L.nprecio,L.nimporte_neto,L.ncosto,(L.nimporte_neto-L.ncosto*L.ncantidad) AS margen
FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
WHERE F.ccod_cia=@CodCia AND F.cdoc=@cdoc_seri AND F.cserie=@serie AND F.nnumero=CAST(@correlativo AS INT); END
GO

IF OBJECT_ID('webDatpos_consultarMargenUtilidadArticuloDatos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarMargenUtilidadArticuloDatos;
GO
CREATE PROCEDURE webDatpos_consultarMargenUtilidadArticuloDatos @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal,F.costo,(F.ntotal-F.costo) AS margen,F.fecha_emision
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@CodCia AND F.cdoc=@cdoc_seri AND F.cserie=@serie AND F.nnumero=CAST(@correlativo AS INT); END
GO

/* Formas Pago */
IF OBJECT_ID('webDatpos_consultaFormasPagoPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaFormasPagoPricipal;
GO
CREATE PROCEDURE webDatpos_consultaFormasPagoPricipal @cdoc VARCHAR(5),@cdoc_serie VARCHAR(10),@cdoc_nro VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_caja VARCHAR(20),@cnom_tarje VARCHAR(100),@CodCia VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal,LC.cnom_tarje,LC.nmonto,F.fecha_emision
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia LEFT JOIN CbCobranza CC ON CC.id_cbfact=F.id_cbfact AND CC.ccod_cia=F.ccod_cia LEFT JOIN LnCobranza LC ON LC.id_cbcajac=CC.id_cbcajac AND LC.ccod_cia=CC.ccod_cia
WHERE F.ccod_cia=@CodCia AND(@cdoc='' OR F.cdoc=@cdoc)AND(@cdoc_serie='' OR F.cserie=@cdoc_serie)AND(@cdoc_nro='' OR CAST(F.nnumero AS VARCHAR)=@cdoc_nro)AND(@ccod_coa='' OR F.ccod_coa=@ccod_coa)AND(@ccod_usuario='' OR F.ccod_usuario=@ccod_usuario)AND(@ccod_caja='' OR F.ccod_caja=@ccod_caja)AND(@cnom_tarje='' OR LC.cnom_tarje LIKE '%'+@cnom_tarje+'%')AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_consultaFormasPagoSecundario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaFormasPagoSecundario;
GO
CREATE PROCEDURE webDatpos_consultaFormasPagoSecundario @cdoc VARCHAR(5),@cdoc_serie VARCHAR(10),@cdoc_nro VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_caja VARCHAR(20),@cnom_tarje VARCHAR(100),@CodCia VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje,SUM(LC.nmonto) AS nmonto
FROM LnCobranza LC INNER JOIN CbCobranza CC ON CC.id_cbcajac=LC.id_cbcajac AND CC.ccod_cia=LC.ccod_cia INNER JOIN CbFactura F ON F.id_cbfact=CC.id_cbfact AND F.ccod_cia=CC.ccod_cia
WHERE F.ccod_cia=@CodCia AND(@cdoc='' OR F.cdoc=@cdoc)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
GROUP BY LC.cnom_tarje; END
GO

IF OBJECT_ID('webDatpos_diagramaCobranzaCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_diagramaCobranzaCaja;
GO
CREATE PROCEDURE webDatpos_diagramaCobranzaCaja @cdoc VARCHAR(5),@cdoc_serie VARCHAR(10),@cdoc_nro VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20),@cnom_tarje VARCHAR(100),@CodCia VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje,SUM(LC.nmonto) AS nmonto
FROM LnCobranza LC INNER JOIN CbCobranza CC ON CC.id_cbcajac=LC.id_cbcajac AND CC.ccod_cia=LC.ccod_cia INNER JOIN CbFactura F ON F.id_cbfact=CC.id_cbfact AND F.ccod_cia=CC.ccod_cia
WHERE F.ccod_cia=@CodCia AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
GROUP BY LC.cnom_tarje; END
GO

IF OBJECT_ID('appDatpos_ConsultaPagosClientes','P') IS NOT NULL DROP PROCEDURE appDatpos_ConsultaPagosClientes;
GO
CREATE PROCEDURE appDatpos_ConsultaPagosClientes @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@cusu_crea VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal,F.fecha_emision
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@ccod_coa='' OR F.ccod_coa=@ccod_coa)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')AND(@cusu_crea='' OR F.ccod_usuario=@cusu_crea)
ORDER BY F.fecha_emision DESC; END
GO

/* Gráficos Doc/Mov */
IF OBJECT_ID('webDatpos_cargarDocVentGraBarConMovAlm','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDocVentGraBarConMovAlm;
GO
CREATE PROCEDURE webDatpos_cargarDocVentGraBarConMovAlm @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.cdoc,COUNT(*) AS cantidad,SUM(F.ntotal) AS total FROM CbFactura F
WHERE F.ccod_cia=@CodCia AND F.cstatus<>'A' AND(@cdoc_seri='' OR F.cdoc=@cdoc_seri)AND(@fchDesde='' OR F.fecha_emision>=@fchDesde)AND(@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
GROUP BY F.cdoc; END
GO

IF OBJECT_ID('webDatpos_cargarTipoOperGraBarConMovAlm','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTipoOperGraBarConMovAlm;
GO
CREATE PROCEDURE webDatpos_cargarTipoOperGraBarConMovAlm @tipoOper VARCHAR(20),@serie VARCHAR(10),@numero VARCHAR(20),@almacen VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(T.cdsc_tipoper,I.ctipo) AS ctipo,COUNT(*) AS cantidad,SUM(I.ntotal) AS total
FROM CbInventario I LEFT JOIN TipoOperacion T ON T.ccod_tipoper=I.ctipo AND T.ccod_cia=I.ccod_cia
WHERE I.ccod_cia=@ccod_cia AND(@tipoOper='' OR I.ctipo=@tipoOper)AND(@fchDesde='' OR I.dfecha>=@fchDesde)AND(@fchHasta='' OR I.dfecha<=@fchHasta+' 23:59:59')
GROUP BY ISNULL(T.cdsc_tipoper,I.ctipo); END
GO

PRINT '✓ FIX 22D1: Margen, FormasPago, Gráficos (13 SPs).';
GO
