/* ========================================================================
   MODIFY_942 / FIX_77
   Corrige el guardado de Roles (editar rol + asignar menus/accesos).

   Problema:
     Al editar un rol y marcar las casillas de menus, la API llamaba a
     webDatpos_editarRol con 5 parametros (@ccod_empresa, @cdsc_rol,
     @cstatus, @ccod_usuario, @id_rol) pero el SP solo aceptaba 3
     (@id_rol, @cdsc_rol, @ccod_usuario), produciendo:
        "Procedure or function webDatpos_editarRol has too many arguments
         specified." (SQLSTATE 42000, error 8144)
     Ademas el SP no actualizaba el estado (cstatus) del rol.

   Solucion:
     Redefinir webDatpos_editarRol con la firma que envia la API y que
     ademas persista el estado. El estado llega como '1' (Activo) / '0'
     (Inactivo) desde el combo ddl_estado y se almacena como 'A' / 'I'
     (igual que la columna Roles.cstatus).

   Nota: la llamada a webDatpos_insertarAcceso se corrigio en el PHP
     (api/roles_api.php) para usar la firma real del SP
     (@ccod_empresa, @id_rol, @corden); ese SP NO cambia.

   Ejecutar en DatPos_EMP01.
======================================================================== */

USE DatPos_EMP01;
GO
SET LANGUAGE us_english;
GO
PRINT '== MODIFY 942 / FIX 77 (Tenant): webDatpos_editarRol ==';

IF OBJECT_ID('webDatpos_editarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_editarRol;
GO
CREATE PROCEDURE webDatpos_editarRol
    @ccod_empresa VARCHAR(20),
    @cdsc_rol     VARCHAR(100),
    @cstatus      VARCHAR(5)  = '1',
    @ccod_usuario VARCHAR(50) = NULL,
    @id_rol       INT
AS BEGIN SET NOCOUNT ON;
    UPDATE Roles
       SET cdsc_rol = @cdsc_rol,
           cstatus  = CASE WHEN @cstatus IN ('1','A','a') THEN 'A' ELSE 'I' END
     WHERE id_rol = @id_rol
       AND ccod_empresa = @ccod_empresa;
END
GO
PRINT '+ webDatpos_editarRol actualizado (5 params + estado)';
GO

PRINT 'OK - FIX 77 completo.';
GO
