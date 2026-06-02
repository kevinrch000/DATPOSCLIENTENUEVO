/* PARTE 7: TipoOperacion, ListaPrecio, Precios, ConfigGeneral, AperturaCaja */
USE DatPos_EMP01;
GO

/* TIPO OPERACION */
IF OBJECT_ID('sp_insertartipooperacion','P') IS NOT NULL DROP PROCEDURE sp_insertartipooperacion; 
GO
CREATE PROCEDURE sp_insertartipooperacion
    @ccod_cia VARCHAR(20), @ccod_tipoper VARCHAR(20), @cdsc_tipoper VARCHAR(100),
    @ctipo_flag VARCHAR(5), @cstatus VARCHAR(1), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND ccod_tipoper=@ccod_tipoper)
        INSERT INTO TipoOperacion (ccod_cia,ccod_tipoper,cdsc_tipoper,ctipo_flag,cstatus,ccod_usuario)
        VALUES (@ccod_cia,@ccod_tipoper,@cdsc_tipoper,@ctipo_flag,@cstatus,@ccod_usuario);
END
GO

IF OBJECT_ID('sp_editartipooperacion','P') IS NOT NULL DROP PROCEDURE sp_editartipooperacion; 
GO
CREATE PROCEDURE sp_editartipooperacion
    @ccod_cia VARCHAR(20), @ccod_tipoper VARCHAR(20), @cdsc_tipoper VARCHAR(100), @ctipo_flag VARCHAR(5), @cstatus VARCHAR(1)
AS BEGIN SET NOCOUNT ON;
    UPDATE TipoOperacion SET cdsc_tipoper=@cdsc_tipoper,ctipo_flag=@ctipo_flag,cstatus=@cstatus WHERE ccod_cia=@ccod_cia AND ccod_tipoper=@ccod_tipoper;
END
GO

IF OBJECT_ID('sp_consultartipooperacion','P') IS NOT NULL DROP PROCEDURE sp_consultartipooperacion; 
GO
CREATE PROCEDURE sp_consultartipooperacion @ccod_cia VARCHAR(20), @ccod_tipoper VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND ccod_tipoper=@ccod_tipoper; END
GO

IF OBJECT_ID('sp_consultartiposoperacion','P') IS NOT NULL DROP PROCEDURE sp_consultartiposoperacion; 
GO
CREATE PROCEDURE sp_consultartiposoperacion @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM TipoOperacion WHERE ccod_cia=@ccod_cia ORDER BY ccod_tipoper; END
GO

IF OBJECT_ID('sp_eliminartipooperacion','P') IS NOT NULL DROP PROCEDURE sp_eliminartipooperacion; 
GO
CREATE PROCEDURE sp_eliminartipooperacion @ccod_cia VARCHAR(20), @ccod_tipoper VARCHAR(20)
AS BEGIN SET NOCOUNT ON; UPDATE TipoOperacion SET cstatus='I' WHERE ccod_cia=@ccod_cia AND ccod_tipoper=@ccod_tipoper; END
GO

IF OBJECT_ID('sp_consultartiposoperacionactivosingresos','P') IS NOT NULL DROP PROCEDURE sp_consultartiposoperacionactivosingresos; 
GO
CREATE PROCEDURE sp_consultartiposoperacionactivosingresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_tipoper,cdsc_tipoper FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='I'; END
GO

