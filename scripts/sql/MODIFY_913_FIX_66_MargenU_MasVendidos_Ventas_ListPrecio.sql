/* ========================================================================
   MODIFY_913 / FIX_66
   Crea/recrea los SPs requeridos por las pantallas:
        - pages/Consultas/MargenUtilidad.php          (MargenUtilidad.js)
        - pages/Consultas/ConsultaArticulosMasVendidos.php
        - pages/Consultas/ConsultasVenta.php          (ConsultaVentas.js)
        - pages/Consultas/ConsultaListPrecio.php

   Causa raiz comun (igual que en FIX_63/64/65): los SPs que la API PHP
   invocaba o no existian o devolvian columnas en distinto orden / cantidad
   que la que las DataTable de cada JS esperan. Aqui los recreamos con
   nombres "webDatpos_*" y la API se actualiza para apuntar a estos.

   Todas las conversiones de fecha aceptan DD/MM/YYYY (estilo 103) y la
   variante ISO YYYY-MM-DD (estilo 120) usando TRY_CONVERT para que el
   parseo no falle en sesiones US English.
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 913 / FIX 66: SPs para Margen, MasVendidos, Ventas y ListPrecio ==';

/* ------------------------------------------------------------------------
   1) MargenUtilidad.php  →  Ejecutar
      JS: MargenUtilidad.aspx/MargenUtilidadPricipal
      Payload: { MargenUtilidad: [ { cdoc, cdoc_serie, cdoc_nro,
                                     n_fchDesde, n_fchHasta, ccoa_dsc } ] }
      Columnas que lee DataTables (orden importa):
        [0] cdoc           [1] cdoc_serie     [2] cdoc_nro
        [3] ccoa_dsc       [4] nprecio        [5] ncosto
        [6] n_margenUtilidad [7] n_marUtiPorcenta
        [8] n_docRef       [9] dfch_crea     [10] id_cbfact
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_MargenUtilidadPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_MargenUtilidadPricipal;
GO
CREATE PROCEDURE webDatpos_MargenUtilidadPricipal
    @cdoc       VARCHAR(10) = '',
    @cdoc_serie VARCHAR(20) = '',
    @cdoc_nro   VARCHAR(20) = '',
    @fchDesde   VARCHAR(30) = '',
    @fchHasta   VARCHAR(30) = '',
    @ccoa_dsc   VARCHAR(200) = '',
    @CodCia     VARCHAR(20) = 'EMP01'
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
        ISNULL(F.cdoc,'')                                     AS cdoc,
        ISNULL(F.cserie,'')                                   AS cdoc_serie,
        CAST(F.nnumero AS VARCHAR(20))                        AS cdoc_nro,
        ISNULL(C.cdsc_coa, ISNULL(F.ccod_coa,''))             AS ccoa_dsc,
        CONVERT(VARCHAR(20), ISNULL(F.ntotal,0))              AS nprecio,
        CONVERT(VARCHAR(20), ISNULL(F.costo,0))               AS ncosto,
        CONVERT(VARCHAR(20),
                ISNULL(F.ntotal,0) - ISNULL(F.costo,0))       AS n_margenUtilidad,
        CONVERT(VARCHAR(20),
            CASE WHEN ISNULL(F.ntotal,0) = 0 THEN 0
                 ELSE 100.0 *
                      (ISNULL(F.ntotal,0) - ISNULL(F.costo,0))
                      / NULLIF(F.ntotal,0)
            END)                                              AS n_marUtiPorcenta,
        ''                                                    AS n_docRef,
        CONVERT(VARCHAR(10), F.fecha_emision, 103)            AS dfch_crea,
        CAST(F.id_cbfact AS VARCHAR(20))                      AS id_cbfact
    FROM CbFactura F
    LEFT JOIN Coa C
      ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus <> 'A'
      AND F.fecha_emision >= @dDesde
      AND F.fecha_emision <  @dHasta
      AND (@cdoc = '' OR F.cdoc = @cdoc)
      AND (@cdoc_serie = '' OR F.cserie = @cdoc_serie)
      AND (@cdoc_nro = '' OR CAST(F.nnumero AS VARCHAR(20)) = @cdoc_nro)
      AND (@ccoa_dsc = '' OR F.ccod_coa LIKE '%' + @ccoa_dsc + '%' OR
           ISNULL(C.cdsc_coa,'') LIKE '%' + @ccoa_dsc + '%')
    ORDER BY F.fecha_emision DESC, F.id_cbfact DESC;
END
GO
PRINT '  -> webDatpos_MargenUtilidadPricipal creado.';

/* ------------------------------------------------------------------------
   2) MargenUtilidad.php  →  ModalBuscarDoc (cabecera)
      JS: MargenUtilidad.aspx/ConsultarMargenUtilidadArticuloDatos
      Payload: { cdoc, cdoc_serie, cdoc_nro }
      Columnas que lee el JS de obj[0]:
        ccod_tienda, cdsc_tienda, ccod_caja, cdsc_caja,
        cusu_crea, cdsc_usuario, ccod_coa, ccoa_dsc,
        n_tipoOper, n_serie, n_numero, ccod_alm, cdsc_alm
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultarMargenUtilidadArticuloDatos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultarMargenUtilidadArticuloDatos;
GO
CREATE PROCEDURE webDatpos_ConsultarMargenUtilidadArticuloDatos
    @cdoc       VARCHAR(10) = '',
    @cdoc_serie VARCHAR(20) = '',
    @cdoc_nro   VARCHAR(20) = '',
    @CodCia     VARCHAR(20) = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;
    SELECT TOP 1
        ISNULL(F.ccod_tiend,'')         AS ccod_tienda,
        ISNULL(T.cnombr,'')             AS cdsc_tienda,
        ISNULL(F.ccod_caja,'')          AS ccod_caja,
        ISNULL(CJ.cdsc_caja,'')         AS cdsc_caja,
        ISNULL(F.ccod_usuario,'')       AS cusu_crea,
        ISNULL(U.cdsc_usuario,'')       AS cdsc_usuario,
        ISNULL(F.ccod_coa,'')           AS ccod_coa,
        ISNULL(C.cdsc_coa,'')           AS ccoa_dsc,
        ISNULL(F.cdoc,'')               AS n_tipoOper,
        ISNULL(F.cserie,'')             AS n_serie,
        CAST(F.nnumero AS VARCHAR(20))  AS n_numero,
        ISNULL(F.ccod_almacen,'')       AS ccod_alm,
        ISNULL(A.cdsc_alm,'')           AS cdsc_alm
    FROM CbFactura F
    LEFT JOIN Tiendas T   ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
    LEFT JOIN Cajas   CJ  ON CJ.ccod_cia = F.ccod_cia AND CJ.ccod_caja = F.ccod_caja
    LEFT JOIN Usuarios U  ON U.ccod_empresa = F.ccod_cia AND U.ccod_usuario = F.ccod_usuario
    LEFT JOIN Coa     C   ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
    LEFT JOIN Almacenes A ON A.ccod_cia = F.ccod_cia AND A.ccod_alm = F.ccod_almacen
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc = @cdoc
      AND F.cserie = @cdoc_serie
      AND CAST(F.nnumero AS VARCHAR(20)) = @cdoc_nro;
END
GO
PRINT '  -> webDatpos_ConsultarMargenUtilidadArticuloDatos creado.';

/* ------------------------------------------------------------------------
   3) MargenUtilidad.php  →  ModalBuscarDoc (lineas)
      JS: MargenUtilidad.aspx/ConsultarMargenUtilidadArticulo
      Payload: { cdoc, cdoc_serie, cdoc_nro }
      Columnas: ccod_articulo, cdsc_articulo, ncantidad, nprecio,
                ncosto, n_margenUtilidad, n_marUtiPorcenta
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultarMargenUtilidadArticulo','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultarMargenUtilidadArticulo;
GO
CREATE PROCEDURE webDatpos_ConsultarMargenUtilidadArticulo
    @cdoc       VARCHAR(10) = '',
    @cdoc_serie VARCHAR(20) = '',
    @cdoc_nro   VARCHAR(20) = '',
    @CodCia     VARCHAR(20) = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ISNULL(L.id_articulo,'')                        AS ccod_articulo,
        ISNULL(L.cdsc_articulo,'')                      AS cdsc_articulo,
        CONVERT(VARCHAR(20), ISNULL(L.ncantidad,0))     AS ncantidad,
        CONVERT(VARCHAR(20), ISNULL(L.nprecio,0))       AS nprecio,
        CONVERT(VARCHAR(20), ISNULL(L.ncosto,0))        AS ncosto,
        CONVERT(VARCHAR(20),
                ISNULL(L.nprecio,0) - ISNULL(L.ncosto,0)) AS n_margenUtilidad,
        CONVERT(VARCHAR(20),
            CASE WHEN ISNULL(L.nprecio,0) = 0 THEN 0
                 ELSE 100.0 *
                      (ISNULL(L.nprecio,0) - ISNULL(L.ncosto,0))
                      / NULLIF(L.nprecio,0)
            END)                                        AS n_marUtiPorcenta
    FROM LnFactura L
    JOIN CbFactura F
      ON F.ccod_cia = L.ccod_cia AND F.id_cbfact = L.id_cbfact
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc = @cdoc
      AND F.cserie = @cdoc_serie
      AND CAST(F.nnumero AS VARCHAR(20)) = @cdoc_nro
    ORDER BY L.id_lnfact;
END
GO
PRINT '  -> webDatpos_ConsultarMargenUtilidadArticulo creado.';

/* ------------------------------------------------------------------------
   4) ConsultaArticulosMasVendidos.php  →  Ejecutar
      JS: ConsultaArticulosMasVendidos.aspx/ConsultaArticulosMasVendidos
      Payload: { ArticulosMasVendidos: [ { ccod_articulo, ccod_tienda,
                                          n_fchDesde, n_fchHasta,
                                          ccod_lin } ] }
      Columnas: ccod_caja, cdsc_caja, ccod_lin, ccod_articulo,
                cdsc_articulo, ncantidad
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultaArticulosMasVendidos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaArticulosMasVendidos;
GO
CREATE PROCEDURE webDatpos_ConsultaArticulosMasVendidos
    @ccod_tienda   VARCHAR(20) = '',
    @ccod_articulo VARCHAR(50) = '',
    @ccod_lin      VARCHAR(20) = '',
    @fchDesde      VARCHAR(30) = '',
    @fchHasta      VARCHAR(30) = '',
    @CodCia        VARCHAR(20) = 'EMP01'
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
        ISNULL(F.ccod_caja,'')                        AS ccod_caja,
        ISNULL(CJ.cdsc_caja,'')                       AS cdsc_caja,
        ISNULL(A.ccod_lin,'')                         AS ccod_lin,
        ISNULL(L.id_articulo,'')                      AS ccod_articulo,
        ISNULL(L.cdsc_articulo,'')                    AS cdsc_articulo,
        CONVERT(VARCHAR(20), SUM(ISNULL(L.ncantidad,0))) AS ncantidad
    FROM LnFactura L
    JOIN CbFactura F
      ON F.ccod_cia = L.ccod_cia AND F.id_cbfact = L.id_cbfact
    LEFT JOIN Articulos A
      ON A.ccod_cia = L.ccod_cia AND A.ccod_articulo = L.id_articulo
    LEFT JOIN Cajas CJ
      ON CJ.ccod_cia = F.ccod_cia AND CJ.ccod_caja = F.ccod_caja
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus <> 'A'
      AND F.fecha_emision >= @dDesde
      AND F.fecha_emision <  @dHasta
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_articulo = '' OR L.id_articulo = @ccod_articulo)
      AND (@ccod_lin = '' OR ISNULL(A.ccod_lin,'') = @ccod_lin)
    GROUP BY F.ccod_caja, CJ.cdsc_caja, A.ccod_lin,
             L.id_articulo, L.cdsc_articulo
    ORDER BY SUM(ISNULL(L.ncantidad,0)) DESC;
END
GO
PRINT '  -> webDatpos_ConsultaArticulosMasVendidos creado.';

/* ------------------------------------------------------------------------
   5) ConsultaListPrecio.php  →  Ejecutar
      JS: ConsultaListPrecio.aspx/ConsultaListPrecioPricipal
      Payload: { articulo: [ { ccod_cblistpre, ccod_articulo, cdsc_articulo,
                              ccod_lin, ccod_unidadmedida } ] }
      Columnas: ccod_cblistpre, cdsc_cblistpre, ccod_articulo,
                cdsc_articulo, cdsc_lin, csim_unidadmedida, npre_uni
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_ConsultaListPrecioPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaListPrecioPricipal;
GO
CREATE PROCEDURE webDatpos_ConsultaListPrecioPricipal
    @ccod_cblistpre    VARCHAR(20)  = '',
    @ccod_articulo     VARCHAR(50)  = '',
    @cdsc_articulo     VARCHAR(200) = '',
    @ccod_lin          VARCHAR(20)  = '',
    @ccod_unidadmedida VARCHAR(10)  = '',
    @CodCia            VARCHAR(20)  = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ISNULL(L.ccod_cblistpre,'')             AS ccod_cblistpre,
        ISNULL(CB.cdsc_cblistpre,'')            AS cdsc_cblistpre,
        ISNULL(L.ccod_articulo,'')              AS ccod_articulo,
        ISNULL(A.cdsc_articulo,'')              AS cdsc_articulo,
        ISNULL(FAM.cdsc_lin,'')                 AS cdsc_lin,
        ISNULL(UM.cdsc_unimed, ISNULL(A.uni_medi,'')) AS csim_unidadmedida,
        CONVERT(VARCHAR(20), ISNULL(L.npre_uni,0)) AS npre_uni
    FROM LnListaPrecio L
    JOIN CbListaPrecio CB
      ON CB.ccod_cia = L.ccod_cia AND CB.ccod_cblistpre = L.ccod_cblistpre
    LEFT JOIN Articulos A
      ON A.ccod_cia = L.ccod_cia AND A.ccod_articulo = L.ccod_articulo
    LEFT JOIN Familias FAM
      ON FAM.ccod_cia = A.ccod_cia AND FAM.ccod_lin = A.ccod_lin
    LEFT JOIN UnidadMedida UM
      ON UM.ccod_cia = A.ccod_cia AND UM.ccod_unimed = A.uni_medi
    WHERE L.ccod_cia = @CodCia
      AND (@ccod_cblistpre = '' OR L.ccod_cblistpre = @ccod_cblistpre)
      AND (@ccod_articulo = '' OR L.ccod_articulo = @ccod_articulo)
      AND (@cdsc_articulo = '' OR ISNULL(A.cdsc_articulo,'')
           LIKE '%' + @cdsc_articulo + '%')
      AND (@ccod_lin = '' OR ISNULL(A.ccod_lin,'') = @ccod_lin)
      AND (@ccod_unidadmedida = '' OR ISNULL(A.uni_medi,'') = @ccod_unidadmedida)
    ORDER BY L.ccod_cblistpre, L.ccod_articulo;
END
GO
PRINT '  -> webDatpos_ConsultaListPrecioPricipal creado.';

/* ------------------------------------------------------------------------
   6) ConsultasVenta.php  →  Ejecutar
      JS: ConsultasVenta.aspx/ConsultasVentaPricipal
      Payload: { ConsultaArticulo: [ { ccod_articulo, ccod_tienda,
                                       ccod_coa, n_fchDesde, n_fchHasta,
                                       cobser_variante } ] }
      Columnas (orden importante; ultima es icono "cstatus"):
        ccod_coa (=> nombre del cliente, igual que en ConsultaTributario)
        ccod_articulo, cdsc_articulo, ncantidad, nprecio,
        nimpuesto, nisc, ndescuento, nimporte_neto, dfch_doc,
        cobser_variante, cstatus
      Ademas la API agrega id_cbfact para que ModalBuscarDoc lo encuentre.
   ------------------------------------------------------------------------ */
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
        CAST(F.id_cbfact AS VARCHAR(20))                  AS id_cbfact,
        ISNULL(C.cdsc_coa, ISNULL(F.ccod_coa,''))         AS ccod_coa,
        ISNULL(L.id_articulo,'')                          AS ccod_articulo,
        ISNULL(L.cdsc_articulo,'')                        AS cdsc_articulo,
        CONVERT(VARCHAR(20), ISNULL(L.ncantidad,0))       AS ncantidad,
        CONVERT(VARCHAR(20), ISNULL(L.nprecio,0))         AS nprecio,
        CONVERT(VARCHAR(20), ISNULL(L.nimpuesto,0))       AS nimpuesto,
        CONVERT(VARCHAR(20), ISNULL(L.nisc,0))            AS nisc,
        CONVERT(VARCHAR(20), ISNULL(L.ndescuento,0))      AS ndescuento,
        CONVERT(VARCHAR(20), ISNULL(L.nimporte_neto,0))   AS nimporte_neto,
        CONVERT(VARCHAR(10), F.fecha_emision, 103)        AS dfch_doc,
        ISNULL(L.cobser_variante,'')                      AS cobser_variante
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
PRINT '  -> webDatpos_ConsultasVentaPricipal creado.';

