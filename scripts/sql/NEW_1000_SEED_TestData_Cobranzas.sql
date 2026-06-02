-- ============================================================
-- NEW_1000_SEED_TestData_Cobranzas.sql
-- ============================================================
-- Estado : NEW
-- Motivo : NEW_999 dejaba la BD con CbFactura/LnFactura para
--          validar Consulta Documento, pero las tablas
--          CbCobranza/LnCobranza quedaban vacias, por lo cual
--          la pantalla Consulta Formas de Pago no podia
--          probarse.
--          Este seed crea cobranzas asociadas a las facturas
--          insertadas por NEW_999 (id_cbfact 2,3,6,7,8) con
--          varios tipos de pago para que las DataTables de
--          ConsultaFormaPago.js muestren todas las columnas:
--              - EFECTIVO  (id 2)
--              - VISA      (Tarjeta)            (id 3)
--              - NC + EFECTIVO mixto            (id 6)
--              - EFECTIVO (ND)                  (id 7)
--              - EFECTIVO (anulada)             (id 8)
-- Orden  : Ejecutar despues de NEW_999.
-- ============================================================
USE DatPos_EMP01;
GO

SET DATEFORMAT ymd;
SET LANGUAGE us_english;
SET NOCOUNT ON;
GO

PRINT '== Limpieza idempotente de cobranzas de prueba (v2 cascade) ==';

DECLARE @cia VARCHAR(20) = 'EMP01';

-- Ids hardcoded del test (los que vamos a re-insertar)
DECLARE @factIds TABLE (id INT PRIMARY KEY);
INSERT INTO @factIds VALUES (2),(3),(6),(7),(8);

DECLARE @cobrIdsTest TABLE (id INT PRIMARY KEY);
INSERT INTO @cobrIdsTest VALUES (2),(3),(4),(5),(6);

DECLARE @lnCobrIdsTest TABLE (id INT PRIMARY KEY);
INSERT INTO @lnCobrIdsTest VALUES (2),(3),(4),(5),(6),(7);

-- Recolectar TODOS los id_cbcajac existentes que apunten a un
-- id_cbfact del test O cuyo id_cbcajac este en la lista hardcoded
-- (de corridas previas, parciales o fallidas).
DECLARE @cobrIds TABLE (id INT PRIMARY KEY);
INSERT INTO @cobrIds (id)
SELECT DISTINCT id_cbcajac
FROM CbCobranza
WHERE ccod_cia=@cia
  AND (id_cbfact   IN (SELECT id FROM @factIds)
       OR id_cbcajac IN (SELECT id FROM @cobrIdsTest));

-- 1) Borrar TODAS las LnCobranza que puedan estar referenciando
--    cualquier CbCobranza o CbFactura del test (cualquier ruta).
--    Esto evita el FK_LnCobr_CbCobr cuando se borran las CbCobranza
--    en el siguiente paso.
DELETE FROM LnCobranza
WHERE ccod_cia=@cia
  AND (id_cbcajac IN (SELECT id FROM @cobrIds)
       OR id_cbcajac IN (SELECT id FROM @cobrIdsTest)
       OR id_cbfact  IN (SELECT id FROM @factIds)
       OR id_lncajac IN (SELECT id FROM @lnCobrIdsTest));

-- 2) Ahora ya es seguro borrar las CbCobranza, por id_cbfact y por
--    id_cbcajac (en una sola operacion) para cubrir ambas rutas.
DELETE FROM CbCobranza
WHERE ccod_cia=@cia
  AND (id_cbfact   IN (SELECT id FROM @factIds)
       OR id_cbcajac IN (SELECT id FROM @cobrIds)
       OR id_cbcajac IN (SELECT id FROM @cobrIdsTest));

PRINT '== 1) CbCobranza (cabeceras) ==';
SET IDENTITY_INSERT CbCobranza ON;

-- Cobranza id=2 -> BV B001-2 (id_cbfact=2)  S/2.00  Efectivo
INSERT INTO CbCobranza
    (id_cbcajac, ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja,
     ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea, cnom_tarje)
VALUES
    (2,'EMP01', 2, 2, 'T001', 'CAJ01',
     'ADMIN', 2.0000, 2.0000, 0.0000,
     CONVERT(datetime,'20260513 22:34:05',112), 'EFECTIVO');

-- Cobranza id=3 -> BV B001-3 (id_cbfact=3) S/1.50  Tarjeta VISA
INSERT INTO CbCobranza
    (id_cbcajac, ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja,
     ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea, cnom_tarje)