IF OBJECT_ID('webDatpos_cargarTiposOperacionIngreso','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTiposOperacionIngreso; 
GO
CREATE PROCEDURE webDatpos_cargarTiposOperacionIngreso @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_tipoper,cdsc_tipoper FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='I'; END
GO

IF OBJECT_ID('sp_consultarTiposOperacionSalisa','P') IS NOT NULL DROP PROCEDURE sp_consultarTiposOperacionSalisa; 
GO
CREATE PROCEDURE sp_consultarTiposOperacionSalisa @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_tipoper,cdsc_tipoper FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='S'; END
GO

IF OBJECT_ID('webDatpos_consultarOperTransferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarOperTransferencia; 
GO
CREATE PROCEDURE webDatpos_consultarOperTransferencia @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_tipoper,cdsc_tipoper FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='T'; END
GO

/* LISTA DE PRECIOS */
IF OBJECT_ID('sp_listasprecios','P') IS NOT NULL DROP PROCEDURE sp_listasprecios; 
GO
CREATE PROCEDURE sp_listasprecios @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM CbListaPrecio WHERE ccod_cia=@ccod_cia ORDER BY ccod_cblistpre; END
GO

IF OBJECT_ID('sp_consultarlistasprecios','P') IS NOT NULL DROP PROCEDURE sp_consultarlistasprecios; 
GO
CREATE PROCEDURE sp_consultarlistasprecios @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM CbListaPrecio WHERE ccod_cia=@ccod_cia ORDER BY ccod_cblistpre; END
GO

IF OBJECT_ID('sp_consultarlistaspreciosactivos','P') IS NOT NULL DROP PROCEDURE sp_consultarlistaspreciosactivos; 
GO
CREATE PROCEDURE sp_consultarlistaspreciosactivos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_cblistpre,cdsc_cblistpre FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND cstatus='A'; END
GO

IF OBJECT_ID('sp_consultalistaprecio','P') IS NOT NULL DROP PROCEDURE sp_consultalistaprecio; 
GO
CREATE PROCEDURE sp_consultalistaprecio @ccod_cia VARCHAR(20), @codigo VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@codigo; END
GO

IF OBJECT_ID('sp_insertarlistaprecio','P') IS NOT NULL DROP PROCEDURE sp_insertarlistaprecio; 
GO
CREATE PROCEDURE sp_insertarlistaprecio
    @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20), @cdsc_cblistpre VARCHAR(100),
    @dfch_ini DATE, @dfch_fin DATE, @cstatus VARCHAR(1)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre)
        INSERT INTO CbListaPrecio (ccod_cia,ccod_cblistpre,cdsc_cblistpre,dfch_ini,dfch_fin,cstatus) VALUES (@ccod_cia,@ccod_cblistpre,@cdsc_cblistpre,@dfch_ini,@dfch_fin,@cstatus);
END
GO

IF OBJECT_ID('sp_editarlistaprecio','P') IS NOT NULL DROP PROCEDURE sp_editarlistaprecio; 
GO
CREATE PROCEDURE sp_editarlistaprecio
    @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20), @cdsc_cblistpre VARCHAR(100),
    @dfch_ini DATE, @dfch_fin DATE, @cstatus VARCHAR(1)
AS BEGIN SET NOCOUNT ON;
    UPDATE CbListaPrecio SET cdsc_cblistpre=@cdsc_cblistpre,dfch_ini=@dfch_ini,dfch_fin=@dfch_fin,cstatus=@cstatus
    WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre;
END
GO

IF OBJECT_ID('sp_eliminarlistaprecio','P') IS NOT NULL DROP PROCEDURE sp_eliminarlistaprecio; 
GO
CREATE PROCEDURE sp_eliminarlistaprecio @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DELETE FROM LnListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre;
    DELETE FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre;
END
GO

/* PRECIOS DETALLE */
IF OBJECT_ID('sp_consultarprecios','P') IS NOT NULL DROP PROCEDURE sp_consultarprecios; 
GO
CREATE PROCEDURE sp_consultarprecios @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20), @order VARCHAR(20), @ccod_articulo VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnlistpre,L.ccod_cblistpre,L.ccod_articulo,A.cdsc_articulo,L.npre_uni,L.ndes_max,L.ndes_min
    FROM LnListaPrecio L INNER JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre
      AND (@ccod_articulo='' OR L.ccod_articulo=@ccod_articulo);
END
GO

IF OBJECT_ID('sp_consultararticulopreciocodigo','P') IS NOT NULL DROP PROCEDURE sp_consultararticulopreciocodigo; 
GO
CREATE PROCEDURE sp_consultararticulopreciocodigo
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,A.cdsc_articulo,A.cigv,A.cisc,A.ctip_articulo,
           ISNULL(L.npre_uni,0) AS npre_uni, ISNULL(L.ndes_max,0) AS ndes_max,
           ISNULL(S.ncantidad,0) AS ncantidad, ISNULL(S.ncosto,0) AS ncosto,
           A.nstock_min,A.nstock_max,A.bprefer,A.uni_medi,A.ccod_artSunat
    FROM Articulos A
    LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_almacen AND S.ccod_cia=A.ccod_cia
    LEFT JOIN LnListaPrecio L ON L.ccod_articulo=A.ccod_articulo AND L.ccod_cia=A.ccod_cia
        AND L.ccod_cblistpre=(SELECT TOP 1 ccod_cblistpre FROM CbListaPrecio WHERE ccod_cia=@ccod_cia AND cstatus='A')
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A'
      AND (A.ccod_articulo=@codigo OR A.cdsc_articulo LIKE '%'+@codigo+'%');
