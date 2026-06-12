/* =====================================================================
   FIX 19 — Corrección de 5 errores en módulos Home, UnidadMedida,
            Ingresos, Salidas, GuíaRemisión
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ─────────────────────────────────────────────────────────────────────
   ERROR 1: Home → Mi Perfil → DatosGenerales
   Problema: columnas retornadas en orden incorrecto.
   VB espera: [0] cdsc_tienda, [1] cdsc_alm, [2] cdsc_caja,
              [3] nlista_pre_normal, [4] nlista_pre_preferencial,
              [5] cdsc_listpreNorm, [6] cdsc_listprePref,
              [7] cdescripcion
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_datosGenerales','P') IS NOT NULL DROP PROCEDURE webDatpos_datosGenerales;
GO
CREATE PROCEDURE webDatpos_datosGenerales @CCOD_CIA VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.cnombr                                           AS cdsc_tienda,           -- [0]
        AL.cdsc_alm,                                                                 -- [1]
        CA.cdsc_caja,                                                                -- [2]
        CAST(ISNULL(TI.nlista_pre_normal,1) AS VARCHAR)    AS nlista_pre_normal,      -- [3]
        CAST(ISNULL(TI.nlista_pre_preferencial,2) AS VARCHAR) AS nlista_pre_preferencial, -- [4]
        ISNULL(LP1.cdsc_cblistpre,'')                      AS cdsc_listpreNorm,       -- [5]
        ISNULL(LP2.cdsc_cblistpre,'')                      AS cdsc_listprePref,       -- [6]
        U.cdsc_usuario                                     AS cdescripcion            -- [7]
    FROM Usuarios U
    LEFT JOIN Tiendas TI ON TI.ccod_tiend=U.ccod_tiend AND TI.ccod_cia=U.ccod_empresa
    LEFT JOIN Almacenes AL ON AL.ccod_alm=U.ccod_almacen AND AL.ccod_cia=U.ccod_empresa
    LEFT JOIN Cajas CA ON CA.ccod_caja=U.ccod_caja AND CA.ccod_cia=U.ccod_empresa
    LEFT JOIN Tiendas T ON T.ccod_tiend=U.ccod_tiend AND T.ccod_cia=U.ccod_empresa
    LEFT JOIN CbListaPrecio LP1 ON LP1.ccod_cblistpre=CAST(TI.nlista_pre_normal AS VARCHAR) AND LP1.ccod_cia=U.ccod_empresa
    LEFT JOIN CbListaPrecio LP2 ON LP2.ccod_cblistpre=CAST(TI.nlista_pre_preferencial AS VARCHAR) AND LP2.ccod_cia=U.ccod_empresa
    WHERE U.ccod_empresa=@CCOD_CIA AND U.ccod_usuario=@ccod_usuario;
END
GO

PRINT '✓ ERROR 1 corregido: webDatpos_datosGenerales (orden de columnas)';
GO


/* ─────────────────────────────────────────────────────────────────────
   ERROR 2: Almacén → Tablas → Unidad de Medida
   Problema: SP webDatpos_consultarUnidadMedida no existe.
   VB espera 6 columnas:
     [0] id_unidadmedida
     [1] ccod_unidadmedida
     [2] csim_unidadmedida   ← no existe en tabla, usamos ccod_unimed
     [3] cdsc_unidadmedida
     [4] cstatus
     [5] ccod_tributario      ← no existe en tabla, retornamos NULL
   También falta webDatpos_consultarCodigoUnidadMedida, insertar, editar, eliminar.
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_consultarUnidadMedida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_unimed          AS id_unidadmedida,      -- [0]
        ccod_unimed        AS ccod_unidadmedida,     -- [1]
        ccod_unimed        AS csim_unidadmedida,     -- [2] (símbolo = código)
        cdsc_unimed        AS cdsc_unidadmedida,     -- [3]
        cstatus,                                     -- [4]
        NULL               AS ccod_tributario        -- [5]
    FROM UnidadMedida
    WHERE ccod_cia=@ccod_cia
    ORDER BY ccod_unimed;
END
GO

IF OBJECT_ID('webDatpos_consultarCodigoUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarCodigoUnidadMedida;
GO
CREATE PROCEDURE webDatpos_consultarCodigoUnidadMedida @ccod_cia VARCHAR(20), @ccod_unidadmedida VARCHAR(10)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_unimed          AS id_unidadmedida,
        ccod_unimed        AS ccod_unidadmedida,
        ccod_unimed        AS csim_unidadmedida,
        cdsc_unimed        AS cdsc_unidadmedida,
        cstatus,
        NULL               AS ccod_tributario
    FROM UnidadMedida
    WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
END
GO

IF OBJECT_ID('webDatpos_insertarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_insertarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_insertarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50),
    @ccod_unidadmedida VARCHAR(10), @csim_unidadmedida VARCHAR(10),
    @cdsc_unidadmedida VARCHAR(50), @cstatus VARCHAR(1), @ccod_tributario VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida)
        INSERT INTO UnidadMedida(ccod_cia,ccod_unimed,cdsc_unimed,cstatus,ccod_usuario)
        VALUES(@ccod_cia,@ccod_unidadmedida,@cdsc_unidadmedida,@cstatus,@ccod_usuario);
    SELECT @ccod_unidadmedida AS ccod_unidadmedida, @cdsc_unidadmedida AS cdsc_unidadmedida;
END
GO

IF OBJECT_ID('webDatpos_editarUnidadMedida','P') IS NOT NULL DROP PROCEDURE webDatpos_editarUnidadMedida;
GO
CREATE PROCEDURE webDatpos_editarUnidadMedida
    @ccod_cia VARCHAR(20), @ccod_usuario VARCHAR(50),
    @ccod_unidadmedida VARCHAR(10), @csim_unidadmedida VARCHAR(10),
    @cdsc_unidadmedida VARCHAR(50), @cstatus VARCHAR(1), @ccod_tributario VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    UPDATE UnidadMedida
    SET cdsc_unimed=@cdsc_unidadmedida, cstatus=@cstatus, ccod_usuario=@ccod_usuario
    WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
    SELECT @ccod_unidadmedida AS ccod_unidadmedida, @cdsc_unidadmedida AS cdsc_unidadmedida;
END
GO

IF OBJECT_ID('sp_eliminarUnidadMedida','P') IS NOT NULL DROP PROCEDURE sp_eliminarUnidadMedida;
GO
CREATE PROCEDURE sp_eliminarUnidadMedida @ccod_unidadmedida VARCHAR(10), @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    DELETE FROM UnidadMedida WHERE ccod_cia=@ccod_cia AND ccod_unimed=@ccod_unidadmedida;
    SELECT @ccod_unidadmedida AS ccod_unidadmedida, '' AS cdsc_unidadmedida;
END
GO

PRINT '✓ ERROR 2 corregido: SPs UnidadMedida completos';
GO


/* ─────────────────────────────────────────────────────────────────────
   ERROR 3 & 4: Ingresos Directos + Salidas Directas
   Problema: DataBind busca columnas "cdsc_toper" y "ccod_toper"
             pero los SPs retornan "ccod_tipoper" y "cdsc_tipoper".
   Solución: crear alias con los nombres que espera el DataBind.
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultartiposoperacionactivosingresos','P') IS NOT NULL DROP PROCEDURE sp_consultartiposoperacionactivosingresos;
GO
CREATE PROCEDURE sp_consultartiposoperacionactivosingresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tipoper AS ccod_toper, cdsc_tipoper AS cdsc_toper
    FROM TipoOperacion
    WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='I';
END
GO

IF OBJECT_ID('sp_consultarTiposOperacionSalisa','P') IS NOT NULL DROP PROCEDURE sp_consultarTiposOperacionSalisa;
GO
CREATE PROCEDURE sp_consultarTiposOperacionSalisa @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tipoper AS ccod_toper, cdsc_tipoper AS cdsc_toper
    FROM TipoOperacion
    WHERE ccod_cia=@ccod_cia AND cstatus='A' AND ctipo_flag='S';
END
GO

PRINT '✓ ERROR 3 & 4 corregidos: SPs Ingresos/Salidas (alias cdsc_toper)';
GO


/* ─────────────────────────────────────────────────────────────────────
   ERROR 5: Almacén → Operaciones → Guía de Remisión → ConsultarAlmacenes
   Problema: SP webDatpos_ConsultarAlamcenes no existe.
   VB espera 8 columnas:
     [0] id_ctalmac     ← no tiene, usamos id_almac
     [1] ccod_alm
     [2] cdsc_alm
     [3] cdirc_almac
     [4] cubigeo
     [5] cstatus
     [6] cserieDest     ← viene de NumeradorAlmacen (serie ingreso)
     [7] cserieOrig     ← viene de NumeradorAlmacen (serie salida)
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_ConsultarAlamcenes','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarAlamcenes;
GO
CREATE PROCEDURE webDatpos_ConsultarAlamcenes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        A.id_almac                                        AS id_ctalmac,     -- [0]
        A.ccod_alm,                                                          -- [1]
        A.cdsc_alm,                                                          -- [2]
        ISNULL(A.cdirc_almac,'')                          AS cdirc_almac,    -- [3]
        ISNULL(A.cubigeo,'')                              AS cubigeo,        -- [4]
        A.cstatus,                                                           -- [5]
        ISNULL((SELECT TOP 1 cserie FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=A.ccod_alm AND ctip_doc='I'),'') AS cserieDest,  -- [6]
        ISNULL((SELECT TOP 1 cserie FROM NumeradorAlmacen WHERE ccod_cia=@ccod_cia AND ccod_alm=A.ccod_alm AND ctip_doc='S'),'') AS cserieOrig   -- [7]
    FROM Almacenes A
    WHERE A.ccod_cia=@ccod_cia AND A.cstatus='A';
END
GO

PRINT '✓ ERROR 5 corregido: webDatpos_ConsultarAlamcenes (8 columnas)';
GO


/* ─────────────────────────────────────────────────────────────────────
   SPs auxiliares que otros módulos de GuíaRemisión necesitan
───────────────────────────────────────────────────────────────────── */

