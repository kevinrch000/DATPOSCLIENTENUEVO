-- ============================================================
-- MODIFY_880_FIX_60_NotaDebito_NotaCreditoPricipal_Filtro.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : `webDatpos_NotaCreditoPricipal` (usado por NotaDebito.php
--          y NotaCredito.php "Lista") filtraba por
--          `F.cdoc IN ('F','B')` cuando los valores reales son
--          'BV', 'FV', 'FA', 'BO'. Además parseaba @fchDesde/
--          @fchHasta como BETWEEN sobre VARCHAR, lo cual fallaba
--          con formato dd/MM/yyyy.
-- Cambio : 1) Aceptar cdoc IN ('BV','FV','FA','BO').
--          2) Comparar fechas convertidas explícitamente con
--             TRY_CONVERT(DATETIME, ..., 103).
--          3) Mantener mismo shape de columnas (id_cbfact, cdoc,
--             cserie, nnumero, fecha_emision, ntotal, cdsc_coa,
--             ccod_coa) para no romper el mapper PHP.
-- Orden  : Ejecutar después de MODIFY_870.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_NotaCreditoPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_NotaCreditoPricipal;
GO
CREATE PROCEDURE webDatpos_NotaCreditoPricipal
    @cdoc_seri    VARCHAR(5),
    @serie        VARCHAR(10),
    @correlativo  VARCHAR(20),
    @ccod_tienda  VARCHAR(20),
    @ccod_coa     VARCHAR(20),
    @fchDesde     VARCHAR(20),
    @fchHasta     VARCHAR(20),
    @CodCia       VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        F.id_cbfact,
        F.cdoc,
        F.cserie,
        F.nnumero,
        CONVERT(VARCHAR(10), F.fecha_emision, 103) AS fecha_emision,
        F.ntotal,
        ISNULL(C.cdsc_coa, '') AS cdsc_coa,
        ISNULL(F.ccod_coa, '') AS ccod_coa
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc IN ('BV','FV','FA','BO')
      AND F.cstatus = 'P'
      AND (@cdoc_seri = '' OR F.cdoc = @cdoc_seri)
      AND (@serie = '' OR F.cserie = @serie)
      AND (@correlativo = '' OR F.nnumero = TRY_CAST(@correlativo AS INT))
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
      AND (@fchDesde = '' OR F.fecha_emision >= TRY_CONVERT(DATETIME, @fchDesde, 103))
      AND (@fchHasta = '' OR F.fecha_emision <  DATEADD(DAY, 1, TRY_CONVERT(DATETIME, @fchHasta, 103)))
    ORDER BY F.fecha_emision DESC;
END
GO
PRINT 'MODIFY_880 aplicado: webDatpos_NotaCreditoPricipal corrige filtros cdoc + fechas + filtros opcionales.';
