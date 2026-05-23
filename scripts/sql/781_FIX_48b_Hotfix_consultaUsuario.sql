/* ========================================================================
   FIX 48b HOTFIX - Corrige webDatpos_consultaUsuario
   
   El FIX 48 original usaba nombres de columna incorrectos de la tabla
   Tiendas (ccod_tienda, cdirc_tienda, cdsc_tienda, ctelf) que no existen.
   Los nombres correctos son: ccod_tiend, cdirec, cnombr, ctelef.
   
   Tambien restaura la firma dual (@ccod_usuario, @ccod_cia) de FIX 28.
   
   Ejecutar en: DatPos_EMP01
   ======================================================================== */
USE DatPos_EMP01;
GO

IF NOT EXISTS (SELECT 1 FROM sys.columns WHERE object_id=OBJECT_ID('Usuarios') AND name='cpassw_bcrypt')
    ALTER TABLE Usuarios ADD cpassw_bcrypt VARCHAR(255) NULL;
GO

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

PRINT '+ webDatpos_consultaUsuario CORREGIDO (hotfix columnas Tiendas)';
GO
