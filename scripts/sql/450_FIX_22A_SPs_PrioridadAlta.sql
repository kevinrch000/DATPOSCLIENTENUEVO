/* =====================================================================
   FIX 22A — SPs FALTANTES: PRIORIDAD ALTA (CRUDs + DDLs)
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. sp_consultaestados ── */
IF OBJECT_ID('sp_consultaestados','P') IS NOT NULL DROP PROCEDURE sp_consultaestados; 
GO
CREATE PROCEDURE sp_consultaestados
AS BEGIN SET NOCOUNT ON;
    SELECT 'A' AS cstatus, 'Activo' AS cdescripcion
    UNION ALL
    SELECT 'I','Inactivo';
END
GO

/* ── 2. sp_consultarroles ── */
IF OBJECT_ID('sp_consultarroles','P') IS NOT NULL DROP PROCEDURE sp_consultarroles; 
GO
CREATE PROCEDURE sp_consultarroles @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_rol, cdsc_rol AS cdescripcion FROM Roles WHERE ccod_empresa=@ccod_cia AND cstatus='A' ORDER BY cdsc_rol;
END
GO

/* ── 3. sp_consultatipodocumento ── */
IF OBJECT_ID('sp_consultatipodocumento','P') IS NOT NULL DROP PROCEDURE sp_consultatipodocumento; 
GO
CREATE PROCEDURE sp_consultatipodocumento
AS BEGIN SET NOCOUNT ON;
    SELECT 'BV' AS cdoc_tipo, 'BOLETA' AS cdsc_tipo
    UNION ALL SELECT 'FA','FACTURA'
    UNION ALL SELECT 'NC','NOTA DE CREDITO'
    UNION ALL SELECT 'ND','NOTA DE DEBITO';
END
GO

