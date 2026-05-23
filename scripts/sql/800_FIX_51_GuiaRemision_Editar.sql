/* =====================================================================
   FIX 51 - Guia de Remision: Editar cabecera + persistir Destino
   =====================================================================

   PROBLEMA
   --------
   En GuiaRemision.php, al hacer doble-click sobre una guia de la pestania
   Lista y luego clicar Guardar, se generaba un registro NUEVO en lugar de
   actualizar el existente, porque no habia un SP de update y el JS no
   distinguia modo nuevo vs editar.

   Ademas:
   - "Tipo Op. Ingreso" y "Almacen Destino" no se mostraban al editar
     porque CbGuia.ccod_almDestino nunca se persistia (el SP solo guardaba
     ccod_almOrigen/ctipoOrigen/cserieOrigen) y no existian columnas para
     ctipoDestino / cserieDestino.

   SOLUCION
   --------
   1. Agregar columnas CbGuia.ctipoDestino y CbGuia.cserieDestino para que
      el lado "Almacen Destino" del formulario tenga donde guardarse.
   2. Reemplazar webDatpos_InsertarGuia para que persista origen Y destino
      (ccod_almOrigen, ctipoOrigen, cserieOrigen, ccod_almDestino,
       ctipoDestino, cserieDestino), y mantenga ccod_alm/ctipo/cserie con
      los datos de origen para compatibilidad con codigo viejo.
   3. Nuevo SP webDatpos_ActualizarGuia que UPDATEa la cabecera CbGuia por
      id_cbinve (no toca CbInventario ni LnInventario para no recalcular
      stock; los articulos no se editan via este flujo).
   ===================================================================== */

USE DatPos_EMP01;
GO

PRINT '=== FIX 51: Guia de Remision Editar + persistir Destino ===';

IF NOT EXISTS (SELECT 1 FROM sys.columns
               WHERE object_id = OBJECT_ID('dbo.CbGuia')
                 AND name = 'ctipoDestino')
BEGIN
    ALTER TABLE dbo.CbGuia ADD ctipoDestino VARCHAR(10) NULL;
    PRINT '  [+] Columna CbGuia.ctipoDestino agregada.';
END
ELSE
    PRINT '  [=] Columna CbGuia.ctipoDestino ya existe.';
GO

IF NOT EXISTS (SELECT 1 FROM sys.columns
               WHERE object_id = OBJECT_ID('dbo.CbGuia')
                 AND name = 'cserieDestino')
BEGIN
    ALTER TABLE dbo.CbGuia ADD cserieDestino VARCHAR(10) NULL;
    PRINT '  [+] Columna CbGuia.cserieDestino agregada.';
END
ELSE
    PRINT '  [=] Columna CbGuia.cserieDestino ya existe.';
GO

/* -------------------------------------------------------------------
   InsertarGuiaVentaCompra: aceptar y persistir tambien ccod_almDestino,
   ctipoDestino, cserieDestino. La firma original (sin destino) seguia
   funcionando para INSERT pero nunca persistia el lado destino, lo que
   hacia imposible recuperarlo al editar.
   ------------------------------------------------------------------- */
IF OBJECT_ID('InsertarGuiaVentaCompra','P') IS NOT NULL DROP PROCEDURE InsertarGuiaVentaCompra;
GO
CREATE PROCEDURE InsertarGuiaVentaCompra
    @ccod_cia VARCHAR(20),@ccod_guia VARCHAR(20),@cserie_guia VARCHAR(10),
    @cnum_ruc_rem VARCHAR(20),@cnom_rzn_soc_rem VARCHAR(200),@cnum_ruc_dest VARCHAR(20),@cnom_rzn_soc_dest VARCHAR(200),
    @cnum_ruc_proy VARCHAR(20),@cdsc_coa VARCHAR(200),@cdomicilio_partida VARCHAR(300),@ccod_ubi_partida VARCHAR(10),
    @cdomicilio_llegada VARCHAR(300),@ccod_ubi_llegada VARCHAR(10),@ctrans_nombre VARCHAR(200),@ctrans_ruc VARCHAR(20),
    @ccod_unid_peso_bruto VARCHAR(10),@nmnt_tot_peso_bruto DECIMAL(18,4),@cdesc_motiv_tras VARCHAR(200),
    @nobs VARCHAR(500),@ctrans_placa VARCHAR(20),@ctrans_licencia VARCHAR(20),@ntotal DECIMAL(18,4),
    @cusu_crea VARCHAR(50),
    @ccod_almOrigen VARCHAR(20),@ctipoOrigen VARCHAR(10),@cserieOrigen VARCHAR(10),
    @ccod_almDestino VARCHAR(20),@ctipoDestino VARCHAR(10),@cserieDestino VARCHAR(10),
    @dfec_fin DATE,@cdoc_ref VARCHAR(20),@cod_tip_cpe VARCHAR(10),@ccod_coa VARCHAR(20),@flag VARCHAR(1),
    @id_cbinve INT OUTPUT,@numero NVARCHAR(100) OUTPUT,@fchEmision NVARCHAR(100) OUTPUT