/* ------------------------------------------------------------------------
   7) ConsultasVenta.php  →  ModalBuscarDoc (detalle por id_cbfact)
      JS: ConsultasVenta.aspx/ConsultaListArticulos
      Payload: { id_fact: "<id_cbfact>" }
      Columnas: ccod_articulo, cdsc_articulo, ncantidad, nprecio,
                nimpuesto, nisc, ndescuento, nimporte_neto
   ------------------------------------------------------------------------ */
IF OBJECT_ID('webDatpos_consultaListArticulos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_consultaListArticulos;
GO
CREATE PROCEDURE webDatpos_consultaListArticulos
    @id_cbfact INT          = 0,
    @cdoc      VARCHAR(10)  = '',
    @cdoc_serie VARCHAR(20) = '',
    @cdoc_nro  VARCHAR(20)  = '',
    @CodCia    VARCHAR(20)  = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ISNULL(L.id_articulo,'')                            AS ccod_articulo,
        ISNULL(L.cdsc_articulo,'')                          AS cdsc_articulo,
        CONVERT(VARCHAR(20), ISNULL(L.ncantidad,0))         AS ncantidad,
        CONVERT(VARCHAR(20), ISNULL(L.nprecio,0))           AS nprecio,
        CONVERT(VARCHAR(20), ISNULL(L.nimpuesto,0))         AS nimpuesto,
        CONVERT(VARCHAR(20), ISNULL(L.nisc,0))              AS nisc,
        CONVERT(VARCHAR(20), ISNULL(L.ndescuento,0))        AS ndescuento,
        CONVERT(VARCHAR(20), ISNULL(L.nimporte_neto,0))     AS nimporte_neto
    FROM LnFactura L
    JOIN CbFactura F
      ON F.ccod_cia = L.ccod_cia AND F.id_cbfact = L.id_cbfact
    WHERE L.ccod_cia = @CodCia
      AND (@id_cbfact > 0 AND L.id_cbfact = @id_cbfact
           OR @id_cbfact <= 0 AND F.cdoc = @cdoc AND F.cserie = @cdoc_serie
              AND CAST(F.nnumero AS VARCHAR(20)) = @cdoc_nro)
    ORDER BY L.id_lnfact;
END
GO
PRINT '  -> webDatpos_consultaListArticulos creado.';

PRINT '== MODIFY 913 / FIX 66 completado. ==';
GO
