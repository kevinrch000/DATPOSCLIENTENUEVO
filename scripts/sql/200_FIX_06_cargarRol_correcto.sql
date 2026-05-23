/* FIX 06 — webDatpos_cargarRol con columnas en el ORDEN EXACTO que lee Home.aspx.vb
   Home.aspx.vb líneas 373-382:
     fila.ItemArray(0) = cdsc_menu
     fila.ItemArray(1) = curl_href
     fila.ItemArray(2) = nid_menupadre
     fila.ItemArray(3) = cli_menu
     fila.ItemArray(4) = cul_menu
     fila.ItemArray(5) = cstatus
     fila.ItemArray(6) = corden
     fila.ItemArray(7) = id_menu
     fila.ItemArray(8) = curl_src
*/
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_cargarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarRol;
GO
CREATE PROCEDURE webDatpos_cargarRol
    @ccod_cia VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @id_rol INT
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
    INNER JOIN Accesos A ON A.corden = M.corden
        AND A.id_rol = @id_rol
        AND A.ccod_empresa = @ccod_cia
        AND A.cstatus = '1'
    ORDER BY M.corden;
END
GO

EXEC webDatpos_cargarRol 'EMP01', 'ADMIN', 1;
GO

PRINT '✓ webDatpos_cargarRol corregido con orden de columnas correcto.';
GO
