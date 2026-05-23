/* =====================================================================
   FIX 15 — Corrección de SPs de AperturaCaja
   
   Problema 1: appDatpos_abrirCaja debe retornar 'OK' o 'TurnoAperturado'
               en campo id_turno (el JS lo valida como string en línea 171)
   Problema 2: webDatpos_consultarCierreCaja devuelve NULL en dfchdoc_fin
               y el VB hace cast directo a String → InvalidCastException
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. appDatpos_abrirCaja — retornar 'OK' o 'TurnoAperturado'
   El JS (línea 171) valida: if(obj[0].id_turno == 'TurnoAperturado')
   El JS (línea 178) valida: else if(obj[0].id_turno == 'OK')
   El VB (línea 32) lee: objBEV.id_turno = fila.ItemArray(0)
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja;
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie      VARCHAR(20),
    @IdUsuario   VARCHAR(50),
    @CodCaj      VARCHAR(20),
    @Monto       DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50),
    @dfchdoc_ini DATETIME
AS BEGIN SET NOCOUNT ON;
    -- Verificar si ya tiene turno abierto
    IF EXISTS (SELECT 1 FROM Turno WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND cstatus='A')
    BEGIN
        SELECT 'TurnoAperturado' AS id_turno;
        RETURN;
    END
    -- Crear nuevo turno
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');
    SELECT 'OK' AS id_turno;
END
GO

/* ── 2. webDatpos_consultarCierreCaja — ISNULL en columnas nullable
   El VB hace cast directo a String (ItemArray), NULL → InvalidCastException
   Columnas que pueden ser NULL: nmonto_fin, dfchdoc_ini, dfchdoc_fin
   El VB lee 9 columnas: [0]id_turno [1]cdsc_tienda [2]cdsc_usuario 
   [3]cdsc_caja [4]nmonto_ini [5]nmonto_fin [6]dfecha_ini [7]dfecha_fin [8]cstatus
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.id_turno,                                                          -- [0]
        ISNULL(T.ccod_tienda, '')                           AS cdsc_tienda,  -- [1]
        ISNULL(T.ccod_usuario, '')                          AS cdsc_usuario, -- [2]
        ISNULL(T.ccod_caja, '')                             AS cdsc_caja,    -- [3]
        ISNULL(CAST(T.nmonto_ini  AS NVARCHAR(50)), '0')    AS nmonto_ini,   -- [4]
        ISNULL(CAST(T.nmonto_fin  AS NVARCHAR(50)), '0')    AS nmonto_fin,   -- [5]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_ini, 120), '') AS dfecha_ini, -- [6]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_fin, 120), '') AS dfecha_fin, -- [7]
        ISNULL(T.cstatus, '')                               AS cstatus       -- [8]
    FROM Turno T
    WHERE T.ccod_cia=@ccod_cia
    ORDER BY T.id_turno DESC;
END
GO

/* ── 3. webDatpos_consultarIdCierreCaja — misma corrección de NULLs
   El VB lee 13 columnas [0]..[12]
────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_turno,                                                          -- [0]
        ISNULL(ccod_tienda, '')                           AS cdsc_tienda,  -- [1]
        ISNULL(ccod_usuario, '')                          AS cdsc_usuario, -- [2]
        ISNULL(ccod_caja, '')                             AS cdsc_caja,    -- [3]
        ISNULL(CAST(nmonto_ini AS NVARCHAR(50)),'0')      AS nmonto_ini,   -- [4]
        ISNULL(CAST(nmonto_fin AS NVARCHAR(50)),'0')      AS nmonto_fin,   -- [5]
        ISNULL(CONVERT(NVARCHAR(20),dfchdoc_ini,120),'')  AS dfchdoc_ini,  -- [6]
        ISNULL(CONVERT(NVARCHAR(20),dfchdoc_fin,120),'')  AS dfchdoc_fin,  -- [7]
        ISNULL(ccod_tienda, '')                           AS ccod_tienda,  -- [8]
        ISNULL(ccod_usuario, '')                          AS ccod_usuario, -- [9]
        ISNULL(ccod_caja, '')                             AS ccod_caja,    -- [10]
        ISNULL(CAST(ntot_entreg AS NVARCHAR(50)),'0')     AS ntot_entreg,  -- [11]
        ISNULL(CAST(ndiferencia AS NVARCHAR(50)),'0')     AS ndiferencia   -- [12]
    FROM Turno
    WHERE ccod_cia=@ccod_cia AND id_turno=@id_turno;
END
GO

/* ── 4. sp_consultarusuarioturno — para que Facturación valide turno abierto ── */
IF OBJECT_ID('sp_consultarusuarioturno','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarioturno;
GO
CREATE PROCEDURE sp_consultarusuarioturno @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT CAST(id_turno AS NVARCHAR(20)) AS id_turno
    FROM Turno
    WHERE ccod_cia=@ccod_cia AND ccod_usuario=@ccod_usuario AND cstatus='A';
END
GO

PRINT 'OK - FIX 15 completo.';
GO