END
GO

IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL DROP PROCEDURE sp_consultararticuloprecio; 
GO
CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON; EXEC sp_consultararticulopreciocodigo @ccod_cia,@ccod_usuario,@codigo,@ccod_almacen; END
GO

IF OBJECT_ID('sp_lsconsultararticulopreciocodigo','P') IS NOT NULL DROP PROCEDURE sp_lsconsultararticulopreciocodigo; 
GO
CREATE PROCEDURE sp_lsconsultararticulopreciocodigo
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20), @ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.ccod_articulo,A.cdsc_articulo,A.cigv,A.cisc,A.ctip_articulo,
           ISNULL(L.npre_uni,0) AS npre_uni, ISNULL(L.ndes_max,0) AS ndes_max,
           ISNULL(S.ncantidad,0) AS ncantidad, ISNULL(S.ncosto,0) AS ncosto, A.bprefer
    FROM Articulos A
    LEFT JOIN Stock S ON S.ccod_articulo=A.ccod_articulo AND S.ccod_alm=@ccod_almacen AND S.ccod_cia=A.ccod_cia
    LEFT JOIN LnListaPrecio L ON L.ccod_articulo=A.ccod_articulo AND L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A'
      AND (A.ccod_articulo=@codigo OR A.cdsc_articulo LIKE '%'+@codigo+'%');
END
GO

IF OBJECT_ID('sp_lsconsultararticuloprecio','P') IS NOT NULL DROP PROCEDURE sp_lsconsultararticuloprecio; 
GO
CREATE PROCEDURE sp_lsconsultararticuloprecio
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20), @ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON; EXEC sp_lsconsultararticulopreciocodigo @ccod_cia,@ccod_usuario,@codigo,@ccod_almacen,@ccod_cblistpre; END
GO

IF OBJECT_ID('sp_insertarprecio','P') IS NOT NULL DROP PROCEDURE sp_insertarprecio; 
GO
CREATE PROCEDURE sp_insertarprecio
    @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20), @ccod_articulo VARCHAR(50),
    @npre_uni DECIMAL(18,4), @ndes_max DECIMAL(18,4), @ndes_min DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM LnListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre AND ccod_articulo=@ccod_articulo)
        INSERT INTO LnListaPrecio (ccod_cia,ccod_cblistpre,ccod_articulo,npre_uni,ndes_max,ndes_min) VALUES (@ccod_cia,@ccod_cblistpre,@ccod_articulo,@npre_uni,@ndes_max,@ndes_min);
    ELSE UPDATE LnListaPrecio SET npre_uni=@npre_uni,ndes_max=@ndes_max,ndes_min=@ndes_min WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre AND ccod_articulo=@ccod_articulo;
END
GO

IF OBJECT_ID('sp_editarprecio','P') IS NOT NULL DROP PROCEDURE sp_editarprecio; 
GO
CREATE PROCEDURE sp_editarprecio @id_lnlistpre INT, @ccod_articulo VARCHAR(50), @npre_uni DECIMAL(18,4), @ndes_max DECIMAL(18,4), @ndes_min DECIMAL(18,4)
AS BEGIN SET NOCOUNT ON; UPDATE LnListaPrecio SET npre_uni=@npre_uni,ndes_max=@ndes_max,ndes_min=@ndes_min WHERE id_lnlistpre=@id_lnlistpre; END
GO

IF OBJECT_ID('sp_eliminarprecio','P') IS NOT NULL DROP PROCEDURE sp_eliminarprecio; 
GO
CREATE PROCEDURE sp_eliminarprecio @id_lnlistpre INT
AS BEGIN SET NOCOUNT ON; DELETE FROM LnListaPrecio WHERE id_lnlistpre=@id_lnlistpre; END
GO

