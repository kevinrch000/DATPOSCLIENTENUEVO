/* =========================================================================
   FIX_55 — SPs que retornan [] cuando reciben '%%%' o '' como filtro
   =========================================================================

   SÍNTOMAS:
     - Alerta de Stock → ConsultaStockMinimoPrincipal devuelve []
       con {ccod_alm:"%%%", cdsc_lin:"%%%"}
     - Consulta Lista Precios → ConsultaListPrecioPricipal devuelve []
       con {ccod_lin:"%%%", ccod_unidadmedida:"%%%"}
     - Artículos Más Vendidos → devuelve [] con {ccod_lin:"%%%"}

   CAUSA:
     Los SPs usan los filtros como condiciones rígidas de igualdad
     (ej. S.ccod_alm = @ccod_alm) en lugar de condicionales opcionales
     (AND (@ccod_alm = '' OR S.ccod_alm = @ccod_alm)).
     Cuando el valor llega como '' (después de que la API convierte %%%
     a '') el JOIN no encuentra filas → resultado vacío.

   Nota: la API PHP ya fue actualizada para convertir '%%%' → ''
   antes de llamar al SP. Este migration hace que el SP también sea
   robusto ante '' (todos) en @ccod_alm.

   Ejecutar en DatPos_EMP01
   ========================================================================= */

USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== FIX_55: SPs Stock Mínimo + Lista Precio + Más Vendidos ==';

