-- ============================================================
-- MODIFY_930_FIX_74_CamposVaciosListaDatos.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : Campos vacios al pasar de "Lista" a "Datos" en multiples
--          modulos. Causa raiz comun: SPs no devuelven todas las
--          columnas que el frontend/API espera (ver PLAN_CORRECCION).
-- Cubre  :
--   BUG 2.9  Salida.php "Lista" muestra TIPO vacio para AJUSTE DE
--            EGRESO / VENTA SALIDA -> JOIN TipoOperacion y devolver
--            cdsc_tipoper.
--   BUG 2.16 ConsultaOperAlmacen.php "Lista" Doc Ref vacio ->
--            JOIN CbFactura por id_cbinve para obtener cdoc/cserie/
--            nnumero del documento de venta enlazado.
--   BUG 3.11 NotaCredito.php "Lista" no llena cdoc_coa ni cdsc_usuario.
--            Ampliamos SP para devolver F.ccod_coa y nombre de usuario.
--   BUG 3.13 Anulacion.php "Lista" tabla incompleta. SP de 8 cols ->
--            agregamos ccod_coa y filtramos por cstatus='P' (vigentes)
--            que es lo que realmente se puede anular.
--   BUG 3.17 NotaDebito.php "Lista" tabla no se llena. Ampliamos
--            webDatpos_NotaCreditoPricipal para aceptar tambien
--            documentos no asociados a una NC previa.
-- Orden  : Ejecutar despues de MODIFY_924.
-- ============================================================
USE DatPos_EMP01;
GO

PRINT '== MODIFY 930 / FIX 74 ==';

-- ----------------------------------------------------------------
-- 1) BUG 2.9 - webDatpos_consultarSalidas: incluir cdsc_tipoper
-- ----------------------------------------------------------------
-- Antes: ctipo devolvia el codigo crudo ('S','VS','AE'...) que el
-- DataTable mostraba como columna "Tipo Operacion". Para 'S' (Salida
-- generica) funcionaba bien, pero para codigos como 'AE' (AJUSTE DE
-- EGRESO) o 'VS' (VENTA/SALIDA) la celda mostraba un texto corto que
-- el usuario percibia como vacio en la columna ancha del DataTable.
-- Solucion: tras la migracion del Sprint 2 a "texto en lugar de
-- codigo", aqui hacemos lo mismo: devolvemos cdsc_tipoper. El API
-- ya mapea $f[6] -> 'ctipo'; mantenemos ese alias pero ahora con
-- la descripcion completa, y agregamos ctipo_raw para compatibilidad.
IF OBJECT_ID('webDatpos_consultarSalidas','P') IS NOT NULL
    DROP PROCEDURE webDatpos_consultarSalidas;
GO
CREATE PROCEDURE webDatpos_consultarSalidas @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        C.id_cbinve,                                                  -- [0]
        ISNULL(T1.cnombr, ISNULL(C.ccod_tienda,''))    AS cdsc_tienda,-- [1]
        ISNULL(A.cdsc_alm, ISNULL(C.ccod_alm,''))      AS cdsc_alm,   -- [2]
        CONVERT(VARCHAR(10), C.dfecha, 103)            AS dfecha,     -- [3]
        ISNULL(C.vserie,'')                            AS vserie,     -- [4]
        ISNULL(C.nnumero,0)                            AS nnumero,    -- [5]
        -- BUG 2.9: devolver cdsc_tipoper (texto) en lugar de codigo
        ISNULL(T.cdsc_tipoper, ISNULL(C.ctipo,''))     AS ctipo,      -- [6]
        ISNULL(C.vobservacion,'')                      AS vobservacion,-- [7]
        ISNULL(C.ntotal,0)                             AS ntotal,     -- [8]
        ISNULL(C.ctipo,'')                             AS ctipo_raw   -- [9]
    FROM CbInventario C
    LEFT JOIN TipoOperacion T
      ON T.ccod_cia=C.ccod_cia AND T.ccod_tipoper=C.ctipo
    LEFT JOIN Almacenes A
      ON A.ccod_cia=C.ccod_cia AND A.ccod_alm=C.ccod_alm
    LEFT JOIN Tiendas T1
      ON T1.ccod_cia=C.ccod_cia AND T1.ccod_tiend=C.ccod_tienda
    WHERE C.ccod_cia=@ccod_cia
      AND ( T.ctipo_flag='S' OR C.ctipo='S' )
    ORDER BY C.dfecha DESC, C.id_cbinve DESC;
