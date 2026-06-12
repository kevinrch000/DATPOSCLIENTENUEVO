/* =========================================================================
   FIX_54 — sp_consultararticulospricipal
   =========================================================================
   SÍNTOMA:  ConsultaArticulos devuelve [] aunque haya artículos en la BD.
   CAUSA 1:  El SP no existía (MODIFY_930 no se ejecutó).
   CAUSA 2:  El JS envía '%%%' como "Todos" pero el SP no lo interpreta
             como filtro vacío → lo trata como valor real, resultado vacío.
             (La API PHP ya fue corregida para convertir '%%%' → '' antes
              de llamar al SP, pero el SP también debe ser robusto.)

   Ejecutar en DatPos_EMP01
   ========================================================================= */

USE DatPos_EMP01;
GO

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
    @id_articulo   VARCHAR(20)  = ''
AS
BEGIN
    SET NOCOUNT ON;

    /* Normalizar '%%%' a '' (el JS lo usa como señal de "Todos") */
    SET @CodArticulo = CASE WHEN @CodArticulo = '%%%' THEN '' ELSE ISNULL(@CodArticulo,'') END;
    SET @NomAticulo  = CASE WHEN @NomAticulo  = '%%%' THEN '' ELSE ISNULL(@NomAticulo,'')  END;
    SET @TipArticulo = CASE WHEN @TipArticulo = '%%%' THEN '' ELSE ISNULL(@TipArticulo,'') END;
    SET @Tributos    = CASE WHEN @Tributos    = '%%%' THEN '' ELSE ISNULL(@Tributos,'')    END;
    SET @Familia     = CASE WHEN @Familia     = '%%%' THEN '' ELSE ISNULL(@Familia,'')     END;
    SET @UniMedida   = CASE WHEN @UniMedida   = '%%%' THEN '' ELSE ISNULL(@UniMedida,'')   END;
    /* Estado: convertir '1'→'A' y '0'→'I' por si la API no lo hizo */
    SET @Estado = CASE
        WHEN @Estado = '%%%' THEN ''
        WHEN @Estado = '1'   THEN 'A'
        WHEN @Estado = '0'   THEN 'I'
        ELSE ISNULL(@Estado,'')
    END;

    SELECT
        A.ccod_articulo                                              AS ccod_articulo,
        ISNULL(A.cdsc_articulo,'')                                   AS cdsc_articulo,
        ISNULL(L.cdsc_lin, ISNULL(A.ccod_lin,''))                    AS linea,
        ISNULL(U.cdsc_unimed, ISNULL(A.uni_medi,''))                 AS uni_medi,
        ISNULL(A.ctip_articulo,'')                                   AS ctip_articulo,
        CASE WHEN A.cstatus='A' THEN 'Activo' ELSE 'Inactivo' END   AS estado,
        ISNULL(A.cigv,'')                                            AS cigv
    FROM Articulos A
    LEFT JOIN Familias L
        ON L.ccod_cia = A.ccod_cia AND L.ccod_lin = A.ccod_lin
    LEFT JOIN UnidadMedida U
        ON U.ccod_cia = A.ccod_cia AND U.ccod_unimed = A.uni_medi
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

PRINT 'OK - FIX_54: sp_consultararticulospricipal creado/actualizado.';
GO
