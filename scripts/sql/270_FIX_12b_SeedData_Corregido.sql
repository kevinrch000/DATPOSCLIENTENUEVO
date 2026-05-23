/* FIX 12b — SEED DATA CORREGIDO (nombres reales de tablas/columnas) */
USE DatPos_EMP01;
GO

/* 1. ConfigGeneral — sin cnombre_moneda/csimbolo_moneda/cstatus */
IF NOT EXISTS (SELECT 1 FROM ConfigGeneral WHERE ccod_cia='EMP01')
INSERT INTO ConfigGeneral(ccod_cia, nigv, nisc, nmonto_maxboleta, coper_ingreso, coper_salida)
VALUES('EMP01', 18, 0, 700, 'INGRESO', 'SALIDA');
GO

/* 2. NumeradorCaja (no existe tabla "Numeradores" ni "TipoDocumento") */
IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01')
INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer) VALUES
('EMP01','CAJ01','B','B001',1,'BOLETA'),
('EMP01','CAJ01','F','F001',1,'FACTURA'),
('EMP01','CAJ01','T','T001',1,'TICKET');
GO

/* 3. UnidadMedida — columnas reales: ccod_unimed, cdsc_unimed */
IF NOT EXISTS (SELECT 1 FROM UnidadMedida WHERE ccod_cia='EMP01')
INSERT INTO UnidadMedida(ccod_cia, ccod_unimed, cdsc_unimed, cstatus) VALUES
('EMP01','UND','UNIDAD','A'),
('EMP01','KG','KILOGRAMO','A'),
('EMP01','PZA','PIEZA','A');
GO

/* 4. Familias — PK es id_lin (no id_ctlin) */
IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01')
INSERT INTO Familias(ccod_cia, ccod_lin, cdsc_lin, ccolor, cstatus) VALUES
('EMP01','FAM001','BEBIDAS','#e74c3c','A'),
('EMP01','FAM002','COMIDAS','#e67e22','A'),
('EMP01','FAM003','SNACKS','#2ecc71','A');
GO

/* 5. Coa (cliente por defecto) — sin columna ctip_doc */
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa='CLI000')
INSERT INTO Coa(ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, ctipo_coa, cstatus, cproveedor, cdirc_coa)
VALUES('EMP01','CLI000','00000000','CONSUMIDOR FINAL','CL','A','0','SIN DIRECCION');
GO

/* 6. CbListaPrecio (no existe tabla "ListaPrecios") */
IF NOT EXISTS (SELECT 1 FROM CbListaPrecio WHERE ccod_cia='EMP01')
INSERT INTO CbListaPrecio(ccod_cia, ccod_cblistpre, cdsc_cblistpre, cstatus) VALUES
('EMP01','LP001','PRECIO NORMAL','A'),
('EMP01','LP002','PRECIO MAYORISTA','A');
GO

/* 7. Articulos — columnas reales: ccod_lin, uni_medi (no id_ctlin/npre_costo) */
IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia='EMP01')
INSERT INTO Articulos(ccod_cia, ccod_articulo, cdsc_articulo, ccod_lin, uni_medi, ctip_articulo, cstatus, cigv, bprefer) VALUES
('EMP01','ART001','COCA COLA 500ML','FAM001','UND','P','A','18',0),
('EMP01','ART002','INCA KOLA 500ML','FAM001','UND','P','A','18',0),
('EMP01','ART003','AGUA SAN LUIS','FAM001','UND','P','A','18',1),
('EMP01','ART004','COMBO ALMUERZO','FAM002','UND','S','A','18',1),
('EMP01','ART005','PAPAS FRITAS','FAM003','UND','P','A','18',0);
GO

/* 8. LnListaPrecio (no existe tabla "Precios") */
IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia='EMP01')
BEGIN
  INSERT INTO LnListaPrecio(ccod_cia, ccod_cblistpre, ccod_articulo, npre_uni, ndes_max) VALUES
  ('EMP01','LP001','ART001',3.50,10),
  ('EMP01','LP001','ART002',3.50,10),
  ('EMP01','LP001','ART003',2.00,5),
  ('EMP01','LP001','ART004',15.00,0),
  ('EMP01','LP001','ART005',3.00,5),
  ('EMP01','LP002','ART001',3.00,10),
  ('EMP01','LP002','ART002',3.00,10),
  ('EMP01','LP002','ART003',1.50,5),
  ('EMP01','LP002','ART004',12.00,0),
  ('EMP01','LP002','ART005',2.50,5);
END
GO

/* 9. Tiendas — asignar listas de precios */
UPDATE Tiendas SET nlista_pre_normal=1, nlista_pre_preferencial=2
WHERE ccod_cia='EMP01' AND ccod_tiend='T001';
GO

