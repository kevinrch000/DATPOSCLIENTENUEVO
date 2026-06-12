/* =====================================================================
   FIX 17 — Corrección de NumeradorCaja: códigos reales del sistema
   
   El JS envía como cdoc el valor del radio button seleccionado.
   Los valores reales son: BV (Boleta de Venta), FA (Factura), NV (Nota de Venta)
   
   El SP sp_validaralfacturar valida que exista un registro en NumeradorCaja
   con cdoc_tipo = cdoc enviado.
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. Limpiar y recrear NumeradorCaja con códigos correctos ── */
DELETE FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01';
GO

INSERT INTO NumeradorCaja(ccod_cia, ccod_caja, cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer) VALUES
('EMP01','CAJ01','BV', 'B001', 1, 'BOLETA DE VENTA'),
('EMP01','CAJ01','FA', 'F001', 1, 'FACTURA'),
('EMP01','CAJ01','NV', 'T001', 1, 'NOTA DE VENTA'),
('EMP01','CAJ01','NC', 'NC01', 1, 'NOTA DE CREDITO'),
('EMP01','CAJ01','ND', 'ND01', 1, 'NOTA DE DEBITO');
GO

/* ── 2. Verificar el SP sp_validaralfacturar (creado en FIX_12b) ──
   Si ya existe, no hace falta recrear. Solo verificamos que use cdoc_tipo correctamente.
   Si NO existe lo creamos. 
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_validaralfacturar','P') IS NULL
BEGIN
    EXEC('
    CREATE PROCEDURE sp_validaralfacturar
        @CodCia VARCHAR(20), @caja VARCHAR(20), @cdoc_tipo VARCHAR(10), @resp VARCHAR(200) OUTPUT
    AS BEGIN SET NOCOUNT ON;
        SET @resp = '''';
        IF NOT EXISTS (SELECT 1 FROM NumeradorCaja WHERE ccod_cia=@CodCia AND ccod_caja=@caja AND cdoc_tipo=@cdoc_tipo)
            SET @resp = ''Sin numerador para: '' + @cdoc_tipo;
    END')
END
GO

/* ── 3. sp_consultarnumeradorcaja — retorna serie y número actual
   Llamado antes de insertar la venta para obtener cdoc_serie y cdoc_nro
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultarnumeradorcaja','P') IS NOT NULL DROP PROCEDURE sp_consultarnumeradorcaja;
GO
CREATE PROCEDURE sp_consultarnumeradorcaja
    @ccod_cia VARCHAR(20), @ccod_caja VARCHAR(20), @cdoc_tipo VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT cdoc_serie, cdoc_nro, cdsc_numer
    FROM NumeradorCaja
    WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc_tipo;
END
GO

/* ── 4. sp_actualizarnumeradorcaja — incrementa el número después de grabar ── */
IF OBJECT_ID('sp_actualizarnumeradorcaja','P') IS NOT NULL DROP PROCEDURE sp_actualizarnumeradorcaja;
GO
CREATE PROCEDURE sp_actualizarnumeradorcaja
    @ccod_cia VARCHAR(20), @ccod_caja VARCHAR(20), @cdoc_tipo VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    UPDATE NumeradorCaja
    SET cdoc_nro = cdoc_nro + 1
    WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc_tipo;
    SELECT 'OK' AS resultado;
END
GO

/* ── 5. sp_actualizarnumeradorcobranza — stub requerido por DAMovimientoCabecera ── */
IF OBJECT_ID('sp_actualizarnumeradorcobranza','P') IS NOT NULL DROP PROCEDURE sp_actualizarnumeradorcobranza;
GO
CREATE PROCEDURE sp_actualizarnumeradorcobranza
    @ccod_cia VARCHAR(20), @ccod_caja VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT 'OK' AS resultado;
END
GO

/* ── VERIFICACIÓN ── */
SELECT cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer
FROM NumeradorCaja WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01'
ORDER BY cdoc_tipo;
GO

-- Probar la validación con BV:
DECLARE @r VARCHAR(200)='';
EXEC sp_validaralfacturar 'EMP01','CAJ01','BV',@r OUTPUT;
SELECT @r AS resultado_BV;  -- debe quedar vacío ''
GO

PRINT 'OK - FIX 17 completo.';
GO