AS BEGIN SET NOCOUNT ON;
    DECLARE @nnumero INT;
    SELECT @nnumero = ISNULL(MAX(nnumero),0) + 1
      FROM CbInventario
     WHERE ccod_cia = @ccod_cia
       AND ccod_alm = @ccod_almOrigen
       AND ctipo    = @ctipoOrigen;

    INSERT INTO CbInventario(ccod_cia,ccod_alm,dfecha,ctipo,vserie,nnumero,vobservacion,ccod_usuario,ccod_coa,ntotal)
    VALUES(@ccod_cia,@ccod_almOrigen,GETDATE(),@ctipoOrigen,@cserieOrigen,@nnumero,@nobs,@cusu_crea,@ccod_coa,@ntotal);
    SET @id_cbinve = SCOPE_IDENTITY();

    INSERT INTO CbGuia(
        ccod_cia,ccod_guia,cserie_guia,cnum_ruc_rem,cnom_rzn_soc_rem,cnum_ruc_dest,cnom_rzn_soc_dest,
        cnum_ruc_proy,cdsc_coa,cdomicilio_partida,ccod_ubi_partida,cdomicilio_llegada,ccod_ubi_llegada,
        ctrans_nombre,ctrans_ruc,ccod_unid_peso_bruto,nmnt_tot_peso_bruto,cdesc_motiv_tras,nobs,
        ctrans_placa,ctrans_licencia,ntotal,cusu_crea,
        ccod_alm,ctipo,cserie,
        ccod_almOrigen,ccod_almDestino,ctipoDestino,cserieDestino,
        dfec_fin,cdoc_ref,cod_tip_cpe,ccod_coa,flag,id_cbinve,nnumero,fchEmision)
    VALUES(
        @ccod_cia,@ccod_guia,@cserie_guia,@cnum_ruc_rem,@cnom_rzn_soc_rem,@cnum_ruc_dest,@cnom_rzn_soc_dest,
        @cnum_ruc_proy,@cdsc_coa,@cdomicilio_partida,@ccod_ubi_partida,@cdomicilio_llegada,@ccod_ubi_llegada,
        @ctrans_nombre,@ctrans_ruc,@ccod_unid_peso_bruto,@nmnt_tot_peso_bruto,@cdesc_motiv_tras,@nobs,
        @ctrans_placa,@ctrans_licencia,@ntotal,@cusu_crea,
        @ccod_almOrigen,@ctipoOrigen,@cserieOrigen,
        @ccod_almOrigen,@ccod_almDestino,@ctipoDestino,@cserieDestino,
        @dfec_fin,@cdoc_ref,@cod_tip_cpe,@ccod_coa,@flag,
        @id_cbinve,CAST(@nnumero AS VARCHAR),CONVERT(NVARCHAR,GETDATE(),120));

    SET @numero     = ISNULL(@cserieOrigen,'') + '-' + RIGHT('00000000' + CAST(@nnumero AS VARCHAR),8);
    SET @fchEmision = CONVERT(NVARCHAR,GETDATE(),120);
END
GO
PRINT '  [+] InsertarGuiaVentaCompra recreado (acepta destino).';
GO

/* -------------------------------------------------------------------
   webDatpos_InsertarGuia: reenvia los 6 campos de origen y destino.
   ------------------------------------------------------------------- */
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
    EXEC InsertarGuiaVentaCompra
        @ccod_cia,@ccod_guia,@cserie_guia,@cnum_ruc_rem,@cnom_rzn_soc_rem,@cnum_ruc_dest,@cnom_rzn_soc_dest,
        @cnum_ruc_proy,@cdsc_coa,@cdomicilio_partida,@ccod_ubi_partida,@cdomicilio_llegada,@ccod_ubi_llegada,
        @ctrans_nombre,@ctrans_ruc,@ccod_unid_peso_bruto,@nmnt_tot_peso_bruto,@cdesc_motiv_tras,@nobs,
        @ctrans_placa,@ctrans_licencia,@ntotal,@cusu_crea,
        @ccod_almOrigen,@ctipoOrigen,@cserieOrigen,
        @ccod_almDestino,@ctipoDestino,@cserieDestino,
        @dfec_fin,@cdoc_ref,@cod_tip_cpe,@ccod_coa,'T',
        @id_cbinve OUTPUT,@numero OUTPUT,@fchEmision OUTPUT;
