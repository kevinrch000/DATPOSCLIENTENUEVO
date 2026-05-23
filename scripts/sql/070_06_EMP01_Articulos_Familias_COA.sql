/* PARTE 6: Artículos, Familias, Unidad Medida, COA */
USE DatPos_EMP01;
GO

/* FAMILIAS */
IF OBJECT_ID('sp_consultafamilias','P') IS NOT NULL DROP PROCEDURE sp_consultafamilias; 
GO
CREATE PROCEDURE sp_consultafamilias @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT id_lin,ccod_lin,cdsc_lin,cstatus,ccolor FROM Familias WHERE ccod_cia=@ccod_cia ORDER BY ccod_lin; END
GO

IF OBJECT_ID('sp_consultafamiliasactivas','P') IS NOT NULL DROP PROCEDURE sp_consultafamiliasactivas; 
GO
CREATE PROCEDURE sp_consultafamiliasactivas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_lin,cdsc_lin,ccolor FROM Familias WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY ccod_lin; END
GO

IF OBJECT_ID('sp_consultafamilia','P') IS NOT NULL DROP PROCEDURE sp_consultafamilia; 
GO
CREATE PROCEDURE sp_consultafamilia @ccod_cia VARCHAR(20), @codigo VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM Familias WHERE ccod_cia=@ccod_cia AND ccod_lin=@codigo; END
GO

IF OBJECT_ID('webDatpos_insertarFamilia','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarFamilia; 
GO
CREATE PROCEDURE webDatpos_insertarFamilia
    @ccod_lin VARCHAR(20), @ccod_cia VARCHAR(20), @cdsc_lin VARCHAR(100),
    @cstatus VARCHAR(1), @ccolor VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Familias WHERE ccod_cia=@ccod_cia AND ccod_lin=@ccod_lin)
        INSERT INTO Familias (ccod_lin,ccod_cia,cdsc_lin,cstatus,ccolor,ccod_usuario) VALUES (@ccod_lin,@ccod_cia,@cdsc_lin,@cstatus,@ccolor,@ccod_usuario);
    SELECT 'OK' AS respuesta;
END
GO

IF OBJECT_ID('sp_editarfamilia','P') IS NOT NULL DROP PROCEDURE sp_editarfamilia; 
GO
CREATE PROCEDURE sp_editarfamilia
    @ccod_lin VARCHAR(20), @ccod_cia VARCHAR(20), @cdsc_lin VARCHAR(100),
    @cstatus VARCHAR(1), @ccolor VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Familias SET cdsc_lin=@cdsc_lin,cstatus=@cstatus,ccolor=@ccolor,ccod_usuario=@ccod_usuario
    WHERE ccod_cia=@ccod_cia AND ccod_lin=@ccod_lin;
    SELECT 'OK' AS respuesta;
END
GO

IF OBJECT_ID('sp_eliminarfamilia','P') IS NOT NULL DROP PROCEDURE sp_eliminarfamilia; 
GO
CREATE PROCEDURE sp_eliminarfamilia @ccod_lin VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; UPDATE Familias SET cstatus='I' WHERE ccod_cia=@ccod_cia AND ccod_lin=@ccod_lin; SELECT 'OK' AS respuesta; END
GO

