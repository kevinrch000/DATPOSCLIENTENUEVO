/* FIX 04 — SPs del Dashboard que faltaban (bloquean el menú lateral) */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_CargarDatosCajero','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarDatosCajero; 
GO
CREATE PROCEDURE webDatpos_CargarDatosCajero @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @id_turno INT;
    SELECT TOP 1 @id_turno=id_turno FROM Turno WHERE ccod_cia=@ccod_cia AND ccod_usuario=@ccod_usuario AND cstatus='A';
    SELECT
        ISNULL((SELECT nmonto_ini FROM Turno WHERE id_turno=@id_turno),0) AS ImporteCaja,
        ISNULL((SELECT SUM(ntotal) FROM CbFactura WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno AND cstatus='P'),0) AS TotVentTurn,
        ISNULL((SELECT SUM(ndescuento) FROM CbFactura WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno AND cstatus='P'),0) AS TotDescTurn,
        ISNULL((SELECT COUNT(*) FROM CbFactura WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno AND cstatus='A'),0) AS DocAnulado;
END
GO

IF OBJECT_ID('webDatpos_CargarVendedoresDelDia','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarVendedoresDelDia; 
GO
CREATE PROCEDURE webDatpos_CargarVendedoresDelDia @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT U.cdsc_usuario AS cusu_crea, ISNULL(SUM(F.ntotal),0) AS nimporte_neto
    FROM CbFactura F INNER JOIN Usuarios U ON U.ccod_usuario=F.ccod_usuario AND U.ccod_empresa=F.ccod_cia
    WHERE F.ccod_cia=@ccod_cia AND CAST(F.fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND F.cstatus='P'
    GROUP BY U.cdsc_usuario ORDER BY nimporte_neto DESC;
END
GO

IF OBJECT_ID('webDatpos_CargarProductosDelDia','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarProductosDelDia; 
GO
CREATE PROCEDURE webDatpos_CargarProductosDelDia @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.cdsc_articulo, ISNULL(SUM(L.nimporte_neto),0) AS nimporte_neto
    FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND CAST(F.fecha_emision AS DATE)=CAST(GETDATE() AS DATE) AND F.cstatus='P'
    GROUP BY L.cdsc_articulo ORDER BY nimporte_neto DESC;
END
GO

IF OBJECT_ID('webDatpos_ArticuloSinStock','P') IS NOT NULL DROP PROCEDURE webDatpos_ArticuloSinStock; 
GO
CREATE PROCEDURE webDatpos_ArticuloSinStock @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT S.ccod_alm,A.cdsc_articulo FROM Stock S
    INNER JOIN Articulos A ON A.ccod_articulo=S.ccod_articulo AND A.ccod_cia=S.ccod_cia
    WHERE S.ccod_cia=@ccod_cia AND S.ncantidad<=0 AND A.cstatus='A';
END
GO

IF OBJECT_ID('webDatpos_CargarProductoSinStock','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarProductoSinStock; 
GO
CREATE PROCEDURE webDatpos_CargarProductoSinStock @ccod_cia VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT S.ccod_alm,A.cdsc_articulo FROM Stock S
    INNER JOIN Articulos A ON A.ccod_articulo=S.ccod_articulo AND A.ccod_cia=S.ccod_cia
    WHERE S.ccod_cia=@ccod_cia AND S.ncantidad<=0 AND A.cstatus='A';
END
GO

IF OBJECT_ID('webDatpos_CargarProductoConDescuento','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarProductoConDescuento; 
GO
CREATE PROCEDURE webDatpos_CargarProductoConDescuento @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.cdsc_articulo, ISNULL(L.ndes_max,0) AS ndes_max FROM LnListaPrecio L
    INNER JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.ndes_max>0 ORDER BY L.ndes_max DESC;
END
GO

IF OBJECT_ID('webDatpos_CargarTiendaDashboard','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarTiendaDashboard; 
GO
CREATE PROCEDURE webDatpos_CargarTiendaDashboard @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend,cnombr FROM Tiendas WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

IF OBJECT_ID('webDatpos_consultaBashboard','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaBashboard; 
GO
CREATE PROCEDURE webDatpos_consultaBashboard @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL((SELECT SUM(nmonto_ini) FROM Turno WHERE ccod_cia=@ccod_cia AND cstatus='A'),0) AS ImporteCaja,
        ISNULL((SELECT SUM(ntotal) FROM CbFactura WHERE ccod_cia=@ccod_cia AND fecha_emision BETWEEN @fchDesde AND @fchHasta AND cstatus='P'),0) AS VentaDelDia,
        ISNULL((SELECT COUNT(DISTINCT ccod_usuario) FROM Turno WHERE ccod_cia=@ccod_cia AND cstatus='A'),0) AS CantUsuarios,
        ISNULL((SELECT COUNT(*) FROM Usuarios WHERE ccod_empresa=@ccod_cia AND id_estado=1),0) AS UsuRegistrados;
END
GO

IF OBJECT_ID('webDatpos_datosGenerales','P') IS NOT NULL DROP PROCEDURE webDatpos_datosGenerales; 
GO
CREATE PROCEDURE webDatpos_datosGenerales @CCOD_CIA VARCHAR(20),@ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT T.cnombr AS cdsc_tienda, AL.cdsc_alm, CA.cdsc_caja,
           ISNULL(LP1.cdsc_cblistpre,'') AS cdsc_listpreNorm, ISNULL(LP2.cdsc_cblistpre,'') AS cdsc_listprePref,
           CAST(ISNULL(TI.nlista_pre_normal,1) AS VARCHAR) AS nlista_pre_normal, CAST(ISNULL(TI.nlista_pre_preferencial,2) AS VARCHAR) AS nlista_pre_preferencial,
           U.cdsc_usuario AS cdescripcion
    FROM Usuarios U
    LEFT JOIN Tiendas TI ON TI.ccod_tiend=U.ccod_tiend AND TI.ccod_cia=U.ccod_empresa
    LEFT JOIN Almacenes AL ON AL.ccod_alm=U.ccod_almacen AND AL.ccod_cia=U.ccod_empresa
    LEFT JOIN Cajas CA ON CA.ccod_caja=U.ccod_caja AND CA.ccod_cia=U.ccod_empresa
    LEFT JOIN Tiendas T ON T.ccod_tiend=U.ccod_tiend AND T.ccod_cia=U.ccod_empresa
    LEFT JOIN CbListaPrecio LP1 ON LP1.ccod_cblistpre=CAST(TI.nlista_pre_normal AS VARCHAR) AND LP1.ccod_cia=U.ccod_empresa
    LEFT JOIN CbListaPrecio LP2 ON LP2.ccod_cblistpre=CAST(TI.nlista_pre_preferencial AS VARCHAR) AND LP2.ccod_cia=U.ccod_empresa
    WHERE U.ccod_empresa=@CCOD_CIA AND U.ccod_usuario=@ccod_usuario;
END
GO

IF OBJECT_ID('webDatpos_CargarDCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarDCaja; 
GO
CREATE PROCEDURE webDatpos_CargarDCaja @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT DISTINCT F.ccod_caja FROM CbFactura F
    WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta
    AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda);
END
GO

IF OBJECT_ID('webDatpos_CargarUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarUsuario; 
GO
CREATE PROCEDURE webDatpos_CargarUsuario @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT DISTINCT F.ccod_usuario FROM CbFactura F
    WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta
    AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda);
END
GO

IF OBJECT_ID('webDatpos_CargarDiagramaUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarDiagramaUsuario; 
GO
CREATE PROCEDURE webDatpos_CargarDiagramaUsuario @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.ccod_usuario,F.ccod_caja,ISNULL(SUM(F.ntotal),0) AS ntotal
    FROM CbFactura F WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta
    AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)
    GROUP BY F.ccod_usuario,F.ccod_caja;
END
GO

IF OBJECT_ID('webDatpos_CargarDiagramaCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarDiagramaCaja; 
GO
CREATE PROCEDURE webDatpos_CargarDiagramaCaja @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.ccod_usuario,F.ccod_caja,ISNULL(SUM(F.ntotal),0) AS ntotal
    FROM CbFactura F WHERE F.ccod_cia=@ccod_cia AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta
    AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda)
    GROUP BY F.ccod_usuario,F.ccod_caja;
END
GO

IF OBJECT_ID('webDatpos_cargarDiagramaBarrasDia','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaBarrasDia; 
GO
CREATE PROCEDURE webDatpos_cargarDiagramaBarrasDia @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT DATENAME(WEEKDAY,fecha_emision) AS cdsc_dia FROM CbFactura
    WHERE ccod_cia=@ccod_cia AND CAST(fecha_emision AS DATE)=CAST(GETDATE() AS DATE)
    AND (@ccod_tienda='' OR ccod_tiend=@ccod_tienda) AND cstatus='P'
    GROUP BY DATENAME(WEEKDAY,fecha_emision);
END
GO

IF OBJECT_ID('webDatpos_cargarDiagramaBarrasEfectivo','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaBarrasEfectivo; 
GO
CREATE PROCEDURE webDatpos_cargarDiagramaBarrasEfectivo @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT CONVERT(VARCHAR,fecha_emision,103) AS cdsc_dia, ISNULL(SUM(ntotal),0) AS nmonto
    FROM CbFactura WHERE ccod_cia=@ccod_cia AND CAST(fecha_emision AS DATE)=CAST(GETDATE() AS DATE)
    AND (@ccod_tienda='' OR ccod_tiend=@ccod_tienda) AND cstatus='P'
    GROUP BY CONVERT(VARCHAR,fecha_emision,103);
END
GO

IF OBJECT_ID('webDatpos_cargarDiagramaBarrasTarjeta','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaBarrasTarjeta; 
GO
CREATE PROCEDURE webDatpos_cargarDiagramaBarrasTarjeta @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT CONVERT(VARCHAR,C.dfch_crea,103) AS cdsc_dia, ISNULL(SUM(D.nmonto),0) AS nmonto
    FROM LnCobranza D INNER JOIN CbCobranza C ON C.id_cbcajac=D.id_cbcajac AND C.ccod_cia=D.ccod_cia
    WHERE D.ccod_cia=@ccod_cia AND CAST(C.dfch_crea AS DATE)=CAST(GETDATE() AS DATE)
    AND D.cnum_tarje IS NOT NULL AND D.cnum_tarje<>''
    GROUP BY CONVERT(VARCHAR,C.dfch_crea,103);
END
GO

ALTER TABLE [DatPos_EMP01].[dbo].[CbCobranza]
ADD [cnom_tarje] VARCHAR(100) NOT NULL DEFAULT '';
IF OBJECT_ID('webDatpos_cargarDiagramaPastel','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarDiagramaPastel; 
GO
CREATE PROCEDURE webDatpos_cargarDiagramaPastel @ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ISNULL(C.cnom_tarje,'Efectivo') AS name, ISNULL(SUM(D.nmonto),0) AS y
    FROM LnCobranza D INNER JOIN CbCobranza C ON C.id_cbcajac=D.id_cbcajac AND C.ccod_cia=D.ccod_cia
    WHERE D.ccod_cia=@ccod_cia AND CAST(C.dfch_crea AS DATE)=CAST(GETDATE() AS DATE)
    AND (@ccod_caja='' OR C.ccod_caja=@ccod_caja)
    GROUP BY C.cnom_tarje;
END
GO

IF OBJECT_ID('webDatpos_DiasRestantes','P') IS NOT NULL DROP PROCEDURE webDatpos_DiasRestantes; 
GO
CREATE PROCEDURE webDatpos_DiasRestantes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT 'Activo' AS cdescripcion;
END
GO

PRINT '✓ SPs Dashboard completos creados.';
GO
