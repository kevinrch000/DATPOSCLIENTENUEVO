/* ========================================================================
   PARTE 3: DatPos_EMP01 — STORED PROCEDURES: ROLES, MENÚS, ACCESOS
======================================================================== */
USE DatPos_EMP01;
GO

/* ---- ROLES ---- */
IF OBJECT_ID('webDatpos_insertarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarRol; 
GO
CREATE PROCEDURE webDatpos_insertarRol
    @ccod_empresa VARCHAR(20), @cdsc_rol VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO Roles (ccod_empresa, cdsc_rol, cstatus, ccod_usuario)
    VALUES (@ccod_empresa, @cdsc_rol, 'A', @ccod_usuario);
    SELECT SCOPE_IDENTITY() AS id_rol;
END
GO

IF OBJECT_ID('webDatpos_editarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_editarRol; 
GO
CREATE PROCEDURE webDatpos_editarRol
    @id_rol INT, @cdsc_rol VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Roles SET cdsc_rol = @cdsc_rol WHERE id_rol = @id_rol;
    /* Accesos: eliminar los que ya no aplican y reinsertar */
END
GO

IF OBJECT_ID('webDatpos_cargarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarRol; 
GO
CREATE PROCEDURE webDatpos_cargarRol
    @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_rol, cdsc_rol, cstatus FROM Roles WHERE ccod_empresa = @ccod_empresa ORDER BY id_rol;
END
GO

IF OBJECT_ID('webDatpos_eliminarRol','P') IS NOT NULL DROP PROCEDURE webDatpos_eliminarRol; 
GO
CREATE PROCEDURE webDatpos_eliminarRol
    @id_rol INT, @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DELETE FROM Accesos WHERE id_rol = @id_rol AND ccod_empresa = @ccod_empresa;
    DELETE FROM Roles WHERE id_rol = @id_rol AND ccod_empresa = @ccod_empresa;
END
GO

IF OBJECT_ID('webDatpos_consultarRoles','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarRoles; 
GO
CREATE PROCEDURE webDatpos_consultarRoles
    @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_rol, ccod_empresa, cdsc_rol, cstatus FROM Roles WHERE ccod_empresa = @ccod_empresa;
END
GO

IF OBJECT_ID('webDatpos_consultarIdRol','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdRol; 
GO
CREATE PROCEDURE webDatpos_consultarIdRol
    @id_rol INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_rol, cdsc_rol, cstatus FROM Roles WHERE id_rol = @id_rol;
END
GO

/* ---- ACCESOS ---- */
IF OBJECT_ID('webDatpos_insertarAcceso','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarAcceso; 
GO
CREATE PROCEDURE webDatpos_insertarAcceso
    @ccod_empresa VARCHAR(20), @id_rol INT, @corden INT
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Accesos WHERE ccod_empresa=@ccod_empresa AND id_rol=@id_rol AND corden=@corden)
        INSERT INTO Accesos (ccod_empresa, id_rol, corden, cstatus) VALUES (@ccod_empresa, @id_rol, @corden, '1');
END
GO

IF OBJECT_ID('webDatpos_eliminarIDAcceso','P') IS NOT NULL DROP PROCEDURE webDatpos_eliminarIDAcceso; 
GO
CREATE PROCEDURE webDatpos_eliminarIDAcceso
    @ccod_empresa VARCHAR(20), @id_rol INT
AS BEGIN SET NOCOUNT ON;
    DELETE FROM Accesos WHERE ccod_empresa = @ccod_empresa AND id_rol = @id_rol;
END
GO

IF OBJECT_ID('webDatpos_verificarAccesos','P') IS NOT NULL DROP PROCEDURE webDatpos_verificarAccesos; 
GO
CREATE PROCEDURE webDatpos_verificarAccesos
    @ccod_empresa VARCHAR(20), @id_rol INT, @corden INT
AS BEGIN SET NOCOUNT ON;
    SELECT COUNT(1) AS tiene_acceso FROM Accesos
    WHERE ccod_empresa=@ccod_empresa AND id_rol=@id_rol AND corden=@corden AND cstatus='1';
END
GO

/* ---- MENÚS ---- */
IF OBJECT_ID('webDatpos_cargarTablaMenu','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTablaMenu; 
GO
CREATE PROCEDURE webDatpos_cargarTablaMenu
    @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_menu, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu,
           nivel, corden, cstatus
    FROM Menus
    WHERE (ccod_empresa = @ccod_empresa OR ccod_empresa IS NULL)
    ORDER BY corden;
END
GO

IF OBJECT_ID('webDatpos_CargarTablaMenuIdAccesos','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarTablaMenuIdAccesos; 
GO
CREATE PROCEDURE webDatpos_CargarTablaMenuIdAccesos
    @ccod_empresa VARCHAR(20), @id_rol INT
AS BEGIN SET NOCOUNT ON;
    SELECT M.id_menu, M.cdsc_menu, M.curl_href, M.curl_src, M.nid_menupadre,
           M.nivel, M.corden,
           CASE WHEN A.id_acceso IS NOT NULL THEN '1' ELSE '0' END AS tiene_acceso
    FROM Menus M
    LEFT JOIN Accesos A ON A.corden = M.corden
        AND A.id_rol = @id_rol AND A.ccod_empresa = @ccod_empresa AND A.cstatus='1'
    WHERE (M.ccod_empresa = @ccod_empresa OR M.ccod_empresa IS NULL)
    ORDER BY M.corden;
END
GO

IF OBJECT_ID('webDatpos_obtenerIdMenu','P') IS NOT NULL DROP PROCEDURE webDatpos_obtenerIdMenu; 
GO
CREATE PROCEDURE webDatpos_obtenerIdMenu
    @ccod_empresa VARCHAR(20), @id_rol INT
AS BEGIN SET NOCOUNT ON;
    SELECT M.id_menu, M.cdsc_menu, M.curl_href, M.curl_src, M.nid_menupadre,
           M.cli_menu, M.cul_menu, M.nivel, M.corden
    FROM Menus M
    INNER JOIN Accesos A ON A.corden = M.corden
        AND A.id_rol = @id_rol AND A.ccod_empresa = @ccod_empresa AND A.cstatus='1'
    ORDER BY M.corden;
END
GO

IF OBJECT_ID('webDatpos_obtenerIdMenuPadre','P') IS NOT NULL DROP PROCEDURE webDatpos_obtenerIdMenuPadre; 
GO
CREATE PROCEDURE webDatpos_obtenerIdMenuPadre
    @id_menu INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_menu, cdsc_menu, nid_menupadre, nivel, corden FROM Menus WHERE id_menu = @id_menu;
END
GO

/* ========================================================================
   SEED DATA — MENÚS DEL SISTEMA
   (estructura base para que el módulo de Roles funcione desde el primer login)
======================================================================== */
IF NOT EXISTS (SELECT 1 FROM Menus WHERE curl_href LIKE '%Dashboard%' OR cdsc_menu = 'Dashboard')
BEGIN
    INSERT INTO Menus (ccod_empresa, cdsc_menu, curl_href, curl_src, nid_menupadre, cli_menu, cul_menu, nivel, corden, cstatus)
    VALUES
    -- Nivel 1: módulos principales
    (NULL,'Dashboard',      '~/Interfaces/Dashboard.aspx',          'fa fa-tachometer',  0,  'Dashboard',      '',  '1',  1, 'A'),
    (NULL,'Ventas',         '',                                      'fa fa-shopping-cart',0, 'Ventas',         '',  '1',  2, 'A'),
    (NULL,'Inventario',     '',                                      'fa fa-boxes',        0, 'Inventario',     '',  '1',  3, 'A'),
    (NULL,'Artículos',      '',                                      'fa fa-tag',          0, 'Articulos',      '',  '1',  4, 'A'),
    (NULL,'Clientes',       '~/Interfaces/AdministrarClientes.aspx', 'fa fa-users',        0, 'Clientes',       '',  '1',  5, 'A'),
    (NULL,'Reportes',       '',                                      'fa fa-chart-bar',    0, 'Reportes',       '',  '1',  6, 'A'),
    (NULL,'Configuración',  '',                                      'fa fa-cog',          0, 'Configuracion',  '',  '1',  7, 'A'),
    -- Nivel 2: submenús Ventas
    (NULL,'Facturación',        '~/Interfaces/Facturacion.aspx',            'fa fa-file-invoice', 2, 'Facturacion',     '', '2', 21, 'A'),
    (NULL,'Consulta Ventas',    '~/Interfaces/ConsultaVenta.aspx',          'fa fa-search',       2, 'ConsultaVenta',   '', '2', 22, 'A'),
    (NULL,'Nota de Crédito',    '~/Interfaces/NotaCredito.aspx',            'fa fa-file-alt',     2, 'NotaCredito',     '', '2', 23, 'A'),
    (NULL,'Apertura/Cierre',    '~/Interfaces/AperturaCaja.aspx',           'fa fa-cash-register',2,'AperturaCaja',    '', '2', 24, 'A'),
    (NULL,'Cuentas',            '~/Interfaces/Cuenta.aspx',                 'fa fa-receipt',      2, 'Cuenta',          '', '2', 25, 'A'),
    -- Nivel 2: submenús Inventario
    (NULL,'Ingresos',           '~/Interfaces/AdministrarInventario.aspx',  'fa fa-arrow-down',   3, 'Ingresos',        '', '2', 31, 'A'),
    (NULL,'Salidas',            '~/Interfaces/AdministrarSalidas.aspx',     'fa fa-arrow-up',     3, 'Salidas',         '', '2', 32, 'A'),
    (NULL,'Transferencias',     '~/Interfaces/AdministrarTransferencia.aspx','fa fa-exchange-alt',3, 'Transferencias',  '', '2', 33, 'A'),
    (NULL,'Guías de Remisión',  '~/Interfaces/AdministrarGuia.aspx',        'fa fa-truck',        3, 'Guias',           '', '2', 34, 'A'),
    (NULL,'Saldos',             '~/Interfaces/ConsultaAlmacen.aspx',        'fa fa-balance-scale',3,'Saldos',          '', '2', 35, 'A'),
    (NULL,'Kardex',             '~/Interfaces/ConsultaKardex.aspx',         'fa fa-history',      3, 'Kardex',          '', '2', 36, 'A'),
    -- Nivel 2: submenús Artículos
    (NULL,'Catálogo',           '~/Interfaces/AdministrarArticulos.aspx',   'fa fa-list',         4, 'Catalogo',        '', '2', 41, 'A'),
    (NULL,'Familias',           '~/Interfaces/AdministrarFamilias.aspx',    'fa fa-folder',       4, 'Familias',        '', '2', 42, 'A'),
    (NULL,'Unidades de Medida', '~/Interfaces/AdministrarUnidadMedida.aspx','fa fa-ruler',        4, 'UnidadMedida',    '', '2', 43, 'A'),
    (NULL,'Variantes',          '~/Interfaces/AdministrarVariantes.aspx',   'fa fa-layer-group',  4, 'Variantes',       '', '2', 44, 'A'),
    (NULL,'Lista de Precios',   '~/Interfaces/AdministrarListaPrecio.aspx', 'fa fa-dollar-sign',  4, 'ListaPrecio',     '', '2', 45, 'A'),
    -- Nivel 2: submenús Reportes
    (NULL,'Reporte Ventas',     '~/Interfaces/ReporteVenta.aspx',           'fa fa-file-invoice-dollar',6,'ReporteVenta','','2',61,'A'),
    (NULL,'Reporte Almacén',    '~/Interfaces/ReporteAlmacen.aspx',         'fa fa-warehouse',    6, 'ReporteAlmacen',  '', '2', 62, 'A'),
    (NULL,'Reporte Saldo',      '~/Interfaces/ReporteSaldo.aspx',           'fa fa-coins',        6, 'ReporteSaldo',    '', '2', 63, 'A'),
    (NULL,'Reporte Turno',      '~/Interfaces/ReporteTurno.aspx',           'fa fa-clock',        6, 'ReporteTurno',    '', '2', 64, 'A'),
    (NULL,'Tributario',         '~/Interfaces/ConsultaTributaria.aspx',     'fa fa-file-contract',6,'Tributario',      '', '2', 65, 'A'),
    -- Nivel 2: submenús Configuración
    (NULL,'Config. General',    '~/Interfaces/ConfigGeneral.aspx',          'fa fa-sliders-h',    7, 'ConfigGeneral',   '', '2', 71, 'A'),
    (NULL,'Tiendas',            '~/Interfaces/AdministrarTiendas.aspx',     'fa fa-store',        7, 'Tiendas',         '', '2', 72, 'A'),
    (NULL,'Almacenes',          '~/Interfaces/AdministrarAlmacenes.aspx',   'fa fa-warehouse',    7, 'Almacenes',       '', '2', 73, 'A'),
    (NULL,'Cajas',              '~/Interfaces/AdministrarCajas.aspx',       'fa fa-cash-register',7,'Cajas',           '', '2', 74, 'A'),
    (NULL,'Usuarios',           '~/Interfaces/AdministrarUsuarios.aspx',    'fa fa-user-cog',     7, 'Usuarios',        '', '2', 75, 'A'),
    (NULL,'Roles',              '~/Interfaces/AdministrarRoles.aspx',       'fa fa-shield-alt',   7, 'Roles',           '', '2', 76, 'A'),
    (NULL,'Tipo Operación',     '~/Interfaces/AdministrarTipoOperacion.aspx','fa fa-project-diagram',7,'TipoOperacion', '','2', 77, 'A');
END
GO

PRINT '✓ Roles, Menús y Accesos creados correctamente.';
GO
