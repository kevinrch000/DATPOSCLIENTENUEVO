/* =====================================================================
   FIX 50 - Coa.ccod_coa = RUC SUNAT + Guia de Remision FK_CbGuia_Coa
   =====================================================================

   PROBLEMA 1 (FK_CbGuia_Coa)
   --------------------------
   Al guardar una Guia de Remision, el SP webDatpos_InsertarGuia ->
   InsertarGuiaVentaCompra inserta CbGuia con el ccod_coa enviado por el
   frontend. CbGuia tiene la FK FK_CbGuia_Coa(ccod_cia, ccod_coa) ->
   Coa(ccod_cia, ccod_coa). El frontend mandaba ccod_coa='' cuando el
   usuario no abria el modal "Buscar Proveedor", asi que la FK
   fallaba con codigo 547.

   Solucion:
   - DA/DAGuiaRemision.php convierte ccod_coa='' a NULL antes de invocar
     el SP. CbGuia.ccod_coa es NULLABLE asi que NULL satisface la FK.
   - GuiaRemision.js ahora copia el RUC al hidden #ccod_coa cuando el
     usuario escribe un RUC en IdProveedor / IdDestino, e invoca
     EnsureCoaByRuc para upsert del registro Coa.

   PROBLEMA 2 (Coa.ccod_coa debe ser RUC SUNAT)
   --------------------------------------------
   El campo Coa.ccod_coa antes era un codigo arbitrario (con cruc_coa
   como columna separada). A partir de este fix, el convenio es:
   ccod_coa = cruc_coa = RUC SUNAT del asociado (o DNI si no hay RUC).
   No se necesita cambio de esquema -- ccod_coa varchar(20) ya soporta
   el RUC. El SP webDatpos_insertarclientes ya respeta este convenio
   porque el frontend (Cliente1.js BuscarDatosRuc) fija tb_codigo=RUC.

   NUEVO SP webDatpos_EnsureCoaByRuc
   ---------------------------------
   Upsert idempotente de un registro Coa identificado por RUC. Lo usan:
   - Cliente1.js -> guardado normal (a traves del SP existente).
   - GuiaRemision.js -> cuando se escribe un RUC para Destinatario/Tercero,
     antes de guardar la Guia, asegura que el Coa exista (FK satisfecha).
   ===================================================================== */

PRINT '=== FIX 50: Coa.ccod_coa = RUC SUNAT + Guia EnsureCoaByRuc ===';

IF OBJECT_ID('webDatpos_EnsureCoaByRuc','P') IS NOT NULL
    DROP PROCEDURE webDatpos_EnsureCoaByRuc;
GO

CREATE PROCEDURE webDatpos_EnsureCoaByRuc
    @ccod_cia       VARCHAR(20),
    @ruc            VARCHAR(20),
    @razon_social   VARCHAR(200) = NULL,
    @direccion      VARCHAR(200) = NULL,
    @ubigeo         VARCHAR(10)  = NULL,
    @ccod_usuario   VARCHAR(50)  = NULL,
    @cproveedor     VARCHAR(1)   = '2',  -- '1'=Cliente '0'=Proveedor '2'=Otros '3'=Transportista
    @ctipo_coa      VARCHAR(10)  = NULL  -- '0'=Juridica '1'=Natural
AS
BEGIN
    SET NOCOUNT ON;

    DECLARE @ruc_clean VARCHAR(20) = LTRIM(RTRIM(ISNULL(@ruc, '')));
    IF @ruc_clean = '' RETURN;

    DECLARE @razon VARCHAR(200) = LTRIM(RTRIM(ISNULL(@razon_social, '')));
    DECLARE @direc VARCHAR(200) = LTRIM(RTRIM(ISNULL(@direccion, '')));
    DECLARE @ubi   VARCHAR(10)  = LTRIM(RTRIM(ISNULL(@ubigeo, '')));
    DECLARE @usr   VARCHAR(50)  = LTRIM(RTRIM(ISNULL(@ccod_usuario, '')));
    DECLARE @prov  VARCHAR(1)   = ISNULL(@cproveedor, '2');

    -- Si no se especifica, inferir tipo (0=Juridica si RUC empieza con 2 y tiene 11 digitos; 1=Natural en otro caso)
    DECLARE @tipo VARCHAR(10) = @ctipo_coa;
    IF @tipo IS NULL OR LTRIM(RTRIM(@tipo)) = ''
        SET @tipo = CASE
            WHEN LEN(@ruc_clean) = 11 AND LEFT(@ruc_clean, 1) = '2' THEN '0'
            ELSE '1'
        END;

    IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia = @ccod_cia AND ccod_coa = @ruc_clean)
    BEGIN
        INSERT INTO Coa (
            ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, cdirc_coa,
            cstatus, cproveedor, cruc_coa, ccod_usuario, ctipo_coa
        )
        VALUES (
            @ccod_cia, @ruc_clean, @ruc_clean, @razon, @direc,
            'A', @prov, @ruc_clean, @usr, @tipo
        );
    END
    ELSE
    BEGIN
        UPDATE Coa
        SET cdsc_coa  = CASE WHEN @razon <> '' THEN @razon ELSE cdsc_coa END,
            cdirc_coa = CASE WHEN @direc <> '' THEN @direc ELSE cdirc_coa END,
            cruc_coa  = @ruc_clean,
            ccod_usuario = CASE WHEN @usr <> '' THEN @usr ELSE ccod_usuario END
        WHERE ccod_cia = @ccod_cia AND ccod_coa = @ruc_clean;
    END

    -- Devolver el codigo (igual al RUC) para confirmacion del cliente
    SELECT @ruc_clean AS ccod_coa;
