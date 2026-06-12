/* FIX 22E — 2 SPs faltantes finales — DatPos_EMP01 */
USE DatPos_EMP01; 
GO

/* sp_insertarinventario (legado, comentado en DAInventario pero cmd.CommandText referenciado) */
IF OBJECT_ID('sp_insertarinventario','P') IS NOT NULL DROP PROCEDURE sp_insertarinventario; 
GO
CREATE PROCEDURE sp_insertarinventario
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha DATETIME, @ctipo VARCHAR(10), @vserie VARCHAR(10),
    @nnumero INT, @vobservacion VARCHAR(500)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO CbInventario(ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion)
    VALUES(@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha,@ctipo,@vserie,@nnumero,@vobservacion);
    SELECT SCOPE_IDENTITY() AS id_cbinve;
END
GO

/* webDatpos_insertarInventarioSalida */
IF OBJECT_ID('webDatpos_insertarInventarioSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarInventarioSalida; 
GO
CREATE PROCEDURE webDatpos_insertarInventarioSalida
    @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20), @ccod_alm VARCHAR(20),
    @dfecha DATETIME, @ctipo VARCHAR(10), @vserie VARCHAR(10),
    @vobservacion VARCHAR(500), @ccod_usuario VARCHAR(50), @ntotal DECIMAL(18,4),
    @id_cbinve NVARCHAR(16) OUTPUT, @ErrorNumber NVARCHAR(16) OUTPUT, @ErrorMessage NVARCHAR(200) OUTPUT
AS BEGIN SET NOCOUNT ON;
  BEGIN TRY
    INSERT INTO CbInventario(ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,vobservacion,ccod_usuario,ntotal)
    VALUES(@ccod_cia,@ccod_tienda,@ccod_alm,@dfecha,@ctipo,@vserie,@vobservacion,@ccod_usuario,@ntotal);
    SET @id_cbinve = CAST(SCOPE_IDENTITY() AS NVARCHAR);
    SET @ErrorNumber = '0';
    SET @ErrorMessage = 'OK';
  END TRY
  BEGIN CATCH
    SET @id_cbinve = '0';
    SET @ErrorNumber = CAST(ERROR_NUMBER() AS NVARCHAR);
    SET @ErrorMessage = ERROR_MESSAGE();
  END CATCH
END
GO

PRINT '✓ FIX 22E: 2 SPs finales (insertarInventario). TODO COMPLETO.'; 
GO
