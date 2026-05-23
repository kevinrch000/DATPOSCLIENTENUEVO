USE DatPos_EMP01;
GO
IF OBJECT_ID('webDatpos_OptenerImpuesto','P') IS NOT NULL DROP PROCEDURE webDatpos_OptenerImpuesto; 
GO
CREATE PROCEDURE webDatpos_OptenerImpuesto @ccod_cia VARCHAR(20),@IGV NVARCHAR(16) OUTPUT,@ISC NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON; SELECT @IGV=CAST(ISNULL(nigv,18) AS NVARCHAR),@ISC=CAST(ISNULL(nisc,0) AS NVARCHAR) FROM ConfigGeneral WHERE ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_GenerarNCCBFact','P') IS NOT NULL DROP PROCEDURE webDatpos_GenerarNCCBFact; 
GO
CREATE PROCEDURE webDatpos_GenerarNCCBFact
    @id_cbfact INT,@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20),
    @TotalIGV DECIMAL(18,4),@TotalISC DECIMAL(18,4),@TotalDescuento DECIMAL(18,4),
    @TotalSubtotal DECIMAL(18,4),@TotalDevolucion DECIMAL(18,4),
    @ccod_tienda VARCHAR(20),@ccod_alm VARCHAR(20),@ccod_caja VARCHAR(20),
    @id_cbfactRef NVARCHAR(16) OUTPUT,@TipoRef NVARCHAR(16) OUTPUT,@SerieRef NVARCHAR(16) OUTPUT,@NumRef NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @cserie VARCHAR(10),@nnumero INT,@ccod_coa VARCHAR(20);
    SELECT @ccod_coa=ccod_coa FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia;
    SELECT TOP 1 @cserie=cdoc_serie,@nnumero=ISNULL(cdoc_nro,0)+1 FROM NumeradorCaja WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo='NC';
    IF @cserie IS NULL BEGIN SET @id_cbfactRef=''; RETURN; END
    INSERT INTO CbFactura(ccod_cia,ccod_tiend,ccod_caja,ccod_almacen,ccod_usuario,cdoc,cserie,nnumero,ccod_coa,nimpuesto,nisc,ndescuento,ntotal,nsubtotal,cstatus,fecha_emision)
    VALUES(@ccod_cia,@ccod_tienda,@ccod_caja,@ccod_alm,@ccod_usuario,'NC',@cserie,@nnumero,@ccod_coa,@TotalIGV,@TotalISC,@TotalDescuento,@TotalDevolucion,@TotalSubtotal,'NC',GETDATE());
    SET @id_cbfactRef=CAST(SCOPE_IDENTITY() AS NVARCHAR);
    SET @TipoRef='NC'; SET @SerieRef=@cserie; SET @NumRef=CAST(@nnumero AS NVARCHAR);
    UPDATE NumeradorCaja SET cdoc_nro=@nnumero WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo='NC';
END
GO

IF OBJECT_ID('webDatpos_GenerarNCLNFact','P') IS NOT NULL DROP PROCEDURE webDatpos_GenerarNCLNFact; 
GO
CREATE PROCEDURE webDatpos_GenerarNCLNFact
    @id_cbfactRef NVARCHAR(16),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20),
    @cdoc_tipo_ref VARCHAR(5),@cdoc_serie_ref VARCHAR(10),@cdoc_nro_ref VARCHAR(16),
    @lnccod_articulo VARCHAR(50),@lncdsc_articulo VARCHAR(200),@lncobser_variante VARCHAR(200),
    @lncorden INT,@lnnprecio DECIMAL(18,4),@lnncantidad DECIMAL(18,4),
    @lnnimporte_bruto DECIMAL(18,4),@lnnimporte_neto DECIMAL(18,4),@lnnisc DECIMAL(18,4),
    @lnndescuento DECIMAL(18,4),@lnnimpuesto DECIMAL(18,4),@id_lnfact INT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnFactura(ccod_cia,id_cbfact,id_articulo,cdsc_articulo,cobser_variante,corden,nprecio,ncantidad,nimporte_bruto,nimporte_neto,nisc,ndescuento,nimpuesto,cdoc,ccod_usuario)
    VALUES(@ccod_cia,CAST(@id_cbfactRef AS INT),@lnccod_articulo,@lncdsc_articulo,@lncobser_variante,@lncorden,@lnnprecio,@lnncantidad,@lnnimporte_bruto,@lnnimporte_neto,@lnnisc,@lnndescuento,@lnnimpuesto,@cdoc_tipo_ref,@ccod_usuario);
END
GO

