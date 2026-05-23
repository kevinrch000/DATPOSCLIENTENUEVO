/* =====================================================================
   FIX 24 — SPs faltantes de Numerador Almacén + Datos con ISNULL
   
   Problema 1: Salida.aspx muestra "La serie del almacen no esta 
   configurado" porque el SP appDatpos_consultaNumeradorSalida no existe.
   
   Problema 2: Ingresos.aspx lanza InvalidCastException (DBNull→String)
   porque el SP webDatpos_consultaringresos retorna campos que pueden
   ser NULL y el VB no tiene null checks.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ─────────────────────────────────────────────────────────────────────
   1. appDatpos_consultaNumeradorSalida
   Llamado por DAAlmacen.ConsultarNumeradorSalida(@ccod_alm, @ccod_cia)
   VB espera: [0] cserie, [1] nnumero
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('appDatpos_consultaNumeradorSalida','P') IS NOT NULL DROP PROCEDURE appDatpos_consultaNumeradorSalida;
GO
CREATE PROCEDURE appDatpos_consultaNumeradorSalida
    @ccod_alm VARCHAR(20),
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT cserie, nnumero
    FROM NumeradorAlmacen
    WHERE ccod_cia = @ccod_cia 
      AND ccod_alm = @ccod_alm 
      AND ctip_doc = 'S';
END
GO
PRINT '✓ appDatpos_consultaNumeradorSalida creado';
GO

/* ─────────────────────────────────────────────────────────────────────
   2. appDatpos_consultaNumeradorAlmacen
   Llamado por DAAlmacen.ConsultarNumerador(@ccod_alm, @ccod_cia)
   VB espera: [0] cserie
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('appDatpos_consultaNumeradorAlmacen','P') IS NOT NULL DROP PROCEDURE appDatpos_consultaNumeradorAlmacen;
GO
CREATE PROCEDURE appDatpos_consultaNumeradorAlmacen
    @ccod_alm VARCHAR(20),
    @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT cserie, nnumero
    FROM NumeradorAlmacen
    WHERE ccod_cia = @ccod_cia 
      AND ccod_alm = @ccod_alm 
      AND ctip_doc = 'I';
END
GO
PRINT '✓ appDatpos_consultaNumeradorAlmacen creado';
GO

/* ─────────────────────────────────────────────────────────────────────
   3. Corregir webDatpos_consultaringresos para retornar ISNULL
   Evita DBNull→String InvalidCastException
   VB espera 8 columnas: id_cbinve, ccod_alm, dfecha, ctipo, 
                          vserie, nnumero, vobservacion, ntotal
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultaringresos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultaringresos;
GO
CREATE PROCEDURE webDatpos_consultaringresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT 
        C.id_cbinve,
        ISNULL(C.ccod_alm, '')       AS ccod_alm,
        C.dfecha,
        ISNULL(C.ctipo, '')          AS ctipo,
        ISNULL(C.vserie, '')         AS vserie,
        ISNULL(C.nnumero, 0)         AS nnumero,
        ISNULL(C.vobservacion, '')   AS vobservacion,
        ISNULL(C.ntotal, 0)          AS ntotal
    FROM CbInventario C 
    WHERE C.ccod_cia = @ccod_cia 
      AND C.ctipo NOT IN ('S','ST') 
    ORDER BY C.dfecha DESC;
END
GO
PRINT '✓ webDatpos_consultaringresos corregido con ISNULL';
GO

/* ─────────────────────────────────────────────────────────────────────
   4. Corregir webDatpos_consultarSalidas para retornar ISNULL
───────────────────────────────────────────────────────────────────── */
IF OBJECT_ID('webDatpos_consultarSalidas','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalidas;
GO
CREATE PROCEDURE webDatpos_consultarSalidas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT 
        C.id_cbinve,
        ISNULL(C.ccod_alm, '')       AS ccod_alm,
        C.dfecha,
        ISNULL(C.ctipo, '')          AS ctipo,
        ISNULL(C.vserie, '')         AS vserie,
        ISNULL(C.nnumero, 0)         AS nnumero,
        ISNULL(C.vobservacion, '')   AS vobservacion,
        ISNULL(C.ntotal, 0)          AS ntotal
    FROM CbInventario C 
    WHERE C.ccod_cia = @ccod_cia 
      AND C.ctipo IN ('S') 
    ORDER BY C.dfecha DESC;
END
GO
PRINT '✓ webDatpos_consultarSalidas corregido con ISNULL';
GO

/* ─────────────────────────────────────────────────────────────────────
   5. Datos semilla NumeradorAlmacen (serie I y S para cada almacén)
   Solo inserta si no existen registros
───────────────────────────────────────────────────────────────────── */
-- Ingreso para cada almacén
INSERT INTO NumeradorAlmacen (ccod_cia, ccod_alm, ctip_doc, cserie, nnumero, cdsc_numeralmacen, ccod_usuario)
SELECT 'EMP01', ccod_alm, 'I', 'I001', 1, 'Numerador Ingreso', 'ADMIN'
FROM Almacenes 
WHERE ccod_cia = 'EMP01' AND cstatus = 'A'
  AND NOT EXISTS (
    SELECT 1 FROM NumeradorAlmacen NA 
    WHERE NA.ccod_cia = 'EMP01' AND NA.ccod_alm = Almacenes.ccod_alm AND NA.ctip_doc = 'I'
  );
GO

-- Salida para cada almacén
INSERT INTO NumeradorAlmacen (ccod_cia, ccod_alm, ctip_doc, cserie, nnumero, cdsc_numeralmacen, ccod_usuario)
SELECT 'EMP01', ccod_alm, 'S', 'S001', 1, 'Numerador Salida', 'ADMIN'
FROM Almacenes 
WHERE ccod_cia = 'EMP01' AND cstatus = 'A'
  AND NOT EXISTS (
    SELECT 1 FROM NumeradorAlmacen NA 
    WHERE NA.ccod_cia = 'EMP01' AND NA.ccod_alm = Almacenes.ccod_alm AND NA.ctip_doc = 'S'
  );
GO

PRINT '✓ Datos semilla NumeradorAlmacen insertados (I y S)';
GO

-- Verificación
SELECT ccod_alm, ctip_doc, cserie, nnumero, cdsc_numeralmacen
FROM NumeradorAlmacen 
WHERE ccod_cia = 'EMP01' 
ORDER BY ccod_alm, ctip_doc;
GO

PRINT '═══════════════════════════════════════';
PRINT '  FIX 24 COMPLETO';
PRINT '═══════════════════════════════════════';
GO
