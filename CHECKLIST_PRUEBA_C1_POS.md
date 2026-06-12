# Checklist de prueba — Sprint C.1 POS (FacturaListaPrecio)

Fecha: 2026-04-25

URL a abrir: `http://localhost:PUERTO/FacturaListaPrecio.aspx` (si tienes `router.php` activo)
o directo: `http://localhost:PUERTO/pages/Ventas/FacturaListaPrecio.php`

> Antes de empezar: ten **F12 → Console** abierta para capturar errores JS.
> Si algo falla, copia el mensaje de error literal y pásamelo (incluye archivo:línea).

---

## 0. Pre-requisitos

- [ ] PHP corriendo (`php -S localhost:PUERTO router.php` desde `DatPOS_PHP/`)
- [ ] SQL Server activo y la BD del tenant restaurada
- [ ] Estás logueado en el sistema (`/migcliente/LogOn.php`) y el usuario tiene:
  - `ccod_empresa` definido
  - `id_rol` con permiso al menú **1035** (Facturación) — ojo: si el rol no tiene este menú, te redirigirá a `SinAcceso.php`. Esa es la nueva validación que acabo de agregar (paridad con el VB original).
- [ ] Hay al menos **una caja con turno abierto** del usuario logueado
- [ ] Hay al menos **una lista de precios activa** (cstatus='A')
- [ ] Hay al menos **un artículo activo** con precio en esa lista

---

## 1. Carga inicial de la página

- [ ] La página carga sin pantalla en blanco
- [ ] **Console limpia**: 0 errores rojos (warnings amarillos OK)
- [ ] Se ve el layout: sidebar a la izquierda + topbar arriba con nombre tienda/usuario
- [ ] Tabs visibles: `Factura` | `Cobranza`
- [ ] El dropdown `Lista de Precios` (`#ddl_lpn`) tiene opciones cargadas
  - Si dice "Sin opciones" o vacío → me reportas y reviso el SP de listas de precios
- [ ] El campo `Buscar Artículos` (`#tb_articulo`) está visible
- [ ] La sección **Favoritos** muestra el botón de Favoritos y/o las categorías

### Posibles errores acá:
- 🔴 `Cannot redeclare jsonResponse()` → algún archivo carga helpers.php dos veces. Reportar.
- 🔴 `Class 'DAEmpresa' not found` → falta `require_once`. Reportar.
- 🔴 `Call to undefined function basePath()` → helpers.php no se cargó. Reportar.
- 🔴 Redirect a `SinAcceso.php` → tu usuario no tiene rol con menú 1035. Da otro usuario o ajusta el rol.

---

## 2. Validación de turno

Debe ocurrir **una de dos cosas**:

- [ ] **Caso A — Tienes turno abierto**: la página carga normal, sin alertas.
- [ ] **Caso B — Sin turno**: aparece alert/popup `MensajeTurno()` o similar.

Si aparece error de "validación de facturación", el SP `sp_validarfacturacion` devolvió algo distinto a 'OK'. Pásame el mensaje exacto.

---

## 3. Búsqueda y agregado de artículo

- [ ] Escribir en `#tb_articulo` el nombre o código de un artículo conocido
- [ ] Aparece autocomplete con sugerencias (llama a `ConsultarArticulosTodos`)
- [ ] Seleccionar un artículo → se agrega a la tabla `#table_Articulos`
- [ ] Aparecen las columnas: Artículo | Cantidad | Precio | Importe
- [ ] El total inferior actualiza: `Subtotal`, `IGV`, `Cobranza Total`

### Posibles errores acá:
- 🔴 `404 .aspx/ConsultarArticulosTodos` → router.php no está activo. Asegúrate de iniciar PHP con `php -S localhost:PUERTO router.php`.
- 🔴 Respuesta `{"d":"-1"}` → sesión expiró. Hacer login de nuevo.
- 🔴 Respuesta `{"d":[]}` con texto correcto → revisar SP `webDatpos_consultarArticulosTodos`.

---

## 4. Favoritos / Categorías

- [ ] Click en botón **Favoritos** → carga grid con artículos favoritos (`LSCargarFavoritos`)
- [ ] Click en una categoría → carga artículos de esa categoría (`LSConsultarArticulosCategoria`)
- [ ] Click en un artículo favorito → se agrega al carrito

---

