/* ========================================================================
   PARTE 1: COMPLETAR DatPosAdmin
   SP necesario para el módulo cliente: webDatpos_consultaUsuario
   (ya existía webDatpos_consultaUsuario en admin pero el cliente lo llama
    pasando solo @ccod_usuario — alineamos la firma)
======================================================================== */
USE DatPosAdmin;
GO

/* Ya incluido en el avance: sp_validarusuario, sp_insertarusuarios,
   sp_editarusuariocliente, sp_eliminarusuariocliente, webDatpos_cambiarContrasena,
   sp_consultausuario (en EMP01), sp_consultausuarios (en EMP01),
   sp_editarusuario (en EMP01), sp_eliminarusuario (en EMP01),
   webDatpos_cargarFotoUsuario (en EMP01), sp_consultarusuarioturno (en EMP01) */


/* Tablas base de DatPosAdmin para setup desde cero */
IF OBJECT_ID('Empresas', 'U') IS NULL
CREATE TABLE Empresas (
    ccod_empresa VARCHAR(20) NOT NULL PRIMARY KEY,
    cdsc_empresa VARCHAR(200) NULL,
    cnum_tribu VARCHAR(20) NULL,
    cnombre_bd VARCHAR(100) NULL,
    cnombre_servidor VARCHAR(100) NULL,
    cdomicilio VARCHAR(200) NULL,
    curbanizacion VARCHAR(100) NULL,
    cubigeo VARCHAR(20) NULL,
    csimbolo_moneda VARCHAR(5) NULL,
    cnombre_moneda VARCHAR(50) NULL,
    cpais_origen VARCHAR(50) NULL,
    ctarifas VARCHAR(50) NULL,
    ntienda_extra INT NULL,
    nusuario_extra INT NULL,
    cdoc VARCHAR(20) NULL,
    cnomser VARCHAR(100) NULL
);
GO

IF OBJECT_ID('Usuarios', 'U') IS NULL
CREATE TABLE Usuarios (
    id_usuario INT IDENTITY(1,1) PRIMARY KEY,
    ccod_usuario VARCHAR(50) NOT NULL,
    cdsc_usuario VARCHAR(100) NULL,
    cpassw VARCHAR(200) NULL,
    cdirec VARCHAR(200) NULL,
    id_rol INT NULL,
    ccod_empresa VARCHAR(20) NULL,
    id_estado INT DEFAULT 1,
    dfch_crea DATETIME DEFAULT GETDATE(),
    cmail VARCHAR(100) NULL,
    ctelf VARCHAR(20) NULL,
    ccelular VARCHAR(20) NULL,
    ccod_tiend VARCHAR(20) NULL,
    ccod_almacen VARCHAR(20) NULL,
    ccod_caja VARCHAR(20) NULL,
    cperm_descn VARCHAR(50) NULL,
    ifoto VARBINARY(MAX) NULL,
    cpassw_bcrypt VARCHAR(255) NULL
);
GO

IF NOT EXISTS (SELECT 1 FROM Empresas WHERE ccod_empresa='EMP01')
INSERT INTO Empresas(ccod_empresa, cdsc_empresa, cnum_tribu, cnombre_bd, cnombre_servidor,
                     cdomicilio, csimbolo_moneda, cnombre_moneda, cdoc, cnomser)
VALUES('EMP01', 'EMPRESA DEMO DATPOS', '20000000001', 'DatPos_EMP01', 'localhost',
       'Av. Principal 123, Lima', 'S/', 'SOLES', 'RUC', 'localhost');
GO

IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_usuario='ADMIN')
INSERT INTO Usuarios(ccod_usuario, cdsc_usuario, cpassw, id_rol, ccod_empresa, id_estado,
                     ccod_tiend, ccod_almacen, ccod_caja, cperm_descn)
VALUES('ADMIN', 'ADMINISTRADOR', '123456', 1, 'EMP01', 1, 'T001', 'ALM001', 'CAJ01', '100');
GO


IF OBJECT_ID('Roles', 'U') IS NULL
CREATE TABLE Roles (
    id_rol INT NOT NULL PRIMARY KEY,
    cdsc_rol VARCHAR(100) NULL,
    cstatus VARCHAR(1) DEFAULT 'A'
);
GO
IF NOT EXISTS (SELECT 1 FROM Roles WHERE id_rol=1)
INSERT INTO Roles(id_rol, cdsc_rol, cstatus) VALUES(1, 'ADMINISTRADOR', 'A');
GO


IF OBJECT_ID('Estados', 'U') IS NULL
CREATE TABLE Estados (
    id_estado INT NOT NULL PRIMARY KEY,
    cdsc_estado VARCHAR(50) NULL
);
GO
IF NOT EXISTS (SELECT 1 FROM Estados WHERE id_estado=1)
INSERT INTO Estados(id_estado, cdsc_estado) VALUES(1, 'ACTIVO');
GO

IF OBJECT_ID('Menus', 'U') IS NULL
CREATE TABLE Menus (
    id_menu INT IDENTITY(1,1) PRIMARY KEY,
    cdsc_menu VARCHAR(100) NULL
);
GO

IF OBJECT_ID('Accesos', 'U') IS NULL
CREATE TABLE Accesos (
    id_acceso INT IDENTITY(1,1) PRIMARY KEY,
    id_rol INT NULL,
    id_menu INT NULL
);
GO