IF OBJECT_ID('sp_eliminarprecios','P') IS NOT NULL DROP PROCEDURE sp_eliminarprecios; 
GO
CREATE PROCEDURE sp_eliminarprecios @ccod_cia VARCHAR(20), @ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON; DELETE FROM LnListaPrecio WHERE ccod_cia=@ccod_cia AND ccod_cblistpre=@ccod_cblistpre; END
GO

/* CONFIG GENERAL */
IF OBJECT_ID('webDatpos_insertarConfigGeneral','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarConfigGeneral; 
GO
CREATE PROCEDURE webDatpos_insertarConfigGeneral
    @ccod_clibol VARCHAR(20), @coper_ingreso VARCHAR(20), @coper_salida VARCHAR(20),
    @ccod_cia VARCHAR(20), @ctipo_flag_ingreso VARCHAR(5), @ctipo_flag_salida VARCHAR(5),
    @nigv DECIMAL(18,4), @nisc DECIMAL(18,4), @nmonto_maxboleta DECIMAL(18,4), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM ConfigGeneral WHERE ccod_cia=@ccod_cia)
        INSERT INTO ConfigGeneral (ccod_cia,ccod_clibol,coper_ingreso,coper_salida,ctipo_flag_ingreso,ctipo_flag_salida,nigv,nisc,nmonto_maxboleta,ccod_usuario)
        VALUES (@ccod_cia,@ccod_clibol,@coper_ingreso,@coper_salida,@ctipo_flag_ingreso,@ctipo_flag_salida,@nigv,@nisc,@nmonto_maxboleta,@ccod_usuario);
END
GO

IF OBJECT_ID('webDatpos_editarConfigGeneral','P') IS NOT NULL DROP PROCEDURE webDatpos_editarConfigGeneral; 
GO
CREATE PROCEDURE webDatpos_editarConfigGeneral
    @ccod_clibol VARCHAR(20), @coper_ingreso VARCHAR(20), @coper_salida VARCHAR(20),
    @ccod_cia VARCHAR(20), @ctipo_flag_ingreso VARCHAR(5), @ctipo_flag_salida VARCHAR(5),
    @nigv DECIMAL(18,4), @nisc DECIMAL(18,4), @nmonto_maxboleta DECIMAL(18,4),
    @ccod_usuario VARCHAR(50), @ilogo VARBINARY(MAX)
AS BEGIN SET NOCOUNT ON;
    UPDATE ConfigGeneral SET ccod_clibol=@ccod_clibol,coper_ingreso=@coper_ingreso,coper_salida=@coper_salida,
        ctipo_flag_ingreso=@ctipo_flag_ingreso,ctipo_flag_salida=@ctipo_flag_salida,
        nigv=@nigv,nisc=@nisc,nmonto_maxboleta=@nmonto_maxboleta,ccod_usuario=@ccod_usuario,
        ilogo=ISNULL(@ilogo,ilogo)
    WHERE ccod_cia=@ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_datosConfigGenreal','P') IS NOT NULL DROP PROCEDURE webDatpos_datosConfigGenreal; 
GO
CREATE PROCEDURE webDatpos_datosConfigGenreal @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT * FROM ConfigGeneral WHERE ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos__eliminarConfigGenral','P') IS NOT NULL DROP PROCEDURE webDatpos__eliminarConfigGenral; 
GO
CREATE PROCEDURE webDatpos__eliminarConfigGenral @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; DELETE FROM ConfigGeneral WHERE ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_codigoOperacionIngreso','P') IS NOT NULL DROP PROCEDURE webDatpos_codigoOperacionIngreso; 
GO
CREATE PROCEDURE webDatpos_codigoOperacionIngreso @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.ccod_tipoper,T.cdsc_tipoper FROM TipoOperacion T
    INNER JOIN ConfigGeneral C ON C.coper_ingreso=T.ccod_tipoper AND C.ccod_cia=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_codigoOperacionSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_codigoOperacionSalida; 
