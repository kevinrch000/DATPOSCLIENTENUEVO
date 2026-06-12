/* =====================================================================
   770 – FIX 47: GuíaRemisión SPs adicionales + datos + Filtros Operaciones
   Ejecutar en DatPos_EMP01

   1. webDatpos_consultarArticulosSalida: agregar ccod_artSunat (col [5])
      para modal artículos en GuíaRemisión.
   2. webDatpos_ConsultarOperaciones: corregir query — usar tabla
      TipoOperacion directamente (columna ctipo_transferencia no existe).
   3. Insertar NumeradorAlmacen tipo RT para GuíaRemisión (si no existe).
   4. Popular Articulos.ccod_artSunat vacíos con ccod_articulo como placeholder.
===================================================================== */
USE DatPos_EMP01;
GO

-- ─────────────────────────────────────────────────────────────────────
-- 1) webDatpos_consultarArticulosSalida: 6 columnas (añade ccod_artSunat)
-- ─────────────────────────────────────────────────────────────────────
IF OBJECT_ID('webDatpos_consultarArticulosSalida','P') IS NOT NULL
    DROP PROCEDURE webDatpos_consultarArticulosSalida;
GO
CREATE PROCEDURE webDatpos_consultarArticulosSalida
    @ccod_cia VARCHAR(20), @almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,                          -- [0]
           A.cdsc_articulo,                          -- [1]
           ISNULL(A.ccod_lin,'')       AS ccod_lin,  -- [2]
           ISNULL(S.ncantidad,0)       AS ncantidad, -- [3]
           ISNULL(S.ncosto,0)          AS ncosto,    -- [4]
           ISNULL(A.ccod_artSunat,'')  AS ccod_artSunat -- [5]
    FROM Articulos A
    INNER JOIN Stock S ON S.ccod_articulo=A.ccod_articulo
                      AND S.ccod_alm=@almacen
                      AND S.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND S.ncantidad>0
    ORDER BY A.cdsc_articulo;
END
GO
PRINT '  [1/4] webDatpos_consultarArticulosSalida — 6 cols (+ ccod_artSunat)';
GO

-- ─────────────────────────────────────────────────────────────────────
-- 2) webDatpos_ConsultarOperaciones: query TipoOperacion directamente
-- ─────────────────────────────────────────────────────────────────────
IF OBJECT_ID('webDatpos_ConsultarOperaciones','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultarOperaciones;
GO
CREATE PROCEDURE webDatpos_ConsultarOperaciones @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_tipoper                                  AS id_ctoper,            -- [0] Integer
        ccod_tipoper                                AS ccod_toper,           -- [1] String
        cdsc_tipoper                                AS cdsc_toper,           -- [2] String
        ctipo_flag,                                                          -- [3] String (I/S/T)
        ''                                          AS ctipo_transferencia,  -- [4] String (vacío)
        CASE WHEN cstatus='A' THEN 1 ELSE 0 END    AS cstatus               -- [5] Integer
    FROM TipoOperacion
    WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO
PRINT '  [2/4] webDatpos_ConsultarOperaciones — usa TipoOperacion directamente';
GO

-- ─────────────────────────────────────────────────────────────────────
-- 3) NumeradorAlmacen tipo RT para GuíaRemisión (idempotente)
-- ─────────────────────────────────────────────────────────────────────
IF NOT EXISTS (
    SELECT 1 FROM NumeradorAlmacen
    WHERE ctip_doc='RT'
)
BEGIN
    DECLARE @ccod_alm VARCHAR(20);
    SELECT TOP 1 @ccod_alm = ccod_alm FROM Almacenes WHERE ccod_cia='EMP01' AND cstatus='A';
    IF @ccod_alm IS NOT NULL
    BEGIN
        INSERT INTO NumeradorAlmacen (ccod_cia, ccod_alm, cserie, nnumero, ctip_doc, cdsc_numeralmacen)
        VALUES ('EMP01', @ccod_alm, 'T002', 0, 'RT', 'GUIA REMISION');
        PRINT '  [3/4] NumeradorAlmacen RT insertado (ccod_alm=' + @ccod_alm + ')';
    END
    ELSE
        PRINT '  [3/4] SKIP: No hay almacén activo para insertar NumeradorAlmacen RT';
END
ELSE
    PRINT '  [3/4] SKIP: NumeradorAlmacen RT ya existe';
GO

-- ─────────────────────────────────────────────────────────────────────
-- 4) Popular Articulos.ccod_artSunat vacíos (placeholder = ccod_articulo)
-- ─────────────────────────────────────────────────────────────────────
UPDATE Articulos
SET ccod_artSunat = ccod_articulo
WHERE (ccod_artSunat IS NULL OR ccod_artSunat = '');

PRINT '  [4/4] Articulos.ccod_artSunat vacíos populados con ccod_articulo';
GO

PRINT '770_FIX_47 aplicado correctamente.';
GO
