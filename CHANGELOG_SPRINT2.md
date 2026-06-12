# DATPOS — Changelog Sprint 2 (26–27/05/2026)

> **Para el técnico que instala esto en otro PC:**
> Lee la sección de Scripts SQL antes de arrancar el servidor.
> Los scripts son **idempotentes** (DROP + CREATE): se pueden ejecutar varias veces sin problema.

---

## Orden de ejecución de Scripts SQL

> Ejecutar en **SQL Server Management Studio** contra `DatPos_EMP01`, **en este orden exacto**.

### Obligatorios — sin estos, hay errores de SP o resultados vacíos

| # | Archivo | Qué resuelve |
|---|---------|-------------|
| 1 | `scripts/sql/MODIFY_920_FIX_69_SPs_Faltantes.sql` | Crea `sp_consultastockminimoprincipal`, `sp_cargararticulosolobienes`, `sp_cargaroperacionesclientes` |
| 2 | `scripts/sql/MODIFY_924_FIX_73_DataTablesAndKardex.sql` | Agrega `cobser_variante` a `webDatpos_ConsultasVentaPricipal`; crea `sp_kardexprincipal` |
| 3 | `scripts/sql/MODIFY_930_FIX_74_CamposVaciosListaDatos.sql` | Crea `sp_consultararticulospricipal` (catálogo artículos con 7 cols) y otros SPs de listas/datos |
| 4 | `migrations/FIX_52_SP_ArticuloPrecio_VarcharCast.sql` | Recrea `sp_consultararticuloprecio` — buscaba por `id_articulo INT` causando error de conversión con códigos alfanuméricos como "ART003" |
| 5 | `migrations/FIX_53_SPs_Errores_Servidor_23May2026.sql` | Recrea `sp_consultararticuloprecio` (mismo fix) + `webDatpos_verificarAccesos` con `@ccod_cia` opcional |
| 6 | `migrations/FIX_54_SP_ConsultaArticulos.sql` | Recrea `sp_consultararticulospricipal` con normalización interna de `%%%` → `''` |
| 7 | `migrations/FIX_55_SP_StockMinimo_ListaPrecio_MasVendidos.sql` | Recrea 3 SPs con soporte de `%%%`/`''` como "Todos": `webDatpos_ConsultaStockMinimo`, `webDatpos_ConsultaListPrecioPricipal`, `webDatpos_ConsultaArticulosMasVendidos` |

### Opcional — seed de datos de prueba

| # | Archivo | Qué hace |
|---|---------|----------|
| 8 | `migrations/SEED_01_StockMinimo_Test.sql` | Pone `nstock_min=10` en 5 artículos y `stock=2` (< mínimo) para que aparezcan en Alerta de Stock. Ejecutar solo si la alerta devuelve lista vacía y quieres verificar que el SP funciona. |

> **Nota sobre "Alerta de Stock vacía":** Si después de ejecutar FIX_55 la alerta sigue sin mostrar artículos, es porque en tu BD todos los artículos tienen `nstock_min = 0` (valor por defecto). El SP solo muestra artículos donde `stock_actual ≤ nstock_min` Y `nstock_min > 0`. Ejecuta el SEED_01 para crear datos de prueba, o ve a Tablas → Artículos y asigna un stock mínimo manualmente.

---

## Archivos PHP / JS modificados en este sprint

### Autenticación y sesión

| Archivo | Cambio |
|---------|--------|
| `includes/auth.php` | Al reconstruir sesión desde JWT: si `cnomser`/`cnombre_bd` vacíos, usar `DATPOS_TENANT_SERVER`/`DATPOS_TENANT_DATABASE` (variables de entorno) como fallback |
| `pages/migcliente/LogOn.php` | Al hacer login: si el SP Admin devuelve `cnombre_bd`/`cnomser` vacíos, completar desde env vars. Evita el modal "sesión expirada" al usar Facturación |

### APIs

