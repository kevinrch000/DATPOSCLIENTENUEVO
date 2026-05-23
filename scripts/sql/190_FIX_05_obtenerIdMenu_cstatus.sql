/* FIX 05 — Corregir webDatpos_obtenerIdMenu para devolver cstatus que necesita el JS */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_obtenerIdMenu','P') IS NOT NULL DROP PROCEDURE webDatpos_obtenerIdMenu;
GO
CREATE PROCEDURE webDatpos_obtenerIdMenu
    @ccod_empresa VARCHAR(20), @id_rol INT
AS BEGIN SET NOCOUNT ON;
    SELECT
        M.id_menu,
        M.cdsc_menu,
        M.curl_href,
        M.curl_src,
        M.nid_menupadre,
        M.cli_menu,
        M.cul_menu,
        M.nivel,
        M.corden,
        '1' AS cstatus        -- EL JS verifica obj[i].cstatus == "1" para mostrar el item
    FROM Menus M
    INNER JOIN Accesos A ON A.corden = M.corden
        AND A.id_rol = @id_rol
        AND A.ccod_empresa = @ccod_empresa
        AND A.cstatus = '1'
    ORDER BY M.corden;
END
GO

/* También corregir webDatpos_obtenerIdMenuPadre por si acaso */
IF OBJECT_ID('webDatpos_obtenerIdMenuPadre','P') IS NOT NULL DROP PROCEDURE webDatpos_obtenerIdMenuPadre;
GO
CREATE PROCEDURE webDatpos_obtenerIdMenuPadre
    @id_menu INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_menu, cdsc_menu, nid_menupadre, nivel, corden, '1' AS cstatus
    FROM Menus WHERE id_menu = @id_menu;
END
GO

-- Verificar que el SP devuelve cstatus = '1' para todos los 35 registros
-- (Ejecuta esta línea para confirmar antes de reloguear)
EXEC webDatpos_obtenerIdMenu 'EMP01', 1;
GO

PRINT '✓ webDatpos_obtenerIdMenu corregido con columna cstatus.';
GO
