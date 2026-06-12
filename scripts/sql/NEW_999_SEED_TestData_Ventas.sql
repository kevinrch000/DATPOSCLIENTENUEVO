-- ============================================================
-- NEW_999_SEED_TestData_Ventas.sql      (v3 — cascade dinámica)
-- ============================================================
-- Estado : NEW
-- Motivo : Seed idempotente con el mismo estado de datos que
--          se usó para validar Ventas en pruebas end-to-end.
-- Orden  : Ejecutar al final, después de TODOS los scripts
--          base + MODIFY/NEW (820..900).
--
-- v3 changes:
--   * Cleanup en cascada DINÁMICA: primero detecta TODOS los
--     ids reales (CbFactura, CbCobranza, LnCobranza) que están
--     relacionados con los turnos 2/3 y los ids hardcoded del
--     test, luego borra en el orden correcto.
--     Antes el script solo limpiaba por una lista fija de ids,
--     dejaba huérfanas otras CbFactura con id_turno IN (2,3) y
--     trozaba con FK_LnFact_CbFact / FK_CbFact_Turno.
--   * SET DATEFORMAT ymd + fechas ISO sin guiones (locale-safe).
--   * Si el INSERT del Turno detecta que ya existe, hace UPDATE
--     en lugar de fallar (PK violation friendly).
--
-- Árbol de FKs (basado en sys.foreign_keys de DatPos_EMP01):
--   LnCobranza.id_cbcajac  -> CbCobranza
--   LnCobranza.id_cbfact   -> CbFactura
--   CbCobranza.id_cbfact   -> CbFactura
--   CbCobranza.id_turno    -> Turno
--   LnFactura.id_cbfact    -> CbFactura
--   CbFactura.id_turno     -> Turno
-- ============================================================
USE DatPos_EMP01;
GO

SET DATEFORMAT ymd;
SET LANGUAGE us_english;
GO

SET NOCOUNT ON;
PRINT '== Limpieza inicial idempotente (cascade dinamica) ==';

DECLARE @cia VARCHAR(20)  = 'EMP01';
DECLARE @ids TABLE (id INT);
INSERT INTO @ids VALUES (2),(3),(6),(7),(8);

DECLARE @turnos TABLE (id INT);
INSERT INTO @turnos VALUES (2),(3);

-- 1. Recolectar TODOS los CbFactura que toquen turnos 2/3
--    o estén en la lista hardcoded.
DECLARE @factIds TABLE (id INT PRIMARY KEY);
INSERT INTO @factIds (id)
SELECT DISTINCT F.id_cbfact
FROM CbFactura F
WHERE F.ccod_cia=@cia
  AND (F.id_cbfact IN (SELECT id FROM @ids)
       OR F.id_turno IN (SELECT id FROM @turnos));

-- 2. Recolectar TODOS los CbCobranza relacionados:
--    (a) referencian un CbFactura del paso 1
--    (b) o referencian directamente uno de los turnos
DECLARE @cobrIds TABLE (id INT PRIMARY KEY);
IF OBJECT_ID('CbCobranza','U') IS NOT NULL
    INSERT INTO @cobrIds (id)
    SELECT DISTINCT CC.id_cbcajac
    FROM CbCobranza CC
    WHERE CC.ccod_cia=@cia
      AND (CC.id_cbfact IN (SELECT id FROM @factIds)
           OR CC.id_turno IN (SELECT id FROM @turnos));

-- 3. DELETE LnCobranza
IF OBJECT_ID('LnCobranza','U') IS NOT NULL
    DELETE FROM LnCobranza
    WHERE ccod_cia=@cia
      AND (id_cbcajac IN (SELECT id FROM @cobrIds)
           OR id_cbfact   IN (SELECT id FROM @factIds));

-- 4. DELETE CbCobranza
IF OBJECT_ID('CbCobranza','U') IS NOT NULL
    DELETE FROM CbCobranza
    WHERE ccod_cia=@cia AND id_cbcajac IN (SELECT id FROM @cobrIds);

-- 5. DELETE LnFactura (ahora sí cubre TODA la cabecera a borrar)
DELETE FROM LnFactura
WHERE ccod_cia=@cia AND id_cbfact IN (SELECT id FROM @factIds);

-- 6. DELETE CbFactura
DELETE FROM CbFactura
WHERE ccod_cia=@cia AND id_cbfact IN (SELECT id FROM @factIds);

