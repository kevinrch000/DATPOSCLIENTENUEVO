/* NEW_1001 — Test data extra para ReporteTributario.php
   ===================================================================
   El seed NEW_999 crea las facturas con cstatus_tributario='P', pero
   el dropdown "Estado Tributario" de ReporteTributario.php usa los
   codigos 1/4/5/6/8 (Pendiente/Aceptado/Aceptado con obs/Error/Anulado).

   Este script actualiza el cstatus_tributario de las facturas de
   prueba para que el filtro por Estado pueda probarse manualmente:

        id_cbfact   cdoc-serie-nro     cstatus_tributario
        ---------   ----------------   ------------------
        2           BV B001-2          4   Aceptado
        3           BV B001-3          5   Aceptado con observaciones
        6           NC NC01-1          1   Pendiente de envio
        7           ND ND01-1          6   Error
        8           ND ND01-2          8   Anulado

   Idempotente: se puede correr tantas veces como sea necesario.
   Pre-requisito: haber corrido NEW_999_SEED_TestData_Ventas.sql.
   =================================================================== */

USE DatPos_EMP01;
GO

SET LANGUAGE us_english;
GO

PRINT '== Actualizando cstatus_tributario en facturas de prueba ==';

UPDATE CbFactura SET cstatus_tributario='4' WHERE ccod_cia='EMP01' AND id_cbfact=2;
UPDATE CbFactura SET cstatus_tributario='5' WHERE ccod_cia='EMP01' AND id_cbfact=3;
UPDATE CbFactura SET cstatus_tributario='1' WHERE ccod_cia='EMP01' AND id_cbfact=6;
UPDATE CbFactura SET cstatus_tributario='6' WHERE ccod_cia='EMP01' AND id_cbfact=7;
UPDATE CbFactura SET cstatus_tributario='8' WHERE ccod_cia='EMP01' AND id_cbfact=8;

PRINT '== Verificacion ==';
SELECT id_cbfact, cdoc, cserie, nnumero, cstatus, cstatus_tributario
FROM CbFactura
WHERE ccod_cia = 'EMP01' AND id_cbfact IN (2,3,6,7,8)
ORDER BY id_cbfact;

PRINT '== NEW_1001 completado ==';
GO
