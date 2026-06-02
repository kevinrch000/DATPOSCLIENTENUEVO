-- ============================================================
-- MODIFY_910_FIX_63_ConsultaDocumento_FormasPago_SPs.sql
-- ============================================================
-- Estado : MODIFY (crea SPs faltantes referenciados por
--          api/consultadocumento_api.php)
-- Motivo : Las pantallas Consulta Documento (Ventas) y
--          Consulta Formas de Pago invocan SPs que nunca se
--          habian creado en la BD:
--              sp_consultasdocumentopricipal
--              sp_consultaformaspagop
--              sp_consultalistcobranzaid
--              sp_consultadatosdocref
--              sp_consultapdf
--              sp_cargarclientefacturar
--              sp_cargarcliente
--              sp_cargarlistausuario
--              sp_cargarnumeradorcobranza
--              sp_datosadicionales3
--          Sin estos SPs ambas pantallas devolvian listas
--          vacias y los modales (clientes, usuario, doc ref)
--          quedaban inoperantes.
-- Cambio : 1) Crear los 10 SPs con los parametros que el
--             adaptador AJAX (assets/Javascript/facturacion_adapter.js)
--             ya envia desde el JS legacy (.aspx).
--          2) El SP de Consulta Documento atiende los modos
--             TLista y TDetallado mediante @Opcion (la columna
--             "Detallado" en CbFactura se obtiene haciendo JOIN
--             con LnFactura).
--          3) El SP de Consulta Formas de Pago tambien atiende
--             TLista (agregado por documento) y TDetallado
--             (linea por linea de LnCobranza), con todas las
--             columnas requeridas por las DataTables de
--             ConsultaFormaPago.js (Efectivo, Tarjeta,
--             NotaCredito, NotaDebito, ntotal, etc.).
--          4) Los SPs aceptan fechas dd/MM/yyyy o yyyy-MM-dd
--             usando TRY_CONVERT con estilo 103/120 y caen al
--             string vacio si vienen NULL.
-- Orden  : Ejecutar despues de MODIFY_900.
-- ============================================================
USE DatPos_EMP01;
GO

PRINT '=== FIX 63: SPs Consulta Documento + Formas de Pago ===';
GO

