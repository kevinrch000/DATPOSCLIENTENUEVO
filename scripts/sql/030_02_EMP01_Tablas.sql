/* ========================================================================
   PARTE 2: DatPos_EMP01 — LOGINS + TABLAS BASE
======================================================================== */
USE master;
GO

/* Logins SQL requeridos por las conexiones directas del cliente */
IF NOT EXISTS (SELECT * FROM sys.server_principals WHERE name = N'U76GY')
    CREATE LOGIN [U76GY] WITH PASSWORD = N'ADM', CHECK_POLICY = OFF;
GO
IF NOT EXISTS (SELECT * FROM sys.server_principals WHERE name = N'ADM')
    CREATE LOGIN [ADM] WITH PASSWORD = N'ADM', CHECK_POLICY = OFF;
GO

USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT * FROM sys.database_principals WHERE name = N'U76GY')
    CREATE USER [U76GY] FOR LOGIN [U76GY];
IF NOT EXISTS (SELECT * FROM sys.database_principals WHERE name = N'ADM')
    CREATE USER [ADM] FOR LOGIN [ADM];
ALTER ROLE [db_owner] ADD MEMBER [U76GY];
ALTER ROLE [db_owner] ADD MEMBER [ADM];
GO

/* -----------------------------------------------------------------------
   TABLAS (orden por dependencias FK)
----------------------------------------------------------------------- */

/* 1. ROLES DEL TENANT */
IF OBJECT_ID('Roles', 'U') IS NULL
CREATE TABLE Roles (
    id_rol       INT IDENTITY(1,1) PRIMARY KEY,
    ccod_empresa VARCHAR(20)  NOT NULL,
    cdsc_rol     VARCHAR(100) NOT NULL,
    cstatus      VARCHAR(1)   DEFAULT 'A',
    ccod_usuario VARCHAR(50)  NULL,
    dfch_crea    DATETIME     DEFAULT GETDATE()
);
GO

/* 2. MENUS DEL TENANT */
IF OBJECT_ID('Menus', 'U') IS NULL
CREATE TABLE Menus (
    id_menu      INT IDENTITY(1,1) PRIMARY KEY,
    ccod_empresa VARCHAR(20)  NULL,
    cdsc_menu    VARCHAR(100) NULL,
    curl_href    VARCHAR(255) NULL,
    curl_src     VARCHAR(255) NULL,
    nid_menupadre INT          NULL,
    cli_menu     VARCHAR(100) NULL,
    cul_menu     VARCHAR(100) NULL,
    nivel        VARCHAR(10)  NULL,
    corden       INT          NULL,
    cstatus      VARCHAR(1)   DEFAULT 'A'
);
GO

/* 3. ACCESOS */
IF OBJECT_ID('Accesos', 'U') IS NULL
CREATE TABLE Accesos (
    id_acceso    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_empresa VARCHAR(20) NULL,
    id_rol       INT          NULL,
    corden       INT          NULL,
    cstatus      VARCHAR(1)   DEFAULT '1'
);
GO

/* 4. TIENDAS */
IF OBJECT_ID('Tiendas', 'U') IS NULL
CREATE TABLE Tiendas (
    id_tienda             INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia              VARCHAR(20)  NOT NULL,
    ccod_tiend            VARCHAR(20)  NOT NULL,
    cnombr                VARCHAR(100) NULL,
    cdirec                VARCHAR(200) NULL,
    cmail                 VARCHAR(100) NULL,
    ctelef                VARCHAR(20)  NULL,
    cpassw                VARCHAR(50)  NULL,
    cstatus               VARCHAR(1)   DEFAULT 'A',
    nlista_pre_normal     INT          DEFAULT 0,
    nlista_pre_preferencial INT        DEFAULT 0,
    cdepartamento         VARCHAR(2)   NULL,
    cprovincia            VARCHAR(4)   NULL,
    cdistrito             VARCHAR(6)   NULL,
    cubigeo               VARCHAR(6)   NULL,
    curba_tienda          VARCHAR(100) NULL,
    ccod_loc_emis         VARCHAR(20)  NULL,
    ccod_usuario          VARCHAR(50)  NULL,
    dfch_crea             DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_tiend)
);
GO

