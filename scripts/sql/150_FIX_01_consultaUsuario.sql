/* CORRECCIÓN: sp_consultausuario — debe devolver exactamente 18 columnas
   según lo que lee LogOn.aspx.vb (índices 0 al 17) */
USE DatPos_EMP01;
GO

IF OBJECT_ID('webDatpos_consultaUsuario','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaUsuario;
GO
CREATE PROCEDURE webDatpos_consultaUsuario
    @ccod_usuario VARCHAR(50)
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
        '',                                     -- 10 (reservado)
        '',                                     -- 11 (reservado)
        ISNULL(T.cdirec,''),                    -- 12: cdirc_tienda
        ISNULL(T.cprovincia,''),                -- 13: cprovincia_tienda
        ISNULL(T.cdistrito,''),                 -- 14: cdistrito_tienda
        ISNULL(T.cdepartamento,''),             -- 15: cdepartamento_tienda
        ISNULL(T.ctelef,''),                    -- 16: ctelf_tienda
        ISNULL(T.cnombr,'')                     -- 17: cdsc_tienda
    FROM Usuarios U
    LEFT JOIN Roles R ON R.id_rol = U.id_rol AND R.ccod_empresa = U.ccod_empresa
    LEFT JOIN Tiendas T ON T.ccod_tiend = U.ccod_tiend AND T.ccod_cia = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.id_estado = 1;
END
GO

PRINT '✓ webDatpos_consultaUsuario corregido con 18 columnas.';
GO
