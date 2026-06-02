/* =====================================================================
   FIX 48 — JWT + Bcrypt Authentication
   
   Cambios:
   1. Agrega columna cpassw_bcrypt a DatPosAdmin.Usuarios (VARCHAR(255))
   2. Agrega columna cpassw_bcrypt a DatPos_EMP01.Usuarios (VARCHAR(255))
   3. Crea SP sp_buscarusuario_login en DatPosAdmin
      (busca usuario por username SIN filtrar por password,
       retorna cpassw + cpassw_bcrypt para verificacion PHP-side)
   4. Crea SP sp_migrar_password_bcrypt en DatPosAdmin
      (actualiza cpassw_bcrypt tras migracion automatica)
   5. Crea SP webDatpos_cambiarContrasena_v2 en DatPos_EMP01
      (recibe hash bcrypt directo, sin verificar old password en SQL)
   
   Ejecutar en: DatPosAdmin primero, luego DatPos_EMP01
===================================================================== */

/* ── PARTE A: DatPosAdmin ── */
USE DatPosAdmin;
GO

-- 1. Agregar columna cpassw_bcrypt si no existe
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID('Usuarios') AND name = 'cpassw_bcrypt'
)
BEGIN
    ALTER TABLE Usuarios ADD cpassw_bcrypt VARCHAR(255) NULL;
    PRINT '+ columna cpassw_bcrypt agregada a DatPosAdmin.Usuarios';
END
ELSE
    PRINT '= columna cpassw_bcrypt ya existe en DatPosAdmin.Usuarios';
GO

-- 2. SP sp_buscarusuario_login
--    Igual que sp_validarusuario pero SIN filtrar por cpassw.
--    Retorna las mismas 23 columnas + cpassw[23] + cpassw_bcrypt[24]
IF OBJECT_ID('sp_buscarusuario_login','P') IS NOT NULL DROP PROCEDURE sp_buscarusuario_login;
GO
CREATE PROCEDURE sp_buscarusuario_login @ccod_usuario VARCHAR(50)
AS BEGIN SET NOCOUNT ON;
    SELECT
        U.id_usuario                               AS id_ctusu,         -- [0]
        U.ccod_usuario,                                                  -- [1]
        U.cdsc_usuario,                                                  -- [2]
        U.id_rol                                   AS rolMaster,         -- [3]
        U.ccod_empresa,                                                  -- [4]
        ISNULL(E.cnombre_bd,'')                    AS cnombre_bd,        -- [5]
        ISNULL(E.cnombre_servidor,'')              AS cnomser,           -- [6]
        ISNULL(E.cdsc_empresa,'')                  AS cdescripcion,      -- [7]
        ISNULL(E.cnum_tribu,'')                    AS cnum_tribu,        -- [8]
        ISNULL(E.ntienda_extra,0)                  AS ntienda_extra,     -- [9]
        ISNULL(E.nusuario_extra,0)                 AS nusuario_extra,    -- [10]
        ISNULL(E.ctarifas,'')                      AS ctarifas,          -- [11]
        ISNULL(E.cnombre_moneda,'')                AS cnombre_moneda,    -- [12]
        ISNULL(E.csimbolo_moneda,'')               AS csimbolo_moneda,   -- [13]
        ISNULL(E.cdomicilio,'')                    AS cdomicilio,        -- [14]
        ISNULL(E.cprovincia,'')                    AS cprovincia,        -- [15]
        ISNULL(E.cdistrito,'')                     AS cdistrito,         -- [16]
        ISNULL(E.cdepartamento,'')                 AS cdepartamento,     -- [17]
        ISNULL(E.ctip_facturador,'')               AS ctip_facturador,   -- [18]
        E.dfch_vencimiento,                                              -- [19]
        CASE WHEN U.id_estado=1 THEN 'Habilitado' ELSE 'Bloqueado' END AS estado, -- [20]
        ISNULL(E.ccod_cliente_emis,'')             AS ccod_cliente_emis, -- [21]
        ISNULL(E.ctoken,'')                        AS ctoken,            -- [22]
        ISNULL(U.cpassw,'')                        AS cpassw,            -- [23] MD5 legacy
        ISNULL(U.cpassw_bcrypt,'')                 AS cpassw_bcrypt      -- [24] bcrypt hash
    FROM Usuarios U
    INNER JOIN Empresas E ON E.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario;
END
GO
PRINT '+ sp_buscarusuario_login creado';
GO

