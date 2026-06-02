/* =====================================================================
   FIX 14 — SP webDatpos_consultaTienda + Seed Data Tiendas/Cajas/Usuarios
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. SEED DATA: Tienda, Almacén, Caja, TiendaAlmacén, TiendaCaja ── */

-- Tienda
IF NOT EXISTS (SELECT 1 FROM Tiendas WHERE ccod_cia='EMP01' AND ccod_tiend='T001')
INSERT INTO Tiendas(ccod_cia, ccod_tiend, cnombr, cdirec, cstatus, nlista_pre_normal, nlista_pre_preferencial)
VALUES('EMP01','T001','TIENDA PRINCIPAL','AV. PRINCIPAL 123','A',1,2);
GO

-- Almacén
IF NOT EXISTS (SELECT 1 FROM Almacenes WHERE ccod_cia='EMP01' AND ccod_alm='ALM001')
INSERT INTO Almacenes(ccod_cia, ccod_alm, cdsc_alm, cstatus)
VALUES('EMP01','ALM001','ALMACEN PRINCIPAL','A');
GO

-- Caja
IF NOT EXISTS (SELECT 1 FROM Cajas WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01')
INSERT INTO Cajas(ccod_cia, ccod_caja, cdsc_caja, cstatus)
VALUES('EMP01','CAJ01','CAJA 01','A');
GO

-- TiendaAlmacen
IF NOT EXISTS (SELECT 1 FROM TiendaAlmacen WHERE ccod_cia='EMP01' AND ccod_tiend='T001' AND ccod_alm='ALM001')
INSERT INTO TiendaAlmacen(ccod_cia, ccod_tiend, ccod_alm) VALUES('EMP01','T001','ALM001');
GO

-- TiendaCaja
IF NOT EXISTS (SELECT 1 FROM TiendaCaja WHERE ccod_cia='EMP01' AND ccod_tiend='T001' AND ccod_caja='CAJ01')
INSERT INTO TiendaCaja(ccod_cia, ccod_tiend, ccod_caja, ccod_usuario) VALUES('EMP01','T001','CAJ01','ADMIN');
GO

-- NumeradorCaja (si no se creó antes)
IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01')
INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer) VALUES
('EMP01','CAJ01','B','B001',1,'BOLETA'),
('EMP01','CAJ01','F','F001',1,'FACTURA'),
('EMP01','CAJ01','T','T001',1,'TICKET');
GO

-- Asignar tienda, almacén y caja al usuario ADMIN
UPDATE Usuarios
SET ccod_tiend  = 'T001',
    ccod_almacen= 'ALM001',
    ccod_caja   = 'CAJ01'
WHERE ccod_empresa = 'EMP01';
GO

/* ── 2. webDatpos_consultaTienda (@ccod_cia)
   Retorna: ccod_tiend[0], cnombr[1]
   (AperturaCaja.aspx.vb línea 66-67)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultaTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaTienda;
GO
CREATE PROCEDURE webDatpos_consultaTienda @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend, cnombr FROM Tiendas WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cnombr;
END
GO

/* ── 3. webDatpos_cargarIdUsuario (@ccod_cia, @ccod_tienda)
   Retorna: ccod_usuario[0], cdsc_usuario[1]
   (AperturaCaja.aspx.vb línea 97-98)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarIdUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarIdUsuario;
GO
CREATE PROCEDURE webDatpos_cargarIdUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_usuario, cdsc_usuario
    FROM Usuarios
    WHERE ccod_empresa=@ccod_cia AND ccod_tiend=@ccod_tienda AND id_estado=1;
END
GO

/* ── 4. webDatpos_cargarCajaDeUsuario (@ccod_cia, @ccod_usuario)
   Retorna: ccod_caja[0], cdsc_caja[1]
   (AperturaCaja.aspx.vb línea 79-80)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarCajaDeUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCajaDeUsuario;
GO
CREATE PROCEDURE webDatpos_cargarCajaDeUsuario @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_caja, C.cdsc_caja
    FROM Usuarios U
    JOIN Cajas C ON C.ccod_cia=U.ccod_empresa AND C.ccod_caja=U.ccod_caja
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_usuario=@ccod_usuario;
END
GO

/* ── 5. webDatpos_consultarCierreCaja — para pestaña Lista
   Retorna: id_turno[0], ccod_tienda[1], ccod_usuario[2], ccod_caja[3],
            nmonto_ini[4], nmonto_fin[5], dfchdoc_ini[6], dfchdoc_fin[7], cstatus[8]
   (AperturaCaja.aspx.vb líneas 47-55)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.id_turno,
           T.ccod_tienda,
           T.ccod_usuario,
           T.ccod_caja,
           T.nmonto_ini,
           T.nmonto_fin,
           T.dfchdoc_ini,
           T.dfchdoc_fin,
           T.cstatus
    FROM Turno T
    WHERE T.ccod_cia=@ccod_cia
    ORDER BY T.id_turno DESC;
END
GO

/* ── 6. webDatpos_consultarIdCierreCaja — detalle de turno
   Retorna: id_turno[0..12] (AperturaCaja.aspx.vb líneas 116-129)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_turno,
           ccod_tienda,   -- [1]
           ccod_usuario,  -- [2]
           ccod_caja,     -- [3]
           nmonto_ini,    -- [4]
           nmonto_fin,    -- [5]
           dfchdoc_ini,   -- [6]
           dfchdoc_fin,   -- [7]
           ccod_tienda,   -- [8] ccod_tienda
           ccod_usuario,  -- [9] ccod_usuario
           ccod_caja,     -- [10] ccod_caja
           ntot_entreg,   -- [11]
           ndiferencia    -- [12]
    FROM Turno
    WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno;
END
GO

/* ── 7. appDatpos_abrirCaja — guarda el turno
   Retorna: id_turno[0]
   (AperturaCaja.aspx.vb línea 32)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja;
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie      VARCHAR(20),
    @IdUsuario   VARCHAR(50),
    @CodCaj      VARCHAR(20),
    @Monto       DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50),
    @dfchdoc_ini DATETIME
AS BEGIN SET NOCOUNT ON;
    -- Cerrar turno anterior del usuario si lo hay
    UPDATE Turno SET cstatus='C', dfchdoc_fin=GETDATE()
    WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND cstatus='A';
    -- Crear nuevo turno
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');
    -- Retornar id_turno en índice [0]
    SELECT CAST(SCOPE_IDENTITY() AS INT) AS id_turno;
END
GO

/* ── VERIFICACIÓN ─────────────────────────────────────────────── */
SELECT 'Tiendas'  AS tabla, COUNT(*) AS filas FROM Tiendas    WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Almacenes', COUNT(*) FROM Almacenes  WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Cajas',     COUNT(*) FROM Cajas      WHERE ccod_cia='EMP01'
UNION ALL SELECT 'Usuarios con tienda/caja', COUNT(*) FROM Usuarios WHERE ccod_empresa='EMP01' AND ccod_caja IS NOT NULL
UNION ALL SELECT 'NumeradorCaja', COUNT(*) FROM NumeradorCaja WHERE ccod_cia='EMP01';
GO

SELECT name AS sp FROM sys.procedures
WHERE name IN ('webDatpos_consultaTienda','webDatpos_cargarIdUsuario',
               'webDatpos_cargarCajaDeUsuario','webDatpos_consultarCierreCaja',
               'webDatpos_consultarIdCierreCaja','appDatpos_abrirCaja',
               'sp_consultarusuarioturno')
ORDER BY name;
GO
PRINT 'OK - FIX 14 completo.';
GO