IF OBJECT_ID('Departamentos', 'U') IS NULL
CREATE TABLE Departamentos(id_departamento VARCHAR(10) PRIMARY KEY, cdescripcion VARCHAR(100));
GO
IF OBJECT_ID('Provincias', 'U') IS NULL
CREATE TABLE Provincias(id_provincia VARCHAR(10) PRIMARY KEY, id_departamento VARCHAR(10), cdescripcion VARCHAR(100));
GO
IF OBJECT_ID('Distritos', 'U') IS NULL
CREATE TABLE Distritos(id_distrito VARCHAR(10) PRIMARY KEY, id_provincia VARCHAR(10), cdescripcion VARCHAR(100));
GO


/* Completar columnas de Usuarios esperadas por SPs legacy */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='cdirec')
    ALTER TABLE Usuarios ADD cdirec VARCHAR(200) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='dfch_crea')
    ALTER TABLE Usuarios ADD dfch_crea DATETIME NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='cmail')
    ALTER TABLE Usuarios ADD cmail VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='ctelf')
    ALTER TABLE Usuarios ADD ctelf VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='ccelular')
    ALTER TABLE Usuarios ADD ccelular VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='ifoto')
    ALTER TABLE Usuarios ADD ifoto VARBINARY(MAX) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='cpassw_bcrypt')
    ALTER TABLE Usuarios ADD cpassw_bcrypt VARCHAR(255) NULL;
GO

/* Completar columnas esperadas por los SPs legacy */
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='curbanizacion')
    ALTER TABLE Empresas ADD curbanizacion VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cubigeo')
    ALTER TABLE Empresas ADD cubigeo VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='csimbolo_moneda')
    ALTER TABLE Empresas ADD csimbolo_moneda VARCHAR(5) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cnombre_moneda')
    ALTER TABLE Empresas ADD cnombre_moneda VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cpais_origen')
    ALTER TABLE Empresas ADD cpais_origen VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ctarifas')
    ALTER TABLE Empresas ADD ctarifas VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ntienda_extra')
    ALTER TABLE Empresas ADD ntienda_extra INT NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='nusuario_extra')
    ALTER TABLE Empresas ADD nusuario_extra INT NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cdoc')
    ALTER TABLE Empresas ADD cdoc VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cnomser')
    ALTER TABLE Empresas ADD cnomser VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cdirec')
    ALTER TABLE Empresas ADD cdirec VARCHAR(200) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ctelf')
    ALTER TABLE Empresas ADD ctelf VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cprovincia')
    ALTER TABLE Empresas ADD cprovincia VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cdistrito')
    ALTER TABLE Empresas ADD cdistrito VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='cdepartamento')
    ALTER TABLE Empresas ADD cdepartamento VARCHAR(100) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ctip_facturador')
    ALTER TABLE Empresas ADD ctip_facturador VARCHAR(20) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='dfch_vencimiento')
    ALTER TABLE Empresas ADD dfch_vencimiento DATETIME NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ccod_cliente_emis')
    ALTER TABLE Empresas ADD ccod_cliente_emis VARCHAR(50) NULL;
GO
IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Empresas') AND name='ctoken')
    ALTER TABLE Empresas ADD ctoken VARCHAR(500) NULL;
GO

/* Verificar que los campos extra existen en Usuarios */
IF NOT EXISTS (SELECT * FROM sys.columns WHERE object_id = OBJECT_ID('Usuarios') AND name = 'ccod_tiend')
BEGIN
    ALTER TABLE Usuarios ADD
        ccod_tiend      VARCHAR(20)  NULL,
        ccod_almacen    VARCHAR(20)  NULL,
        ccod_caja       VARCHAR(20)  NULL,
        cperm_descn     VARCHAR(50)  NULL,
        ifoto           VARBINARY(MAX) NULL;
END
GO

/* webDatpos_consultaUsuario — consulta por código de usuario (para ConsultarUsuario del cliente) */
IF OBJECT_ID('webDatpos_consultaUsuario', 'P') IS NOT NULL DROP PROCEDURE webDatpos_consultaUsuario;
GO
CREATE PROCEDURE webDatpos_consultaUsuario
    @ccod_usuario VARCHAR(50)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        U.id_usuario,
        U.ccod_usuario,
        U.cdsc_usuario,
        U.cpassw,
        ISNULL(U.cdirec, ''),
        U.id_rol,
        U.ccod_empresa,
        CAST(U.id_estado AS VARCHAR),
        ISNULL(CONVERT(VARCHAR, U.dfch_crea, 120), ''),
        ISNULL(U.cmail, ''),
        ISNULL(U.ctelf, ''),
        ISNULL(U.ccelular, ''),
        ISNULL(E.cdsc_empresa, ''),
        ISNULL(U.ccod_tiend, ''),
        ISNULL(U.ccod_almacen, ''),
        ISNULL(U.ccod_caja, ''),
        ISNULL(U.cperm_descn, '')
    FROM Usuarios U
    INNER JOIN Empresas E ON U.ccod_empresa = E.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.id_estado = 1;
END
GO

PRINT '✓ DatPosAdmin completado correctamente.';
GO
