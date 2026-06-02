/* ========================================================================
   MODIFY_941 / FIX_76
   Corrige el login de empleados del tenant y el CRUD de Usuarios.

   Problemas:
     1. LOGIN TENANT: autenticarUsuarioTenant() (LogOn.php) no lograba
        resolver el servidor del tenant ("[LoginTenant] No se pudo resolver
        servidor/BD del tenant"). Usaba sp_consultarempresas, que devuelve la
        columna Empresas.cnomser, normalmente VACIA. El login admin funciona
        porque sp_buscarusuario_login usa Empresas.cnombre_servidor (la columna
        canonica, si poblada).
        -> Se crea sp_consultar_empresas_login que toma el servidor de
           cnombre_servidor (fallback a cnomser).

     2. EDITAR USUARIO: webDatpos_editarUsuario sobrescribia cpassw aunque el
        formulario enviara la contrasena vacia (al editar sin cambiarla),
        dejando al usuario sin contrasena. Ahora conserva la contrasena
        existente cuando los parametros llegan vacios.

   Ejecutar PARTE A en DatPosAdmin y PARTE B en DatPos_EMP01.
======================================================================== */

/* ===================== PARTE A: DatPosAdmin ===========================*/
USE DatPosAdmin;
GO
SET LANGUAGE us_english;
GO
PRINT '== MODIFY 941 / FIX 76 (Admin): sp_consultar_empresas_login ==';

IF OBJECT_ID('sp_consultar_empresas_login','P') IS NOT NULL DROP PROCEDURE sp_consultar_empresas_login;
GO
CREATE PROCEDURE sp_consultar_empresas_login
AS BEGIN SET NOCOUNT ON;
    SELECT
        ccod_empresa,                                                   -- [0]
        ISNULL(cdsc_empresa,'')                       AS cdescripcion,  -- [1]
        ''                                            AS cdoc,          -- [2]
        ISNULL(cnum_tribu,'')                         AS cnum_tribu,    -- [3]
        ISNULL(NULLIF(cnombre_servidor,''),
               ISNULL(cnomser,''))                    AS cnomser,       -- [4] servidor real
        ISNULL(cnombre_bd,'')                         AS cnombre_bd     -- [5]
    FROM Empresas;
END
GO
PRINT '+ sp_consultar_empresas_login creado';
GO

/* ===================== PARTE B: DatPos_EMP01 ==========================*/
USE DatPos_EMP01;
GO
SET LANGUAGE us_english;
GO
PRINT '== MODIFY 941 / FIX 76 (Tenant): webDatpos_editarUsuario conserva password ==';

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
            -- Conservar contrasena si el formulario la envia vacia (edicion sin cambio)
            cpassw        = CASE WHEN @cpassw <> '' THEN @cpassw ELSE cpassw END,
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
PRINT '+ webDatpos_editarUsuario actualizado (conserva password)';
GO

/* --- Usuarios de ejemplo para probar el login por rol (opcional) -------
   Empleados creados normalmente por un admin desde Usuarios.php; se siembran
   aqui solo para tener un camino de prueba inmediato. Password '123456'
   (MD5 e10adc3949ba59abbe56e057f20f883e; se migra a bcrypt en el primer login).
   Re-ejecutable: solo inserta si no existen.                              */
DECLARE @cia VARCHAR(20) = 'EMP01';
DECLARE @rolCajero     INT = (SELECT TOP 1 id_rol FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='CAJERO'     ORDER BY id_rol);
DECLARE @rolAlmacenero INT = (SELECT TOP 1 id_rol FROM Roles WHERE ccod_empresa=@cia AND cdsc_rol='ALMACENERO' ORDER BY id_rol);
DECLARE @md5_123456 VARCHAR(32) = 'e10adc3949ba59abbe56e057f20f883e';

IF @rolCajero IS NOT NULL AND NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa=@cia AND ccod_usuario='cajero')
    INSERT INTO Usuarios (ccod_empresa, ccod_usuario, cdsc_usuario, cpassw, id_rol,
                          ccod_tiend, ccod_almacen, ccod_caja, cperm_descn, id_estado, ccod_usuariocrea)
    VALUES (@cia, 'cajero', 'CAJERO DEMO', @md5_123456, @rolCajero,
            'T001', 'ALM001', 'CAJ01', '100', 1, 'ADMIN');

IF @rolAlmacenero IS NOT NULL AND NOT EXISTS (SELECT 1 FROM Usuarios WHERE ccod_empresa=@cia AND ccod_usuario='almacenero')
    INSERT INTO Usuarios (ccod_empresa, ccod_usuario, cdsc_usuario, cpassw, id_rol,
                          ccod_tiend, ccod_almacen, ccod_caja, cperm_descn, id_estado, ccod_usuariocrea)
    VALUES (@cia, 'almacenero', 'ALMACENERO DEMO', @md5_123456, @rolAlmacenero,
            'T001', 'ALM001', 'CAJ01', '100', 1, 'ADMIN');
GO
PRINT '+ Usuarios demo cajero/almacenero (password 123456) verificados';
GO

PRINT 'OK - FIX 76 completo.';
GO
