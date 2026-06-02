/* ========================================================================
   FIX 48c HOTFIX - Actualiza sp_migrar_password_bcrypt
   
   Agrega parametro @cpassw_md5 para reemplazar texto plano en cpassw
   con el hash MD5 durante la migracion a bcrypt.
   
   Ejecutar en: DatPosAdmin
   ======================================================================== */
USE DatPosAdmin;
GO

IF OBJECT_ID('sp_migrar_password_bcrypt','P') IS NOT NULL DROP PROCEDURE sp_migrar_password_bcrypt;
GO
CREATE PROCEDURE sp_migrar_password_bcrypt
    @ccod_usuario    VARCHAR(50),
    @cpassw_bcrypt   VARCHAR(255),
    @cpassw_md5      VARCHAR(200) = NULL
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios
    SET cpassw_bcrypt = @cpassw_bcrypt,
        cpassw        = COALESCE(@cpassw_md5, cpassw)
    WHERE ccod_usuario = @ccod_usuario;
END
GO

PRINT '+ sp_migrar_password_bcrypt actualizado (ahora reemplaza cpassw con MD5)';
GO
