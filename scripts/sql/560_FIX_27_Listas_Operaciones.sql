/* =====================================================================
   FIX 27 — Listas de Operaciones de Almacén
   
   Problema:
   - INSERT guarda CbInventario.ctipo con el codigo del TipoOperacion
     (ej. 'IPC', 'SPV', 'SPA') que viene del dropdown.
   - SELECTs antiguos filtraban ctipo='I' o ctipo='S' (un solo caracter),
     por lo que la pestaña "Lista" siempre quedaba vacía.
   
   Solución: filtrar usando el flag de TipoOperacion (I=Ingreso, S=Salida)
   y, como respaldo, aceptar también el caracter literal 'I'/'S' por si
   alguna fila vieja quedo asi.
   
   Ejecutar en DatPos_EMP01
===================================================================== */
USE DatPos_EMP01;
GO

/* ---------------------------------------------------------------------
   1. webDatpos_consultarIngresos
--------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_consultarIngresos','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarIngresos;
GO
CREATE PROCEDURE webDatpos_consultarIngresos @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        C.id_cbinve,
        ISNULL(C.ccod_alm,'')       AS ccod_alm,
        C.dfecha,
        ISNULL(C.ctipo,'')          AS ctipo,
        ISNULL(C.vserie,'')         AS vserie,
        ISNULL(C.nnumero,0)         AS nnumero,
        ISNULL(C.vobservacion,'')   AS vobservacion,
        ISNULL(C.ntotal,0)          AS ntotal
    FROM CbInventario C
    LEFT JOIN TipoOperacion T
      ON T.ccod_cia=C.ccod_cia AND T.ccod_tipoper=C.ctipo
    WHERE C.ccod_cia=@ccod_cia
      AND ( T.ctipo_flag='I' OR C.ctipo='I' )
    ORDER BY C.dfecha DESC, C.id_cbinve DESC;
END
GO
PRINT 'OK: webDatpos_consultarIngresos';
GO

/* ---------------------------------------------------------------------
   2. webDatpos_consultarSalidas
--------------------------------------------------------------------- */
IF OBJECT_ID('webDatpos_consultarSalidas','P') IS NOT NULL DROP PROCEDURE webDatpos_consultarSalidas;
GO
CREATE PROCEDURE webDatpos_consultarSalidas @ccod_cia VARCHAR(20)
AS BEGIN SET NOCOUNT ON;
    SELECT
        C.id_cbinve,
        ISNULL(C.ccod_alm,'')       AS ccod_alm,
        C.dfecha,
        ISNULL(C.ctipo,'')          AS ctipo,
        ISNULL(C.vserie,'')         AS vserie,
        ISNULL(C.nnumero,0)         AS nnumero,
        ISNULL(C.vobservacion,'')   AS vobservacion,
        ISNULL(C.ntotal,0)          AS ntotal
    FROM CbInventario C
    LEFT JOIN TipoOperacion T
      ON T.ccod_cia=C.ccod_cia AND T.ccod_tipoper=C.ctipo
    WHERE C.ccod_cia=@ccod_cia
      AND ( T.ctipo_flag='S' OR C.ctipo='S' )
    ORDER BY C.dfecha DESC, C.id_cbinve DESC;
END
GO
PRINT 'OK: webDatpos_consultarSalidas';
GO

/* ---------------------------------------------------------------------
   3. Verificacion rapida
--------------------------------------------------------------------- */
PRINT '--- Salidas en BD ---';
EXEC webDatpos_consultarSalidas 'EMP01';
PRINT '--- Ingresos en BD ---';
EXEC webDatpos_consultarIngresos 'EMP01';
GO
