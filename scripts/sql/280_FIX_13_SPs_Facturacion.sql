/* =====================================================================
   FIX 13 — SPs EXACTOS PARA PANTALLA DE FACTURACIÓN
   Nombres sacados directamente de DAFamilia.vb, DAArticulo.vb, DAPrecio.vb
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ──────────────────────────────────────────────────────────────────
   FAMILIAS — sp_consultafamiliasactivas (@ccod_cia)
   Retorna: ccod_lin[0], cdsc_lin[1], id_ctlin[2], ccolor[3]
   (Facturacion.aspx.vb líneas 295-299)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultafamiliasactivas','P') IS NOT NULL DROP PROCEDURE sp_consultafamiliasactivas;
GO
CREATE PROCEDURE sp_consultafamiliasactivas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_lin, cdsc_lin, id_lin AS id_ctlin, ccolor
    FROM Familias WHERE ccod_cia=@ccod_cia AND cstatus='A'
    ORDER BY cdsc_lin;
END
GO

/* ──────────────────────────────────────────────────────────────────
   ARTÍCULOS POR CATEGORÍA — sp_consultararticulocategoria
   Params: @ccod_cia, @codigo (id_lin), @ccod_usuario, @ccod_almacen
   Retorna: cdsc_articulo[0], iimage[1], id_articulo[2], precio[3],
            ctip_articulo[4], bprefer[5]
   (Facturacion.aspx.vb líneas 273-278)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultararticulocategoria','P') IS NOT NULL DROP PROCEDURE sp_consultararticulocategoria;
GO
CREATE PROCEDURE sp_consultararticulocategoria
    @ccod_cia VARCHAR(20), @codigo INT, @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    -- id_ctlin en Familias es id_lin; Articulos guarda ccod_lin (código, no id)
    DECLARE @ccod_lin VARCHAR(20) = (SELECT ccod_lin FROM Familias WHERE ccod_cia=@ccod_cia AND id_lin=@codigo);
    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN CbListaPrecio CB ON CB.ccod_cia=A.ccod_cia AND CB.cstatus='A'
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=CB.ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_lin=@ccod_lin AND A.cstatus='A'
    ORDER BY A.cdsc_articulo;
END
GO

/* ──────────────────────────────────────────────────────────────────
   FAVORITOS — sp_consultarfavoritos
   Params: @ccod_cia, @ccod_usuario, @ccod_almacen
   Retorna: cdsc_articulo[0], iimage[1], id_articulo[2], precio[3],
            ctip_articulo[4], bprefer[5]
   (Facturacion.aspx.vb líneas 367-372)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultarfavoritos','P') IS NOT NULL DROP PROCEDURE sp_consultarfavoritos;
GO
CREATE PROCEDURE sp_consultarfavoritos
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN CbListaPrecio CB ON CB.ccod_cia=A.ccod_cia AND CB.cstatus='A'
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=CB.ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.bprefer=1 AND A.cstatus='A'
    ORDER BY A.cdsc_articulo;
END
GO

/* ──────────────────────────────────────────────────────────────────
   BÚSQUEDA LIBRE — sp_consultararticulotodos
   Params: @ccod_cia, @texto, @ccod_usuario, @ccod_almacen
   Retorna: cdsc_articulo[0], iimage[1], id_articulo[2], precio[3]
   (Facturacion.aspx.vb líneas 140-143)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultararticulotodos','P') IS NOT NULL DROP PROCEDURE sp_consultararticulotodos;
GO
CREATE PROCEDURE sp_consultararticulotodos
    @ccod_cia VARCHAR(20), @texto VARCHAR(100), @ccod_usuario VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT A.cdsc_articulo,
           A.iimage,
           A.id_articulo,
           ISNULL(L.npre_uni, 0) AS precio,
           A.ctip_articulo,
           CAST(A.bprefer AS INT) * -1 AS bprefer,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN CbListaPrecio CB ON CB.ccod_cia=A.ccod_cia AND CB.cstatus='A'
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=CB.ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A'
      AND (A.cdsc_articulo LIKE '%'+@texto+'%' OR A.ccod_articulo LIKE '%'+@texto+'%')
    ORDER BY A.cdsc_articulo;
END
GO

/* ──────────────────────────────────────────────────────────────────
   PRECIO POR ID — sp_consultararticuloprecio
   Params: @ccod_cia, @ccod_usuario, @codigo (id_articulo), @ccod_almacen
   Retorna: npre_uni[0], cdsc_articulo[1], igv[2], isc[3],
            ctip_articulo[4], cstatus[5], npre_costo[6], ndes_max[7]
   (Facturacion.aspx.vb líneas 237-244)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultararticuloprecio','P') IS NOT NULL DROP PROCEDURE sp_consultararticuloprecio;
GO
CREATE PROCEDURE sp_consultararticuloprecio
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
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
    LEFT JOIN CbListaPrecio CB ON CB.ccod_cia=A.ccod_cia AND CB.cstatus='A'
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=CB.ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C  ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.id_articulo=CAST(@codigo AS INT);
END
GO

/* ──────────────────────────────────────────────────────────────────
   PRECIO POR CÓDIGO — sp_consultararticulopreciocodigo
   Params: @ccod_cia, @ccod_usuario, @codigo (ccod_articulo), @ccod_almacen
   Retorna: npre_uni[0], cdsc_articulo[1], igv[2], isc[3],
            ctip_articulo[4], cstatus[5], npre_costo[6], ndes_max[7], id_cblistpre[8]
   (Facturacion.aspx.vb líneas 200-208)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultararticulopreciocodigo','P') IS NOT NULL DROP PROCEDURE sp_consultararticulopreciocodigo;
GO
CREATE PROCEDURE sp_consultararticulopreciocodigo
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50), @codigo VARCHAR(50), @ccod_almacen VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ISNULL(L.npre_uni, 0)           AS npre_uni,
           A.cdsc_articulo,
           ISNULL(C.nigv, 18)              AS igv,
           ISNULL(C.nisc, 0)               AS isc,
           A.ctip_articulo,
           A.cstatus,
           ISNULL(L.npre_uni, 0)           AS npre_costo,
           ISNULL(L.ndes_max, 0)           AS ndes_max,
           CB.id_cblistpre,
           A.ccod_articulo
    FROM Articulos A
    LEFT JOIN CbListaPrecio CB ON CB.ccod_cia=A.ccod_cia AND CB.cstatus='A'
    LEFT JOIN LnListaPrecio L  ON L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=CB.ccod_cblistpre AND L.ccod_articulo=A.ccod_articulo
    LEFT JOIN ConfigGeneral C  ON C.ccod_cia=A.ccod_cia
    WHERE A.ccod_cia=@ccod_cia AND A.ccod_articulo=@codigo AND A.cstatus='A';
END
GO

/* ──────────────────────────────────────────────────────────────────
   SP ACTUALIZARFAVORITO — sin @ccod_cia en el DA (solo @id_articulo, @bprefer)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_actualizarfavorito','P') IS NOT NULL DROP PROCEDURE sp_actualizarfavorito;
GO
CREATE PROCEDURE sp_actualizarfavorito @id_articulo INT, @bprefer INT
AS BEGIN SET NOCOUNT ON;
    UPDATE Articulos SET bprefer=@bprefer WHERE id_articulo=@id_articulo;
    SELECT 'OK' AS resultado;
END
GO

/* ──────────────────────────────────────────────────────────────────
   TIENDA — para ConsultarTienda en Facturacion.aspx.vb
   Retorna 11 columnas (índices 0-10), incluyendo nlista_pre_normal[9] y preferencial[10]
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_ConsultarTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarTienda;
GO
CREATE PROCEDURE webDatpos_ConsultarTienda @ccod_cia VARCHAR(20), @ccod_tiend VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend, cnombr, cdirec, ctelef, cmail, cpassw, cstatus,
           cdepartamento, cprovincia, cdistrito,
           nlista_pre_normal, nlista_pre_preferencial
    FROM Tiendas WHERE ccod_cia=@ccod_cia AND ccod_tiend=@ccod_tiend;
END
GO

/* ──────────────────────────────────────────────────────────────────
   DATOS EMPRESA / IMPUESTOS
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_ConsultarImpuestos','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarImpuestos;
GO
CREATE PROCEDURE webDatpos_ConsultarImpuestos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT nigv, nisc FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* appDatpos_ObtenerIGV (ya existe pero lo recreamos limpio) */
IF OBJECT_ID('appDatpos_ObtenerIGV','P') IS NOT NULL DROP PROCEDURE appDatpos_ObtenerIGV;
GO
CREATE PROCEDURE appDatpos_ObtenerIGV @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT nigv, nisc FROM ConfigGeneral WHERE ccod_cia=@ccod_cia;
END
GO