/* ──────────────────────────────────────────────────────────
   1. sp_consultasdocumentopricipal
   Parametros provienen del payload JS:
       { consultadocumentos: [{ cdoc, cdoc_serie, cdoc_nro,
         ccod_coa, n_fchDesde, n_fchHasta, ccod_tienda,
         cusu_crea, cobs, cobser_variante, Opcion }] }
   Opcion:
       TLista     -> cabecera CbFactura
       TDetallado -> CbFactura JOIN LnFactura (linea por linea)
   Columnas (mismas en ambos modos para que el mapper PHP
   pueda emitir todas las llaves que la DataTable consume):
       [0] id_cbfact
       [1] cdoc
       [2] cdoc_serie
       [3] cdoc_nro
       [4] cusu_crea
       [5] ccoa_dsc
       [6] ctelf
       [7] cdsc_tienda
       [8] ntotal
       [9] dfch_doc
       [10] cstatus
       [11] cobs
       [12] ccod_articulo
       [13] cdsc_articulo
       [14] ncantidad
       [15] nprecio
       [16] ndescuento
       [17] nimpuesto
       [18] nimporte_bruto
       [19] nimporte_neto
       [20] cobser_variante
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultasdocumentopricipal','P') IS NOT NULL
    DROP PROCEDURE sp_consultasdocumentopricipal;
GO
CREATE PROCEDURE sp_consultasdocumentopricipal
    @ccod_cia        VARCHAR(20),
    @cdoc            VARCHAR(5)   = '',
    @cdoc_serie      VARCHAR(10)  = '',
    @cdoc_nro        VARCHAR(20)  = '',
    @ccod_coa        VARCHAR(20)  = '',
    @n_fchDesde      VARCHAR(20)  = '',
    @n_fchHasta      VARCHAR(20)  = '',
    @ccod_tienda     VARCHAR(20)  = '',
    @cusu_crea       VARCHAR(50)  = '',
    @cobs            VARCHAR(500) = '',
    @cobser_variante VARCHAR(200) = '',
    @Opcion          VARCHAR(20)  = 'TLista'
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @fDesde DATETIME =
        COALESCE(TRY_CONVERT(DATETIME, NULLIF(@n_fchDesde,''), 103),
                 TRY_CONVERT(DATETIME, NULLIF(@n_fchDesde,''), 120),
                 '19000101');
    DECLARE @fHasta DATETIME =
        DATEADD(DAY, 1,
            COALESCE(TRY_CONVERT(DATETIME, NULLIF(@n_fchHasta,''), 103),
                     TRY_CONVERT(DATETIME, NULLIF(@n_fchHasta,''), 120),
                     '99991230'));

    IF UPPER(ISNULL(@Opcion,'TLista')) = 'TDETALLADO'
    BEGIN
        SELECT
            F.id_cbfact                                  AS id_cbfact,
            ISNULL(F.cdoc,'')                            AS cdoc,
            ISNULL(F.cserie,'')                          AS cdoc_serie,
            ISNULL(CAST(F.nnumero AS VARCHAR(20)),'')    AS cdoc_nro,
            ISNULL(F.ccod_usuario,'')                    AS cusu_crea,
            ISNULL(C.cdsc_coa,'')                        AS ccoa_dsc,
            ISNULL(C.ctelf,'')                           AS ctelf,
            ISNULL(T.cnombr,'')                          AS cdsc_tienda,
            ISNULL(F.ntotal,0)                           AS ntotal,
            CONVERT(VARCHAR(10), F.fecha_emision, 103)   AS dfch_doc,
            ISNULL(F.cstatus,'')                         AS cstatus,
            ISNULL(F.cobs,'')                            AS cobs,
            ISNULL(L.id_articulo,'')                     AS ccod_articulo,
            ISNULL(L.cdsc_articulo,'')                   AS cdsc_articulo,
            ISNULL(L.ncantidad,0)                        AS ncantidad,
            ISNULL(L.nprecio,0)                          AS nprecio,
            ISNULL(L.ndescuento,0)                       AS ndescuento,
            ISNULL(L.nimpuesto,0)                        AS nimpuesto,
            ISNULL(L.nimporte_bruto,0)                   AS nimporte_bruto,
            ISNULL(L.nimporte_neto,0)                    AS nimporte_neto,
            ISNULL(L.cobser_variante,'')                 AS cobser_variante
        FROM CbFactura F
        INNER JOIN LnFactura L
            ON L.ccod_cia = F.ccod_cia AND L.id_cbfact = F.id_cbfact
        LEFT JOIN Coa C
            ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
        LEFT JOIN Tiendas T
            ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
        WHERE F.ccod_cia = @ccod_cia
          AND (@cdoc = '' OR F.cdoc = @cdoc)
          AND (@cdoc_serie = '' OR F.cserie = @cdoc_serie)
          AND (@cdoc_nro = '' OR F.nnumero = TRY_CAST(@cdoc_nro AS INT))
          AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
          AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
          AND (@cusu_crea = '' OR F.ccod_usuario = @cusu_crea)
          AND (@cobs = '' OR ISNULL(F.cobs,'') LIKE '%' + @cobs + '%')
          AND (@cobser_variante = '' OR ISNULL(L.cobser_variante,'') LIKE '%' + @cobser_variante + '%')
          AND F.fecha_emision >= @fDesde
          AND F.fecha_emision <  @fHasta
        ORDER BY F.fecha_emision DESC, F.id_cbfact, L.corden;
    END
    ELSE
    BEGIN
        SELECT
            F.id_cbfact                                  AS id_cbfact,
            ISNULL(F.cdoc,'')                            AS cdoc,
            ISNULL(F.cserie,'')                          AS cdoc_serie,
            ISNULL(CAST(F.nnumero AS VARCHAR(20)),'')    AS cdoc_nro,
            ISNULL(F.ccod_usuario,'')                    AS cusu_crea,
            ISNULL(C.cdsc_coa,'')                        AS ccoa_dsc,
            ISNULL(C.ctelf,'')                           AS ctelf,
            ISNULL(T.cnombr,'')                          AS cdsc_tienda,
            ISNULL(F.ntotal,0)                           AS ntotal,
            CONVERT(VARCHAR(10), F.fecha_emision, 103)   AS dfch_doc,
            ISNULL(F.cstatus,'')                         AS cstatus,
            ISNULL(F.cobs,'')                            AS cobs,
            ''                                           AS ccod_articulo,
            ''                                           AS cdsc_articulo,
            CAST(0 AS DECIMAL(18,4))                     AS ncantidad,
            CAST(0 AS DECIMAL(18,4))                     AS nprecio,
            CAST(0 AS DECIMAL(18,4))                     AS ndescuento,
            CAST(0 AS DECIMAL(18,4))                     AS nimpuesto,
            CAST(0 AS DECIMAL(18,4))                     AS nimporte_bruto,
            CAST(0 AS DECIMAL(18,4))                     AS nimporte_neto,
            ''                                           AS cobser_variante
        FROM CbFactura F
        LEFT JOIN Coa C
            ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
        LEFT JOIN Tiendas T
            ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
        WHERE F.ccod_cia = @ccod_cia
          AND (@cdoc = '' OR F.cdoc = @cdoc)
          AND (@cdoc_serie = '' OR F.cserie = @cdoc_serie)
          AND (@cdoc_nro = '' OR F.nnumero = TRY_CAST(@cdoc_nro AS INT))
          AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
          AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
          AND (@cusu_crea = '' OR F.ccod_usuario = @cusu_crea)
          AND (@cobs = '' OR ISNULL(F.cobs,'') LIKE '%' + @cobs + '%')
          AND F.fecha_emision >= @fDesde
          AND F.fecha_emision <  @fHasta
        ORDER BY F.fecha_emision DESC, F.id_cbfact;
    END
END
GO
PRINT '  -> sp_consultasdocumentopricipal OK';
GO

/* ──────────────────────────────────────────────────────────
   2. sp_consultaformaspagop
   Parametros provienen del payload JS:
       { FormaPago: [{ cnom_tarje, ccod_coa, cdoc, cdoc_serie,
         cdoc_nro, ccod_caja, fchDesde, fchHasta, cusu_crea,
         Opcion }] }
   Opcion:
       TLista     -> cabecera CbCobranza agregada por documento
                     con totales por tipo de cobro
       TDetallado -> LnCobranza (linea por linea, una por
                     tarjeta/efectivo/NC)
   Columnas (igual en ambos modos):
       [0]  id_cbfact
       [1]  id_cbcajac
       [2]  cdoc
       [3]  cdoc_serie
       [4]  cdoc_nro
       [5]  cusu_crea
       [6]  cdsc_usuario
       [7]  ccod_coa
       [8]  cdsc_coa
       [9]  Efectivo
       [10] Tarjeta
       [11] NotaCredito
       [12] NotaDebito
       [13] ntotal
       [14] nvuelto
       [15] dfch_crea
       [16] cnom_tarje
       [17] nmonto
       [18] DocRef
   El JS distingue las "tarjetas" leyendo `cnom_tarje`:
       Efectivo cuando LnCobranza.cnom_tarje IN ('','EFECTIVO')
                o id_cbfactNC IS NULL y nmonto > 0
       NotaCredito cuando id_cbfactNC IS NOT NULL
                   o cnom_tarje LIKE 'NC%' / 'NOTA CREDITO%'
       NotaDebito cuando cnom_tarje LIKE 'ND%' / 'NOTA DEBITO%'
       Tarjeta en cualquier otro caso (Visa, Mastercard, etc.)
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultaformaspagop','P') IS NOT NULL
    DROP PROCEDURE sp_consultaformaspagop;
GO
CREATE PROCEDURE sp_consultaformaspagop
    @ccod_cia    VARCHAR(20),
    @cnom_tarje  VARCHAR(100) = '',
    @ccod_coa    VARCHAR(20)  = '',
    @cdoc        VARCHAR(5)   = '',
    @cdoc_serie  VARCHAR(10)  = '',
    @cdoc_nro    VARCHAR(20)  = '',
    @ccod_caja   VARCHAR(20)  = '',
    @fchDesde    VARCHAR(20)  = '',
    @fchHasta    VARCHAR(20)  = '',
    @cusu_crea   VARCHAR(50)  = '',
    @Opcion      VARCHAR(20)  = 'TLista'
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @fDesde DATETIME =
        COALESCE(TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 103),
                 TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 120),
                 '19000101');
    DECLARE @fHasta DATETIME =
        DATEADD(DAY, 1,
            COALESCE(TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 103),
                     TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 120),
                     '99991230'));

    ;WITH PagosFiltrados AS (
        SELECT
            CC.id_cbcajac,
            CC.id_cbfact,
            CC.ccod_cia,
            CC.ccod_usuario,
            CC.ntotal           AS cbc_ntotal,
            CC.ntot_entreg      AS cbc_ntot_entreg,
            CC.nvuelto          AS cbc_nvuelto,
            CC.dfch_crea        AS cbc_dfch_crea,
            CC.ccod_caja        AS cbc_ccod_caja,
            F.cdoc, F.cserie, F.nnumero,
            F.ccod_coa,
            F.fecha_emision,
            ISNULL(CO.cdsc_coa,'')           AS cdsc_coa,
            ISNULL(U.cdsc_usuario,'')        AS cdsc_usuario,
            LC.id_lncajac,
            LC.nmonto                         AS lc_nmonto,
            ISNULL(LC.cnom_tarje,'EFECTIVO')  AS lc_cnom_tarje,
            ISNULL(LC.cnum_opera,'')          AS lc_cnum_opera,
            ISNULL(LC.cnum_tarje,'')          AS lc_cnum_tarje,
            LC.id_cbfactNC
        FROM CbCobranza CC
        INNER JOIN CbFactura F
            ON F.ccod_cia = CC.ccod_cia AND F.id_cbfact = CC.id_cbfact
        LEFT JOIN Coa CO
            ON CO.ccod_cia = F.ccod_cia AND CO.ccod_coa = F.ccod_coa
        LEFT JOIN Usuarios U
            ON U.ccod_empresa = F.ccod_cia AND U.ccod_usuario = F.ccod_usuario
        LEFT JOIN LnCobranza LC
            ON LC.ccod_cia = CC.ccod_cia AND LC.id_cbcajac = CC.id_cbcajac
        WHERE CC.ccod_cia = @ccod_cia
          AND (@ccod_caja = '' OR CC.ccod_caja = @ccod_caja)
          AND (@cdoc = '' OR F.cdoc = @cdoc)
          AND (@cdoc_serie = '' OR F.cserie = @cdoc_serie)
          AND (@cdoc_nro = '' OR F.nnumero = TRY_CAST(@cdoc_nro AS INT))
          AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
          AND (@cusu_crea = '' OR F.ccod_usuario = @cusu_crea)
          AND (@cnom_tarje = '' OR UPPER(ISNULL(LC.cnom_tarje,'EFECTIVO')) LIKE UPPER('%' + @cnom_tarje + '%'))
          AND CC.dfch_crea >= @fDesde
          AND CC.dfch_crea <  @fHasta
    ),
    PagosTipo AS (
        SELECT
            *,
            CASE
                WHEN id_cbfactNC IS NOT NULL THEN 'NC'
                WHEN UPPER(lc_cnom_tarje) LIKE 'NC%' OR UPPER(lc_cnom_tarje) LIKE 'NOTA CR%' THEN 'NC'
                WHEN UPPER(lc_cnom_tarje) LIKE 'ND%' OR UPPER(lc_cnom_tarje) LIKE 'NOTA DEB%' THEN 'ND'
                WHEN UPPER(lc_cnom_tarje) IN ('', 'EFECTIVO', 'CASH') THEN 'EFE'
                ELSE 'TAR'
            END AS tipo_pago
        FROM PagosFiltrados
    )
    SELECT * INTO #Pagos FROM PagosTipo;

    IF UPPER(ISNULL(@Opcion,'TLista')) = 'TDETALLADO'
    BEGIN
        SELECT
            id_cbfact                                                              AS id_cbfact,
            id_cbcajac                                                             AS id_cbcajac,
            ISNULL(cdoc,'')                                                        AS cdoc,
            ISNULL(cserie,'')                                                      AS cdoc_serie,
            ISNULL(CAST(nnumero AS VARCHAR(20)),'')                                AS cdoc_nro,
            ISNULL(ccod_usuario,'')                                                AS cusu_crea,
            ISNULL(cdsc_usuario,'')                                                AS cdsc_usuario,
            ISNULL(ccod_coa,'')                                                    AS ccod_coa,
            ISNULL(cdsc_coa,'')                                                    AS cdsc_coa,
            CAST(CASE WHEN tipo_pago = 'EFE' THEN lc_nmonto ELSE 0 END AS DECIMAL(18,4))   AS Efectivo,
            CAST(CASE WHEN tipo_pago = 'TAR' THEN lc_nmonto ELSE 0 END AS DECIMAL(18,4))   AS Tarjeta,
            CAST(CASE WHEN tipo_pago = 'NC'  THEN lc_nmonto ELSE 0 END AS DECIMAL(18,4))   AS NotaCredito,
            CAST(CASE WHEN tipo_pago = 'ND'  THEN lc_nmonto ELSE 0 END AS DECIMAL(18,4))   AS NotaDebito,
            ISNULL(lc_nmonto,0)                                                    AS ntotal,
            ISNULL(cbc_nvuelto,0)                                                  AS nvuelto,
            CONVERT(VARCHAR(10), cbc_dfch_crea, 103)                               AS dfch_crea,
            ISNULL(lc_cnom_tarje,'EFECTIVO')                                       AS cnom_tarje,
            ISNULL(lc_nmonto,0)                                                    AS nmonto,
            ISNULL(lc_cnum_opera,'')                                               AS DocRef
        FROM #Pagos
        ORDER BY cbc_dfch_crea DESC, id_cbcajac, id_lncajac;
    END
    ELSE
    BEGIN
        SELECT
            id_cbfact                                                              AS id_cbfact,
            MAX(id_cbcajac)                                                        AS id_cbcajac,
            ISNULL(MAX(cdoc),'')                                                   AS cdoc,
            ISNULL(MAX(cserie),'')                                                 AS cdoc_serie,
            ISNULL(MAX(CAST(nnumero AS VARCHAR(20))),'')                           AS cdoc_nro,
            ISNULL(MAX(ccod_usuario),'')                                           AS cusu_crea,
            ISNULL(MAX(cdsc_usuario),'')                                           AS cdsc_usuario,
            ISNULL(MAX(ccod_coa),'')                                               AS ccod_coa,
            ISNULL(MAX(cdsc_coa),'')                                               AS cdsc_coa,
            ISNULL(SUM(CASE WHEN tipo_pago = 'EFE' THEN lc_nmonto ELSE 0 END),0)   AS Efectivo,
            ISNULL(SUM(CASE WHEN tipo_pago = 'TAR' THEN lc_nmonto ELSE 0 END),0)   AS Tarjeta,
            ISNULL(SUM(CASE WHEN tipo_pago = 'NC'  THEN lc_nmonto ELSE 0 END),0)   AS NotaCredito,
            ISNULL(SUM(CASE WHEN tipo_pago = 'ND'  THEN lc_nmonto ELSE 0 END),0)   AS NotaDebito,
            ISNULL(MAX(cbc_ntotal),0)                                              AS ntotal,
            ISNULL(MAX(cbc_nvuelto),0)                                             AS nvuelto,
            CONVERT(VARCHAR(10), MAX(cbc_dfch_crea), 103)                          AS dfch_crea,
            ''                                                                     AS cnom_tarje,
            ISNULL(MAX(cbc_ntotal),0)                                              AS nmonto,
            ''                                                                     AS DocRef
        FROM #Pagos
        GROUP BY id_cbfact
        ORDER BY MAX(cbc_dfch_crea) DESC, id_cbfact;
    END

    DROP TABLE #Pagos;
END
GO
PRINT '  -> sp_consultaformaspagop OK';
GO

/* ──────────────────────────────────────────────────────────
   3. sp_consultalistcobranzaid
   Llamado al hacer click en una fila de Consulta Formas de
   Pago (modo TLista). Recibe el id_cbcajac de la cabecera
   y devuelve cada LnCobranza con su tarjeta/monto/operacion.
   Columnas:
       [0] cnom_tarje
       [1] nmonto
       [2] cnum_tarje
       [3] cnum_opera
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultalistcobranzaid','P') IS NOT NULL
    DROP PROCEDURE sp_consultalistcobranzaid;
GO
CREATE PROCEDURE sp_consultalistcobranzaid
    @ccod_cia   VARCHAR(20),
    @id_cbcajac VARCHAR(20) = NULL,
    @id_cbcobr  VARCHAR(20) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id INT = TRY_CAST(COALESCE(NULLIF(@id_cbcajac,''), NULLIF(@id_cbcobr,'')) AS INT);
    SELECT
        ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje,
        ISNULL(LC.nmonto,0)              AS nmonto,
        ISNULL(LC.cnum_tarje,'')         AS cnum_tarje,
        ISNULL(LC.cnum_opera,'')         AS cnum_opera
    FROM LnCobranza LC
    WHERE LC.ccod_cia = @ccod_cia
      AND LC.id_cbcajac = @id;
END
GO
PRINT '  -> sp_consultalistcobranzaid OK';
GO

/* ──────────────────────────────────────────────────────────
   4. sp_consultadatosdocref
   Datos del documento para impresion / referencia (ArmarHtml
   en Consulta Documento). El JS solo lee response.d[2],
   response.d[3], response.d[11], response.d[27..33]. Para
   evitar romper indices se devuelven 34 columnas alineadas:
       [0] id_cbfact      [17] cdsc_alm
       [1] ccod_coa       [18] -
       [2] dfch_doc       [19] -
       [3] ntotal         [20] -
       [4] -              [21] -
       [5] -              [22] -
       [6] -              [23] -
       [7] -              [24] -
       [8] -              [25] -
       [9] -              [26] -
       [10] -             [27] cdoc_coa  (RUC/DNI)
       [11] cdsc_coa      [28] cdirc_coa
       [12] -             [29] nsubtotal
       [13] -             [30] nimpuesto
       [14] -             [31] cserie
       [15] -             [32] nnumero
       [16] cdsc_tienda   [33] cdoc
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultadatosdocref','P') IS NOT NULL
    DROP PROCEDURE sp_consultadatosdocref;
GO
CREATE PROCEDURE sp_consultadatosdocref
    @ccod_cia  VARCHAR(20),
    @id_cbfact VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id INT = TRY_CAST(@id_cbfact AS INT);
    SELECT
        F.id_cbfact                                  AS [0],
        ISNULL(F.ccod_coa,'')                        AS [1],
        CONVERT(VARCHAR(10), F.fecha_emision, 103)   AS [2],
        ISNULL(F.ntotal,0)                           AS [3],
        ''                                           AS [4],
        ''                                           AS [5],
        ''                                           AS [6],
        ''                                           AS [7],
        ''                                           AS [8],
        ''                                           AS [9],
        ''                                           AS [10],
        ISNULL(C.cdsc_coa,'')                        AS [11],
        ''                                           AS [12],
        ''                                           AS [13],
        ''                                           AS [14],
        ''                                           AS [15],
        ISNULL(T.cnombr,'')                          AS [16],
        ISNULL(A.cdsc_alm,'')                        AS [17],
        ''                                           AS [18],
        ''                                           AS [19],
        ''                                           AS [20],
        ''                                           AS [21],
        ''                                           AS [22],
        ''                                           AS [23],
        ''                                           AS [24],
        ''                                           AS [25],
        ''                                           AS [26],
        ISNULL(C.cdoc_coa,'')                        AS [27],
        ISNULL(C.cdirc_coa,'')                       AS [28],
        ISNULL(F.nsubtotal,0)                        AS [29],
        ISNULL(F.nimpuesto,0)                        AS [30],
        ISNULL(F.cserie,'')                          AS [31],
        ISNULL(CAST(F.nnumero AS VARCHAR(20)),'')    AS [32],
        ISNULL(F.cdoc,'')                            AS [33]
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
    LEFT JOIN Tiendas T
        ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
    LEFT JOIN Almacenes A
        ON A.ccod_cia = F.ccod_cia AND A.ccod_alm = F.ccod_almacen
    WHERE F.ccod_cia = @ccod_cia AND F.id_cbfact = @id;
END
GO
PRINT '  -> sp_consultadatosdocref OK';
GO

/* ──────────────────────────────────────────────────────────
   5. sp_consultapdf
   Devuelve el PDF guardado en CbFactura.pdf como blob.
   La API hace base64_encode del primer campo.
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultapdf','P') IS NOT NULL
    DROP PROCEDURE sp_consultapdf;
GO
CREATE PROCEDURE sp_consultapdf
    @ccod_cia  VARCHAR(20),
    @id_cbfact VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id INT = TRY_CAST(@id_cbfact AS INT);
    SELECT TOP 1 ISNULL(pdf, CONVERT(VARBINARY(MAX),'')) AS pdf
    FROM CbFactura
    WHERE ccod_cia = @ccod_cia AND id_cbfact = @id;
END
GO
PRINT '  -> sp_consultapdf OK';
GO

/* ──────────────────────────────────────────────────────────
   6. sp_cargarclientefacturar
   Lista de Coa para el modal "Cliente" en Consulta Documento.
   El mapper PHP usa [0]=id_coa, [1]=cdsc_coa, [2]=cdoc_coa.
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargarclientefacturar','P') IS NOT NULL
    DROP PROCEDURE sp_cargarclientefacturar;
GO
CREATE PROCEDURE sp_cargarclientefacturar
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ccod_coa                AS id_coa,
        ISNULL(cdsc_coa,'')     AS cdsc_coa,
        ISNULL(cdoc_coa,'')     AS cdoc_coa
    FROM Coa
    WHERE ccod_cia = @ccod_cia
      AND ISNULL(cstatus,'A') <> 'X'
    ORDER BY cdsc_coa;
END
GO
PRINT '  -> sp_cargarclientefacturar OK';
GO

/* ──────────────────────────────────────────────────────────
   7. sp_cargarcliente
   Catch-all que usan varias pantallas (ConsultaDocumento,
   ConsultaVenta, etc.) — el mapper PHP usa [0]=ccod_coa,
   [1]=cdsc_coa.
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargarcliente','P') IS NOT NULL
    DROP PROCEDURE sp_cargarcliente;
GO
CREATE PROCEDURE sp_cargarcliente
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ccod_coa            AS ccod_coa,
        ISNULL(cdsc_coa,'') AS cdsc_coa
    FROM Coa
    WHERE ccod_cia = @ccod_cia
      AND ISNULL(cstatus,'A') <> 'X'
    ORDER BY cdsc_coa;
END
GO
PRINT '  -> sp_cargarcliente OK';
GO

/* ──────────────────────────────────────────────────────────
   8. sp_cargarlistausuario
   Catch-all para el modal "Usuario". Mapper PHP usa
   [0]=ccod_usuario, [1]=cdsc_usuario.
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargarlistausuario','P') IS NOT NULL
    DROP PROCEDURE sp_cargarlistausuario;
GO
CREATE PROCEDURE sp_cargarlistausuario
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        ccod_usuario                                  AS ccod_usuario,
        ISNULL(cdsc_usuario, ccod_usuario)            AS cdsc_usuario
    FROM Usuarios
    WHERE ccod_empresa = @ccod_cia
    ORDER BY cdsc_usuario;
END
GO
PRINT '  -> sp_cargarlistausuario OK';
GO

/* ──────────────────────────────────────────────────────────
   9. sp_cargarnumeradorcobranza
   Llena el combo "Cod Doc" en Consulta Formas de Pago con
   los tipos de documento que la empresa puede cobrar (BV,
   FV/FA, NV, NC, ND, ...). El JS espera value=cdoc_tipo y
   text=cdsc_numer. El mapper PHP devuelve
   id_cbnumerador=[0], cdsc_numer=[1] — para mantener
   compatibilidad mando cdoc_tipo en la posicion [0].
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargarnumeradorcobranza','P') IS NOT NULL
    DROP PROCEDURE sp_cargarnumeradorcobranza;
GO
CREATE PROCEDURE sp_cargarnumeradorcobranza
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT DISTINCT
        cdoc_tipo                                                            AS cdoc_tipo,
        ISNULL(cdsc_numer, cdoc_tipo)                                        AS cdsc_numer
    FROM NumeradorCaja
    WHERE ccod_cia = @ccod_cia
      AND cdoc_tipo IS NOT NULL
      AND cdoc_tipo <> ''
    ORDER BY cdoc_tipo;
END
GO
PRINT '  -> sp_cargarnumeradorcobranza OK';
GO

/* ──────────────────────────────────────────────────────────
  10. sp_datosadicionales3
   Llamado desde Consulta Documento (modo TDetallado) para
   obtener los pagos de la cobranza de un documento. El
   mapper PHP usa [0]=cnom_tarje, [1]=nmonto, [2]=cnum_tarje.
   El JS solo lee response.d.ntotal cuando llega un objeto,
   asi que la API tambien acepta arrays para sumar.
────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_datosadicionales3','P') IS NOT NULL
    DROP PROCEDURE sp_datosadicionales3;
GO
CREATE PROCEDURE sp_datosadicionales3
    @ccod_cia  VARCHAR(20),
    @id_cbfact VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @id INT = TRY_CAST(@id_cbfact AS INT);
    SELECT
        ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje,
        ISNULL(LC.nmonto,0)              AS nmonto,
        ISNULL(LC.cnum_tarje,'')         AS cnum_tarje
    FROM LnCobranza LC
    WHERE LC.ccod_cia = @ccod_cia
      AND LC.id_cbfact = @id
    ORDER BY LC.id_lncajac;
END
GO
PRINT '  -> sp_datosadicionales3 OK';
GO

PRINT '=== FIX 63 aplicado. SPs Consulta Documento + Formas de Pago disponibles. ===';
GO

/* Verificacion final */
SELECT name AS sp_creado
FROM sys.procedures
WHERE name IN (
    'sp_consultasdocumentopricipal',
    'sp_consultaformaspagop',
    'sp_consultalistcobranzaid',
    'sp_consultadatosdocref',
    'sp_consultapdf',
    'sp_cargarclientefacturar',
    'sp_cargarcliente',
    'sp_cargarlistausuario',
    'sp_cargarnumeradorcobranza',
    'sp_datosadicionales3'
)
ORDER BY name;
GO