/* 10. Stock inicial para articulos */
IF NOT EXISTS (SELECT 1 FROM Stock WHERE ccod_cia='EMP01')
INSERT INTO Stock(ccod_cia, ccod_alm, ccod_articulo, ncantidad, ncosto) VALUES
('EMP01','ALM001','ART001',100,2.00),
('EMP01','ALM001','ART002',100,2.00),
('EMP01','ALM001','ART003',50,1.00),
('EMP01','ALM001','ART004',30,8.00),
('EMP01','ALM001','ART005',80,1.50);
GO

/* ── SPs CORREGIDOS ─────────────────────────────────────────────── */

/* sp_validarfacturacion */
IF OBJECT_ID('sp_validarfacturacion','P') IS NOT NULL DROP PROCEDURE sp_validarfacturacion;
GO
CREATE PROCEDURE sp_validarfacturacion
    @CodCia VARCHAR(20), @ccod_usuario VARCHAR(50), @resp NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @resp = '';
    IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario AND ccod_tiend IS NOT NULL AND ccod_caja IS NOT NULL)
        SET @resp = 'El usuario no tiene tienda o caja asignada.';
    IF @resp = ''
    BEGIN
        DECLARE @caja VARCHAR(20)=(SELECT ccod_caja FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario);
        IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia=@CodCia AND ccod_caja=@caja)
            SET @resp = 'No hay numeradores para la caja del usuario.';
    END
END
GO

/* sp_validaralfacturar */
IF OBJECT_ID('sp_validaralfacturar','P') IS NOT NULL DROP PROCEDURE sp_validaralfacturar;
GO
CREATE PROCEDURE sp_validaralfacturar
    @CodCia VARCHAR(20), @ccod_usuario VARCHAR(50), @cdoc_tipo VARCHAR(10), @resp NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @resp = '';
    DECLARE @caja VARCHAR(20)=(SELECT ccod_caja FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario);
    IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia=@CodCia AND ccod_caja=@caja AND cdoc_tipo=@cdoc_tipo)
        SET @resp = 'Sin numerador para: ' + @cdoc_tipo;
END
GO

