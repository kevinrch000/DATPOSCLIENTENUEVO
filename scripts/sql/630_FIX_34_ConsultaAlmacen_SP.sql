USE DatPos_EMP01;
GO

/* ───────────────────────────────────────────────────────────────────
   FIX 34 — Actualizar SP webDatpos_consultasAlmacenPrincipal
   Agrega: JOIN con UnidadMedida para descripción, columna cigv,
           y columna calculada costo_tot.
   Antes devolvía 7 columnas; ahora devuelve 8 que el JS espera:
     ccod_articulo, cdsc_articulo, cdsc_unidadmedida, cdsc_lin,
     ncantidad, npre_costo, costo_tot, cigv
   ─────────────────────────────────────────────────────────────────── */

IF OBJECT_ID('webDatpos_consultasAlmacenPrincipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_consultasAlmacenPrincipal;
GO

CREATE PROCEDURE webDatpos_consultasAlmacenPrincipal
    @ccod_articulo VARCHAR(50),
    @cdsc_articulo VARCHAR(200),
    @ccod_lin      VARCHAR(20),
    @Codalmacen    VARCHAR(20),
    @CodCia        VARCHAR(20)
AS BEGIN
    SET NOCOUNT ON;

    SELECT
        A.ccod_articulo,
        A.cdsc_articulo,
        ISNULL(UM.cdsc_unimed, A.uni_medi) AS cdsc_unidadmedida,
        ISNULL(F.cdsc_lin, '')                    AS cdsc_lin,
        ISNULL(S.ncantidad, 0)                    AS ncantidad,
        ISNULL(S.ncosto, 0)                       AS npre_costo,
        ISNULL(S.ncantidad, 0) * ISNULL(S.ncosto, 0) AS costo_tot,
        ISNULL(A.cigv, '')                        AS cigv
    FROM Articulos A
        LEFT JOIN Familias      F  ON F.ccod_lin       = A.ccod_lin
                                   AND F.ccod_cia       = A.ccod_cia
        LEFT JOIN Stock         S  ON S.ccod_articulo   = A.ccod_articulo
                                   AND S.ccod_alm        = @Codalmacen
                                   AND S.ccod_cia        = A.ccod_cia
        LEFT JOIN Almacenes     AL ON AL.ccod_alm       = @Codalmacen
                                   AND AL.ccod_cia       = A.ccod_cia
        LEFT JOIN UnidadMedida  UM ON UM.ccod_unimed = A.uni_medi
                                   AND UM.ccod_cia       = A.ccod_cia
    WHERE A.ccod_cia = @CodCia
      AND A.cstatus  = 'A'
      AND (@ccod_articulo = '' OR A.ccod_articulo LIKE '%' + @ccod_articulo + '%')
      AND (@cdsc_articulo = '' OR A.cdsc_articulo LIKE '%' + @cdsc_articulo + '%')
      AND (@ccod_lin      = '' OR A.ccod_lin = @ccod_lin)
    ORDER BY A.cdsc_articulo;
END
GO

PRINT '✓ FIX 34: SP webDatpos_consultasAlmacenPrincipal actualizado (8 columnas con UnidadMedida, cigv, costo_tot).';
GO
