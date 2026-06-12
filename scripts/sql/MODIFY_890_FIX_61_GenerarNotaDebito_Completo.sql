-- ============================================================
-- MODIFY_890_FIX_61_GenerarNotaDebito_Completo.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : `webDatpos_generarNotaDebito` era stub: solo devolvía
--          'OK' sin generar nada. La UI NotaDebito.php quedaba
--          sin emitir ND real, sin numerador y sin asiento en
--          CbFactura/LnFactura.
-- Cambio : Implementación completa:
--          1) Lee NumeradorCaja para cdoc_tipo='ND'.
--          2) Calcula subTotal/IGV 1.18 inclusive
--             (total ÷ 1.18 = subtotal, total − subtotal = igv).
--          3) INSERTA CbFactura con cdoc='ND' y datos de la
--             factura original.
--          4) INSERTA LnFactura clonando los renglones de la
--             factura origen, con cantidades = 0 (la ND no afecta
--             stock — sólo importa el monto aplicado).
--          5) Incrementa NumeradorCaja.cdoc_nro.
--          6) Devuelve: ['OK', 'ND', cserie, nnumero, '',
--             dfch, dhra, id_nd] que es lo que espera NotaDebito.js.
-- Orden  : Ejecutar después de MODIFY_880.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_generarNotaDebito','P') IS NOT NULL
    DROP PROCEDURE webDatpos_generarNotaDebito;
GO
CREATE PROCEDURE webDatpos_generarNotaDebito
    @id_cbfact        INT,
    @ccod_usuario     VARCHAR(50),
    @nmonto_aplicado  DECIMAL(18,4),
    @ccod_cia         VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE
        @ccod_tiend   VARCHAR(20),
        @ccod_caja    VARCHAR(20),
        @ccod_almacen VARCHAR(20),
        @ccod_coa     VARCHAR(20),
        @id_turno     INT,
        @cantidad     DECIMAL(18,4),
        @cserie_nd    VARCHAR(10),
        @nnumero_nd   INT,
        @nsubtotal    DECIMAL(18,4),
        @nimpuesto    DECIMAL(18,4),
        @ntotal       DECIMAL(18,4),
        @id_nd        INT,
        @dfch         VARCHAR(10),
        @dhra         VARCHAR(8);

    SELECT
        @ccod_tiend   = ccod_tiend,
        @ccod_caja    = ccod_caja,
        @ccod_almacen = ccod_almacen,
        @ccod_coa     = ccod_coa,
        @id_turno     = id_turno,
        @cantidad     = cantidad_bienes
    FROM CbFactura
    WHERE ccod_cia = @ccod_cia AND id_cbfact = @id_cbfact;

    IF @ccod_caja IS NULL
    BEGIN
        SELECT 'IdCaja' AS Doc, '' AS cdoc, '' AS cdoc_serie, '' AS cdoc_nro,
               '' AS cmail, '' AS dfch_crea, '' AS dhra_crea, 0 AS id_cbfact;
        RETURN;
    END

    SELECT @cserie_nd = cdoc_serie, @nnumero_nd = ISNULL(cdoc_nro, 0)
    FROM NumeradorCaja
    WHERE ccod_cia = @ccod_cia AND ccod_caja = @ccod_caja AND cdoc_tipo = 'ND';

    IF @cserie_nd IS NULL OR @cserie_nd = ''
    BEGIN
        SELECT 'SerOperND' AS Doc, '' AS cdoc, '' AS cdoc_serie, '' AS cdoc_nro,
               '' AS cmail, '' AS dfch_crea, '' AS dhra_crea, 0 AS id_cbfact;
        RETURN;
    END

    SET @ntotal    = @nmonto_aplicado;
    SET @nsubtotal = ROUND(@ntotal / 1.18, 4);
    SET @nimpuesto = @ntotal - @nsubtotal;

    INSERT INTO CbFactura
        (ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
         cdoc, cserie, nnumero, ccod_coa,
         nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
         nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
         cobs, cstatus, cstatus_tributario, fecha_emision,
         id_cbinve, dfch_crea)
    VALUES
        (@ccod_cia, @ccod_tiend, @ccod_caja, @ccod_almacen, @ccod_usuario,
         'ND', @cserie_nd, @nnumero_nd, @ccod_coa,
         @nimpuesto, 0, 0, @ntotal, @nsubtotal,
         0, @ntotal, ISNULL(@cantidad, 0), @id_turno, 0,
         '', 'P', 'P', GETDATE(),
         NULL, GETDATE());

    SET @id_nd = SCOPE_IDENTITY();

    INSERT INTO LnFactura
        (ccod_cia, id_cbfact, id_articulo, cdsc_articulo, ncantidad,
         nprecio, nimporte_neto, nimpuesto, nisc, ndescuento,
         dfch_crea)
    SELECT
        L.ccod_cia, @id_nd, L.id_articulo, L.cdsc_articulo, 0,
        L.nprecio, 0, 0, 0, 0,
        GETDATE()
    FROM LnFactura L
    WHERE L.ccod_cia = @ccod_cia AND L.id_cbfact = @id_cbfact;

    UPDATE NumeradorCaja
       SET cdoc_nro = @nnumero_nd + 1
     WHERE ccod_cia = @ccod_cia AND ccod_caja = @ccod_caja AND cdoc_tipo = 'ND';

    SET @dfch = CONVERT(VARCHAR(10), GETDATE(), 103);
    SET @dhra = CONVERT(VARCHAR(8),  GETDATE(), 108);

    SELECT 'OK' AS Doc,
           'ND' AS cdoc,
           @cserie_nd AS cdoc_serie,
           CAST(@nnumero_nd AS VARCHAR(20)) AS cdoc_nro,
           '' AS cmail,
           @dfch AS dfch_crea,
           @dhra AS dhra_crea,
           @id_nd AS id_cbfact;
END
GO
PRINT 'MODIFY_890 aplicado: webDatpos_generarNotaDebito genera ND real con CbFactura/LnFactura/Numerador.';