IF OBJECT_ID('webDatpos_GenerarNCCBInve','P') IS NOT NULL DROP PROCEDURE webDatpos_GenerarNCCBInve; 
GO
CREATE PROCEDURE webDatpos_GenerarNCCBInve
    @id_cbfactRef NVARCHAR(16),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20),
    @inve_ntotal DECIMAL(18,4),@ccod_tienda VARCHAR(20),@ccod_alm VARCHAR(20),@id_cbinve NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO CbInventario(ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ntotal)
    VALUES(@ccod_cia,@ccod_tienda,@ccod_alm,GETDATE(),'DV','NC',0,'Devolucion NC',@ccod_usuario,@inve_ntotal);
    SET @id_cbinve=CAST(SCOPE_IDENTITY() AS NVARCHAR);
    UPDATE CbFactura SET id_cbinve=CAST(@id_cbinve AS INT) WHERE id_cbfact=CAST(@id_cbfactRef AS INT) AND ccod_cia=@ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_GenerarNCLNInve','P') IS NOT NULL DROP PROCEDURE webDatpos_GenerarNCLNInve; 
GO
CREATE PROCEDURE webDatpos_GenerarNCLNInve
    @id_cbinve NVARCHAR(16),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20),
    @ccod_articulo VARCHAR(50),@ncantidad DECIMAL(18,4),@ncosto DECIMAL(18,4),@ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario(ccod_cia,id_cbinve,ccod_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES(@ccod_cia,CAST(@id_cbinve AS INT),@ccod_articulo,@ncantidad,@ncosto,@ccod_alm,@ccod_usuario);
    EXEC _stock_actualizar @ccod_cia,@ccod_alm,@ccod_articulo,@ncantidad,@ncosto,1;
END
GO

IF OBJECT_ID('webDatpos_DetalleNotaCredito','P') IS NOT NULL DROP PROCEDURE webDatpos_DetalleNotaCredito; 
GO
CREATE PROCEDURE webDatpos_DetalleNotaCredito
    @ccod_cia VARCHAR(20),@id_cbfact INT,
    @cdsc_tienda NVARCHAR(100) OUTPUT,@cdoc_serie NVARCHAR(10) OUTPUT,@cdoc_nro NVARCHAR(10) OUTPUT,
    @fecha NVARCHAR(25) OUTPUT,@hora NVARCHAR(25) OUTPUT,@ccoa_dsc NVARCHAR(100) OUTPUT,
    @cdir_coa NVARCHAR(100) OUTPUT,@cdoc_coa NVARCHAR(25) OUTPUT,@nsubtotal NVARCHAR(25) OUTPUT,
    @nimpuesto NVARCHAR(25) OUTPUT,@nisc NVARCHAR(25) OUTPUT,@ntotal NVARCHAR(25) OUTPUT,
    @cref_doc NVARCHAR(25) OUTPUT,@cref_serie NVARCHAR(25) OUTPUT,@cref_nro NVARCHAR(25) OUTPUT,
    @cdsc_usuario NVARCHAR(100) OUTPUT,@ccod_caja NVARCHAR(25) OUTPUT,@ccod_motivo NVARCHAR(25) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SELECT @cdsc_tienda=T.cnombr,@cdoc_serie=F.cserie,@cdoc_nro=CAST(F.nnumero AS NVARCHAR),
           @fecha=CONVERT(NVARCHAR,F.fecha_emision,103),@hora=CONVERT(NVARCHAR,F.fecha_emision,108),
           @ccoa_dsc=C.cdsc_coa,@cdir_coa=C.cdirc_coa,@cdoc_coa=C.cdoc_coa,
           @nsubtotal=CAST(F.nsubtotal AS NVARCHAR),@nimpuesto=CAST(F.nimpuesto AS NVARCHAR),
           @nisc=CAST(F.nisc AS NVARCHAR),@ntotal=CAST(F.ntotal AS NVARCHAR),
           @cref_doc=F.cdoc,@cref_serie=F.cserie,@cref_nro=CAST(F.nnumero AS NVARCHAR),
           @cdsc_usuario=U.cdsc_usuario,@ccod_caja=F.ccod_caja,@ccod_motivo='NC'
    FROM CbFactura F LEFT JOIN Tiendas T ON T.ccod_tiend=F.ccod_tiend AND T.ccod_cia=F.ccod_cia
    LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    LEFT JOIN Usuarios U ON U.ccod_usuario=F.ccod_usuario AND U.ccod_empresa=F.ccod_cia
    WHERE F.ccod_cia=@ccod_cia AND F.id_cbfact=@id_cbfact;
END
GO

IF OBJECT_ID('webDatpos_NCListaDeBienes','P') IS NOT NULL DROP PROCEDURE webDatpos_NCListaDeBienes; 
GO
CREATE PROCEDURE webDatpos_NCListaDeBienes @id_cbfact INT,@id_cbinve INT,@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT L.id_lnfact,L.id_articulo,L.cdsc_articulo,L.ncantidad,L.nprecio,L.ndescuento,L.nimpuesto,L.nisc,L.cobser_variante,ISNULL(S.ncosto,0) AS ncosto FROM LnFactura L LEFT JOIN Stock S ON S.ccod_articulo=L.id_articulo AND S.ccod_cia=L.ccod_cia AND S.ccod_alm=(SELECT ccod_almacen FROM CbFactura WHERE id_cbfact=@id_cbfact) WHERE L.ccod_cia=@ccod_cia AND L.id_cbfact=@id_cbfact; END
GO

IF OBJECT_ID('webDatpos_NCMontoRestante','P') IS NOT NULL DROP PROCEDURE webDatpos_NCMontoRestante; 
GO
CREATE PROCEDURE webDatpos_NCMontoRestante @id_cbfact INT,@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ntotal AS monto_original,ISNULL((SELECT SUM(ntotal) FROM CbFactura WHERE ccod_cia=@ccod_cia AND cstatus='NC' AND id_cbfact=@id_cbfact),0) AS monto_nc FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; END
GO

IF OBJECT_ID('webDatpos_ConsultarDocumentosNotaCredito','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarDocumentosNotaCredito; 
GO
CREATE PROCEDURE webDatpos_ConsultarDocumentosNotaCredito @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,F.cstatus,C.cdsc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@CodCia AND F.cstatus='NC' AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta; END
GO

IF OBJECT_ID('webDatpos_NotaCreditoPricipal','P') IS NOT NULL DROP PROCEDURE webDatpos_NotaCreditoPricipal; 
GO
CREATE PROCEDURE webDatpos_NotaCreditoPricipal @cdoc_seri VARCHAR(5),@serie VARCHAR(10),@correlativo VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_coa VARCHAR(20),@fchDesde VARCHAR(20),@fchHasta VARCHAR(20),@CodCia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.fecha_emision,F.ntotal,C.cdsc_coa,C.cdoc_coa FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia WHERE F.ccod_cia=@CodCia AND F.cdoc IN ('F','B') AND F.cstatus='P' AND (@ccod_tienda='' OR F.ccod_tiend=@ccod_tienda) AND F.fecha_emision BETWEEN @fchDesde AND @fchHasta ORDER BY F.fecha_emision DESC; END
GO

IF OBJECT_ID('webDatpos_generarNotaCredito','P') IS NOT NULL DROP PROCEDURE webDatpos_generarNotaCredito; 
GO
CREATE PROCEDURE webDatpos_generarNotaCredito @id_cbfact INT,@nimp_aplicado DECIMAL(18,4),@cdsc_movito VARCHAR(200),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; UPDATE CbFactura SET cstatus='NC' WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; SELECT 'OK' AS resultado; END
GO

IF OBJECT_ID('webDatpos_generarNotaCreditoDescuento','P') IS NOT NULL DROP PROCEDURE webDatpos_generarNotaCreditoDescuento; 
GO
CREATE PROCEDURE webDatpos_generarNotaCreditoDescuento @id_cbfact INT,@nimp_aplicado DECIMAL(18,4),@cdsc_movito VARCHAR(200),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; UPDATE CbFactura SET cstatus='NC',ndescuento=ndescuento+@nimp_aplicado WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia; SELECT 'OK' AS resultado; END
GO

IF OBJECT_ID('webDatpos_generarNotaDebito','P') IS NOT NULL DROP PROCEDURE webDatpos_generarNotaDebito; 
GO
CREATE PROCEDURE webDatpos_generarNotaDebito @id_cbfact INT,@ccod_usuario VARCHAR(50),@nmonto_aplicado DECIMAL(18,4),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT 'OK' AS resultado; END
GO

IF OBJECT_ID('webDatpos_ValidarNC','P') IS NOT NULL DROP PROCEDURE webDatpos_ValidarNC; 
GO
CREATE PROCEDURE webDatpos_ValidarNC @id_cbfact INT,@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20),@ccod_tienda VARCHAR(20),@ccod_alm VARCHAR(20),@ccod_caja VARCHAR(20),@mensaje NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON; IF EXISTS(SELECT 1 FROM CbFactura WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia AND cstatus='P') SET @mensaje='OK'; ELSE SET @mensaje='DOCUMENTO_NO_VALIDO'; END
GO

IF OBJECT_ID('webDatpos_buscarNCIdCliente','P') IS NOT NULL DROP PROCEDURE webDatpos_buscarNCIdCliente; 
GO
CREATE PROCEDURE webDatpos_buscarNCIdCliente @id_coa VARCHAR(20),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT F.id_cbfact,F.cdoc,F.cserie,F.nnumero,F.ntotal,F.fecha_emision FROM CbFactura F WHERE F.ccod_cia=@ccod_cia AND F.ccod_coa=@id_coa AND F.cstatus='NC' ORDER BY F.fecha_emision DESC; END
GO

/* CUENTAS (mesa/reserva) */
IF OBJECT_ID('sp_insertarcuenta','P') IS NOT NULL DROP PROCEDURE sp_insertarcuenta; 
GO
CREATE PROCEDURE sp_insertarcuenta @ccod_cia VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),@etiqueta VARCHAR(50),@ccod_usuario VARCHAR(50),@ctip_cuenta VARCHAR(5),@id_cbcuenta INT OUTPUT
AS BEGIN SET NOCOUNT ON; INSERT INTO CbCuenta(ccod_cia,ccod_coa,ccod_tiend,ccod_caja,etiqueta,ccod_usuario,ctip_cuenta) VALUES(@ccod_cia,@ccod_coa,@ccod_tiend,@ccod_caja,@etiqueta,@ccod_usuario,@ctip_cuenta); SET @id_cbcuenta=SCOPE_IDENTITY(); END
GO

