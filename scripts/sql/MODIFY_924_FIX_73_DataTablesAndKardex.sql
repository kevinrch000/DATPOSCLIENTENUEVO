/* ====================================================================
   MODIFY_924 / FIX 73 - Sprint 2 Prioridad 3: DataTables + Kardex
   ====================================================================
   Modulo  : Home / Ventas / Kardex
   Fecha   : 2026-05-23
   Bugs    :
     BUG 1.2: Home -> Ventas por Articulo. DataTables warning:
              "Requested unknown parameter 'cdoc_seri' for row 0".
              webDatpos_ConsultasVentaPricipal no devuelve el doc-ref
              compuesto (cdoc + cserie + nnumero). Se agrega.
     BUG 1.3: Home -> Kardex. webDatpos_ConsultaKardex devolvia 11
              columnas crudas (id_cbinve, dfecha, ctipo, vserie,
              nnumero, ncantidad, ncosto, entrada, salida, ccod_alm,
              ccod_alm_ingreso) pero el JS espera 12 columnas
              (DocRef, FchDoc, cdsc_articulo, Entrada{Cantidad,Costo,
              Total}, Salida{...}, Saldo{...}) con saldo acumulado.
     BUG 2.20: Kardex.php llamaba a sp_kardexprincipal (no existia).
              Se crea con agregacion mensual.

   Cambios:
     1. webDatpos_ConsultasVentaPricipal: agrega 13a columna
        cdoc_seri (cdoc + ' ' + cserie + '-' + nnumero).
     2. webDatpos_ConsultaKardex: reescrito para devolver las 12
        columnas que espera el dashboard, con saldo acumulado
        calculado via SUM() OVER (ORDER BY fecha).
     3. sp_kardexprincipal: nuevo. Agregacion por
        (tienda, almacen, articulo, anio, mes) con saldo inicial,
        ingresos, salidas y saldo final.

   Ejecutar en: DatPos_EMP01
==================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 924 / FIX 73: DataTables + Kardex ==';

/* ------------------------------------------------------------------ */
/* 1) webDatpos_ConsultasVentaPricipal - agregar cdoc_seri (BUG 1.2)   */
/* ------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultasVentaPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultasVentaPricipal;
GO
CREATE PROCEDURE webDatpos_ConsultasVentaPricipal
    @ccod_tienda      VARCHAR(20)  = '',
    @ccod_coa         VARCHAR(20)  = '',
    @ccod_articulo    VARCHAR(50)  = '',
    @cobser_variante  VARCHAR(200) = '',
    @fchDesde         VARCHAR(30)  = '',
    @fchHasta         VARCHAR(30)  = '',
    @CodCia           VARCHAR(20)  = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @dDesde DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 120),
        '19000101');
    DECLARE @dHasta DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 120),
        '99991231');
    SET @dHasta = DATEADD(DAY, 1, @dHasta);

    SELECT
        CAST(F.id_cbfact AS VARCHAR(20))                  AS id_cbfact,        -- [0]
        ISNULL(C.cdsc_coa, ISNULL(F.ccod_coa,''))         AS ccod_coa,         -- [1]
        ISNULL(L.id_articulo,'')                          AS ccod_articulo,    -- [2]
        ISNULL(L.cdsc_articulo,'')                        AS cdsc_articulo,    -- [3]
        CONVERT(VARCHAR(20), ISNULL(L.ncantidad,0))       AS ncantidad,        -- [4]
        CONVERT(VARCHAR(20), ISNULL(L.nprecio,0))         AS nprecio,          -- [5]
        CONVERT(VARCHAR(20), ISNULL(L.nimpuesto,0))       AS nimpuesto,        -- [6]
        CONVERT(VARCHAR(20), ISNULL(L.nisc,0))            AS nisc,             -- [7]
        CONVERT(VARCHAR(20), ISNULL(L.ndescuento,0))      AS ndescuento,       -- [8]
        CONVERT(VARCHAR(20), ISNULL(L.nimporte_neto,0))   AS nimporte_neto,    -- [9]
        CONVERT(VARCHAR(10), F.fecha_emision, 103)        AS dfch_doc,         -- [10]
        ISNULL(L.cobser_variante,'')                      AS cobser_variante,  -- [11]
        /* cdoc_seri = "FV F001-12345" (formato doc-ref) */
        ISNULL(F.cdoc,'')
          + CASE WHEN ISNULL(F.cserie,'') <> ''
                 THEN ' ' + F.cserie ELSE '' END
          + CASE WHEN ISNULL(F.nnumero,0) <> 0
                 THEN '-' + CAST(F.nnumero AS VARCHAR(20)) ELSE '' END
                                                          AS cdoc_seri         -- [12]
    FROM LnFactura L
    JOIN CbFactura F
      ON F.ccod_cia = L.ccod_cia AND F.id_cbfact = L.id_cbfact
    LEFT JOIN Coa C
      ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus <> 'A'
      AND F.fecha_emision >= @dDesde
      AND F.fecha_emision <  @dHasta
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa    = '' OR F.ccod_coa   = @ccod_coa)
      AND (@ccod_articulo = '' OR L.id_articulo = @ccod_articulo)
      AND (@cobser_variante = '' OR ISNULL(L.cobser_variante,'')
           LIKE '%' + @cobser_variante + '%')
    ORDER BY F.fecha_emision DESC, F.id_cbfact DESC, L.id_lnfact;
