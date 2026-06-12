/* PARTE 9: Ventas / Facturación */
USE DatPos_EMP01;
GO
IF OBJECT_ID('sp_insertarmovimientocabeceranew','P') IS NOT NULL DROP PROCEDURE sp_insertarmovimientocabeceranew; 
GO
CREATE PROCEDURE sp_insertarmovimientocabeceranew
    @ccod_cia VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),
    @ccod_almacen VARCHAR(20),@cdoc VARCHAR(5),@ccod_coa VARCHAR(20),@nimpuesto DECIMAL(18,4),
    @nisc DECIMAL(18,4),@ndescuento DECIMAL(18,4),@ntotal DECIMAL(18,4),@nsubtotal DECIMAL(18,4),
    @nvuelto DECIMAL(18,4),@ntot_entreg DECIMAL(18,4),@cantidad_bienes INT,@id_turno INT,
    @costo DECIMAL(18,4),@cobs VARCHAR(500),
    @id_cbfact INT OUTPUT,@id_cbinve INT OUTPUT,@fecha_emision NVARCHAR(16) OUTPUT,@documento NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @cserie VARCHAR(10),@nnumero INT;
    SELECT TOP 1 @cserie=cdoc_serie,@nnumero=cdoc_nro FROM NumeradorCaja WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc;
    SET @nnumero=ISNULL(@nnumero,0)+1;
    INSERT INTO CbFactura(ccod_cia,ccod_tiend,ccod_caja,ccod_almacen,ccod_usuario,cdoc,cserie,nnumero,ccod_coa,nimpuesto,nisc,ndescuento,ntotal,nsubtotal,nvuelto,ntot_entreg,cantidad_bienes,id_turno,costo,cobs,fecha_emision)
    VALUES(@ccod_cia,@ccod_tiend,@ccod_caja,@ccod_almacen,@ccod_usuario,@cdoc,@cserie,@nnumero,@ccod_coa,@nimpuesto,@nisc,@ndescuento,@ntotal,@nsubtotal,@nvuelto,@ntot_entreg,@cantidad_bienes,@id_turno,@costo,@cobs,GETDATE());
    SET @id_cbfact=SCOPE_IDENTITY();
    DECLARE @nnumInve INT;
    SELECT @nnumInve=ISNULL(MAX(nnumero),0)+1 FROM CbInventario WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_almacen AND ctipo='VT';
    INSERT INTO CbInventario(ccod_cia,ccod_tienda,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES(@ccod_cia,@ccod_tiend,@ccod_almacen,GETDATE(),'VT',@cserie,@nnumInve,'Venta generada',@ccod_usuario,@ccod_coa,@costo);
    SET @id_cbinve=SCOPE_IDENTITY();
    UPDATE CbFactura SET id_cbinve=@id_cbinve WHERE id_cbfact=@id_cbfact;
    UPDATE NumeradorCaja SET cdoc_nro=@nnumero WHERE ccod_cia=@ccod_cia AND ccod_caja=@ccod_caja AND cdoc_tipo=@cdoc;
    SET @fecha_emision=CONVERT(NVARCHAR,GETDATE(),120);
    SET @documento=ISNULL(@cserie,'')+'-'+RIGHT('00000000'+CAST(@nnumero AS VARCHAR),8);
END
GO

IF OBJECT_ID('sp_insertarmovimientodetalle','P') IS NOT NULL DROP PROCEDURE sp_insertarmovimientodetalle; 
GO
CREATE PROCEDURE sp_insertarmovimientodetalle
    @ccod_cia VARCHAR(20),@ccod_tiend VARCHAR(20),@id_articulo VARCHAR(50),@cdsc_articulo VARCHAR(200),
    @id_cbfact INT,@cdoc VARCHAR(5),@nprecio DECIMAL(18,4),@ncantidad DECIMAL(18,4),
    @nimporte_bruto DECIMAL(18,4),@nimpuesto DECIMAL(18,4),@nisc DECIMAL(18,4),@ndescuento DECIMAL(18,4),
    @nimporte_neto DECIMAL(18,4),@corden INT,@ccod_usuario VARCHAR(50),@id_cbinve INT,
    @ccod_almacen VARCHAR(20),@cobser_variante VARCHAR(200),@ctip_descn VARCHAR(10),
    @respuesta NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnFactura(ccod_cia,id_cbfact,ccod_tiend,id_articulo,cdsc_articulo,cdoc,nprecio,ncantidad,nimporte_bruto,nimpuesto,nisc,ndescuento,nimporte_neto,corden,ccod_usuario,id_cbinve,ccod_almacen,cobser_variante,ctip_descn)
    VALUES(@ccod_cia,@id_cbfact,@ccod_tiend,@id_articulo,@cdsc_articulo,@cdoc,@nprecio,@ncantidad,@nimporte_bruto,@nimpuesto,@nisc,@ndescuento,@nimporte_neto,@corden,@ccod_usuario,@id_cbinve,@ccod_almacen,@cobser_variante,@ctip_descn);
    INSERT INTO LnInventario(ccod_cia,id_cbinve,ccod_articulo,cdsc_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES(@ccod_cia,@id_cbinve,@id_articulo,@cdsc_articulo,@ncantidad,0,@ccod_almacen,@ccod_usuario);
    EXEC _stock_actualizar @ccod_cia,@ccod_almacen,@id_articulo,@ncantidad,0,-1;
    SET @respuesta='OK';
END
GO

IF OBJECT_ID('sp_insertarcobranzacabecera','P') IS NOT NULL DROP PROCEDURE sp_insertarcobranzacabecera; 
GO
CREATE PROCEDURE sp_insertarcobranzacabecera
    @id_cbfact INT,@id_turno INT,@ccod_cia VARCHAR(20),@ccod_tiend VARCHAR(20),@ccod_caja VARCHAR(20),
    @ccod_usuario VARCHAR(50),@ntotal DECIMAL(18,4),@ntot_entreg DECIMAL(18,4),@nvuelto DECIMAL(18,4),
    @id_cbcajac INT OUTPUT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO CbCobranza(ccod_cia,id_cbfact,id_turno,ccod_tiend,ccod_caja,ccod_usuario,ntotal,ntot_entreg,nvuelto)
    VALUES(@ccod_cia,@id_cbfact,@id_turno,@ccod_tiend,@ccod_caja,@ccod_usuario,@ntotal,@ntot_entreg,@nvuelto);
    SET @id_cbcajac=SCOPE_IDENTITY();
END
GO

IF OBJECT_ID('sp_insertarcobranzadetalle','P') IS NOT NULL DROP PROCEDURE sp_insertarcobranzadetalle; 
GO
CREATE PROCEDURE sp_insertarcobranzadetalle
    @ccod_cia VARCHAR(20),@id_cbcajac INT,@id_cbfact INT,@ccod_tiend VARCHAR(20),
    @nmonto DECIMAL(18,4),@cnum_opera VARCHAR(50),@cnum_tarje VARCHAR(50),@cnom_tarje VARCHAR(100),
    @id_cbfactNC INT,@ccod_usuario VARCHAR(50),@ccod_caja VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnCobranza(ccod_cia,id_cbcajac,id_cbfact,ccod_tiend,nmonto,cnum_opera,cnum_tarje,cnom_tarje,id_cbfactNC,ccod_usuario,ccod_caja)
    VALUES(@ccod_cia,@id_cbcajac,@id_cbfact,@ccod_tiend,@nmonto,@cnum_opera,@cnum_tarje,@cnom_tarje,@id_cbfactNC,@ccod_usuario,@ccod_caja);
END
GO

IF OBJECT_ID('sp_validarfacturacion','P') IS NOT NULL DROP PROCEDURE sp_validarfacturacion; 
GO
CREATE PROCEDURE sp_validarfacturacion @CodCia VARCHAR(20),@ccod_usuario VARCHAR(50),@resp NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @turno INT;
    SELECT TOP 1 @turno=id_turno FROM Turno WHERE ccod_cia=@CodCia AND ccod_usuario=@ccod_usuario AND cstatus='A';
    IF @turno IS NOT NULL SET @resp='OK'; ELSE SET @resp='SIN_TURNO';
END
GO

IF OBJECT_ID('sp_validaralfacturar','P') IS NOT NULL DROP PROCEDURE sp_validaralfacturar; 
GO
CREATE PROCEDURE sp_validaralfacturar @CodCia VARCHAR(20),@ccod_usuario VARCHAR(50),@cdoc_tipo VARCHAR(5),@resp NVARCHAR(256) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @existe INT;
    SELECT @existe=COUNT(1) FROM NumeradorCaja NC INNER JOIN Usuarios U ON U.ccod_caja=NC.ccod_caja AND U.ccod_empresa=NC.ccod_cia
    WHERE NC.ccod_cia=@CodCia AND U.ccod_usuario=@ccod_usuario AND NC.cdoc_tipo=@cdoc_tipo;
    IF @existe>0 SET @resp='OK'; ELSE SET @resp='SIN_NUMERADOR';
END
GO

IF OBJECT_ID('sp_consultarsunatfactura','P') IS NOT NULL DROP PROCEDURE sp_consultarsunatfactura; 
GO
CREATE PROCEDURE sp_consultarsunatfactura
    @CodCia VARCHAR(20),@id_fact INT,
    @cliente_tipo_de_documento NVARCHAR(16) OUTPUT,@serie NVARCHAR(16) OUTPUT,@numero NVARCHAR(16) OUTPUT,
    @cliente_numero_de_documento NVARCHAR(32) OUTPUT,@cliente_denominacion NVARCHAR(32) OUTPUT,
    @cliente_direccion NVARCHAR(32) OUTPUT,@fecha_de_emision NVARCHAR(32) OUTPUT,
    @fecha_de_vencimiento NVARCHAR(32) OUTPUT,@porcentaje_de_igv NVARCHAR(32) OUTPUT,
    @total NVARCHAR(32) OUTPUT,@total_igv NVARCHAR(32) OUTPUT,@total_gravada NVARCHAR(32) OUTPUT
AS BEGIN SET NOCOUNT ON;
    SELECT @cliente_tipo_de_documento=C.ctipo_coa,@serie=F.cserie,@numero=CAST(F.nnumero AS NVARCHAR),
           @cliente_numero_de_documento=C.cdoc_coa,@cliente_denominacion=C.cdsc_coa,
           @cliente_direccion=C.cdirc_coa,@fecha_de_emision=CONVERT(NVARCHAR,F.fecha_emision,103),
           @fecha_de_vencimiento=CONVERT(NVARCHAR,F.fecha_emision,103),
           @porcentaje_de_igv=CAST(ISNULL(CG.nigv,18) AS NVARCHAR),
           @total=CAST(F.ntotal AS NVARCHAR),@total_igv=CAST(F.nimpuesto AS NVARCHAR),@total_gravada=CAST(F.nsubtotal AS NVARCHAR)
    FROM CbFactura F LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    LEFT JOIN ConfigGeneral CG ON CG.ccod_cia=F.ccod_cia
    WHERE F.ccod_cia=@CodCia AND F.id_cbfact=@id_fact;
END
GO

IF OBJECT_ID('sp_consultarsunatfacturadetalle','P') IS NOT NULL DROP PROCEDURE sp_consultarsunatfacturadetalle; 
GO
CREATE PROCEDURE sp_consultarsunatfacturadetalle @CodCia VARCHAR(20),@id_fact INT
AS BEGIN SET NOCOUNT ON;
    SELECT id_lnfact,id_articulo,cdsc_articulo,nprecio,ncantidad,nimporte_bruto,nimpuesto,nisc,ndescuento,nimporte_neto,cobser_variante FROM LnFactura WHERE ccod_cia=@CodCia AND id_cbfact=@id_fact;
END
GO

IF OBJECT_ID('sp_consultardocumentocabecera','P') IS NOT NULL DROP PROCEDURE sp_consultardocumentocabecera; 
GO
CREATE PROCEDURE sp_consultardocumentocabecera @id_cbfact INT
AS BEGIN SET NOCOUNT ON;
    SELECT F.*,C.cdsc_coa,C.cdoc_coa,C.cdirc_coa,T.cnombr AS cdsc_tienda FROM CbFactura F
    LEFT JOIN Coa C ON C.ccod_coa=F.ccod_coa AND C.ccod_cia=F.ccod_cia
    LEFT JOIN Tiendas T ON T.ccod_tiend=F.ccod_tiend AND T.ccod_cia=F.ccod_cia
    WHERE F.id_cbfact=@id_cbfact;
END
GO

IF OBJECT_ID('sp_consultardocumentodetalle','P') IS NOT NULL DROP PROCEDURE sp_consultardocumentodetalle; 
GO
CREATE PROCEDURE sp_consultardocumentodetalle @id_cbfact INT
AS BEGIN SET NOCOUNT ON;
    SELECT * FROM LnFactura WHERE id_cbfact=@id_cbfact;
END
GO

IF OBJECT_ID('sp_consultardocumentocobranza','P') IS NOT NULL DROP PROCEDURE sp_consultardocumentocobranza; 
GO
CREATE PROCEDURE sp_consultardocumentocobranza @id_cbfact INT
AS BEGIN SET NOCOUNT ON;
    SELECT CB.*,D.nmonto,D.cnum_opera,D.cnum_tarje,D.cnom_tarje FROM CbCobranza CB
    LEFT JOIN LnCobranza D ON D.id_cbcajac=CB.id_cbcajac WHERE CB.id_cbfact=@id_cbfact;
END
GO

IF OBJECT_ID('InsertarNotaCredito','P') IS NOT NULL DROP PROCEDURE InsertarNotaCredito; 
GO
CREATE PROCEDURE InsertarNotaCredito @id_cbfact INT,@cod_motivo VARCHAR(20),@nimp_aplicado DECIMAL(18,4),@cdsc_movito VARCHAR(200),@ccod_usuario VARCHAR(50),@ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    UPDATE CbFactura SET cstatus='NC' WHERE id_cbfact=@id_cbfact AND ccod_cia=@ccod_cia;
    SELECT 'OK' AS resultado;
END
GO

PRINT '✓ SPs Ventas/Facturacion creados.';
GO
