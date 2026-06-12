/* =====================================================================
   FIX 19b — Corregir cstatus en ConsultarAlamcenes (Integer, no String)
   BEAlmacen.cstatus es Integer → devolver 1/0, no 'A'/'I'
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_ConsultarAlamcenes','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarAlamcenes;
GO
CREATE PROCEDURE webDatpos_ConsultarAlamcenes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        A.id_almac                   AS id_ctalmac,     -- [0] String
        A.ccod_alm,                                      -- [1] String
        A.cdsc_alm,                                      -- [2] String
        ISNULL(A.cdirc_almac,'')     AS cdirc_almac,     -- [3] String
        ISNULL(A.cubigeo,'')         AS cubigeo,          -- [4] String
        CASE WHEN A.cstatus='A' THEN 1 ELSE 0 END AS cstatus, -- [5] Integer ← FIX
        ISNULL((SELECT TOP 1 cserie FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=A.ccod_alm AND ctip_doc='I'),'') AS cserieDest,  -- [6] String
        ISNULL((SELECT TOP 1 cserie FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=A.ccod_alm AND ctip_doc='S'),'') AS cserieOrig   -- [7] String
    FROM Almacenes A
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A';
END
GO

PRINT '✓ FIX 19b: cstatus ahora retorna Integer (1/0) para BEAlmacen';
GO
