/* ========================================================================
   MODIFY_914 / FIX_67
   ConsultaListPrecio.php: combo "Lista de Precios" salia vacio.

   Causa: el case 'CargarListPrecio' del API llamaba al SP
   'sp_cargarlistprecio' que nunca existio (ni en los scripts base ni en
   los MODIFY/NEW posteriores). El SP que si existe y devuelve lo que el
   JS necesita es 'sp_consultarlistaspreciosactivos' (creado en
   080_07_EMP01_TipoOper_Config_Caja.sql lineas 82-86).

   Cambios en este PR:
   1. api/consultadocumento_api.php: 'CargarListPrecio' ahora invoca
      'sp_consultarlistaspreciosactivos' y replica el resultado tanto en
      las claves nuevas (ccod_cblistpre / cdsc_cblistpre que lee el JS de
      ConsultaListPrecio.js) como en las claves legacy (id_cblistpre /
      cdsc_listpre).
   2. Este script: idempotentemente garantiza que ambos SPs existan
      ('sp_consultarlistaspreciosactivos' canonico y 'sp_cargarlistprecio'
      como alias) para que el endpoint sirva tanto si el origen es la
      instalacion completa como si solo se aplicaron los scripts
      MODIFY/NEW posteriores.
======================================================================== */
USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== MODIFY 914 / FIX 67: SPs CargarListPrecio (alias + canonico) ==';

/* SP canonico (ya existe en 080, lo recreamos por idempotencia) */
IF OBJECT_ID('sp_consultarlistaspreciosactivos','P') IS NOT NULL
    DROP PROCEDURE sp_consultarlistaspreciosactivos;
GO
CREATE PROCEDURE sp_consultarlistaspreciosactivos
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    SELECT ccod_cblistpre, cdsc_cblistpre
    FROM CbListaPrecio
    WHERE ccod_cia = @ccod_cia AND cstatus = 'A'
    ORDER BY ccod_cblistpre;
END
GO
PRINT '  -> sp_consultarlistaspreciosactivos (canonico) creado.';

/* Alias para el nombre legacy que la API usaba antes de FIX_67 */
IF OBJECT_ID('sp_cargarlistprecio','P') IS NOT NULL
    DROP PROCEDURE sp_cargarlistprecio;
GO
CREATE PROCEDURE sp_cargarlistprecio
    @ccod_cia VARCHAR(20)
AS
BEGIN
    SET NOCOUNT ON;
    EXEC sp_consultarlistaspreciosactivos @ccod_cia;
END
GO
PRINT '  -> sp_cargarlistprecio (alias) creado.';

PRINT '== MODIFY 914 / FIX 67 completado. ==';
GO

/* Verificacion rapida (no es necesaria, solo informativa).
   Si CbListaPrecio tiene LP001/LP002 con cstatus='A' debe devolverlos. */
PRINT '== Verificacion (EMP01) ==';
EXEC sp_consultarlistaspreciosactivos @ccod_cia = 'EMP01';
GO
