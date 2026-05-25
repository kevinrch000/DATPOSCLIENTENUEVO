/* ========================================================================
   MODIFY_923 / FIX_72
   Roles.php: "no hay permisos por seleccionar" (BUG 4.4).

   Causa:
     - api/roles_api.php cases 'CargarTablaMenu' y 'CargarTablaMenuIdAccesos'
       leen los resultados por posicion en el array $f[N] usando un orden
       que NO coincide con el SELECT que devuelven las dos SPs definidas
       en DatPosEMP01.sql / 040_03_EMP01_Roles_Menus.sql.
     - Como consecuencia, JS recibe cstatus undefined, corden con el valor
       de 'nivel', y los checkboxes nunca quedan checked ni se construye
       la jerarquia padre/hijo correctamente.

   API espera para CargarTablaMenu (caso 'CargarTablaMenu'):
       [0] cdsc_menu  [1] curl_href  [2] nid_menupadre  [3] cli_menu
       [4] cul_menu   [5] cstatus    [6] corden         [7] id_menu
       [8] curl_src

   API espera para CargarTablaMenuIdAccesos:
       [0] cdsc_menu  [1] curl_href  [2] nid_menupadre  [3] cli_menu
       [4] cul_menu   [5] corden     [6] id_menu        [7] curl_src
       [8] cstatus     -- usado por JS como atributo HTML ('checked' o '')

   Este script recrea ambas SPs con SELECT en el orden esperado.

   Ejecutar en DatPos_EMP01
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 923 / FIX 72: Roles menu column order ==';

/* ─── webDatpos_cargarTablaMenu ────────────────────────────────────────
   Devuelve la lista completa de menus del tenant.
   Orden alineado con api/roles_api.php case 'CargarTablaMenu'.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarTablaMenu','P') IS NOT NULL
    DROP PROCEDURE webDatpos_cargarTablaMenu;
GO
CREATE PROCEDURE webDatpos_cargarTablaMenu
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(cdsc_menu, '')           AS cdsc_menu,        -- [0]
        ISNULL(curl_href, '')           AS curl_href,        -- [1]
        ISNULL(CAST(nid_menupadre AS NVARCHAR(20)), '0')
                                        AS nid_menupadre,    -- [2]
        ISNULL(cli_menu, '')            AS cli_menu,         -- [3]
        ISNULL(cul_menu, '')            AS cul_menu,         -- [4]
        ISNULL(cstatus, '')             AS cstatus,          -- [5]
        ISNULL(CAST(corden AS NVARCHAR(20)), '0')
                                        AS corden,           -- [6]
        ISNULL(CAST(id_menu AS NVARCHAR(20)), '0')
                                        AS id_menu,          -- [7]
        ISNULL(curl_src, '')            AS curl_src          -- [8]
    FROM Menus
    WHERE (ccod_empresa = @ccod_cia OR ccod_empresa IS NULL)
    ORDER BY corden;
END
GO

/* ─── webDatpos_cargarTablaMenuIdAccesos ───────────────────────────────
   Devuelve los menus marcados con cstatus='checked' si el rol tiene
   acceso (Accesos.cstatus='1'), '' (string vacio) en caso contrario.

   El JS Roles.js inserta este valor TAL CUAL como atributo HTML:
       <input ' + obj.cstatus + ' ...
   por eso debe ser 'checked' o cadena vacia, no '1'/'0'.

   Orden alineado con api/roles_api.php case 'CargarTablaMenuIdAccesos'.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarTablaMenuIdAccesos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_cargarTablaMenuIdAccesos;
GO
CREATE PROCEDURE webDatpos_cargarTablaMenuIdAccesos
    @ccod_cia VARCHAR(20),
    @id_rol   INT
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(M.cdsc_menu, '')         AS cdsc_menu,        -- [0]
        ISNULL(M.curl_href, '')         AS curl_href,        -- [1]
        ISNULL(CAST(M.nid_menupadre AS NVARCHAR(20)), '0')
                                        AS nid_menupadre,    -- [2]
        ISNULL(M.cli_menu, '')          AS cli_menu,         -- [3]
        ISNULL(M.cul_menu, '')          AS cul_menu,         -- [4]
        ISNULL(CAST(M.corden AS NVARCHAR(20)), '0')
                                        AS corden,           -- [5]
        ISNULL(CAST(M.id_menu AS NVARCHAR(20)), '0')
                                        AS id_menu,          -- [6]
        ISNULL(M.curl_src, '')          AS curl_src,         -- [7]
        CASE WHEN A.id_acceso IS NOT NULL THEN 'checked' ELSE '' END
                                        AS cstatus           -- [8]
    FROM Menus M
    LEFT JOIN Accesos A
           ON A.corden = M.corden
          AND A.id_rol = @id_rol
          AND A.ccod_empresa = @ccod_cia
          AND A.cstatus = '1'
    WHERE (M.ccod_empresa = @ccod_cia OR M.ccod_empresa IS NULL)
    ORDER BY M.corden;
END
GO

PRINT 'OK - FIX 72 completo: webDatpos_cargarTablaMenu y webDatpos_cargarTablaMenuIdAccesos alineados con la API.';
GO
