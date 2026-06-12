/* =====================================================================
   MODIFY_950_FIX_79_Facturacion_Duplicates.sql
   =====================================================================
   Motivo: sp_consultararticulocategoria, sp_consultarfavoritos, 
           sp_consultararticulotodos, sp_consultararticuloprecio y
           sp_consultararticulopreciocodigo hacían LEFT JOIN con 
           CbListaPrecio con la condición cstatus='A' sin filtrar por la
           lista de precios asociada a la tienda del usuario. 
           Esto hacía que se duplicaran los artículos en pantalla 
           cuando existían múltiples listas de precios activas (ej: General y Mayorista).
   Solución: Resolver dinámicamente @ccod_cblistpre a partir de la 
             tienda del usuario (nlista_pre_normal) y usar esa lista específica.
   ===================================================================== */
USE DatPos_EMP01;
GO

/* 1. ARTÍCULOS POR CATEGORÍA — sp_consultararticulocategoria */
IF OBJECT_ID('sp_consultararticulocategoria','P') IS NOT NULL DROP PROCEDURE sp_consultararticulocategoria;
GO
CREATE PROCEDURE sp_consultararticulocategoria
    @ccod_cia VARCHAR(20), @codigo INT, @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @ccod_lin VARCHAR(20) = (SELECT ccod_lin FROM Familias WHERE ccod_cia=@ccod_cia AND id_lin=@codigo);
    
    DECLARE @ccod_cblistpre VARCHAR(20);
    SELECT @ccod_cblistpre = CB.ccod_cblistpre
    FROM Usuarios U
    JOIN Tiendas T ON T.ccod_cia = U.ccod_empresa AND T.ccod_tiend = U.ccod_tiend
    JOIN CbListaPrecio CB ON CB.ccod_cia = U.ccod_empresa AND CB.id_cblistpre = T.nlista_pre_normal
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;

    IF @ccod_cblistpre IS NULL
    BEGIN
        SELECT TOP 1 @ccod_cblistpre = ccod_cblistpre
        FROM CbListaPrecio
        WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
        ORDER BY ccod_cblistpre;
    END

    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_lin=@ccod_lin AND A.cstatus='A'
    ORDER BY A.cdsc_articulo;
END
GO

/* 2. FAVORITOS — sp_consultarfavoritos */
IF OBJECT_ID('sp_consultarfavoritos','P') IS NOT NULL DROP PROCEDURE sp_consultarfavoritos;
GO
CREATE PROCEDURE sp_consultarfavoritos
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @ccod_cblistpre VARCHAR(20);
    SELECT @ccod_cblistpre = CB.ccod_cblistpre
    FROM Usuarios U
    JOIN Tiendas T ON T.ccod_cia = U.ccod_empresa AND T.ccod_tiend = U.ccod_tiend
    JOIN CbListaPrecio CB ON CB.ccod_cia = U.ccod_empresa AND CB.id_cblistpre = T.nlista_pre_normal
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;

    IF @ccod_cblistpre IS NULL
    BEGIN
        SELECT TOP 1 @ccod_cblistpre = ccod_cblistpre
        FROM CbListaPrecio
        WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
        ORDER BY ccod_cblistpre;
    END

    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.bprefer=1 AND A.cstatus='A'
    ORDER BY A.cdsc_articulo;
END
GO

/* 3. BÚSQUEDA LIBRE — sp_consultararticulotodos */
IF OBJECT_ID('sp_consultararticulotodos','P') IS NOT NULL DROP PROCEDURE sp_consultararticulotodos;
GO
CREATE PROCEDURE sp_consultararticulotodos
    @ccod_cia VARCHAR(20), @texto VARCHAR(100), @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @ccod_cblistpre VARCHAR(20);
    SELECT @ccod_cblistpre = CB.ccod_cblistpre
    FROM Usuarios U
    JOIN Tiendas T ON T.ccod_cia = U.ccod_empresa AND T.ccod_tiend = U.ccod_tiend
    JOIN CbListaPrecio CB ON CB.ccod_cia = U.ccod_empresa AND CB.id_cblistpre = T.nlista_pre_normal
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;

    IF @ccod_cblistpre IS NULL
    BEGIN
        SELECT TOP 1 @ccod_cblistpre = ccod_cblistpre
        FROM CbListaPrecio
        WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
        ORDER BY ccod_cblistpre;
    END

    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A'
      AND (A.cdsc_articulo LIKE '%'+@texto+'%' OR A.ccod_articulo LIKE '%'+@texto+'%')
    ORDER BY A.cdsc_articulo;
