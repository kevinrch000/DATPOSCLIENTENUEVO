/* =====================================================================
   FIX 16 — Corrección de sp_consultararticuloprecio y sp_consultararticulopreciocodigo
   
   Error: BEPrecios.state es Integer pero el SP devolvía cstatus='A' (String)
   
   Mapeo correcto (Facturacion.aspx.vb líneas 237-244):
   [0] npre_uni      → npre_uni  (String/Decimal)
   [1] cdsc_articulo → cdsc_articulo (String)
   [2] igv           → igv (String/Decimal)
   [3] isc           → isc (String/Decimal)
   [4] ctip_articulo → ctip_articulo (String)
   [5] state         → Integer: 1 si activo, 0 si inactivo  ← CORRECCIÓN
   [6] npre_costo    → npre_costo (String/Decimal)
   [7] ndes_max      → ndes_max (String/Decimal)
   
   ConsultarArticuloPrecioCodigo (líneas 200-208) lee además:
   [8] id_cblistpre  → id_cblistpre (Integer)
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* sp_consultararticuloprecio — @ccod_cia, @ccod_usuario, @codigo (id_articulo), @ccod_almacen */
IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL DROP PROCEDURE sp_consultararticuloprecio;
GO
CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),
    @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(L.npre_uni, 0)                              AS npre_uni,      -- [0]
        ISNULL(A.cdsc_articulo, '')                        AS cdsc_articulo, -- [1]
        ISNULL(CAST(C.nigv AS NVARCHAR(20)), '18')         AS igv,           -- [2]
        ISNULL(CAST(C.nisc AS NVARCHAR(20)), '0')          AS isc,           -- [3]
        ISNULL(A.ctip_articulo, 'P')                       AS ctip_articulo, -- [4]
        CASE WHEN A.cstatus='A' THEN 1 ELSE 0 END          AS state,         -- [5] Integer
        ISNULL(L.npre_uni, 0)                              AS npre_costo,    -- [6]
        ISNULL(L.ndes_max, 0)                              AS ndes_max       -- [7]
    FROM Articulos A
    LEFT JOIN (
        SELECT L2.ccod_articulo, L2.npre_uni, L2.ndes_max, CB2.id_cblistpre
        FROM CbListaPrecio CB2
        JOIN LnListaPrecio L2 ON L2.ccod_cia=CB2.ccod_cia AND L2.ccod_cblistpre=CB2.ccod_cblistpre
        WHERE CB2.ccod_cia=@ccod_cia AND CB2.cstatus='A'
    ) L ON L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.id_articulo=CAST(@codigo AS INT);
END
GO

/* sp_consultararticulopreciocodigo — @ccod_cia, @ccod_usuario, @codigo (ccod_articulo), @ccod_almacen */
IF OBJECT_ID('sp_consultararticulopreciocodigo','P') IS NOT NULL DROP PROCEDURE sp_consultararticulopreciocodigo;
GO
CREATE PROCEDURE sp_consultararticulopreciocodigo
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),
    @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(L.npre_uni, 0)                              AS npre_uni,      -- [0]
        ISNULL(A.cdsc_articulo, '')                        AS cdsc_articulo, -- [1]
        ISNULL(CAST(C.nigv AS NVARCHAR(20)), '18')         AS igv,           -- [2]
        ISNULL(CAST(C.nisc AS NVARCHAR(20)), '0')          AS isc,           -- [3]
        ISNULL(A.ctip_articulo, 'P')                       AS ctip_articulo, -- [4]
        CASE WHEN A.cstatus='A' THEN 1 ELSE 0 END          AS state,         -- [5] Integer
        ISNULL(L.npre_uni, 0)                              AS npre_costo,    -- [6]
        ISNULL(L.ndes_max, 0)                              AS ndes_max,      -- [7]
        ISNULL(L.id_cblistpre, 0)                          AS id_cblistpre   -- [8] Integer
    FROM Articulos A
    LEFT JOIN (
        SELECT L2.ccod_articulo, L2.npre_uni, L2.ndes_max, CB2.id_cblistpre
        FROM CbListaPrecio CB2
        JOIN LnListaPrecio L2 ON L2.ccod_cia=CB2.ccod_cia AND L2.ccod_cblistpre=CB2.ccod_cblistpre
        WHERE CB2.ccod_cia=@ccod_cia AND CB2.cstatus='A'
    ) L ON L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@codigo AND A.cstatus='A';
END
GO

/* Prueba rápida */
EXEC sp_consultararticulopreciocodigo 'EMP01','ADMIN','ART001','ALM001';
GO
PRINT 'OK - FIX 16 completo.';
GO