END
GO
PRINT '  -> webDatpos_ConsultasVentaPricipal (con cdoc_seri) recreado.';

/* ------------------------------------------------------------------ */
/* 2) webDatpos_ConsultaKardex - 12 columnas con saldo acumulado       */
/*    Usado por Home dashboard (BUG 1.3).                              */
/*    Columnas (orden importante; el JS lee por nombre):              */
/*      [0]  DocRef          ctipo + ' ' + vserie + '-' + nnumero    */
/*      [1]  FchDoc          dd/mm/yyyy                              */
/*      [2]  cdsc_articulo                                            */
/*      [3]  EntradaCantidad                                         */
/*      [4]  EntradaCosto                                            */
/*      [5]  EntradaTotal    (Cantidad * Costo si I/GI sino 0)       */
/*      [6]  SalidaCantidad                                          */
/*      [7]  SalidaCosto                                             */
/*      [8]  SalidaTotal                                             */
/*      [9]  SaldoCantidad   (SUM OVER de Entrada-Salida)             */
/*      [10] SaldoCosto      (ncosto)                                 */
/*      [11] SaldoTotal      (SaldoCantidad * ncosto)                 */
/* ------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultaKardex','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaKardex;
GO
CREATE PROCEDURE webDatpos_ConsultaKardex
    @ccod_cia       VARCHAR(20),
    @ccod_articulo  VARCHAR(50) = '',
    @ccod_alm       VARCHAR(20) = '',
    @fchDesde       VARCHAR(30) = '',
    @fchHasta       VARCHAR(30) = ''
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @dDesde DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 120),
        '19000101');
    DECLARE @dHasta DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 120),
        '99991231');
    SET @dHasta = DATEADD(DAY, 1, @dHasta);

    ;WITH movimientos AS (
        SELECT
            CB.id_cbinve,
            CB.dfecha,
            CB.ctipo,
            ISNULL(CB.vserie,'')                              AS vserie,
            ISNULL(CAST(CB.nnumero AS VARCHAR(20)), '')       AS nnumero,
            ISNULL(A.cdsc_articulo, L.cdsc_articulo)          AS cdsc_articulo,
            L.ccod_articulo,
            L.ncantidad,
            L.ncosto,
            CASE WHEN CB.ctipo IN ('I','GI') THEN L.ncantidad ELSE 0 END AS cantEnt,
            CASE WHEN CB.ctipo IN ('S','GS') THEN L.ncantidad ELSE 0 END AS cantSal
        FROM LnInventario L
        INNER JOIN CbInventario CB
            ON CB.id_cbinve = L.id_cbinve AND CB.ccod_cia = L.ccod_cia
        LEFT JOIN Articulos A
            ON A.ccod_cia = L.ccod_cia AND A.ccod_articulo = L.ccod_articulo
        WHERE L.ccod_cia = @ccod_cia
          AND (@ccod_articulo = '' OR L.ccod_articulo = @ccod_articulo)
          AND (@ccod_alm = '' OR L.ccod_alm = @ccod_alm OR L.ccod_alm_ingreso = @ccod_alm)
          AND CB.dfecha >= @dDesde
          AND CB.dfecha <  @dHasta
    )
    SELECT
        ISNULL(ctipo,'')
          + CASE WHEN vserie <> '' THEN ' ' + vserie ELSE '' END
          + CASE WHEN nnumero <> '' THEN '-' + nnumero ELSE '' END
                                                              AS DocRef,
        CONVERT(VARCHAR(10), dfecha, 103)                     AS FchDoc,
        ISNULL(cdsc_articulo,'')                              AS cdsc_articulo,
        CONVERT(VARCHAR(20), cantEnt)                         AS EntradaCantidad,
        CONVERT(VARCHAR(20), ncosto)                          AS EntradaCosto,
        CONVERT(VARCHAR(20), cantEnt * ncosto)                AS EntradaTotal,
        CONVERT(VARCHAR(20), cantSal)                         AS SalidaCantidad,
        CONVERT(VARCHAR(20), ncosto)                          AS SalidaCosto,
        CONVERT(VARCHAR(20), cantSal * ncosto)                AS SalidaTotal,
        CONVERT(VARCHAR(20),
            SUM(cantEnt - cantSal)
            OVER (PARTITION BY ccod_articulo
                  ORDER BY dfecha, id_cbinve
                  ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)
        )                                                     AS SaldoCantidad,
        CONVERT(VARCHAR(20), ncosto)                          AS SaldoCosto,
        CONVERT(VARCHAR(20),
            SUM(cantEnt - cantSal)
            OVER (PARTITION BY ccod_articulo
                  ORDER BY dfecha, id_cbinve
                  ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW)
            * ncosto
        )                                                     AS SaldoTotal
    FROM movimientos
    ORDER BY ccod_articulo, dfecha, id_cbinve;
END
GO
PRINT '  -> webDatpos_ConsultaKardex (12 cols + saldo acumulado) recreado.';

/* ------------------------------------------------------------------ */
/* 3) sp_kardexprincipal - agregacion mensual para Kardex.php          */
/*    JS espera: ccod_tienda, ccod_alm, ccod_articulo, cdsc_articulo,  */
/*               n_anio, n_mes, n_cantInicial, n_cantIngreso,          */
/*               n_cantSalisa, n_saldo                                 */
/* ------------------------------------------------------------------ */
IF OBJECT_ID('sp_kardexprincipal','P') IS NOT NULL
    DROP PROCEDURE sp_kardexprincipal;
