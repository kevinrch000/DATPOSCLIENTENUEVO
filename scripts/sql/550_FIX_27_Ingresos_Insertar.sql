/* =====================================================================
   FIX 27 — webDatpos_insertarinventario para Ingresos
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. webDatpos_insertarinventario ──
   Se corrige el tipo de @dfecha a VARCHAR(20) y se fuerza CONVERT(DATETIME, @dfecha, 103)
   (dd/mm/yyyy) porque el frontend envía '24/04/2026' y daba error fuera de rango.
   Además, se cambia para usar NumeradorAlmacen al igual que Salidas, 
   o mantener el MAX(nnumero) si NumeradorAlmacen no tiene el tipo 'I'.
*/
IF OBJECT_ID('webDatpos_insertarinventario','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarinventario;
GO
CREATE PROCEDURE webDatpos_insertarinventario
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha VARCHAR(20), @ctipo VARCHAR(10), @vserie VARCHAR(20), @vobservacion VARCHAR(500),
    @ccod_usuario VARCHAR(50), @ccod_coa VARCHAR(20), @ntotal DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON;
    DECLARE @nnumero INT;
    DECLARE @dfecha_dt DATETIME;
    
    -- Convertimos el formato dd/mm/yyyy a DATETIME
    BEGIN TRY
        SET @dfecha_dt = CONVERT(DATETIME, @dfecha, 103);
    END TRY
    BEGIN CATCH
        SET @dfecha_dt = GETDATE(); -- fallback si la conversión falla
    END CATCH

    -- Intentamos sacar el número de NumeradorAlmacen
    SELECT @nnumero = ISNULL(nnumero,1) FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctip_doc='I';
    
    IF @nnumero IS NULL
    BEGIN
        SELECT @nnumero = ISNULL(MAX(nnumero),0)+1 FROM CbInventario WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctipo=@ctipo AND vserie=@vserie;
    END
    ELSE
    BEGIN
        -- Actualizamos el NumeradorAlmacen
        UPDATE NumeradorAlmacen SET nnumero=@nnumero+1 WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctip_doc='I';
    END

    INSERT INTO CbInventario (ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES (@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha_dt,@ctipo,@vserie,@nnumero,@vobservacion,@ccod_usuario,@ccod_coa,@ntotal);
    
    SELECT SCOPE_IDENTITY() AS id_cbinve;
END
GO
PRINT '✓ webDatpos_insertarinventario (Ingresos) corregido (dfecha y numerador)';
GO

PRINT '═══════════════════════════════════════';
PRINT '  FIX 27 COMPLETO';
PRINT '═══════════════════════════════════════';
GO
