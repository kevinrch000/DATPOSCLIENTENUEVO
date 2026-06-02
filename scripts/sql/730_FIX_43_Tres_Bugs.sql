/* =====================================================================
   FIX 43 — Tres bugs: FILTROS, Ingresos ccod_tienda, Facturación ccod_coa
   =====================================================================
   BUGS CORREGIDOS
   ---------------
   BUG 1 (PHP/JS): FILTROS dropdown vacío en Familias, Articulos, UnidadMedida
     → Solución: Se agregó FilterStatus(n) a Comun.js y los <li> items en
       las páginas Familias.php, Articulos.php, UnidadMedida.php y se
       corrigieron las etiquetas en Almacenes.php (Abiertos→Activos, etc.)
     → Este script SQL no necesita cambios para este bug.

   BUG 2 (PHP): Ingresos Directos → Guardar: ccod_tienda="" viola FK
     → Causa: JS envía ccod_tienda:"" (cadena vacía). PHP usaba ??
       que sólo hace fallback con NULL, no con "". La tabla CbInventario
       tiene FK (ccod_cia, ccod_tienda) → Tiendas, por lo que "" viola la FK.
     → Solución PHP: DAIngreso.php cambia ?? a ?: para ccod_tienda
       (ya aplicado en los 3 métodos: InsertarInventario, EditarInventario,
       InsertarInventarioDetalle)
     → No requiere cambio SQL.

   BUG 3 (PHP): Facturación → Cobrar: "Error insertando cabecera" cuando ccod_coa=""
     → Causa: Para Boleta sin cliente (o total < 700), el JS envía
       ccod_coa:"" (cadena vacía). El SP sp_insertarmovimientocabeceranew
       intenta insertar en CbFactura con ccod_coa='' pero la FK
       FK_CbFact_Coa requiere (ccod_cia, ccod_coa) en Coa. No existe
       ningún Coa con ccod_coa=''.
     → Solución PHP: DAMovimientoCabecera.php convierte '' a NULL antes
       de pasar el parámetro al SP. CbFactura.ccod_coa es NULLABLE, así
       que NULL es válido y el FK no aplica a NULLs.
     → No requiere cambio SQL en el SP porque la corrección es PHP-side.

   BUG 4 (HTML): Facturación → HTML duplicado en Facturacion.php
     → Causa: El archivo tenía dos copias completas de:
         modalTarjetaEditar, modalResumenVenta, MenuFavoritos,
         modalConsultarClientes. La primera copia incompleta del
         modalTarjetaNuevo quedó como código huérfano visible en la
         página con un botón "Confirmar" (id=btn_confirmartarjeta)
         permanentemente deshabilitado. El usuario confundía este botón
         con el de pago con tarjeta.
     → Solución HTML: Se eliminaron las líneas duplicadas (1040-1216
       en la versión previa al fix) dejando solo la primera copia de
       cada modal.
     → No requiere cambio SQL.

   NOTA: No hay cambios SQL en este script. Los fixes son todos PHP/HTML/JS.
   Este archivo existe para documentar los fixes en el historial de scripts.
   =====================================================================
*/
PRINT '=== FIX 43: Tres bugs (FILTROS / Ingresos ccod_tienda / Facturacion ccod_coa) ===';
PRINT 'Este fix es PHP/HTML/JS solamente - no requiere cambios en la base de datos.';
PRINT '  BUG 1 FIXED: FilterStatus() en Comun.js + items en Familias/Articulos/UnidadMedida/Almacenes';
PRINT '  BUG 2 FIXED: DAIngreso.php - ccod_tienda "" fallback a sesion via ?: operator';
PRINT '  BUG 3 FIXED: DAMovimientoCabecera.php - ccod_coa "" -> NULL (evita FK_CbFact_Coa)';
PRINT '  BUG 4 FIXED: Facturacion.php - eliminados 177 lineas de HTML duplicado';
PRINT '=== FIX 43 OK ===';
GO