/* 5. ALMACENES */
IF OBJECT_ID('Almacenes', 'U') IS NULL
CREATE TABLE Almacenes (
    id_almac      INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20)  NOT NULL,
    ccod_alm      VARCHAR(20)  NOT NULL,
    cdsc_alm      VARCHAR(100) NULL,
    cstatus       VARCHAR(1)   DEFAULT 'A',
    cdepartamento VARCHAR(2)   NULL,
    cprovincia    VARCHAR(4)   NULL,
    cdistrito     VARCHAR(6)   NULL,
    cdirc_almac   VARCHAR(200) NULL,
    curba_almac   VARCHAR(100) NULL,
    cubigeo       VARCHAR(6)   NULL,
    ccod_usuario  VARCHAR(50)  NULL,
    dfch_crea     DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_alm)
);
GO

/* 6. TIENDA-ALMACEN */
IF OBJECT_ID('TiendaAlmacen', 'U') IS NULL
CREATE TABLE TiendaAlmacen (
    id_tiendaalm INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20) NOT NULL,
    ccod_tiend   VARCHAR(20) NOT NULL,
    ccod_alm     VARCHAR(20) NOT NULL
);
GO

/* 7. NUMERADORES ALMACEN */
IF OBJECT_ID('NumeradorAlmacen', 'U') IS NULL
CREATE TABLE NumeradorAlmacen (
    id_ctalmac        INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia          VARCHAR(20)  NOT NULL,
    ccod_alm          VARCHAR(20)  NOT NULL,
    ctip_doc          VARCHAR(10)  NULL,
    cserie            VARCHAR(10)  NULL,
    nnumero           INT          DEFAULT 0,
    cdsc_numeralmacen VARCHAR(100) NULL,
    ccod_usuario      VARCHAR(50)  NULL,
    dfch_crea         DATETIME     DEFAULT GETDATE()
);
GO

/* 8. CAJAS */
IF OBJECT_ID('Cajas', 'U') IS NULL
CREATE TABLE Cajas (
    id_caja      INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)  NOT NULL,
    ccod_caja    VARCHAR(20)  NOT NULL,
    cdsc_caja    VARCHAR(100) NULL,
    cstatus      VARCHAR(1)   DEFAULT 'A',
    ccod_usuario VARCHAR(50)  NULL,
    dfch_crea    DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_caja)
);
GO

/* 9. TIENDA-CAJA */
IF OBJECT_ID('TiendaCaja', 'U') IS NULL
CREATE TABLE TiendaCaja (
    id_tiendacaja INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20) NOT NULL,
    ccod_tiend    VARCHAR(20) NOT NULL,
    ccod_caja     VARCHAR(20) NOT NULL,
    ccod_usuario  VARCHAR(50) NULL
);
GO

/* 10. NUMERADORES CAJA */
IF OBJECT_ID('NumeradorCaja', 'U') IS NULL
CREATE TABLE NumeradorCaja (
    id_numer     INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)  NOT NULL,
    ccod_caja    VARCHAR(20)  NOT NULL,
    cdoc_tipo    VARCHAR(10)  NULL,
    cdoc_serie   VARCHAR(10)  NULL,
    cdoc_nro     INT          DEFAULT 0,
    ccod_numer   VARCHAR(20)  NULL,
    cdsc_numer   VARCHAR(100) NULL,
    ccod_usuario VARCHAR(50)  NULL,
    dfch_crea    DATETIME     DEFAULT GETDATE()
);
GO

/* 11. FAMILIAS */
IF OBJECT_ID('Familias', 'U') IS NULL
CREATE TABLE Familias (
    id_lin       INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)  NOT NULL,
    ccod_lin     VARCHAR(20)  NOT NULL,
    cdsc_lin     VARCHAR(100) NULL,
    cstatus      VARCHAR(1)   DEFAULT 'A',
    ccolor       VARCHAR(20)  NULL,
    ccod_usuario VARCHAR(50)  NULL,
    dfch_crea    DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_lin)
);
GO

