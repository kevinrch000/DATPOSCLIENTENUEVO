-- ============================================================
-- MODIFY_860_FIX_58_GenerarNotaCreditoDevolucion_Completo.sql
-- ============================================================
-- Estado : MODIFY  (reemplaza webDatpos_generarNotaCreditoDevolucion
--          previo que sólo marcaba la CbFactura origen como 'A'
--          y devolvía 'OK' sin generar la NC real).
-- Motivo : El flujo Nota Crédito por Devolución no estaba
--          creando un documento NC propio: numdoc='' y
--          credito='0' en la UI; sin LnFactura nueva; numerador
--          NC nunca incrementaba. Esto impedía pasar la prueba
--          real de NC.
-- Cambio : Reescribir el SP para que:
--           1. Lea el correlativo de NC del NumeradorCaja del
--              usuario (cdoc_tipo='NC', cdoc_serie='NC01').
--           2. Calcule subtotal/IGV/total de los artículos
--              seleccionados (1.18 inclusivo) sumando precio*cant.
--           3. INSERTE una nueva fila en CbFactura con cdoc='NC',
--              cserie del numerador, nnumero del numerador,
--              ccod_coa heredado de la factura original.
--           4. INSERTE las LnFactura correspondientes copiando
--              los precios e impuestos de la línea original.
--           5. Incremente NumeradorCaja para NC.
--           6. Devuelva un set de columnas que la UI espera:
--              [id_cbfact_orig, 'OK', numdoc, credito, id_nc].
-- Orden  : Ejecutar después de NEW_851. Requiere que existan
--          NumeradorCaja seeds para NC (810_FIX_52 los siembra).
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_generarNotaCreditoDevolucion','P') IS NOT NULL
    DROP PROCEDURE webDatpos_generarNotaCreditoDevolucion;
GO
CREATE PROCEDURE webDatpos_generarNotaCreditoDevolucion
    @ccod_cia     VARCHAR(20),
    @id_cbfact    INT,
    @motivo       VARCHAR(500),
    @ccod_usuario VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;
    SET XACT_ABORT ON;

    DECLARE @ccod_caja     VARCHAR(20),
            @ccod_tiend    VARCHAR(20),
            @ccod_almacen  VARCHAR(20),
            @ccod_coa      VARCHAR(20),
            @id_turno      INT;

    SELECT
        @ccod_caja    = F.ccod_caja,
        @ccod_tiend   = F.ccod_tiend,
        @ccod_almacen = F.ccod_almacen,
        @ccod_coa     = F.ccod_coa,
        @id_turno     = F.id_turno
    FROM CbFactura F
    WHERE F.id_cbfact = @id_cbfact
      AND F.ccod_cia  = @ccod_cia;

    IF @ccod_caja IS NULL
    BEGIN
        SELECT CAST(@id_cbfact AS VARCHAR(20)) AS id_orig,
               'ERR_NO_FACT' AS estado, '' AS numdoc,
               '0' AS credito, '' AS id_nc;
        RETURN;
    END

    DECLARE @cserie_nc VARCHAR(10), @nnumero_nc INT;
    SELECT @cserie_nc = cdoc_serie, @nnumero_nc = cdoc_nro
    FROM NumeradorCaja
    WHERE ccod_cia = @ccod_cia AND ccod_caja = @ccod_caja
      AND cdoc_tipo = 'NC';

    IF @cserie_nc IS NULL
    BEGIN
        SELECT CAST(@id_cbfact AS VARCHAR(20)) AS id_orig,
               'ERR_NO_NUMER_NC' AS estado, '' AS numdoc,
               '0' AS credito, '' AS id_nc;
        RETURN;
    END

    DECLARE @nsubtotal DECIMAL(18,4) = 0,
            @nimpuesto DECIMAL(18,4) = 0,
            @ntotal    DECIMAL(18,4) = 0,
            @cantidad  INT           = 0;

    SELECT
        @ntotal    = ISNULL(SUM(L.nprecio * L.ncantidad), 0),
        @cantidad  = ISNULL(SUM(CAST(L.ncantidad AS INT)), 0)
    FROM LnFactura L
    WHERE L.ccod_cia  = @ccod_cia
      AND L.id_cbfact = @id_cbfact;

    -- IGV inclusivo 18%
    SET @nsubtotal = ROUND(@ntotal / 1.18, 4);
    SET @nimpuesto = ROUND(@ntotal - @nsubtotal, 4);

    BEGIN TRAN;

    INSERT INTO CbFactura
        (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
         cdoc, cserie, nnumero, ccod_coa,
         nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
         nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
         cobs, cstatus, cstatus_tributario, fecha_emision,
         id_cbinve, dfch_crea)
    VALUES
        (@ccod_cia, @ccod_tiend, @ccod_caja, @ccod_almacen, @ccod_usuario,
         'NC', @cserie_nc, @nnumero_nc, @ccod_coa,
         @nimpuesto, 0, 0, @ntotal, @nsubtotal,
         0, 0, @cantidad, @id_turno, 0,
         @motivo, 'P', 'P', GETDATE(),
         NULL, GETDATE());

    DECLARE @id_nc INT = SCOPE_IDENTITY();

    INSERT INTO LnFactura
        (ccod_cia, id_cbfact, ccod_tiend, id_articulo, cdsc_articulo,
         cdoc, nprecio, ncantidad,
         nimporte_bruto, nimpuesto, nisc, ndescuento, nimporte_neto,
         corden, ccod_usuario, id_cbinve, ccod_almacen,
         dfch_crea)
    SELECT
        L.ccod_cia, @id_nc, L.ccod_tiend, L.id_articulo, L.cdsc_articulo,
        'NC', L.nprecio, L.ncantidad,
        L.nimporte_bruto, L.nimpuesto, L.nisc, L.ndescuento, L.nimporte_neto,
        L.corden, @ccod_usuario, NULL, L.ccod_almacen,
        GETDATE()
    FROM LnFactura L
    WHERE L.ccod_cia  = @ccod_cia
      AND L.id_cbfact = @id_cbfact;

    UPDATE NumeradorCaja
    SET cdoc_nro = cdoc_nro + 1
    WHERE ccod_cia = @ccod_cia AND ccod_caja = @ccod_caja
      AND cdoc_tipo = 'NC';

    -- Marcamos la factura origen como Anulada para que no se
    -- pueda emitir NC duplicada (legacy comportamiento).
    UPDATE CbFactura
    SET cstatus = 'A', cobs = @motivo
    WHERE id_cbfact = @id_cbfact AND ccod_cia = @ccod_cia;

    COMMIT TRAN;

    DECLARE @numdoc VARCHAR(40) =
        @cserie_nc + '-' + RIGHT('00000000' + CAST(@nnumero_nc AS VARCHAR(8)), 8);

    SELECT
        CAST(@id_cbfact AS VARCHAR(20))                    AS id_orig,
        'OK'                                               AS estado,
        @numdoc                                            AS numdoc,
        CAST(@ntotal AS VARCHAR(20))                       AS credito,
        CAST(@id_nc AS VARCHAR(20))                        AS id_nc;
END
GO
PRINT 'MODIFY_860 aplicado: webDatpos_generarNotaCreditoDevolucion ahora genera NC real con LnFactura + numerador NC.';