/* ──────────────────────────────────────────────────────────────────
   DATOS GENERALES DEL USUARIO (Dashboard y páginas)
   sp_datosgenerales: tienda, almacén, caja, listas de precio
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_DatosGenerales','P') IS NOT NULL DROP PROCEDURE webDatpos_DatosGenerales;
GO
CREATE PROCEDURE webDatpos_DatosGenerales @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT T.cnombr AS cdsc_tienda,
           AL.cdsc_alm,
           CA.cdsc_caja,
           TI.nlista_pre_normal,
           TI.nlista_pre_preferencial,
           CB1.cdsc_cblistpre AS cdsc_listpreNorm,
           CB2.cdsc_cblistpre AS cdsc_listprePref
    FROM Usuarios U
    JOIN Tiendas TI   ON TI.ccod_cia=U.ccod_empresa  AND TI.ccod_tiend=U.ccod_tiend
    JOIN Tiendas T    ON T.ccod_cia=U.ccod_empresa    AND T.ccod_tiend=U.ccod_tiend
    JOIN Almacenes AL ON AL.ccod_cia=U.ccod_empresa   AND AL.ccod_alm=U.ccod_almacen
    JOIN Cajas CA     ON CA.ccod_cia=U.ccod_empresa   AND CA.ccod_caja=U.ccod_caja
    LEFT JOIN CbListaPrecio CB1 ON CB1.ccod_cia=U.ccod_empresa AND CB1.id_cblistpre=TI.nlista_pre_normal
    LEFT JOIN CbListaPrecio CB2 ON CB2.ccod_cia=U.ccod_empresa AND CB2.id_cblistpre=TI.nlista_pre_preferencial
    WHERE U.ccod_empresa=@ccod_cia AND U.ccod_usuario=@ccod_usuario;
END
GO

/* ──────────────────────────────────────────────────────────────────
   VERIFICACIÓN FINAL
────────────────────────────────────────────────────────────────── */
SELECT name AS sp_creado FROM sys.procedures
WHERE name IN (
  'sp_consultafamiliasactivas','sp_consultararticulocategoria',
  'sp_consultarfavoritos','sp_consultararticulotodos',
  'sp_consultararticuloprecio','sp_consultararticulopreciocodigo',
  'sp_actualizarfavorito','webDatpos_ConsultarTienda',
  'appDatpos_ObtenerIGV','sp_validarfacturacion',
  'sp_consultarusuarioturno','appDatpos_abrirCaja',
  'sp_clientepordefecto','sp_consultarclientestodos'
)
ORDER BY name;
GO
PRINT 'OK - SPs de Facturacion listos.';
GO
