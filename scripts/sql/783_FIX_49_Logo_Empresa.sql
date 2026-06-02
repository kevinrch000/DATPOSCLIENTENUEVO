/* =================================================================
   FIX 49 — Logo de Empresa: SP obtenerFotoUsuario devuelve ilogo
   =================================================================
   Problema: webDatpos_obtenerFotoUsuario solo retornaba ifoto de Usuarios,
   pero el JS (Comun.js → CargarFotoUsuario) espera también ilogo de
   ConfigGeneral para mostrarlo en el header.
   
   Cambios:
   1. Actualiza webDatpos_obtenerFotoUsuario para hacer JOIN con ConfigGeneral
      y devolver 2 columnas: [0]=ifoto, [1]=ilogo
   ================================================================= */

USE DatPos_EMP01;
GO

/* --- 1. SP que retorna foto usuario + logo empresa ------------------- */
IF OBJECT_ID('webDatpos_obtenerFotoUsuario','P') IS NOT NULL
    DROP PROCEDURE webDatpos_obtenerFotoUsuario;
GO

CREATE PROCEDURE [dbo].[webDatpos_obtenerFotoUsuario]
    @ccod_cia VARCHAR(20),
    @ccod_usuario VARCHAR(50)
AS BEGIN
    SET NOCOUNT ON;
    SELECT 
        U.ifoto,
        CG.ilogo
    FROM Usuarios U
    LEFT JOIN ConfigGeneral CG ON CG.ccod_cia = U.ccod_empresa
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;
END
GO

PRINT '✓ FIX 49: webDatpos_obtenerFotoUsuario ahora retorna ifoto + ilogo';
GO
