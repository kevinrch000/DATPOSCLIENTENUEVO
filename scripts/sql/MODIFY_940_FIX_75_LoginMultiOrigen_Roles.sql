/* ========================================================================
   MODIFY_940 / FIX_75
   Login multi-origen + roles por defecto + limpieza FacturaListaPrecio

   Contexto:
     - Hasta ahora el login (LogOn.php) solo buscaba al usuario en DatPosAdmin.
       Los empleados creados por un admin desde "Administracion -> Usuarios"
       se guardan en el TENANT (DatPos_EMP01) y por eso NO podian iniciar sesion.
     - Este script habilita el 2do origen de login (tenant) y crea SPs de apoyo.

   Cambios:
     PARTE A (DatPos_EMP01)
       1. sp_buscarusuario_login  -> busca empleado por usuario SIN filtrar por
          contrasena; devuelve hashes (MD5 + bcrypt) + rol + tienda/almacen/caja.
       2. sp_migrar_password_bcrypt -> migra el hash bcrypt del empleado.
       3. webDatpos_cargarRolAdmin -> menu COMPLETO (para superusuarios DatPosAdmin).
       4. Roles de ejemplo: CAJERO (rama VENTAS) y ALMACENERO (rama ALMACEN)
          + sus Accesos (calculados con un CTE recursivo sobre Menus).
       5. Elimina el menu "FACT. LISTA PRECIO" (corden 1035) y sus Accesos
          (la pagina FacturaListaPrecio.php se elimino; solo se usa Facturacion).

   Ejecutar en: DatPos_EMP01
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 940 / FIX 75: Login multi-origen + roles ==';
GO

/* Asegurar columna cpassw_bcrypt en el tenant (idempotente) */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='cpassw_bcrypt')
    ALTER TABLE Usuarios ADD cpassw_bcrypt VARCHAR(255) NULL;
GO

/* ── 1. sp_buscarusuario_login (TENANT) ────────────────────────────────
   Busca un empleado por codigo SIN filtrar por contrasena.
   Columnas (orden esperado por autenticarUsuarioTenant() en LogOn.php):
     [0] id_usuario   [1] ccod_usuario  [2] cdsc_usuario  [3] id_rol
     [4] ccod_empresa [5] cdsc_rol      [6] estado        [7] ccod_tiend
     [8] ccod_almacen [9] ccod_caja     [10] cperm_descn  [11] cpassw (MD5)
     [12] cpassw_bcrypt                 [13] cdirec
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_buscarusuario_login','P') IS NOT NULL DROP PROCEDURE sp_buscarusuario_login;
GO
CREATE PROCEDURE sp_buscarusuario_login
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT
        U.id_usuario,                                                       -- [0]
        U.ccod_usuario,                                                     -- [1]
        ISNULL(U.cdsc_usuario,''),                                          -- [2]
        ISNULL(U.id_rol,0),                                                 -- [3]
        U.ccod_empresa,                                                     -- [4]
        ISNULL(R.cdsc_rol,''),                                             -- [5]
        CASE WHEN U.id_estado=1 THEN 'Habilitado' ELSE 'Bloqueado' END,     -- [6]
        ISNULL(U.ccod_tiend,''),                                            -- [7]
        ISNULL(U.ccod_almacen,''),                                          -- [8]
        ISNULL(U.ccod_caja,''),                                             -- [9]
        ISNULL(U.cperm_descn,''),                                           -- [10]
        ISNULL(U.cpassw,''),                                                -- [11]
        ISNULL(U.cpassw_bcrypt,''),                                         -- [12]
        ISNULL(U.cdirec,'')                                                 -- [13]
    FROM Usuarios U
    LEFT JOIN Roles R ON R.id_rol = U.id_rol AND R.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario;
END
GO
PRINT '+ sp_buscarusuario_login (tenant) creado';
GO

/* ── 2. sp_migrar_password_bcrypt (TENANT) ─────────────────────────────
   Actualiza el hash bcrypt del empleado tras migracion automatica.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_migrar_password_bcrypt','P') IS NOT NULL DROP PROCEDURE sp_migrar_password_bcrypt;
GO
CREATE PROCEDURE sp_migrar_password_bcrypt
    @ccod_empresa  VARCHAR(20),
    @ccod_usuario  VARCHAR(50),
    @cpassw_bcrypt VARCHAR(255)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios
    SET cpassw_bcrypt = @cpassw_bcrypt
    WHERE ccod_empresa = @ccod_empresa AND ccod_usuario = @ccod_usuario;
END
GO
PRINT '+ sp_migrar_password_bcrypt (tenant) creado';
GO

/* ── 3. webDatpos_cargarRolAdmin ───────────────────────────────────────
   Menu COMPLETO para superusuarios (DatPosAdmin). Mismo orden de columnas
   que webDatpos_cargarRol (lo consume api/home_api.php :: CargarRoles).
     [0] cdsc_menu [1] curl_href [2] nid_menupadre [3] cli_menu [4] cul_menu
     [5] cstatus   [6] corden    [7] id_menu       [8] curl_src
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarRolAdmin','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarRolAdmin;
GO
CREATE PROCEDURE webDatpos_cargarRolAdmin
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        M.cdsc_menu,            -- 0
        M.curl_href,            -- 1
        M.nid_menupadre,        -- 2
        M.cli_menu,             -- 3
        M.cul_menu,             -- 4
        '1' AS cstatus,         -- 5
        M.corden,               -- 6
        M.id_menu,              -- 7
        M.curl_src              -- 8
    FROM Menus M
    WHERE (M.ccod_empresa = @ccod_cia OR M.ccod_empresa IS NULL)
      AND ISNULL(M.cstatus,'A') = 'A'
    ORDER BY M.corden;
END
GO
PRINT '+ webDatpos_cargarRolAdmin creado';
GO

/* ── 4. Roles de ejemplo CAJERO (Ventas) y ALMACENERO (Almacen) ────────
   Se crean (si no existen) para la empresa EMP01 y se les asignan los
   Accesos de toda su rama de menu mediante un CTE recursivo sobre Menus.
   ─────────────────────────────────────────────────────────────────── */
