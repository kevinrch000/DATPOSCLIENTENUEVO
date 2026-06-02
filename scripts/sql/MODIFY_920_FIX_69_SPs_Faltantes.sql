/* ========================================================================
   MODIFY_920 / FIX_69
   SPs faltantes que bloquean funcionalidad critica (Sprint 1 - Prioridad 1).

   Errores del servidor:
     S.1  sp_consultastockminimoprincipal — SQLSTATE 42000, code 2812
     S.2  sp_cargararticulosolobienes     — SQLSTATE 42000, code 2812
     S.4  sp_cargaroperacionesclientes    — SQLSTATE 42000, code 2812

   Causa: la API usa nombres con prefijo sp_ que nunca fueron creados.
   Ya existen equivalentes con prefijo webDatpos_ para S.2 pero no para
   S.1 ni S.4. Este script crea los tres SPs.

   Ejecutar en DatPos_EMP01
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 920 / FIX 69: SPs faltantes (S.1, S.2, S.4) ==';

/* ─── S.1  sp_consultastockminimoprincipal ─────────────────────────────
   Endpoint: POST /api/consultadocumento_api.php?method=ConsultaStockMinimoPrincipal
   Payload:  { Consultar: [{ ccod_alm, id_articulo }] }
   API params: @ccod_cia, @ccod_alm, @id_articulo
   API lee:  [0] cdsc_articulo, [1] nstock (cantidad actual),
             [2] nstock_min, [3] cdsc_alm

   Ya existe webDatpos_ConsultaStockMinimo con firma distinta
   (5 params incl. @ccod_lin y @nstock_min). Este SP es la version
   simplificada que la API realmente llama.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_consultastockminimoprincipal','P') IS NOT NULL
    DROP PROCEDURE sp_consultastockminimoprincipal;
GO
CREATE PROCEDURE sp_consultastockminimoprincipal
    @ccod_cia     VARCHAR(20),
    @ccod_alm     VARCHAR(20),
    @id_articulo  VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT
        A.cdsc_articulo                                       AS cdsc_articulo,  -- [0]
        ISNULL(CAST(S.ncantidad AS NVARCHAR(50)), '0')        AS nstock,         -- [1]
        ISNULL(CAST(A.nstock_min AS NVARCHAR(50)), '0')       AS nstock_min,     -- [2]
        ISNULL(AL.cdsc_alm, @ccod_alm)                       AS cdsc_alm        -- [3]
    FROM Articulos A
    LEFT JOIN Stock S
        ON S.ccod_cia = A.ccod_cia
       AND S.ccod_articulo = A.ccod_articulo
       AND S.ccod_alm = @ccod_alm
    LEFT JOIN Almacenes AL
        ON AL.ccod_cia = A.ccod_cia
       AND AL.ccod_alm = @ccod_alm
    WHERE A.ccod_cia = @ccod_cia
      AND A.cstatus = 'A'
      AND (@id_articulo = '' OR A.ccod_articulo = @id_articulo)
      AND (@ccod_alm = '' OR S.ccod_alm IS NOT NULL)
      AND ISNULL(S.ncantidad, 0) <= ISNULL(A.nstock_min, 0)
    ORDER BY A.cdsc_articulo;
END
GO

/* ─── S.2  sp_cargararticulosolobienes ─────────────────────────────────
   Endpoint: POST /api/consultadocumento_api.php?method=CargarArticuloSoloBienes
   API params: @ccod_cia
   API lee: [0] ccod_articulo, [1] cdsc_articulo

   Alias directo de webDatpos_cargarArticuloSoloBienes que ya existe.
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargararticulosolobienes','P') IS NOT NULL
    DROP PROCEDURE sp_cargararticulosolobienes;
GO
CREATE PROCEDURE sp_cargararticulosolobienes
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_articulo, cdsc_articulo
    FROM Articulos
    WHERE ccod_cia = @ccod_cia
      AND cstatus = 'A'
      AND ctip_articulo = 'B'
    ORDER BY cdsc_articulo;
END
GO

/* ─── S.4  sp_cargaroperacionesclientes ────────────────────────────────
   Endpoint: POST /api/home_api.php?method=CargarOperacionesClientes
   Payload:  { OperCliente: [{ fchDesde, fchHasta, ccod_tienda }] }
   API params: @ccod_cia, @fchDesde, @fchHasta, @ccod_tienda
   API lee:  [0] cdsc_coa (nombre cliente)
             [1] cdsc_usuario (cajero)
             [2] DocRef (tipo doc + serie + numero)
             [3] cnom_tarje (forma de pago)
             [4] dfch_crea (fecha cobranza)
             [5] nmonto (monto cobrado)

   Join: CbCobranza -> CbFactura (id_cbfact) -> Coa (ccod_coa)
         + Usuarios (ccod_usuario)
   ─────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('sp_cargaroperacionesclientes','P') IS NOT NULL
    DROP PROCEDURE sp_cargaroperacionesclientes;
GO
CREATE PROCEDURE sp_cargaroperacionesclientes
    @ccod_cia    VARCHAR(20),
    @fchDesde    VARCHAR(20),
    @fchHasta    VARCHAR(20),
    @ccod_tienda VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        ISNULL(CO.cdsc_coa, '')                                              AS cdsc_coa,     -- [0]
        ISNULL(U.cdsc_usuario, CB.ccod_usuario)                              AS cdsc_usuario,  -- [1]
        ISNULL(F.cdoc,'') + ' ' + ISNULL(F.cserie,'') + '-'
            + ISNULL(CAST(F.nnumero AS NVARCHAR(20)),'')                     AS DocRef,        -- [2]
        ISNULL(CB.cnom_tarje, '')                                            AS cnom_tarje,    -- [3]
        ISNULL(CONVERT(NVARCHAR(20), CB.dfch_crea, 103), '')                 AS dfch_crea,     -- [4]
        ISNULL(CAST(CB.ntotal AS NVARCHAR(50)), '0')                         AS nmonto         -- [5]
    FROM CbCobranza CB
    LEFT JOIN CbFactura F
        ON F.id_cbfact = CB.id_cbfact AND F.ccod_cia = CB.ccod_cia
    LEFT JOIN Coa CO
        ON CO.ccod_cia = F.ccod_cia AND CO.ccod_coa = F.ccod_coa
    LEFT JOIN Usuarios U
        ON U.ccod_empresa = CB.ccod_cia AND U.ccod_usuario = CB.ccod_usuario
    WHERE CB.ccod_cia = @ccod_cia
      AND (@ccod_tienda = '' OR CB.ccod_tiend = @ccod_tienda)
      AND (@fchDesde = '' OR CB.dfch_crea >= CAST(@fchDesde AS DATETIME))
      AND (@fchHasta = '' OR CB.dfch_crea <= CAST(@fchHasta + ' 23:59:59' AS DATETIME))
    ORDER BY CB.dfch_crea DESC;
END
GO

PRINT 'OK - FIX 69 completo: sp_consultastockminimoprincipal, sp_cargararticulosolobienes, sp_cargaroperacionesclientes.';
GO
