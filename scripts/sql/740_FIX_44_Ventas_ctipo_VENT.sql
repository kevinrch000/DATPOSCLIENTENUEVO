/* =====================================================================
   FIX 44 — sp_insertarmovimientocabeceranew: ctipo 'VT' → 'VENT'
   =====================================================================
   PROBLEMA:
   - sp_insertarmovimientocabeceranew usa ctipo='VT' al insertar en CbInventario.
   - FK_CbInve_TipoOper: CbInventario.ctipo → TipoOperacion.ccod_tipoper
   - TipoOperacion sólo tiene 'VENT' para ventas (script 140_13_EMP01_ConsultasVenta_SeedData).
   - Resultado: INSERT falla con "Instrucción INSERT en conflicto con la restricción
     FOREIGN KEY 'FK_CbInve_TipoOper'".

   TAMBIÉN:
   - webDatpos_ReporteKardexArticulos usa ctipo IN ('S','VT','GS') para salidas.
   - Con el cambio a 'VENT', el kardex no mostraría ventas → se corrige a 'VENT'.

   SOLUCIÓN:
   - Recrear sp_insertarmovimientocabeceranew con ctipo='VENT' (2 ocurrencias).
   - Recrear webDatpos_ReporteKardexArticulos con ctipo IN (...,'VENT',...).
   ===================================================================== */

USE DatPos_EMP01;
GO

PRINT '=== FIX 44: ctipo VT -> VENT en sp_insertarmovimientocabeceranew + Kardex ===';
GO

/* ── 1. Recrear sp_insertarmovimientocabeceranew ─────────────────────── */
IF OBJECT_ID('sp_insertarmovimientocabeceranew','P') IS NOT NULL
    DROP PROCEDURE sp_insertarmovimientocabeceranew;
GO
CREATE PROCEDURE sp_insertarmovimientocabeceranew
    @ccod_cia VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),
    @ccod_almacen VARCHAR(20),@cdoc VARCHAR(5),@ccod_coa VARCHAR(20),@nimpuesto DECIMAL(18,4),
    @nisc DECIMAL(18,4),@ndescuento DECIMAL(18,4),@ntotal DECIMAL(18,4),@nsubtotal DECIMAL(18,4),
    @nvuelto DECIMAL(18,4),@ntot_entreg DECIMAL(18,4),@cantidad_bienes INT,@id_turno INT,
    @costo DECIMAL(18,4),@cobs VARCHAR(500),
    @id_cbfact INT OUTPUT,@id_cbinve INT OUTPUT,@fecha_emision NVARCHAR(16) OUTPUT,@documento NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @cserie VARCHAR(10),@nnumero INT;
    SELECT TOP 1 @cserie=cdoc_serie,@nnumero=cdoc_nro
    FROM NumeradorCaja
    WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc;
    SET @nnumero=ISNULL(@nnumero,0)+1;

    INSERT INTO CbFactura(ccod_cia,ccod_tiend,ccod_caja,ccod_almacen,ccod_usuario,
        cdoc,cserie,nnumero,ccod_coa,nimpuesto,nisc,ndescuento,ntotal,nsubtotal,
        nvuelto,ntot_entreg,cantidad_bienes,id_turno,costo,cobs,fecha_emision)
    VALUES(@ccod_cia,@ccod_tiend,@ccod_caja,@ccod_almacen,@ccod_usuario,
        @cdoc,@cserie,@nnumero,@ccod_coa,@nimpuesto,@nisc,@ndescuento,@ntotal,@nsubtotal,
        @nvuelto,@ntot_entreg,@cantidad_bienes,@id_turno,@costo,@cobs,GETDATE());
    SET @id_cbfact=SCOPE_IDENTITY();

    DECLARE @nnumInve INT;
    -- CORREGIDO: 'VT' → 'VENT'  (FK_CbInve_TipoOper requiere valor en TipoOperacion)
    SELECT @nnumInve=ISNULL(MAX(nnumero),0)+1
    FROM CbInventario
    WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_almacen AND ctipo='VENT';

    INSERT INTO CbInventario(ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES(@ccod_cia,@ccod_tiend,@ccod_almacen,GETDATE(),'VENT',@cserie,@nnumInve,'Venta generada',@ccod_usuario,@ccod_coa,@costo);
    SET @id_cbinve=SCOPE_IDENTITY();

    UPDATE CbFactura SET id_cbinve=@id_cbinve WHERE id_cbfact=@id_cbfact;
    UPDATE NumeradorCaja SET cdoc_nro=@nnumero WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc;
    SET @fecha_emision=CONVERT(NVARCHAR,GETDATE(),120);
    SET @documento=ISNULL(@cserie,'')+'-'+RIGHT('00000000'+CAST(@nnumero AS VARCHAR),8);
END
GO
PRINT '  -> sp_insertarmovimientocabeceranew recreado con ctipo=VENT.';
GO

/* ── 2. Recrear webDatpos_ReporteKardexArticulos ─────────────────────── */
IF OBJECT_ID('webDatpos_ReporteKardexArticulos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ReporteKardexArticulos;
GO
CREATE PROCEDURE webDatpos_ReporteKardexArticulos
    @ccod_articulo VARCHAR(50),@ccod_alm VARCHAR(20),
    @fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        C.dfecha, C.ctipo, C.vserie, C.nnumero,
        L.ncantidad, L.ncosto,
        -- CORREGIDO: 'VT' → 'VENT'
        CASE WHEN C.ctipo IN ('I','GI','DV')        THEN L.ncantidad ELSE 0 END AS entrada,
        CASE WHEN C.ctipo IN ('S','VENT','GS','VT') THEN L.ncantidad ELSE 0 END AS salida
    FROM LnInventario L
    INNER JOIN CbInventario C ON C.id_cbinve=L.id_cbinve AND C.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia
      AND L.ccod_articulo=@ccod_articulo
      AND (L.ccod_alm=@ccod_alm OR L.ccod_alm_ingreso=@ccod_alm)
      AND C.dfecha BETWEEN @fchDesde AND @fchHasta
    ORDER BY C.dfecha;
END
GO
PRINT '  -> webDatpos_ReporteKardexArticulos recreado con ctipo VENT (+ VT legacy).';
GO

PRINT '=== FIX 44 OK ===';
GO
