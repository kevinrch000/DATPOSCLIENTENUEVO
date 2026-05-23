/* =====================================================================
   FIX 41 — AperturaCaja: SPs DEFINITIVOS (Turno correcto)
   
   Problema raíz detectado:
     - Script 260 sobrescribió los SPs correctos de 080 con versiones
       que referencian la tabla "AperturaCaja" (que no existe; la tabla
       real se llama "Turno").
     - Script 270 intentó corregirlos pero webDatpos_cargarIdUsuario
       seguía retornando id_usuario (entero) como primera columna,
       haciendo que ccod_usuario lleve "2" en vez del código de usuario.
     - Scripts 290/300 mejoraron más pero los SPs de consulta de lista
       devolvían códigos crudos (T001, admin) en vez de descripciones.
   
   Este script crea las versiones DEFINITIVAS de todos los SPs del
   módulo AperturaCaja / Turno.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. webDatpos_consultaTienda ─────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultaTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaTienda;
GO
CREATE PROCEDURE webDatpos_consultaTienda @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend, cnombr
    FROM Tiendas
    WHERE ccod_cia=@ccod_cia AND cstatus='A'
    ORDER BY cnombr;
END
GO

/* ── 2. webDatpos_cargarIdUsuario ────────────────────────────────── */
/* Retorna [0]=ccod_usuario [1]=cdsc_usuario (filtrado por tienda)   */
/* PHP mapea: ccod_usuario=f[0], cdsc_usuario=f[1]                   */
/* JS: option.text=ccod_usuario, option.val=cdsc_usuario             */
IF OBJECT_ID('webDatpos_cargarIdUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarIdUsuario;
GO
CREATE PROCEDURE webDatpos_cargarIdUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_usuario, ISNULL(cdsc_usuario, ccod_usuario) AS cdsc_usuario
    FROM Usuarios
    WHERE ccod_empresa=@ccod_cia
      AND ccod_tiend=@ccod_tienda
      AND id_estado=1
    ORDER BY cdsc_usuario;
END
GO

/* ── 3. webDatpos_cargarCajaDeUsuario ────────────────────────────── */
/* Retorna [0]=ccod_caja [1]=cdsc_caja                                */
IF OBJECT_ID('webDatpos_cargarCajaDeUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarCajaDeUsuario;
GO
CREATE PROCEDURE webDatpos_cargarCajaDeUsuario @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_caja,
           ISNULL(C.cdsc_caja, U.ccod_caja) AS cdsc_caja
    FROM Usuarios U
    LEFT JOIN Cajas C ON C.ccod_cia=U.ccod_empresa AND C.ccod_caja=U.ccod_caja
    WHERE U.ccod_empresa=@ccod_cia
      AND U.ccod_usuario=@ccod_usuario
      AND U.id_estado=1;
END
GO

/* ── 4. webDatpos_consultarCierreCaja ────────────────────────────── */
/* [0]=id_turno [1]=cdsc_tienda [2]=cdsc_usuario [3]=cdsc_caja       */
/* [4]=nmonto_ini [5]=nmonto_fin [6]=dfecha_ini [7]=dfecha_fin        */
/* [8]=cstatus                                                         */
IF OBJECT_ID('webDatpos_consultarCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarCierreCaja @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.id_turno,                                                                  -- [0]
        ISNULL(Ti.cnombr,   T.ccod_tienda)  AS cdsc_tienda,                        -- [1]
        ISNULL(U.cdsc_usuario, T.ccod_usuario) AS cdsc_usuario,                    -- [2]
        ISNULL(C.cdsc_caja, T.ccod_caja)    AS cdsc_caja,                          -- [3]
        ISNULL(CAST(T.nmonto_ini AS NVARCHAR(50)), '0')             AS nmonto_ini,  -- [4]
        ISNULL(CAST(T.nmonto_fin AS NVARCHAR(50)), '0')             AS nmonto_fin,  -- [5]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_ini, 103), '')       AS dfecha_ini,  -- [6]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_fin, 103), '')       AS dfecha_fin,  -- [7]
        ISNULL(T.cstatus, '')                                       AS cstatus      -- [8]
    FROM Turno T
    LEFT JOIN Tiendas  Ti ON Ti.ccod_cia=T.ccod_cia AND Ti.ccod_tiend=T.ccod_tienda
    LEFT JOIN Usuarios U  ON U.ccod_empresa=T.ccod_cia AND U.ccod_usuario=T.ccod_usuario
    LEFT JOIN Cajas    C  ON C.ccod_cia=T.ccod_cia AND C.ccod_caja=T.ccod_caja
    WHERE T.ccod_cia=@ccod_cia
    ORDER BY T.id_turno DESC;
