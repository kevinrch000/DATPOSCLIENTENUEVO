-- ============================================================
-- MODIFY_870_FIX_59_ConsultarDocumentosNotaCredito_Filtro.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : webDatpos_ConsultarDocumentosNotaCredito filtraba por
--          F.cstatus='NC' (que es estado, no tipo de documento)
--          así que la pestaña "Lista" de NotaCredito.php quedaba
--          siempre vacía aunque se hayan generado NC reales.
-- Cambio : Filtrar por F.cdoc IN ('NC','ND') y aceptar filtros
--          opcionales por documento/serie/correlativo. Mantiene
--          el shape de columnas existente para no romper el
--          mapper de api/notacredito_api.php (8 columnas).
-- Orden  : Ejecutar después de MODIFY_860.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_ConsultarDocumentosNotaCredito','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultarDocumentosNotaCredito;
GO
CREATE PROCEDURE webDatpos_ConsultarDocumentosNotaCredito
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
        CONVERT(VARCHAR(10), F.dfch_crea, 103) AS dfch_doc,
        F.ntotal,
        F.cstatus,
        ISNULL(C.cdsc_coa, '') AS cdsc_coa
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc IN ('NC', 'ND')
      AND (@cdoc_seri = '' OR F.cdoc = @cdoc_seri)
      AND (@serie = '' OR F.cserie = @serie)
      AND (@correlativo = '' OR F.nnumero = TRY_CAST(@correlativo AS INT))
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
      AND (@fchDesde = '' OR F.dfch_crea >= TRY_CONVERT(DATETIME, @fchDesde, 103))
      AND (@fchHasta = '' OR F.dfch_crea < DATEADD(DAY, 1, TRY_CONVERT(DATETIME, @fchHasta, 103)))
    ORDER BY F.id_cbfact DESC;
END
GO
PRINT 'MODIFY_870 aplicado: webDatpos_ConsultarDocumentosNotaCredito filtra por F.cdoc IN (NC,ND) con filtros opcionales.';
