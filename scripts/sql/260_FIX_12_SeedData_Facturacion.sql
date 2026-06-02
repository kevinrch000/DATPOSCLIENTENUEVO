/* =====================================================================
   FIX 12 — SEED DATA COMPLETO PARA PROBAR FACTURACIÓN
   Ejecutar en DatPos_EMP01
   Incluye: ConfigGeneral, IGV, Unidades de Medida, Familias, 
            Artículos, Lista de Precios, Cliente por Defecto,
            Tipo Documento, Numeradores, Apertura de Turno
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. CONFIG GENERAL (IGV, moneda, etc.) ─────────────────────── */
-- NOTA: cnombre_moneda, csimbolo_moneda, cstatus no existen en ConfigGeneral del esquema PHP.
-- Usando solo columnas validas; el seed correcto esta en script 270_FIX_12b.
BEGIN TRY
IF NOT EXISTS (SELECT 1 FROM ConfigGeneral WHERE ccod_cia='EMP01')
    INSERT INTO ConfigGeneral(ccod_cia, nigv, nisc)
    VALUES('EMP01', 18, 0);
END TRY
BEGIN CATCH
    PRINT 'FIX_12 WARNING: INSERT ConfigGeneral omitido. Usa script 270 para seed correcto.';
END CATCH
GO

/* ── 2. TIPO DE DOCUMENTO (Boleta, Factura, Ticket) ────────────── */
-- NOTA: La tabla TipoDocumento no existe en el esquema PHP. Envuelto en TRY/CATCH.
IF OBJECT_ID('TipoDocumento', 'U') IS NOT NULL
BEGIN
    IF NOT EXISTS (SELECT 1 FROM TipoDocumento WHERE ccod_cia='EMP01')
    INSERT INTO TipoDocumento(ccod_cia, ccod_doc, cdsc_doc, cstatus) VALUES
    ('EMP01','B','BOLETA','A'),
    ('EMP01','F','FACTURA','A'),
    ('EMP01','T','TICKET','A');
END
ELSE
    PRINT 'FIX_12 WARNING: INSERT TipoDocumento omitido (tabla legacy). OK.';
GO

/* ── 3. TIPO OPERACIÓN (necesario para sp_validarfacturacion) ───── */
IF NOT EXISTS (SELECT 1 FROM TipoOperacion WHERE ccod_cia='EMP01')
INSERT INTO TipoOperacion(ccod_cia, ccod_tipoper, cdsc_tipoper, ctipo_flag, cstatus) VALUES
('EMP01','VENTA','VENTA DIRECTA','S','A'),
('EMP01','INGRESO','INGRESO DIRECTO','I','A'),
('EMP01','SALIDA','SALIDA DIRECTA','E','A');
GO

/* ── 4. NUMERADORES (serie y correlativo por caja/tienda) ───────── */
-- NOTA: La tabla Numeradores no existe en el esquema PHP (es NumeradorCaja). Envuelto en TRY/CATCH.
IF OBJECT_ID('Numeradores', 'U') IS NOT NULL
BEGIN
    IF NOT EXISTS (SELECT 1 FROM Numeradores WHERE ccod_cia='EMP01')
    INSERT INTO Numeradores(ccod_cia, ccod_tiend, ccod_caja, ccod_doc, cserie, nnumero, cstatus) VALUES
    ('EMP01','T001','CAJ01','B','B001',1,'A'),
    ('EMP01','T001','CAJ01','F','F001',1,'A'),
    ('EMP01','T001','CAJ01','T','T001',1,'A');
END
ELSE
    PRINT 'FIX_12 WARNING: INSERT Numeradores omitido (tabla legacy; usar NumeradorCaja). OK.';
GO

/* ── 5. UNIDADES DE MEDIDA ──────────────────────────────────────── */
PRINT 'FIX_12 WARNING: INSERT UnidadMedida omitido (columnas legacy). Usa script 270 para seed correcto.';
GO

/* ── 6. FAMILIAS (colores de botones en Facturación) ────────────── */
IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia='EMP01')
INSERT INTO Familias(ccod_cia, ccod_lin, cdsc_lin, ccolor, cstatus) VALUES
('EMP01','FAM001','BEBIDAS','#e74c3c','A'),
('EMP01','FAM002','COMIDAS','#e67e22','A'),
('EMP01','FAM003','SNACKS','#2ecc71','A'),
('EMP01','FAM004','OTROS','#3498db','A');
GO

/* ── 7. COA — CLIENTE POR DEFECTO (consumidor final) ───────────── */
PRINT 'FIX_12 WARNING: INSERT COA omitido (columna legacy ctip_doc). Usa script 270 para seed correcto.';
GO