VALUES
    (3,'EMP01', 3, 2, 'T001', 'CAJ01',
     'ADMIN', 1.5000, 1.5000, 0.0000,
     CONVERT(datetime,'20260513 22:42:50',112), 'VISA');

-- Cobranza id=4 -> NC NC01-1 (id_cbfact=6)  S/2.00 (devolucion)
INSERT INTO CbCobranza
    (id_cbcajac, ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja,
     ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea, cnom_tarje)
VALUES
    (4,'EMP01', 6, 2, 'T001', 'CAJ01',
     'ADMIN', 2.0000, 2.0000, 0.0000,
     CONVERT(datetime,'20260513 22:54:33',112), 'NOTA CREDITO');

-- Cobranza id=5 -> ND ND01-1 (id_cbfact=7)  S/1.50  Efectivo
INSERT INTO CbCobranza
    (id_cbcajac, ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja,
     ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea, cnom_tarje)
VALUES
    (5,'EMP01', 7, 2, 'T001', 'CAJ01',
     'ADMIN', 1.5000, 1.5000, 0.0000,
     CONVERT(datetime,'20260513 23:18:46',112), 'EFECTIVO');

-- Cobranza id=6 -> ND ND01-2 (id_cbfact=8)  S/0.50  Efectivo
INSERT INTO CbCobranza
    (id_cbcajac, ccod_cia, id_cbfact, id_turno, ccod_tiend, ccod_caja,
     ccod_usuario, ntotal, ntot_entreg, nvuelto, dfch_crea, cnom_tarje)
VALUES
    (6,'EMP01', 8, 2, 'T001', 'CAJ01',
     'ADMIN', 0.5000, 0.5000, 0.0000,
     CONVERT(datetime,'20260513 23:18:55',112), 'EFECTIVO');

SET IDENTITY_INSERT CbCobranza OFF;

PRINT '== 2) LnCobranza (detalles) ==';
SET IDENTITY_INSERT LnCobranza ON;

-- BV B001-2 -> 1 linea EFECTIVO 2.00
INSERT INTO LnCobranza
    (id_lncajac, ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto,
     cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea)
VALUES
    (2,'EMP01', 2, 2, 'T001', 2.0000,
     '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01',
     CONVERT(datetime,'20260513 22:34:05',112));

-- BV B001-3 -> 1 linea VISA 1.50 (operacion + numero tarjeta)
INSERT INTO LnCobranza
    (id_lncajac, ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto,
     cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea)
VALUES
    (3,'EMP01', 3, 3, 'T001', 1.5000,
     'OP123456', '4111-XXXX-XXXX-1111', 'VISA', NULL, 'ADMIN', 'CAJ01',
     CONVERT(datetime,'20260513 22:42:50',112));

-- NC NC01-1 -> 1 linea (referencia a CbFactura origen id 2 mediante id_cbfactNC)
INSERT INTO LnCobranza
    (id_lncajac, ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto,
     cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea)
VALUES
    (4,'EMP01', 4, 6, 'T001', 2.0000,
     '', '', 'NOTA CREDITO', 2, 'ADMIN', 'CAJ01',
     CONVERT(datetime,'20260513 22:54:33',112));

-- ND ND01-1 -> 1 linea EFECTIVO 1.50
INSERT INTO LnCobranza
    (id_lncajac, ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto,
     cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea)
VALUES
    (5,'EMP01', 5, 7, 'T001', 1.5000,
     '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01',
     CONVERT(datetime,'20260513 23:18:46',112));

-- ND ND01-2 -> 1 linea EFECTIVO 0.50
INSERT INTO LnCobranza
    (id_lncajac, ccod_cia, id_cbcajac, id_cbfact, ccod_tiend, nmonto,
     cnum_opera, cnum_tarje, cnom_tarje, id_cbfactNC, ccod_usuario, ccod_caja, dfch_crea)
VALUES
    (6,'EMP01', 6, 8, 'T001', 0.5000,
     '', '', 'EFECTIVO', NULL, 'ADMIN', 'CAJ01',
     CONVERT(datetime,'20260513 23:18:55',112));

SET IDENTITY_INSERT LnCobranza OFF;

PRINT '== Seed NEW_1000 completado ==';

SELECT 'CbCobranza' AS tabla, COUNT(*) AS filas FROM CbCobranza WHERE ccod_cia='EMP01' AND id_cbcajac IN (2,3,4,5,6)
UNION ALL SELECT 'LnCobranza', COUNT(*) FROM LnCobranza WHERE ccod_cia='EMP01' AND id_lncajac IN (2,3,4,5,6);
GO
