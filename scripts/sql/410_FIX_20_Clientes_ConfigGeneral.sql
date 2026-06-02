/* =====================================================================
   FIX 20 — Errores en Clientes (Asociados) y Configuración General
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ─────────────────────────────────────────────────────────────────────
   ERROR 1: Tablas → Ventas → Asociados
   Problema: sp_consultaclientes retorna 9 columnas pero el VB
   espera 17 columnas con ccod_cia en [1] y muchos campos nullable.
   VB ConsultarClientes() espera:
     [0]  id_coa
     [1]  ccod_cia
     [2]  ccod_coa
     [3]  cdoc_coa
     [4]  cdsc_coa
     [5]  ctelf
     [6]  cmail
     [7]  destipo_coa  (ctipo_coa → alias destipo_coa)
     [8]  cdirc_coa    (nullable)
     [9]  cdistrito    (nullable)
     [10] cprovincia   (nullable)
     [11] cdepartamento(nullable)
     [12] cpais        (nullable)
     [13] estado       (cstatus)
     [14] cproveedor
     [15] ctip_doc     (cdoc_coa como ctip_doc - no existe, '01' default)
     [16] cruc_coa
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultaclientes','P') IS NOT NULL DROP PROCEDURE sp_consultaclientes;
GO
CREATE PROCEDURE sp_consultaclientes @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        id_coa,                                    -- [0]
        ccod_cia,                                  -- [1]
        ccod_coa,                                  -- [2]
        ISNULL(cdoc_coa,'')    AS cdoc_coa,        -- [3]
        ISNULL(cdsc_coa,'')    AS cdsc_coa,        -- [4]
        ISNULL(ctelf,'')       AS ctelf,            -- [5]
        ISNULL(cmail,'')       AS cmail,            -- [6]
        ISNULL(ctipo_coa,'')   AS destipo_coa,     -- [7]
        cdirc_coa,                                  -- [8]  nullable OK
        cdistrito,                                  -- [9]  nullable OK
        cprovincia,                                 -- [10] nullable OK
        cdepartamento,                              -- [11] nullable OK
        cpais,                                      -- [12] nullable OK
        ISNULL(cstatus,'A')    AS estado,           -- [13]
        ISNULL(cproveedor,'0') AS cproveedor,       -- [14]
        ISNULL(cdoc_coa,'')    AS ctip_doc,         -- [15]
        ISNULL(cruc_coa,'')    AS cruc_coa          -- [16]
    FROM Coa
    WHERE ccod_cia=@ccod_cia
    ORDER BY cdsc_coa;
END
GO

PRINT '✓ ERROR 1 corregido: sp_consultaclientes (17 columnas)';
GO


/* ─────────────────────────────────────────────────────────────────────
   ERROR 2a: ConfigGeneral → CodigoOperacionIngreso / Salida
   Problema: SPs retornan 2 columnas pero VB espera 3:
     [0] ccod_toper, [1] cdsc_toper, [2] ctipo_flag_Oper
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_codigoOperacionIngreso','P') IS NOT NULL DROP PROCEDURE webDatpos_codigoOperacionIngreso;
GO
CREATE PROCEDURE webDatpos_codigoOperacionIngreso @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.ccod_tipoper  AS ccod_toper,         -- [0]
        T.cdsc_tipoper  AS cdsc_toper,         -- [1]
        T.ctipo_flag    AS ctipo_flag_Oper     -- [2]
    FROM TipoOperacion T
    INNER JOIN ConfigGeneral C ON C.coper_ingreso=T.ccod_tipoper AND C.ccod_cia=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia;
END
GO

IF OBJECT_ID('webDatpos_codigoOperacionSalida','P') IS NOT NULL DROP PROCEDURE webDatpos_codigoOperacionSalida;
GO
CREATE PROCEDURE webDatpos_codigoOperacionSalida @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        T.ccod_tipoper  AS ccod_toper,         -- [0]
        T.cdsc_tipoper  AS cdsc_toper,         -- [1]
        T.ctipo_flag    AS ctipo_flag_Oper     -- [2]
    FROM TipoOperacion T
    INNER JOIN ConfigGeneral C ON C.coper_salida=T.ccod_tipoper AND C.ccod_cia=T.ccod_cia
    WHERE T.ccod_cia=@ccod_cia;
END
GO

PRINT '✓ ERROR 2a corregido: CodigoOperacionIngreso/Salida (3 columnas)';
GO


/* ─────────────────────────────────────────────────────────────────────
   ERROR 2b: ConfigGeneral → DatosConfigGenreal
   Problema: SELECT * no retorna las columnas en el orden esperado.
   VB espera 10 columnas:
     [0] ccod_clibol
     [1] cnom_clibol        (nombre del cliente boleta → JOIN Coa)
     [2] ccod_OperIngreso   (coper_ingreso)
     [3] ccod_OperSalida    (coper_salida)
     [4] cnom_OperIngreso   (JOIN TipoOperacion ingreso)
     [5] cnom_OperSalida    (JOIN TipoOperacion salida)
     [6] nigv
     [7] nisc
     [8] nmonto_maxboleta
     [9] ilogo              (VARBINARY)
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_datosConfigGenreal','P') IS NOT NULL DROP PROCEDURE webDatpos_datosConfigGenreal;
GO
CREATE PROCEDURE webDatpos_datosConfigGenreal @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(C.ccod_clibol,'')       AS ccod_clibol,          -- [0]
        ISNULL(CL.cdsc_coa,'')         AS cnom_clibol,          -- [1]
        ISNULL(C.coper_ingreso,'')     AS ccod_OperIngreso,     -- [2]
        ISNULL(C.coper_salida,'')      AS ccod_OperSalida,      -- [3]
        ISNULL(TI.cdsc_tipoper,'')     AS cnom_OperIngreso,     -- [4]
        ISNULL(TS.cdsc_tipoper,'')     AS cnom_OperSalida,      -- [5]
        ISNULL(C.nigv,18)              AS nigv,                  -- [6]
        ISNULL(C.nisc,0)               AS nisc,                  -- [7]
        ISNULL(C.nmonto_maxboleta,700) AS nmonto_maxboleta,      -- [8]
        C.ilogo                                                  -- [9] VARBINARY
    FROM ConfigGeneral C
    LEFT JOIN Coa CL ON CL.ccod_coa=C.ccod_clibol AND CL.ccod_cia=C.ccod_cia
    LEFT JOIN TipoOperacion TI ON TI.ccod_tipoper=C.coper_ingreso AND TI.ccod_cia=C.ccod_cia
    LEFT JOIN TipoOperacion TS ON TS.ccod_tipoper=C.coper_salida AND TS.ccod_cia=C.ccod_cia
    WHERE C.ccod_cia=@ccod_cia;
END
GO

PRINT '✓ ERROR 2b corregido: webDatpos_datosConfigGenreal (10 columnas con JOINs)';
GO


/* ─────────────────────────────────────────────────────────────────────
   Seed: Asegurar que ConfigGeneral tenga al menos 1 fila
───────────────────────────────────────────────────────────────────── */
IF NOT EXISTS (SELECT 1 FROM ConfigGeneral WHERE ccod_cia='EMP01')
    INSERT INTO ConfigGeneral (ccod_cia, ccod_clibol, coper_ingreso, coper_salida,
        ctipo_flag_ingreso, ctipo_flag_salida, nigv, nisc, nmonto_maxboleta, ccod_usuario)
    VALUES ('EMP01','0001','ING','SAL','I','S',18.00,0.00,700.00,'ADMIN');
GO

PRINT '═══════════════════════════════════════';
PRINT '  FIX 20 COMPLETO — Clientes + ConfigGeneral';
PRINT '═══════════════════════════════════════';
GO