/* 12. UNIDADES DE MEDIDA */
IF OBJECT_ID('UnidadMedida', 'U') IS NULL
CREATE TABLE UnidadMedida (
    id_unimed    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)  NOT NULL,
    ccod_unimed  VARCHAR(10)  NOT NULL,
    cdsc_unimed  VARCHAR(50)  NULL,
    cstatus      VARCHAR(1)   DEFAULT 'A',
    ccod_usuario VARCHAR(50)  NULL,
    UNIQUE (ccod_cia, ccod_unimed)
);
GO

/* 13. ARTICULOS */
IF OBJECT_ID('Articulos', 'U') IS NULL
CREATE TABLE Articulos (
    id_articulo        INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia           VARCHAR(20)   NOT NULL,
    ccod_articulo      VARCHAR(50)   NOT NULL,
    cdsc_articulo      VARCHAR(200)  NULL,
    ccod_lin           VARCHAR(20)   NULL,
    uni_medi           VARCHAR(10)   NULL,
    cstatus            VARCHAR(1)    DEFAULT 'A',
    ctip_articulo      VARCHAR(10)   NULL,
    cigv               VARCHAR(5)    NULL,
    cisc               VARCHAR(5)    NULL,
    iimage             VARBINARY(MAX) NULL,
    ccod_artSunat      VARCHAR(20)   NULL,
    nstock_max         DECIMAL(18,4) DEFAULT 0,
    nstock_min         DECIMAL(18,4) DEFAULT 0,
    ctipo_isc          VARCHAR(5)    NULL,
    nporcentaje_isc    DECIMAL(18,4) DEFAULT 0,
    nmonto_isc         DECIMAL(18,4) DEFAULT 0,
    bprefer            BIT           DEFAULT 0,
    ccod_usuario       VARCHAR(50)   NULL,
    dfch_crea          DATETIME      DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_articulo)
);
GO

/* 14. VARIANTES CABECERA */
IF OBJECT_ID('CbVariante', 'U') IS NULL
CREATE TABLE CbVariante (
    id_cbvariante INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20)  NOT NULL,
    ccod_articulo VARCHAR(50)  NULL,
    cdsc_variante VARCHAR(100) NULL,
    ccod_usuario  VARCHAR(50)  NULL,
    dfch_crea     DATETIME     DEFAULT GETDATE()
);
GO

/* 15. VARIANTES LINEA */
IF OBJECT_ID('LnVariante', 'U') IS NULL
CREATE TABLE LnVariante (
    id_lnvariante  INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia       VARCHAR(20)  NOT NULL,
    id_cbvariante  INT          NULL,
    cdsc_lnvariante VARCHAR(100) NULL,
    ccod_usuario   VARCHAR(50)  NULL,
    dfch_crea      DATETIME     DEFAULT GETDATE()
);
GO

/* 16. STOCK */
IF OBJECT_ID('Stock', 'U') IS NULL
CREATE TABLE Stock (
    id_stock      INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20)   NOT NULL,
    ccod_alm      VARCHAR(20)   NOT NULL,
    ccod_articulo VARCHAR(50)   NOT NULL,
    ncantidad     DECIMAL(18,4) DEFAULT 0,
    ncosto        DECIMAL(18,4) DEFAULT 0,
    dfch_crea     DATETIME      DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_alm, ccod_articulo)
);
GO

