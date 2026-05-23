-- ============================================================================
-- FIX_30: SPs faltantes para módulo Operaciones (Ingresos / Salidas / Transf.)
--
-- Aplicar sobre la BD del tenant (ej. DatPos_EMP01).
-- Crea wrappers para mantener compatibilidad con el VB original.
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar según tenant
GO

-- ----------------------------------------------------------------------------
-- 1) appDatpos_consultaNumeradorPorAlm
--    Devuelve el primer numerador del almacén (cserie, nnumero) sin filtrar
--    por tip_doc. Reemplaza el antiguo sp_consultarnumerador (singular).
-- ----------------------------------------------------------------------------
IF OBJECT_ID('appDatpos_consultaNumeradorPorAlm','P') IS NOT NULL DROP PROCEDURE appDatpos_consultaNumeradorPorAlm;
GO
CREATE PROCEDURE appDatpos_consultaNumeradorPorAlm
    @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT TOP 1 cserie, nnumero
    FROM NumeradorAlmacen
    WHERE ccod_cia = @ccod_cia AND ccod_alm = @ccod_alm
    ORDER BY id_ctalmac;
END
GO

-- ----------------------------------------------------------------------------
-- 2) appDatpos_validarArticulo
--    Dado un código de artículo, devuelve (ccod_articulo, cdsc_articulo, uni_medi)
--    para auto-completar el modal de detalle en Ingresos / Salidas / Transferencias.
--    Reemplaza el antiguo sp_validararticulo.
-- ----------------------------------------------------------------------------
IF OBJECT_ID('appDatpos_validarArticulo','P') IS NOT NULL DROP PROCEDURE appDatpos_validarArticulo;
GO
CREATE PROCEDURE appDatpos_validarArticulo
    @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_articulo, cdsc_articulo, uni_medi
    FROM Articulos
    WHERE ccod_cia = @ccod_cia
      AND ccod_articulo = @ccod_articulo
      AND cstatus = 'A';
END
GO

PRINT 'FIX_30: SPs Operaciones creados/actualizados';
