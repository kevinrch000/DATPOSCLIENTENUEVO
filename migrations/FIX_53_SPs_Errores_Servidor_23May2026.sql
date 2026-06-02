/* =========================================================================
   FIX_53 — SPs faltantes / errores del servidor identificados el 23/05/2026
   =========================================================================

   ERRORES REPORTADOS:
     E1. sp_consultastockminimoprincipal      — SQLSTATE 42000, Code 2812
     E2. sp_cargararticulosolobienes          — SQLSTATE 42000, Code 2812
     E3. sp_consultararticuloprecio           — Conversión varchar→int (Code 245)
     E4. webDatpos_verificarAccesos           — Falta @ccod_cia (Code 201)

   SOLUCIÓN:
     E1. Se creó en FIX_52 / MODIFY_920. Ejecutar MODIFY_920_FIX_69_SPs_Faltantes.sql.
     E2. Se creó en FIX_52 / MODIFY_920. Ejecutar MODIFY_920_FIX_69_SPs_Faltantes.sql.
     E3. Se recrea aquí sp_consultararticuloprecio sin CAST forzado a INT.
     E4. Se recrea aquí webDatpos_verificarAccesos con @ccod_cia opcional.

   ORDEN DE EJECUCIÓN RECOMENDADO:
     1. MODIFY_920_FIX_69_SPs_Faltantes.sql   (E1, E2)
     2. Este archivo FIX_53                   (E3, E4)
     3. MODIFY_924_FIX_73_DataTablesAndKardex.sql
     4. Resto de migrations en orden numérico.

   Ejecutar en DatPos_EMP01
   ========================================================================= */

USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== FIX_53: sp_consultararticuloprecio (E3) + webDatpos_verificarAccesos (E4) ==';
GO

/* --------------------------------------------------------------------- */
/* E3. sp_consultararticuloprecio — Conversión varchar→int               */
/* --------------------------------------------------------------------- */
/* Causa: el WHERE usaba A.id_articulo = CAST(@codigo AS INT), pero
   @codigo puede recibir el código alfanumérico del artículo (ej. 'ART003')
   causando fallo de conversión. Se corrige buscando por ccod_articulo
   con fallback numérico solo si ISNUMERIC(@codigo)=1.                    */

IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL
    DROP PROCEDURE sp_consultararticuloprecio;
GO

CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),    -- ccod_articulo (alfanumérico) O id_articulo (numérico)
    @ccod_almacen VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    -- El costo vive en Stock (ncosto), no en Articulos.
    SELECT TOP 1
        ISNULL(L.npre_uni, 0)                              AS npre_uni,      -- [0]
        ISNULL(A.cdsc_articulo, '')                        AS cdsc_articulo, -- [1]
        ISNULL(C.nigv, 18)                                 AS igv,           -- [2]
        ISNULL(C.nisc, 0)                                  AS isc,           -- [3]
        A.ctip_articulo,                                                      -- [4]
        A.cstatus,                                                            -- [5]
        ISNULL(S.ncosto, 0)                                AS npre_costo,    -- [6]
        ISNULL(L.ndes_max, 0)                              AS ndes_max       -- [7]
    FROM Articulos A
    LEFT JOIN (
        SELECT L2.ccod_articulo, L2.npre_uni, L2.ndes_max
        FROM CbListaPrecio CB2
        JOIN LnListaPrecio L2
            ON L2.ccod_cia = CB2.ccod_cia
           AND L2.ccod_cblistpre = CB2.ccod_cblistpre
        WHERE CB2.ccod_cia = @ccod_cia AND CB2.cstatus = 'A'
    ) L ON L.ccod_articulo = A.ccod_articulo
    LEFT JOIN Stock S
        ON S.ccod_cia      = A.ccod_cia
       AND S.ccod_articulo = A.ccod_articulo
       AND S.ccod_alm      = @ccod_almacen
    LEFT JOIN ConfigGeneral C ON C.ccod_cia = A.ccod_cia
    WHERE A.ccod_cia = @ccod_cia
      AND (
          A.ccod_articulo = @codigo
          OR (ISNUMERIC(@codigo) = 1
              AND A.id_articulo = CAST(CASE WHEN ISNUMERIC(@codigo)=1
                                           THEN @codigo ELSE '0' END AS INT))
      )
      AND A.cstatus = 'A';
END
GO

PRINT '  [E3] sp_consultararticuloprecio recreado sin CAST forzado a INT.';
GO

/* --------------------------------------------------------------------- */
/* E4. webDatpos_verificarAccesos — @ccod_cia faltante                   */
/* --------------------------------------------------------------------- */
/* El error "Expects parameter '@ccod_cia', which was not supplied"
   indica que la versión en BD no tenía @ccod_cia con default,
   o el SP no existía con esa firma exacta.
   Se recrea con @ccod_cia = '' como default para retrocompatibilidad. */

IF OBJECT_ID('webDatpos_verificarAccesos','P') IS NOT NULL
    DROP PROCEDURE webDatpos_verificarAccesos;
GO

CREATE PROCEDURE webDatpos_verificarAccesos
    @ccod_cia  VARCHAR(20) = '',
    @id_rol    INT         = 0,
    @id_menu   VARCHAR(20) = ''
AS
BEGIN
    SET NOCOUNT ON;
    -- Devuelve 1 fila si el rol tiene acceso al menú indicado.
    -- Si @id_rol = 0 o @ccod_cia vacío → sin acceso (array vacío).
    IF @id_rol = 0 OR @ccod_cia = ''
    BEGIN
        SELECT CAST(0 AS INT) AS tiene_acceso WHERE 1 = 0; -- vacío
        RETURN;
    END

    SELECT TOP 1
        A.corden      AS tiene_acceso
    FROM Accesos A
    JOIN Menu M ON M.corden = A.corden AND M.ccod_cia = A.ccod_cia
    WHERE A.ccod_cia = @ccod_cia
      AND A.id_rol   = @id_rol
      AND (CAST(M.id_menu AS VARCHAR(20)) = @id_menu OR @id_menu = '');
END
GO

PRINT '  [E4] webDatpos_verificarAccesos recreado con @ccod_cia default.';
GO

PRINT 'OK - FIX_53 completo.';
GO
