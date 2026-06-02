/* ========================================================================
   MODIFY_921 / FIX_70
   sp_consultararticuloprecio: Conversion failed varchar 'ART003' to int.

   Error del servidor:
     S.3  [sp_consultararticuloprecio] — SQLSTATE 22018, code 245
     Endpoint: POST /api/facturacion_api.php?method=ConsultarArticuloPrecio

   Causa: FIX_16 (310) usa CAST(@codigo AS INT) en el WHERE, que falla
   cuando @codigo es alfanumerico (ej. 'ART003'). Se corrige con
   TRY_CONVERT para aceptar tanto id numerico como codigo alfanumerico.

   Ejecutar en DatPos_EMP01
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 921 / FIX 70: ArticuloPrecio tolerante a alfanumerico ==';

/* ─── sp_consultararticuloprecio ───────────────────────────────────────
   @codigo puede ser INT (id_articulo) o VARCHAR (ccod_articulo).
   Si TRY_CONVERT a INT da NULL => buscar por ccod_articulo.
   Si da entero => buscar por id_articulo.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL
    DROP PROCEDURE sp_consultararticuloprecio;
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
        CASE WHEN A.cstatus='A' THEN 1 ELSE 0 END          AS state,         -- [5]
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
    WHERE A.ccod_cia = @ccod_cia
      AND (
          (TRY_CONVERT(INT, @codigo) IS NOT NULL AND A.id_articulo = TRY_CONVERT(INT, @codigo))
          OR A.ccod_articulo = @codigo
      );
END
GO

PRINT 'OK - FIX 70 completo: sp_consultararticuloprecio tolerante a alfanumerico.';
GO
