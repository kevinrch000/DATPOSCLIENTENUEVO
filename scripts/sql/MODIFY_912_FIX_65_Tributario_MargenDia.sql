/* MODIFY 912 / FIX 65
   ===================================================================
   Fix de ReporteTributario.php y ConsultaMargenUtilidadDia.php — los
   SPs existentes devolvian columnas que NO coincidian con las claves
   que el JS de cada pantalla espera leer, y/o no soportaban los
   filtros enviados por el JS (formato DD/MM/YYYY, ccod_tienda /
   ccod_caja, etc.).

   Este script recrea los siguientes SPs en DatPos_EMP01:

     1. webDatpos_ConsultaTributarioPrincipal
        - Mantiene los 9 parametros, pero ahora hace TRY_CONVERT de
          @fchDesde / @fchHasta para aceptar formato DD/MM/YYYY (103)
          o YYYY-MM-DD (120), tal como hace sp_consultasdocumentopricipal.
        - Reordena las columnas para que el API las mapee 1:1:
            [0] id_cbfact       (idDoc para los iconos PDF/XML/CDR)
            [1] cdsc_coa        (nombre del cliente — col. "Cliente")
            [2] cdoc            (col. "Documento")
            [3] cdoc_serie      (col. "Serie")
            [4] cdoc_nro        (col. "Correlativo")
            [5] ntotal          (col. "Importe Total")
            [6] dfch_doc        (col. "Fecha Emisión", DD/MM/YYYY)
            [7] cstatus_tributario (col. "Estado")

     2. webDatpos_DescargarArchivoPDF / XML / XMLCDR
        - Antes devolvian solo la columna binaria.
        - Ahora devuelven 4 columnas (binario, cdoc, cserie, nnumero)
          para que el API construya `obj[0].ipdf_datpos|contentxml|
          contentzipcdr` + `cdoc/cdoc_serie/cdoc_nro` que el JS lee
          para nombrar el archivo descargado.

     3. webDatpos_MargenUtilidadDiaPricipal
        - Recreado: agrupa CbFactura por (tienda, caja, fecha) para
          mostrar margen por dia, que es lo que la pantalla pide.
          Antes devolvia una fila por factura sin tienda/caja, asi
          que la tabla quedaba con campos vacios.
        - Devuelve las 9 columnas que el JS usa en DataTables:
          ccod_tienda, cdsc_tienda, ccod_caja, cdsc_caja, nprecio,
          ncosto, n_margenUtilidad, n_marUtiPorcenta, dfch_crea.
        - Acepta filtros @ccod_tienda, @ccod_caja, @fchDesde y
          @fchHasta (DD/MM/YYYY o ISO).

   Idempotente: cada CREATE va precedido de DROP IF OBJECT_ID.
   =================================================================== */

USE DatPos_EMP01;
GO

PRINT '== Recreando webDatpos_ConsultaTributarioPrincipal ==';

