-- ============================================================================
-- FIX_31: SPs de listado para Transferencias y Guías de Remisión
-- (devuelven todas las columnas que el JS frontend espera)
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar según tenant
GO

-- ----------------------------------------------------------------------------
-- 1) appDatpos_consultarTransferenciasFull
--    Devuelve para cada cabecera de transferencia ambos almacenes (origen y
--    destino) reuniendo CbInventario + LnInventario.
-- ----------------------------------------------------------------------------
IF OBJECT_ID('appDatpos_consultarTransferenciasFull','P') IS NOT NULL DROP PROCEDURE appDatpos_consultarTransferenciasFull;
GO
CREATE PROCEDURE appDatpos_consultarTransferenciasFull @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    -- Una transferencia tiene N líneas en LnInventario que comparten el mismo
    -- ccod_alm (origen) y ccod_alm_ingreso (destino). Tomamos el primer registro
    -- de detalle para identificar el destino. dfecha viene de la cabecera.
    WITH FirstLine AS (
        SELECT id_cbinve,
               MIN(ccod_alm)         AS alm_o,
               MIN(ccod_alm_ingreso) AS alm_d,
               ROW_NUMBER() OVER (PARTITION BY id_cbinve ORDER BY id_lninve) AS rn
        FROM LnInventario WHERE ccod_cia=@ccod_cia AND ccod_alm_ingreso IS NOT NULL
        GROUP BY id_cbinve, id_lninve
    )
    SELECT
        C.id_cbinve,
        C.ccod_alm                      AS ccod_almOrigen,
        ISNULL(AO.cdsc_alm, C.ccod_alm) AS cdsc_almOrigen,
        C.ctipo                         AS ctipoOrigen,
        C.vserie                        AS vserieOrigen,
        C.nnumero                       AS nnumeroOrigen,
        ISNULL(L.alm_d, '')             AS ccod_almDestino,
        ISNULL(AD.cdsc_alm, '')         AS cdsc_almDestino,
        C.ctipo                         AS ctipoDestino,    -- mismo tipo en transferencia
        C.vserie                        AS vserieDestino,
        C.nnumero                       AS nnumeroDestino,
        CONVERT(VARCHAR(10), C.dfecha, 103) AS dfecha,
        C.vobservacion,
        C.ntotal
    FROM CbInventario C
    LEFT JOIN (
        SELECT DISTINCT id_cbinve, alm_d FROM FirstLine WHERE rn=1
    ) L ON L.id_cbinve = C.id_cbinve
    LEFT JOIN Almacenes AO ON AO.ccod_cia=@ccod_cia AND AO.ccod_alm=C.ccod_alm
    LEFT JOIN Almacenes AD ON AD.ccod_cia=@ccod_cia AND AD.ccod_alm=L.alm_d
    WHERE C.ccod_cia=@ccod_cia
      AND EXISTS (SELECT 1 FROM LnInventario X WHERE X.id_cbinve=C.id_cbinve AND X.ccod_alm_ingreso IS NOT NULL)
    ORDER BY C.id_cbinve DESC;
END
GO

-- ----------------------------------------------------------------------------
-- 2) appDatpos_consultarGuiaRemisionFull
--    Devuelve datos de la guía joineando CbInventario + CbGuia + Almacenes,
--    con todas las columnas que el JS espera.
-- ----------------------------------------------------------------------------
IF OBJECT_ID('appDatpos_consultarGuiaRemisionFull','P') IS NOT NULL DROP PROCEDURE appDatpos_consultarGuiaRemisionFull;
GO
CREATE PROCEDURE appDatpos_consultarGuiaRemisionFull @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        G.id_cbinve,
        G.ctipo,
        G.cod_tip_cpe,
        G.ccod_alm,
        G.cdomicilio_partida,
        ISNULL(L.alm_ing, G.ccod_alm)  AS ccod_alm_ing,
        G.cdomicilio_llegada,
        CONVERT(VARCHAR(10), G.fchEmision, 103) AS dfecha,
        G.cdoc_ref,
        ISNULL(G.cserie,'') + '-' + RIGHT('00000000'+ISNULL(G.nnumero,'0'),8) AS guia,
        G.cnum_ruc_dest,
        G.cnom_rzn_soc_dest,
        G.ntotal,
        G.fchEmision,
        G.cserie,
        G.nnumero
    FROM CbGuia G
    LEFT JOIN (
        SELECT id_cbinve, MAX(ccod_alm_ingreso) AS alm_ing
        FROM LnInventario WHERE ccod_cia=@ccod_cia GROUP BY id_cbinve
    ) L ON L.id_cbinve = G.id_cbinve
    WHERE G.ccod_cia = @ccod_cia
    ORDER BY G.id_cbguia DESC;
END
GO

PRINT 'FIX_31: SPs lista Transferencias y Guías creados';