/* --------------------------------------------------------------------- */
/* 1. webDatpos_ConsultaStockMinimo                                       */
/*    @ccod_alm = '' → muestra todos los almacenes                       */
/* --------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_ConsultaStockMinimo','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaStockMinimo;
GO

CREATE PROCEDURE webDatpos_ConsultaStockMinimo
    @ccod_cia      VARCHAR(20),
    @ccod_alm      VARCHAR(20) = '',
    @ccod_lin      VARCHAR(20) = '',
    @ccod_articulo VARCHAR(50) = '',
    @nstock_min    VARCHAR(20) = ''
AS
BEGIN
    SET NOCOUNT ON;

    -- Normalizar '%%%' a '' (sin filtro) por si la API no lo hizo
    SET @ccod_alm      = CASE WHEN @ccod_alm      = '%%%' THEN '' ELSE ISNULL(@ccod_alm,'')      END;
    SET @ccod_lin      = CASE WHEN @ccod_lin      = '%%%' THEN '' ELSE ISNULL(@ccod_lin,'')      END;
    SET @ccod_articulo = CASE WHEN @ccod_articulo = '%%%' THEN '' ELSE ISNULL(@ccod_articulo,'') END;

    -- Devuelve artículos cuyo stock actual está EN O POR DEBAJO del stock mínimo.
    -- La tabla muestra 7 columnas: ccod_alm, ccod_articulo, cdsc_articulo,
    -- cdsc_lin, ncantidad, nstock_min, nstock_max
    SELECT
        ISNULL(S.ccod_alm, ISNULL(@ccod_alm,''))       AS ccod_alm,
        A.ccod_articulo,
        ISNULL(A.cdsc_articulo,'')                      AS cdsc_articulo,
        ISNULL(F.cdsc_lin, ISNULL(A.ccod_lin,''))       AS cdsc_lin,
        ISNULL(S.ncantidad, 0)                          AS ncantidad,
        ISNULL(A.nstock_min, 0)                         AS nstock_min,
        ISNULL(A.nstock_max, 0)                         AS nstock_max
    FROM Articulos A
    -- Si @ccod_alm vacío → LEFT JOIN a todo el stock (todos los almacenes)
    LEFT JOIN Stock S
        ON S.ccod_cia      = A.ccod_cia
       AND S.ccod_articulo = A.ccod_articulo
       AND (@ccod_alm = '' OR S.ccod_alm = @ccod_alm)
    LEFT JOIN Familias F
        ON F.ccod_cia = A.ccod_cia
       AND F.ccod_lin = A.ccod_lin
    WHERE A.ccod_cia   = @ccod_cia
      AND A.cstatus    = 'A'
      AND A.nstock_min > 0
      AND ISNULL(S.ncantidad, 0) <= A.nstock_min
      AND (@ccod_articulo = '' OR A.ccod_articulo LIKE '%' + @ccod_articulo + '%')
      AND (@ccod_lin      = '' OR A.ccod_lin = @ccod_lin)
    ORDER BY A.cdsc_articulo;
END
GO

PRINT '  [1/3] webDatpos_ConsultaStockMinimo — devuelve nstock_max, soporta @ccod_alm=''.';
GO

/* --------------------------------------------------------------------- */
/* 2. webDatpos_ConsultaListPrecioPricipal                                */
/*    @ccod_lin = '' y @ccod_unidadmedida = '' → sin filtro              */
/* --------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_ConsultaListPrecioPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaListPrecioPricipal;
GO

CREATE PROCEDURE webDatpos_ConsultaListPrecioPricipal
    @ccod_cblistpre    VARCHAR(20)  = '',
    @ccod_articulo     VARCHAR(50)  = '',
    @cdsc_articulo     VARCHAR(200) = '',
    @ccod_lin          VARCHAR(20)  = '',
    @ccod_unidadmedida VARCHAR(10)  = '',
    @CodCia            VARCHAR(20)  = ''
AS
BEGIN
    SET NOCOUNT ON;

    -- Normalizar '%%%' a '' por si la API no lo hizo
    SET @ccod_cblistpre    = CASE WHEN @ccod_cblistpre    = '%%%' THEN '' ELSE ISNULL(@ccod_cblistpre,'')    END;
    SET @ccod_articulo     = CASE WHEN @ccod_articulo     = '%%%' THEN '' ELSE ISNULL(@ccod_articulo,'')     END;
    SET @cdsc_articulo     = CASE WHEN @cdsc_articulo     = '%%%' THEN '' ELSE ISNULL(@cdsc_articulo,'')     END;
    SET @ccod_lin          = CASE WHEN @ccod_lin          = '%%%' THEN '' ELSE ISNULL(@ccod_lin,'')          END;
    SET @ccod_unidadmedida = CASE WHEN @ccod_unidadmedida = '%%%' THEN '' ELSE ISNULL(@ccod_unidadmedida,'') END;

    SELECT
        ISNULL(L.ccod_cblistpre,'')                          AS ccod_cblistpre,
        ISNULL(CB.cdsc_cblistpre,'')                         AS cdsc_cblistpre,
        ISNULL(L.ccod_articulo,'')                           AS ccod_articulo,
        ISNULL(A.cdsc_articulo,'')                           AS cdsc_articulo,
        ISNULL(FAM.cdsc_lin,'')                              AS cdsc_lin,
        ISNULL(UM.cdsc_unimed, ISNULL(A.uni_medi,''))        AS csim_unidadmedida,
        CONVERT(VARCHAR(20), ISNULL(L.npre_uni, 0))          AS npre_uni
    FROM LnListaPrecio L
    JOIN CbListaPrecio CB
        ON CB.ccod_cia = L.ccod_cia
       AND CB.ccod_cblistpre = L.ccod_cblistpre
       AND CB.cstatus = 'A'
    LEFT JOIN Articulos A
        ON A.ccod_cia = L.ccod_cia
       AND A.ccod_articulo = L.ccod_articulo
    LEFT JOIN Familias FAM
        ON FAM.ccod_cia = A.ccod_cia
       AND FAM.ccod_lin = A.ccod_lin
    LEFT JOIN UnidadMedida UM
        ON UM.ccod_cia = A.ccod_cia
       AND UM.ccod_unimed = A.uni_medi
    WHERE L.ccod_cia = @CodCia
      AND (@ccod_cblistpre    = '' OR L.ccod_cblistpre          = @ccod_cblistpre)
      AND (@ccod_articulo     = '' OR L.ccod_articulo           = @ccod_articulo)
      AND (@cdsc_articulo     = '' OR ISNULL(A.cdsc_articulo,'') LIKE '%' + @cdsc_articulo + '%')
      AND (@ccod_lin          = '' OR ISNULL(A.ccod_lin,'')      = @ccod_lin)
      AND (@ccod_unidadmedida = '' OR ISNULL(A.uni_medi,'')      = @ccod_unidadmedida)
    ORDER BY L.ccod_cblistpre, L.ccod_articulo;
END
GO

PRINT '  [2/3] webDatpos_ConsultaListPrecioPricipal — soporta filtros vacíos (todos).';
GO

/* --------------------------------------------------------------------- */
/* 3. webDatpos_ConsultaArticulosMasVendidos                              */
/*    LnFactura usa id_articulo (no ccod_articulo).                       */
/*    Agrega normalización de '%%%' → '' al SP existente.                 */
/* --------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_ConsultaArticulosMasVendidos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultaArticulosMasVendidos;
GO

CREATE PROCEDURE webDatpos_ConsultaArticulosMasVendidos
    @ccod_tienda   VARCHAR(20) = '',
    @ccod_articulo VARCHAR(50) = '',
    @ccod_lin      VARCHAR(20) = '',
    @fchDesde      VARCHAR(30) = '',
    @fchHasta      VARCHAR(30) = '',
    @CodCia        VARCHAR(20) = ''
AS
BEGIN
    SET NOCOUNT ON;

    -- Normalizar '%%%' a '' (sin filtro)
    SET @ccod_tienda   = CASE WHEN @ccod_tienda   = '%%%' THEN '' ELSE ISNULL(@ccod_tienda,'')   END;
    SET @ccod_articulo = CASE WHEN @ccod_articulo = '%%%' THEN '' ELSE ISNULL(@ccod_articulo,'') END;
    SET @ccod_lin      = CASE WHEN @ccod_lin      = '%%%' THEN '' ELSE ISNULL(@ccod_lin,'')      END;

    DECLARE @dDesde DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchDesde,''), 120),
        '19000101');
    DECLARE @dHasta DATETIME = COALESCE(
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 103),
        TRY_CONVERT(DATETIME, NULLIF(@fchHasta,''), 120),
        '99991231');
    SET @dHasta = DATEADD(DAY, 1, @dHasta);

    -- LnFactura guarda el código del artículo en la columna id_articulo (VARCHAR)
    SELECT
        ISNULL(F.ccod_caja,'')                            AS ccod_caja,
        ISNULL(CJ.cdsc_caja,'')                           AS cdsc_caja,
        ISNULL(A.ccod_lin,'')                             AS ccod_lin,
        ISNULL(L.id_articulo,'')                          AS ccod_articulo,
        ISNULL(L.cdsc_articulo,'')                        AS cdsc_articulo,
        CONVERT(VARCHAR(20), SUM(ISNULL(L.ncantidad,0))) AS ncantidad
    FROM LnFactura L
    JOIN CbFactura F
        ON F.ccod_cia  = L.ccod_cia
       AND F.id_cbfact = L.id_cbfact
    LEFT JOIN Articulos A
        ON A.ccod_cia      = L.ccod_cia
       AND A.ccod_articulo = L.id_articulo
    LEFT JOIN Cajas CJ
        ON CJ.ccod_cia  = F.ccod_cia
       AND CJ.ccod_caja = F.ccod_caja
    WHERE F.ccod_cia = @CodCia
      AND F.cstatus <> 'A'
      AND F.fecha_emision >= @dDesde
      AND F.fecha_emision <  @dHasta
      AND (@ccod_tienda   = '' OR F.ccod_tiend            = @ccod_tienda)
      AND (@ccod_articulo = '' OR L.id_articulo            = @ccod_articulo)
      AND (@ccod_lin      = '' OR ISNULL(A.ccod_lin,'')   = @ccod_lin)
    GROUP BY F.ccod_caja, CJ.cdsc_caja, A.ccod_lin,
             L.id_articulo, L.cdsc_articulo
    ORDER BY SUM(ISNULL(L.ncantidad,0)) DESC;
END
GO

PRINT '  [3/3] webDatpos_ConsultaArticulosMasVendidos — usa L.id_articulo, soporta %%%.';
GO

PRINT 'OK - FIX_55 completo.';
GO
