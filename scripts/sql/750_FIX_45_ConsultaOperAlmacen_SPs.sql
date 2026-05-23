/* =====================================================================
   FIX 45 – SPs para Consulta Operaciones de Almacén
   Corrige:
     1. sp_consultaoperalmacenpricipal  – con todos los filtros
     2. sp_consultalistimventarioporid  – columnas correctas para JS
     3. sp_consultalistarticulosporid   – columnas correctas para JS
     4. sp_datosreferencia              – columnas correctas para JS
   ===================================================================== */
USE DatPos_EMP01;
GO

/* ──────────────────────────────────────────────────────────────────
   1. sp_consultaoperalmacenpricipal
   Params: @ccod_cia, @ccod_alm, @fchDesde, @fchHasta,
           @ctipo, @cserie, @nnumero, @ccod_coa, @cdsc_usuario
   Retorna (para la tabla JS):
     [0] id_cbinve  [1] ctipo  [2] vserie  [3] nnumero
     [4] ntotal     [5] dfecha [6] ccod_alm_ingreso
     [7] cdsc_usuario  [8] ccod_coa  [9] DocRef  [10] DocFact
   DocFact = botón HTML generado en el API
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultaoperalmacenpricipal','P') IS NOT NULL
    DROP PROCEDURE sp_consultaoperalmacenpricipal;
GO
CREATE PROCEDURE sp_consultaoperalmacenpricipal
    @ccod_cia       VARCHAR(20),
    @ccod_alm       VARCHAR(20)  = '',
    @fchDesde       VARCHAR(20)  = '',
    @fchHasta       VARCHAR(20)  = '',
    @ctipo          VARCHAR(20)  = '',
    @cserie         VARCHAR(20)  = '',
    @nnumero        VARCHAR(20)  = '',
    @ccod_coa       VARCHAR(200) = '',
    @cdsc_usuario   VARCHAR(200) = ''
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @dDesde  DATETIME = TRY_CONVERT(DATETIME, @fchDesde,  103);
    DECLARE @dHasta  DATETIME = TRY_CONVERT(DATETIME, @fchHasta,  103);
    -- Si fchHasta tiene hora 00:00 incluimos todo el día
    IF @dHasta IS NOT NULL
        SET @dHasta = DATEADD(SECOND, 86399, CAST(CAST(@dHasta AS DATE) AS DATETIME));

    SELECT
        CB.id_cbinve,
        CB.ctipo,
        ISNULL(CB.vserie,'')   AS vserie,
        CB.nnumero,
        CB.ntotal,
        CONVERT(VARCHAR(10), CB.dfecha, 103)  AS dfecha,
        ISNULL(CB.ccod_alm,'')               AS ccod_alm_ing,
        ISNULL(CB.ccod_usuario,'')           AS cdsc_usuario,
        ISNULL(CB.ccod_coa,'')               AS ccoa_dsc,
        ''                                   AS DocRef,
        ''                                   AS DocFact
    FROM CbInventario CB
    WHERE CB.ccod_cia = @ccod_cia
      AND (@ccod_alm      = '' OR CB.ccod_alm      = @ccod_alm)
      AND (@ctipo         = '' OR CB.ctipo          = @ctipo)
      AND (@cserie        = '' OR CB.vserie         LIKE '%' + @cserie + '%')
      AND (@nnumero       = '' OR CAST(CB.nnumero AS VARCHAR(20)) = @nnumero)
      AND (@ccod_coa      = '' OR CB.ccod_coa       LIKE '%' + @ccod_coa + '%')
      AND (@cdsc_usuario  = '' OR CB.ccod_usuario   LIKE '%' + @cdsc_usuario + '%')
      AND (@dDesde IS NULL OR CB.dfecha >= @dDesde)
      AND (@dHasta IS NULL OR CB.dfecha <= @dHasta)
    ORDER BY CB.dfecha DESC, CB.id_cbinve DESC;
END
GO
PRINT 'sp_consultaoperalmacenpricipal OK';
GO

/* ──────────────────────────────────────────────────────────────────
   2. sp_consultalistimventarioporid
   Retorna las líneas de inventario de un movimiento
   JS espera: ccod_articulo, cdsc_articulo, csim_unidadmedida,
              ncantidad, ncosto, ncosto_tot
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultalistimventarioporid','P') IS NOT NULL
    DROP PROCEDURE sp_consultalistimventarioporid;
GO
CREATE PROCEDURE sp_consultalistimventarioporid
    @ccod_cia  VARCHAR(20),
    @id_cbfact INT          -- JS pasa el id_cbinve aquí (nombre heredado)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        LN.ccod_articulo,
        ISNULL(LN.cdsc_articulo,'')             AS cdsc_articulo,
        ISNULL(UM.cdsc_unimed,'')               AS csim_unidadmedida,
        LN.ncantidad,
        LN.ncosto,
        CAST(LN.ncantidad * LN.ncosto AS DECIMAL(18,4)) AS ncosto_tot
    FROM LnInventario LN
    LEFT JOIN Articulos A
        ON A.ccod_cia = LN.ccod_cia AND A.ccod_articulo = LN.ccod_articulo
    LEFT JOIN UnidadMedida UM
        ON UM.ccod_cia = LN.ccod_cia AND UM.ccod_unimed = A.uni_medi
    WHERE LN.ccod_cia = @ccod_cia
      AND LN.id_cbinve = @id_cbfact
    ORDER BY LN.id_lninve;
END
GO
PRINT 'sp_consultalistimventarioporid OK';
GO

/* ──────────────────────────────────────────────────────────────────
   3. sp_consultalistarticulosporid
   Retorna artículos de una factura referenciada por el inventario
   JS espera: ccod_articulo, cdsc_articulo, ncantidad,
              nprecio, nimpuesto, ndescuento, nimporte_neto
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultalistarticulosporid','P') IS NOT NULL
    DROP PROCEDURE sp_consultalistarticulosporid;
GO
CREATE PROCEDURE sp_consultalistarticulosporid
    @ccod_cia  VARCHAR(20),
    @id_cbinv  INT
AS
BEGIN
    SET NOCOUNT ON;
    -- Obtener id_cbfact referenciado (vía CbFactura si hay vínculo)
    -- Si no hay referencia a factura, devolver líneas de inventario
    IF EXISTS (
        SELECT 1 FROM LnFactura LF
        JOIN CbFactura CF ON CF.id_cbfact = LF.id_cbfact
        WHERE CF.ccod_cia = @ccod_cia AND CF.id_cbinve = @id_cbinv
    )
    BEGIN
        SELECT
            LF.id_articulo                  AS ccod_articulo,
            ISNULL(LF.cdsc_articulo,'')     AS cdsc_articulo,
            LF.ncantidad,
            LF.nprecio,
            LF.nimpuesto,
            LF.ndescuento,
            LF.nimporte_neto
        FROM LnFactura LF
        JOIN CbFactura CF ON CF.id_cbfact = LF.id_cbfact
        WHERE CF.ccod_cia = @ccod_cia AND CF.id_cbinve = @id_cbinv
        ORDER BY LF.corden;
    END
    ELSE
    BEGIN
        SELECT
            LN.ccod_articulo,
            ISNULL(LN.cdsc_articulo,'')   AS cdsc_articulo,
            LN.ncantidad,
            LN.ncosto                     AS nprecio,
            CAST(0 AS DECIMAL(18,4))      AS nimpuesto,
            CAST(0 AS DECIMAL(18,4))      AS ndescuento,
            CAST(LN.ncantidad * LN.ncosto AS DECIMAL(18,4)) AS nimporte_neto
        FROM LnInventario LN
        WHERE LN.ccod_cia = @ccod_cia AND LN.id_cbinve = @id_cbinv
        ORDER BY LN.id_lninve;
    END
END
GO
PRINT 'sp_consultalistarticulosporid OK';
GO

/* ──────────────────────────────────────────────────────────────────
   4. sp_datosreferencia
   Datos detallados del movimiento de inventario + factura vinculada
   JS mapea muchos índices (0..25) — devolvemos las columnas que usa:
    [1]=cdoc  [2]=dfecha  [3]=ntotal  [4]=ccod_tiend  [5]=cdsc_tiend
    [6]=ccod_alm  [7]=cdsc_alm  [8]=ccod_usuario  [9]=cdsc_usuario
    [10]=ccod_coa  [11]=cdsc_coa  [14]=ntotal_inve  [15]=cdoc_inve
    [16]=dfecha_inve  [17]=ccod_caja  [18]=cdsc_caja
    [24]=ccod_usuario_fac  [25]=cdsc_usuario_fac
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_datosreferencia','P') IS NOT NULL
    DROP PROCEDURE sp_datosreferencia;
GO
CREATE PROCEDURE sp_datosreferencia
    @ccod_cia  VARCHAR(20),
    @id_cbinve INT
AS
BEGIN
    SET NOCOUNT ON;

    -- Obtener datos del movimiento de inventario
    SELECT
        CB.id_cbinve,                                          -- [0]
        ISNULL(CB.vserie,'') + '-' + CAST(CB.nnumero AS VARCHAR(20)) AS cdoc,  -- [1]
        CONVERT(VARCHAR(10), CB.dfecha, 103) AS dfecha,        -- [2]
        CB.ntotal,                                             -- [3]
        ISNULL(CB.ccod_tienda,'')  AS ccod_tiend,              -- [4]
        ISNULL(T.cnombr,'')        AS cdsc_tiend,              -- [5]
        ISNULL(CB.ccod_alm,'')     AS ccod_alm,                -- [6]
        ISNULL(AL.cdsc_alm,'')     AS cdsc_alm,                -- [7]
        ISNULL(CB.ccod_usuario,'') AS ccod_usuario,            -- [8]
        ISNULL(CB.ccod_usuario,'') AS cdsc_usuario,            -- [9]
        ISNULL(CB.ccod_coa,'')     AS ccod_coa,                -- [10]
        ISNULL(COA.cdsc_coa,'')    AS cdsc_coa,                -- [11]
        ''  AS col12,                                          -- [12]
        ''  AS col13,                                          -- [13]
        CB.ntotal                  AS ntotal_inve,             -- [14]
        ISNULL(CB.vserie,'') + '-' + CAST(CB.nnumero AS VARCHAR(20)) AS cdoc_inve, -- [15]
        CONVERT(VARCHAR(10), CB.dfecha, 103) AS dfecha_inve,   -- [16]
        ISNULL(CB.ccod_tienda,'')  AS ccod_caja,               -- [17]
        ''                         AS cdsc_caja,               -- [18]
        ''  AS col19,                                          -- [19]
        ''  AS col20,                                          -- [20]
        ''  AS col21,                                          -- [21]
        ''  AS col22,                                          -- [22]
        ''  AS col23,                                          -- [23]
        ISNULL(CB.ccod_usuario,'') AS ccod_usuario_fac,        -- [24]
        ISNULL(CB.ccod_usuario,'') AS cdsc_usuario_fac         -- [25]
    FROM CbInventario CB
    LEFT JOIN Tiendas  T   ON T.ccod_cia  = CB.ccod_cia AND T.ccod_tiend = CB.ccod_tienda
    LEFT JOIN Almacenes AL ON AL.ccod_cia = CB.ccod_cia AND AL.ccod_alm  = CB.ccod_alm
    LEFT JOIN Coa      COA ON COA.ccod_cia   = CB.ccod_cia AND COA.ccod_coa = CB.ccod_coa
    WHERE CB.ccod_cia = @ccod_cia AND CB.id_cbinve = @id_cbinve;
END
GO
PRINT 'sp_datosreferencia OK';
GO

/* ──────────────────────────────────────────────────────────────────
   VERIFICACIÓN
────────────────────────────────────────────────────────────────── */
SELECT name FROM sys.procedures
WHERE name IN (
  'sp_consultaoperalmacenpricipal',
  'sp_consultalistimventarioporid',
  'sp_consultalistarticulosporid',
  'sp_datosreferencia'
)
ORDER BY name;
GO
PRINT 'FIX 45 completado.';
GO