| Archivo | Cambio |
|---------|--------|
| `api/facturacion_api.php` | **Fix crítico sesión:** completa `cnomser`/`cnombre_bd` desde env vars antes de devolver `-1`. `mapPreciosResponse()` devuelve `[]` en vez de `false` cuando el SP no encuentra artículo — el JS ya no confunde "no encontrado" con "sesión expirada" |
| `api/aperturacaja_api.php` | Mismo fix de sesión incompleta |
| `api/transferencia_api.php` | `ConsultarArticulosSalida`: índices corregidos — SP devuelve 5 cols, `linea`(familia) de col[2] y costo de col[4] |
| `api/notacredito_api.php` | `ConsultarNotaCredito`: mapeo de campos del SP a los nombres que espera `CompletarCampos` en el JS |
| `api/consultadocumento_api.php` | **Fix `%%%`**: 6 endpoints ahora convierten `'%%%'`→`''` y `'1'`/`'0'`→`'A'`/`'I'` antes de llamar al SP: `ConsultarArticulosPricipal`, `ConsultaStockMinimoPrincipal`, `ConsultaArticulosMasVendidos`, `ConsultaListPrecioPricipal`, `ConsultasAlmacenPrincipal` (ya estaba), `CargarArticuloSaldo` (ya estaba) |

### JavaScript

| Archivo | Cambio |
|---------|--------|
| `assets-patch/modal_fix.js` | **Fix crítico CRUD:** eliminada sección 7 que forzaba tab `#Lista` al entrar — causaba que los módulos CRUD (Almacenes, Artículos, etc.) preseleccionaran el primer registro, dejaran el botón Nuevo deshabilitado y rompieran páginas como ConfigGeneral (`null value` en `Deshacer`) |
| `assets/Javascript/Almacen1.js` | `NuevoModal()` refactorizado: función `_poblarTipDocSelect()` + listener `show.bs.modal` para evitar race condition con Bootstrap que dejaba el select "Tipo Documento" vacío |
| `assets/Javascript/AperturaCaja.js` | Estado `A`/`C` → "Abierto"/"Cerrado" en ambas DataTables |
| `assets/Javascript/CierreCaja.js` | Estado `A`/`C` → "Abierto"/"Cerrado"; `CargarTienda` val/text corregidos (estaban invertidos); funciones `Nuevo/Desabilitar/Deshacer/Editar` definidas |
| `assets/Javascript/Transferencias.js` | Número origen/destino ahora se llena al seleccionar almacén (`obj[0].nnumero`) |
| `assets/Javascript/GuiaRemision.js` | RUC del transportista (`IdTransportista`) se asigna en `CompletarCampos` al hacer doble clic en Lista |
| `assets/Javascript/ConsultaArticulos.js` | Validaciones `== null` → `!value` — los selects nunca son `null` |
| `assets/Javascript/ConsultaListPrecio.js` | Función `CargarListPrecio` duplicada eliminada (la versión legacy que devolvía vacío) |
| `assets/Javascript/NotaCredito.js` | Botón Nuevo habilitado al cargar; `CompletarCampos` ahora usa los campos correctos del SP |
| `assets/Javascript/NotaDebito.js` | Botón Nuevo habilitado al cargar |
| `assets/Javascript/FacturaListaPrecio3.js` | Fix Enter en "Añadir artículo": `keydown`+`keypress` con `preventDefault` |
| `assets/Javascript/Filtros.js` | `CargarUnidadMedida()` agrega opción "Todos" (`%%%`) al principio |

### Parches (assets-patch)

| Archivo | Cambio |
|---------|--------|
| `assets-patch/operaciones_patch.js` | Delegación de eventos para radio buttons en `#table_Articulos` (funciona aunque Bootstrap mueva el modal a `<body>`) |
| `assets-patch/datpos-responsive.css` | Z-index SweetAlert → `1,100,000,000` (sobre modales); **Sección 17 nueva**: sidebar 270px, `height:auto` en `li`, `text-transform:none`, `white-space:normal` — el texto largo ya no se corta |

### Layout / HTML

| Archivo | Cambio |
|---------|--------|
| `includes/layout_master.php` | Menú lateral: `collapse out` → `collapse in` + JS que fuerza `.addClass('in')` al cargar |
| `pages/Ventas/Precios.php` | Filtros Lista: opciones Activos/Inactivos/Todos (estaban vacíos) |
| `pages/Consultas/ConsultaArticulos.php` | Rediseño con `dp-filters` + encabezado moderno; selects con opción "Todos" por defecto |
| `pages/Consultas/ConsultaArticulosMasVendidos.php` | Rediseño con `dp-filters` + encabezado moderno |
| `pages/Consultas/MargenUtilidad.php` | Anchos de `colgroup` corregidos (sumaban 86%, ahora 100%) |
| `pages/Administracion/Tiendas.php` | Agregada navbar con filtros Activos/Inactivos/Todos (no tenía) |
| `pages/Administracion/Cajas.php` | Filtros Activos/Inactivos/Todos (dropdown estaba vacío) |
| `pages/Administracion/Roles.php` | Filtros Activos/Inactivos/Todos (dropdown estaba vacío) |
| `pages/Administracion/Usuarios.php` | Filtros Activos/Inactivos/Todos (dropdown estaba vacío) |