DECLARE @cia VARCHAR(20) = 'EMP01';

/* --- CAJERO --- */
IF NOT EXISTS (SELECT 1 FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='CAJERO')
    INSERT INTO Roles (ccod_empresa, cdsc_rol, cstatus, ccod_usuario)
    VALUES (@cia, 'CAJERO', 'A', 'ADMIN');

/* --- ALMACENERO --- */
IF NOT EXISTS (SELECT 1 FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='ALMACENERO')
    INSERT INTO Roles (ccod_empresa, cdsc_rol, cstatus, ccod_usuario)
    VALUES (@cia, 'ALMACENERO', 'A', 'ADMIN');
GO

DECLARE @cia VARCHAR(20) = 'EMP01';
DECLARE @rolCajero     INT = (SELECT TOP 1 id_rol FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='CAJERO'     ORDER BY id_rol);
DECLARE @rolAlmacenero INT = (SELECT TOP 1 id_rol FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='ALMACENERO' ORDER BY id_rol);

/* Limpiar accesos previos de estos roles (re-ejecutable) */
DELETE FROM Accesos WHERE ccod_empresa=@cia AND id_rol IN (@rolCajero, @rolAlmacenero);

/* CAJERO => toda la rama VENTAS (cli_menu raiz = '1_li_Ventas') */
;WITH RamaVentas AS (
    SELECT id_menu, corden FROM Menus WHERE cli_menu = '1_li_Ventas'
    UNION ALL
    SELECT M.id_menu, M.corden
    FROM Menus M INNER JOIN RamaVentas R ON M.nid_menupadre = R.id_menu
)
INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT DISTINCT @cia, @rolCajero, corden, '1'
FROM RamaVentas
WHERE corden IS NOT NULL;

/* Extras de paginas de Ventas que verifican con corden distinto al del menu */
INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT @cia, @rolCajero, v, '1'
FROM (VALUES (25),(30),(39)) AS X(v)
WHERE NOT EXISTS (SELECT 1 FROM Accesos WHERE ccod_empresa=@cia AND id_rol=@rolCajero AND corden=X.v);

/* ALMACENERO => toda la rama ALMACEN (cli_menu raiz = '1_li_Almacen') */
;WITH RamaAlmacen AS (
    SELECT id_menu, corden FROM Menus WHERE cli_menu = '1_li_Almacen'
    UNION ALL
    SELECT M.id_menu, M.corden
    FROM Menus M INNER JOIN RamaAlmacen R ON M.nid_menupadre = R.id_menu
)
INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT DISTINCT @cia, @rolAlmacenero, corden, '1'
FROM RamaAlmacen
WHERE corden IS NOT NULL;

/* Extras de paginas de Almacen (Kardex / ReporteKardex) */
INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus)
SELECT @cia, @rolAlmacenero, v, '1'
FROM (VALUES (42),(43)) AS X(v)
WHERE NOT EXISTS (SELECT 1 FROM Accesos WHERE ccod_empresa=@cia AND id_rol=@rolAlmacenero AND corden=X.v);
GO
PRINT '+ Roles CAJERO/ALMACENERO y sus Accesos configurados';
GO

/* ── 5. Eliminar menu "FACT. LISTA PRECIO" (corden 1035) ────────────────
   La pagina FacturaListaPrecio.php fue eliminada (solo se usa Facturacion).
   ─────────────────────────────────────────────────────────────────── */
DELETE FROM Accesos WHERE corden = 1035;
DELETE FROM Menus
 WHERE corden = 1035
    OR cli_menu = '3_li_FacturaListaPrecio'
    OR curl_href LIKE '%FacturaListaPrecio%';
GO
PRINT '+ Menu FACT. LISTA PRECIO eliminado';
GO

PRINT 'OK - FIX 75 completo.';
GO