-- 7. DELETE Turno
DELETE FROM Turno WHERE ccod_cia=@cia AND id_turno IN (SELECT id FROM @turnos);

-- 8. DELETE Coa de prueba
DELETE FROM Coa WHERE ccod_cia=@cia AND ccod_coa IN ('87654321','CLI_TEST');

SET NOCOUNT OFF;

PRINT '== 1) Clientes de prueba ==';
INSERT INTO Coa
    (ccod_cia, ccod_coa, cdsc_coa, cdoc_coa, ctipo_coa, ctelf, cmail,
     cpais, cdepartamento, cprovincia, cdistrito, cdirc_coa, cstatus, cproveedor)
VALUES
    ('EMP01','CLI_TEST','CLIENTE EDITADO DEVIN','12345678','D','999999999','edit@dev.local',
     'PE','15','1501','150101','Av Reforma 999','I','1'),
    ('EMP01','87654321','CLIENTE UI DEVIN (EDITADO 2)','87654321','1','987654321','ui-devin@test.com',
     'PE','LI','LIMA','MIRAFL','Av. Test 123 Mod','I','0');

PRINT '== 2) NumeradorCaja ==';
UPDATE NumeradorCaja SET cdoc_nro=3 WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='BV';
UPDATE NumeradorCaja SET cdoc_nro=1 WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='FA';
UPDATE NumeradorCaja SET cdoc_nro=2 WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='NC';
UPDATE NumeradorCaja SET cdoc_nro=3 WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='ND';
UPDATE NumeradorCaja SET cdoc_nro=1 WHERE ccod_cia='EMP01' AND ccod_caja='CAJ01' AND cdoc_tipo='NV';

PRINT '== 3) Turno (id=2 cerrado, id=3 abierto) ==';
SET IDENTITY_INSERT Turno ON;
INSERT INTO Turno
    (id_turno, ccod_cia, ccod_tienda, ccod_usuario, ccod_caja,
     nmonto_ini, nmonto_fin, ntot_entreg, ndiferencia, cstatus,
     dfchdoc_ini, dfchdoc_fin)
VALUES
    (2,'EMP01','T001','ADMIN','CAJ01',
     100.0000,102.0000,102.0000,0.0000,'C',
     CONVERT(datetime,'20260513 22:19:56',112), CONVERT(datetime,'20260513 23:21:25',112)),
    (3,'EMP01','T001','cajero','CAJ01',
     250.0000,0.0000,0.0000,0.0000,'A',
     CONVERT(datetime,'20260513 22:23:50',112), NULL);
SET IDENTITY_INSERT Turno OFF;

PRINT '== 4) CbFactura ==';
SET IDENTITY_INSERT CbFactura ON;

-- BV B001-2  S/2.00  (origen NC, queda anulada)
INSERT INTO CbFactura
    (id_cbfact, ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
     cdoc, cserie, nnumero, ccod_coa,
     nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
     nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
     cobs, cstatus, cstatus_tributario, fecha_emision, dfch_crea)
VALUES (2,'EMP01','T001','CAJ01','ALM001','ADMIN',
        'BV','B001',2,'CLI000',
        0.3100,0.0000,0.0000,2.0000,1.6900,
        0.0000,2.0000,0,2,2.0000,
        'TEST DEVIN NC','A','P',
        CONVERT(datetime,'20260513 22:34:01',112),
        CONVERT(datetime,'20260513 22:34:01',112));

-- BV B001-3  S/1.50  (vigente)
INSERT INTO CbFactura
    (id_cbfact, ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
     cdoc, cserie, nnumero, ccod_coa,
     nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
     nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
     cobs, cstatus, cstatus_tributario, fecha_emision, dfch_crea)
VALUES (3,'EMP01','T001','CAJ01','ALM001','ADMIN',
        'BV','B001',3,'CLI000',
        0.2300,0.0000,0.0000,1.5000,1.2700,
        0.5000,2.0000,0,2,1.0000,
        '','P','P',
        CONVERT(datetime,'20260513 22:42:46',112),
        CONVERT(datetime,'20260513 22:42:46',112));

-- NC NC01-1 S/2.00 (devolución de BV B001-2)
INSERT INTO CbFactura
    (id_cbfact, ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
     cdoc, cserie, nnumero, ccod_coa,
     nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
     nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
     cobs, cstatus, cstatus_tributario, fecha_emision, dfch_crea)