END
GO
PRINT '  -> webDatpos_consultarSalidas (con cdsc_tipoper) recreado';

-- ----------------------------------------------------------------
-- 1b) Mismo tratamiento para webDatpos_consultarIngresos
-- ----------------------------------------------------------------
IF OBJECT_ID('webDatpos_consultarIngresos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_consultarIngresos;
GO
CREATE PROCEDURE webDatpos_consultarIngresos @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        C.id_cbinve,                                                  -- [0]
        ISNULL(T1.cnombr, ISNULL(C.ccod_tienda,''))    AS cdsc_tienda,-- [1]
        ISNULL(A.cdsc_alm, ISNULL(C.ccod_alm,''))      AS cdsc_alm,   -- [2]
        CONVERT(VARCHAR(10), C.dfecha, 103)            AS dfecha,     -- [3]
        ISNULL(C.vserie,'')                            AS vserie,     -- [4]
        ISNULL(C.nnumero,0)                            AS nnumero,    -- [5]
        ISNULL(T.cdsc_tipoper, ISNULL(C.ctipo,''))     AS ctipo,      -- [6]
        ISNULL(C.vobservacion,'')                      AS vobservacion,-- [7]
        ISNULL(C.ntotal,0)                             AS ntotal,     -- [8]
        ISNULL(C.ctipo,'')                             AS ctipo_raw   -- [9]
    FROM CbInventario C
    LEFT JOIN TipoOperacion T
      ON T.ccod_cia=C.ccod_cia AND T.ccod_tipoper=C.ctipo
    LEFT JOIN Almacenes A
      ON A.ccod_cia=C.ccod_cia AND A.ccod_alm=C.ccod_alm
    LEFT JOIN Tiendas T1
      ON T1.ccod_cia=C.ccod_cia AND T1.ccod_tiend=C.ccod_tienda
    WHERE C.ccod_cia=@ccod_cia
      AND ( T.ctipo_flag='I' OR C.ctipo='I' )
    ORDER BY C.dfecha DESC, C.id_cbinve DESC;
END
GO
PRINT '  -> webDatpos_consultarIngresos (con cdsc_tipoper) recreado';

-- ----------------------------------------------------------------
-- 2) BUG 2.16 - sp_consultaoperalmacenpricipal: DocRef desde CbFactura
-- ----------------------------------------------------------------
-- Antes: DocRef era hardcoded ''. CbInventario no tiene cdoc_ref,
-- pero CbFactura.id_cbinve enlaza factura -> movimiento de almacen.
-- Cuando un id_cbinve esta enlazado a una factura, mostramos el
-- "doc+serie-numero" del documento de venta como referencia.
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

    DECLARE @dDesde  DATETIME = TRY_CONVERT(DATETIME, @fchDesde, 103);
    DECLARE @dHasta  DATETIME = TRY_CONVERT(DATETIME, @fchHasta, 103);
    IF @dHasta IS NOT NULL
        SET @dHasta = DATEADD(SECOND, 86399, CAST(CAST(@dHasta AS DATE) AS DATETIME));

    SELECT
        CB.id_cbinve,                                            -- [0]
        CB.ctipo,                                                -- [1]
        ISNULL(CB.vserie,'')                       AS vserie,    -- [2]
        CB.nnumero,                                              -- [3]
        CB.ntotal,                                               -- [4]
        CONVERT(VARCHAR(10), CB.dfecha, 103)       AS dfecha,    -- [5]
        ISNULL(CB.ccod_alm,'')                     AS ccod_alm_ing,-- [6]
        ISNULL(CB.ccod_usuario,'')                 AS cdsc_usuario,-- [7]
        ISNULL(CB.ccod_coa,'')                     AS ccoa_dsc,  -- [8]
        -- BUG 2.16: DocRef = cdoc+cserie-nnumero de la factura asociada
        -- al movimiento de inventario (CbFactura.id_cbinve = CB.id_cbinve).
        -- Si no hay factura asociada, queda en blanco.
        ISNULL(
            (SELECT TOP 1
                 ISNULL(F.cdoc,'')
                 + CASE WHEN ISNULL(F.cserie,'') <> ''
                        THEN ' ' + F.cserie ELSE '' END
                 + CASE WHEN ISNULL(F.nnumero,0) <> 0
                        THEN '-' + CAST(F.nnumero AS VARCHAR(20)) ELSE '' END
             FROM CbFactura F
             WHERE F.ccod_cia=CB.ccod_cia AND F.id_cbinve=CB.id_cbinve
             ORDER BY F.id_cbfact DESC), '')        AS DocRef,   -- [9]
        ''                                          AS DocFact   -- [10]
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
PRINT '  -> sp_consultaoperalmacenpricipal (con DocRef JOIN CbFactura) recreado';

