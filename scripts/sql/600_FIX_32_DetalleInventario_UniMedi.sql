-- ============================================================================
-- FIX_32: agregar uni_medi al detalle de inventario (sp_consultarinventariodetalle)
--          y al detalle de salida (webDatpos_consultarInventarioDetalleSalida)
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar según tenant
GO

IF OBJECT_ID('sp_consultarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_consultarinventariodetalle;
GO
CREATE PROCEDURE sp_consultarinventariodetalle @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, A.cdsc_articulo,
           ISNULL(A.uni_medi, '') AS csim_unidadmedida,
           L.ncantidad, L.ncosto,
           (L.ncantidad * L.ncosto) AS nimporte
    FROM LnInventario L
    LEFT JOIN Articulos A ON A.ccod_articulo = L.ccod_articulo AND A.ccod_cia = L.ccod_cia
    WHERE L.ccod_cia = @ccod_cia AND L.id_cbinve = @id;
END
GO

IF OBJECT_ID('webDatpos_consultarInventarioDetalleSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarInventarioDetalleSalida;
GO
CREATE PROCEDURE webDatpos_consultarInventarioDetalleSalida @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, A.cdsc_articulo,
           ISNULL(A.uni_medi, '') AS csim_unidadmedida,
           L.ncantidad, L.ncosto
    FROM LnInventario L
    LEFT JOIN Articulos A ON A.ccod_articulo = L.ccod_articulo AND A.ccod_cia = L.ccod_cia
    WHERE L.ccod_cia = @ccod_cia AND L.id_cbinve = @id;
END
GO

PRINT 'FIX_32: SPs detalle inventario actualizados con uni_medi';
