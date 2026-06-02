/* =====================================================================
   FIX 20 — Almacenes SPs: Alinear parámetros y columnas con VB/DA
   
   Problema: La página Almacenes.aspx no muestra datos ni permite
   crear/editar/eliminar porque los stored procedures tienen parámetros
   y columnas que no coinciden con lo que el código VB Data Access envía.
   
   Estrategia: SOLO se modifican los SPs. El código VB/DA/JS NO se toca.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* =====================================================================
   1. sp_consultaalmacenes — Lista todos los almacenes
   
   DA llama: cmd.Parameters.Add("@ccod_cia", obj.ccod_empresa)
             cmd.CommandText = "sp_consultaalmacenes"
   
   VB ConsultarAlmacenes() lee por index:
     [0] = ccod_alm (usado como checkbox id Y código)
     [1] = cdsc_alm
     [2] = cstatus (mapeado a 'estado')
   
   ANTES: SELECT id_almac,ccod_alm,cdsc_alm,cstatus → index shift
   AHORA: SELECT ccod_alm,cdsc_alm,cstatus
===================================================================== */
IF OBJECT_ID('sp_consultaalmacenes','P') IS NOT NULL DROP PROCEDURE sp_consultaalmacenes;
GO
CREATE PROCEDURE sp_consultaalmacenes
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_alm, cdsc_alm,
           CASE WHEN cstatus = 'A' THEN 'Activo' ELSE 'Inactivo' END AS cstatus
    FROM Almacenes
    WHERE ccod_cia = @ccod_cia
    ORDER BY ccod_alm;
END
GO
PRINT 'OK: sp_consultaalmacenes (3 columnas: ccod_alm, cdsc_alm, estado)';
GO

/* =====================================================================
   2. sp_consultaalmacen — Consulta un almacén por código
   
   DA llama: cmd.Parameters.Add("@ccod_cia", obj.ccod_empresa)
             cmd.Parameters.Add("@codigo", codigo)
             cmd.CommandText = "sp_consultaalmacen"
   
   VB ConsultarAlmacen() lee por index:
     [0] = ccod_alm
     [1] = cdsc_alm
     [2] = cstatus
     [3] = cdepartamento
     [4] = cprovincia
     [5] = cdistrito
     [6] = cdirc_almac
     [7] = curba_almac
     [8] = cubigeo
   
   ANTES: @ccod_alm param + SELECT * (12+ columnas con id_almac como [0])
   AHORA: @codigo param + SELECT las 9 columnas en el orden exacto
===================================================================== */
IF OBJECT_ID('sp_consultaalmacen','P') IS NOT NULL DROP PROCEDURE sp_consultaalmacen;
GO
CREATE PROCEDURE sp_consultaalmacen
    @ccod_cia VARCHAR(20),
    @codigo   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ccod_alm,                                                          -- [0]
        cdsc_alm,                                                          -- [1]
        CASE WHEN cstatus = 'A' THEN 1 ELSE 0 END                AS cstatus,       -- [2] Integer: 1=Activo, 0=Inactivo
        ISNULL(cdepartamento, '')                                 AS cdepartamento,  -- [3]
        ISNULL(cprovincia, '')                                    AS cprovincia,      -- [4]
        ISNULL(cdistrito, '')                                     AS cdistrito,       -- [5]
        ISNULL(cdirc_almac, '')                                   AS cdirc_almac,     -- [6]
        ISNULL(curba_almac, '')                                   AS curba_almac,     -- [7]
        ISNULL(cubigeo, '')                                       AS cubigeo          -- [8]
    FROM Almacenes
    WHERE ccod_cia = @ccod_cia AND ccod_alm = @codigo;
END
GO
PRINT 'OK: sp_consultaalmacen (@codigo param, 9 columnas exactas)';
GO

