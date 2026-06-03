/* ========================================================================
   MODIFY_943 / FIX_78
   webDatpos_cargarRol ahora incluye a los ANCESTROS de cada menu concedido.

   Problema:
     El menu lateral de un empleado se arma con webDatpos_cargarRol, que
     hacia un INNER JOIN entre Menus y Accesos por 'corden'. Por eso un
     menu solo aparecia si su corden estaba EXPLICITAMENTE en Accesos.

     La pantalla Roles.php -> "Accesos" guardaba (antes del FIX_78 en PHP)
     unicamente las hojas/detalles, y los menus principales (TABLAS,
     OPERACIONES, ...) y los modulos (ALMACEN, VENTAS, ...) no quedaban en
     Accesos. Resultado: aunque la hoja estuviera concedida, su cabecera y
     su modulo no se devolvian, y el arbol del dashboard no podia anidar ni
     mostrar la rama => "no muestra nada".

   Solucion:
     Redefinir webDatpos_cargarRol para que, partiendo de los menus cuyo
     'corden' esta en Accesos, suba por la jerarquia (nid_menupadre ->
     id_menu) e incluya tambien a todos sus ancestros. Asi el modulo y la
     cabecera siempre acompanan a la hoja concedida y el arbol se renderiza
     completo. Esto es robusto para cualquier catalogo de menus (no depende
     de cordenes/ids fijos).

   La firma (@ccod_cia, @ccod_usuario, @id_rol) y las columnas devueltas
   no cambian, por lo que el PHP (api/home_api.php) no requiere ajustes.

   Ejecutar en DatPos_EMP01.
======================================================================== */

USE DatPos_EMP01;
GO
SET LANGUAGE us_english;
GO
PRINT '== MODIFY 943 / FIX 78 (Tenant): webDatpos_cargarRol con ancestros ==';

IF OBJECT_ID('webDatpos_cargarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarRol;
GO
CREATE PROCEDURE webDatpos_cargarRol
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @id_rol       INT
AS BEGIN SET NOCOUNT ON;
    ;WITH Concedidos AS (
        -- Menus cuyo corden esta directamente en Accesos para el rol.
        SELECT M.id_menu, M.nid_menupadre
        FROM Menus M
        INNER JOIN Accesos A
                ON A.corden = M.corden
               AND A.id_rol = @id_rol
               AND A.ccod_empresa = @ccod_cia
               AND A.cstatus = '1'
        WHERE (M.ccod_empresa = @ccod_cia OR M.ccod_empresa IS NULL)
    ),
    Arbol AS (
        -- Punto de partida: los menus concedidos.
        SELECT id_menu, nid_menupadre FROM Concedidos
        UNION ALL
        -- Sube al padre por id_menu = nid_menupadre del hijo.
        SELECT P.id_menu, P.nid_menupadre
        FROM Menus P
        INNER JOIN Arbol H ON P.id_menu = H.nid_menupadre
        WHERE (P.ccod_empresa = @ccod_cia OR P.ccod_empresa IS NULL)
    )
    SELECT DISTINCT
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
    INNER JOIN Arbol T ON T.id_menu = M.id_menu
    ORDER BY M.corden
    OPTION (MAXRECURSION 100);
END
GO
PRINT '+ webDatpos_cargarRol actualizado (incluye ancestros de cada acceso)';
GO

PRINT 'OK - FIX 78 completo.';
GO