/* ── 4. sp_consultaunidadmedidaactiva ── */
IF OBJECT_ID('sp_consultaunidadmedidaactiva','P') IS NOT NULL DROP PROCEDURE sp_consultaunidadmedidaactiva; 
GO
CREATE PROCEDURE sp_consultaunidadmedidaactiva @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_unimed, cdsc_unimed FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* ── 5. sp_consultarimpuestos ── */
IF OBJECT_ID('sp_consultarimpuestos','P') IS NOT NULL DROP PROCEDURE sp_consultarimpuestos; 
GO
CREATE PROCEDURE sp_consultarimpuestos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ISNULL(nigv,18) AS nigv, ISNULL(nisc,0) AS nisc FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* ── 6. webDatpos_consultaAlmacen (DDL almacenes activos) ── */
IF OBJECT_ID('webDatpos_consultaAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaAlmacen; 
GO
CREATE PROCEDURE webDatpos_consultaAlmacen @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_alm, cdsc_alm FROM Almacenes WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_alm;
END
GO

/* ── 7. webDatpos_consultaFamilia (DDL familias activas) ── */
IF OBJECT_ID('webDatpos_consultaFamilia','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaFamilia; 
GO
CREATE PROCEDURE webDatpos_consultaFamilia @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_lin, cdsc_lin FROM Familias WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_lin;
END
GO

/* ── 8. webDatpos_consultaUnidadMedida (DDL UM activas) ── */
IF OBJECT_ID('webDatpos_consultaUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaUnidadMedida; 
GO
CREATE PROCEDURE webDatpos_consultaUnidadMedida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_unimed, cdsc_unimed FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* ── 9. webDatpos_consultaCaja (DDL cajas activas) ── */
IF OBJECT_ID('webDatpos_consultaCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaCaja; 
GO
CREATE PROCEDURE webDatpos_consultaCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_caja, cdsc_caja FROM Cajas WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_caja;
END
GO

/* ── 10. webDatpos_cargarArticuloSoloBienes ── */
IF OBJECT_ID('webDatpos_cargarArticuloSoloBienes','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarArticuloSoloBienes; 
GO
CREATE PROCEDURE webDatpos_cargarArticuloSoloBienes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_articulo, cdsc_articulo FROM Articulos WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctip_articulo='B' ORDER BY cdsc_articulo;
END
GO

/* ── 11. webDatpos_consultarIdAccesos ── */
IF OBJECT_ID('webDatpos_consultarIdAccesos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdAccesos; 
GO
CREATE PROCEDURE webDatpos_consultarIdAccesos @ccod_cia VARCHAR(20), @ccod_rol VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.id_acceso,
           CAST(A.id_rol AS VARCHAR(20)) AS ccod_rol,
           A.corden,
           ISNULL(M.cdsc_menu, '') AS cdescripcion
    FROM Accesos A
    INNER JOIN Menus M ON M.corden=A.corden
    WHERE A.ccod_empresa=@ccod_cia AND CAST(A.id_rol AS VARCHAR(20))=@ccod_rol;
END
GO

/* ── 12-15. Inventario Detalle CRUD ── */
IF OBJECT_ID('sp_consultarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_consultarinventariodetalle; 
GO
CREATE PROCEDURE sp_consultarinventariodetalle @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, A.cdsc_articulo, L.ncantidad, L.ncosto,
           (L.ncantidad*L.ncosto) AS nimporte
    FROM LnInventario L
    LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=@id;
END
GO

IF OBJECT_ID('sp_insertarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_insertarinventariodetalle; 
GO
CREATE PROCEDURE sp_insertarinventariodetalle
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_tienda VARCHAR(20),
    @almacen VARCHAR(20), @ccod_articulo VARCHAR(50), @ncantidad INT,
    @ncosto DECIMAL(18,4), @id_cbinve INT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES (@ccod_cia,@id_cbinve,@ccod_articulo,@ncantidad,@ncosto,@almacen,@ccod_usuario);
    -- Actualizar stock
    IF EXISTS (SELECT 1 FROM Stock WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo AND ccod_alm=@almacen)
        UPDATE Stock SET ncantidad=ncantidad+@ncantidad, ncosto=@ncosto WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo AND ccod_alm=@almacen;
    ELSE
        INSERT INTO Stock (ccod_cia,ccod_articulo,ccod_alm,ncantidad,ncosto) VALUES (@ccod_cia,@ccod_articulo,@almacen,@ncantidad,@ncosto);
END
GO

IF OBJECT_ID('sp_editarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_editarinventariodetalle; 
GO
CREATE PROCEDURE sp_editarinventariodetalle @id_lninve INT, @ccod_articulo VARCHAR(50), @ncantidad INT, @ncosto DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON;
    UPDATE LnInventario SET ccod_articulo=@ccod_articulo, ncantidad=@ncantidad, ncosto=@ncosto WHERE id_lninve=@id_lninve;
END
GO

IF OBJECT_ID('sp_eliminarinventariodetalle','P') IS NOT NULL DROP PROCEDURE sp_eliminarinventariodetalle; 
GO
CREATE PROCEDURE sp_eliminarinventariodetalle @id_lninve INT
AS BEGIN SET NOCOUNT ON;
    DELETE FROM LnInventario WHERE id_lninve=@id_lninve;
END
GO

IF OBJECT_ID('sp_eliminarinventariodetalletodo','P') IS NOT NULL DROP PROCEDURE sp_eliminarinventariodetalletodo; 
GO
CREATE PROCEDURE sp_eliminarinventariodetalletodo @id_cbinve INT
AS BEGIN SET NOCOUNT ON;
    DELETE FROM LnInventario WHERE id_cbinve=@id_cbinve;
END
GO

/* ── 16. webDatpos_insertarInventarioDetalleSalida ── */
IF OBJECT_ID('webDatpos_insertarInventarioDetalleSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarInventarioDetalleSalida; 
GO
CREATE PROCEDURE webDatpos_insertarInventarioDetalleSalida
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_tienda VARCHAR(20),
    @vserie VARCHAR(10), @nnumero INT, @ccod_articulo VARCHAR(50),
    @ncantidad INT, @ncosto DECIMAL(18,4), @id_cbinve INT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario (ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_usuario)
    VALUES (@ccod_cia,@id_cbinve,@ccod_articulo,@ncantidad,@ncosto,@ccod_usuario);
END
GO

/* ── 17. webDatpos_consultarInventarioDetalleSalida ── */
IF OBJECT_ID('webDatpos_consultarInventarioDetalleSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarInventarioDetalleSalida; 
GO
CREATE PROCEDURE webDatpos_consultarInventarioDetalleSalida @ccod_cia VARCHAR(20), @id INT
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, A.cdsc_articulo, L.ncantidad, L.ncosto, (L.ncantidad*L.ncosto) AS nimporte
    FROM LnInventario L LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=@id;
END
GO

/* ── 18. webDatpos_consultaColumnas ── */
IF OBJECT_ID('webDatpos_consultaColumnas','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaColumnas; 
GO
CREATE PROCEDURE webDatpos_consultaColumnas
AS BEGIN SET NOCOUNT ON;
    SELECT DATA_TYPE AS TipoDato, ISNULL(CHARACTER_MAXIMUM_LENGTH,0) AS longitud,
           ISNULL(NUMERIC_PRECISION,0) AS CantEnteros, ISNULL(NUMERIC_SCALE,0) AS CantDecimales,
           COLUMN_NAME AS DscColumna, TABLE_NAME AS DscTabla
    FROM INFORMATION_SCHEMA.COLUMNS ORDER BY TABLE_NAME, ORDINAL_POSITION;
END
GO

/* ── 19. webDatpos_ConsultarClientes (para dropdown en documentos) ── */
IF OBJECT_ID('webDatpos_ConsultarClientes','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarClientes; 
GO
CREATE PROCEDURE webDatpos_ConsultarClientes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_coa, cdsc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_coa;
END
GO

/* ── 20. webDatpos_consultaIdCliente ── */
IF OBJECT_ID('webDatpos_consultaIdCliente','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaIdCliente; 
GO
CREATE PROCEDURE webDatpos_consultaIdCliente @ccod_cia VARCHAR(20), @tipodoc VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_coa, cdsc_coa, cdoc_coa, cruc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_coa;
END
GO

/* ── 21. webDatpos_consultaTienda (DDL tiendas) ── */
IF OBJECT_ID('webDatpos_consultaTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaTienda; 
GO
CREATE PROCEDURE webDatpos_consultaTienda @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend, cnombr FROM Tiendas WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cnombr;
END
GO

/* ── 22. webDatpos_cargarCodigoDocumentos (numeradores factura) ── */
IF OBJECT_ID('webDatpos_cargarCodigoDocumentos','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCodigoDocumentos; 
GO
CREATE PROCEDURE webDatpos_cargarCodigoDocumentos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT cdoc_tipo, cdsc_numer FROM NumeradorCaja WHERE ccod_cia=@ccod_cia ORDER BY cdoc_tipo;
END
GO

/* ── 23. webDatpos_cargarCodigoDoc (numeradores cobranza) ── */
IF OBJECT_ID('webDatpos_cargarCodigoDoc','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCodigoDoc; 
GO
CREATE PROCEDURE webDatpos_cargarCodigoDoc @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT cdoc_tipo, cdsc_numer, cdoc_serie FROM NumeradorCaja WHERE ccod_cia=@ccod_cia ORDER BY cdoc_tipo;
END
GO

/* ── 24. webDatpos_cargarListPrecio (DDL listas precio) ── */
IF OBJECT_ID('webDatpos_cargarListPrecio','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarListPrecio; 
GO
CREATE PROCEDURE webDatpos_cargarListPrecio @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_cblistpre, cdsc_cblistpre FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

PRINT '✓ FIX 22A: SPs Prioridad Alta (24 SPs) creados.';
GO
