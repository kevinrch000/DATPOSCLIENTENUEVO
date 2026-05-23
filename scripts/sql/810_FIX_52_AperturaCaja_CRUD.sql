/*
   FIX 52 — AperturaCaja: SPs para Editar y Eliminar turnos
   Ejecutar contra la BD del tenant (DatPos_EMP01)
*/
USE DatPos_EMP01;
GO

/* ── 1. webDatpos_editarTurno ─────────────────────────────────── */
IF OBJECT_ID('webDatpos_editarTurno','P') IS NOT NULL DROP PROCEDURE webDatpos_editarTurno;
GO
CREATE PROCEDURE webDatpos_editarTurno
    @id_turno     INT,
    @ccod_cia     VARCHAR(20),
    @ccod_tienda  VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @ccod_caja    VARCHAR(20),
    @nmonto_ini   DECIMAL(18,4),
    @dfchdoc_ini  DATETIME
AS
BEGIN
    SET NOCOUNT ON;

    -- Solo permitir editar turnos abiertos (cstatus = 'A')
    IF NOT EXISTS (SELECT 1 FROM Turno WHERE id_turno = @id_turno AND ccod_cia = @ccod_cia AND cstatus = 'A')
    BEGIN
        SELECT 'TurnoCerrado' AS resultado;
        RETURN;
    END

    UPDATE Turno SET
        ccod_tienda  = @ccod_tienda,
        ccod_usuario = @ccod_usuario,
        ccod_caja    = @ccod_caja,
        nmonto_ini   = @nmonto_ini,
        dfchdoc_ini  = @dfchdoc_ini
    WHERE id_turno = @id_turno AND ccod_cia = @ccod_cia;

    SELECT 'OK' AS resultado;
END
GO

/* ── 2. webDatpos_eliminarTurno ─────────────────────────────────── */
IF OBJECT_ID('webDatpos_eliminarTurno','P') IS NOT NULL DROP PROCEDURE webDatpos_eliminarTurno;
GO
CREATE PROCEDURE webDatpos_eliminarTurno
    @id_turno INT,
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    -- Verificar que no tenga facturas asociadas
    IF EXISTS (SELECT 1 FROM CbFactura WHERE id_turno = @id_turno AND ccod_cia = @ccod_cia)
    BEGIN
        SELECT 'TieneFacturas' AS resultado;
        RETURN;
    END

    -- Solo permitir eliminar turnos abiertos
    IF NOT EXISTS (SELECT 1 FROM Turno WHERE id_turno = @id_turno AND ccod_cia = @ccod_cia AND cstatus = 'A')
    BEGIN
        SELECT 'TurnoCerrado' AS resultado;
        RETURN;
    END

    DELETE FROM Turno WHERE id_turno = @id_turno AND ccod_cia = @ccod_cia;

    SELECT 'OK' AS resultado;
END
GO

PRINT 'OK - FIX 52: SPs AperturaCaja CRUD (Editar/Eliminar) creados.';
GO