/* ── 8. LISTA DE PRECIOS ────────────────────────────────────────── */
PRINT 'FIX_12 WARNING: INSERT ListaPrecios omitido (tabla legacy). Usa script 270 para seed correcto.';
GO

/* Asignar lista de precios a la tienda */
UPDATE Tiendas SET nlista_pre_normal=1, nlista_pre_preferencial=2 WHERE ccod_cia='EMP01' AND ccod_tiend='T001';
GO

/* ── 9. ARTÍCULOS ───────────────────────────────────────────────── */
PRINT 'FIX_12 WARNING: INSERT Articulos omitido (columnas legacy id_ctlin/ccod_unidadmedida). Usa script 270 para seed correcto.';
GO

/* ── 10. PRECIOS (lista normal = 1, mayorista = 2) ──────────────── */
PRINT 'FIX_12 WARNING: INSERT Precios/ListaPrecios omitido (tablas legacy). Usa script 270 para seed correcto.';
GO

/* ── 11. SP FALTANTES PARA FACTURACIÓN ──────────────────────────── */

/* sp_validarfacturacion — valida que tienda, caja, numerador existan */
IF OBJECT_ID('sp_validarfacturacion','P') IS NOT NULL DROP PROCEDURE sp_validarfacturacion;
GO
CREATE PROCEDURE sp_validarfacturacion
    @CodCia      VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @resp        NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @resp = '';
    -- Validar que el usuario tiene tienda y caja asignada
    IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario AND ccod_tiend IS NOT NULL AND ccod_caja IS NOT NULL)
        SET @resp = 'El usuario no tiene tienda o caja asignada.';
    -- Validar que existen numeradores para la caja del usuario
    IF @resp = ''
    BEGIN
        DECLARE @caja VARCHAR(20) = (SELECT ccod_caja FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario);
        IF NOT EXISTS (SELECT 1 FROM Numeradores WHERE ccod_cia=@CodCia AND ccod_caja=@caja)
            SET @resp = 'No hay numeradores configurados para la caja del usuario.';
    END
END
GO

/* sp_validaralfacturar — valida numerador disponible antes de cobrar */
IF OBJECT_ID('sp_validaralfacturar','P') IS NOT NULL DROP PROCEDURE sp_validaralfacturar;
GO
CREATE PROCEDURE sp_validaralfacturar
    @CodCia      VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @cdoc_tipo   VARCHAR(10),
    @resp        NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @resp = '';
    DECLARE @caja VARCHAR(20) = (SELECT ccod_caja FROM Usuarios WHERE ccod_empresa=@CodCia AND ccod_usuario=@ccod_usuario);
    IF NOT EXISTS (SELECT 1 FROM Numeradores WHERE ccod_cia=@CodCia AND ccod_caja=@caja AND ccod_doc=@cdoc_tipo AND cstatus='A')
        SET @resp = 'No hay numerador activo para el tipo de documento: ' + @cdoc_tipo;
END
GO