END
GO

/* 4. PRECIO POR ID — sp_consultararticuloprecio */
IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL DROP PROCEDURE sp_consultararticuloprecio;
GO
CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),
    @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @ccod_cblistpre VARCHAR(20);
    SELECT @ccod_cblistpre = CB.ccod_cblistpre
    FROM Usuarios U
    JOIN Tiendas T ON T.ccod_cia = U.ccod_empresa AND T.ccod_tiend = U.ccod_tiend
    JOIN CbListaPrecio CB ON CB.ccod_cia = U.ccod_empresa AND CB.id_cblistpre = T.nlista_pre_normal
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;

    IF @ccod_cblistpre IS NULL
    BEGIN
        SELECT TOP 1 @ccod_cblistpre = ccod_cblistpre
        FROM CbListaPrecio
        WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
        ORDER BY ccod_cblistpre;
    END

    SELECT ISNULL(L.npre_uni, 0)           AS npre_uni,
           A.cdsc_articulo,
           ISNULL(C.nigv, 18)              AS igv,
           ISNULL(C.nisc, 0)               AS isc,
           A.ctip_articulo,
           A.cstatus,
           ISNULL(L.npre_uni, 0)           AS npre_costo,
           ISNULL(L.ndes_max, 0)           AS ndes_max,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C  ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.id_articulo=CAST(@codigo AS INT);
END
GO

/* 5. PRECIO POR CÓDIGO — sp_consultararticulopreciocodigo */
IF OBJECT_ID('sp_consultararticulopreciocodigo','P') IS NOT NULL DROP PROCEDURE sp_consultararticulopreciocodigo;
GO
CREATE PROCEDURE sp_consultararticulopreciocodigo
    @ccod_cia     VARCHAR(20),
    @ccod_usuario VARCHAR(50),
    @codigo       VARCHAR(50),
    @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @ccod_cblistpre VARCHAR(20);
    DECLARE @id_cblistpre INT;
    
    SELECT @ccod_cblistpre = CB.ccod_cblistpre, @id_cblistpre = CB.id_cblistpre
    FROM Usuarios U
    JOIN Tiendas T ON T.ccod_cia = U.ccod_empresa AND T.ccod_tiend = U.ccod_tiend
    JOIN CbListaPrecio CB ON CB.ccod_cia = U.ccod_empresa AND CB.id_cblistpre = T.nlista_pre_normal
    WHERE U.ccod_empresa = @ccod_cia AND U.ccod_usuario = @ccod_usuario;

    IF @ccod_cblistpre IS NULL
    BEGIN
        SELECT TOP 1 @ccod_cblistpre = ccod_cblistpre, @id_cblistpre = id_cblistpre
        FROM CbListaPrecio
        WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
        ORDER BY ccod_cblistpre;
    END

    SELECT ISNULL(L.npre_uni, 0)           AS npre_uni,
           A.cdsc_articulo,
           ISNULL(C.nigv, 18)              AS igv,
           ISNULL(C.nisc, 0)               AS isc,
           A.ctip_articulo,
           A.cstatus,
           ISNULL(L.npre_uni, 0)           AS npre_costo,
           ISNULL(L.ndes_max, 0)           AS ndes_max,
           @id_cblistpre                   AS id_cblistpre,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C  ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@codigo AND A.cstatus='A';
END
GO

PRINT 'MODIFY_950 aplicado correctamente.';
