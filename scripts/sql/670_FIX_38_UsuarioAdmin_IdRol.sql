/* =====================================================================
   FIX 38 — CORREGIR id_rol=NULL en Usuarios del tenant
   
   El usuario 'admin' fue insertado por el script de setup inicial
   con id_rol=NULL (la columna lo permite, pero causa que webDatpos_cargarRol
   reciba @id_rol=0 y el INNER JOIN con Accesos no devuelva nada → menú vacío).
   
   Este script:
   1. Corrige id_rol=NULL → 1 (Administrador) en el tenant DatPos_EMP01
   2. Corrige cperm_descn=NULL → '100'
   3. Verifica consistencia de la clave con lo esperado por sp_validarusuario
   
   Ejecutar en: DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

PRINT 'FIX 38: Estado ANTES:';
SELECT ccod_empresa, ccod_usuario, id_rol, cperm_descn, id_estado,
       ccod_tiend, ccod_almacen, ccod_caja
FROM Usuarios WHERE ccod_empresa='EMP01';
GO

-- 1. Corregir id_rol y cperm_descn del admin
UPDATE Usuarios
SET    id_rol      = 1,
       cperm_descn = '100'
WHERE  ccod_empresa  = 'EMP01'
  AND  ccod_usuario  = 'admin'
  AND  (id_rol IS NULL OR id_rol <> 1 OR cperm_descn IS NULL);
GO

-- 2. Asegurar que el Rol 1 existe en la tabla Roles
IF NOT EXISTS (SELECT 1 FROM Roles WHERE ccod_empresa='EMP01' AND id_rol=1)
BEGIN
    -- Insertar sólo si falta (normalmente ya existe con id_rol=1 por IDENTITY)
    SET IDENTITY_INSERT Roles ON;
    INSERT INTO Roles(id_rol, ccod_empresa, cdsc_rol, cstatus)
    VALUES(1,'EMP01','Administrador Sistema','A');
    SET IDENTITY_INSERT Roles OFF;
    PRINT 'Rol 1 creado.';
END
GO

-- 3. Asegurar Accesos completos para id_rol=1 y EMP01
INSERT INTO Accesos(ccod_empresa, id_rol, corden, cstatus)
SELECT 'EMP01', 1, M.corden, '1'
FROM   Menus M
WHERE  (M.ccod_empresa = 'EMP01' OR M.ccod_empresa IS NULL)
  AND  NOT EXISTS (
           SELECT 1 FROM Accesos A2
           WHERE  A2.ccod_empresa = 'EMP01'
             AND  A2.id_rol       = 1
             AND  A2.corden       = M.corden
       );
GO

PRINT 'FIX 38: Estado DESPUÉS:';
SELECT ccod_empresa, ccod_usuario, id_rol, cperm_descn, id_estado,
       ccod_tiend, ccod_almacen, ccod_caja
FROM Usuarios WHERE ccod_empresa='EMP01';
GO

PRINT 'Accesos para id_rol=1:';
SELECT COUNT(*) AS total_accesos FROM Accesos WHERE ccod_empresa='EMP01' AND id_rol=1;
GO

PRINT 'FIX 38 aplicado correctamente.';
GO