IF OBJECT_ID('sp_insertarcuentadetalle','P') IS NOT NULL DROP PROCEDURE sp_insertarcuentadetalle; 
GO
CREATE PROCEDURE sp_insertarcuentadetalle @ncantidad DECIMAL(18,4),@nprecio DECIMAL(18,4),@nimporte_neto DECIMAL(18,4),@id_articulo VARCHAR(50),@nimporte_bruto DECIMAL(18,4),@nimpuesto DECIMAL(18,4),@ndescuento DECIMAL(18,4),@ctip_descn VARCHAR(10),@cobser_variante VARCHAR(200),@ccod_cia VARCHAR(20),@id_cbcuenta INT,@corden INT,@ccod_usuario VARCHAR(50),@ctip_desc VARCHAR(10)
AS BEGIN SET NOCOUNT ON; INSERT INTO LnCuenta(ccod_cia,id_cbcuenta,ncantidad,nprecio,nimporte_neto,id_articulo,nimporte_bruto,nimpuesto,ndescuento,ctip_descn,cobser_variante,corden,ccod_usuario,ctip_desc) VALUES(@ccod_cia,@id_cbcuenta,@ncantidad,@nprecio,@nimporte_neto,@id_articulo,@nimporte_bruto,@nimpuesto,@ndescuento,@ctip_descn,@cobser_variante,@corden,@ccod_usuario,@ctip_desc); END
GO