GO
CREATE PROCEDURE sp_kardexprincipal
    @ccod_cia      VARCHAR(20),
    @fchDesde      VARCHAR(30) = '',
    @fchHasta      VARCHAR(30) = '',
    @id_articulo   VARCHAR(50) = '',
    @ccod_alm      VARCHAR(20) = ''
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @dDesde DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 120),
        '19000101');
    DECLARE @dHasta DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 120),
        '99991231');
    SET @dHasta = DATEADD(DAY, 1, @dHasta);

    ;WITH movs AS (
        SELECT
            CB.ccod_tienda,
            L.ccod_alm,
            L.ccod_articulo,
            ISNULL(A.cdsc_articulo, L.cdsc_articulo) AS cdsc_articulo,
            YEAR(CB.dfecha)  AS n_anio,
            MONTH(CB.dfecha) AS n_mes,
            CB.dfecha,
            CASE WHEN CB.ctipo IN ('I','GI') THEN L.ncantidad ELSE 0 END AS cantEnt,
            CASE WHEN CB.ctipo IN ('S','GS') THEN L.ncantidad ELSE 0 END AS cantSal
        FROM LnInventario L
        INNER JOIN CbInventario CB
            ON CB.id_cbinve = L.id_cbinve AND CB.ccod_cia = L.ccod_cia
        LEFT JOIN Articulos A
            ON A.ccod_cia = L.ccod_cia AND A.ccod_articulo = L.ccod_articulo
        WHERE L.ccod_cia = @ccod_cia
          AND (@id_articulo = '' OR L.ccod_articulo = @id_articulo)
          AND (@ccod_alm = '' OR L.ccod_alm = @ccod_alm)
          AND CB.dfecha >= @dDesde
          AND CB.dfecha <  @dHasta
    ), agregado AS (
        SELECT
            ISNULL(ccod_tienda,'')   AS ccod_tienda,
            ISNULL(ccod_alm,'')      AS ccod_alm,
            ccod_articulo,
            cdsc_articulo,
            n_anio,
            n_mes,
            SUM(cantEnt) AS n_cantIngreso,
            SUM(cantSal) AS n_cantSalisa
        FROM movs
        GROUP BY ccod_tienda, ccod_alm, ccod_articulo, cdsc_articulo, n_anio, n_mes
    )
    SELECT
        ccod_tienda,
        ccod_alm,
        ccod_articulo,
        cdsc_articulo,
        CAST(n_anio AS VARCHAR(4))            AS n_anio,
        CAST(n_mes AS VARCHAR(2))             AS n_mes,
        CONVERT(VARCHAR(20),
            ISNULL(SUM(n_cantIngreso - n_cantSalisa) OVER (
                PARTITION BY ccod_articulo, ccod_alm
                ORDER BY n_anio, n_mes
                ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING), 0))
                                              AS n_cantInicial,
        CONVERT(VARCHAR(20), n_cantIngreso)   AS n_cantIngreso,
        CONVERT(VARCHAR(20), n_cantSalisa)    AS n_cantSalisa,
        CONVERT(VARCHAR(20),
            SUM(n_cantIngreso - n_cantSalisa) OVER (
                PARTITION BY ccod_articulo, ccod_alm
                ORDER BY n_anio, n_mes
                ROWS BETWEEN UNBOUNDED PRECEDING AND CURRENT ROW))
                                              AS n_saldo
    FROM agregado
    ORDER BY ccod_articulo, ccod_alm, n_anio, n_mes;
END
GO
PRINT '  -> sp_kardexprincipal (mensual con saldo acumulado) creado.';

PRINT '== MODIFY 924 finalizado ==';