-- 3. SP sp_migrar_password_bcrypt
--    Actualiza cpassw_bcrypt + reemplaza cpassw con MD5 (elimina texto plano)
IF OBJECT_ID('sp_migrar_password_bcrypt','P') IS NOT NULL DROP PROCEDURE sp_migrar_password_bcrypt;
GO
CREATE PROCEDURE sp_migrar_password_bcrypt
    @ccod_usuario    VARCHAR(50),
    @cpassw_bcrypt   VARCHAR(255),
    @cpassw_md5      VARCHAR(200) = NULL
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios
    SET cpassw_bcrypt = @cpassw_bcrypt,
        cpassw        = COALESCE(@cpassw_md5, cpassw)
    WHERE ccod_usuario = @ccod_usuario;
END
GO
PRINT '+ sp_migrar_password_bcrypt creado';
GO


/* ── PARTE B: DatPos_EMP01 (Tenant) ── */
USE DatPos_EMP01;
GO

-- 4. Agregar columna cpassw_bcrypt al tenant si no existe
IF NOT EXISTS (
    SELECT * FROM sys.columns 
    WHERE object_id = OBJECT_ID('Usuarios') AND name = 'cpassw_bcrypt'
)
BEGIN
    ALTER TABLE Usuarios ADD cpassw_bcrypt VARCHAR(255) NULL;
    PRINT '+ columna cpassw_bcrypt agregada a DatPos_EMP01.Usuarios';
END
ELSE
    PRINT '= columna cpassw_bcrypt ya existe en DatPos_EMP01.Usuarios';
GO

-- 5. Actualizar sp_insertarusuarios para incluir cpassw_bcrypt
IF OBJECT_ID('sp_insertarusuarios','P') IS NOT NULL DROP PROCEDURE sp_insertarusuarios;
GO
CREATE PROCEDURE sp_insertarusuarios
    @ccod_empresa    VARCHAR(20),
    @ccod_usuario    VARCHAR(50),
    @cdsc_usuario    VARCHAR(200),
    @cpassw          VARCHAR(200),
    @cmail           VARCHAR(100) = '',
    @ctelf           VARCHAR(20)  = '',
    @ccelular        VARCHAR(20)  = '',
    @id_rol          INT          = 0,
    @ccod_tiend      VARCHAR(20)  = '',
    @ccod_almacen    VARCHAR(20)  = '',
    @ccod_caja       VARCHAR(20)  = '',
    @cperm_descn     VARCHAR(50)  = '',
    @ccod_usuariocrea VARCHAR(50) = '',
    @cpassw_bcrypt   VARCHAR(255) = NULL,
    @cdirec          VARCHAR(200) = '',
    @ifoto           VARBINARY(MAX) = NULL,
    @ctarifas        VARCHAR(50) = '',
    @nusuario_extra  INT = 0,
    @id_estado       INT = 1
AS BEGIN SET NOCOUNT ON;
    INSERT INTO Usuarios (
        ccod_empresa, ccod_usuario, cdsc_usuario, cpassw, cpassw_bcrypt,
        cmail, ctelf, ccelular, id_rol,
        ccod_tiend, ccod_almacen, ccod_caja, cperm_descn,
        id_estado, ccod_usuariocrea, cdirec, ifoto, dfch_crea
    ) VALUES (
        @ccod_empresa, @ccod_usuario, @cdsc_usuario, @cpassw, @cpassw_bcrypt,
        @cmail, @ctelf, @ccelular, @id_rol,
        @ccod_tiend, @ccod_almacen, @ccod_caja, @cperm_descn,
        @id_estado, @ccod_usuariocrea, @cdirec, @ifoto, GETDATE()
    );
END
GO
PRINT '+ sp_insertarusuarios actualizado con cpassw_bcrypt';
GO