GO
CREATE PROCEDURE webDatpos_codigoOperacionSalida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.ccod_tipoper,T.cdsc_tipoper FROM TipoOperacion T
    INNER JOIN ConfigGeneral C ON C.coper_salida=T.ccod_tipoper AND C.ccod_cia=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia;
END
GO

IF OBJECT_ID('appDatpos_ObtenerIGV','P') IS NOT NULL DROP PROCEDURE appDatpos_ObtenerIGV; 
GO
CREATE PROCEDURE appDatpos_ObtenerIGV @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ISNULL(nigv,18) AS nigv, ISNULL(nisc,0) AS nisc FROM ConfigGeneral WHERE ccod_cia=@ccod_cia; END
GO

/* APERTURA / CIERRE CAJA */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja; 
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie VARCHAR(20), @IdUsuario VARCHAR(50), @CodCaj VARCHAR(20),
    @Monto DECIMAL(18,4), @CodCia VARCHAR(20), @CodUsu VARCHAR(50), @dfchdoc_ini DATETIME
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Turno WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND ccod_caja=@CodCaj AND cstatus='A')
        INSERT INTO Turno (ccod_cia,ccod_tienda,ccod_usuario,ccod_caja,nmonto_ini,dfchdoc_ini,cstatus)
        VALUES (@CodCia,@CodTie,@IdUsuario,@CodCaj,@Monto,@dfchdoc_ini,'A');
    SELECT TOP 1 id_turno,cstatus,nmonto_ini,dfchdoc_ini FROM Turno
    WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND ccod_caja=@CodCaj AND cstatus='A';
END
GO

IF OBJECT_ID('appDatpos_cierreCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_cierreCaja; 
GO
CREATE PROCEDURE appDatpos_cierreCaja
    @id_turno INT, @ntot_entreg DECIMAL(18,4), @nmonto_fin DECIMAL(18,4),
    @ndiferencia DECIMAL(18,4), @CodCia VARCHAR(20), @CodUsu VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Turno SET ntot_entreg=@ntot_entreg,nmonto_fin=@nmonto_fin,ndiferencia=@ndiferencia,
        dfchdoc_fin=GETDATE(),cstatus='C'
    WHERE id_turno=@id_turno AND ccod_cia=@CodCia;
    SELECT 'OK' AS respuesta;
END
GO

IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja; 
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.*,U.cdsc_usuario FROM Turno T LEFT JOIN Usuarios U ON U.ccod_usuario=T.ccod_usuario AND U.ccod_empresa=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia ORDER BY T.dfchdoc_ini DESC;
END
GO

IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja; 
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON; SELECT * FROM Turno WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno; END
GO

IF OBJECT_ID('webDatpos_cargarCajaDeUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCajaDeUsuario; 
GO
CREATE PROCEDURE webDatpos_cargarCajaDeUsuario @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_caja,C.cdsc_caja FROM Usuarios U LEFT JOIN Cajas C ON C.ccod_caja=U.ccod_caja AND C.ccod_cia=U.ccod_empresa
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_usuario=@ccod_usuario;
END
GO

IF OBJECT_ID('webDatpos_cargarTurnoUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTurnoUsuario; 
GO
CREATE PROCEDURE webDatpos_cargarTurnoUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.id_turno,T.ccod_usuario,U.cdsc_usuario,T.ccod_caja,T.nmonto_ini,T.dfchdoc_ini,T.cstatus
    FROM Turno T LEFT JOIN Usuarios U ON U.ccod_usuario=T.ccod_usuario AND U.ccod_empresa=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia AND T.ccod_tienda=@ccod_tienda AND T.cstatus='A';
END
GO

IF OBJECT_ID('webDatpos_cargarIdUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarIdUsuario; 
GO
CREATE PROCEDURE webDatpos_cargarIdUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_usuario,U.cdsc_usuario,T.id_turno FROM Usuarios U
    LEFT JOIN Turno T ON T.ccod_usuario=U.ccod_usuario AND T.ccod_cia=U.ccod_empresa AND T.cstatus='A'
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_tiend=@ccod_tienda AND U.id_estado=1;
END
GO

PRINT '✓ SPs TipoOperacion, ListaPrecios, ConfigGeneral y AperturaCaja creados.';
GO
