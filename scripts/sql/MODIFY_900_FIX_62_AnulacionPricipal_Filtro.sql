-- ============================================================
-- MODIFY_900_FIX_62_AnulacionPricipal_Filtro.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : `webDatpos_anulacionPricipal` mostraba SOLO documentos
--          ya anulados (`F.cstatus='A'`), pero el flujo de la
--          pantalla Anulacion.php es justo lo contrario: el
--          usuario debe ver documentos vigentes ('P') que aún
--          se pueden anular. Además parseaba @fchDesde/@fchHasta
--          como string lo cual fallaba para formato dd/MM/yyyy.
-- Cambio : 1) Filtrar por F.cstatus='P'.
--          2) Aceptar cdoc IN ('BV','FV','FA','BO','NV').
--          3) Comparar fechas con TRY_CONVERT(..., 103).
--          4) Mantiene shape de columnas para mapper PHP.
-- Orden  : Ejecutar después de MODIFY_890.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_anulacionPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_anulacionPricipal;
GO
CREATE PROCEDURE webDatpos_anulacionPricipal
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
        ISNULL(C.cdsc_coa, '') AS cdsc_coa,
        F.ntotal,
        F.cstatus
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus = 'P'
      AND F.cdoc IN ('BV','FV','FA','BO','NV','NC','ND')
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
PRINT 'MODIFY_900 aplicado: webDatpos_anulacionPricipal lista docs vigentes (P) elegibles para anulación.';
