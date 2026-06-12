/* =====================================================================
   FIX 47 — Ventas / Cuentas pendientes
   Devuelve columnas en el orden que consume la capa PHP/JS para que
   Guardar/Obtener Cuenta muestre etiqueta, fecha y detalle correctos.
===================================================================== */
USE DatPos_EMP01;
GO

IF OBJECT_ID('sp_consultarcuentas','P') IS NOT NULL DROP PROCEDURE sp_consultarcuentas;
GO
CREATE PROCEDURE sp_consultarcuentas
    @ccod_cia VARCHAR(20),
    @ccod_tiend VARCHAR(20),
    @ccod_caja VARCHAR(20),
    @ctip_cuenta VARCHAR(5)
AS BEGIN
    SET NOCOUNT ON;

    SELECT
        CB.id_cbcuenta,
        CB.ccod_cia,
        CB.ccod_coa,
        CB.ccod_tiend,
        CB.ccod_caja,
        CB.etiqueta,
        CB.ccod_usuario,
        CB.ctip_cuenta,
        ISNULL(CB.ntot_desct, 0) AS ntot_desct,
        ISNULL(CB.ntot_impbruto, 0) AS ntot_impbruto,
        ISNULL(CB.ntot_igv, 0) AS ntot_igv,
        ISNULL(CB.ntot_impneto, 0) AS ntot_impneto,
        CB.cstatus,
        CONVERT(VARCHAR(19), CB.dfch_crea, 120) AS fechacreacion,
        ISNULL(C.cdsc_coa, '') AS cdsc_coa
    FROM CbCuenta CB
    LEFT JOIN Coa C ON C.ccod_coa = CB.ccod_coa AND C.ccod_cia = CB.ccod_cia
    WHERE CB.ccod_cia = @ccod_cia
      AND CB.ccod_tiend = @ccod_tiend
      AND CB.ccod_caja = @ccod_caja
      AND CB.ctip_cuenta = @ctip_cuenta
      AND CB.cstatus = 'A'
    ORDER BY CB.dfch_crea DESC, CB.id_cbcuenta DESC;
END
GO

IF OBJECT_ID('sp_consultarcuentadetalles','P') IS NOT NULL DROP PROCEDURE sp_consultarcuentadetalles;
GO
CREATE PROCEDURE sp_consultarcuentadetalles
    @id_cbcuenta INT
AS BEGIN
    SET NOCOUNT ON;

    SELECT
        L.id_lncuenta,
        L.ccod_cia,
        L.id_cbcuenta,
        L.ncantidad,
        L.nprecio,
        L.nimporte_neto,
        L.id_articulo,
        L.nimporte_bruto,
        L.nimpuesto,
        L.ndescuento,
        ISNULL(L.ctip_descn, '') AS ctip_descn,
        ISNULL(NULLIF(L.cobser_variante, ''), '-') AS cobser_variante,
        L.corden,
        L.ccod_usuario,
        ISNULL(L.ctip_desc, '') AS ctip_desc,
        L.nigv_uni,
        L.ncosto,
        L.id_variante,
        L.cdescn_max,
        CONVERT(VARCHAR(19), L.dfch_crea, 120) AS fechacreacion,
        ISNULL(A.cdsc_articulo, L.id_articulo) AS cdsc_articulo,
        ISNULL(A.ctip_articulo, 'S') AS ctip_articulo
    FROM LnCuenta L
    LEFT JOIN Articulos A ON A.ccod_articulo = L.id_articulo AND A.ccod_cia = L.ccod_cia
    WHERE L.id_cbcuenta = @id_cbcuenta
    ORDER BY L.corden, L.id_lncuenta;
END
GO

IF OBJECT_ID('sp_lsconsultarcuentadetalles','P') IS NOT NULL DROP PROCEDURE sp_lsconsultarcuentadetalles;
GO
CREATE PROCEDURE sp_lsconsultarcuentadetalles
    @id_cbcuenta INT
AS BEGIN
    SET NOCOUNT ON;

    EXEC sp_consultarcuentadetalles @id_cbcuenta = @id_cbcuenta;
END
GO

PRINT 'OK - FIX 47 Cuentas Facturacion aplicado.';
GO