IF OBJECT_ID('webDatpos_ConsultaTributarioPrincipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaTributarioPrincipal;
GO

CREATE PROCEDURE webDatpos_ConsultaTributarioPrincipal
    @ccod_tienda        VARCHAR(20),
    @fchDesde           VARCHAR(20),
    @fchHasta           VARCHAR(20),
    @cdoc               VARCHAR(5),
    @cdoc_serie         VARCHAR(10),
    @cdoc_nro           VARCHAR(20),
    @ccod_coa           VARCHAR(20),
    @cstatus_tributario VARCHAR(5),
    @ccod_cia           VARCHAR(20)
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

    SELECT
        F.id_cbfact                                  AS id_cbfact,
        ISNULL(C.cdsc_coa,'')                        AS cdsc_coa,
        ISNULL(F.cdoc,'')                            AS cdoc,
        ISNULL(F.cserie,'')                          AS cdoc_serie,
        ISNULL(CAST(F.nnumero AS VARCHAR(20)),'')    AS cdoc_nro,
        ISNULL(F.ntotal,0)                           AS ntotal,
        CONVERT(VARCHAR(10), F.fecha_emision, 103)   AS dfch_doc,
        ISNULL(F.cstatus_tributario,'')              AS cstatus_tributario
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
    WHERE F.ccod_cia = @ccod_cia
      AND F.fecha_emision >= @fDesde
      AND F.fecha_emision <  @fHasta
      AND (@ccod_tienda = ''        OR F.ccod_tiend = @ccod_tienda)
      AND (@cdoc = ''               OR F.cdoc = @cdoc)
      AND (@cdoc_serie = ''         OR F.cserie = @cdoc_serie)
      AND (@cdoc_nro = ''           OR F.nnumero = TRY_CAST(@cdoc_nro AS INT))
      AND (@ccod_coa = ''           OR F.ccod_coa = @ccod_coa)
      AND (@cstatus_tributario = '' OR F.cstatus_tributario = @cstatus_tributario)
    ORDER BY F.fecha_emision DESC, F.id_cbfact;
END
GO
PRINT '  -> webDatpos_ConsultaTributarioPrincipal OK';

PRINT '== Recreando webDatpos_reporteTributarioPrincipal (alias) ==';
IF OBJECT_ID('webDatpos_reporteTributarioPrincipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_reporteTributarioPrincipal;
GO
CREATE PROCEDURE webDatpos_reporteTributarioPrincipal
    @ccod_tienda VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),
    @ccod_coa VARCHAR(20),@ccod_cia VARCHAR(20),@cstatus_tributario VARCHAR(5)
AS BEGIN
    SET NOCOUNT ON;
    EXEC webDatpos_ConsultaTributarioPrincipal
        @ccod_tienda, @fchDesde, @fchHasta, '', '', '', @ccod_coa,
        @cstatus_tributario, @ccod_cia;
END
GO
PRINT '  -> webDatpos_reporteTributarioPrincipal OK';

PRINT '== Recreando webDatpos_DescargarArchivoPDF / XML / XMLCDR ==';

IF OBJECT_ID('webDatpos_DescargarArchivoPDF','P') IS NOT NULL
    DROP PROCEDURE webDatpos_DescargarArchivoPDF;
GO
CREATE PROCEDURE webDatpos_DescargarArchivoPDF
    @id_cbfact INT, @ccod_cia VARCHAR(20)
AS BEGIN
    SET NOCOUNT ON;
    SELECT pdf,
           ISNULL(cdoc,''),
           ISNULL(cserie,''),
           ISNULL(CAST(nnumero AS VARCHAR(20)),'')
    FROM CbFactura
    WHERE id_cbfact = @id_cbfact AND ccod_cia = @ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_DescargarArchivoXML','P') IS NOT NULL
    DROP PROCEDURE webDatpos_DescargarArchivoXML;
GO
CREATE PROCEDURE webDatpos_DescargarArchivoXML
    @id_cbfact INT, @ccod_cia VARCHAR(20)
AS BEGIN
    SET NOCOUNT ON;
    SELECT xml,
           ISNULL(cdoc,''),
           ISNULL(cserie,''),
           ISNULL(CAST(nnumero AS VARCHAR(20)),'')
    FROM CbFactura
    WHERE id_cbfact = @id_cbfact AND ccod_cia = @ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_DescargarArchivoXMLCDR','P') IS NOT NULL
    DROP PROCEDURE webDatpos_DescargarArchivoXMLCDR;
GO
CREATE PROCEDURE webDatpos_DescargarArchivoXMLCDR
    @id_cbfact INT, @ccod_cia VARCHAR(20)
AS BEGIN
    SET NOCOUNT ON;
    SELECT xml_cdr,
           ISNULL(cdoc,''),
           ISNULL(cserie,''),
           ISNULL(CAST(nnumero AS VARCHAR(20)),'')
    FROM CbFactura
    WHERE id_cbfact = @id_cbfact AND ccod_cia = @ccod_cia;
END
GO
PRINT '  -> webDatpos_DescargarArchivoPDF / XML / XMLCDR OK';

PRINT '== Recreando webDatpos_MargenUtilidadDiaPricipal (agrupado) ==';

IF OBJECT_ID('webDatpos_MargenUtilidadDiaPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_MargenUtilidadDiaPricipal;
GO
CREATE PROCEDURE webDatpos_MargenUtilidadDiaPricipal
    @ccod_tienda VARCHAR(20),
    @ccod_caja   VARCHAR(20),
    @fchDesde    VARCHAR(20),
    @fchHasta    VARCHAR(20),
    @CodCia      VARCHAR(20)
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

    SELECT
        ISNULL(F.ccod_tiend, '')                              AS ccod_tienda,
        ISNULL(T.cnombr, '')                                  AS cdsc_tienda,
        ISNULL(F.ccod_caja, '')                               AS ccod_caja,
        ISNULL(CJ.cdsc_caja, '')                              AS cdsc_caja,
        CAST(SUM(F.ntotal)            AS DECIMAL(18,2))       AS nprecio,
        CAST(SUM(F.costo)             AS DECIMAL(18,2))       AS ncosto,
        CAST(SUM(F.ntotal - F.costo)  AS DECIMAL(18,2))       AS n_margenUtilidad,
        CASE WHEN SUM(F.ntotal) > 0
             THEN CAST(100.0 * SUM(F.ntotal - F.costo) / SUM(F.ntotal) AS DECIMAL(18,2))
             ELSE CAST(0 AS DECIMAL(18,2))
        END                                                   AS n_marUtiPorcenta,
        CONVERT(VARCHAR(10), CAST(F.fecha_emision AS DATE), 103) AS dfch_crea
    FROM CbFactura F
    LEFT JOIN Tiendas T
        ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
    LEFT JOIN Cajas CJ
        ON CJ.ccod_cia = F.ccod_cia AND CJ.ccod_caja = F.ccod_caja
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus <> 'A'
      AND F.fecha_emision >= @fDesde
      AND F.fecha_emision <  @fHasta
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_caja   = '' OR F.ccod_caja  = @ccod_caja)
    GROUP BY F.ccod_tiend, T.cnombr, F.ccod_caja, CJ.cdsc_caja,
             CAST(F.fecha_emision AS DATE)
    ORDER BY CAST(F.fecha_emision AS DATE) DESC,
             F.ccod_tiend, F.ccod_caja;
END
GO
PRINT '  -> webDatpos_MargenUtilidadDiaPricipal OK';

PRINT '== MODIFY 912 / FIX 65 completado. ==';
GO
