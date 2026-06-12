-- ============================================================================
-- FIX_37: SPs y schema fixes para Guardar Tienda (Administracion -> Tiendas).
--
-- Errores corregidos:
--   1) "Could not find stored procedure 'sp_limpiarasignaciontiendaalmacen'."
--      "Could not find stored procedure 'sp_limpiarasignaciontiendacaja'."
--      Los SPs reales son sp_limpiartiendasalmacen / sp_limpiartiendascaja.
--      (PHP ya fue corregido para usar los nombres correctos.)
--   2) "sp_asignartiendaalmacen has too many arguments specified."
--      El SP solo acepta 3 params (sin @ccod_usuario).
--   3) "sp_asignartiendacaja expects parameter '@ccod_empresa'."
--      Renombrado por consistencia (PHP ya envia @ccod_empresa).
--   4) Tienda.cdepartamento / cprovincia / cdistrito eran VARCHAR(2/4/6),
--      no caben los nombres ("LIMA", "MIRAFLORES"). Ampliado a VARCHAR(100)
--      como ya se hizo en Almacenes en FIX_22.
--   5) sp_insertartienda / sp_editartienda no aceptaban @cpassw / @cstatus,
--      por eso "Contrasena Mail" y "Estado" nunca se persistian.
--
-- Idempotente: drop & re-create para SPs; ALTER TABLE solo agranda.
-- ============================================================================

USE [DatPos_EMP01];   -- ajustar segun tenant
GO

-- ----------------------------------------------------------------------------
-- 1) Ampliar columnas ubigeo en Tiendas (igual que FIX_22 hizo en Almacenes).
-- ----------------------------------------------------------------------------
IF COL_LENGTH('Tiendas','cdepartamento') < 100
    ALTER TABLE Tiendas ALTER COLUMN cdepartamento VARCHAR(100) NULL;
GO
IF COL_LENGTH('Tiendas','cprovincia') < 100
    ALTER TABLE Tiendas ALTER COLUMN cprovincia VARCHAR(100) NULL;
GO
IF COL_LENGTH('Tiendas','cdistrito') < 100
    ALTER TABLE Tiendas ALTER COLUMN cdistrito VARCHAR(100) NULL;
GO

-- ----------------------------------------------------------------------------
-- 2) sp_insertartienda(@ccod_empresa, @ccod_tiend, ..., @cpassw, @cstatus, ...)
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_insertartienda','P') IS NOT NULL DROP PROCEDURE sp_insertartienda;
GO
CREATE PROCEDURE sp_insertartienda
    @ccod_empresa            VARCHAR(20),
    @ccod_tiend              VARCHAR(20),
    @cnombr                  VARCHAR(100),
    @cdirec                  VARCHAR(200),
    @cmail                   VARCHAR(100),
    @ctelef                  VARCHAR(20),
    @cpassw                  VARCHAR(50),
    @cstatus                 VARCHAR(1),
    @cdepartamento           VARCHAR(100),
    @cprovincia              VARCHAR(100),
    @cdistrito               VARCHAR(100),
    @cubigeo                 VARCHAR(6),
    @curba_tienda            VARCHAR(100),
    @ccod_loc_emis           VARCHAR(20),
    @nlista_pre_normal       INT,
    @nlista_pre_preferencial INT,
    @ccod_usuario            VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Tiendas WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend)
        INSERT INTO Tiendas (
            ccod_cia, ccod_tiend, cnombr, cdirec, cmail, ctelef, cpassw, cstatus,
            cdepartamento, cprovincia, cdistrito, cubigeo, curba_tienda, ccod_loc_emis,
            nlista_pre_normal, nlista_pre_preferencial, ccod_usuario
        ) VALUES (
            @ccod_empresa, @ccod_tiend, @cnombr, @cdirec, @cmail, @ctelef, @cpassw,
            ISNULL(NULLIF(@cstatus,''),'A'),
            @cdepartamento, @cprovincia, @cdistrito, @cubigeo, @curba_tienda, @ccod_loc_emis,
            @nlista_pre_normal, @nlista_pre_preferencial, @ccod_usuario
        );
END
GO