IF OBJECT_ID('sp_lsinsertarcuenta','P') IS NOT NULL DROP PROCEDURE sp_lsinsertarcuenta; 
GO
CREATE PROCEDURE sp_lsinsertarcuenta @ccod_cia VARCHAR(20),@ccod_coa VARCHAR(20),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),@etiqueta VARCHAR(50),@ccod_usuario VARCHAR(50),@ctip_cuenta VARCHAR(5),@ntot_desct DECIMAL(18,4),@ntot_impbruto DECIMAL(18,4),@ntot_igv DECIMAL(18,4),@ntot_impneto DECIMAL(18,4),@id_cbcuenta INT OUTPUT
AS BEGIN SET NOCOUNT ON; INSERT INTO CbCuenta(ccod_cia,ccod_coa,ccod_tiend,ccod_caja,etiqueta,ccod_usuario,ctip_cuenta,ntot_desct,ntot_impbruto,ntot_igv,ntot_impneto) VALUES(@ccod_cia,@ccod_coa,@ccod_tiend,@ccod_caja,@etiqueta,@ccod_usuario,@ctip_cuenta,@ntot_desct,@ntot_impbruto,@ntot_igv,@ntot_impneto); SET @id_cbcuenta=SCOPE_IDENTITY(); END
GO

IF OBJECT_ID('sp_lsinsertarcuentadetalle','P') IS NOT NULL DROP PROCEDURE sp_lsinsertarcuentadetalle; 
GO
CREATE PROCEDURE sp_lsinsertarcuentadetalle @ncantidad DECIMAL(18,4),@nprecio DECIMAL(18,4),@nimporte_neto DECIMAL(18,4),@id_articulo VARCHAR(50),@nimporte_bruto DECIMAL(18,4),@nimpuesto DECIMAL(18,4),@ndescuento DECIMAL(18,4),@ctip_descn VARCHAR(10),@cobser_variante VARCHAR(200),@ccod_cia VARCHAR(20),@id_cbcuenta INT,@corden INT,@ccod_usuario VARCHAR(50),@ctip_desc VARCHAR(10),@nigv_uni DECIMAL(18,4),@ncosto DECIMAL(18,4),@id_variante VARCHAR(20),@cdescn_max VARCHAR(50)
AS BEGIN SET NOCOUNT ON; INSERT INTO LnCuenta(ccod_cia,id_cbcuenta,ncantidad,nprecio,nimporte_neto,id_articulo,nimporte_bruto,nimpuesto,ndescuento,ctip_descn,cobser_variante,corden,ccod_usuario,ctip_desc,nigv_uni,ncosto,id_variante,cdescn_max) VALUES(@ccod_cia,@id_cbcuenta,@ncantidad,@nprecio,@nimporte_neto,@id_articulo,@nimporte_bruto,@nimpuesto,@ndescuento,@ctip_descn,@cobser_variante,@corden,@ccod_usuario,@ctip_desc,@nigv_uni,@ncosto,@id_variante,@cdescn_max); END
GO

