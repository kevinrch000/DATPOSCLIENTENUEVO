/* =====================================================================
   FIX 18b — Datos de empresa para el ticket (versión corregida)
   
   Los campos del ticket vienen de:
   - Session("objBEUsuario").cnum_tribu  → tabla Empresas en DatPosAdmin
   - Session("objBEUsuario").cnom_empre  → tabla Empresas en DatPosAdmin
   - Tienda (dirección, teléfono)        → tabla Tiendas en DatPos_EMP01
   
   Ejecutar en DatPosAdmin primero, luego en DatPos_EMP01
===================================================================== */

/* ── PARTE A: DatPosAdmin — completar datos de la Empresa ── */
USE DatPosAdmin;
GO

-- Ver columnas reales de Empresas:
SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='Empresas';
GO

-- Actualizar datos de empresa EMP01
-- (ajustar los nombres de columna si los anteriores fallan)
UPDATE Empresas
SET cnum_tribu  = '20000000001',
    cdomicilio  = 'Av. Principal 123, Lima',
    ctelf       = '01-1234567'
WHERE ccod_empresa = 'EMP01';
GO

/* Actualizar sp_validarusuario en DatPosAdmin para incluir cnum_tribu
   y los demás campos que carga el Session ("objBEUsuario") */
IF OBJECT_ID('sp_validarusuario','P') IS NOT NULL DROP PROCEDURE sp_validarusuario;
GO
CREATE PROCEDURE sp_validarusuario @ccod_usuario VARCHAR(50), @cpassw VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    SELECT 
        U.id_usuario,
        U.ccod_usuario,
        U.cdsc_usuario,
        U.cpassw,
        U.ccod_empresa,
        U.id_rol,
        U.id_estado,
        ISNULL(U.ccod_tiend,'')    AS ccod_tiend,
        ISNULL(U.ccod_almacen,'')  AS ccod_almacen,
        ISNULL(U.ccod_caja,'')     AS ccod_caja,
        ISNULL(U.cperm_descn,'')   AS cperm_descn,
        ISNULL(E.cnum_tribu,'')    AS cnum_tribu,     -- ← RUC para el ticket
        ISNULL(E.cdsc_empresa,'')  AS cdsc_empresa,   -- ← Nombre empresa
        ISNULL(E.cdomicilio,'')    AS cdirec_empresa,
        ISNULL(E.ctelf,'')         AS ctelf_empresa,
        ISNULL(E.cnombre_bd,'')    AS cnombre_bd,
        ISNULL(E.cnomser,'')       AS cnomser
    FROM Usuarios U
    INNER JOIN Empresas E ON E.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.cpassw       = @cpassw
      AND U.id_estado    = 1;
END
GO

/* ── PARTE B: DatPos_EMP01 — datos de tienda para el ticket ── */
USE DatPos_EMP01;
GO

-- Actualizar tienda con dirección y teléfono reales
UPDATE Tiendas
SET cdirec = 'Av. Principal 123, Lima',
    ctelef = '01-1234567'
WHERE ccod_cia='EMP01' AND ccod_tiend='T001';
GO

-- Verificar
SELECT 'Empresas en DatPosAdmin' AS fuente;
USE DatPosAdmin;
SELECT ccod_empresa, cdsc_empresa, cnum_tribu, cdomicilio, ctelf FROM Empresas WHERE ccod_empresa='EMP01';
GO

USE DatPos_EMP01;
SELECT 'Tiendas en EMP01' AS fuente;
SELECT ccod_tiend, cnombr, cdirec, ctelef FROM Tiendas WHERE ccod_cia='EMP01';
GO

PRINT 'OK - FIX 18b completo. Volver a iniciar sesión para refrescar datos del ticket.';
GO