/* 17. CLIENTES / PROVEEDORES (COA) */
IF OBJECT_ID('Coa', 'U') IS NULL
CREATE TABLE Coa (
    id_coa        INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20)  NOT NULL,
    ccod_coa      VARCHAR(20)  NOT NULL,
    cdoc_coa      VARCHAR(20)  NULL,
    cdsc_coa      VARCHAR(200) NULL,
    ctelf         VARCHAR(20)  NULL,
    cmail         VARCHAR(100) NULL,
    ctipo_coa     VARCHAR(10)  NULL,
    cpais         VARCHAR(50)  NULL,
    cdepartamento VARCHAR(2)   NULL,
    cprovincia    VARCHAR(4)   NULL,
    cdistrito     VARCHAR(6)   NULL,
    cdirc_coa     VARCHAR(200) NULL,
    cstatus       VARCHAR(1)   DEFAULT 'A',
    cproveedor    VARCHAR(1)   DEFAULT '0',
    cruc_coa      VARCHAR(20)  NULL,
    ccod_usuario  VARCHAR(50)  NULL,
    dfch_crea     DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_coa)
);
GO

/* 18. TIPOS DE OPERACION */
IF OBJECT_ID('TipoOperacion', 'U') IS NULL
CREATE TABLE TipoOperacion (
    id_tipoper    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia      VARCHAR(20)  NOT NULL,
    ccod_tipoper  VARCHAR(20)  NOT NULL,
    cdsc_tipoper  VARCHAR(100) NULL,
    ctipo_flag    VARCHAR(5)   NULL,  -- 'I'=ingreso, 'S'=salida, 'T'=transferencia
    cstatus       VARCHAR(1)   DEFAULT 'A',
    ccod_usuario  VARCHAR(50)  NULL,
    dfch_crea     DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_cia, ccod_tipoper)
);
GO

/* 19. LISTA DE PRECIOS CABECERA */
IF OBJECT_ID('CbListaPrecio', 'U') IS NULL
CREATE TABLE CbListaPrecio (
    id_cblistpre    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia        VARCHAR(20)  NOT NULL,
    ccod_cblistpre  VARCHAR(20)  NOT NULL,
    cdsc_cblistpre  VARCHAR(100) NULL,
    dfch_ini        DATE         NULL,
    dfch_fin        DATE         NULL,
    cstatus         VARCHAR(1)   DEFAULT 'A',
    UNIQUE (ccod_cia, ccod_cblistpre)
);
GO

/* 20. LISTA DE PRECIOS DETALLE */
IF OBJECT_ID('LnListaPrecio', 'U') IS NULL
CREATE TABLE LnListaPrecio (
    id_lnlistpre   INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia       VARCHAR(20)   NOT NULL,
    ccod_cblistpre VARCHAR(20)   NOT NULL,
    ccod_articulo  VARCHAR(50)   NOT NULL,
    npre_uni       DECIMAL(18,4) DEFAULT 0,
    ndes_max       DECIMAL(18,4) DEFAULT 0,
    ndes_min       DECIMAL(18,4) DEFAULT 0
);
GO

/* 21. CONFIG GENERAL */
IF OBJECT_ID('ConfigGeneral', 'U') IS NULL
CREATE TABLE ConfigGeneral (
    id_config            INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia             VARCHAR(20)   NOT NULL,
    ccod_clibol          VARCHAR(20)   NULL,
    coper_ingreso        VARCHAR(20)   NULL,
    coper_salida         VARCHAR(20)   NULL,
    ctipo_flag_ingreso   VARCHAR(5)    NULL,
    ctipo_flag_salida    VARCHAR(5)    NULL,
    nigv                 DECIMAL(18,4) DEFAULT 18,
    nisc                 DECIMAL(18,4) DEFAULT 0,
    nmonto_maxboleta     DECIMAL(18,4) DEFAULT 700,
    ilogo                VARBINARY(MAX) NULL,
    ccod_usuario         VARCHAR(50)   NULL,
    dfch_crea            DATETIME      DEFAULT GETDATE()
);
GO