IF OBJECT_ID('sp_consultarcuentas','P') IS NOT NULL DROP PROCEDURE sp_consultarcuentas; 
GO
CREATE PROCEDURE sp_consultarcuentas @ccod_cia VARCHAR(20),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),@ctip_cuenta VARCHAR(5)
AS BEGIN SET NOCOUNT ON; SELECT CB.*,C.cdsc_coa FROM CbCuenta CB LEFT JOIN Coa C ON C.ccod_coa=CB.ccod_coa AND C.ccod_cia=CB.ccod_cia WHERE CB.ccod_cia=@ccod_cia AND CB.ccod_tiend=@ccod_tiend AND CB.ccod_caja=@ccod_caja AND CB.ctip_cuenta=@ctip_cuenta AND CB.cstatus='A' ORDER BY CB.etiqueta; END
GO

IF OBJECT_ID('sp_consultarcuentadetalles','P') IS NOT NULL DROP PROCEDURE sp_consultarcuentadetalles; 
GO
CREATE PROCEDURE sp_consultarcuentadetalles @id_cbcuenta INT
AS BEGIN SET NOCOUNT ON; SELECT L.*,A.cdsc_articulo FROM LnCuenta L LEFT JOIN Articulos A ON A.ccod_articulo=L.id_articulo AND A.ccod_cia=L.ccod_cia WHERE L.id_cbcuenta=@id_cbcuenta; END
GO

IF OBJECT_ID('sp_lsconsultarcuentadetalles','P') IS NOT NULL DROP PROCEDURE sp_lsconsultarcuentadetalles; 
GO
CREATE PROCEDURE sp_lsconsultarcuentadetalles @id_cbcuenta INT
AS BEGIN SET NOCOUNT ON; SELECT L.*,A.cdsc_articulo FROM LnCuenta L LEFT JOIN Articulos A ON A.ccod_articulo=L.id_articulo AND A.ccod_cia=L.ccod_cia WHERE L.id_cbcuenta=@id_cbcuenta; END
GO

IF OBJECT_ID('sp_lsconsultararticulo','P') IS NOT NULL DROP PROCEDURE sp_lsconsultararticulo; 
GO
CREATE PROCEDURE sp_lsconsultararticulo @ccod_cia VARCHAR(20),@ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT A.ccod_articulo,A.cdsc_articulo,A.cigv,A.cisc,A.bprefer,ISNULL(L.npre_uni,0) AS npre_uni FROM Articulos A LEFT JOIN LnListaPrecio L ON L.ccod_articulo=A.ccod_articulo AND L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' ORDER BY A.cdsc_articulo; END
GO

IF OBJECT_ID('sp_lsconsultarfavoritos','P') IS NOT NULL DROP PROCEDURE sp_lsconsultarfavoritos; 
GO
CREATE PROCEDURE sp_lsconsultarfavoritos @ccod_cia VARCHAR(20),@ccod_cblistpre VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT A.ccod_articulo,A.cdsc_articulo,A.cigv,A.cisc,ISNULL(L.npre_uni,0) AS npre_uni FROM Articulos A LEFT JOIN LnListaPrecio L ON L.ccod_articulo=A.ccod_articulo AND L.ccod_cia=A.ccod_cia AND L.ccod_cblistpre=@ccod_cblistpre WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A' AND A.bprefer=1 ORDER BY A.cdsc_articulo; END
GO

PRINT '✓ SPs Nota Credito y Cuentas creados.';
GO
