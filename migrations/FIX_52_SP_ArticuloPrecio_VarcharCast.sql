/* =========================================================================
   FIX_52 — Corrección sp_consultararticuloprecio: CAST varchar → INT
   =========================================================================

   ERROR:  Conversion failed when converting varchar 'ART003' to data type int.
   CAUSA:  El SP usaba A.id_articulo = CAST(@codigo AS INT) pero @codigo
           recibe el código alfanumérico del artículo (ccod_articulo), no el
           id numérico. Cuando el código es 'ART003' el CAST falla.
   FIX:    Cambiar el WHERE para buscar por A.ccod_articulo = @codigo.

   Ejecutar en DatPos_EMP01
   ========================================================================= */

USE DatPos_EMP01;
GO

IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL
    DROP PROCEDURE sp_consultararticuloprecio;
GO

CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),    -- ccod_articulo (alfanumérico) O id_articulo (numérico)
    @ccod_almacen VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    -- Intentar buscar primero por ccod_articulo; si no hay resultado y @codigo
    -- es puramente numérico, buscar por id_articulo como fallback.
    -- El costo del artículo vive en Stock (ncosto), no en Articulos.
    -- Se usa LEFT JOIN a Stock por almacén (@ccod_almacen) para obtenerlo.
    SELECT TOP 1
        ISNULL(L.npre_uni, 0)                              AS npre_uni,      -- [0]
        ISNULL(A.cdsc_articulo, '')                        AS cdsc_articulo, -- [1]
        ISNULL(C.nigv, 18)                                 AS igv,           -- [2]
        ISNULL(C.nisc, 0)                                  AS isc,           -- [3]
        A.ctip_articulo,                                                      -- [4]
        A.cstatus,                                                            -- [5]
        ISNULL(S.ncosto, 0)                                AS npre_costo,    -- [6] costo de Stock
        ISNULL(L.ndes_max, 0)                              AS ndes_max       -- [7]
    FROM Articulos A
    LEFT JOIN (
        SELECT L2.ccod_articulo, L2.npre_uni, L2.ndes_max
        FROM CbListaPrecio CB2
        JOIN LnListaPrecio L2
            ON L2.ccod_cia = CB2.ccod_cia
           AND L2.ccod_cblistpre = CB2.ccod_cblistpre
        WHERE CB2.ccod_cia = @ccod_cia AND CB2.cstatus = 'A'
    ) L ON L.ccod_articulo = A.ccod_articulo
    LEFT JOIN Stock S
        ON S.ccod_cia      = A.ccod_cia
       AND S.ccod_articulo = A.ccod_articulo
       AND S.ccod_alm      = @ccod_almacen
    LEFT JOIN ConfigGeneral C ON C.ccod_cia = A.ccod_cia
    WHERE A.ccod_cia = @ccod_cia
      AND (
          A.ccod_articulo = @codigo
          OR (ISNUMERIC(@codigo) = 1
              AND A.id_articulo = CAST(CASE WHEN ISNUMERIC(@codigo)=1
                                           THEN @codigo ELSE '0' END AS INT))
      )
      AND A.cstatus = 'A';
END
GO

PRINT 'OK - FIX_52: sp_consultararticuloprecio corregido (sin CAST forzado a INT).';
GO