/* =====================================================================
   3. webDatpos_insertarAlmacen — Insertar almacén
   
   DA InsertarAlmacen() envía:
     @ccod_alm, @ccod_cia, @cdsc_alm, @cstatus, @ccod_usuario,
     @cdepartamento, @cprovincia, @cdistrito, @cdirc_almac, @curba_almac, @cubigeo
     OUTPUT: @ErrorNumber, @ErrorMessage, @id_ctalmac
   
   ANTES: Esperaba @ccod_empresa, no tenía @cstatus ni output params
   AHORA: Acepta los parámetros exactos que DA envía
===================================================================== */
IF OBJECT_ID('webDatpos_insertarAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarAlmacen;
GO
CREATE PROCEDURE webDatpos_insertarAlmacen
    @ccod_alm        VARCHAR(20),
    @ccod_cia        VARCHAR(20),
    @cdsc_alm        VARCHAR(100),
    @cstatus         VARCHAR(1),
    @ccod_usuario    VARCHAR(50),
    @cdepartamento   VARCHAR(100) = '',
    @cprovincia      VARCHAR(100) = '',
    @cdistrito       VARCHAR(100) = '',
    @cdirc_almac     VARCHAR(200) = '',
    @curba_almac     VARCHAR(100) = '',
    @cubigeo         VARCHAR(6)   = '',
    @ErrorNumber     NVARCHAR(16)  OUTPUT,
    @ErrorMessage    NVARCHAR(100) OUTPUT,
    @id_ctalmac      NVARCHAR(16)  OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @ErrorNumber  = 'OK';
    SET @ErrorMessage = '';
    SET @id_ctalmac   = '';
    -- Convertir 1/0 (del dropdown) a A/I (de la BD)
    DECLARE @cstatus_db VARCHAR(1) = CASE WHEN @cstatus = '1' OR @cstatus = 'A' THEN 'A' ELSE 'I' END;
    BEGIN TRY
        INSERT INTO Almacenes (ccod_cia, ccod_alm, cdsc_alm, cstatus,
            cdepartamento, cprovincia, cdistrito, cdirc_almac, curba_almac, cubigeo, ccod_usuario)
        VALUES (@ccod_cia, @ccod_alm, @cdsc_alm, @cstatus_db,
            @cdepartamento, @cprovincia, @cdistrito, @cdirc_almac, @curba_almac, @cubigeo, @ccod_usuario);
        
        SET @id_ctalmac = CAST(SCOPE_IDENTITY() AS NVARCHAR(16));
    END TRY
    BEGIN CATCH
        SET @ErrorNumber  = CAST(ERROR_NUMBER() AS NVARCHAR(16));
        SET @ErrorMessage = ERROR_MESSAGE();
    END CATCH
END
GO
PRINT 'OK: webDatpos_insertarAlmacen (@ccod_cia, @cstatus, output params)';
GO

/* =====================================================================
   4. webDatpos_editaralmacen — Editar almacén
   
   DA EditarAlmacen() envía:
     @ccod_alm, @ccod_cia, @cdsc_alm, @cstatus, @ccod_usuario,
     @cdepartamento, @cprovincia, @cdistrito, @cdirc_almac, @curba_almac, @cubigeo
     OUTPUT: @ErrorNumber
   
   ANTES: Esperaba @ccod_empresa, no tenía @cstatus ni @ErrorNumber
   AHORA: Acepta los parámetros exactos que DA envía
===================================================================== */
IF OBJECT_ID('webDatpos_editaralmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_editaralmacen;
GO
CREATE PROCEDURE webDatpos_editaralmacen
    @ccod_alm        VARCHAR(20),
    @ccod_cia        VARCHAR(20),
    @cdsc_alm        VARCHAR(100),
    @cstatus         VARCHAR(1),
    @ccod_usuario    VARCHAR(50),
    @cdepartamento   VARCHAR(100) = '',
    @cprovincia      VARCHAR(100) = '',
    @cdistrito       VARCHAR(100) = '',
    @cdirc_almac     VARCHAR(200) = '',
    @curba_almac     VARCHAR(100) = '',
    @cubigeo         VARCHAR(6)   = '',
    @ErrorNumber     NVARCHAR(16)  OUTPUT
AS BEGIN SET NOCOUNT ON;
    SET @ErrorNumber = 'OK';
    -- Convertir 1/0 (del dropdown) a A/I (de la BD)
    DECLARE @cstatus_db VARCHAR(1) = CASE WHEN @cstatus = '1' OR @cstatus = 'A' THEN 'A' ELSE 'I' END;
    BEGIN TRY
        UPDATE Almacenes
        SET cdsc_alm      = @cdsc_alm,
            cstatus       = @cstatus_db,
            cdepartamento = @cdepartamento,
            cprovincia    = @cprovincia,
            cdistrito     = @cdistrito,
            cdirc_almac   = @cdirc_almac,
            curba_almac   = @curba_almac,
            cubigeo       = @cubigeo,
            ccod_usuario  = @ccod_usuario
        WHERE ccod_cia = @ccod_cia AND ccod_alm = @ccod_alm;
    END TRY
    BEGIN CATCH
        SET @ErrorNumber = CAST(ERROR_NUMBER() AS NVARCHAR(16));
    END CATCH
END
GO
PRINT 'OK: webDatpos_editaralmacen (@ccod_cia, @cstatus, @ErrorNumber output)';
GO

/* =====================================================================
   5. sp_eliminaralmacen — Eliminar (desactivar) almacén
   
   DA EliminarAlmacen() envía:
     @ccod_alm, @ccod_cia
     cmd.CommandText = "sp_eliminaralmacen"
     Usa selectstored → espera DataTable resultado
   
   VB Eliminar() lee resultado:
     [0] = ccod_alm (compara con 'OK' o '547')
     [1] = cdsc_alm (mensaje de error)
   
   ANTES: Esperaba @ccod_empresa, no retornaba nada
   AHORA: Acepta @ccod_cia, @ccod_alm y retorna OK/error
===================================================================== */
IF OBJECT_ID('sp_eliminaralmacen','P') IS NOT NULL DROP PROCEDURE sp_eliminaralmacen;
GO
CREATE PROCEDURE sp_eliminaralmacen
    @ccod_alm VARCHAR(20),
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        -- Verificar si el almacén está asignado a alguna tienda
        IF EXISTS (SELECT 1 FROM TiendaAlmacen WHERE ccod_cia = @ccod_cia AND ccod_alm = @ccod_alm)
        BEGIN
            SELECT '547' AS ccod_alm, 'El almacén está asignado a una tienda' AS cdsc_alm;
            RETURN;
        END
        
        -- Desactivar el almacén
        UPDATE Almacenes SET cstatus = 'I' WHERE ccod_cia = @ccod_cia AND ccod_alm = @ccod_alm;
        SELECT 'OK' AS ccod_alm, '' AS cdsc_alm;
    END TRY
    BEGIN CATCH
        SELECT CAST(ERROR_NUMBER() AS VARCHAR(20)) AS ccod_alm, ERROR_MESSAGE() AS cdsc_alm;
    END CATCH
END
GO
PRINT 'OK: sp_eliminaralmacen (@ccod_alm, @ccod_cia, retorna OK/547/error)';
GO

/* =====================================================================
   6. webDatpos_insertarNumeradorAlmacen — Insertar numerador de almacén
   
   DA InsertarAlmacen() (dentro de transacción) envía:
     @ccod_cia, @ccod_usuario, @ccod_alm, @ctip_doc, @cserie, @nnumero,
     @cdsc_numeralmacen, @id_ctalmac
     OUTPUT: @ErrorNumber, @ErrorMessage, @Error
   
   ANTES: No tenía @cdsc_numeralmacen, @ErrorNumber, @ErrorMessage, @Error
   AHORA: Acepta todos los parámetros que DA envía
===================================================================== */
IF OBJECT_ID('webDatpos_insertarNumeradorAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarNumeradorAlmacen;
GO
CREATE PROCEDURE webDatpos_insertarNumeradorAlmacen
    @ccod_cia          VARCHAR(20),
    @ccod_usuario      VARCHAR(50),
    @ccod_alm          VARCHAR(20),
    @ctip_doc          VARCHAR(10),
    @cserie            VARCHAR(10),
    @nnumero           INT,
    @cdsc_numeralmacen NVARCHAR(100) = '',
    @ErrorNumber       NVARCHAR(16)  OUTPUT,
    @ErrorMessage      NVARCHAR(100) OUTPUT,
    @Error             NVARCHAR(16)  OUTPUT,
    @id_ctalmac        VARCHAR(20) = ''
AS BEGIN SET NOCOUNT ON;
    SET @ErrorNumber  = 'OK';
    SET @ErrorMessage = '';
    SET @Error        = '';
    BEGIN TRY
        INSERT INTO NumeradorAlmacen (ccod_cia, ccod_alm, ctip_doc, cserie, nnumero, cdsc_numeralmacen, ccod_usuario)
        VALUES (@ccod_cia, @ccod_alm, @ctip_doc, @cserie, @nnumero, @cdsc_numeralmacen, @ccod_usuario);
    END TRY
    BEGIN CATCH
        SET @ErrorNumber  = CAST(ERROR_NUMBER() AS NVARCHAR(16));
        SET @ErrorMessage = ERROR_MESSAGE();
        SET @Error        = @cserie + '-' + @ctip_doc;
    END CATCH
END
GO
PRINT 'OK: webDatpos_insertarNumeradorAlmacen (con @cdsc_numeralmacen, output params)';
GO

/* =====================================================================
   7. webDatpos_consultarNumeradoresAlmacen — Consultar numeradores
   
   DA envía: @ccod_cia, @ccod_alm
   
   VB ConsultarNumeradoresAlmacen() lee por index:
     [0] = cdoc_tipo
     [1] = cdoc_serie
     [2] = cdoc_nro
     [3] = cdsc_numer
   
   JS CargarTablaNumerador() lee:
     cdoc_tipo, cdsc_numer, cdoc_serie, cdoc_nro
   
   ANTES: SELECT id_ctalmac,ctip_doc,cserie,nnumero (4 cols, wrong order)
   AHORA: SELECT ctip_doc,cserie,nnumero,description con alias correctos
===================================================================== */
IF OBJECT_ID('webDatpos_consultarNumeradoresAlmacen','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarNumeradoresAlmacen;
GO
CREATE PROCEDURE webDatpos_consultarNumeradoresAlmacen
    @ccod_cia VARCHAR(20),
    @ccod_alm VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ctip_doc                                                   AS cdoc_tipo,   -- [0] VB: cdoc_tipo
        cserie                                                     AS cdoc_serie,  -- [1] VB: cdoc_serie
        nnumero                                                    AS cdoc_nro,    -- [2] VB: cdoc_nro
        ISNULL(NULLIF(cdsc_numeralmacen,''),
            CASE ctip_doc WHEN 'I' THEN 'Ingreso' WHEN 'S' THEN 'Salida' ELSE ctip_doc END
        ) AS cdsc_numer  -- [3] VB: cdsc_numer
    FROM NumeradorAlmacen
    WHERE ccod_cia = @ccod_cia AND ccod_alm = @ccod_alm;
END
GO
PRINT 'OK: webDatpos_consultarNumeradoresAlmacen (4 cols: cdoc_tipo, cdoc_serie, cdoc_nro, cdsc_numer)';
GO

/* =====================================================================
   8. webDatpos_eliminarNumeradoresAlmacen — Sin cambios necesarios
   DA envía @ccod_cia, @ccod_alm — coincide con SP
===================================================================== */
-- No requiere cambios

/* =====================================================================
   8b. sp_consultaalmacenesactivos — DA envía @ccod_cia, SP esperaba @ccod_empresa
===================================================================== */
IF OBJECT_ID('sp_consultaalmacenesactivos','P') IS NOT NULL DROP PROCEDURE sp_consultaalmacenesactivos;
GO
CREATE PROCEDURE sp_consultaalmacenesactivos
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_alm, cdsc_alm FROM Almacenes
    WHERE ccod_cia = @ccod_cia AND cstatus = 'A' ORDER BY ccod_alm;
END
GO
PRINT 'OK: sp_consultaalmacenesactivos (@ccod_cia)';
GO

/* =====================================================================
   8c. sp_consultaalmempactivos — DA envía @ccod_tiend + @ccod_cia,
       SP solo tenía @ccod_empresa
===================================================================== */
IF OBJECT_ID('sp_consultaalmempactivos','P') IS NOT NULL DROP PROCEDURE sp_consultaalmempactivos;
GO
CREATE PROCEDURE sp_consultaalmempactivos
    @ccod_tiend VARCHAR(20),
    @ccod_cia   VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_alm, cdsc_alm FROM Almacenes
    WHERE ccod_cia = @ccod_cia AND cstatus = 'A' ORDER BY ccod_alm;
END
GO
PRINT 'OK: sp_consultaalmempactivos (@ccod_tiend, @ccod_cia)';
GO

/* =====================================================================
   9. Verificar datos seed — Al menos un almacén debe existir
===================================================================== */
IF NOT EXISTS (SELECT 1 FROM Almacenes WHERE ccod_cia = 'EMP01')
BEGIN
    INSERT INTO Almacenes (ccod_cia, ccod_alm, cdsc_alm, cstatus, ccod_usuario)
    VALUES 
        ('EMP01', '001', 'Almacén Principal', 'A', 'ADMIN'),
        ('EMP01', '002', 'Almacén Secundario', 'A', 'ADMIN');
    PRINT 'SEED: Insertados 2 almacenes de ejemplo.';
END
ELSE
BEGIN
    PRINT 'INFO: Ya existen almacenes para EMP01.';
END
GO

/* =====================================================================
   10. Verificación final
===================================================================== */
-- Listar SPs relevantes
SELECT name AS sp_almacen FROM sys.procedures
WHERE name IN (
    'sp_consultaalmacenes',
    'sp_consultaalmacen',
    'sp_consultaalmacenesactivos',
    'sp_consultaalmacenesdispo',
    'sp_consultaalmempactivos',
    'sp_limpiartiendasalmacen',
    'sp_asignartiendaalmacen',
    'sp_consultartiendaalmacenes',
    'sp_eliminaralmacen',
    'webDatpos_insertarAlmacen',
    'webDatpos_editaralmacen',
    'webDatpos_insertarNumeradorAlmacen',
    'webDatpos_eliminarNumeradoresAlmacen',
    'webDatpos_consultarNumeradoresAlmacen'
)
ORDER BY name;
GO

-- Mostrar almacenes actuales
SELECT ccod_alm, cdsc_alm, cstatus FROM Almacenes WHERE ccod_cia = 'EMP01';
GO

-- Mostrar numeradores actuales
SELECT NA.ccod_alm, NA.ctip_doc, NA.cserie, NA.nnumero
FROM NumeradorAlmacen NA
WHERE NA.ccod_cia = 'EMP01';
GO

PRINT '============================================';
PRINT 'FIX 20 completo — Almacenes SPs alineados.';
PRINT 'Recargar http://localhost:22094/Tablas/Almacenes.aspx';
PRINT '============================================';
GO
