/* =========================================================================
   SEED_01 — Datos de prueba: Alerta de Stock
   =========================================================================

   ¿POR QUÉ LA ALERTA SALE VACÍA?
   ─────────────────────────────────────────────────────────────────────────
   La Alerta de Stock muestra artículos que cumplen DOS condiciones:
     1. El artículo tiene nstock_min > 0  (stock mínimo configurado)
     2. La cantidad actual en el almacén <= nstock_min

   El problema: cuando creas artículos en el sistema, nstock_min = 0 por
   defecto. El SP filtra `WHERE nstock_min > 0`, así que artículos con
   mínimo = 0 NUNCA aparecen aunque su stock sea también 0.

   SOLUCIÓN REAL: ir a Tablas → Artículos, editar cada artículo y asignar
   un valor de "Stock Mínimo" (ej. 5 o 10).

   SOLUCIÓN DE PRUEBA: este script hace eso automáticamente para 5
   artículos de tu BD, para que puedas ver la pantalla funcionando.

   REQUISITO: ejecutar FIX_55 antes de este script.
   Ejecutar en DatPos_EMP01
   ========================================================================= */

USE DatPos_EMP01;
GO

PRINT '== SEED_01: Alerta de Stock — datos de prueba ==';
PRINT '';
PRINT 'EXPLICACION:';
PRINT '  La alerta solo muestra artículos con nstock_min > 0 Y stock_actual <= nstock_min.';
PRINT '  En una BD nueva todos los artículos tienen nstock_min = 0 → lista vacía.';
PRINT '  Este script pone nstock_min=10 y stock=3 en 5 artículos para probarlo.';
PRINT '';

/* ── PASO 1: Ver estado actual de la BD ───────────────────────────────── */
PRINT '--- Estado actual (primeros 10 artículos activos) ---';
SELECT TOP 10
    A.ccod_articulo,
    A.cdsc_articulo,
    A.nstock_min,
    A.nstock_max,
    ISNULL(S.ncantidad, 0) AS stock_actual,
    ISNULL(S.ccod_alm, 'SIN STOCK') AS almacen
FROM Articulos A
LEFT JOIN Stock S
    ON S.ccod_articulo = A.ccod_articulo
    AND S.ccod_cia = A.ccod_cia
WHERE A.ccod_cia = 'EMP01'
  AND A.cstatus  = 'A'
ORDER BY A.cdsc_articulo;
GO

PRINT '';
PRINT '--- ¿Cuántos artículos tienen nstock_min > 0 ahora? ---';
SELECT COUNT(*) AS articulos_con_stock_minimo_configurado
FROM Articulos
WHERE ccod_cia = 'EMP01'
  AND cstatus  = 'A'
  AND nstock_min > 0;
GO

/* ── PASO 2: Asignar nstock_min=10 y nstock_max=50 a los primeros 5 ──── */
PRINT '';
PRINT '--- Asignando nstock_min=10, nstock_max=50 a 5 artículos ---';

UPDATE Articulos
SET nstock_min = 10,
    nstock_max = 50
WHERE ccod_cia = 'EMP01'
  AND cstatus  = 'A'
  AND id_articulo IN (
      SELECT TOP 5 id_articulo
      FROM Articulos
      WHERE ccod_cia = 'EMP01'
        AND cstatus  = 'A'
      ORDER BY cdsc_articulo
  );

PRINT CAST(@@ROWCOUNT AS VARCHAR) + ' artículos actualizados con nstock_min=10 y nstock_max=50.';
GO

/* ── PASO 3: Poner stock = 3 en ALM001 (< mínimo de 10) ────────────────
   Usamos MERGE para INSERT si no existe fila, UPDATE si ya existe       */
PRINT '';
PRINT '--- Poniendo stock=3 en ALM001 para esos artículos ---';

-- Primero obtener el código real del almacén (puede no ser ALM001)
DECLARE @primer_alm VARCHAR(20);
SELECT TOP 1 @primer_alm = ccod_alm
FROM Almacenes
WHERE ccod_cia = 'EMP01'
ORDER BY ccod_alm;

IF @primer_alm IS NULL
BEGIN
    PRINT 'ERROR: No hay almacenes en la BD. Crea al menos uno en Administración → Almacenes.';
    RETURN;
END

PRINT 'Usando almacén: ' + @primer_alm;

MERGE Stock AS tgt
USING (
    SELECT TOP 5
        'EMP01'                     AS ccod_cia,
        A.ccod_articulo,
        @primer_alm                 AS ccod_alm,
        CAST(3 AS DECIMAL(18,4))    AS ncantidad,
        CAST(0 AS DECIMAL(18,4))    AS ncosto
    FROM Articulos A
    WHERE A.ccod_cia  = 'EMP01'
      AND A.cstatus   = 'A'
      AND A.nstock_min = 10
    ORDER BY A.cdsc_articulo
) AS src
ON  tgt.ccod_cia      = src.ccod_cia
AND tgt.ccod_articulo = src.ccod_articulo
AND tgt.ccod_alm      = src.ccod_alm
WHEN MATCHED THEN
    UPDATE SET tgt.ncantidad = src.ncantidad
WHEN NOT MATCHED THEN
    INSERT (ccod_cia, ccod_articulo, ccod_alm, ncantidad, ncosto)
    VALUES (src.ccod_cia, src.ccod_articulo, src.ccod_alm,
            src.ncantidad, src.ncosto);

PRINT CAST(@@ROWCOUNT AS VARCHAR) + ' filas de Stock creadas/actualizadas (ncantidad=3, < mínimo=10).';
GO

/* ── PASO 4: Verificar que el SP ahora devuelve resultados ───────────── */
PRINT '';
PRINT '--- Verificación: ejecutando SP sin filtros (debe devolver los 5 artículos) ---';
EXEC webDatpos_ConsultaStockMinimo
    @ccod_cia      = 'EMP01',
    @ccod_alm      = '',
    @ccod_lin      = '',
    @ccod_articulo = '',
    @nstock_min    = '';
GO

PRINT '';
PRINT '=================================================================';
PRINT 'Si ves filas arriba → el SP funciona correctamente.';
PRINT 'Ahora ve a: Almacén → Consultas → Alerta de Stock';
PRINT '  - Selecciona "Todos" en Almacén y Familia';
PRINT '  - Haz clic en Ejecutar';
PRINT '  - Deberías ver los 5 artículos con stock 3 / mínimo 10';
PRINT '';
PRINT 'PARA PRODUCCIÓN: en Tablas → Artículos, edita cada artículo';
PRINT 'y asígnale un Stock Mínimo real (ej. 5, 10, 20).';
PRINT '=================================================================';
GO