END
GO

/* ── 5. webDatpos_consultarIdCierreCaja ──────────────────────────── */
/* 13 columnas que necesita aperturacaja_api.php (ConsultarIdCierreCaja) */
/* [0]=id_turno  [1]=cdsc_tienda  [2]=cdsc_usuario  [3]=cdsc_caja    */
/* [4]=nmonto_ini [5]=nmonto_fin  [6]=dfchdoc_ini  [7]=dfchdoc_fin   */
/* [8]=ccod_tienda [9]=ccod_usuario [10]=ccod_caja                   */
/* [11]=ntot_entreg [12]=ndiferencia                                   */
IF OBJECT_ID('webDatpos_consultarIdCierreCaja','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIdCierreCaja;
GO
CREATE PROCEDURE webDatpos_consultarIdCierreCaja @ccod_cia VARCHAR(20), @id_turno INT
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.id_turno,                                                                  -- [0]
        ISNULL(Ti.cnombr,   T.ccod_tienda)  AS cdsc_tienda,                        -- [1]
        ISNULL(U.cdsc_usuario, T.ccod_usuario) AS cdsc_usuario,                    -- [2]
        ISNULL(C.cdsc_caja, T.ccod_caja)    AS cdsc_caja,                          -- [3]
        ISNULL(CAST(T.nmonto_ini AS NVARCHAR(50)), '0')             AS nmonto_ini,  -- [4]
        ISNULL(CAST(T.nmonto_fin AS NVARCHAR(50)), '0')             AS nmonto_fin,  -- [5]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_ini, 103), '')       AS dfchdoc_ini, -- [6]
        ISNULL(CONVERT(NVARCHAR(20), T.dfchdoc_fin, 103), '')       AS dfchdoc_fin, -- [7]
        ISNULL(T.ccod_tienda, '')                                   AS ccod_tienda, -- [8]
        ISNULL(T.ccod_usuario, '')                                  AS ccod_usuario,-- [9]
        ISNULL(T.ccod_caja, '')                                     AS ccod_caja,   -- [10]
        ISNULL(CAST(T.ntot_entreg  AS NVARCHAR(50)), '0')           AS ntot_entreg, -- [11]
        ISNULL(CAST(T.ndiferencia  AS NVARCHAR(50)), '0')           AS ndiferencia  -- [12]
    FROM Turno T
    LEFT JOIN Tiendas  Ti ON Ti.ccod_cia=T.ccod_cia AND Ti.ccod_tiend=T.ccod_tienda
    LEFT JOIN Usuarios U  ON U.ccod_empresa=T.ccod_cia AND U.ccod_usuario=T.ccod_usuario
    LEFT JOIN Cajas    C  ON C.ccod_cia=T.ccod_cia AND C.ccod_caja=T.ccod_caja
    WHERE T.ccod_cia=@ccod_cia AND T.id_turno=@id_turno;
END
GO

/* ── 6. appDatpos_abrirCaja ──────────────────────────────────────── */
/* Retorna 'TurnoAperturado' si ya hay turno abierto, 'OK' si creó   */
/* El JS valida result[0].id_turno == 'TurnoAperturado' o 'OK'        */
IF OBJECT_ID('appDatpos_abrirCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_abrirCaja;
GO
CREATE PROCEDURE appDatpos_abrirCaja
    @CodTie      VARCHAR(20),
    @IdUsuario   VARCHAR(50),
    @CodCaj      VARCHAR(20),
    @Monto       DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50),
    @dfchdoc_ini DATETIME = NULL
AS BEGIN SET NOCOUNT ON;
    -- Usar fecha actual si no se proporciona
    IF @dfchdoc_ini IS NULL SET @dfchdoc_ini = GETDATE();

    -- Si el usuario ya tiene turno abierto, notificar sin crear nuevo
    IF EXISTS (SELECT 1 FROM Turno
               WHERE ccod_cia=@CodCia AND ccod_usuario=@IdUsuario AND cstatus='A')
    BEGIN
        SELECT 'TurnoAperturado' AS id_turno;
        RETURN;
    END

    -- Crear nuevo turno
    INSERT INTO Turno(ccod_cia, ccod_tienda, ccod_usuario, ccod_caja,
                      nmonto_ini, dfchdoc_ini, cstatus)
    VALUES(@CodCia, @CodTie, @IdUsuario, @CodCaj, @Monto, @dfchdoc_ini, 'A');

    SELECT 'OK' AS id_turno;