---

## Migrations creados en este sprint

| Archivo | Descripción |
|---------|-------------|
| `migrations/FIX_52_SP_ArticuloPrecio_VarcharCast.sql` | `sp_consultararticuloprecio` sin `CAST(@codigo AS INT)` forzado; costo desde `Stock.ncosto` |
| `migrations/FIX_53_SPs_Errores_Servidor_23May2026.sql` | Consolida FIX_52 + `webDatpos_verificarAccesos` con `@ccod_cia VARCHAR(20) = ''` |
| `migrations/FIX_54_SP_ConsultaArticulos.sql` | `sp_consultararticulospricipal` con normalización `%%%`→`''` y `1/0`→`A/I` interna |
| `migrations/FIX_55_SP_StockMinimo_ListaPrecio_MasVendidos.sql` | Tres SPs con soporte `%%%`: `webDatpos_ConsultaStockMinimo` (también soporta `@ccod_alm=''` → todos), `webDatpos_ConsultaListPrecioPricipal`, `webDatpos_ConsultaArticulosMasVendidos` (usa `L.id_articulo` correcto) |
| `migrations/SEED_01_StockMinimo_Test.sql` | Datos de prueba: 5 artículos con `nstock_min=10` y `stock=2` para verificar Alerta de Stock |

---

## Errores del servidor — estado final

| # | Error | Estado |
|---|-------|--------|
| E1 | `sp_consultastockminimoprincipal` no existe | ✅ Script #1 |
| E2 | `sp_cargararticulosolobienes` no existe | ✅ Script #1 |
| E3 | `sp_consultararticuloprecio` — CAST varchar→int | ✅ Scripts #4 y #5 |
| E4 | `webDatpos_verificarAccesos` sin `@ccod_cia` | ✅ Script #5 |
| E5 | `ConsultaArticulos` devuelve `[]` con filtros `%%%` | ✅ Scripts #6 + API PHP |
| E6 | `AlertaStock` devuelve `[]` con `ccod_alm=%%%` | ✅ Script #7 + API PHP |
| E7 | `ListaPrecios` devuelve `[]` con filtros `%%%` | ✅ Script #7 + API PHP |
| E8 | `ArticulosMasVendidos` devuelve `[]` con `ccod_lin=%%%` | ✅ Script #7 (columna `id_articulo` correcta) |

---

## Bugs de código — estado final

| Módulo | Bug | Estado |
|--------|-----|--------|
| Todos los CRUDs | Entraban con primer registro preseleccionado, botón Nuevo deshabilitado | ✅ `modal_fix.js` sección 7 eliminada |
| ConfigGeneral | Error `null.value` en `Deshacer` al entrar | ✅ Mismo fix |
| Facturación | Enter en "Añadir artículo" → modal "sesión expirada" | ✅ API + JS |
| Facturación | `sp_consultararticuloprecio` falla con código alfanumérico | ✅ Scripts #4/#5 |
| Sidebar | Texto largo se cortaba; íconos descentrados | ✅ `datpos-responsive.css` sección 17 |
| Sidebar | Menú colapsado al entrar | ✅ `layout_master.php` |
| Administración | Filtros de Lista vacíos (Tiendas/Cajas/Roles/Usuarios) | ✅ HTML de cada página |
| AperturaCaja / CierreCaja | Estado `A`/`C` en lista | ✅ JS |
| Transferencias | Número origen/destino no se llenaba | ✅ JS |
| Transferencias | Familia vacía en modal artículos | ✅ API transferencia |
| GuiaRemision | RUC transportista vacío al editar | ✅ JS |

---

## Notas para el técnico

1. **Backup primero** — antes de ejecutar cualquier script SQL.
2. Los scripts son idempotentes: `DROP + CREATE` — seguros de re-ejecutar.
3. Si el servidor usa **variables de entorno** (`DATPOS_TENANT_SERVER`, `DATPOS_TENANT_DATABASE`), el sistema funciona aunque `cnomser`/`cnombre_bd` estén vacíos en la BD Admin.
4. Si usas Apache/IIS con **OPcache**, reiniciar el servicio PHP después de aplicar cambios `.php`.
5. **Alerta de Stock vacía** = los artículos en BD tienen `nstock_min = 0`. Ejecuta SEED_01 para datos de prueba, o asigna `nstock_min` manualmente en Tablas → Artículos.