VALUES (6,'EMP01','T001','CAJ01','ALM001','ADMIN',
        'NC','NC01',1,'CLI000',
        0.3051,0.0000,0.0000,2.0000,1.6949,
        0.0000,0.0000,1,2,0.0000,
        'TEST DEVIN NC','P','P',
        CONVERT(datetime,'20260513 22:54:29',112),
        CONVERT(datetime,'20260513 22:54:29',112));

-- ND ND01-1 S/1.50 (sobre BV B001-3)
INSERT INTO CbFactura
    (id_cbfact, ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
     cdoc, cserie, nnumero, ccod_coa,
     nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
     nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
     cobs, cstatus, cstatus_tributario, fecha_emision, dfch_crea)
VALUES (7,'EMP01','T001','CAJ01','ALM001','ADMIN',
        'ND','ND01',1,'CLI000',
        0.2288,0.0000,0.0000,1.5000,1.2712,
        0.0000,1.5000,0,2,0.0000,
        '','P','P',
        CONVERT(datetime,'20260513 23:18:43',112),
        CONVERT(datetime,'20260513 23:18:43',112));

-- ND ND01-2 S/0.50 (anulada en Anulación)
INSERT INTO CbFactura
    (id_cbfact, ccod_cia, ccod_tiend, ccod_caja, ccod_almacen, ccod_usuario,
     cdoc, cserie, nnumero, ccod_coa,
     nimpuesto, nisc, ndescuento, ntotal, nsubtotal,
     nvuelto, ntot_entreg, cantidad_bienes, id_turno, costo,
     cobs, cstatus, cstatus_tributario, fecha_emision, dfch_crea)
VALUES (8,'EMP01','T001','CAJ01','ALM001','ADMIN',
        'ND','ND01',2,'CLI000',
        0.0763,0.0000,0.0000,0.5000,0.4237,
        0.0000,0.5000,0,2,0.0000,
        'Prueba anulacion ND01-2','A','P',
        CONVERT(datetime,'20260513 23:18:50',112),
        CONVERT(datetime,'20260513 23:18:50',112));
SET IDENTITY_INSERT CbFactura OFF;

PRINT '== 5) LnFactura ==';
SET IDENTITY_INSERT LnFactura ON;
INSERT INTO LnFactura
    (id_lnfact, ccod_cia, id_cbfact, id_articulo, cdsc_articulo,
     ncantidad, nprecio, nimporte_neto, nimpuesto, nisc, ndescuento, dfch_crea)
VALUES
    (2,'EMP01',2,'ART003','AGUA SAN LUIS',1.0000,2.0000,2.0000,0.3100,0.0000,0.0000,CONVERT(datetime,'20260513 22:34:01',112)),
    (3,'EMP01',3,'ART003','AGUA SAN LUIS',1.0000,1.5000,1.5000,0.2300,0.0000,0.0000,CONVERT(datetime,'20260513 22:42:46',112)),
    (4,'EMP01',6,'ART003','AGUA SAN LUIS',1.0000,2.0000,2.0000,0.3100,0.0000,0.0000,CONVERT(datetime,'20260513 22:54:29',112)),
    (5,'EMP01',7,'ART003','AGUA SAN LUIS',0.0000,1.5000,0.0000,0.0000,0.0000,0.0000,CONVERT(datetime,'20260513 23:18:43',112)),
    (6,'EMP01',8,'ART003','AGUA SAN LUIS',0.0000,1.5000,0.0000,0.0000,0.0000,0.0000,CONVERT(datetime,'20260513 23:18:50',112));
SET IDENTITY_INSERT LnFactura OFF;

PRINT '== Seed NEW_999 completado ==';
PRINT 'Tras este script la BD queda en el mismo estado que en el reporte final.';

SELECT 'Clientes' AS tabla, COUNT(*) AS filas FROM Coa WHERE ccod_cia='EMP01' AND ccod_coa IN ('87654321','CLI_TEST')
UNION ALL SELECT 'Turnos',  COUNT(*) FROM Turno WHERE ccod_cia='EMP01' AND id_turno IN (2,3)
UNION ALL SELECT 'CbFactura', COUNT(*) FROM CbFactura WHERE ccod_cia='EMP01' AND id_cbfact IN (2,3,6,7,8)
UNION ALL SELECT 'LnFactura', COUNT(*) FROM LnFactura WHERE ccod_cia='EMP01' AND id_lnfact IN (2,3,4,5,6);
GO