END
GO

/* ── 7. appDatpos_cierreCaja ──────────────────────────────────────── */
IF OBJECT_ID('appDatpos_cierreCaja','P') IS NOT NULL DROP PROCEDURE appDatpos_cierreCaja;
GO
CREATE PROCEDURE appDatpos_cierreCaja
    @id_turno    INT,
    @ntot_entreg DECIMAL(18,4),
    @nmonto_fin  DECIMAL(18,4),
    @ndiferencia DECIMAL(18,4),
    @CodCia      VARCHAR(20),
    @CodUsu      VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Turno
    SET cstatus='C',
        nmonto_fin  = @nmonto_fin,
        ntot_entreg = @ntot_entreg,
        ndiferencia = @ndiferencia,
        dfchdoc_fin = GETDATE()
    WHERE id_turno=@id_turno AND ccod_cia=@CodCia;

    SELECT 'OK' AS id_turno;
END
GO

/* ── 8. sp_consultarusuarioturno ─────────────────────────────────── */
/* Verifica si el usuario tiene turno abierto; lo usa Facturación     */
IF OBJECT_ID('sp_consultarusuarioturno','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarioturno;
GO
CREATE PROCEDURE sp_consultarusuarioturno @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT CAST(id_turno AS NVARCHAR(20)) AS id_turno
    FROM Turno
    WHERE ccod_cia=@ccod_cia AND ccod_usuario=@ccod_usuario AND cstatus='A';
END
GO

/* ── 9. webDatpos_cargarTurnoUsuario ─────────────────────────────── */
IF OBJECT_ID('webDatpos_cargarTurnoUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarTurnoUsuario;
GO
CREATE PROCEDURE webDatpos_cargarTurnoUsuario @ccod_cia VARCHAR(20), @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT T.id_turno,
           T.ccod_tienda,
           T.ccod_usuario,
           ISNULL(U.cdsc_usuario, T.ccod_usuario) AS cdsc_usuario,
           T.ccod_caja,
           T.nmonto_ini,
           T.dfchdoc_ini,
           T.cstatus
    FROM Turno T
    LEFT JOIN Usuarios U ON U.ccod_usuario=T.ccod_usuario AND U.ccod_empresa=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia
      AND T.ccod_tienda=@ccod_tienda
      AND T.cstatus='A';
END
GO

/* ── 10. Asegurar datos de Usuarios en EMP01 ─────────────────────── */
/* Si el usuario no tiene tienda/caja/almacen asignado, asignar los   */
/* valores por defecto (T001 / CAJ01 / ALM001)                        */
UPDATE Usuarios
SET ccod_tiend   = ISNULL(ccod_tiend, 'T001'),
    ccod_almacen = ISNULL(ccod_almacen, 'ALM001'),
    ccod_caja    = ISNULL(ccod_caja, 'CAJ01')
WHERE ccod_empresa = 'EMP01'
  AND (ccod_tiend IS NULL OR ccod_almacen IS NULL OR ccod_caja IS NULL);
GO

/* ── VERIFICACIÓN ─────────────────────────────────────────────────── */
SELECT name AS procedimiento, create_date, modify_date
FROM sys.procedures
WHERE name IN (
    'webDatpos_consultaTienda',
    'webDatpos_cargarIdUsuario',
    'webDatpos_cargarCajaDeUsuario',
    'webDatpos_consultarCierreCaja',
    'webDatpos_consultarIdCierreCaja',
    'appDatpos_abrirCaja',
    'appDatpos_cierreCaja',
    'sp_consultarusuarioturno',
    'webDatpos_cargarTurnoUsuario'
)
ORDER BY name;
GO

/* Verificar que los usuarios de EMP01 tienen caja/tienda asignada */
SELECT ccod_usuario, cdsc_usuario,
       ISNULL(ccod_tiend,'NULL')   AS ccod_tiend,
       ISNULL(ccod_caja,'NULL')    AS ccod_caja,
       ISNULL(ccod_almacen,'NULL') AS ccod_almacen
FROM Usuarios
WHERE ccod_empresa='EMP01' AND id_estado=1;
GO

PRINT 'OK - FIX 41: SPs AperturaCaja/Turno definitivos creados.';
GO
