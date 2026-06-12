/* FIX 11 — Corregir SP webDatpos_verificarAccesos
   El VB envía: @ccod_cia, @id_rol, @id_menu
   Mi SP tenía: @ccod_empresa, @id_rol, @corden  ← ¡NOMBRES INCORRECTOS!
*/
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_verificarAccesos','P') IS NOT NULL DROP PROCEDURE webDatpos_verificarAccesos;
GO
CREATE PROCEDURE webDatpos_verificarAccesos
    @ccod_cia VARCHAR(20),
    @id_rol INT,
    @id_menu INT
AS BEGIN
    SET NOCOUNT ON;
    SELECT COUNT(1) AS tiene_acceso
    FROM Accesos
    WHERE ccod_empresa = @ccod_cia
      AND id_rol = @id_rol
      AND corden = @id_menu
      AND cstatus = '1';
END
GO

-- Verificación rápida: probar que el SP funciona
DECLARE @rol INT = (SELECT TOP 1 id_rol FROM Roles WHERE cdsc_rol LIKE '%ADMIN%');
EXEC webDatpos_verificarAccesos @ccod_cia='EMP01', @id_rol=@rol, @id_menu=1002;
GO

PRINT '✓ SP corregido con nombres de parámetros correctos (@ccod_cia, @id_rol, @id_menu).';
GO
