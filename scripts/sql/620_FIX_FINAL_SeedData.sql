/* =====================================================================
   FIX FINAL — Seed data manual que se aplico durante la migracion
   
   Estas correcciones se hicieron a mano durante la sesion de pruebas.
   Las dejamos aqui para que cualquier instalacion nueva quede consistente.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ---------------------------------------------------------------------
   1. TipoOperacion: clasificar por flag I (Ingreso) / S (Salida)
   GuiaRemision, Salidas e Ingresos filtran combos por ctipo_flag,
   pero la columna a veces queda en NULL al insertar tipos nuevos.
--------------------------------------------------------------------- */
PRINT '--- Estado inicial de TipoOperacion ---';
SELECT ccod_tipoper, cdsc_tipoper, ctipo_flag, cstatus FROM TipoOperacion WHERE ccod_cia='EMP01';

UPDATE TipoOperacion SET ctipo_flag='I'
WHERE ccod_cia='EMP01' AND ctipo_flag IS NULL
  AND ccod_tipoper IN ('COMP','AJIN','IPC','IPD','ICC','IPT');

UPDATE TipoOperacion SET ctipo_flag='S'
WHERE ccod_cia='EMP01' AND ctipo_flag IS NULL
  AND ccod_tipoper IN ('VENT','AJER','SPV','SPA','SPT');

-- Como ultimo recurso: inferir flag por nombre si nunca se seteo
UPDATE TipoOperacion SET ctipo_flag='I'
WHERE ccod_cia='EMP01' AND ctipo_flag IS NULL
  AND (cdsc_tipoper LIKE '%INGRESO%' OR cdsc_tipoper LIKE '%COMPRA%' OR cdsc_tipoper LIKE '%DEVOLUCION%');

UPDATE TipoOperacion SET ctipo_flag='S'
WHERE ccod_cia='EMP01' AND ctipo_flag IS NULL
  AND (cdsc_tipoper LIKE '%SALIDA%' OR cdsc_tipoper LIKE '%VENTA%' OR cdsc_tipoper LIKE '%EGRESO%' OR cdsc_tipoper LIKE '%AJUSTE%');

PRINT '--- Estado final de TipoOperacion ---';
SELECT ccod_tipoper, cdsc_tipoper, ctipo_flag, cstatus FROM TipoOperacion WHERE ccod_cia='EMP01';
GO

/* ---------------------------------------------------------------------
   2. COA: marcar proveedores activos
   Si la tabla COA tiene cproveedor en NULL, ningun cliente aparece como
   proveedor en Ingresos Directos. Marcamos los que tengan datos validos.
--------------------------------------------------------------------- */
PRINT '--- Antes: proveedores activos ---';
SELECT COUNT(*) AS proveedores_activos FROM Coa WHERE ccod_cia='EMP01' AND cproveedor='1' AND cstatus='A';

-- Si no hay ninguno, marcar TODOS los activos como proveedores (datos de prueba)
IF NOT EXISTS (SELECT 1 FROM Coa WHERE ccod_cia='EMP01' AND cproveedor='1' AND cstatus='A')
BEGIN
    UPDATE Coa SET cproveedor='1' WHERE ccod_cia='EMP01' AND cstatus='A';
    PRINT 'OK: COA marcados como proveedores (datos de prueba)';
END
GO

PRINT '--- Despues: proveedores activos ---';
SELECT COUNT(*) AS proveedores_activos FROM Coa WHERE ccod_cia='EMP01' AND cproveedor='1' AND cstatus='A';
GO

PRINT '=== Seed final aplicado correctamente ===';
GO
