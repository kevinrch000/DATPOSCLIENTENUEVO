/* =====================================================================
   FIX 23 — UnidadMedida: Agregar columnas, arreglar SPs y validación
   
   Problema:
   1. Tabla no tiene csim_unimed (etiqueta) ni ccod_tributario
   2. SPs no almacenan esos valores
   3. Insert/Edit no retornan 'OK' como espera el JS
   4. JS valida exactamente 5 dígitos, pero códigos pueden ser menores
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

-- 1. Agregar columnas faltantes
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('UnidadMedida') AND name='csim_unimed')
    ALTER TABLE UnidadMedida ADD csim_unimed VARCHAR(20) NULL;
GO

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('UnidadMedida') AND name='ccod_tributario')
    ALTER TABLE UnidadMedida ADD ccod_tributario VARCHAR(20) NULL;
GO

PRINT 'OK: Columnas csim_unimed y ccod_tributario agregadas';
GO

-- 2. webDatpos_consultarUnidadMedida (lista)
IF OBJECT_ID('webDatpos_consultarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_consultarUnidadMedida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_unimed                                  AS id_unidadmedida,       -- [0]
        ccod_unimed                                AS ccod_unidadmedida,     -- [1]
        ISNULL(csim_unimed, ccod_unimed)           AS csim_unidadmedida,     -- [2]
        ISNULL(cdsc_unimed, '')                    AS cdsc_unidadmedida,     -- [3]
        CASE WHEN cstatus='A' THEN 'Activo' ELSE 'Inactivo' END AS cstatus, -- [4]
        ISNULL(ccod_tributario, '')                 AS ccod_tributario        -- [5]
    FROM UnidadMedida
    WHERE ccod_cia=@ccod_cia
    ORDER BY ccod_unimed;
END
GO
PRINT 'OK: webDatpos_consultarUnidadMedida';
GO

-- 3. webDatpos_consultarCodigoUnidadMedida (detalle)
IF OBJECT_ID('webDatpos_consultarCodigoUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCodigoUnidadMedida;
GO
CREATE PROCEDURE webDatpos_consultarCodigoUnidadMedida @ccod_cia VARCHAR(20), @ccod_unidadmedida VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_unimed                                  AS id_unidadmedida,       -- [0]
        ccod_unimed                                AS ccod_unidadmedida,     -- [1]
        ISNULL(csim_unimed, ccod_unimed)           AS csim_unidadmedida,     -- [2]
        ISNULL(cdsc_unimed, '')                    AS cdsc_unidadmedida,     -- [3]
        CASE WHEN cstatus='A' THEN 1 ELSE 0 END   AS cstatus,              -- [4] Integer
        ISNULL(ccod_tributario, '')                 AS ccod_tributario        -- [5]
    FROM UnidadMedida
    WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
END
GO
PRINT 'OK: webDatpos_consultarCodigoUnidadMedida';
GO

-- 4. webDatpos_insertarUnidadMedida
IF OBJECT_ID('webDatpos_insertarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_insertarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50),
    @ccod_unidadmedida VARCHAR(10), @csim_unidadmedida VARCHAR(20),
    @cdsc_unidadmedida VARCHAR(50), @cstatus VARCHAR(1), @ccod_tributario VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @cstatus_db VARCHAR(1) = CASE WHEN @cstatus='1' OR @cstatus='A' THEN 'A' ELSE 'I' END;
    BEGIN TRY
        INSERT INTO UnidadMedida(ccod_cia, ccod_unimed, csim_unimed, cdsc_unimed, cstatus, ccod_tributario, ccod_usuario)
        VALUES(@ccod_cia, @ccod_unidadmedida, @csim_unidadmedida, @cdsc_unidadmedida, @cstatus_db, @ccod_tributario, @ccod_usuario);
        SELECT 'OK' AS ccod_unidadmedida, '' AS cdsc_unidadmedida;
    END TRY
    BEGIN CATCH
        SELECT CAST(ERROR_NUMBER() AS VARCHAR(20)) AS ccod_unidadmedida, ERROR_MESSAGE() AS cdsc_unidadmedida;
    END CATCH
END
GO
PRINT 'OK: webDatpos_insertarUnidadMedida';
GO

-- 5. webDatpos_editarUnidadMedida
IF OBJECT_ID('webDatpos_editarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_editarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_editarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50),
    @ccod_unidadmedida VARCHAR(10), @csim_unidadmedida VARCHAR(20),
    @cdsc_unidadmedida VARCHAR(50), @cstatus VARCHAR(1), @ccod_tributario VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DECLARE @cstatus_db VARCHAR(1) = CASE WHEN @cstatus='1' OR @cstatus='A' THEN 'A' ELSE 'I' END;
    BEGIN TRY
        UPDATE UnidadMedida
        SET cdsc_unimed=@cdsc_unidadmedida, csim_unimed=@csim_unidadmedida,
            cstatus=@cstatus_db, ccod_tributario=@ccod_tributario, ccod_usuario=@ccod_usuario
        WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
        SELECT 'OK' AS ccod_unidadmedida, '' AS cdsc_unidadmedida;
    END TRY
    BEGIN CATCH
        SELECT CAST(ERROR_NUMBER() AS VARCHAR(20)) AS ccod_unidadmedida, ERROR_MESSAGE() AS cdsc_unidadmedida;
    END CATCH
END
GO
PRINT 'OK: webDatpos_editarUnidadMedida';
GO

-- 6. sp_eliminarUnidadMedida
IF OBJECT_ID('sp_eliminarUnidadMedida','P') IS NOT NULL DROP PROCEDURE sp_eliminarUnidadMedida;
GO
CREATE PROCEDURE sp_eliminarUnidadMedida @ccod_unidadmedida VARCHAR(10), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        DELETE FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
        SELECT 'OK' AS ccod_unidadmedida, '' AS cdsc_unidadmedida;
    END TRY
    BEGIN CATCH
        SELECT CAST(ERROR_NUMBER() AS VARCHAR(20)) AS ccod_unidadmedida, ERROR_MESSAGE() AS cdsc_unidadmedida;
    END CATCH
END
GO
PRINT 'OK: sp_eliminarUnidadMedida';
GO

PRINT '============================================';
PRINT 'FIX 23 completo — UnidadMedida corregido.';
PRINT '============================================';
GO
