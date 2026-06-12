/* ========================================================================
   FIX 28 - webDatpos_consultaUsuario: aceptar tanto @ccod_usuario como @ccod_cia
   - Si @ccod_usuario != '' → devuelve un solo usuario (login / detalle)
   - Si @ccod_cia      != '' → devuelve TODOS los usuarios de la empresa (listado)
   Mantiene el mismo set de 18 columnas que FIX_01 para compatibilidad con LogOn.php
   y con api/usuario_api.php :: ConsultarUsuarios
   ======================================================================== */
USE DatPos_EMP01;
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
        ISNULL(T.cnombr,'')                     -- 17: cnombr (cdsc_tienda)
    FROM Usuarios U
    LEFT JOIN Roles R   ON R.id_rol  = U.id_rol     AND R.ccod_empresa = U.ccod_empresa
    LEFT JOIN Tiendas T ON T.ccod_tiend = U.ccod_tiend AND T.ccod_cia    = U.ccod_empresa
    WHERE
          (NULLIF(@ccod_usuario, '') IS NULL OR U.ccod_usuario = @ccod_usuario)
      AND (NULLIF(@ccod_cia,     '') IS NULL OR U.ccod_empresa = @ccod_cia)
      AND (NULLIF(@ccod_usuario, '') IS NOT NULL  -- en modo single-user no filtramos por estado
           OR U.id_estado = 1)                    -- en modo lista: solo activos
    ORDER BY U.cdsc_usuario;
END
GO

PRINT 'OK: webDatpos_consultaUsuario aceptara ahora @ccod_usuario o @ccod_cia.';
GO
