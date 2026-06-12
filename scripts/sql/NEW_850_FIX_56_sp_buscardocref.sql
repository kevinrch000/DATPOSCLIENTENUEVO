-- ============================================================
-- NEW_850_FIX_56_sp_buscardocref.sql
-- ============================================================
-- Estado : NEW
-- Motivo : api/notacredito_api.php (case 'BuscarDocRef') invocaba
--          el SP `sp_buscardocref` con (@ccod_cia, @codigo, @serie,
--          @correlativo) que NO existía en la BD (sólo existía la
--          versión legacy `webDatpos_BuscarDocRef` con shape
--          insuficiente).  Esto bloqueaba el flujo NC/ND:
--          EjecutarRef() recibía {"d":[]} y no llenaba importe,
--          fecha, cliente, ni id_cbinve.
-- Cambio : Crear sp_buscardocref devolviendo en ESTE orden:
--           ntotal, dfch_crea, cdsc_coa, ccod_coa,
--           id_cbfact, cod_motivo, montodisponible, id_cbinve
--          (ver mapping en api/notacredito_api.php case
--          BuscarDocRef líneas 67-72).
--          `montodisponible` = ntotal - SUM(NC ya aplicadas).
-- Orden  : Ejecutar después de 460_FIX_22B y 760_FIX_46.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('sp_buscardocref','P') IS NOT NULL DROP PROCEDURE sp_buscardocref;
GO
CREATE PROCEDURE sp_buscardocref
    @ccod_cia    VARCHAR(20),
    @codigo      VARCHAR(5),
    @serie       VARCHAR(10),
    @correlativo VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @nro INT = TRY_CAST(@correlativo AS INT);
    SELECT
        ISNULL(F.ntotal, 0)                                AS ntotal,
        CONVERT(VARCHAR(10), F.dfch_crea, 103)             AS dfch_crea,
        ISNULL(C.cdsc_coa, '')                             AS cdsc_coa,
        ISNULL(F.ccod_coa, '')                             AS ccod_coa,
        F.id_cbfact                                        AS id_cbfact,
        ''                                                 AS cod_motivo,
        ISNULL(F.ntotal, 0)
            - ISNULL((SELECT SUM(NC.ntotal)
                      FROM CbFactura NC
                      WHERE NC.ccod_cia = F.ccod_cia
                        AND NC.cdoc IN ('NC','ND')
                        AND NC.ccod_coa = F.ccod_coa
                        AND NC.cstatus = 'P'), 0)         AS montodisponible,
        ISNULL(F.id_cbinve, 0)                            AS id_cbinve
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa
       AND C.ccod_cia = F.ccod_cia
    WHERE F.ccod_cia = @ccod_cia
      AND F.cdoc     = @codigo
      AND F.cserie   = @serie
      AND F.nnumero  = @nro;
END
GO
PRINT 'NEW_850 aplicado: sp_buscardocref creado con shape esperado por notacredito_api.php BuscarDocRef.';
