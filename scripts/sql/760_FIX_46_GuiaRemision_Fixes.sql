/*  760 – FIX 46: Guía de Remisión – múltiples correcciones
 *
 *  1. appDatpos_validarArticuloEnAlm: añadir ccod_artSunat (col [5])
 *     para que GuiaRemision.js pueda poblar el campo SUNAT al validar artículo.
 *
 *  Los demás fixes son solo en PHP/JS (no requieren cambios en BD):
 *  - VerificarCantaArti: cambió de webDatpos_validarArticuloAlmacenSalida
 *    a webDatpos_articuloCantaArti (igual que salida/transferencia)
 *  - ConsultarAlamcenes API: ahora mapea las 8 columnas del SP
 *  - ObtenerNumerador API: mapeo corregido a cdoc_tipo/cdoc_serie/cstatus
 *  - Server-side: webDatpos_consultarNumeradores → webDatpos_ObtenerNumerador
 */

-- 1) Actualizar SP para incluir ccod_artSunat
IF OBJECT_ID('appDatpos_validarArticuloEnAlm','P') IS NOT NULL
BEGIN
    EXEC('
    ALTER PROCEDURE appDatpos_validarArticuloEnAlm
        @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20)
    AS BEGIN SET NOCOUNT ON;
        SELECT
            A.ccod_articulo,
            A.cdsc_articulo,
            ISNULL(A.uni_medi, '''') AS uni_medi,
            ISNULL(S.ncantidad, 0) AS ncantidad,
            ISNULL(S.ncosto, 0)    AS ncosto,
            ISNULL(A.ccod_artSunat, '''') AS ccod_artSunat
        FROM Articulos A
        LEFT JOIN Stock S
          ON S.ccod_articulo = A.ccod_articulo
         AND S.ccod_cia      = A.ccod_cia
         AND S.ccod_alm      = @ccod_alm
        WHERE A.ccod_cia      = @ccod_cia
          AND A.ccod_articulo = @ccod_articulo
          AND A.cstatus       = ''A'';
    END
    ');
END
GO

PRINT '760_FIX_46 aplicado correctamente.';
GO