END
GO

PRINT '  webDatpos_EnsureCoaByRuc OK';

/* ---------------------------------------------------------------------
   webDatpos_insertarclientes -> ahora es UPSERT
   ---------------------------------------------------------------------
   Antes el SP solo insertaba si el ccod_coa no existia. Como
   webDatpos_EnsureCoaByRuc puede haber pre-insertado un registro stub
   (al consultar SUNAT desde Guia de Remision), si el usuario luego
   guarda manualmente al cliente en Clientes.php su info se perdia.
   Ahora si existe, se actualizan los campos editables manteniendo el
   convenio ccod_coa = RUC SUNAT.
   --------------------------------------------------------------------- */

IF OBJECT_ID('webDatpos_insertarclientes','P') IS NOT NULL
    DROP PROCEDURE webDatpos_insertarclientes;
GO

CREATE PROCEDURE webDatpos_insertarclientes
    @ccod_cia VARCHAR(20), @ccod_coa VARCHAR(20), @cdoc_coa VARCHAR(20), @cdsc_coa VARCHAR(200),
    @ctelf VARCHAR(20), @cmail VARCHAR(100), @ctipo_coa VARCHAR(10), @cpais VARCHAR(50),
    @cdepartamento VARCHAR(2), @cprovincia VARCHAR(4), @cdistrito VARCHAR(6),
    @cdirc_coa VARCHAR(200), @cstatus VARCHAR(1), @cproveedor VARCHAR(1),
    @ccod_usuario VARCHAR(50), @ctip_doc VARCHAR(10), @cruc_coa VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;

    -- Si no hay RUC explicito, usar el codigo como cruc (convenio: ccod_coa=RUC)
    DECLARE @ruc VARCHAR(20) = ISNULL(NULLIF(LTRIM(RTRIM(@cruc_coa)), ''), @ccod_coa);

    IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa)
    BEGIN
        INSERT INTO Coa (
            ccod_cia, ccod_coa, cdoc_coa, cdsc_coa, ctelf, cmail, ctipo_coa, cpais,
            cdepartamento, cprovincia, cdistrito, cdirc_coa, cstatus, cproveedor,
            ccod_usuario, cruc_coa
        ) VALUES (
            @ccod_cia, @ccod_coa, @cdoc_coa, @cdsc_coa, @ctelf, @cmail, @ctipo_coa, @cpais,
            @cdepartamento, @cprovincia, @cdistrito, @cdirc_coa, @cstatus, @cproveedor,
            @ccod_usuario, @ruc
        );
    END
    ELSE
    BEGIN
        UPDATE Coa
        SET cdoc_coa      = @cdoc_coa,
            cdsc_coa      = @cdsc_coa,
            ctelf         = @ctelf,
            cmail         = @cmail,
            ctipo_coa     = @ctipo_coa,
            cpais         = @cpais,
            cdepartamento = @cdepartamento,
            cprovincia    = @cprovincia,
            cdistrito     = @cdistrito,
            cdirc_coa     = @cdirc_coa,
            cstatus       = @cstatus,
            cproveedor    = @cproveedor,
            ccod_usuario  = @ccod_usuario,
            cruc_coa      = @ruc
        WHERE ccod_cia=@ccod_cia AND ccod_coa=@ccod_coa;
    END
END
GO

PRINT '  webDatpos_insertarclientes (upsert) OK';

PRINT '=== FIX 50 OK ===';
GO