-- 6. Actualizar webDatpos_editarUsuario para incluir cpassw_bcrypt
IF OBJECT_ID('webDatpos_editarUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_editarUsuario;
GO
CREATE PROCEDURE webDatpos_editarUsuario
    @ccod_cia        VARCHAR(20),
    @usu_crea        VARCHAR(50),
    @ccod_usuario    VARCHAR(50),
    @cdirc_usuario   VARCHAR(200) = '',
    @cdsc_usuario    VARCHAR(200) = '',
    @cpassw          VARCHAR(200) = '',
    @rol             INT = 0,
    @cstatus         VARCHAR(1) = 'A',
    @cmail           VARCHAR(100) = '',
    @ctelf           VARCHAR(20) = '',
    @ccelular        VARCHAR(20) = '',
    @ErrorNumber     VARCHAR(20) = '' OUTPUT,
    @ErrorMessage    VARCHAR(200) = '' OUTPUT,
    @cpassw_bcrypt   VARCHAR(255) = NULL
AS BEGIN SET NOCOUNT ON;
    BEGIN TRY
        UPDATE Usuarios SET
            cdsc_usuario  = @cdsc_usuario,
            cdirec        = @cdirc_usuario,
            cpassw        = @cpassw,
            cpassw_bcrypt = CASE WHEN @cpassw_bcrypt IS NOT NULL THEN @cpassw_bcrypt ELSE cpassw_bcrypt END,
            id_rol        = @rol,
            id_estado     = CASE WHEN @cstatus='A' THEN 1 ELSE 0 END,
            cmail         = @cmail,
            ctelf         = @ctelf,
            ccelular      = @ccelular
        WHERE ccod_empresa = @ccod_cia AND ccod_usuario = @ccod_usuario;
        SET @ErrorNumber = '0';
        SET @ErrorMessage = 'OK';
    END TRY
    BEGIN CATCH
        SET @ErrorNumber = CAST(ERROR_NUMBER() AS VARCHAR);
        SET @ErrorMessage = ERROR_MESSAGE();
    END CATCH
END
GO
PRINT '+ webDatpos_editarUsuario actualizado con cpassw_bcrypt';
GO

-- 7. SP para cambio de contrasena con bcrypt (version nueva)
IF OBJECT_ID('webDatpos_cambiarContrasena_v2','P') IS NOT NULL DROP PROCEDURE webDatpos_cambiarContrasena_v2;
GO
CREATE PROCEDURE webDatpos_cambiarContrasena_v2
    @ccod_cia        VARCHAR(20),
    @ccod_usuario    VARCHAR(50),
    @cpassw          VARCHAR(200),
    @cpassw_bcrypt   VARCHAR(255)
AS BEGIN SET NOCOUNT ON;
    UPDATE Usuarios SET
        cpassw        = @cpassw,
        cpassw_bcrypt = @cpassw_bcrypt
    WHERE ccod_empresa = @ccod_cia AND ccod_usuario = @ccod_usuario;
    SELECT 'OK' AS respuesta;
END
GO
PRINT '+ webDatpos_cambiarContrasena_v2 creado';
GO

-- 8. Actualizar webDatpos_consultaUsuario para incluir cpassw_bcrypt
--    (columna 18 = cpassw_bcrypt)
--    Mantiene firma dual (@ccod_usuario, @ccod_cia) de FIX 28
IF OBJECT_ID('webDatpos_consultaUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaUsuario;
GO
CREATE PROCEDURE webDatpos_consultaUsuario
    @ccod_usuario VARCHAR(50) = NULL,
    @ccod_cia     VARCHAR(50) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    SELECT
        U.ccod_usuario,                         -- 0
        U.cdsc_usuario,                         -- 1
        ISNULL(U.cdirec,''),                    -- 2
        ISNULL(R.cdsc_rol,''),                  -- 3
        U.id_estado,                            -- 4
        ISNULL(U.ccod_tiend,''),                -- 5
        ISNULL(U.ccod_almacen,''),              -- 6
        ISNULL(U.ccod_caja,''),                 -- 7
        ISNULL(U.cpassw,''),                    -- 8
        ISNULL(U.id_rol,0),                     -- 9
        '',                                     -- 10
        '',                                     -- 11
        ISNULL(T.cdirec,''),                    -- 12: cdirc_tienda
        ISNULL(T.cprovincia,''),                -- 13: cprovincia_tienda
        ISNULL(T.cdistrito,''),                 -- 14: cdistrito_tienda
        ISNULL(T.cdepartamento,''),             -- 15: cdepartamento_tienda
        ISNULL(T.ctelef,''),                    -- 16: ctelef_tienda
        ISNULL(T.cnombr,''),                    -- 17: cnombr (cdsc_tienda)
        ISNULL(U.cpassw_bcrypt,'')              -- 18: cpassw_bcrypt
    FROM Usuarios U
    LEFT JOIN Roles R   ON R.id_rol  = U.id_rol     AND R.ccod_empresa = U.ccod_empresa
    LEFT JOIN Tiendas T ON T.ccod_tiend = U.ccod_tiend AND T.ccod_cia    = U.ccod_empresa
    WHERE
          (NULLIF(@ccod_usuario, '') IS NULL OR U.ccod_usuario = @ccod_usuario)
      AND (NULLIF(@ccod_cia,     '') IS NULL OR U.ccod_empresa = @ccod_cia)
      AND (NULLIF(@ccod_usuario, '') IS NOT NULL
           OR U.id_estado = 1)
    ORDER BY U.cdsc_usuario;
END
GO
PRINT '+ webDatpos_consultaUsuario actualizado con cpassw_bcrypt';
GO

PRINT '';
PRINT '===================================================';
PRINT 'FIX 48 completado — JWT + Bcrypt Authentication';
PRINT '===================================================';
GO
