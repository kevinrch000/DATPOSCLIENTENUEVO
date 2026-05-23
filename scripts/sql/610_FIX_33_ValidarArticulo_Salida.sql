-- ============================================================================
-- FIX_33: SP para validar artículo en almacén (búsqueda por Enter en Salidas)
--   Devuelve cdsc_articulo, ncantidad (stock), ncosto para que el modal
--   "Agregar" auto-complete los datos cuando el usuario escribe el código
--   y presiona Enter.
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar según tenant
GO

IF OBJECT_ID('appDatpos_validarArticuloEnAlm','P') IS NOT NULL DROP PROCEDURE appDatpos_validarArticuloEnAlm;
GO
CREATE PROCEDURE appDatpos_validarArticuloEnAlm
    @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        A.ccod_articulo,
        A.cdsc_articulo,
        ISNULL(A.uni_medi, '') AS uni_medi,
        ISNULL(S.ncantidad, 0) AS ncantidad,
        ISNULL(S.ncosto, 0)    AS ncosto
    FROM Articulos A
    LEFT JOIN Stock S
      ON S.ccod_articulo = A.ccod_articulo
     AND S.ccod_cia      = A.ccod_cia
     AND S.ccod_alm      = @ccod_alm
    WHERE A.ccod_cia      = @ccod_cia
      AND A.ccod_articulo = @ccod_articulo
      AND A.cstatus       = 'A';
END
GO

PRINT 'FIX_33: appDatpos_validarArticuloEnAlm creado';
