/* =====================================================================
   FIX 18c — Datos de empresa para ticket (columnas reales verificadas)
   
   Columnas reales de DatPosAdmin.Empresas:
   cnum_tribu, cdsc_empresa, cnombre_bd, cnombre_servidor, cdomicilio,
   curbanizacion, cubigeo, csimbolo_moneda, cnombre_moneda
   
   NO existen: cdirec, ctelf, cnombr_empre, cnomser
===================================================================== */

/* ── PARTE A: DatPosAdmin — completar RUC y dirección ── */
USE DatPosAdmin;
GO

UPDATE Empresas
SET cnum_tribu   = '20000000001',
    cdomicilio   = 'Av. Principal 123, Lima',
    csimbolo_moneda = 'S/',
    cnombre_moneda  = 'SOLES'
WHERE ccod_empresa = 'EMP01';
GO

/* Recrear sp_validarusuario con los campos reales de Empresas
   para que Session("objBEUsuario").cnum_tribu llegue con el RUC */
IF OBJECT_ID('sp_validarusuario','P') IS NOT NULL DROP PROCEDURE sp_validarusuario;
GO
CREATE PROCEDURE sp_validarusuario @ccod_usuario VARCHAR(50), @cpassw VARCHAR(200)
AS BEGIN SET NOCOUNT ON;
    SELECT 
        U.id_usuario,
        U.ccod_usuario,
        U.cdsc_usuario,
        U.cpassw,
        U.ccod_empresa,
        U.id_rol,
        U.id_estado,
        ISNULL(U.ccod_tiend,'')       AS ccod_tiend,
        ISNULL(U.ccod_almacen,'')     AS ccod_almacen,
        ISNULL(U.ccod_caja,'')        AS ccod_caja,
        ISNULL(U.cperm_descn,'')      AS cperm_descn,
        ISNULL(E.cnum_tribu,'')       AS cnum_tribu,
        ISNULL(E.cdsc_empresa,'')     AS cdsc_empresa,
        ISNULL(E.cdomicilio,'')       AS cdomicilio,
        ISNULL(E.cnombre_bd,'')       AS cnombre_bd,
        ISNULL(E.cnombre_servidor,'') AS cnombre_servidor,
        ISNULL(E.csimbolo_moneda,'')  AS csimbolo_moneda,
        ISNULL(E.cnombre_moneda,'')   AS cnombre_moneda
    FROM Usuarios U
    INNER JOIN Empresas E ON E.ccod_empresa = U.ccod_empresa
    WHERE U.ccod_usuario = @ccod_usuario
      AND U.cpassw       = @cpassw
      AND U.id_estado    = 1;
END
GO

-- Verificar que EMP01 ahora tiene RUC
SELECT ccod_empresa, cdsc_empresa, cnum_tribu, cdomicilio, csimbolo_moneda, cnombre_moneda
FROM Empresas WHERE ccod_empresa = 'EMP01';
GO

/* ── PARTE B: DatPos_EMP01 — teléfono viene de Tiendas ── */
USE DatPos_EMP01;
GO

UPDATE Tiendas
SET ctelef = '01-1234567'
WHERE ccod_cia='EMP01' AND ccod_tiend='T001';
GO

SELECT ccod_tiend, cnombr, cdirec, ctelef FROM Tiendas WHERE ccod_cia='EMP01';
GO

PRINT 'OK - FIX 18c completo. Cierra sesión y vuelve a entrar.';
GO