/* Consultar GuíaRemisión (listado) */
IF OBJECT_ID('webDatpos_consultarGuiaRemision','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarGuiaRemision;
GO
CREATE PROCEDURE webDatpos_consultarGuiaRemision @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    -- El VB espera: id_cbinve[0], ctipo[1], cod_tip_cpe[2], ccod_alm[3],
    -- cdomicilio_partida[4], ccod_alm_ing[5], cdomicilio_llegada[6],
    -- dfecha[7], cdoc_ref[8]
    SELECT
        I.id_cbinve,                                             -- [0]
        ISNULL(I.ctipo,'')           AS ctipo,                   -- [1]
        ''                           AS cod_tip_cpe,             -- [2]
        ISNULL(I.ccod_alm,'')       AS ccod_alm,                -- [3]
        ''                           AS cdomicilio_partida,      -- [4]
        ''                           AS ccod_alm_ing,            -- [5]
        ''                           AS cdomicilio_llegada,      -- [6]
        I.dfecha,                                                -- [7]
        ISNULL(I.vserie,'') + '-' + CAST(ISNULL(I.nnumero,0) AS VARCHAR) AS cdoc_ref -- [8]
    FROM CbInventario I
    WHERE I.ccod_cia=@ccod_cia
    ORDER BY I.dfecha DESC;
END
GO

/* ConsultarOperaciones para GuíaRemisión */
IF OBJECT_ID('webDatpos_ConsultarOperaciones','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarOperaciones;
GO
CREATE PROCEDURE webDatpos_ConsultarOperaciones @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT id_tipoper AS id_ctoper, ccod_tipoper AS ccod_toper, cdsc_tipoper AS cdsc_toper,
           ctipo_flag, '' AS ctipo_transferencia, cstatus
    FROM TipoOperacion
    WHERE ccod_cia=@ccod_cia AND cstatus='A';
END
GO


PRINT '═══════════════════════════════════════';
PRINT '  FIX 19 COMPLETO — 5 errores resueltos';
PRINT '═══════════════════════════════════════';
GO
