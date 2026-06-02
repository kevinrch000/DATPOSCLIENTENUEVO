/* ========================================================================
   MODIFY_922 / FIX_71
   Cajas (Administracion): el campo Estado no se persistia ni se mostraba.

   BUG 4.3 — Funciona a medias, algunos campos no se llenan.

   Causa:
     1. <select id="ddl_estado"> en Cajas.php usaba values "1"/"0" pero la
        tabla Cajas almacena cstatus VARCHAR(1) como 'A'/'I'. El
        findIndex(o => o.value === obj.cstatus) nunca matcheaba, dejando
        el dropdown vacio al cargar Datos desde Lista.
     2. webDatpos_insertarcaja y sp_editarcaja NO reciben @cstatus, por
        lo que el estado nunca se persiste al crear/editar.
     3. ConsultarCajas devolvia 'A'/'I' crudo en la columna Estado.

   Este script recrea los SPs CRUD para aceptar @cstatus.
   Las correcciones de page (.php) y api (.php) van en este mismo PR.

   Ejecutar en DatPos_EMP01
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 922 / FIX 71: Cajas estado CRUD ==';

/* ─── webDatpos_insertarcaja ───────────────────────────────────────────
   Agrega @cstatus (default 'A' si viene vacio).
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_insertarcaja','P') IS NOT NULL
    DROP PROCEDURE webDatpos_insertarcaja;
GO
CREATE PROCEDURE webDatpos_insertarcaja
    @ccod_empresa VARCHAR(20),
    @ccod_caja    VARCHAR(20),
    @cdsc_caja    VARCHAR(100),
    @cstatus      VARCHAR(1) = 'A',
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Cajas WHERE ccod_cia=@ccod_empresa AND ccod_caja=@ccod_caja)
        INSERT INTO Cajas (ccod_cia, ccod_caja, cdsc_caja, cstatus, ccod_usuario, dfch_crea)
        VALUES (@ccod_empresa, @ccod_caja, @cdsc_caja,
                ISNULL(NULLIF(@cstatus,''),'A'),
                @ccod_usuario, GETDATE());
END
GO

/* ─── sp_editarcaja ────────────────────────────────────────────────────
   Agrega @cstatus.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_editarcaja','P') IS NOT NULL
    DROP PROCEDURE sp_editarcaja;
GO
CREATE PROCEDURE sp_editarcaja
    @ccod_empresa VARCHAR(20),
    @ccod_caja    VARCHAR(20),
    @cdsc_caja    VARCHAR(100),
    @cstatus      VARCHAR(1) = 'A',
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Cajas
       SET cdsc_caja    = @cdsc_caja,
           cstatus      = ISNULL(NULLIF(@cstatus,''),'A'),
           ccod_usuario = @ccod_usuario
     WHERE ccod_cia=@ccod_empresa
       AND ccod_caja=@ccod_caja;
END
GO

PRINT 'OK - FIX 71 completo: webDatpos_insertarcaja, sp_editarcaja aceptan @cstatus.';
GO