/* sp_consultarusuarioturno — tabla Turno (no AperturaCaja) */
IF OBJECT_ID('sp_consultarusuarioturno','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarioturno;
GO
CREATE PROCEDURE sp_consultarusuarioturno @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT id_turno FROM Turno WHERE ccod_cia=@ccod_cia AND ccod_usuario=@ccod_usuario AND cstatus='A';
END
GO

/* appDatpos_abrirCaja — usa tabla Turno */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja;
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie VARCHAR(20), @IdUsuario VARCHAR(50), @CodCaj VARCHAR(20),
    @Monto DECIMAL(18,4), @CodCia VARCHAR(20), @CodUsu VARCHAR(50), @dfchdoc_ini DATETIME
AS BEGIN SET NOCOUNT ON;
    UPDATE Turno SET cstatus='C', dfchdoc_fin=GETDATE() WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND cstatus='A';
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');
    SELECT CAST(SCOPE_IDENTITY() AS INT) AS id_turno, 'OK' AS resultado;
END
GO

/* appDatpos_cierreCaja */
IF OBJECT_ID('appDatpos_cierreCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_cierreCaja;
GO
CREATE PROCEDURE appDatpos_cierreCaja
    @id_turno INT, @ntot_entreg DECIMAL(18,4), @nmonto_fin DECIMAL(18,4),
    @ndiferencia DECIMAL(18,4), @CodCia VARCHAR(20), @CodUsu VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Turno SET cstatus='C', nmonto_fin=@nmonto_fin, ntot_entreg=@ntot_entreg,
        ndiferencia=@ndiferencia, dfchdoc_fin=GETDATE()
    WHERE id_turno=@id_turno AND ccod_cia=@CodCia;
    SELECT 'OK' AS resultado;
END
GO

/* webDatpos_consultarCierreCaja */
IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus
    FROM Turno WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* webDatpos_consultarIdCierreCaja */
IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin,
        ntot_entreg, ndiferencia, dfchdoc_ini, dfchdoc_fin, cstatus
    FROM Turno WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno;
END
GO

/* webDatpos_cargarCajaDeUsuario */
IF OBJECT_ID('webDatpos_cargarCajaDeUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCajaDeUsuario;
GO
CREATE PROCEDURE webDatpos_cargarCajaDeUsuario @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_caja, C.cdsc_caja FROM Usuarios U
    JOIN Cajas C ON C.ccod_cia=U.ccod_empresa AND C.ccod_caja=U.ccod_caja
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_usuario=@ccod_usuario;
END
GO

/* webDatpos_cargarTurnoUsuario */
IF OBJECT_ID('webDatpos_cargarTurnoUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTurnoUsuario;
GO
CREATE PROCEDURE webDatpos_cargarTurnoUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini
    FROM Turno WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* webDatpos_cargarIdUsuario */
IF OBJECT_ID('webDatpos_cargarIdUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarIdUsuario;
GO
CREATE PROCEDURE webDatpos_cargarIdUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_usuario, ccod_usuario, cdsc_usuario FROM Usuarios WHERE ccod_empresa=@ccod_cia;
END
GO

/* appDatpos_ObtenerIGV */
IF OBJECT_ID('appDatpos_ObtenerIGV','P') IS NOT NULL DROP PROCEDURE appDatpos_ObtenerIGV;
GO
CREATE PROCEDURE appDatpos_ObtenerIGV @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT nigv, nisc FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* sp_clientepordefecto — sin ctip_doc (no existe en Coa) */
IF OBJECT_ID('sp_clientepordefecto','P') IS NOT NULL DROP PROCEDURE sp_clientepordefecto;
GO
CREATE PROCEDURE sp_clientepordefecto @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ctipo_coa AS ctip_doc, cdoc_coa, cdsc_coa, cdirc_coa, id_coa, ccod_coa
    FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa='CLI000';
END
GO

/* sp_consultarclientestodos — sin ctip_doc */
IF OBJECT_ID('sp_consultarclientestodos','P') IS NOT NULL DROP PROCEDURE sp_consultarclientestodos;
GO
CREATE PROCEDURE sp_consultarclientestodos
    @ccod_cia VARCHAR(20), @texto VARCHAR(100), @ccod_usuario VARCHAR(50), @tipodoc VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT cdsc_coa, id_coa, cdoc_coa, ctipo_coa, cdirc_coa, ctipo_coa AS ctip_doc
    FROM Coa
    WHERE ccod_cia=@ccod_cia AND cstatus='A'
      AND (cdsc_coa LIKE '%'+@texto+'%' OR cdoc_coa LIKE '%'+@texto+'%');
END
GO

/* sp_consultarFamiliasActivas — usa id_lin (no id_ctlin) */
IF OBJECT_ID('sp_consultarFamiliasActivas','P') IS NOT NULL DROP PROCEDURE sp_consultarFamiliasActivas;
GO
CREATE PROCEDURE sp_consultarFamiliasActivas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_lin, cdsc_lin, id_lin AS id_ctlin, ccolor
    FROM Familias WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* sp_actualizarfavorito */
IF OBJECT_ID('sp_actualizarfavorito','P') IS NOT NULL DROP PROCEDURE sp_actualizarfavorito;
GO
CREATE PROCEDURE sp_actualizarfavorito @ccod_cia VARCHAR(20), @id_articulo INT, @bprefer INT
AS BEGIN SET NOCOUNT ON;
    UPDATE Articulos SET bprefer=@bprefer WHERE ccod_cia=@ccod_cia AND id_articulo=@id_articulo;
    SELECT 'OK' AS resultado;
END
GO

/* sp_actualizarnumeradorcobranza */
IF OBJECT_ID('sp_actualizarnumeradorcobranza','P') IS NOT NULL DROP PROCEDURE sp_actualizarnumeradorcobranza;
GO
CREATE PROCEDURE sp_actualizarnumeradorcobranza @ccod_cia VARCHAR(20), @ccod_caja VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON; SELECT 'OK' AS resultado; END
GO

/* webDatpos_OptenerImpuesto */
IF OBJECT_ID('webDatpos_OptenerImpuesto','P') IS NOT NULL DROP PROCEDURE webDatpos_OptenerImpuesto;
GO
CREATE PROCEDURE webDatpos_OptenerImpuesto @ccod_cia VARCHAR(20), @IGV NVARCHAR(16) OUTPUT, @ISC NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SELECT @IGV=CAST(nigv AS NVARCHAR(16)), @ISC=CAST(nisc AS NVARCHAR(16)) FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* ── VERIFICACIÓN ───────────────────────────────────────────────── */
SELECT 'ConfigGeneral' AS tabla, COUNT(*) AS filas FROM ConfigGeneral WHERE ccod_cia='EMP01'
UNION ALL SELECT 'UnidadMedida', COUNT(*) FROM UnidadMedida WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Familias',     COUNT(*) FROM Familias     WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Articulos',    COUNT(*) FROM Articulos    WHERE ccod_cia='EMP01'
UNION ALL SELECT 'LnListaPrecio',COUNT(*) FROM LnListaPrecio WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Coa',          COUNT(*) FROM Coa          WHERE ccod_cia='EMP01'
UNION ALL SELECT 'CbListaPrecio',COUNT(*) FROM CbListaPrecio WHERE ccod_cia='EMP01'
UNION ALL SELECT 'NumeradorCaja',COUNT(*) FROM NumeradorCaja WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Stock',        COUNT(*) FROM Stock        WHERE ccod_cia='EMP01';
GO
PRINT 'OK - Seed data corregido listo.';
GO
