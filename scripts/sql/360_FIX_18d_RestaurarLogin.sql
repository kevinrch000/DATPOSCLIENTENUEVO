/* =====================================================================
   FIX 18d — RESTAURAR sp_validarusuario en DatPosAdmin
   
   El LogOn.aspx.vb lee EXACTAMENTE 23 columnas por índice:
   [0]  id_ctusu        → algún ID del admin
   [1]  ccod_usuario
   [2]  cdsc_usuario
   [3]  rolMaster
   [4]  ccod_empresa
   [5]  cnombre_bd
   [6]  cnomser         → cnombre_servidor
   [7]  cdescripcion
   [8]  cnum_tribu      ← RUC (YA actualizado con '20000000001')
   [9]  ntienda_extra
   [10] nusuario_extra
   [11] ctarifas
   [12] cnombre_moneda
   [13] csimbolo_moneda
   [14] cdomicilio
   [15] cprovincia
   [16] cdistrito
   [17] cdepartamento
   [18] ctip_facturador
   [19] dfch_vencimiento
   [20] id_estado (= 'Habilitado' cuando =1)  ← CHECK CRÍTICO línea 14
   [21] ccod_cliente_emis
   [22] ctoken
   Ejecutar en DatPosAdmin
===================================================================== */
USE DatPosAdmin;
GO

IF OBJECT_ID('sp_validarusuario','P') IS NOT NULL DROP PROCEDURE sp_validarusuario;
GO
CREATE PROCEDURE sp_validarusuario @ccod_usuario VARCHAR(50), @cpassw VARCHAR(200)
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
        ISNULL(E.cnum_tribu,'')                    AS cnum_tribu,        -- [8] ← RUC
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
        CASE WHEN U.id_estado=1 THEN 'Habilitado' ELSE 'Bloqueado' END AS estado, -- [20] ← CHECK
        ISNULL(E.ccod_cliente_emis,'')             AS ccod_cliente_emis, -- [21]
        ISNULL(E.ctoken,'')                        AS ctoken             -- [22]
    FROM Usuarios U
    INNER JOIN Empresas E ON E.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.cpassw       = @cpassw;
END
GO

-- Probar que el login funciona:
EXEC sp_validarusuario 'ADMIN', 'e10adc3949ba59abbe56e057f20f883e';
GO

PRINT 'OK - FIX 18d. Ahora intenta iniciar sesion de nuevo.';
GO
