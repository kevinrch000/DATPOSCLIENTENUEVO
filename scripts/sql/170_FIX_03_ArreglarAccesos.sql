/* FIX 03 — Verificar y arreglar datos de Accesos/Menús 
   Ejecutar en DatPos_EMP01 */
USE DatPos_EMP01;
GO

-- PASO 1: Ver id_rol real del usuario ADMIN
SELECT ccod_usuario, id_rol, ccod_empresa FROM Usuarios WHERE ccod_usuario='ADMIN';

-- PASO 2: Ver qué roles existen y sus IDs reales
SELECT id_rol, cdsc_rol, ccod_empresa FROM Roles ORDER BY id_rol;

-- PASO 3: Ver accesos existentes
SELECT ccod_empresa, id_rol, COUNT(*) AS total FROM Accesos GROUP BY ccod_empresa, id_rol;

-- PASO 4: Probar el SP directamente (reemplaza los valores con los reales del paso 1)
-- Ejemplo: si id_rol real del ADMIN es 4 y empresa es 'EMP01':
-- EXEC webDatpos_obtenerIdMenu 'EMP01', 4;

-- PASO 5: LIMPIAR Y REINSERTAR ACCESOS con el id_rol correcto
-- (Reemplaza @id_rol_admin con el valor real del PASO 1)
DECLARE @ccod_empresa VARCHAR(20) = 'EMP01';
DECLARE @id_rol_admin INT;

-- Obtener el id_rol real del rol ADMINISTRADOR
SELECT @id_rol_admin = id_rol FROM Roles 
WHERE ccod_empresa=@ccod_empresa AND cdsc_rol='ADMINISTRADOR';

PRINT 'id_rol del ADMINISTRADOR: ' + CAST(ISNULL(@id_rol_admin,0) AS VARCHAR);

-- Actualizar el usuario ADMIN para usar ese id_rol correcto
UPDATE Usuarios SET id_rol=@id_rol_admin 
WHERE ccod_empresa=@ccod_empresa AND ccod_usuario='ADMIN';

-- Eliminar accesos anteriores que pueden estar con id_rol=1 incorrecto
DELETE FROM Accesos WHERE ccod_empresa=@ccod_empresa AND id_rol=1 
AND @id_rol_admin <> 1;

-- Reinsertar accesos con el id_rol correcto
INSERT INTO Accesos(ccod_empresa,id_rol,corden,cstatus)
SELECT @ccod_empresa, @id_rol_admin, corden, '1' 
FROM Menus 
WHERE NOT EXISTS (
    SELECT 1 FROM Accesos A2 
    WHERE A2.ccod_empresa=@ccod_empresa 
    AND A2.id_rol=@id_rol_admin 
    AND A2.corden=Menus.corden
);

-- Verificar resultado
SELECT 'Accesos insertados:' AS mensaje, COUNT(*) AS total 
FROM Accesos WHERE ccod_empresa=@ccod_empresa AND id_rol=@id_rol_admin;

-- Probar el SP de menú con los valores corregidos
EXEC webDatpos_obtenerIdMenu @ccod_empresa, @id_rol_admin;
GO