/* UNIDAD MEDIDA */
IF OBJECT_ID('webDatpos_consultarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarUnidadMedida; 
GO
CREATE PROCEDURE webDatpos_consultarUnidadMedida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT id_unimed,ccod_unimed,cdsc_unimed,cstatus FROM UnidadMedida WHERE ccod_cia=@ccod_cia ORDER BY ccod_unimed; END
GO

IF OBJECT_ID('webDatpos_consultarCodigoUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCodigoUnidadMedida; 
GO
CREATE PROCEDURE webDatpos_consultarCodigoUnidadMedida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_unimed,cdsc_unimed FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY ccod_unimed; END
GO

IF OBJECT_ID('webDatpos_insertarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarUnidadMedida; 
GO
CREATE PROCEDURE webDatpos_insertarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_unimed VARCHAR(10), @cdsc_unimed VARCHAR(50), @cstatus VARCHAR(1), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unimed)
        INSERT INTO UnidadMedida (ccod_cia,ccod_unimed,cdsc_unimed,cstatus,ccod_usuario) VALUES (@ccod_cia,@ccod_unimed,@cdsc_unimed,@cstatus,@ccod_usuario);
END
GO

IF OBJECT_ID('webDatpos_editarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_editarUnidadMedida; 
GO
CREATE PROCEDURE webDatpos_editarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_unimed VARCHAR(10), @cdsc_unimed VARCHAR(50), @cstatus VARCHAR(1), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE UnidadMedida SET cdsc_unimed=@cdsc_unimed,cstatus=@cstatus,ccod_usuario=@ccod_usuario
    WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unimed;
END
GO

IF OBJECT_ID('sp_eliminarUnidadMedida','P') IS NOT NULL DROP PROCEDURE sp_eliminarUnidadMedida; 
GO
CREATE PROCEDURE sp_eliminarUnidadMedida @ccod_cia VARCHAR(20), @ccod_unimed VARCHAR(10)
AS BEGIN SET NOCOUNT ON; UPDATE UnidadMedida SET cstatus='I' WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unimed; END
GO

/* VARIANTES */
IF OBJECT_ID('sp_consultarvariantesactivas','P') IS NOT NULL DROP PROCEDURE sp_consultarvariantesactivas; 
GO
CREATE PROCEDURE sp_consultarvariantesactivas @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT id_cbvariante,cdsc_variante FROM CbVariante WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo;
END
GO

IF OBJECT_ID('sp_consultarsubvariantesactivas','P') IS NOT NULL DROP PROCEDURE sp_consultarsubvariantesactivas; 
GO
CREATE PROCEDURE sp_consultarsubvariantesactivas @ccod_cia VARCHAR(20), @id_cbvariante INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_lnvariante,cdsc_lnvariante FROM LnVariante WHERE ccod_cia=@ccod_cia AND id_cbvariante=@id_cbvariante;
END
GO

IF OBJECT_ID('webDatpos_insertarCbVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarCbVariante; 
GO
CREATE PROCEDURE webDatpos_insertarCbVariante
    @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @cdsc_variante VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO CbVariante (ccod_cia,ccod_articulo,cdsc_variante,ccod_usuario) VALUES (@ccod_cia,@ccod_articulo,@cdsc_variante,@ccod_usuario);
    SELECT SCOPE_IDENTITY() AS id_cbvariante;
END
GO

IF OBJECT_ID('webDatpos_editarCbVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_editarCbVariante; 
GO
CREATE PROCEDURE webDatpos_editarCbVariante
    @ccod_cia VARCHAR(20), @id_cbvariante INT, @cdsc_variante VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE CbVariante SET cdsc_variante=@cdsc_variante,ccod_usuario=@ccod_usuario WHERE ccod_cia=@ccod_cia AND id_cbvariante=@id_cbvariante;
END
GO

IF OBJECT_ID('webDatpos_eliminarCbVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_eliminarCbVariante; 
GO
CREATE PROCEDURE webDatpos_eliminarCbVariante @ccod_cia VARCHAR(20), @id_cbvariante INT
AS BEGIN SET NOCOUNT ON;
    DELETE FROM LnVariante WHERE ccod_cia=@ccod_cia AND id_cbvariante=@id_cbvariante;
    DELETE FROM CbVariante WHERE ccod_cia=@ccod_cia AND id_cbvariante=@id_cbvariante;
END
GO

IF OBJECT_ID('webDatpos_insertarLNVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarLNVariante; 
GO
CREATE PROCEDURE webDatpos_insertarLNVariante
    @ccod_cia VARCHAR(20), @id_cbvariante INT, @cdsc_lnvariante VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnVariante (ccod_cia,id_cbvariante,cdsc_lnvariante,ccod_usuario) VALUES (@ccod_cia,@id_cbvariante,@cdsc_lnvariante,@ccod_usuario);
END
GO

IF OBJECT_ID('webDatpos_editarLNVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_editarLNVariante; 
GO
CREATE PROCEDURE webDatpos_editarLNVariante
    @ccod_cia VARCHAR(20), @id_lnvariante INT, @cdsc_lnvariante VARCHAR(100), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE LnVariante SET cdsc_lnvariante=@cdsc_lnvariante,ccod_usuario=@ccod_usuario WHERE ccod_cia=@ccod_cia AND id_lnvariante=@id_lnvariante;
END
GO

IF OBJECT_ID('webDatpos_eliminarLNVariante','P') IS NOT NULL DROP PROCEDURE webDatpos_eliminarLNVariante; 
GO
CREATE PROCEDURE webDatpos_eliminarLNVariante @ccod_cia VARCHAR(20), @id_lnvariante INT
AS BEGIN SET NOCOUNT ON;
    DELETE FROM LnVariante WHERE ccod_cia=@ccod_cia AND id_lnvariante=@id_lnvariante;
END
GO

/* COA (Clientes/Proveedores) */
IF OBJECT_ID('sp_consultaclientes','P') IS NOT NULL DROP PROCEDURE sp_consultaclientes; 
GO
/* sp_consultaclientes: 17 columnas alineadas con VB ConsultarClientes()
   y con el endpoint api/configgeneral_api.php → CargarCliente
   (ccod_coa = [2], cdsc_coa = [4]).
   Ver tambien: scripts/sql/410_FIX_20_Clientes_ConfigGeneral.sql */
CREATE PROCEDURE sp_consultaclientes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_coa,                                    -- [0]
        ccod_cia,                                  -- [1]
        ccod_coa,                                  -- [2]
        ISNULL(cdoc_coa,'')    AS cdoc_coa,        -- [3]
        ISNULL(cdsc_coa,'')    AS cdsc_coa,        -- [4]
        ISNULL(ctelf,'')       AS ctelf,           -- [5]
        ISNULL(cmail,'')       AS cmail,           -- [6]
        ISNULL(ctipo_coa,'')   AS destipo_coa,     -- [7]
        cdirc_coa,                                 -- [8]
        cdistrito,                                 -- [9]
        cprovincia,                                -- [10]
        cdepartamento,                             -- [11]
        cpais,                                     -- [12]
        ISNULL(cstatus,'A')    AS estado,          -- [13]
        ISNULL(cproveedor,'0') AS cproveedor,      -- [14]
        ISNULL(cdoc_coa,'')    AS ctip_doc,        -- [15]
        ISNULL(cruc_coa,'')    AS cruc_coa         -- [16]
    FROM Coa
    WHERE ccod_cia=@ccod_cia
    ORDER BY cdsc_coa;
END
GO

IF OBJECT_ID('sp_consultarclientestodos','P') IS NOT NULL DROP PROCEDURE sp_consultarclientestodos; 
GO
CREATE PROCEDURE sp_consultarclientestodos @ccod_cia VARCHAR(20), @texto VARCHAR(100), @ccod_usuario VARCHAR(50), @tipodoc VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_coa,cdsc_coa,cdoc_coa,cruc_coa,ctelf,cdirc_coa FROM Coa
    WHERE ccod_cia=@ccod_cia AND cstatus='A'
      AND (cdsc_coa LIKE '%'+@texto+'%' OR cdoc_coa LIKE '%'+@texto+'%' OR cruc_coa LIKE '%'+@texto+'%');
END
GO

IF OBJECT_ID('webDatpos_ConsultaCliente','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaCliente; 
GO
CREATE PROCEDURE webDatpos_ConsultaCliente @ccod_cia VARCHAR(20), @codigo VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT * FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@codigo;
END
GO

IF OBJECT_ID('webDatpos_cargarClientePredeterminado','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarClientePredeterminado; 
GO
CREATE PROCEDURE webDatpos_cargarClientePredeterminado @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT TOP 1 ccod_coa,cdsc_coa,cdoc_coa,cruc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND cdoc_coa='00000000' AND cstatus='A';
END
GO

IF OBJECT_ID('sp_clientepordefecto','P') IS NOT NULL DROP PROCEDURE sp_clientepordefecto; 
GO
CREATE PROCEDURE sp_clientepordefecto @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT TOP 1 ccod_coa,cdsc_coa,cdoc_coa,cruc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND cdoc_coa='00000000' AND cstatus='A';
END
GO

IF OBJECT_ID('webDatpos_insertarclientes','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarclientes; 
GO
CREATE PROCEDURE webDatpos_insertarclientes
    @ccod_cia VARCHAR(20), @ccod_coa VARCHAR(20), @cdoc_coa VARCHAR(20), @cdsc_coa VARCHAR(200),
    @ctelf VARCHAR(20), @cmail VARCHAR(100), @ctipo_coa VARCHAR(10), @cpais VARCHAR(50),
    @cdepartamento VARCHAR(2), @cprovincia VARCHAR(4), @cdistrito VARCHAR(6),
    @cdirc_coa VARCHAR(200), @cstatus VARCHAR(1), @cproveedor VARCHAR(1),
    @ccod_usuario VARCHAR(50), @ctip_doc VARCHAR(10), @cruc_coa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa)
        INSERT INTO Coa (ccod_cia,ccod_coa,cdoc_coa,cdsc_coa,ctelf,cmail,ctipo_coa,cpais,
            cdepartamento,cprovincia,cdistrito,cdirc_coa,cstatus,cproveedor,ccod_usuario,cruc_coa)
        VALUES (@ccod_cia,@ccod_coa,@cdoc_coa,@cdsc_coa,@ctelf,@cmail,@ctipo_coa,@cpais,
            @cdepartamento,@cprovincia,@cdistrito,@cdirc_coa,@cstatus,@cproveedor,@ccod_usuario,@cruc_coa);
END
GO

IF OBJECT_ID('webDatpos_editarclientes','P') IS NOT NULL DROP PROCEDURE webDatpos_editarclientes; 
GO
CREATE PROCEDURE webDatpos_editarclientes
    @ccod_cia VARCHAR(20), @ccod_coa VARCHAR(20), @cdoc_coa VARCHAR(20), @cdsc_coa VARCHAR(200),
    @ctelf VARCHAR(20), @cmail VARCHAR(100), @ctipo_coa VARCHAR(10), @cpais VARCHAR(50),
    @cdepartamento VARCHAR(2), @cprovincia VARCHAR(4), @cdistrito VARCHAR(6),
    @cdirc_coa VARCHAR(200), @cstatus VARCHAR(1), @cproveedor VARCHAR(1),
    @ccod_usuario VARCHAR(50), @ctip_doc VARCHAR(10), @cruc_coa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    UPDATE Coa SET cdoc_coa=@cdoc_coa,cdsc_coa=@cdsc_coa,ctelf=@ctelf,cmail=@cmail,
        ctipo_coa=@ctipo_coa,cpais=@cpais,cdepartamento=@cdepartamento,cprovincia=@cprovincia,
        cdistrito=@cdistrito,cdirc_coa=@cdirc_coa,cstatus=@cstatus,cproveedor=@cproveedor,
        ccod_usuario=@ccod_usuario,cruc_coa=@cruc_coa
    WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa;
END
GO

IF OBJECT_ID('sp_eliminarcliente','P') IS NOT NULL DROP PROCEDURE sp_eliminarcliente; 
GO
CREATE PROCEDURE sp_eliminarcliente @ccod_cia VARCHAR(20), @ccod_coa VARCHAR(20)
AS BEGIN SET NOCOUNT ON; UPDATE Coa SET cstatus='I' WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa; END
GO

/* ARTICULOS */
IF OBJECT_ID('sp_consultararticulos','P') IS NOT NULL DROP PROCEDURE sp_consultararticulos; 
GO
CREATE PROCEDURE sp_consultararticulos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.id_articulo,A.ccod_articulo,A.cdsc_articulo,A.ccod_lin,F.cdsc_lin,
           A.uni_medi,A.cstatus,A.ctip_articulo,A.cigv,A.cisc,A.ccod_artSunat,A.bprefer
    FROM Articulos A LEFT JOIN Familias F ON F.ccod_lin=A.ccod_lin AND F.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia ORDER BY A.cdsc_articulo;
END
GO

IF OBJECT_ID('sp_consultararticulo','P') IS NOT NULL DROP PROCEDURE sp_consultararticulo; 
GO
CREATE PROCEDURE sp_consultararticulo @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT * FROM Articulos WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo;
END
GO

IF OBJECT_ID('sp_consultararticulosactivos','P') IS NOT NULL DROP PROCEDURE sp_consultararticulosactivos; 
GO
CREATE PROCEDURE sp_consultararticulosactivos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_articulo,cdsc_articulo,ccod_lin,uni_medi FROM Articulos WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY cdsc_articulo;
END
GO

IF OBJECT_ID('webDatpos_insertar_Articulo','P') IS NOT NULL DROP PROCEDURE webDatpos_insertar_Articulo; 
GO
CREATE PROCEDURE webDatpos_insertar_Articulo
    @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @cdsc_articulo VARCHAR(200),
    @ccod_lin VARCHAR(20), @uni_medi VARCHAR(10), @cstatus VARCHAR(1),
    @ctip_articulo VARCHAR(10), @cigv VARCHAR(5), @cisc VARCHAR(5),
    @iimage VARBINARY(MAX), @ccod_artSunat VARCHAR(20),
    @nstock_max DECIMAL(18,4), @nstock_min DECIMAL(18,4),
    @ctipo_isc VARCHAR(5), @nporcentaje_isc DECIMAL(18,4), @nmonto_isc DECIMAL(18,4),
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Articulos WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo)
        INSERT INTO Articulos (ccod_cia,ccod_articulo,cdsc_articulo,ccod_lin,uni_medi,cstatus,
            ctip_articulo,cigv,cisc,iimage,ccod_artSunat,nstock_max,nstock_min,
            ctipo_isc,nporcentaje_isc,nmonto_isc,ccod_usuario)
        VALUES (@ccod_cia,@ccod_articulo,@cdsc_articulo,@ccod_lin,@uni_medi,@cstatus,
            @ctip_articulo,@cigv,@cisc,@iimage,@ccod_artSunat,@nstock_max,@nstock_min,
            @ctipo_isc,@nporcentaje_isc,@nmonto_isc,@ccod_usuario);
    SELECT SCOPE_IDENTITY() AS id_articulo;
END
GO

IF OBJECT_ID('webDatpos_editarArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_editarArticulo; 
GO
CREATE PROCEDURE webDatpos_editarArticulo
    @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @cdsc_articulo VARCHAR(200),
    @ccod_lin VARCHAR(20), @uni_medi VARCHAR(10), @cstatus VARCHAR(1),
    @ctip_articulo VARCHAR(10), @cigv VARCHAR(5), @cisc VARCHAR(5),
    @iimage VARBINARY(MAX), @ccod_artSunat VARCHAR(20),
    @nstock_max DECIMAL(18,4), @nstock_min DECIMAL(18,4),
    @ctipo_isc VARCHAR(5), @nporcentaje_isc DECIMAL(18,4), @nmonto_isc DECIMAL(18,4),
    @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Articulos SET cdsc_articulo=@cdsc_articulo,ccod_lin=@ccod_lin,uni_medi=@uni_medi,
        cstatus=@cstatus,ctip_articulo=@ctip_articulo,cigv=@cigv,cisc=@cisc,
        iimage=ISNULL(@iimage,iimage),ccod_artSunat=@ccod_artSunat,
        nstock_max=@nstock_max,nstock_min=@nstock_min,ctipo_isc=@ctipo_isc,
        nporcentaje_isc=@nporcentaje_isc,nmonto_isc=@nmonto_isc,ccod_usuario=@ccod_usuario
    WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo;
END
GO

IF OBJECT_ID('sp_eliminararticulo','P') IS NOT NULL DROP PROCEDURE sp_eliminararticulo; 
GO
CREATE PROCEDURE sp_eliminararticulo @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON; UPDATE Articulos SET cstatus='I' WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo; END
GO

IF OBJECT_ID('sp_actualizarfavorito','P') IS NOT NULL DROP PROCEDURE sp_actualizarfavorito; 
GO
CREATE PROCEDURE sp_actualizarfavorito @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @bprefer BIT
AS BEGIN SET NOCOUNT ON; UPDATE Articulos SET bprefer=@bprefer WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo; END
GO

IF OBJECT_ID('webDatpos_consultarCostoArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCostoArticulo; 
GO
CREATE PROCEDURE webDatpos_consultarCostoArticulo @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ISNULL(ncosto,0) AS ncosto FROM Stock WHERE ccod_cia=@ccod_cia AND ccod_articulo=@ccod_articulo AND ccod_alm=@ccod_alm;
END
GO

IF OBJECT_ID('webDatpos_consultarArticulosConStock','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarArticulosConStock; 
GO
CREATE PROCEDURE webDatpos_consultarArticulosConStock @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,A.cdsc_articulo,A.cigv,A.cisc,A.ccod_lin,A.uni_medi,
           ISNULL(S.ncantidad,0) AS ncantidad, ISNULL(S.ncosto,0) AS ncosto
    FROM Articulos A
    INNER JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND S.ncantidad>0
    ORDER BY A.cdsc_articulo;
END
GO

IF OBJECT_ID('webDatpos_validarArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_validarArticulo; 
GO
CREATE PROCEDURE webDatpos_validarArticulo @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ISNULL(S.ncantidad,0) AS ncantidad FROM Stock S
    WHERE S.ccod_cia=@ccod_cia AND S.ccod_articulo=@ccod_articulo AND S.ccod_alm=@ccod_alm;
END
GO

IF OBJECT_ID('webDatpos_validarArticuloAlmacenSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_validarArticuloAlmacenSalida; 
GO
CREATE PROCEDURE webDatpos_validarArticuloAlmacenSalida @ccod_cia VARCHAR(20), @ccod_articulo VARCHAR(50), @ccod_alm VARCHAR(20), @ncantidad DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON;
    SELECT CASE WHEN ISNULL(S.ncantidad,0)>=@ncantidad THEN 'OK' ELSE 'SIN_STOCK' END AS resultado
    FROM Stock S WHERE S.ccod_cia=@ccod_cia AND S.ccod_articulo=@ccod_articulo AND S.ccod_alm=@ccod_alm;
END
GO

IF OBJECT_ID('webDatpos_articuloCantaArti','P') IS NOT NULL DROP PROCEDURE webDatpos_articuloCantaArti; 
GO
CREATE PROCEDURE webDatpos_articuloCantaArti @ccod_articulo VARCHAR(50), @ncantidad DECIMAL(18,4), @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT CASE WHEN ISNULL(S.ncantidad,0)>=@ncantidad THEN 'OK' ELSE 'SIN_STOCK' END AS resultado, ISNULL(S.ncantidad,0) AS stock_actual
    FROM Stock S WHERE S.ccod_cia=@ccod_cia AND S.ccod_articulo=@ccod_articulo AND S.ccod_alm=@ccod_alm;
END
GO

IF OBJECT_ID('appDatpos_validarStockArticulos','P') IS NOT NULL DROP PROCEDURE appDatpos_validarStockArticulos; 
GO
CREATE PROCEDURE appDatpos_validarStockArticulos @ccod_cia VARCHAR(20), @ccod_alm VARCHAR(20), @producto NVARCHAR(MAX)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,A.cdsc_articulo,ISNULL(S.ncantidad,0) AS ncantidad
    FROM Articulos A LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_alm AND S.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo IN (SELECT value FROM STRING_SPLIT(@producto,','));
END
GO

IF OBJECT_ID('sp_registrarpdf','P') IS NOT NULL DROP PROCEDURE sp_registrarpdf; 
GO
CREATE PROCEDURE sp_registrarpdf @ccod_cia VARCHAR(20), @id_cbfact INT, @pdf VARBINARY(MAX)
AS BEGIN SET NOCOUNT ON;
    UPDATE CbFactura SET pdf=@pdf WHERE ccod_cia=@ccod_cia AND id_cbfact=@id_cbfact;
END
GO

PRINT '✓ SPs Familias, UM, Variantes, COA y Articulos creados.';
GO