/* 22. TURNOS / APERTURA CAJA */
IF OBJECT_ID('Turno', 'U') IS NULL
CREATE TABLE Turno (
    id_turno     INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    ccod_tienda  VARCHAR(20)   NULL,
    ccod_usuario VARCHAR(50)   NULL,
    ccod_caja    VARCHAR(20)   NULL,
    nmonto_ini   DECIMAL(18,4) DEFAULT 0,
    nmonto_fin   DECIMAL(18,4) DEFAULT 0,
    ntot_entreg  DECIMAL(18,4) DEFAULT 0,
    ndiferencia  DECIMAL(18,4) DEFAULT 0,
    dfchdoc_ini  DATETIME      NULL,
    dfchdoc_fin  DATETIME      NULL,
    cstatus      VARCHAR(1)    DEFAULT 'A',
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

/* 23. INVENTARIO CABECERA */
IF OBJECT_ID('CbInventario', 'U') IS NULL
CREATE TABLE CbInventario (
    id_cbinve    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    ccod_tienda  VARCHAR(20)   NULL,
    ccod_alm     VARCHAR(20)   NULL,
    dfecha       DATETIME      NULL,
    ctipo        VARCHAR(10)   NULL,
    vserie       VARCHAR(10)   NULL,
    nnumero      INT           DEFAULT 0,
    vobservacion VARCHAR(500)  NULL,
    ccod_usuario VARCHAR(50)   NULL,
    ntotal       DECIMAL(18,4) DEFAULT 0,
    ccod_coa     VARCHAR(20)   NULL,
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

/* 24. INVENTARIO DETALLE */
IF OBJECT_ID('LnInventario', 'U') IS NULL
CREATE TABLE LnInventario (
    id_lninve    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    id_cbinve    INT           NOT NULL,
    ccod_articulo VARCHAR(50)  NULL,
    ccod_artSunat VARCHAR(20)  NULL,
    cdsc_articulo VARCHAR(200) NULL,
    ncantidad    DECIMAL(18,4) DEFAULT 0,
    ncosto       DECIMAL(18,4) DEFAULT 0,
    ccod_alm     VARCHAR(20)   NULL,
    ccod_alm_ingreso VARCHAR(20) NULL,
    ccod_usuario VARCHAR(50)   NULL,
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

/* 25. FACTURA CABECERA */
IF OBJECT_ID('CbFactura', 'U') IS NULL
CREATE TABLE CbFactura (
    id_cbfact        INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia         VARCHAR(20)   NOT NULL,
    ccod_tiend       VARCHAR(20)   NULL,
    ccod_caja        VARCHAR(20)   NULL,
    ccod_almacen     VARCHAR(20)   NULL,
    ccod_usuario     VARCHAR(50)   NULL,
    cdoc             VARCHAR(5)    NULL,
    cserie           VARCHAR(10)   NULL,
    nnumero          INT           DEFAULT 0,
    ccod_coa         VARCHAR(20)   NULL,
    nimpuesto        DECIMAL(18,4) DEFAULT 0,
    nisc             DECIMAL(18,4) DEFAULT 0,
    ndescuento       DECIMAL(18,4) DEFAULT 0,
    ntotal           DECIMAL(18,4) DEFAULT 0,
    nsubtotal        DECIMAL(18,4) DEFAULT 0,
    nvuelto          DECIMAL(18,4) DEFAULT 0,
    ntot_entreg      DECIMAL(18,4) DEFAULT 0,
    cantidad_bienes  INT           DEFAULT 0,
    id_turno         INT           NULL,
    costo            DECIMAL(18,4) DEFAULT 0,
    cobs             VARCHAR(500)  NULL,
    cstatus          VARCHAR(5)    DEFAULT 'P',  -- P=pendiente, E=emitido, A=anulado
    cstatus_tributario VARCHAR(5)  DEFAULT 'P',
    fecha_emision    DATETIME      DEFAULT GETDATE(),
    id_cbinve        INT           NULL,
    pdf              VARBINARY(MAX) NULL,
    xml              VARBINARY(MAX) NULL,
    xml_cdr          VARBINARY(MAX) NULL,
    dfch_crea        DATETIME      DEFAULT GETDATE()
);
GO

/* 26. FACTURA DETALLE */
IF OBJECT_ID('LnFactura', 'U') IS NULL
CREATE TABLE LnFactura (
    id_lnfact       INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia        VARCHAR(20)   NOT NULL,
    id_cbfact       INT           NOT NULL,
    ccod_tiend      VARCHAR(20)   NULL,
    id_articulo     VARCHAR(50)   NULL,
    cdsc_articulo   VARCHAR(200)  NULL,
    cdoc            VARCHAR(5)    NULL,
    nprecio         DECIMAL(18,4) DEFAULT 0,
    ncantidad       DECIMAL(18,4) DEFAULT 0,
    nimporte_bruto  DECIMAL(18,4) DEFAULT 0,
    nimpuesto       DECIMAL(18,4) DEFAULT 0,
    nisc            DECIMAL(18,4) DEFAULT 0,
    ndescuento      DECIMAL(18,4) DEFAULT 0,
    nimporte_neto   DECIMAL(18,4) DEFAULT 0,
    corden          INT           DEFAULT 0,
    ccod_usuario    VARCHAR(50)   NULL,
    id_cbinve       INT           NULL,
    ccod_almacen    VARCHAR(20)   NULL,
    cobser_variante VARCHAR(200)  NULL,
    ctip_descn      VARCHAR(10)   NULL,
    ncosto          DECIMAL(18,4) DEFAULT 0,
    dfch_crea       DATETIME      DEFAULT GETDATE()
);
GO

/* 27. COBRANZA CABECERA */
IF OBJECT_ID('CbCobranza', 'U') IS NULL
CREATE TABLE CbCobranza (
    id_cbcajac   INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    id_cbfact    INT           NULL,
    id_turno     INT           NULL,
    ccod_tiend   VARCHAR(20)   NULL,
    ccod_caja    VARCHAR(20)   NULL,
    ccod_usuario VARCHAR(50)   NULL,
    ntotal       DECIMAL(18,4) DEFAULT 0,
    ntot_entreg  DECIMAL(18,4) DEFAULT 0,
    nvuelto      DECIMAL(18,4) DEFAULT 0,
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

/* 28. COBRANZA DETALLE */
IF OBJECT_ID('LnCobranza', 'U') IS NULL
CREATE TABLE LnCobranza (
    id_lncajac   INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    id_cbcajac   INT           NULL,
    id_cbfact    INT           NULL,
    ccod_tiend   VARCHAR(20)   NULL,
    nmonto       DECIMAL(18,4) DEFAULT 0,
    cnum_opera   VARCHAR(50)   NULL,
    cnum_tarje   VARCHAR(50)   NULL,
    cnom_tarje   VARCHAR(100)  NULL,
    id_cbfactNC  INT           NULL,
    ccod_usuario VARCHAR(50)   NULL,
    ccod_caja    VARCHAR(20)   NULL,
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

/* 29. CUENTAS (mesa/reserva) */
IF OBJECT_ID('CbCuenta', 'U') IS NULL
CREATE TABLE CbCuenta (
    id_cbcuenta  INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia     VARCHAR(20)   NOT NULL,
    ccod_coa     VARCHAR(20)   NULL,
    ccod_tiend   VARCHAR(20)   NULL,
    ccod_caja    VARCHAR(20)   NULL,
    etiqueta     VARCHAR(50)   NULL,
    ccod_usuario VARCHAR(50)   NULL,
    ctip_cuenta  VARCHAR(5)    DEFAULT '1',
    ntot_desct   DECIMAL(18,4) DEFAULT 0,
    ntot_impbruto DECIMAL(18,4) DEFAULT 0,
    ntot_igv     DECIMAL(18,4) DEFAULT 0,
    ntot_impneto DECIMAL(18,4) DEFAULT 0,
    cstatus      VARCHAR(1)    DEFAULT 'A',
    dfch_crea    DATETIME      DEFAULT GETDATE()
);
GO

IF OBJECT_ID('LnCuenta', 'U') IS NULL
CREATE TABLE LnCuenta (
    id_lncuenta    INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia       VARCHAR(20)   NOT NULL,
    id_cbcuenta    INT           NOT NULL,
    ncantidad      DECIMAL(18,4) DEFAULT 0,
    nprecio        DECIMAL(18,4) DEFAULT 0,
    nimporte_neto  DECIMAL(18,4) DEFAULT 0,
    id_articulo    VARCHAR(50)   NULL,
    nimporte_bruto DECIMAL(18,4) DEFAULT 0,
    nimpuesto      DECIMAL(18,4) DEFAULT 0,
    ndescuento     DECIMAL(18,4) DEFAULT 0,
    ctip_descn     VARCHAR(10)   NULL,
    cobser_variante VARCHAR(200) NULL,
    corden         INT           DEFAULT 0,
    ccod_usuario   VARCHAR(50)   NULL,
    ctip_desc      VARCHAR(10)   NULL,
    nigv_uni       DECIMAL(18,4) DEFAULT 0,
    ncosto         DECIMAL(18,4) DEFAULT 0,
    id_variante    VARCHAR(20)   NULL,
    cdescn_max     VARCHAR(50)   NULL,
    dfch_crea      DATETIME      DEFAULT GETDATE()
);
GO

/* 30. GUIA DE REMISION */
IF OBJECT_ID('CbGuia', 'U') IS NULL
CREATE TABLE CbGuia (
    id_cbguia             INT IDENTITY(1,1) PRIMARY KEY,
    ccod_cia              VARCHAR(20)   NOT NULL,
    ccod_guia             VARCHAR(20)   NULL,
    cserie_guia           VARCHAR(10)   NULL,
    cnum_ruc_rem          VARCHAR(20)   NULL,
    cnom_rzn_soc_rem      VARCHAR(200)  NULL,
    cnum_ruc_dest         VARCHAR(20)   NULL,
    cnom_rzn_soc_dest     VARCHAR(200)  NULL,
    cnum_ruc_proy         VARCHAR(20)   NULL,
    cdsc_coa              VARCHAR(200)  NULL,
    cdomicilio_partida    VARCHAR(300)  NULL,
    ccod_ubi_partida      VARCHAR(10)   NULL,
    cdomicilio_llegada    VARCHAR(300)  NULL,
    ccod_ubi_llegada      VARCHAR(10)   NULL,
    ctrans_nombre         VARCHAR(200)  NULL,
    ctrans_ruc            VARCHAR(20)   NULL,
    ccod_unid_peso_bruto  VARCHAR(10)   NULL,
    nmnt_tot_peso_bruto   DECIMAL(18,4) DEFAULT 0,
    cdesc_motiv_tras      VARCHAR(200)  NULL,
    nobs                  VARCHAR(500)  NULL,
    ctrans_placa          VARCHAR(20)   NULL,
    ctrans_licencia       VARCHAR(20)   NULL,
    ntotal                DECIMAL(18,4) DEFAULT 0,
    cusu_crea             VARCHAR(50)   NULL,
    ccod_alm              VARCHAR(20)   NULL,
    ctipo                 VARCHAR(10)   NULL,
    cserie                VARCHAR(10)   NULL,
    ccod_almOrigen        VARCHAR(20)   NULL,
    ccod_almDestino       VARCHAR(20)   NULL,
    dfec_fin              DATE          NULL,
    cdoc_ref              VARCHAR(20)   NULL,
    cod_tip_cpe           VARCHAR(10)   NULL,
    ccod_coa              VARCHAR(20)   NULL,
    flag                  VARCHAR(1)    NULL, -- 'I'=ingreso, 'S'=salida, 'T'=translado
    id_cbinve             INT           NULL,
    nnumero               VARCHAR(20)   NULL,
    fchEmision            DATETIME      DEFAULT GETDATE(),
    dfch_crea             DATETIME      DEFAULT GETDATE()
);
GO

PRINT '✓ Tablas de DatPos_EMP01 creadas correctamente.';
GO
