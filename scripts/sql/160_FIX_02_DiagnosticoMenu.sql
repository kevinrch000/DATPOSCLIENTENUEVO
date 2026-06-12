/* DIAGNÓSTICO: Ejecutar en DatPos_EMP01 para identificar problemas de menú */
USE DatPos_EMP01;
GO

-- 1. Ver el usuario ADMIN y su id_rol real
SELECT id_usuario, ccod_empresa, ccod_usuario, cdsc_usuario, id_rol, ccod_tiend, id_estado
FROM Usuarios WHERE ccod_usuario='ADMIN';

-- 2. Ver los roles y sus IDs reales (¿el ADMINISTRADOR tiene id_rol=1?)
SELECT id_rol, ccod_empresa, cdsc_rol, cstatus FROM Roles ORDER BY id_rol;

-- 3. Ver cuántos menús hay y con qué empresa
SELECT ccod_empresa, COUNT(*) AS total FROM Menus GROUP BY ccod_empresa;

-- 4. Ver cuántos accesos hay para cada rol/empresa
SELECT ccod_empresa, id_rol, COUNT(*) AS total FROM Accesos GROUP BY ccod_empresa, id_rol;

-- 5. Probar el SP de menú directamente (reemplaza 'EMP01' y 1 con los valores reales)
-- EXEC webDatpos_obtenerIdMenu 'EMP01', 1;
