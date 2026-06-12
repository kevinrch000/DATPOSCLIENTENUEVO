/* FIX 22C — SPs Reportes, Ventas, Artículos avanzados, Gráficos — DatPos_EMP01 */
USE DatPos_EMP01;
GO

/* Artículos */
IF OBJECT_ID('webDatpos_consultarArticulosPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticulosPricipal;
GO
CREATE PROCEDURE webDatpos_consultarArticulosPricipal @CodArticulo VARCHAR(50),@NomAticulo VARCHAR(200),@TipArticulo VARCHAR(10),@Tributos VARCHAR(5),@Familia VARCHAR(20),@UniMedida VARCHAR(10),@Estado VARCHAR(1),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.id_articulo,A.ccod_articulo,A.cdsc_articulo,A.ccod_lin,ISNULL(F.cdsc_lin,'') AS cdsc_lin,A.uni_medi,A.cstatus,A.ctip_articulo,A.cigv,A.cisc
FROM Articulos A LEFT JOIN Familias F ON F.ccod_lin=A.ccod_lin AND F.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND(@CodArticulo='' OR A.ccod_articulo LIKE '%'+@CodArticulo+'%')AND(@NomAticulo='' OR A.cdsc_articulo LIKE '%'+@NomAticulo+'%')AND(@TipArticulo='' OR A.ctip_articulo=@TipArticulo)AND(@Familia='' OR A.ccod_lin=@Familia)AND(@UniMedida='' OR A.uni_medi=@UniMedida)AND(@Estado='' OR A.cstatus=@Estado) ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('webDatpos_cargarEstadisticasConsArti','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarEstadisticasConsArti;
GO
CREATE PROCEDURE webDatpos_cargarEstadisticasConsArti @CodArticulo VARCHAR(50),@NomAticulo VARCHAR(200),@TipArticulo VARCHAR(10),@Tributos VARCHAR(5),@Familia VARCHAR(20),@UniMedida VARCHAR(10),@Estado VARCHAR(1),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT COUNT(*) AS total, SUM(CASE WHEN A.cstatus='A' THEN 1 ELSE 0 END) AS activos, SUM(CASE WHEN A.cstatus='I' THEN 1 ELSE 0 END) AS inactivos
FROM Articulos A WHERE A.ccod_cia=@ccod_cia AND(@CodArticulo='' OR A.ccod_articulo LIKE '%'+@CodArticulo+'%')AND(@NomAticulo='' OR A.cdsc_articulo LIKE '%'+@NomAticulo+'%')AND(@TipArticulo='' OR A.ctip_articulo=@TipArticulo)AND(@Familia='' OR A.ccod_lin=@Familia)AND(@UniMedida='' OR A.uni_medi=@UniMedida)AND(@Estado='' OR A.cstatus=@Estado); END
GO

IF OBJECT_ID('webDatpos_consultarArticuloVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticuloVariante;
GO
CREATE PROCEDURE webDatpos_consultarArticuloVariante @ccod_cia VARCHAR(20),@codigo VARCHAR(50)
AS BEGIN SET NOCOUNT ON; SELECT id_cbvariante,cdsc_variante FROM CbVariante WHERE ccod_cia=@ccod_cia AND ccod_articulo=@codigo; END
GO

IF OBJECT_ID('webDatpos_consultarArticuloDetalleVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticuloDetalleVariante;
GO
CREATE PROCEDURE webDatpos_consultarArticuloDetalleVariante @ccod_cia VARCHAR(20),@ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON; SELECT V.id_cbvariante,V.cdsc_variante,L.id_lnvariante,L.cdsc_lnvariante FROM CbVariante V LEFT JOIN LnVariante L ON L.id_cbvariante=V.id_cbvariante AND L.ccod_cia=V.ccod_cia WHERE V.ccod_cia=@ccod_cia AND V.ccod_articulo=@ccod_articulo; END
GO

IF OBJECT_ID('webDatpos_consultarCostosArticulos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCostosArticulos;
GO
CREATE PROCEDURE webDatpos_consultarCostosArticulos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(S.ncosto,0) AS ncosto,ISNULL(S.ncantidad,0) AS ncantidad,S.ccod_alm FROM Articulos A LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_cia=A.ccod_cia WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A'; END
GO

IF OBJECT_ID('webDatpos_consultarArticulosSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticulosSalida;
GO
CREATE PROCEDURE webDatpos_consultarArticulosSalida @ccod_cia VARCHAR(20),@almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(S.ncantidad,0) AS ncantidad,ISNULL(S.ncosto,0) AS ncosto FROM Articulos A INNER JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@almacen AND S.ccod_cia=A.ccod_cia WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND S.ncantidad>0 ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('sp_lsconsultararticulocategoria','P') IS NOT NULL DROP PROCEDURE sp_lsconsultararticulocategoria;
GO
CREATE PROCEDURE sp_lsconsultararticulocategoria @ccod_cia VARCHAR(20),@codigo INT,@ccod_usuario VARCHAR(50),@ccod_almacen VARCHAR(20),@ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(P.npre_uni,0) AS npre_uni,A.cigv,A.cisc,ISNULL(S.ncantidad,0) AS ncantidad,A.iimage
FROM Articulos A LEFT JOIN LnListaPrecio P ON P.ccod_articulo=A.ccod_articulo AND P.ccod_cblistpre=@ccod_cblistpre AND P.ccod_cia=A.ccod_cia LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_almacen AND S.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND(@codigo=0 OR A.ccod_lin IN(SELECT ccod_lin FROM Familias WHERE ccod_cia=@ccod_cia AND id_lin=@codigo)) ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('sp_lpconsultarfavoritos','P') IS NOT NULL DROP PROCEDURE sp_lpconsultarfavoritos;
GO
CREATE PROCEDURE sp_lpconsultarfavoritos @ccod_cia VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_almacen VARCHAR(20),@ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(P.npre_uni,0) AS npre_uni,A.cigv,A.cisc,ISNULL(S.ncantidad,0) AS ncantidad,A.iimage
FROM Articulos A LEFT JOIN LnListaPrecio P ON P.ccod_articulo=A.ccod_articulo AND P.ccod_cblistpre=@ccod_cblistpre AND P.ccod_cia=A.ccod_cia LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_almacen AND S.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.bprefer=1 AND A.cstatus='A' ORDER BY A.cdsc_articulo; END
GO

/* Ventas */
IF OBJECT_ID('webDatpos_consultaVentaPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaVentaPrincipal;
GO
CREATE PROCEDURE webDatpos_consultaVentaPrincipal @Codigo VARCHAR(50),@cliente VARCHAR(20),@fechadesde VARCHAR(20),@fechahasta VARCHAR(20),@ccod_tienda VARCHAR(20),@cdsc_lnvariante VARCHAR(200),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT L.id_articulo,L.cdsc_articulo,SUM(L.ncantidad) AS ncantidad,AVG(L.nprecio) AS nprecio,SUM(L.nimporte_neto) AS nimporte_neto
FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND F.cstatus<>'A' AND(@Codigo='' OR L.id_articulo LIKE '%'+@Codigo+'%')AND(@cliente='' OR F.ccod_coa=@cliente)AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@fechadesde='' OR F.fecha_emision>=@fechadesde)AND(@fechahasta='' OR F.fecha_emision<=@fechahasta+' 23:59:59')
GROUP BY L.id_articulo,L.cdsc_articulo ORDER BY nimporte_neto DESC; END
GO

IF OBJECT_ID('webDatpos_consultaArticulosMasVendidos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaArticulosMasVendidos;
GO
CREATE PROCEDURE webDatpos_consultaArticulosMasVendidos @fechadesde VARCHAR(20),@fechahasta VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_lin VARCHAR(20),@ccod_articulo VARCHAR(50),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT TOP 20 L.id_articulo,L.cdsc_articulo,SUM(L.ncantidad) AS ncantidad,SUM(L.nimporte_neto) AS nimporte_neto
FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND F.cstatus<>'A' AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@fechadesde='' OR F.fecha_emision>=@fechadesde)AND(@fechahasta='' OR F.fecha_emision<=@fechahasta+' 23:59:59')AND(@ccod_articulo='' OR L.id_articulo LIKE '%'+@ccod_articulo+'%')
GROUP BY L.id_articulo,L.cdsc_articulo ORDER BY ncantidad DESC; END
GO

IF OBJECT_ID('webDatpos_cargarArticuloVentas','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarArticuloVentas;
GO
CREATE PROCEDURE webDatpos_cargarArticuloVentas @cliente VARCHAR(200),@fechadesde VARCHAR(20),@fechahasta VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,ISNULL(C.cdsc_coa,'') AS cdsc_coa,F.ntotal
FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND F.cstatus<>'A' AND(@cliente='' OR C.cdsc_coa LIKE '%'+@cliente+'%')AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@fechadesde='' OR F.fecha_emision>=@fechadesde)AND(@fechahasta='' OR F.fecha_emision<=@fechahasta+' 23:59:59')
ORDER BY F.fecha_emision DESC; END
GO

/* Gráficos ventas */
IF OBJECT_ID('webDatpos_cargarGraBarConsVent','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarGraBarConsVent;
GO
CREATE PROCEDURE webDatpos_cargarGraBarConsVent @Codigo VARCHAR(50),@cliente VARCHAR(20),@fechadesde VARCHAR(20),@fechahasta VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT TOP 10 L.cdsc_articulo,SUM(L.ncantidad) AS ncantidad FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND F.cstatus<>'A' AND(@Codigo='' OR L.id_articulo LIKE '%'+@Codigo+'%')AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@fechadesde='' OR F.fecha_emision>=@fechadesde)AND(@fechahasta='' OR F.fecha_emision<=@fechahasta+' 23:59:59')
GROUP BY L.cdsc_articulo ORDER BY ncantidad DESC; END
GO

IF OBJECT_ID('webDatpos_cargarGraBarConsVentMenos','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarGraBarConsVentMenos;
GO
CREATE PROCEDURE webDatpos_cargarGraBarConsVentMenos @Codigo VARCHAR(50),@cliente VARCHAR(20),@fechadesde VARCHAR(20),@fechahasta VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT TOP 10 L.cdsc_articulo,SUM(L.ncantidad) AS ncantidad FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
WHERE F.ccod_cia=@ccod_cia AND F.cstatus<>'A' AND(@Codigo='' OR L.id_articulo LIKE '%'+@Codigo+'%')AND(@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)AND(@fechadesde='' OR F.fecha_emision>=@fechadesde)AND(@fechahasta='' OR F.fecha_emision<=@fechahasta+' 23:59:59')
GROUP BY L.cdsc_articulo ORDER BY ncantidad ASC; END
GO

/* Lista Precios */
IF OBJECT_ID('webDatpos_consultaListPrecioPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListPrecioPricipal;
GO
CREATE PROCEDURE webDatpos_consultaListPrecioPricipal @ccod_cia VARCHAR(20),@ccod_cblistpre VARCHAR(20),@ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_lin VARCHAR(20),@ccod_unidadmedida VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,A.ccod_lin,A.uni_medi,ISNULL(P.npre_uni,0) AS npre_uni,ISNULL(P.ndes_max,0) AS ndes_max,ISNULL(P.ndes_min,0) AS ndes_min
FROM Articulos A LEFT JOIN LnListaPrecio P ON P.ccod_articulo=A.ccod_articulo AND P.ccod_cblistpre=@ccod_cblistpre AND P.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND(@ccod_articulo='' OR A.ccod_articulo LIKE '%'+@ccod_articulo+'%')AND(@cdsc_articulo='' OR A.cdsc_articulo LIKE '%'+@cdsc_articulo+'%')AND(@ccod_lin='' OR A.ccod_lin=@ccod_lin)AND(@ccod_unidadmedida='' OR A.uni_medi=@ccod_unidadmedida)
ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('webDatpos_cargarEstadisticasListPrecio','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarEstadisticasListPrecio;
GO
CREATE PROCEDURE webDatpos_cargarEstadisticasListPrecio @ccod_cia VARCHAR(20),@ccod_cblistpre VARCHAR(20),@ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_lin VARCHAR(20),@ccod_unidadmedida VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
SELECT COUNT(*) AS total,SUM(CASE WHEN P.npre_uni>0 THEN 1 ELSE 0 END) AS con_precio,SUM(CASE WHEN ISNULL(P.npre_uni,0)=0 THEN 1 ELSE 0 END) AS sin_precio
FROM Articulos A LEFT JOIN LnListaPrecio P ON P.ccod_articulo=A.ccod_articulo AND P.ccod_cblistpre=@ccod_cblistpre AND P.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND(@ccod_articulo='' OR A.ccod_articulo LIKE '%'+@ccod_articulo+'%')AND(@cdsc_articulo='' OR A.cdsc_articulo LIKE '%'+@cdsc_articulo+'%'); END
GO

IF OBJECT_ID('webDatpos_cargarArticuloListPrecio','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarArticuloListPrecio;
GO
CREATE PROCEDURE webDatpos_cargarArticuloListPrecio @ccod_cia VARCHAR(20),@ccod_cblistpre VARCHAR(20),@ccod_lin VARCHAR(20),@ccod_unidadmedida VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(P.npre_uni,0) AS npre_uni FROM Articulos A LEFT JOIN LnListaPrecio P ON P.ccod_articulo=A.ccod_articulo AND P.ccod_cblistpre=@ccod_cblistpre AND P.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND(@ccod_lin='' OR A.ccod_lin=@ccod_lin)AND(@ccod_unidadmedida='' OR A.uni_medi=@ccod_unidadmedida) ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('webDatpos_cargarArticuloSaldo','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarArticuloSaldo;
GO
CREATE PROCEDURE webDatpos_cargarArticuloSaldo @ccod_lin VARCHAR(20),@Codalmacen VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(S.ncantidad,0) AS ncantidad,ISNULL(S.ncosto,0) AS ncosto FROM Articulos A LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@Codalmacen AND S.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@CodCia AND A.cstatus='A' AND(@ccod_lin='' OR A.ccod_lin=@ccod_lin) ORDER BY A.cdsc_articulo; END
GO

/* Stock Mínimo */
IF OBJECT_ID('webDatpos_ConsultaStockMinimo','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaStockMinimo;
GO
CREATE PROCEDURE webDatpos_ConsultaStockMinimo @ccod_cia VARCHAR(20),@ccod_alm VARCHAR(20),@ccod_lin VARCHAR(20),@ccod_articulo VARCHAR(50),@nstock_min VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,A.nstock_min,ISNULL(S.ncantidad,0) AS ncantidad,ISNULL(AL.cdsc_alm,'') AS cdsc_alm
FROM Articulos A LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ccod_cia=A.ccod_cia LEFT JOIN Almacenes AL ON AL.ccod_alm=@ccod_alm AND AL.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND(@ccod_articulo='' OR A.ccod_articulo LIKE '%'+@ccod_articulo+'%')AND(@ccod_lin='' OR A.ccod_lin=@ccod_lin)AND ISNULL(S.ncantidad,0)<=A.nstock_min AND A.nstock_min>0
ORDER BY A.cdsc_articulo; END
GO

/* Almacén Principal */
IF OBJECT_ID('webDatpos_consultasAlmacenPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultasAlmacenPrincipal;
GO
CREATE PROCEDURE webDatpos_consultasAlmacenPrincipal @ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_lin VARCHAR(20),@Codalmacen VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(F.cdsc_lin,'') AS cdsc_lin,A.uni_medi,ISNULL(S.ncantidad,0) AS ncantidad,ISNULL(S.ncosto,0) AS ncosto,ISNULL(AL.cdsc_alm,'') AS cdsc_alm
FROM Articulos A LEFT JOIN Familias F ON F.ccod_lin=A.ccod_lin AND F.ccod_cia=A.ccod_cia LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@Codalmacen AND S.ccod_cia=A.ccod_cia LEFT JOIN Almacenes AL ON AL.ccod_alm=@Codalmacen AND AL.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@CodCia AND A.cstatus='A' AND(@ccod_articulo='' OR A.ccod_articulo LIKE '%'+@ccod_articulo+'%')AND(@cdsc_articulo='' OR A.cdsc_articulo LIKE '%'+@cdsc_articulo+'%')AND(@ccod_lin='' OR A.ccod_lin=@ccod_lin) ORDER BY A.cdsc_articulo; END
GO

/* Diagramas almacén */
IF OBJECT_ID('webDatpos_cargarDiagramaPastelFamilia','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaPastelFamilia;
GO
CREATE PROCEDURE webDatpos_cargarDiagramaPastelFamilia @ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_lin VARCHAR(20),@Codalmacen VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(F.cdsc_lin,'Sin Familia') AS cdsc_lin,COUNT(*) AS cantidad FROM Articulos A LEFT JOIN Familias F ON F.ccod_lin=A.ccod_lin AND F.ccod_cia=A.ccod_cia
WHERE A.ccod_cia=@CodCia AND A.cstatus='A' GROUP BY F.cdsc_lin; END
GO

IF OBJECT_ID('webDatpos_cargarDiagramaPastelAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaPastelAlmacen;
GO
CREATE PROCEDURE webDatpos_cargarDiagramaPastelAlmacen @ccod_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),@ccod_lin VARCHAR(20),@Codalmacen VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
SELECT ISNULL(AL.cdsc_alm,'Sin Almacén') AS cdsc_alm,SUM(ISNULL(S.ncantidad,0)) AS ncantidad FROM Stock S LEFT JOIN Almacenes AL ON AL.ccod_alm=S.ccod_alm AND AL.ccod_cia=S.ccod_cia
WHERE S.ccod_cia=@CodCia GROUP BY AL.cdsc_alm; END
GO

PRINT '✓ FIX 22C: SPs Reportes/Ventas/Artículos (22 SPs) creados.';
GO
