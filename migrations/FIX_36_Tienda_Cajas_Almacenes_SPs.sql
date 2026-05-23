-- ============================================================================
-- FIX_36: SPs faltantes para Tiendas (Datos / Almacenes / Cajas).
--
-- En el repo no estaban definidos los SP que usa pages/Administracion/Tiendas
-- al hacer click en una fila para cargar la pestania "Datos" y los tabs
-- "Almacenes" / "Cajas". Algunos tenants los tienen del despliegue legacy
-- pero con firmas inconsistentes (ej. @ccod_cia vs @ccod_empresa); esto
-- causaba el error:
--   "Procedure or function 'sp_consultartiendacajas' expects parameter
--    '@ccod_empresa', which was not supplied."
--
-- Este script (idempotente) recrea los SPs con la firma consistente con
-- api/tienda_api.php y devuelve las columnas en el orden que el endpoint
-- consume.
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar segun tenant
GO

-- ----------------------------------------------------------------------------
-- sp_consultartienda(@ccod_empresa, @ccod_tiend)
-- Devuelve 21 columnas alineadas con api/tienda_api.php case 'ConsultarTienda':
--   [0]id_tienda, [1]ccod_cia, [2]ccod_tiend, [3]cnombr, [4]cdirec, [5]cmail,
--   [6]ctelef, [7]cpassw, [8]cstatus, [9]nlista_pre_normal,
--   [10]nlista_pre_preferencial, [11]cdepartamento, [12]cprovincia,
--   [13]cdistrito, [14]cubigeo, [15]curba_tienda, [16]ccod_loc_emis,
--   [17]ccod_usuario, [18]dfch_crea
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_consultartienda','P') IS NOT NULL DROP PROCEDURE sp_consultartienda;
GO
CREATE PROCEDURE sp_consultartienda
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_tienda,                              -- [0]
        ccod_cia,                               -- [1]
        ccod_tiend,                             -- [2]
        ISNULL(cnombr,'')                AS cnombr,                  -- [3]
        ISNULL(cdirec,'')                AS cdirec,                  -- [4]
        ISNULL(cmail,'')                 AS cmail,                   -- [5]
        ISNULL(ctelef,'')                AS ctelef,                  -- [6]
        ISNULL(cpassw,'')                AS cpassw,                  -- [7]
        ISNULL(cstatus,'A')              AS cstatus,                 -- [8]
        ISNULL(nlista_pre_normal,0)      AS nlista_pre_normal,       -- [9]
        ISNULL(nlista_pre_preferencial,0) AS nlista_pre_preferencial,-- [10]
        ISNULL(cdepartamento,'')         AS cdepartamento,           -- [11]
        ISNULL(cprovincia,'')            AS cprovincia,              -- [12]
        ISNULL(cdistrito,'')             AS cdistrito,               -- [13]
        ISNULL(cubigeo,'')               AS cubigeo,                 -- [14]
        ISNULL(curba_tienda,'')          AS curba_tienda,            -- [15]
        ISNULL(ccod_loc_emis,'')         AS ccod_loc_emis,           -- [16]
        ISNULL(ccod_usuario,'')          AS ccod_usuario,            -- [17]
        dfch_crea                                                    -- [18]
    FROM Tiendas
    WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend;
END
GO

-- ----------------------------------------------------------------------------
-- sp_consultartiendaalmacenes(@ccod_empresa, @ccod_tiend)
-- Devuelve los almacenes del tenant marcando con 'cbx' los asociados a la tienda.
--   [0]ccod_alm, [1]cdsc_alm, [2]cbx ('1' si esta asignado, '0' si no)
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_consultartiendaalmacenes','P') IS NOT NULL DROP PROCEDURE sp_consultartiendaalmacenes;
GO
CREATE PROCEDURE sp_consultartiendaalmacenes
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        A.ccod_alm                                  AS ccod_alm,    -- [0]
        ISNULL(A.cdsc_alm,'')                       AS cdsc_alm,    -- [1]
        CASE WHEN TA.id_tiendaalm IS NULL THEN '0' ELSE '1' END
                                                    AS cbx          -- [2]
    FROM Almacenes A
    LEFT JOIN TiendaAlmacen TA
           ON TA.ccod_cia=A.ccod_cia
          AND TA.ccod_alm=A.ccod_alm
          AND TA.ccod_tiend=@ccod_tiend
    WHERE A.ccod_cia=@ccod_empresa
      AND ISNULL(A.cstatus,'A')='A'
    ORDER BY A.ccod_alm;
END
GO

-- ----------------------------------------------------------------------------
-- sp_consultartiendacajas(@ccod_empresa, @ccod_tiend)
-- Idem para cajas:
--   [0]ccod_caja, [1]cdsc_caja, [2]cbx ('1' si esta asignada)
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_consultartiendacajas','P') IS NOT NULL DROP PROCEDURE sp_consultartiendacajas;
GO
CREATE PROCEDURE sp_consultartiendacajas
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        C.ccod_caja                                 AS ccod_caja,   -- [0]
        ISNULL(C.cdsc_caja,'')                      AS cdsc_caja,   -- [1]
        CASE WHEN TC.id_tiendacaja IS NULL THEN '0' ELSE '1' END
                                                    AS cbx          -- [2]
    FROM Cajas C
    LEFT JOIN TiendaCaja TC
           ON TC.ccod_cia=C.ccod_cia
          AND TC.ccod_caja=C.ccod_caja
          AND TC.ccod_tiend=@ccod_tiend
    WHERE C.ccod_cia=@ccod_empresa
      AND ISNULL(C.cstatus,'A')='A'
    ORDER BY C.ccod_caja;
END
GO

PRINT 'FIX_36 OK: sp_consultartienda / sp_consultartiendaalmacenes / sp_consultartiendacajas creados.';
GO
