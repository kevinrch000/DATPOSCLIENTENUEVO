-- =====================================================================
-- 170_FIX_seed_numeradores_nc_nd.sql
-- 
-- Motivo: Configurar numeradores de Nota de Crédito (NC) y Nota de 
--         Débito (ND) para las cajas CAJ01 y CAJ02, evitando el error
--         de "serie no asignada" al intentar emitirlos.
-- base de datos: DatPos_EMP01
-- =====================================================================
USE DatPos_EMP01;
GO

-- CAJ01 - NC y ND
IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='NC')
    INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer, dfch_crea)
    VALUES ('EMP01', 'CAJ01', 'NC', 'NC01', 1, 'NOTA DE CREDITO', GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='ND')
    INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer, dfch_crea)
    VALUES ('EMP01', 'CAJ01', 'ND', 'ND01', 1, 'NOTA DE DEBITO', GETDATE());

-- CAJ02 - NC y ND
IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ02' AND cdoc_tipo='NC')
    INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer, dfch_crea)
    VALUES ('EMP01', 'CAJ02', 'NC', 'NC02', 1, 'NOTA DE CREDITO', GETDATE());

IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ02' AND cdoc_tipo='ND')
    INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer, dfch_crea)
    VALUES ('EMP01', 'CAJ02', 'ND', 'ND02', 1, 'NOTA DE DEBITO', GETDATE());

PRINT 'Numeradores NC y ND configurados correctamente en la base de datos.';
GO

-- Corregir sp_consultardocumentocabecera para retornar cruc_coa
IF OBJECT_ID('sp_consultardocumentocabecera','P') IS NOT NULL
BEGIN
    EXEC('
    ALTER PROCEDURE sp_consultardocumentocabecera @id_cbfact INT
    AS BEGIN SET NOCOUNT ON;
        SELECT F.*,C.cdsc_coa,C.cdoc_coa,C.cruc_coa,C.ctipo_coa AS ctip_doc,C.cdirc_coa,T.cnombr AS cdsc_tienda FROM CbFactura F
        LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
        LEFT JOIN Tiendas T ON T.ccod_tiend=F.ccod_tiend AND T.ccod_cia=F.ccod_cia
        WHERE F.id_cbfact=@id_cbfact;
    END
    ');
    PRINT 'sp_consultardocumentocabecera actualizado con cruc_coa y ctip_doc.';
END
GO