END
GO
PRINT '  [+] webDatpos_InsertarGuia recreado (forward destino).';
GO

/* -------------------------------------------------------------------
   webDatpos_ActualizarGuia: actualiza SOLO la cabecera CbGuia por id_cbinve.
   No toca CbInventario / LnInventario para no recalcular stock; los
   articulos no se editan via este flujo (en el form ya estan deshabilitados
   al editar). Devuelve 1 si actualizo, 0 si no se encontro la guia.
   ------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_ActualizarGuia','P') IS NOT NULL DROP PROCEDURE webDatpos_ActualizarGuia;
GO
CREATE PROCEDURE webDatpos_ActualizarGuia
    @ccod_cia VARCHAR(20),@id_cbinve INT,
    @ccod_guia VARCHAR(20),@cserie_guia VARCHAR(10),
    @cnum_ruc_rem VARCHAR(20),@cnom_rzn_soc_rem VARCHAR(200),
    @cnum_ruc_dest VARCHAR(20),@cnom_rzn_soc_dest VARCHAR(200),
    @cnum_ruc_proy VARCHAR(20),@cdsc_coa VARCHAR(200),
    @cdomicilio_partida VARCHAR(300),@ccod_ubi_partida VARCHAR(10),
    @cdomicilio_llegada VARCHAR(300),@ccod_ubi_llegada VARCHAR(10),
    @ctrans_nombre VARCHAR(200),@ctrans_ruc VARCHAR(20),
    @ccod_unid_peso_bruto VARCHAR(10),@nmnt_tot_peso_bruto DECIMAL(18,4),
    @cdesc_motiv_tras VARCHAR(200),@nobs VARCHAR(500),
    @ctrans_placa VARCHAR(20),@ctrans_licencia VARCHAR(20),
    @ccod_almOrigen VARCHAR(20),@ctipoOrigen VARCHAR(10),@cserieOrigen VARCHAR(10),
    @ccod_almDestino VARCHAR(20),@ctipoDestino VARCHAR(10),@cserieDestino VARCHAR(10),
    @dfec_fin DATE,@cdoc_ref VARCHAR(20),@cod_tip_cpe VARCHAR(10),@ccod_coa VARCHAR(20),
    @rowcount INT OUTPUT
AS BEGIN SET NOCOUNT ON;
    UPDATE dbo.CbGuia
       SET ccod_guia            = @ccod_guia,
           cserie_guia          = @cserie_guia,
           cnum_ruc_rem         = @cnum_ruc_rem,
           cnom_rzn_soc_rem     = @cnom_rzn_soc_rem,
           cnum_ruc_dest        = @cnum_ruc_dest,
           cnom_rzn_soc_dest    = @cnom_rzn_soc_dest,
           cnum_ruc_proy        = @cnum_ruc_proy,
           cdsc_coa             = @cdsc_coa,
           cdomicilio_partida   = @cdomicilio_partida,
           ccod_ubi_partida     = @ccod_ubi_partida,
           cdomicilio_llegada   = @cdomicilio_llegada,
           ccod_ubi_llegada     = @ccod_ubi_llegada,
           ctrans_nombre        = @ctrans_nombre,
           ctrans_ruc           = @ctrans_ruc,
           ccod_unid_peso_bruto = @ccod_unid_peso_bruto,
           nmnt_tot_peso_bruto  = @nmnt_tot_peso_bruto,
           cdesc_motiv_tras     = @cdesc_motiv_tras,
           nobs                 = @nobs,
           ctrans_placa         = @ctrans_placa,
           ctrans_licencia      = @ctrans_licencia,
           ccod_alm             = @ccod_almOrigen,
           ctipo                = @ctipoOrigen,
           cserie               = @cserieOrigen,
           ccod_almOrigen       = @ccod_almOrigen,
           ccod_almDestino      = @ccod_almDestino,
           ctipoDestino         = @ctipoDestino,
           cserieDestino        = @cserieDestino,
           dfec_fin             = @dfec_fin,
           cdoc_ref             = @cdoc_ref,
           cod_tip_cpe          = @cod_tip_cpe,
           ccod_coa             = NULLIF(LTRIM(RTRIM(@ccod_coa)),'')
     WHERE ccod_cia  = @ccod_cia
       AND id_cbinve = @id_cbinve;
    SET @rowcount = @@ROWCOUNT;
END
GO
PRINT '  [+] webDatpos_ActualizarGuia creado.';
GO

PRINT '=== FIX 51 aplicado. ===';
GO
