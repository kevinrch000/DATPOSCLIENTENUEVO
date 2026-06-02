/* ========================================================================
   MODIFY_915 / FIX_68
   ConsultaListPrecio.php: el modal "Seleccione Articulo" salia vacio.

   Causa: el case 'CargarArticuloListPrecio' del API llamaba al SP
   'sp_cargararticulolistprecio' que nunca existio en ningun script. El
   modal abre la AJAX a `ConsultaListPrecio.aspx/CargarArticuloListPrecio`
   con payload { objArticuloListPrecio: [ { ccod_cblistpre, ccod_articulo,
   cdsc_articulo, ccod_lin, ccod_unidadmedida } ] } y espera devolver
   los articulos cargados en esa lista de precios para que el usuario
   pueda elegir uno y poblar el filtro #txtCodArticulo.

   Este script crea el SP canonico (webDatpos_CargarArticuloListPrecio)
   y un alias (sp_cargararticulolistprecio) para defensa, ambos
   idempotentes.

   Filtros aceptados:
     @ccod_cblistpre    cabecera de lista de precios (LP001, LP002, ...)
     @ccod_lin          familia (Articulos.ccod_lin); '' o '%%%' = todas
     @ccod_unidadmedida unidad (Articulos.uni_medi);  '' o '%%%' = todas
     @ccod_articulo     filtro exacto sobre el codigo de articulo (LIKE)
     @cdsc_articulo     filtro LIKE sobre la descripcion
     @CodCia            tenant

   Devuelve: ccod_articulo, cdsc_articulo  (orden por descripcion).
   El API se encarga de agregar la columna 'cbx' (radio button) al payload.
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 915 / FIX 68: CargarArticuloListPrecio (modal) ==';

IF OBJECT_ID('webDatpos_CargarArticuloListPrecio','P') IS NOT NULL
    DROP PROCEDURE webDatpos_CargarArticuloListPrecio;
GO
CREATE PROCEDURE webDatpos_CargarArticuloListPrecio
    @ccod_cblistpre    VARCHAR(20)  = '',
    @ccod_articulo     VARCHAR(50)  = '',
    @cdsc_articulo     VARCHAR(200) = '',
    @ccod_lin          VARCHAR(20)  = '',
    @ccod_unidadmedida VARCHAR(20)  = '',
    @CodCia            VARCHAR(20)  = 'EMP01'
AS
BEGIN
    SET NOCOUNT ON;

    SELECT DISTINCT
        L.ccod_articulo,
        ISNULL(A.cdsc_articulo,'') AS cdsc_articulo
    FROM LnListaPrecio L
    LEFT JOIN Articulos A
        ON A.ccod_cia = L.ccod_cia
       AND A.ccod_articulo = L.ccod_articulo
    WHERE L.ccod_cia = @CodCia
      AND (@ccod_cblistpre = '' OR @ccod_cblistpre = '%%%'
           OR L.ccod_cblistpre = @ccod_cblistpre)
      AND (@ccod_lin = '' OR @ccod_lin = '%%%'
           OR ISNULL(A.ccod_lin,'') = @ccod_lin)
      AND (@ccod_unidadmedida = '' OR @ccod_unidadmedida = '%%%'
           OR ISNULL(A.uni_medi,'') = @ccod_unidadmedida)
      AND (@ccod_articulo = ''
           OR L.ccod_articulo LIKE '%' + @ccod_articulo + '%')
      AND (@cdsc_articulo = ''
           OR ISNULL(A.cdsc_articulo,'') LIKE '%' + @cdsc_articulo + '%')
      AND ISNULL(A.cstatus,'A') = 'A'
    ORDER BY ISNULL(A.cdsc_articulo,''), L.ccod_articulo;
END
GO
PRINT '  -> webDatpos_CargarArticuloListPrecio (canonico) creado.';

/* Alias para el nombre legacy que la API tenia antes de FIX_68 */
IF OBJECT_ID('sp_cargararticulolistprecio','P') IS NOT NULL
    DROP PROCEDURE sp_cargararticulolistprecio;
GO
CREATE PROCEDURE sp_cargararticulolistprecio
    @ccod_cia          VARCHAR(20),
    @ccod_cblistpre    VARCHAR(20)  = '',
    @ccod_articulo     VARCHAR(50)  = '',
    @cdsc_articulo     VARCHAR(200) = '',
    @ccod_lin          VARCHAR(20)  = '',
    @ccod_unidadmedida VARCHAR(20)  = ''
AS
BEGIN
    SET NOCOUNT ON;
    EXEC webDatpos_CargarArticuloListPrecio
        @ccod_cblistpre,
        @ccod_articulo,
        @cdsc_articulo,
        @ccod_lin,
        @ccod_unidadmedida,
        @ccod_cia;
END
GO
PRINT '  -> sp_cargararticulolistprecio (alias) creado.';

PRINT '== MODIFY 915 / FIX 68 completado. ==';
GO

/* Verificacion informativa: articulos cargados en LP001 (familia/UM
   todas). Debe coincidir con LnListaPrecio para LP001 de EMP01. */
PRINT '== Verificacion (LP001, EMP01) ==';
EXEC webDatpos_CargarArticuloListPrecio
    @ccod_cblistpre = 'LP001',
    @CodCia = 'EMP01';
GO
