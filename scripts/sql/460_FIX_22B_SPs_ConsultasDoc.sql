/* =====================================================================
   FIX 22B — SPs FALTANTES: Consultas Documentos + Operaciones Almacén
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. webDatpos_consultaDocumentoPrincipal ── */
IF OBJECT_ID('webDatpos_consultaDocumentoPrincipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDocumentoPrincipal;
GO
CREATE PROCEDURE webDatpos_consultaDocumentoPrincipal
    @cdoc_seri VARCHAR(5), @serie VARCHAR(10), @correlativo VARCHAR(20),
    @ccod_tienda VARCHAR(20), @ccod_coa VARCHAR(20),
    @fchDesde VARCHAR(20), @fchHasta VARCHAR(20), @CodCia VARCHAR(20),
    @cusu_crea VARCHAR(50), @cobs VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.fecha_emision,
           ISNULL(C.cdsc_coa,'') AS cdsc_coa, F.ntotal, F.cstatus, F.ccod_usuario
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia
      AND (@cdoc_seri='' OR F.cdoc=@cdoc_seri) AND (@serie='' OR F.cserie=@serie)
      AND (@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo)
      AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@ccod_coa='' OR F.ccod_coa=@ccod_coa)
      AND (@fchDesde='' OR F.fecha_emision>=@fchDesde) AND (@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
      AND (@cusu_crea='' OR F.ccod_usuario=@cusu_crea)
    ORDER BY F.fecha_emision DESC;
END
GO

/* ── 2. webDatpos_consultaDocumentoSecundario ── */
IF OBJECT_ID('webDatpos_consultaDocumentoSecundario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDocumentoSecundario;
GO
CREATE PROCEDURE webDatpos_consultaDocumentoSecundario
    @cdoc_seri VARCHAR(5), @serie VARCHAR(10), @correlativo VARCHAR(20),
    @ccod_tienda VARCHAR(20), @ccod_coa VARCHAR(20),
    @fchDesde VARCHAR(20), @fchHasta VARCHAR(20), @CodCia VARCHAR(20),
    @cusu_crea VARCHAR(50), @cobs VARCHAR(200), @cobser_variante VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio, L.nimporte_neto
    FROM CbFactura F INNER JOIN LnFactura L ON L.id_cbfact=F.id_cbfact AND L.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia
      AND (@cdoc_seri='' OR F.cdoc=@cdoc_seri) AND (@serie='' OR F.cserie=@serie)
      AND (@correlativo='' OR CAST(F.nnumero AS VARCHAR)=@correlativo)
      AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND (@ccod_coa='' OR F.ccod_coa=@ccod_coa)
      AND (@fchDesde='' OR F.fecha_emision>=@fchDesde) AND (@fchHasta='' OR F.fecha_emision<=@fchHasta+' 23:59:59')
    ORDER BY F.id_cbfact;
END
GO

/* ── 3. webDatpos_consultaDatosDocumento ── */
IF OBJECT_ID('webDatpos_consultaDatosDocumento','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosDocumento;
GO
CREATE PROCEDURE webDatpos_consultaDatosDocumento @cdoc VARCHAR(5), @cdoc_serie VARCHAR(10), @cdoc_nro VARCHAR(20), @CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.fecha_emision, ISNULL(C.cdsc_coa,'') AS cdsc_coa,
           F.nsubtotal, F.nimpuesto, F.ndescuento, F.ntotal, F.cstatus, F.ccod_coa, ISNULL(C.cdoc_coa,'') AS cdoc_coa
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia AND F.cdoc=@cdoc AND F.cserie=@cdoc_serie AND F.nnumero=CAST(@cdoc_nro AS INT);
END
GO

/* ── 4. webDatpos_consultaDatosComplementarios ── */
IF OBJECT_ID('webDatpos_consultaDatosComplementarios','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosComplementarios;
GO
CREATE PROCEDURE webDatpos_consultaDatosComplementarios @id_cbfact VARCHAR(20), @CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.ccod_coa, ISNULL(C.cdsc_coa,'') AS cdsc_coa,
           F.nsubtotal, F.nimpuesto, F.ndescuento, F.ntotal, F.nvuelto, F.ntot_entreg, F.ccod_usuario, F.cobs, F.cstatus
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia AND F.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 5. webDatpos_consultaDatosDocRef ── */
IF OBJECT_ID('webDatpos_consultaDatosDocRef','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosDocRef;
GO
CREATE PROCEDURE webDatpos_consultaDatosDocRef @id_cbfact VARCHAR(20), @CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.ccod_coa, ISNULL(C.cdsc_coa,'') AS cdsc_coa, F.ntotal, F.cstatus
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia AND F.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 6. webDatpos_consultaListArticulos ── */
IF OBJECT_ID('webDatpos_consultaListArticulos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListArticulos;
GO
CREATE PROCEDURE webDatpos_consultaListArticulos @cdoc VARCHAR(5), @cdoc_serie VARCHAR(10), @cdoc_nro VARCHAR(20), @CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio, L.nimporte_bruto,
           L.nimpuesto, L.ndescuento, L.nimporte_neto, ISNULL(L.cobser_variante,'') AS cobser_variante
    FROM LnFactura L INNER JOIN CbFactura F ON F.id_cbfact=L.id_cbfact AND F.ccod_cia=L.ccod_cia
    WHERE F.ccod_cia=@CodCia AND F.cdoc=@cdoc AND F.cserie=@cdoc_serie AND F.nnumero=CAST(@cdoc_nro AS INT)
    ORDER BY L.corden;
END
GO

/* ── 7. webDatpos_consultaListArticulosPorId ── */
IF OBJECT_ID('webDatpos_consultaListArticulosPorId','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListArticulosPorId;
GO
CREATE PROCEDURE webDatpos_consultaListArticulosPorId @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio, L.nimporte_bruto,
           L.nimpuesto, L.ndescuento, L.nimporte_neto, ISNULL(L.cobser_variante,'') AS cobser_variante
    FROM LnFactura L WHERE L.ccod_cia=@ccod_cia AND L.id_cbfact=CAST(@id_cbfact AS INT) ORDER BY L.corden;
END
GO

/* ── 8. webDatpos_consultaListCobranzaPorId ── */
IF OBJECT_ID('webDatpos_consultaListCobranzaPorId','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListCobranzaPorId;
GO
CREATE PROCEDURE webDatpos_consultaListCobranzaPorId @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT LC.id_lncajac, LC.nmonto, ISNULL(LC.cnum_opera,'') AS cnum_opera,
           ISNULL(LC.cnum_tarje,'') AS cnum_tarje, ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje
    FROM LnCobranza LC INNER JOIN CbCobranza CC ON CC.id_cbcajac=LC.id_cbcajac AND CC.ccod_cia=LC.ccod_cia
    WHERE LC.ccod_cia=@ccod_cia AND CC.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 9. webDatpos_consultaListCobranzaId ── */
IF OBJECT_ID('webDatpos_consultaListCobranzaId','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListCobranzaId;
GO
CREATE PROCEDURE webDatpos_consultaListCobranzaId @id_cbcajac VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT LC.id_lncajac, LC.nmonto, ISNULL(LC.cnum_opera,'') AS cnum_opera,
           ISNULL(LC.cnum_tarje,'') AS cnum_tarje, ISNULL(LC.cnom_tarje,'EFECTIVO') AS cnom_tarje
    FROM LnCobranza LC WHERE LC.ccod_cia=@ccod_cia AND LC.id_cbcajac=CAST(@id_cbcajac AS INT);
END
GO

/* ── 10. webDatpos_DetalleFact ── */
IF OBJECT_ID('webDatpos_DetalleFact','P') IS NOT NULL DROP PROCEDURE webDatpos_DetalleFact;
GO
CREATE PROCEDURE webDatpos_DetalleFact @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio, L.nimporte_neto
    FROM LnFactura L WHERE L.ccod_cia=@ccod_cia AND L.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 11. webDatpos_ListaArticulos ── */
IF OBJECT_ID('webDatpos_ListaArticulos','P') IS NOT NULL DROP PROCEDURE webDatpos_ListaArticulos;
GO
CREATE PROCEDURE webDatpos_ListaArticulos @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio,
           L.nimporte_bruto, L.nimpuesto, L.ndescuento, L.nimporte_neto
    FROM LnFactura L WHERE L.ccod_cia=@ccod_cia AND L.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 12. webDatpos_ListaDeArticulo ── */
IF OBJECT_ID('webDatpos_ListaDeArticulo','P') IS NOT NULL DROP PROCEDURE webDatpos_ListaDeArticulo;
GO
CREATE PROCEDURE webDatpos_ListaDeArticulo @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lnfact, L.id_articulo, L.cdsc_articulo, L.ncantidad, L.nprecio, L.nimporte_neto
    FROM LnFactura L WHERE L.ccod_cia=@ccod_cia AND L.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 13. webDatpos_ConsultaPDF ── */
IF OBJECT_ID('webDatpos_ConsultaPDF','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultaPDF;
GO
CREATE PROCEDURE webDatpos_ConsultaPDF @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT pdf FROM CbFactura WHERE ccod_cia=@ccod_cia AND id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 14. webDatpos_BuscarDocRef ── */
IF OBJECT_ID('webDatpos_BuscarDocRef','P') IS NOT NULL DROP PROCEDURE webDatpos_BuscarDocRef;
GO
CREATE PROCEDURE webDatpos_BuscarDocRef @ccod_cia VARCHAR(20), @cdoc VARCHAR(5), @cdoc_serie VARCHAR(10), @cdoc_nro VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.ntotal, F.cstatus, ISNULL(C.cdsc_coa,'') AS cdsc_coa
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@ccod_cia AND F.cdoc=@cdoc AND F.cserie=@cdoc_serie AND F.nnumero=CAST(@cdoc_nro AS INT);
END
GO

/* ── 15. webDatpos_ConsultarNotaCredito ── */
IF OBJECT_ID('webDatpos_ConsultarNotaCredito','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarNotaCredito;
GO
CREATE PROCEDURE webDatpos_ConsultarNotaCredito @id_cbfact VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.ntotal, F.cstatus, F.fecha_emision
    FROM CbFactura F WHERE F.ccod_cia=@ccod_cia AND F.id_cbfact=CAST(@id_cbfact AS INT);
END
GO

/* ── 16. webDatpos_consultaDatosCobranza ── */
IF OBJECT_ID('webDatpos_consultaDatosCobranza','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosCobranza;
GO
CREATE PROCEDURE webDatpos_consultaDatosCobranza @cdoc_tipo VARCHAR(5), @cdoc_serie VARCHAR(10), @cdoc_nro VARCHAR(20), @CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT CC.id_cbcajac, CC.id_cbfact, CC.ntotal, CC.ntot_entreg, CC.nvuelto, CC.ccod_usuario
    FROM CbCobranza CC INNER JOIN CbFactura F ON F.id_cbfact=CC.id_cbfact AND F.ccod_cia=CC.ccod_cia
    WHERE CC.ccod_cia=@CodCia AND F.cdoc=@cdoc_tipo AND F.cserie=@cdoc_serie AND F.nnumero=CAST(@cdoc_nro AS INT);
END
GO

/* ── OPERACIONES ALMACEN ── */

/* ── 17. webDatpos_consultaOperAlmacenPricipal ── */
IF OBJECT_ID('webDatpos_consultaOperAlmacenPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaOperAlmacenPricipal;
GO
CREATE PROCEDURE webDatpos_consultaOperAlmacenPricipal
    @tipoOper VARCHAR(20), @serie VARCHAR(10), @numero VARCHAR(20),
    @almacen VARCHAR(20), @fchDesde VARCHAR(20), @fchHasta VARCHAR(20),
    @ccod_usuario VARCHAR(50), @ccod_cliente VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT I.id_cbinve, T.cdsc_tipoper AS ctipo, I.vserie, I.nnumero, I.dfecha, I.ntotal,
           ISNULL(A.cdsc_alm,'') AS cdsc_alm, I.ccod_usuario
    FROM CbInventario I
    LEFT JOIN TipoOperacion T ON T.ccod_tipoper=I.ctipo AND T.ccod_cia=I.ccod_cia
    LEFT JOIN Almacenes A ON A.ccod_alm=I.ccod_alm AND A.ccod_cia=I.ccod_cia
    WHERE I.ccod_cia=@ccod_cia
      AND (@tipoOper='' OR I.ctipo=@tipoOper) AND (@serie='' OR I.vserie=@serie)
      AND (@numero='' OR CAST(I.nnumero AS VARCHAR)=@numero) AND (@almacen='' OR I.ccod_alm=@almacen)
      AND (@fchDesde='' OR I.dfecha>=@fchDesde) AND (@fchHasta='' OR I.dfecha<=@fchHasta+' 23:59:59')
      AND (@ccod_usuario='' OR I.ccod_usuario=@ccod_usuario) AND (@ccod_cliente='' OR I.ccod_coa=@ccod_cliente)
    ORDER BY I.dfecha DESC;
END
GO

/* ── 18. webDatpos_consultaDatosDocumentoInve ── */
IF OBJECT_ID('webDatpos_consultaDatosDocumentoInve','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosDocumentoInve;
GO
CREATE PROCEDURE webDatpos_consultaDatosDocumentoInve @tipoOper VARCHAR(20), @serie VARCHAR(10), @numero VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT I.id_cbinve, T.cdsc_tipoper AS ctipo, I.vserie, I.nnumero, I.dfecha,
           ISNULL(A.cdsc_alm,'') AS cdsc_alm, I.ntotal, I.vobservacion, I.ccod_usuario,
           ISNULL(C.cdsc_coa,'') AS cdsc_coa
    FROM CbInventario I
    LEFT JOIN TipoOperacion T ON T.ccod_tipoper=I.ctipo AND T.ccod_cia=I.ccod_cia
    LEFT JOIN Almacenes A ON A.ccod_alm=I.ccod_alm AND A.ccod_cia=I.ccod_cia
    LEFT JOIN Coa C ON C.ccod_coa=I.ccod_coa AND C.ccod_cia=I.ccod_cia
    WHERE I.ccod_cia=@ccod_cia AND I.ctipo=@tipoOper AND I.vserie=@serie AND I.nnumero=CAST(@numero AS INT);
END
GO

/* ── 19. webDatpos_consultaDatosMoviInve ── */
IF OBJECT_ID('webDatpos_consultaDatosMoviInve','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaDatosMoviInve;
GO
CREATE PROCEDURE webDatpos_consultaDatosMoviInve @tipoOper VARCHAR(20), @serie VARCHAR(10), @numero VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT I.id_cbinve, I.ctipo, I.vserie, I.nnumero, I.dfecha, I.ccod_alm, I.ntotal, I.vobservacion, I.ccod_coa, I.ccod_usuario
    FROM CbInventario I
    WHERE I.ccod_cia=@ccod_cia AND I.ctipo=@tipoOper AND I.vserie=@serie AND I.nnumero=CAST(@numero AS INT);
END
GO

/* ── 20. webDatpos_consultaListArticulosInventario ── */
IF OBJECT_ID('webDatpos_consultaListArticulosInventario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListArticulosInventario;
GO
CREATE PROCEDURE webDatpos_consultaListArticulosInventario @tipoOper VARCHAR(20), @serie VARCHAR(10), @numero VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, ISNULL(A.cdsc_articulo,'') AS cdsc_articulo, L.ncantidad, L.ncosto, (L.ncantidad*L.ncosto) AS nimporte
    FROM LnInventario L
    INNER JOIN CbInventario I ON I.id_cbinve=L.id_cbinve AND I.ccod_cia=L.ccod_cia
    LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND I.ctipo=@tipoOper AND I.vserie=@serie AND I.nnumero=CAST(@numero AS INT);
END
GO

/* ── 21. webDatpos_consultaListArticulosInve ── */
IF OBJECT_ID('webDatpos_consultaListArticulosInve','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListArticulosInve;
GO
CREATE PROCEDURE webDatpos_consultaListArticulosInve @ccod_cia VARCHAR(20), @id VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, ISNULL(A.cdsc_articulo,'') AS cdsc_articulo, L.ncantidad, L.ncosto, (L.ncantidad*L.ncosto) AS nimporte
    FROM LnInventario L LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=CAST(@id AS INT);
END
GO

/* ── 22. webDatpos_consultaListArticulosInveDat ── */
IF OBJECT_ID('webDatpos_consultaListArticulosInveDat','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaListArticulosInveDat;
GO
CREATE PROCEDURE webDatpos_consultaListArticulosInveDat @ccod_cia VARCHAR(20), @tipoOper VARCHAR(20), @serie VARCHAR(10), @numero VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, ISNULL(A.cdsc_articulo,'') AS cdsc_articulo, L.ncantidad, L.ncosto
    FROM LnInventario L
    INNER JOIN CbInventario I ON I.id_cbinve=L.id_cbinve AND I.ccod_cia=L.ccod_cia
    LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND I.ctipo=@tipoOper AND I.vserie=@serie AND I.nnumero=CAST(@numero AS INT);
END
GO

/* ── 23. webDatpos_DetalleInve ── */
IF OBJECT_ID('webDatpos_DetalleInve','P') IS NOT NULL DROP PROCEDURE webDatpos_DetalleInve;
GO
CREATE PROCEDURE webDatpos_DetalleInve @id_cbinve VARCHAR(20), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT L.id_lninve, L.ccod_articulo, ISNULL(A.cdsc_articulo,'') AS cdsc_articulo, L.ncantidad, L.ncosto
    FROM LnInventario L LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia
    WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=CAST(@id_cbinve AS INT);
END
GO

PRINT '✓ FIX 22B: SPs Consultas Documentos + Oper Almacén (23 SPs) creados.';
GO
