-- ============================================================================
-- FIX_35: Re-crear sp_consultaclientes con la firma de 17 columnas que
-- esperan el VB ConsultarClientes() y el endpoint api/configgeneral_api.php
-- (modal ModalConsultarClientes en Configuracion General, Consulta de
-- Ventas y Dashboard).
--
-- Sintoma corregido: el modal mostraba "00000000" en la columna Cliente y
-- el Nombre del Cliente vacio porque la version legacy del SP devolvia
-- 9 columnas y los indices [2]/[4] caian sobre cdoc_coa/ctelf en lugar
-- de ccod_coa/cdsc_coa.
--
-- Este script es idempotente: re-define el SP siempre.
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar segun tenant
GO

IF OBJECT_ID('sp_consultaclientes','P') IS NOT NULL DROP PROCEDURE sp_consultaclientes;
GO
CREATE PROCEDURE sp_consultaclientes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_coa,                                    -- [0]
        ccod_cia,                                  -- [1]
        ccod_coa,                                  -- [2]
        ISNULL(cdoc_coa,'')    AS cdoc_coa,        -- [3]
        ISNULL(cdsc_coa,'')    AS cdsc_coa,        -- [4]
        ISNULL(ctelf,'')       AS ctelf,           -- [5]
        ISNULL(cmail,'')       AS cmail,           -- [6]
        ISNULL(ctipo_coa,'')   AS destipo_coa,     -- [7]
        cdirc_coa,                                 -- [8]
        cdistrito,                                 -- [9]
        cprovincia,                                -- [10]
        cdepartamento,                             -- [11]
        cpais,                                     -- [12]
        ISNULL(cstatus,'A')    AS estado,          -- [13]
        ISNULL(cproveedor,'0') AS cproveedor,      -- [14]
        ISNULL(cdoc_coa,'')    AS ctip_doc,        -- [15]
        ISNULL(cruc_coa,'')    AS cruc_coa         -- [16]
    FROM Coa
    WHERE ccod_cia=@ccod_cia
    ORDER BY cdsc_coa;
END
GO

PRINT 'FIX_35 OK: sp_consultaclientes actualizado a 17 columnas.';
GO