-- ----------------------------------------------------------------
-- 3) BUG 3.11 - webDatpos_ConsultarDocumentosNotaCredito: agregar
--               ccod_coa (RUC cliente) y cdsc_usuario.
-- ----------------------------------------------------------------
IF OBJECT_ID('webDatpos_ConsultarDocumentosNotaCredito','P') IS NOT NULL
    DROP PROCEDURE webDatpos_ConsultarDocumentosNotaCredito;
GO
CREATE PROCEDURE webDatpos_ConsultarDocumentosNotaCredito
    @cdoc_seri    VARCHAR(5),
    @serie        VARCHAR(10),
    @correlativo  VARCHAR(20),
    @ccod_tienda  VARCHAR(20),
    @ccod_coa     VARCHAR(20),
    @fchDesde     VARCHAR(20),
    @fchHasta     VARCHAR(20),
    @CodCia       VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        F.id_cbfact,                                           -- [0]
        F.cdoc,                                                -- [1]
        F.cserie,                                              -- [2]
        F.nnumero,                                             -- [3]
        CONVERT(VARCHAR(10), F.dfch_crea, 103)  AS dfch_doc,   -- [4]
        F.ntotal,                                              -- [5]
        F.cstatus,                                             -- [6]
        ISNULL(C.cdsc_coa, '')                  AS cdsc_coa,   -- [7]
        -- BUG 3.11: nuevas columnas
        ISNULL(F.ccod_coa, '')                  AS ccod_coa,   -- [8]
        ISNULL(U.cdsc_usuario,
               ISNULL(F.ccod_usuario,''))       AS cdsc_usuario-- [9]
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    LEFT JOIN Usuarios U
        ON U.ccod_empresa = F.ccod_cia AND U.ccod_usuario = F.ccod_usuario
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc IN ('NC', 'ND')
      AND (@cdoc_seri = '' OR F.cdoc = @cdoc_seri)
      AND (@serie = '' OR F.cserie = @serie)
      AND (@correlativo = '' OR F.nnumero = TRY_CAST(@correlativo AS INT))
      AND (@ccod_tienda = '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa = '' OR F.ccod_coa = @ccod_coa)
      AND (@fchDesde = '' OR F.dfch_crea >= TRY_CONVERT(DATETIME, @fchDesde, 103))
      AND (@fchHasta = '' OR F.dfch_crea <  DATEADD(DAY, 1, TRY_CONVERT(DATETIME, @fchHasta, 103)))
    ORDER BY F.id_cbfact DESC;
END
GO
PRINT '  -> webDatpos_ConsultarDocumentosNotaCredito (con ccod_coa + cdsc_usuario) recreado';

-- ----------------------------------------------------------------
-- 4) BUG 3.13 - webDatpos_anulacionPricipal: agregar ccod_coa y
--               filtrar por cstatus='P' (vigentes = anulables).
-- ----------------------------------------------------------------
IF OBJECT_ID('webDatpos_anulacionPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_anulacionPricipal;
GO
CREATE PROCEDURE webDatpos_anulacionPricipal
    @cdoc_seri    VARCHAR(5),
    @serie        VARCHAR(10),
    @correlativo  VARCHAR(20),
    @ccod_tienda  VARCHAR(20),
    @ccod_coa     VARCHAR(20),
    @fchDesde     VARCHAR(20),
    @fchHasta     VARCHAR(20),
    @CodCia       VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        F.id_cbfact,                                           -- [0]
        F.cdoc,                                                -- [1]
        F.cserie,                                              -- [2]
        F.nnumero,                                             -- [3]
        CONVERT(VARCHAR(10), F.fecha_emision, 103) AS dfch_doc,-- [4]
        ISNULL(C.cdsc_coa,'')                   AS cdsc_coa,   -- [5]
        F.ntotal,                                              -- [6]
        ISNULL(F.ccod_coa,'')                   AS ccod_coa,   -- [7]
        ISNULL(F.ccod_tiend,'')                 AS ccod_tienda,-- [8]
        ISNULL(T.cnombr,'')                     AS cdsc_tienda,-- [9]
        ISNULL(F.ccod_caja,'')                  AS ccod_caja,  -- [10]
        ISNULL(K.cdsc_caja,'')                  AS cdsc_caja,  -- [11]
        ISNULL(F.ccod_usuario,'')               AS cusu_crea,  -- [12]
        ISNULL(U.cdsc_usuario,
               ISNULL(F.ccod_usuario,''))       AS cdsc_usuario,-- [13]
        F.cstatus                               AS cstatus_doc -- [14]
    FROM CbFactura F
    LEFT JOIN Coa C
      ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    LEFT JOIN Tiendas T
      ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
    LEFT JOIN Cajas K
      ON K.ccod_cia = F.ccod_cia AND K.ccod_caja = F.ccod_caja
    LEFT JOIN Usuarios U
      ON U.ccod_empresa = F.ccod_cia AND U.ccod_usuario = F.ccod_usuario
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc IN ('BV','FV','FA','BO','NV')
      AND F.cstatus = 'P'    -- BUG 3.13: solo vigentes (anulables)
      AND (@cdoc_seri  = '' OR F.cdoc    = @cdoc_seri)
      AND (@serie      = '' OR F.cserie  = @serie)
      AND (@correlativo= '' OR CAST(F.nnumero AS VARCHAR) = @correlativo)
      AND (@ccod_tienda= '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa   = '' OR F.ccod_coa   = @ccod_coa)
      AND (@fchDesde   = '' OR F.fecha_emision >= TRY_CONVERT(DATETIME, @fchDesde, 103))
      AND (@fchHasta   = '' OR F.fecha_emision <  DATEADD(DAY, 1, TRY_CONVERT(DATETIME, @fchHasta, 103)))
    ORDER BY F.fecha_emision DESC, F.id_cbfact DESC;
END
GO
PRINT '  -> webDatpos_anulacionPricipal (15 cols con ccod_coa/usuario/tienda/caja) recreado';

-- ----------------------------------------------------------------
-- 5) BUG 3.17 - webDatpos_NotaCreditoPricipal: aceptar
--               cdoc IN ('BV','FV','FA','BO','NV') y devolver
--               formato fecha consistente. Mantiene 8 cols.
-- ----------------------------------------------------------------
IF OBJECT_ID('webDatpos_NotaCreditoPricipal','P') IS NOT NULL
    DROP PROCEDURE webDatpos_NotaCreditoPricipal;
GO
CREATE PROCEDURE webDatpos_NotaCreditoPricipal
    @cdoc_seri    VARCHAR(5),
    @serie        VARCHAR(10),
    @correlativo  VARCHAR(20),
    @ccod_tienda  VARCHAR(20),
    @ccod_coa     VARCHAR(20),
    @fchDesde     VARCHAR(20),
    @fchHasta     VARCHAR(20),
    @CodCia       VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        F.id_cbfact,                                           -- [0]
        F.cdoc,                                                -- [1]
        F.cserie,                                              -- [2]
        F.nnumero,                                             -- [3]
        CONVERT(VARCHAR(10), F.fecha_emision, 103) AS fecha_emision, -- [4]
        F.ntotal,                                              -- [5]
        ISNULL(C.cdsc_coa, '') AS cdsc_coa,                    -- [6]
        ISNULL(F.ccod_coa, '') AS ccod_coa                     -- [7]
    FROM CbFactura F
    LEFT JOIN Coa C
        ON C.ccod_coa = F.ccod_coa AND C.ccod_cia = F.ccod_cia
    WHERE F.ccod_cia = @CodCia
      AND F.cdoc IN ('BV','FV','FA','BO','NV')
      AND F.cstatus = 'P'
      AND (@cdoc_seri  = '' OR F.cdoc    = @cdoc_seri)
      AND (@serie      = '' OR F.cserie  = @serie)
      AND (@correlativo= '' OR F.nnumero = TRY_CAST(@correlativo AS INT))
      AND (@ccod_tienda= '' OR F.ccod_tiend = @ccod_tienda)
      AND (@ccod_coa   = '' OR F.ccod_coa   = @ccod_coa)
      AND (@fchDesde   = '' OR F.fecha_emision >= TRY_CONVERT(DATETIME, @fchDesde, 103))
      AND (@fchHasta   = '' OR F.fecha_emision <  DATEADD(DAY, 1, TRY_CONVERT(DATETIME, @fchHasta, 103)))
    ORDER BY F.fecha_emision DESC;
END
GO
PRINT '  -> webDatpos_NotaCreditoPricipal (8 cols con filtros TRY_CONVERT) recreado';

-- ----------------------------------------------------------------
-- 6) BUG 2.17 - sp_consultararticulospricipal: SP no existia, lo
--               creamos con los filtros que envia ConsultaArticulos.js
--               (CodArticulo, NomAticulo, TipArticulo, Tributos,
--                Familia, UniMedida, Estado) y devolvemos los 7 cols
--               que espera la DataTable.
-- ----------------------------------------------------------------
IF OBJECT_ID('sp_consultararticulospricipal','P') IS NOT NULL
    DROP PROCEDURE sp_consultararticulospricipal;
