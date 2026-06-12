/* ========================================================================
   PARTE 5: DatPos_EMP01 — SPs USUARIOS (en BD tenant)
======================================================================== */
USE DatPos_EMP01;
GO

/* Tabla de Usuarios en el tenant (réplica de sesión desde DatPosAdmin) */
IF OBJECT_ID('Usuarios', 'U') IS NULL
CREATE TABLE Usuarios (
    id_usuario   INT IDENTITY(1,1) PRIMARY KEY,
    ccod_empresa VARCHAR(20)  NOT NULL,
    ccod_usuario VARCHAR(50)  NOT NULL,
    cdsc_usuario VARCHAR(100) NULL,
    cpassw       VARCHAR(200) NULL,
    cdirec       VARCHAR(200) NULL,
    cmail        VARCHAR(100) NULL,
    ctelf        VARCHAR(20)  NULL,
    ccelular     VARCHAR(20)  NULL,
    id_rol       INT          NULL,
    ccod_tiend   VARCHAR(20)  NULL,
    ccod_almacen VARCHAR(20)  NULL,
    ccod_caja    VARCHAR(20)  NULL,
    cperm_descn  VARCHAR(50)  NULL,
    id_estado    INT          DEFAULT 1,
    ifoto        VARBINARY(MAX) NULL,
    ccod_usuariocrea VARCHAR(50) NULL,
    dfch_crea    DATETIME     DEFAULT GETDATE(),
    UNIQUE (ccod_empresa, ccod_usuario)
);
GO

/* sp_validarusuario — login del cliente */
IF OBJECT_ID('sp_validarusuario','P') IS NOT NULL DROP PROCEDURE sp_validarusuario; 
GO
CREATE PROCEDURE sp_validarusuario
    @ccod_usuario VARCHAR(50), @cpassw VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    SELECT U.id_usuario, U.ccod_usuario, U.cdsc_usuario, U.cpassw,
           U.ccod_empresa, U.id_rol, U.id_estado,
           ISNULL(U.ccod_tiend,'')   AS ccod_tiend,
           ISNULL(U.ccod_almacen,'') AS ccod_almacen,
           ISNULL(U.ccod_caja,'')    AS ccod_caja,
           ISNULL(U.cperm_descn,'')  AS cperm_descn
    FROM Usuarios U
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.cpassw       = @cpassw
      AND U.id_estado    = 1;
END
GO

/* sp_consultarusuarios — lista todos los usuarios de la empresa */
IF OBJECT_ID('sp_consultarusuarios','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarios; 
GO
CREATE PROCEDURE sp_consultarusuarios @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT U.id_usuario, U.ccod_usuario, U.cdsc_usuario, U.ccod_tiend,
           U.ccod_almacen, U.ccod_caja, U.id_rol,
           ISNULL(R.cdsc_rol,'') AS cdsc_rol, U.id_estado
    FROM Usuarios U
    LEFT JOIN Roles R ON R.id_rol = U.id_rol AND R.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_empresa = @ccod_empresa
    ORDER BY U.cdsc_usuario;
END
GO

/* webDatpos_consultaUsuario — consulta un usuario específico */
IF OBJECT_ID('webDatpos_consultaUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaUsuario; 
GO
CREATE PROCEDURE webDatpos_consultaUsuario @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.id_usuario, U.ccod_usuario, U.cdsc_usuario, U.ccod_empresa,
           U.id_rol, ISNULL(R.cdsc_rol,'') AS cdsc_rol,
           U.id_estado, U.cmail, U.ctelf, U.ccelular,
           ISNULL(U.ccod_tiend,'')   AS ccod_tiend,
           ISNULL(U.ccod_almacen,'') AS ccod_almacen,
           ISNULL(U.ccod_caja,'')    AS ccod_caja,
           ISNULL(U.cperm_descn,'')  AS cperm_descn
    FROM Usuarios U
    LEFT JOIN Roles R ON R.id_rol = U.id_rol AND R.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario AND U.id_estado = 1;
END
GO

/* sp_insertarusuarios — insertar usuario en el tenant */
IF OBJECT_ID('sp_insertarusuarios','P') IS NOT NULL DROP PROCEDURE sp_insertarusuarios; 
GO
CREATE PROCEDURE sp_insertarusuarios
    @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50), @cdsc_usuario VARCHAR(100),
    @cpassw VARCHAR(200), @cmail VARCHAR(100), @ctelf VARCHAR(20), @ccelular VARCHAR(20),
    @id_rol INT, @ccod_tiend VARCHAR(20), @ccod_almacen VARCHAR(20), @ccod_caja VARCHAR(20),
    @cperm_descn VARCHAR(50), @ccod_usuariocrea VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    IF NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario)
        INSERT INTO Usuarios (ccod_empresa,ccod_usuario,cdsc_usuario,cpassw,cmail,ctelf,ccelular,
            id_rol,ccod_tiend,ccod_almacen,ccod_caja,cperm_descn,ccod_usuariocrea)
        VALUES (@ccod_empresa,@ccod_usuario,@cdsc_usuario,@cpassw,@cmail,@ctelf,@ccelular,
            @id_rol,@ccod_tiend,@ccod_almacen,@ccod_caja,@cperm_descn,@ccod_usuariocrea);