-- ----------------------------------------------------------------------------
-- 3) sp_editartienda(...) misma firma para reuso desde la API.
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_editartienda','P') IS NOT NULL DROP PROCEDURE sp_editartienda;
GO
CREATE PROCEDURE sp_editartienda
    @ccod_empresa            VARCHAR(20),
    @ccod_tiend              VARCHAR(20),
    @cnombr                  VARCHAR(100),
    @cdirec                  VARCHAR(200),
    @cmail                   VARCHAR(100),
    @ctelef                  VARCHAR(20),
    @cpassw                  VARCHAR(50),
    @cstatus                 VARCHAR(1),
    @cdepartamento           VARCHAR(100),
    @cprovincia              VARCHAR(100),
    @cdistrito               VARCHAR(100),
    @cubigeo                 VARCHAR(6),
    @curba_tienda            VARCHAR(100),
    @ccod_loc_emis           VARCHAR(20),
    @nlista_pre_normal       INT,
    @nlista_pre_preferencial INT,
    @ccod_usuario            VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Tiendas SET
        cnombr                  = @cnombr,
        cdirec                  = @cdirec,
        cmail                   = @cmail,
        ctelef                  = @ctelef,
        cpassw                  = @cpassw,
        cstatus                 = ISNULL(NULLIF(@cstatus,''),'A'),
        cdepartamento           = @cdepartamento,
        cprovincia              = @cprovincia,
        cdistrito               = @cdistrito,
        cubigeo                 = @cubigeo,
        curba_tienda            = @curba_tienda,
        ccod_loc_emis           = @ccod_loc_emis,
        nlista_pre_normal       = @nlista_pre_normal,
        nlista_pre_preferencial = @nlista_pre_preferencial,
        ccod_usuario            = @ccod_usuario
    WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend;
END
GO

-- ----------------------------------------------------------------------------
-- 4) sp_eliminartienda(@ccod_empresa, @ccod_tiend) baja logica.
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_eliminartienda','P') IS NOT NULL DROP PROCEDURE sp_eliminartienda;
GO
CREATE PROCEDURE sp_eliminartienda
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    UPDATE Tiendas SET cstatus='I'
    WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend;
END
GO

-- ----------------------------------------------------------------------------
-- 5) Asignaciones tienda <-> almacen
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_limpiartiendasalmacen','P') IS NOT NULL DROP PROCEDURE sp_limpiartiendasalmacen;
GO
CREATE PROCEDURE sp_limpiartiendasalmacen
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DELETE FROM TiendaAlmacen
    WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend;
END
GO

IF OBJECT_ID('sp_asignartiendaalmacen','P') IS NOT NULL DROP PROCEDURE sp_asignartiendaalmacen;
GO
CREATE PROCEDURE sp_asignartiendaalmacen
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20),
    @ccod_alm     VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (
        SELECT 1 FROM TiendaAlmacen
        WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend AND ccod_alm=@ccod_alm
    )
        INSERT INTO TiendaAlmacen (ccod_cia, ccod_tiend, ccod_alm)
        VALUES (@ccod_empresa, @ccod_tiend, @ccod_alm);
END
GO

-- ----------------------------------------------------------------------------
-- 6) Asignaciones tienda <-> caja
-- ----------------------------------------------------------------------------
IF OBJECT_ID('sp_limpiartiendascaja','P') IS NOT NULL DROP PROCEDURE sp_limpiartiendascaja;
GO
CREATE PROCEDURE sp_limpiartiendascaja
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DELETE FROM TiendaCaja
    WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend;
END
GO

IF OBJECT_ID('sp_asignartiendacaja','P') IS NOT NULL DROP PROCEDURE sp_asignartiendacaja;
GO
CREATE PROCEDURE sp_asignartiendacaja
    @ccod_empresa VARCHAR(20),
    @ccod_tiend   VARCHAR(20),
    @ccod_caja    VARCHAR(20),
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (
        SELECT 1 FROM TiendaCaja
        WHERE ccod_cia=@ccod_empresa AND ccod_tiend=@ccod_tiend AND ccod_caja=@ccod_caja
    )
        INSERT INTO TiendaCaja (ccod_cia, ccod_tiend, ccod_caja, ccod_usuario)
        VALUES (@ccod_empresa, @ccod_tiend, @ccod_caja, @ccod_usuario);
END
GO

PRINT 'FIX_37 OK: SPs Tienda guardar/asignar + columnas ubigeo ampliadas.';
GO