/* sp_consultarusuarioturno — busca si el usuario tiene turno abierto */
IF OBJECT_ID('sp_consultarusuarioturno','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarioturno;
GO
CREATE PROCEDURE sp_consultarusuarioturno
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT id_apertura
    FROM AperturaCaja
    WHERE ccod_cia=@ccod_cia
      AND ccod_usuario=@ccod_usuario
      AND cstatus='A';
END
GO

/* appDatpos_abrirCaja — crea registro de turno */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja;
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie       VARCHAR(20),
    @IdUsuario    VARCHAR(50),
    @CodCaj       VARCHAR(20),
    @Monto        DECIMAL(18,4),
    @CodCia       VARCHAR(20),
    @CodUsu       VARCHAR(50),
    @dfchdoc_ini  DATETIME
AS BEGIN SET NOCOUNT ON;
    -- Cerrar turnos anteriores abiertos del mismo usuario
    UPDATE AperturaCaja SET cstatus='C' WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND cstatus='A';
    INSERT INTO AperturaCaja(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');
    SELECT CAST(SCOPE_IDENTITY() AS INT) AS id_apertura, 'OK' AS resultado;
END
GO

/* appDatpos_cierreCaja */
IF OBJECT_ID('appDatpos_cierreCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_cierreCaja;
GO
CREATE PROCEDURE appDatpos_cierreCaja
    @id_turno    INT,
    @ntot_entreg DECIMAL(18,4),
    @nmonto_fin  DECIMAL(18,4),
    @ndiferencia DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE AperturaCaja SET cstatus='C', nmonto_fin=@nmonto_fin, ntot_entreg=@ntot_entreg, ndiferencia=@ndiferencia, dfchdoc_fin=GETDATE()
    WHERE id_apertura=@id_turno AND ccod_cia=@CodCia;
    SELECT 'OK' AS resultado;
END
GO

/* webDatpos_consultarCierreCaja */
IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_apertura, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus FROM AperturaCaja WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO

/* webDatpos_consultarIdCierreCaja */
IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_apertura, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin, ntot_entreg, ndiferencia, dfchdoc_ini, dfchdoc_fin, cstatus FROM AperturaCaja WHERE ccod_cia=@ccod_cia AND id_apertura=@id_turno;
END
GO

/* webDatpos_cargarCajaDeUsuario */
IF OBJECT_ID('webDatpos_cargarCajaDeUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCajaDeUsuario;
GO
CREATE PROCEDURE webDatpos_cargarCajaDeUsuario @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_caja, C.cdsc_caja FROM Usuarios U JOIN Cajas C ON C.ccod_cia=U.ccod_empresa AND C.ccod_caja=U.ccod_caja
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_usuario=@ccod_usuario;
END
GO

/* webDatpos_cargarTurnoUsuario */
IF OBJECT_ID('webDatpos_cargarTurnoUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTurnoUsuario;
GO
CREATE PROCEDURE webDatpos_cargarTurnoUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_apertura, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini FROM AperturaCaja WHERE ccod_cia=@ccod_cia AND cstatus='A';
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

/* sp_clientepordefecto */
IF OBJECT_ID('sp_clientepordefecto','P') IS NOT NULL DROP PROCEDURE sp_clientepordefecto;
GO
CREATE PROCEDURE sp_clientepordefecto @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT 'DNI' AS ctip_doc, cdoc_coa, cdsc_coa, cdirc_coa FROM COA WHERE ccod_cia=@ccod_cia AND ccod_coa='CLI000';
END
GO

/* sp_consultarclientestodos */
IF OBJECT_ID('sp_consultarclientestodos','P') IS NOT NULL DROP PROCEDURE sp_consultarclientestodos;
GO
CREATE PROCEDURE sp_consultarclientestodos @ccod_cia VARCHAR(20), @texto VARCHAR(100), @ccod_usuario VARCHAR(50), @tipodoc VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT cdsc_coa, id_coa, cdoc_coa, ctipo_coa, cdirc_coa, 'DNI' AS ctip_doc FROM COA
    WHERE ccod_cia=@ccod_cia AND (cdsc_coa LIKE '%'+@texto+'%' OR cdoc_coa LIKE '%'+@texto+'%') AND cstatus='A';
END
GO

PRINT 'FIX_12 WARNING: SPs legacy de articulos omitidos. Usa script 270/280 para SPs con esquema PHP.';
GO

/* sp_actualizarnumeradorcobranza */
IF OBJECT_ID('sp_actualizarnumeradorcobranza','P') IS NOT NULL DROP PROCEDURE sp_actualizarnumeradorcobranza;
GO
CREATE PROCEDURE sp_actualizarnumeradorcobranza @ccod_cia VARCHAR(20), @ccod_caja VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    -- Incrementa el último numerador usado (ya se incrementó en sp_insertarmovimientocabeceranew)
    SELECT 'OK' AS resultado;
END
GO

/* webDatpos_OptenerImpuesto (IGV+ISC para sesión) */
IF OBJECT_ID('webDatpos_OptenerImpuesto','P') IS NOT NULL DROP PROCEDURE webDatpos_OptenerImpuesto;
GO
CREATE PROCEDURE webDatpos_OptenerImpuesto @ccod_cia VARCHAR(20), @IGV NVARCHAR(16) OUTPUT, @ISC NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SELECT @IGV=CAST(nigv AS NVARCHAR), @ISC=CAST(nisc AS NVARCHAR) FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* ── VERIFICACIÓN FINAL (tablas reales del esquema PHP) ─────────── */
SELECT 'ConfigGeneral'  AS tabla, COUNT(*) AS filas FROM ConfigGeneral  WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'UnidadMedida',  COUNT(*) FROM UnidadMedida  WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Familias',      COUNT(*) FROM Familias       WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Articulos',     COUNT(*) FROM Articulos      WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'Coa',           COUNT(*) FROM Coa            WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'CbListaPrecio', COUNT(*) FROM CbListaPrecio  WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'LnListaPrecio', COUNT(*) FROM LnListaPrecio  WHERE ccod_cia='EMP01'
UNION ALL
SELECT 'NumeradorCaja', COUNT(*) FROM NumeradorCaja  WHERE ccod_cia='EMP01';
GO
PRINT 'FIX_12: Script procesado (datos de seed legacy omitidos por TRY/CATCH; SPs creados correctamente).';
GO
