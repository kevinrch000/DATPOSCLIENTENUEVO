-- ============================================================
-- MODIFY_840_FIX_55_LP_Favoritos_Articulos_ColumnasOrden.sql
-- ============================================================
-- Estado : MODIFY
-- Motivo : sp_lpconsultarfavoritos y sp_lsconsultararticulocategoria
--          devolvían las columnas en orden incompatible con
--          mapArticuloFacturacion() de api/facturacion_api.php
--          (mapper espera: cdsc_articulo, iimage, id_articulo,
--          precio, ctip_articulo, bprefer).
--          Resultado: la UI de FacturaListaPrecio mostraba "18"
--          (IGV) como precio y el código (ART003) como descripción.
-- Orden  : Ejecutar después de 770_FIX_47 y antes de cualquier
--          ejecución de UI/UAT de FacturaListaPrecio.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('sp_lpconsultarfavoritos','P') IS NOT NULL DROP PROCEDURE sp_lpconsultarfavoritos;
GO
CREATE PROCEDURE sp_lpconsultarfavoritos
    @ccod_cia       VARCHAR(20),
    @ccod_usuario   VARCHAR(50),
    @ccod_almacen   VARCHAR(20),
    @ccod_cblistpre VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        A.cdsc_articulo                       AS cdsc_articulo,
        A.iimage                              AS iimage,
        A.id_articulo                         AS id_articulo,
        ISNULL(P.npre_uni, 0)                 AS precio,
        ISNULL(A.ctip_articulo, 'S')          AS ctip_articulo,
        -1                                    AS bprefer,
        A.ccod_articulo                       AS ccod_articulo,
        A.cigv                                AS cigv,
        A.cisc                                AS cisc,
        ISNULL(S.ncantidad, 0)                AS ncantidad
    FROM Articulos A
    LEFT JOIN LnListaPrecio P
        ON P.ccod_articulo = A.ccod_articulo
       AND P.ccod_cblistpre = @ccod_cblistpre
       AND P.ccod_cia = A.ccod_cia
    LEFT JOIN Stock S
        ON S.ccod_articulo = A.ccod_articulo
       AND S.ccod_alm = @ccod_almacen
       AND S.ccod_cia = A.ccod_cia
    WHERE A.ccod_cia = @ccod_cia
      AND A.bprefer = 1
      AND A.cstatus = 'A'
    ORDER BY A.cdsc_articulo;
END
GO

IF OBJECT_ID('sp_lsconsultararticulocategoria','P') IS NOT NULL DROP PROCEDURE sp_lsconsultararticulocategoria;
GO
CREATE PROCEDURE sp_lsconsultararticulocategoria
    @ccod_cia       VARCHAR(20),
    @codigo         INT,
    @ccod_usuario   VARCHAR(50),
    @ccod_almacen   VARCHAR(20),
    @ccod_cblistpre VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        A.cdsc_articulo                       AS cdsc_articulo,
        A.iimage                              AS iimage,
        A.id_articulo                         AS id_articulo,
        ISNULL(P.npre_uni, 0)                 AS precio,
        ISNULL(A.ctip_articulo, 'S')          AS ctip_articulo,
        0                                     AS bprefer,
        A.ccod_articulo                       AS ccod_articulo,
        A.cigv                                AS cigv,
        A.cisc                                AS cisc,
        ISNULL(S.ncantidad, 0)                AS ncantidad
    FROM Articulos A
    LEFT JOIN LnListaPrecio P
        ON P.ccod_articulo = A.ccod_articulo
       AND P.ccod_cblistpre = @ccod_cblistpre
       AND P.ccod_cia = A.ccod_cia
    LEFT JOIN Stock S
        ON S.ccod_articulo = A.ccod_articulo
       AND S.ccod_alm = @ccod_almacen
       AND S.ccod_cia = A.ccod_cia
    WHERE A.ccod_cia = @ccod_cia
      AND A.cstatus = 'A'
      AND (@codigo = 0 OR A.ccod_lin IN
           (SELECT ccod_lin FROM Familias
            WHERE ccod_cia = @ccod_cia AND id_lin = @codigo))
    ORDER BY A.cdsc_articulo;
END
GO
PRINT 'MODIFY_840 aplicado: sp_lpconsultarfavoritos y sp_lsconsultararticulocategoria devuelven columnas en orden esperado por mapArticuloFacturacion.';
