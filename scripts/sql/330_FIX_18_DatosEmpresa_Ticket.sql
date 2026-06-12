/* =====================================================================
   FIX 18 — Datos de empresa y tienda para el ticket de venta
   
   El ticket (Facturacion.aspx) usa campos del Session("objBEUsuario"):
   - #hdd_ruc      ← Session("objBEUsuario").cnum_tribu
   - #hhd_empresa  ← nombre de la empresa 
   - #hdd_nombre_tienda ← nombre de tienda
   - #hdd_telefono_tienda ← teléfono de tienda
   
   Además ArmarHtml() llama: llenarobjeto('Facturacion.aspx/ConsultarTienda')
   que usa el SP webDatpos_ConsultarTienda y lee obj[0].cdirec
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ── 1. Actualizar ConfigGeneral con IGV (columnas reales del esquema PHP) ── */
-- NOTA: cnum_tribu, cnombr_empre, cdirec_empre, ctelf_empre NO existen en ConfigGeneral.
-- Los datos del RUC/empresa vienen de DatPosAdmin.Empresas (ver FIX_18b/18c).
-- Solo actualizamos columnas que realmente existen.
BEGIN TRY
    UPDATE ConfigGeneral
    SET nigv = 18, nisc = 0
    WHERE ccod_cia = 'EMP01';

    IF @@ROWCOUNT = 0
        INSERT INTO ConfigGeneral(ccod_cia, nigv, nisc)
        VALUES('EMP01', 18, 0);
END TRY
BEGIN CATCH
    PRINT 'FIX_18 WARNING: UPDATE ConfigGeneral omitido. OK (columnas legacy cnum_tribu/cnombr_empre no existen).';
END CATCH
GO

/* ── 2. Actualizar Tiendas con dirección y teléfono (para obj[0].cdirec) ── */
UPDATE Tiendas
SET cdirec  = 'Av. Principal 123, Lima',
    ctelef  = '01-1234567',
    cstatus = 'A'
WHERE ccod_cia = 'EMP01' AND ccod_tiend = 'T001';
GO

/* ── 3. Actualizar Usuarios: cnum_tribu del usuario también debe tener RUC ── */
-- El SP de Login guarda los datos del usuario en Session("objBEUsuario")
-- cnum_tribu en Usuarios puede venir de ConfigGeneral o de Empresa según el SP de login
-- Verificar cómo se carga la sesión:
UPDATE Usuarios 
SET cdsc_usuario = 'ADMINISTRADOR'
WHERE ccod_empresa='EMP01' AND ccod_usuario='ADMIN';
GO

/* ── 4. webDatpos_ConsultarTienda (@ccod_cia, @ccod_tiend) — devuelve cdirec, ctelef ── */
IF OBJECT_ID('webDatpos_ConsultarTienda','P') IS NOT NULL DROP PROCEDURE webDatpos_ConsultarTienda;
GO
CREATE PROCEDURE webDatpos_ConsultarTienda @ccod_cia VARCHAR(20), @ccod_tiend VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT ccod_tiend,
           ISNULL(cnombr,'')  AS cnombr,
           ISNULL(cdirec,'')  AS cdirec,
           ISNULL(ctelef,'')  AS ctelef,
           ISNULL(cmail,'')   AS cmail,
           ISNULL(cstatus,'') AS cstatus
    FROM Tiendas WHERE ccod_cia=@ccod_cia AND ccod_tiend=@ccod_tiend;
END
GO

/* ── 5. Facturacion.aspx/ConsultarTienda — el WebMethod llama a este SP
   El JS en ArmarHtml() usa: obj[0].cdirec
   Verificar que el SP que se llama es: webDatpos_ConsultarTienda ──────── */
-- Este SP ya fue creado arriba. Verificar:
EXEC webDatpos_ConsultarTienda 'EMP01','T001';
GO

/* ── 6. Verificar datos para el ticket ── */
SELECT 'ConfigGeneral' AS origen,
       CAST(nigv AS VARCHAR) AS igv,
       CAST(nisc AS VARCHAR) AS isc
FROM ConfigGeneral WHERE ccod_cia='EMP01';

SELECT 'Tienda' AS origen,
       ccod_tiend, cnombr, cdirec, ctelef
FROM Tiendas WHERE ccod_cia='EMP01';
GO
PRINT 'OK - FIX 18 completo.';
GO
