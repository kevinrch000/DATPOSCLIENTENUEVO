/* =====================================================================
   FIX 19c — Corregir ConsultarOperaciones para GuíaRemisión
   BETipoOperacion: id_ctoper = Integer, cstatus = Integer
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_ConsultarOperaciones','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarOperaciones;
GO
CREATE PROCEDURE webDatpos_ConsultarOperaciones @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_tipoper                                  AS id_ctoper,            -- [0] Integer
        ccod_tipoper                                AS ccod_toper,           -- [1] String
        cdsc_tipoper                                AS cdsc_toper,           -- [2] String
        ctipo_flag,                                                          -- [3] String
        ''                                          AS ctipo_transferencia,  -- [4] String
        CASE WHEN cstatus='A' THEN 1 ELSE 0 END    AS cstatus               -- [5] Integer
    FROM TipoOperacion
    WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

PRINT '✓ FIX 19c: webDatpos_ConsultarOperaciones — id_ctoper y cstatus como Integer';
GO
