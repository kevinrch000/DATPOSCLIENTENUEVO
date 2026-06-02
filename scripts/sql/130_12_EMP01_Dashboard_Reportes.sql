USE DatPos_EMP01;
GO
/* DASHBOARD */
IF OBJECT_ID('webDatpos_DashboardVentasTotales','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardVentasTotales; 
GO
CREATE PROCEDURE webDatpos_DashboardVentasTotales @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ISNULL(SUM(ntotal),0) AS total_ventas,COUNT(*) AS num_ventas FROM CbFactura WHERE ccod_cia=@ccod_cia AND CAST(fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND cstatus='P' AND (@ccod_tienda='' OR ccod_tiend=@ccod_tienda); END
GO

IF OBJECT_ID('webDatpos_DashboardUltimosMovimientos','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardUltimosMovimientos; 
GO
CREATE PROCEDURE webDatpos_DashboardUltimosMovimientos @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT TOP 10 F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.ntotal,F.fecha_emision,C.cdsc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND F.cstatus='P' AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_DashboardStockBajo','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardStockBajo; 
GO
CREATE PROCEDURE webDatpos_DashboardStockBajo @ccod_cia VARCHAR(20),@ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT S.ccod_articulo,A.cdsc_articulo,S.ncantidad,A.nstock_min FROM Stock S INNER JOIN Articulos A ON A.ccod_articulo=S.ccod_articulo AND A.ccod_cia=S.ccod_cia WHERE S.ccod_cia=@ccod_cia AND S.ncantidad<=A.nstock_min AND A.cstatus='A' AND (@ccod_alm='' OR S.ccod_alm=@ccod_alm) ORDER BY S.ncantidad; END
GO

IF OBJECT_ID('webDatpos_DashboardVentasPorUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardVentasPorUsuario; 
GO
CREATE PROCEDURE webDatpos_DashboardVentasPorUsuario @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.ccod_usuario,U.cdsc_usuario,ISNULL(SUM(F.ntotal),0) AS total,COUNT(*) AS cantidad FROM CbFactura F LEFT JOIN Usuarios U ON U.ccod_usuario=F.ccod_usuario AND U.ccod_empresa=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND CAST(F.fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND F.cstatus='P' AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) GROUP BY F.ccod_usuario,U.cdsc_usuario ORDER BY total DESC; END
GO

IF OBJECT_ID('webDatpos_DashboardVentasPorHora','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardVentasPorHora; 
GO
CREATE PROCEDURE webDatpos_DashboardVentasPorHora @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT DATEPART(HOUR,fecha_emision) AS hora,ISNULL(SUM(ntotal),0) AS total FROM CbFactura WHERE ccod_cia=@ccod_cia AND CAST(fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND cstatus='P' AND (@ccod_tienda='' OR ccod_tiend=@ccod_tienda) GROUP BY DATEPART(HOUR,fecha_emision) ORDER BY hora; END
GO

IF OBJECT_ID('webDatpos_DashboardTopProductos','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardTopProductos; 
GO
CREATE PROCEDURE webDatpos_DashboardTopProductos @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT TOP 10 L.id_articulo,L.cdsc_articulo,SUM(L.ncantidad) AS total_vendido,SUM(L.nimporte_neto) AS total_importe FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia WHERE L.ccod_cia=@ccod_cia AND CAST(F.fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND F.cstatus='P' AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) GROUP BY L.id_articulo,L.cdsc_articulo ORDER BY total_vendido DESC; END
GO

IF OBJECT_ID('webDatpos_DashboardVentasPorCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardVentasPorCaja; 
GO
CREATE PROCEDURE webDatpos_DashboardVentasPorCaja @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.ccod_caja,C.cdsc_caja,ISNULL(SUM(F.ntotal),0) AS total FROM CbFactura F LEFT JOIN Cajas C ON C.ccod_caja=F.ccod_caja AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND CAST(F.fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND F.cstatus='P' AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) GROUP BY F.ccod_caja,C.cdsc_caja ORDER BY total DESC; END
GO

IF OBJECT_ID('webDatpos_DashboardResumenTurno','P') IS NOT NULL DROP PROCEDURE webDatpos_DashboardResumenTurno; 
GO
CREATE PROCEDURE webDatpos_DashboardResumenTurno @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT T.id_turno,T.ccod_usuario,U.cdsc_usuario,T.ccod_caja,T.nmonto_ini,T.dfchdoc_ini,ISNULL(SUM(F.ntotal),0) AS total_vendido FROM Turno T LEFT JOIN Usuarios U ON U.ccod_usuario=T.ccod_usuario AND U.ccod_empresa=T.ccod_cia LEFT JOIN CbFactura F ON F.ccod_cia=T.ccod_cia AND F.id_turno=T.id_turno AND F.cstatus='P' WHERE T.ccod_cia=@ccod_cia AND T.cstatus='A' AND (@ccod_tienda='' OR T.ccod_tienda=@ccod_tienda) GROUP BY T.id_turno,T.ccod_usuario,U.cdsc_usuario,T.ccod_caja,T.nmonto_ini,T.dfchdoc_ini; END
GO

/* REPORTES */
IF OBJECT_ID('webDatpos_reporteVentaPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_reporteVentaPrincipal; 
GO
CREATE PROCEDURE webDatpos_reporteVentaPrincipal @ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@cdoc VARCHAR(5),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.nsubtotal,F.nimpuesto,F.nisc,F.ndescuento,F.ccod_caja,F.ccod_almacen,F.ccod_usuario,U.cdsc_usuario,C.cdsc_coa,C.cdoc_coa,F.cstatus FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia LEFT JOIN Usuarios U ON U.ccod_usuario=F.ccod_usuario AND U.ccod_empresa=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@cdoc='' OR F.cdoc=@cdoc) ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_reporteVentaImporteTotal','P') IS NOT NULL DROP PROCEDURE webDatpos_reporteVentaImporteTotal; 
GO
CREATE PROCEDURE webDatpos_reporteVentaImporteTotal @ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@cdoc VARCHAR(5),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ISNULL(SUM(ntotal),0) AS total,ISNULL(SUM(nsubtotal),0) AS subtotal,ISNULL(SUM(nimpuesto),0) AS igv,ISNULL(SUM(nisc),0) AS isc,ISNULL(SUM(ndescuento),0) AS descuento,COUNT(*) AS cantidad FROM CbFactura WHERE ccod_cia=@ccod_cia AND fecha_emision BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR ccod_tiend=@ccod_tienda) AND (@cdoc='' OR cdoc=@cdoc) AND cstatus='P'; END
GO

IF OBJECT_ID('webDatpos_reporteAlmacenPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_reporteAlmacenPrincipal; 
GO
CREATE PROCEDURE webDatpos_reporteAlmacenPrincipal @ccod_almacen VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ctipo VARCHAR(10),@ccod_articulo VARCHAR(50),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT C.id_cbinve,C.ccod_alm,A.cdsc_alm,C.dfecha,C.ctipo,C.vserie,C.nnumero,C.ntotal,L.ccod_articulo,AR.cdsc_articulo,L.ncantidad,L.ncosto FROM CbInventario C INNER JOIN LnInventario L ON L.id_cbinve=C.id_cbinve AND L.ccod_cia=C.ccod_cia LEFT JOIN Almacenes A ON A.ccod_alm=C.ccod_alm AND A.ccod_cia=C.ccod_cia LEFT JOIN Articulos AR ON AR.ccod_articulo=L.ccod_articulo AND AR.ccod_cia=L.ccod_cia WHERE C.ccod_cia=@ccod_cia AND C.dfecha BETWEEN @fchDesde AND @fchHasta AND (@ccod_almacen='' OR C.ccod_alm=@ccod_almacen) AND (@ctipo='' OR C.ctipo=@ctipo) AND (@ccod_articulo='' OR L.ccod_articulo=@ccod_articulo) ORDER BY C.dfecha DESC; END
GO

IF OBJECT_ID('webDatpos_ReporteSaldo','P') IS NOT NULL DROP PROCEDURE webDatpos_ReporteSaldo; 
GO
CREATE PROCEDURE webDatpos_ReporteSaldo @ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_tienda VARCHAR(20),@ccod_alm VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT S.ccod_alm,AL.cdsc_alm,S.ccod_articulo,A.cdsc_articulo,S.ncantidad,S.ncosto FROM Stock S INNER JOIN Articulos A ON A.ccod_articulo=S.ccod_articulo AND A.ccod_cia=S.ccod_cia LEFT JOIN Almacenes AL ON AL.ccod_alm=S.ccod_alm AND AL.ccod_cia=S.ccod_cia WHERE S.ccod_cia=@ccod_cia AND (@ccod_alm='' OR S.ccod_alm=@ccod_alm) AND (@ccod_articulo='' OR S.ccod_articulo=@ccod_articulo) ORDER BY S.ccod_alm,A.cdsc_articulo; END
GO

IF OBJECT_ID('webDatpos_ReporteKardexInicio','P') IS NOT NULL DROP PROCEDURE webDatpos_ReporteKardexInicio; 
GO
CREATE PROCEDURE webDatpos_ReporteKardexInicio @ccod_articulo VARCHAR(50),@ccod_alm VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT A.cdsc_articulo,AL.cdsc_alm,ISNULL(S.ncantidad,0) AS stock_actual FROM Articulos A LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ccod_cia=A.ccod_cia LEFT JOIN Almacenes AL ON AL.ccod_alm=@ccod_alm AND AL.ccod_cia=A.ccod_cia WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@ccod_articulo; END
GO

IF OBJECT_ID('webDatpos_ReporteKardexArticulos','P') IS NOT NULL DROP PROCEDURE webDatpos_ReporteKardexArticulos; 
GO
CREATE PROCEDURE webDatpos_ReporteKardexArticulos @ccod_articulo VARCHAR(50),@ccod_alm VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT C.dfecha,C.ctipo,C.vserie,C.nnumero,L.ncantidad,L.ncosto,CASE WHEN C.ctipo IN ('I','GI','DV') THEN L.ncantidad ELSE 0 END AS entrada,CASE WHEN C.ctipo IN ('S','VT','GS') THEN L.ncantidad ELSE 0 END AS salida FROM LnInventario L INNER JOIN CbInventario C ON C.id_cbinve=L.id_cbinve AND C.ccod_cia=L.ccod_cia WHERE L.ccod_cia=@ccod_cia AND L.ccod_articulo=@ccod_articulo AND (L.ccod_alm=@ccod_alm OR L.ccod_alm_ingreso=@ccod_alm) AND C.dfecha BETWEEN @fchDesde AND @fchHasta ORDER BY C.dfecha; END
GO

IF OBJECT_ID('webDatpos_ReporteTurno','P') IS NOT NULL DROP PROCEDURE webDatpos_ReporteTurno; 
GO
CREATE PROCEDURE webDatpos_ReporteTurno @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@id_usuario VARCHAR(50),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT T.*,U.cdsc_usuario,ISNULL(SUM(F.ntotal),0) AS total_vendido FROM Turno T LEFT JOIN Usuarios U ON U.ccod_usuario=T.ccod_usuario AND U.ccod_empresa=T.ccod_cia LEFT JOIN CbFactura F ON F.ccod_cia=T.ccod_cia AND F.id_turno=T.id_turno AND F.cstatus='P' WHERE T.ccod_cia=@ccod_cia AND T.dfchdoc_ini BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR T.ccod_tienda=@ccod_tienda) AND (@id_usuario='' OR T.ccod_usuario=@id_usuario) GROUP BY T.id_turno,T.ccod_cia,T.ccod_tienda,T.ccod_usuario,T.ccod_caja,T.nmonto_ini,T.nmonto_fin,T.ntot_entreg,T.ndiferencia,T.dfchdoc_ini,T.dfchdoc_fin,T.cstatus,T.dfch_crea,U.cdsc_usuario ORDER BY T.dfchdoc_ini DESC; END
GO

IF OBJECT_ID('webDatpos_ConsultaTributarioPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaTributarioPrincipal; 
GO
CREATE PROCEDURE webDatpos_ConsultaTributarioPrincipal @ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@cdoc VARCHAR(5),@cdoc_serie VARCHAR(10),@cdoc_nro VARCHAR(20),@ccod_coa VARCHAR(20),@cstatus_tributario VARCHAR(5),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.nsubtotal,F.nimpuesto,F.nisc,F.ndescuento,F.cstatus,F.cstatus_tributario,C.cdsc_coa,C.cdoc_coa,C.cruc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@cdoc='' OR F.cdoc=@cdoc) AND (@ccod_coa='' OR F.ccod_coa=@ccod_coa) AND (@cstatus_tributario='' OR F.cstatus_tributario=@cstatus_tributario) ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_reporteTributarioPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_reporteTributarioPrincipal; 
GO
CREATE PROCEDURE webDatpos_reporteTributarioPrincipal @ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_cia VARCHAR(20),@cstatus_tributario VARCHAR(5)
AS BEGIN SET NOCOUNT ON; EXEC webDatpos_ConsultaTributarioPrincipal @ccod_tienda,@fchDesde,@fchHasta,'','','',@ccod_coa,@cstatus_tributario,@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_DescargarArchivoPDF','P') IS NOT NULL DROP PROCEDURE webDatpos_DescargarArchivoPDF; 
GO
CREATE PROCEDURE webDatpos_DescargarArchivoPDF @id_cbfact INT,@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT pdf FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_DescargarArchivoXML','P') IS NOT NULL DROP PROCEDURE webDatpos_DescargarArchivoXML; 
GO
CREATE PROCEDURE webDatpos_DescargarArchivoXML @id_cbfact INT,@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT xml FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_DescargarArchivoXMLCDR','P') IS NOT NULL DROP PROCEDURE webDatpos_DescargarArchivoXMLCDR; 
GO
CREATE PROCEDURE webDatpos_DescargarArchivoXMLCDR @id_cbfact INT,@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT xml_cdr FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; END
GO

PRINT '✓ SPs Dashboard y Reportes creados.';
GO
