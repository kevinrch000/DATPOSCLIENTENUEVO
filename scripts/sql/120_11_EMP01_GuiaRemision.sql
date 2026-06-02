USE DatPos_EMP01;
GO
IF OBJECT_ID('InsertarGuiaVentaCompra','P') IS NOT NULL DROP PROCEDURE InsertarGuiaVentaCompra; 
GO
CREATE PROCEDURE InsertarGuiaVentaCompra
    @ccod_cia VARCHAR(20),@ccod_guia VARCHAR(20),@cserie_guia VARCHAR(10),
    @cnum_ruc_rem VARCHAR(20),@cnom_rzn_soc_rem VARCHAR(200),@cnum_ruc_dest VARCHAR(20),@cnom_rzn_soc_dest VARCHAR(200),
    @cnum_ruc_proy VARCHAR(20),@cdsc_coa VARCHAR(200),@cdomicilio_partida VARCHAR(300),@ccod_ubi_partida VARCHAR(10),
    @cdomicilio_llegada VARCHAR(300),@ccod_ubi_llegada VARCHAR(10),@ctrans_nombre VARCHAR(200),@ctrans_ruc VARCHAR(20),
    @ccod_unid_peso_bruto VARCHAR(10),@nmnt_tot_peso_bruto DECIMAL(18,4),@cdesc_motiv_tras VARCHAR(200),
    @nobs VARCHAR(500),@ctrans_placa VARCHAR(20),@ctrans_licencia VARCHAR(20),@ntotal DECIMAL(18,4),
    @cusu_crea VARCHAR(50),@ccod_alm VARCHAR(20),@ctipo VARCHAR(10),@cserie VARCHAR(10),@dfec_fin DATE,
    @cdoc_ref VARCHAR(20),@cod_tip_cpe VARCHAR(10),@ccod_coa VARCHAR(20),@flag VARCHAR(1),
    @id_cbinve INT OUTPUT,@numero NVARCHAR(100) OUTPUT,@fchEmision NVARCHAR(100) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @nnumero INT;
    SELECT @nnumero=ISNULL(MAX(nnumero),0)+1 FROM CbInventario WHERE ccod_cia=@ccod_cia AND ccod_alm=@ccod_alm AND ctipo=@ctipo;
    INSERT INTO CbInventario(ccod_cia,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES(@ccod_cia,@ccod_alm,GETDATE(),@ctipo,@cserie,@nnumero,@nobs,@cusu_crea,@ccod_coa,@ntotal);
    SET @id_cbinve=SCOPE_IDENTITY();
    INSERT INTO CbGuia(ccod_cia,ccod_guia,cserie_guia,cnum_ruc_rem,cnom_rzn_soc_rem,cnum_ruc_dest,cnom_rzn_soc_dest,
        cnum_ruc_proy,cdsc_coa,cdomicilio_partida,ccod_ubi_partida,cdomicilio_llegada,ccod_ubi_llegada,
        ctrans_nombre,ctrans_ruc,ccod_unid_peso_bruto,nmnt_tot_peso_bruto,cdesc_motiv_tras,nobs,
        ctrans_placa,ctrans_licencia,ntotal,cusu_crea,ccod_alm,ctipo,cserie,dfec_fin,cdoc_ref,
        cod_tip_cpe,ccod_coa,flag,id_cbinve,nnumero,fchEmision)
    VALUES(@ccod_cia,@ccod_guia,@cserie_guia,@cnum_ruc_rem,@cnom_rzn_soc_rem,@cnum_ruc_dest,@cnom_rzn_soc_dest,
        @cnum_ruc_proy,@cdsc_coa,@cdomicilio_partida,@ccod_ubi_partida,@cdomicilio_llegada,@ccod_ubi_llegada,
        @ctrans_nombre,@ctrans_ruc,@ccod_unid_peso_bruto,@nmnt_tot_peso_bruto,@cdesc_motiv_tras,@nobs,
        @ctrans_placa,@ctrans_licencia,@ntotal,@cusu_crea,@ccod_alm,@ctipo,@cserie,@dfec_fin,@cdoc_ref,
        @cod_tip_cpe,@ccod_coa,@flag,@id_cbinve,CAST(@nnumero AS VARCHAR),CONVERT(NVARCHAR,GETDATE(),120));
    SET @numero=ISNULL(@cserie,'')+'-'+RIGHT('00000000'+CAST(@nnumero AS VARCHAR),8);
    SET @fchEmision=CONVERT(NVARCHAR,GETDATE(),120);
END
GO

IF OBJECT_ID('webDatpos_InsertarGuia','P') IS NOT NULL DROP PROCEDURE webDatpos_InsertarGuia; 
GO
CREATE PROCEDURE webDatpos_InsertarGuia
    @ccod_cia VARCHAR(20),@ccod_guia VARCHAR(20),@cserie_guia VARCHAR(10),
    @cnum_ruc_rem VARCHAR(20),@cnom_rzn_soc_rem VARCHAR(200),@cnum_ruc_dest VARCHAR(20),@cnom_rzn_soc_dest VARCHAR(200),
    @cnum_ruc_proy VARCHAR(20),@cdsc_coa VARCHAR(200),@cdomicilio_partida VARCHAR(300),@ccod_ubi_partida VARCHAR(10),
    @cdomicilio_llegada VARCHAR(300),@ccod_ubi_llegada VARCHAR(10),@ctrans_nombre VARCHAR(200),@ctrans_ruc VARCHAR(20),
    @ccod_unid_peso_bruto VARCHAR(10),@nmnt_tot_peso_bruto DECIMAL(18,4),@cdesc_motiv_tras VARCHAR(200),
    @nobs VARCHAR(500),@ctrans_placa VARCHAR(20),@ctrans_licencia VARCHAR(20),@ntotal DECIMAL(18,4),
    @cusu_crea VARCHAR(50),@ccod_almOrigen VARCHAR(20),@ctipoOrigen VARCHAR(10),@cserieOrigen VARCHAR(10),
    @ccod_almDestino VARCHAR(20),@ctipoDestino VARCHAR(10),@cserieDestino VARCHAR(10),@dfec_fin DATE,
    @cdoc_ref VARCHAR(20),@cod_tip_cpe VARCHAR(10),@ccod_coa VARCHAR(20),
    @id_cbinve INT OUTPUT,@numero NVARCHAR(100) OUTPUT,@fchEmision NVARCHAR(100) OUTPUT
AS BEGIN SET NOCOUNT ON;
    EXEC InsertarGuiaVentaCompra @ccod_cia,@ccod_guia,@cserie_guia,@cnum_ruc_rem,@cnom_rzn_soc_rem,@cnum_ruc_dest,@cnom_rzn_soc_dest,@cnum_ruc_proy,@cdsc_coa,@cdomicilio_partida,@ccod_ubi_partida,@cdomicilio_llegada,@ccod_ubi_llegada,@ctrans_nombre,@ctrans_ruc,@ccod_unid_peso_bruto,@nmnt_tot_peso_bruto,@cdesc_motiv_tras,@nobs,@ctrans_placa,@ctrans_licencia,@ntotal,@cusu_crea,@ccod_almOrigen,@ctipoOrigen,@cserieOrigen,@dfec_fin,@cdoc_ref,@cod_tip_cpe,@ccod_coa,'T',@id_cbinve OUTPUT,@numero OUTPUT,@fchEmision OUTPUT;
END
GO

IF OBJECT_ID('sp_DetalleGuiaIngreso','P') IS NOT NULL DROP PROCEDURE sp_DetalleGuiaIngreso; 
GO
CREATE PROCEDURE sp_DetalleGuiaIngreso
    @ccod_cia VARCHAR(20),@cusu_crea VARCHAR(50),@ccod_alm VARCHAR(20),@ccod_articulo VARCHAR(50),
    @ccod_artSunat VARCHAR(20),@cdsc_articulo VARCHAR(200),@ncantidad INT,@ncosto DECIMAL(18,4),@id_cbinve INT
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario(ccod_cia,id_cbinve,ccod_articulo,ccod_artSunat,cdsc_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES(@ccod_cia,@id_cbinve,@ccod_articulo,@ccod_artSunat,@cdsc_articulo,@ncantidad,@ncosto,@ccod_alm,@cusu_crea);
    EXEC _stock_actualizar @ccod_cia,@ccod_alm,@ccod_articulo,@ncantidad,@ncosto,1;
END
GO

IF OBJECT_ID('sp_DetalleGuiaSalida','P') IS NOT NULL DROP PROCEDURE sp_DetalleGuiaSalida; 
GO
CREATE PROCEDURE sp_DetalleGuiaSalida
    @ccod_cia VARCHAR(20),@cusu_crea VARCHAR(50),@ccod_articulo VARCHAR(50),
    @ccod_artSunat VARCHAR(20),@cdsc_articulo VARCHAR(200),@ncantidad INT,@ncosto DECIMAL(18,4),@id_cbinve INT,@ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    INSERT INTO LnInventario(ccod_cia,id_cbinve,ccod_articulo,ccod_artSunat,cdsc_articulo,ncantidad,ncosto,ccod_alm,ccod_usuario)
    VALUES(@ccod_cia,@id_cbinve,@ccod_articulo,@ccod_artSunat,@cdsc_articulo,@ncantidad,@ncosto,@ccod_alm,@cusu_crea);
    EXEC _stock_actualizar @ccod_cia,@ccod_alm,@ccod_articulo,@ncantidad,@ncosto,-1;
END
GO

IF OBJECT_ID('webDatpos_insertarLnTransferencia','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarLnTransferencia; 
GO
CREATE PROCEDURE webDatpos_insertarLnTransferencia
    @ccod_cia VARCHAR(20),@ccod_usuario VARCHAR(50),@ccod_articulo VARCHAR(50),@ccod_artSunat VARCHAR(20),
    @cdsc_articulo VARCHAR(200),@ncantidad INT,@ncosto DECIMAL(18,4),@id_cbinve INT,
    @ccod_alm_salida VARCHAR(20),@ccod_alm_ingreso VARCHAR(20),
    @ErrorNumber NVARCHAR(16) OUTPUT,@ErrorMessage NVARCHAR(100) OUTPUT,@Error NVARCHAR(16) OUTPUT
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        INSERT INTO LnInventario(ccod_cia,id_cbinve,ccod_articulo,ccod_artSunat,cdsc_articulo,ncantidad,ncosto,ccod_alm,ccod_alm_ingreso,ccod_usuario)
        VALUES(@ccod_cia,@id_cbinve,@ccod_articulo,@ccod_artSunat,@cdsc_articulo,@ncantidad,@ncosto,@ccod_alm_salida,@ccod_alm_ingreso,@ccod_usuario);
        EXEC _stock_actualizar @ccod_cia,@ccod_alm_salida,@ccod_articulo,@ncantidad,@ncosto,-1;
        EXEC _stock_actualizar @ccod_cia,@ccod_alm_ingreso,@ccod_articulo,@ncantidad,@ncosto,1;
        SET @ErrorNumber='0';SET @ErrorMessage='';SET @Error='0';
    END TRY BEGIN CATCH SET @ErrorNumber=CAST(ERROR_NUMBER() AS NVARCHAR);SET @ErrorMessage=ERROR_MESSAGE();SET @Error='1'; END CATCH
END
GO

IF OBJECT_ID('webDatpos_ObtenerNumerador','P') IS NOT NULL DROP PROCEDURE webDatpos_ObtenerNumerador; 
GO
CREATE PROCEDURE webDatpos_ObtenerNumerador @ccod_cia VARCHAR(20),@tipo VARCHAR(10)
AS BEGIN SET NOCOUNT ON; SELECT TOP 1 id_ctalmac,cserie,nnumero,ctip_doc FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ctip_doc=@tipo; END
GO

IF OBJECT_ID('webDatpos_ObtenerDetalleGuiaRemision','P') IS NOT NULL DROP PROCEDURE webDatpos_ObtenerDetalleGuiaRemision; 
GO
CREATE PROCEDURE webDatpos_ObtenerDetalleGuiaRemision @ccod_cia VARCHAR(20),@id_cbinve INT
AS BEGIN SET NOCOUNT ON; SELECT L.*,A.cdsc_articulo FROM LnInventario L LEFT JOIN Articulos A ON A.ccod_articulo=L.ccod_articulo AND A.ccod_cia=L.ccod_cia WHERE L.ccod_cia=@ccod_cia AND L.id_cbinve=@id_cbinve; END
GO

IF OBJECT_ID('webDatpos_ConsultarAlamcenes','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarAlamcenes; 
GO
CREATE PROCEDURE webDatpos_ConsultarAlamcenes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_alm,cdsc_alm FROM Almacenes WHERE ccod_cia=@ccod_cia AND cstatus='A' ORDER BY ccod_alm; END
GO

IF OBJECT_ID('webDatpos_ConsultarOperaciones','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarOperaciones; 
GO
CREATE PROCEDURE webDatpos_ConsultarOperaciones @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_tipoper,cdsc_tipoper FROM TipoOperacion WHERE ccod_cia=@ccod_cia AND cstatus='A'; END
GO

IF OBJECT_ID('webDatpos_ConsultarCodigoAuxiliar','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarCodigoAuxiliar; 
GO
CREATE PROCEDURE webDatpos_ConsultarCodigoAuxiliar @ccod_cia VARCHAR(20),@cproveedor VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT ccod_coa,cdsc_coa,cruc_coa FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@cproveedor; END
GO

IF OBJECT_ID('webDatpos_InformeGuiaRemision','P') IS NOT NULL DROP PROCEDURE webDatpos_InformeGuiaRemision; 
GO
CREATE PROCEDURE webDatpos_InformeGuiaRemision @id_cbinve INT
AS BEGIN SET NOCOUNT ON; SELECT G.*,L.ccod_articulo,L.cdsc_articulo,L.ncantidad,L.ncosto FROM CbGuia G LEFT JOIN LnInventario L ON L.id_cbinve=G.id_cbinve WHERE G.id_cbinve=@id_cbinve; END
GO

IF OBJECT_ID('webDatpos_ConsultarGuiaRemision','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarGuiaRemision; 
GO
CREATE PROCEDURE webDatpos_ConsultarGuiaRemision @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON; SELECT G.id_cbinve,G.ccod_guia,G.cserie_guia,G.flag,G.ntotal,G.fchEmision,G.ccod_alm FROM CbGuia G WHERE G.ccod_cia=@ccod_cia ORDER BY G.fchEmision DESC; END
GO

IF OBJECT_ID('webDatpos_ObtenerGuiaRemision','P') IS NOT NULL DROP PROCEDURE webDatpos_ObtenerGuiaRemision; 
GO
CREATE PROCEDURE webDatpos_ObtenerGuiaRemision @ccod_cia VARCHAR(20),@id_cbinve INT
AS BEGIN SET NOCOUNT ON; SELECT * FROM CbGuia WHERE ccod_cia=@ccod_cia AND id_cbinve=@id_cbinve; END
GO

PRINT '✓ SPs Guia Remision creados.';
GO