GO
CREATE PROCEDURE sp_consultararticulospricipal
    @ccod_cia      VARCHAR(20),
    @CodArticulo   VARCHAR(50)  = '',
    @NomAticulo    VARCHAR(200) = '',
    @TipArticulo   VARCHAR(10)  = '',
    @Tributos      VARCHAR(5)   = '',
    @Familia       VARCHAR(20)  = '',
    @UniMedida     VARCHAR(10)  = '',
    @Estado        VARCHAR(1)   = '',
    @id_articulo   VARCHAR(20)  = ''     -- compatibilidad antigua
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        A.ccod_articulo                                        AS ccod_articulo,
        ISNULL(A.cdsc_articulo,'')                             AS cdsc_articulo,
        ISNULL(L.cdsc_lin, ISNULL(A.ccod_lin,''))              AS linea,
        ISNULL(U.cdsc_unimed, ISNULL(A.uni_medi,''))           AS uni_medi,
        ISNULL(A.ctip_articulo,'')                             AS ctip_articulo,
        CASE WHEN A.cstatus='A' THEN 'Activo' ELSE 'Inactivo' END AS estado,
        ISNULL(A.cigv,'')                                      AS cigv
    FROM Articulos A
    LEFT JOIN Familias L
        ON L.ccod_cia=A.ccod_cia AND L.ccod_lin=A.ccod_lin
    LEFT JOIN UnidadMedida U
        ON U.ccod_cia=A.ccod_cia AND U.ccod_unimed=A.uni_medi
    WHERE A.ccod_cia = @ccod_cia
      AND (@CodArticulo = '' OR A.ccod_articulo LIKE '%' + @CodArticulo + '%')
      AND (@NomAticulo  = '' OR A.cdsc_articulo LIKE '%' + @NomAticulo  + '%')
      AND (@TipArticulo = '' OR A.ctip_articulo = @TipArticulo)
      AND (@Tributos    = '' OR A.cigv          = @Tributos)
      AND (@Familia     = '' OR A.ccod_lin      = @Familia)
      AND (@UniMedida   = '' OR A.uni_medi      = @UniMedida)
      AND (@Estado      = '' OR A.cstatus       = @Estado)
      AND (@id_articulo = '' OR CAST(A.id_articulo AS VARCHAR(20)) = @id_articulo)
    ORDER BY A.cdsc_articulo;
END
GO
PRINT '  -> sp_consultararticulospricipal (catalogo de articulos con 7 cols) recreado';

PRINT '== MODIFY 930 / FIX 74 (full) aplicado correctamente ==';