END
GO

/* sp_editarusuario */
IF OBJECT_ID('sp_editarusuario','P') IS NOT NULL DROP PROCEDURE sp_editarusuario; 
GO
CREATE PROCEDURE sp_editarusuario
    @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50), @cdsc_usuario VARCHAR(100),
    @cmail VARCHAR(100), @ctelf VARCHAR(20), @ccelular VARCHAR(20),
    @id_rol INT, @ccod_tiend VARCHAR(20), @ccod_almacen VARCHAR(20), @ccod_caja VARCHAR(20),
    @cperm_descn VARCHAR(50), @ccod_usuariocrea VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET cdsc_usuario=@cdsc_usuario,cmail=@cmail,ctelf=@ctelf,ccelular=@ccelular,
        id_rol=@id_rol,ccod_tiend=@ccod_tiend,ccod_almacen=@ccod_almacen,ccod_caja=@ccod_caja,
        cperm_descn=@cperm_descn
    WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario;
END
GO

/* sp_editarusuariocliente — edición del propio perfil */
IF OBJECT_ID('sp_editarusuariocliente','P') IS NOT NULL DROP PROCEDURE sp_editarusuariocliente; 
GO
CREATE PROCEDURE sp_editarusuariocliente
    @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50),
    @cdsc_usuario VARCHAR(100), @cmail VARCHAR(100), @ctelf VARCHAR(20), @ccelular VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET cdsc_usuario=@cdsc_usuario,cmail=@cmail,ctelf=@ctelf,ccelular=@ccelular
    WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario;
END
GO

/* sp_eliminarusuario */
IF OBJECT_ID('sp_eliminarusuario','P') IS NOT NULL DROP PROCEDURE sp_eliminarusuario; 
GO
CREATE PROCEDURE sp_eliminarusuario @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET id_estado=0 WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario;
END
GO

/* sp_eliminarusuariocliente */
IF OBJECT_ID('sp_eliminarusuariocliente','P') IS NOT NULL DROP PROCEDURE sp_eliminarusuariocliente; 
GO
CREATE PROCEDURE sp_eliminarusuariocliente @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET id_estado=0 WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario;
END
GO

/* sp_consultarusuarioturno */
IF OBJECT_ID('sp_consultarusuarioturno','P') IS NOT NULL DROP PROCEDURE sp_consultarusuarioturno; 
GO
CREATE PROCEDURE sp_consultarusuarioturno
    @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT U.ccod_usuario, U.cdsc_usuario, U.ccod_tiend, U.ccod_almacen, U.ccod_caja,
           T.id_turno, T.cstatus AS estado_turno, T.nmonto_ini, T.dfchdoc_ini
    FROM Usuarios U
    LEFT JOIN Turno T ON T.ccod_usuario=U.ccod_usuario AND T.ccod_cia=U.ccod_empresa AND T.cstatus='A'
    WHERE U.ccod_empresa=@ccod_empresa AND U.ccod_usuario=@ccod_usuario;
END
GO

/* webDatpos_cambiarContrasena */
IF OBJECT_ID('webDatpos_cambiarContrasena','P') IS NOT NULL DROP PROCEDURE webDatpos_cambiarContrasena; 
GO
CREATE PROCEDURE webDatpos_cambiarContrasena
    @ccod_usuario VARCHAR(50), @cpassw_ant VARCHAR(200), @cpassw_nva VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    IF EXISTS (SELECT 1 FROM Usuarios WHERE ccod_usuario=@ccod_usuario AND cpassw=@cpassw_ant AND id_estado=1)
    BEGIN
        UPDATE Usuarios SET cpassw=@cpassw_nva WHERE ccod_usuario=@ccod_usuario;
        SELECT 'OK' AS respuesta;
    END
    ELSE SELECT 'ERROR' AS respuesta;
END
GO

/* webDatpos_cargarFotoUsuario */
IF OBJECT_ID('webDatpos_cargarFotoUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_cargarFotoUsuario; 
GO
CREATE PROCEDURE webDatpos_cargarFotoUsuario
    @ccod_empresa VARCHAR(20), @ccod_usuario VARCHAR(50), @ifoto VARBINARY(MAX)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET ifoto=@ifoto WHERE ccod_empresa=@ccod_empresa AND ccod_usuario=@ccod_usuario;
END
GO

/* webDatpos_CargarListaUsuario — para ConfigGeneral */
IF OBJECT_ID('webDatpos_CargarListaUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_CargarListaUsuario; 
GO
CREATE PROCEDURE webDatpos_CargarListaUsuario @ccod_empresa VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_usuario, cdsc_usuario FROM Usuarios WHERE ccod_empresa=@ccod_empresa AND id_estado=1 ORDER BY cdsc_usuario;
END
GO

PRINT '✓ SPs de Usuarios (tenant) creados.';
GO
