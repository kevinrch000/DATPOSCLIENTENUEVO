-- ============================================================
-- NEW_851_FIX_57_sp_listadebienes_articulo.sql
-- ============================================================
-- Estado : NEW
-- Motivo : api/notacredito_api.php (case ListaDeArticulo y
--          ListaDeBienes) invoca SPs `sp_listadearticulo` y
--          `sp_listadebienes` que NO existen — sólo había
--          `webDatpos_ListaDeArticulo` con shape distinto.
--          Por eso al cargar Nota Crédito/Débito la pestaña
--          "Lista de articulos" salía vacía aunque CbFactura/
--          LnFactura tuvieran detalle.
-- Cambio : Crear ambos SPs devolviendo en este orden exacto
--          (alineado con mapper PHP):
--              [0] id_articulo
--              [1] cdsc_articulo
--              [2] ncantidad
--          Filtra por @ccod_cia + @id_cbfact (INT cast).
-- Orden  : Ejecutar después de NEW_850.
-- ============================================================
USE DatPos_EMP01;
GO
IF OBJECT_ID('sp_listadearticulo','P') IS NOT NULL DROP PROCEDURE sp_listadearticulo;
GO
CREATE PROCEDURE sp_listadearticulo
    @ccod_cia   VARCHAR(20),
    @id_cbfact  VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        L.id_articulo     AS id_articulo,
        L.cdsc_articulo   AS cdsc_articulo,
        L.ncantidad       AS ncantidad,
        L.id_lnfact       AS id_lnfact,
        L.id_articulo     AS ccod_articulo,
        L.nprecio         AS nprecio
    FROM LnFactura L
    WHERE L.ccod_cia  = @ccod_cia
      AND L.id_cbfact = TRY_CAST(@id_cbfact AS INT);
END
GO
IF OBJECT_ID('sp_listadebienes','P') IS NOT NULL DROP PROCEDURE sp_listadebienes;
GO
CREATE PROCEDURE sp_listadebienes
    @ccod_cia   VARCHAR(20),
    @id_cbfact  VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        L.id_articulo     AS id_articulo,
        L.cdsc_articulo   AS cdsc_articulo,
        L.ncantidad       AS ncantidad,
        L.id_lnfact       AS id_lnfact,
        L.id_articulo     AS ccod_articulo,
        L.nprecio         AS nprecio
    FROM LnFactura L
    WHERE L.ccod_cia  = @ccod_cia
      AND L.id_cbfact = TRY_CAST(@id_cbfact AS INT);
END
GO
PRINT 'NEW_851 aplicado: sp_listadearticulo y sp_listadebienes creados con shape esperado.';