## 5. Cliente

- [ ] Pasar al tab **Cobranza** o sección de cliente
- [ ] El cliente por defecto está cargado (llama `ClientePorDefecto`)
- [ ] Buscar otro cliente: escribir en `#tb_cliente`
- [ ] Aparecen sugerencias (`ConsultarClientesTodos`)

---

## 6. Cobranza

- [ ] Total a cobrar visible y correcto
- [ ] Selector de **forma de pago / tarjeta** (`#ddl_tarjetas`)
- [ ] Ingresar monto → ver `Vuelto` calculado
- [ ] Marcar tipo doc: `BV` (Boleta) / `FC` (Factura) / `NV` (Nota de Venta)

---

## 7. Cobrar (la prueba real)

- [ ] Click en `Cobrar` → llama al WebMethod `Cobrar`
- [ ] Respuesta esperada: array `[true, "", id_cbfact]`
- [ ] Se genera Serie + Correlativo
- [ ] Se imprime el ticket (`#zona-imprimir`)
- [ ] Se ofrece descargar PDF / imprimir
- [ ] El detalle se inserta correctamente en la BD (verificable consultando el documento por su ID)

### Posibles errores acá:
- 🔴 `Cobrar` devuelve error con mensaje → es `BLMovimientoCabecera.ValidarAlFacturar`. Pásame el mensaje exacto.
- 🔴 `Cobrar` devuelve `[false, "", ""]` sin más info → revisar logs PHP (`error_log`) por excepción interna.
- 🟡 SUNAT: el envío está **comentado** en el VB y en PHP también. No debería intentar enviar. Si intenta, reportar.

---

## 8. Cuenta temporal (Guardar / Obtener)

- [ ] Sin cobrar, click en **Guardar Cuenta**
- [ ] Pone etiqueta y guarda → llama `LSGuardarCuenta`
- [ ] El carrito se vacía
- [ ] Click en **Obtener Cuenta** → modal con cuentas guardadas (`ConsultarCuentas`)
- [ ] Doble click en una cuenta → recupera carrito (`LSConsultarCuentaDetalles`)

---

## 9. Variantes / Subvariantes

Si el artículo tiene variantes (talla, color):
- [ ] Al agregarlo, aparece selector de variantes (`ConsultarVariantesActivas`)
- [ ] Al elegir variante, aparece subvariante si aplica (`ConsultarSubVariantesActivas`)

---

## 10. Lista de documentos generados (tab Lista)

- [ ] Tab `Lista` → muestra documentos generados del día/turno
- [ ] Las columnas se ven correctas

---

# 🚨 Cómo reportarme un error

Cuando algo falle, pásame:

1. **Qué hiciste** (paso del checklist)
2. **Mensaje de error exacto** de Console (F12)
3. **Si es 404 o 500**: Network tab (F12), click en la request fallida, copia la URL y la respuesta
4. **Si es error visual** (algo no aparece): screenshot o descripción

Ejemplo bueno:
> Paso 3.4 — al buscar "polo" en `#tb_articulo` la consola dice:
> `Uncaught TypeError: Cannot read property 'd' of undefined at FacturaListaPrecio3.js:264`
> En Network: `POST /FacturaListaPrecio.aspx/ConsultarArticulosTodos` → 500
> Response body: `{"error": "Undefined variable: $obj in DAArticulo.php:55"}`

Con esto te puedo arreglar el bug en minutos.

---

# Cambios aplicados en esta etapa (Etapa 1)

- ✅ Movidos archivos legacy a `_legacy/`: `api/facturacion.php`, `api/usuario.php`, `temp_facturacion_body.txt`, `temp_home.html`
- ✅ Agregada **validación de acceso al menú 1035** en `pages/Ventas/FacturaListaPrecio.php` (paridad con `FacturaListaPrecio.aspx.vb:23-26`)
- ✅ Verificada cobertura **100% de los 21 WebMethods** del VB original en `api/facturacion_api.php`
- ✅ Verificada cobertura **100% de las 18 llamadas AJAX** del JS legacy
- ✅ Verificado que `jsonResponse()`, `DAEmpresa`, `llenarobjeto()`, `nombre_usuario`, `td_caja`, `td_codtienda` **todos están** disponibles
- ✅ `php -l pages/Ventas/FacturaListaPrecio.php` → sin errores de sintaxis
