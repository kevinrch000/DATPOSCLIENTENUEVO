/* ========================================================================
   MODIFY 820 — FIX 53: appDatpos_abrirCaja debe devolver id_turno real
   ------------------------------------------------------------------------
   Corrige el SP `appDatpos_abrirCaja` (definido originalmente en
   710_FIX_41_AperturaCaja_Definitivo.sql y conservado por 810_FIX_52).

   PROBLEMA:
     El SP devolvía SELECT 'OK' AS id_turno tras INSERT, lo que provoca
     que el API `AperturaCaja.aspx/Guardar` no pueda guardar el id_turno
     real en `$_SESSION['id_turno']` (filtra con ctype_digit).
     Solo se obtenía id_turno cuando se entraba a Facturación.

   FIX:
     Devolver SCOPE_IDENTITY() como id_turno. Cuando ya hay un turno
     abierto para el usuario, devolver el id_turno del turno abierto
     (en lugar de la cadena 'TurnoAperturado').
     Se mantiene compatibilidad con el API: el front sigue funcionando
     porque ctype_digit ahora SÍ acepta el valor.

   EJECUTAR CONTRA: DatPos_EMP01 (tenant)
======================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL
    DROP PROCEDURE appDatpos_abrirCaja;
GO

CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie      VARCHAR(20),
    @IdUsuario   VARCHAR(50),
    @CodCaj      VARCHAR(20),
    @Monto       DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50),
    @dfchdoc_ini DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;

    IF @dfchdoc_ini IS NULL SET @dfchdoc_ini = GETDATE();

    DECLARE @id_turno INT;

    /* Si el usuario ya tiene turno abierto, devolver su id_turno real */
    SELECT TOP 1 @id_turno = id_turno
    FROM Turno
    WHERE ccod_cia = @CodCia
      AND ccod_usuario = @IdUsuario
      AND cstatus = 'A'
    ORDER BY id_turno DESC;

    IF @id_turno IS NOT NULL
    BEGIN
        SELECT CAST(@id_turno AS VARCHAR(20)) AS id_turno;
        RETURN;
    END

    /* Crear nuevo turno */
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja,
                      nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');

    SET @id_turno = SCOPE_IDENTITY();

    SELECT CAST(@id_turno AS VARCHAR(20)) AS id_turno;
END
GO

PRINT 'MODIFY 820 OK: appDatpos_abrirCaja ahora devuelve id_turno real';
GO
