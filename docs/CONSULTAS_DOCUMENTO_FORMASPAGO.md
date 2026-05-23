# Consulta Documento y Consulta Formas de Pago — Guía de uso

Esta guía explica cómo usar correctamente las pantallas
`pages/Consultas/ConsultaDocumento.php` y
`pages/Consultas/ConsultaFormasPago.php`, qué dependencias necesitan en
la base de datos y qué SQL hay que ejecutar para tener datos de prueba.

---

## 1. Arquitectura de cada pantalla

Ambas páginas usan el mismo patrón:

```
Usuario → Página PHP (vista)
          ├─ JS: assets/Javascript/ConsultaDocumento5.js / ConsultaFormaPago.js
          │   └─ AJAX a *.aspx/<method>  ← interceptado por
          │       assets/Javascript/facturacion_adapter.js
          │       (redirige a api/<api>.php?method=<method>)
          └─ API: api/consultadocumento_api.php   ← endpoint unificado
              └─ Stored Procedures de DatPos_EMP01
```

El adaptador `facturacion_adapter.js` traduce el namespace legacy de
ASP.NET (`/Interfaces/ConsultaDocumento.aspx/XYZ`) al backend PHP
(`api/consultadocumento_api.php?method=XYZ`). El JS de cada pantalla NO
se ha tocado: solo se ajustó la API y los SPs para que coincidan con el
payload real que el JS envía.

---

## 2. Stored procedures necesarios

Los siguientes SPs viven en la BD `DatPos_EMP01` y son creados por
`scripts/sql/MODIFY_910_FIX_63_ConsultaDocumento_FormasPago_SPs.sql`
(y `MODIFY_911_FIX_64_DocRef_Indices.sql` re-crea
`sp_consultadatosdocref` con todos los indices que leen los modales):

| SP                                | Llamado por (case en API)               |
|-----------------------------------|-----------------------------------------|
| `sp_consultasdocumentopricipal`   | `ConsultasDocumentoPricipal` (TLista/TDetallado) |
| `sp_consultaformaspagop`          | `ConsultaFormasPagoPricipal` (TLista/TDetallado) |
| `sp_consultalistcobranzaid`       | `ConsultaListCobranzaId`                |
| `sp_consultadatosdocref`          | `ConsultaDatosDocRef` + `DatosReferencia` (cuando `id_cbinve=0`) |
| `sp_consultapdf`                  | `ConsultaPDF`                           |
| `sp_cargarclientefacturar`        | `CargarClienteFacturar`                 |
| `sp_cargarcliente`                | `CargarCliente`                         |
| `sp_cargarlistausuario`           | `CargarListaUsuario`                    |
| `sp_cargarnumeradorcobranza`      | `CargarNumeradorCobranza`               |
| `sp_datosadicionales3`            | `DatosAdicionales3` (modo legacy)       |

`DatosAdicionales` y `DatosAdicionales3` ahora **no** requieren SP en
modo normal: la API recibe el array de filas devuelto por la consulta
previa y calcula los totales (`Efectivo`, `Tarjeta`, `NotaCredito`,
`NotaDebito`, `ntotal`, `nimporte_neto`) en PHP.

Otros SPs y endpoints compartidos vienen de scripts existentes:

* `webDatpos_consultaTienda` → `ConfigGeneral.aspx/CargarTienda` (combo
  Tienda en Consulta Documento) — provisto por
  `460_FIX_22B_SPs_ConsultasDoc.sql`.
* `webDatpos_CargarListaUsuario` → `ConfigGeneral.aspx/CargarListaUsuario`
  — provisto por scripts base.

---

## 3. Tablas que tienen que tener datos

Para que ambas pantallas devuelvan resultados se requiere al menos:

| Tabla         | Filtra por                | Datos mínimos                                  |
|---------------|---------------------------|-------------------------------------------------|
| `Coa`         | `ccod_cia`, `ccod_coa`    | Clientes (al menos `CLI000` o `CLI_TEST`)       |
| `Tiendas`     | `ccod_cia`, `ccod_tiend`  | Tienda `T001`                                   |
| `Almacenes`   | `ccod_cia`, `ccod_alm`    | Almacén `ALM001`                                |
| `Cajas`       | `ccod_cia`, `ccod_caja`   | Caja `CAJ01`                                    |
| `Usuarios`    | `ccod_empresa`, `ccod_usuario` | `ADMIN`, `cajero`                          |
| `NumeradorCaja` | `ccod_cia`, `ccod_caja`, `cdoc_tipo` | BV / FA / NC / ND / NV         |
| `Turno`       | `ccod_cia`, `id_turno`    | Al menos un turno cerrado y uno abierto         |
| `CbFactura`   | `ccod_cia`, `id_cbfact`   | Documentos a consultar                          |
| `LnFactura`   | `ccod_cia`, `id_cbfact`   | Líneas del documento (para modo Detallado)      |
| `CbCobranza`  | `ccod_cia`, `id_cbcajac`  | Cabeceras de cobranza por cada documento        |
| `LnCobranza`  | `ccod_cia`, `id_cbcajac`  | Líneas de cobranza (Efectivo, Tarjeta, NC, ND)  |

---

## 4. Cómo cargar los datos de prueba

Ejecutar **en este orden** (en SSMS o con `sqlcmd`) sobre la BD
`DatPos_EMP01`:

1. Toda la cadena base de scripts (010 → 690) si la BD está vacía. El
   batch `scripts/run_all_safe.bat` ya lo hace.
2. `scripts/sql/MODIFY_910_FIX_63_ConsultaDocumento_FormasPago_SPs.sql`
   — crea los 10 SPs descritos arriba.
3. `scripts/sql/MODIFY_911_FIX_64_DocRef_Indices.sql` — re-crea
   `sp_consultadatosdocref` con los 34 indices necesarios para
   `ModalBuscarDoc` y `ArmarHtml` (Vista previa / Ver detalle).
4. `scripts/sql/NEW_999_SEED_TestData_Ventas.sql` — inserta clientes,
   turnos, `CbFactura` (BV, NC, ND) y `LnFactura`.
5. `scripts/sql/NEW_1000_SEED_TestData_Cobranzas.sql` — inserta
   `CbCobranza` y `LnCobranza` con los 4 tipos de pago (Efectivo,
   Tarjeta VISA, Nota Crédito, Nota Débito).

Tras ejecutarlos la BD tendrá 5 documentos (`id_cbfact` 2,3,6,7,8) con
sus cobranzas asociadas (`id_cbcajac` 2..6).

Verificación rápida:

```sql
SELECT id_cbfact, cdoc, cserie, nnumero, ntotal, cstatus, fecha_emision
FROM CbFactura WHERE ccod_cia = 'EMP01' AND id_cbfact IN (2,3,6,7,8);

SELECT id_cbcajac, id_cbfact, ntotal, cnom_tarje, dfch_crea
FROM CbCobranza WHERE ccod_cia = 'EMP01' AND id_cbcajac IN (2,3,4,5,6);

SELECT id_lncajac, id_cbcajac, cnom_tarje, nmonto, cnum_opera, cnum_tarje
FROM LnCobranza WHERE ccod_cia = 'EMP01' AND id_lncajac IN (2,3,4,5,6);
```

Los scripts NEW_999 y NEW_1000 son **idempotentes**: borran los datos
previos por cascada antes de insertar, así que pueden re-ejecutarse las
veces que haga falta.

---

## 5. Uso de ConsultaDocumento.php

URL: `/pages/Consultas/ConsultaDocumento.php`
(menu **Ventas → Consultas → Consulta de Documento**).

### 5.1 Filtros obligatorios

| Campo            | Tipo            | Obligatorio | Observación                       |
|------------------|-----------------|-------------|-----------------------------------|
| Tienda           | combo           | sí          | Cargado por `CargarTienda`        |
| Fecha desde      | date `dd/mm/yyyy` | sí        | Default = primer día del mes      |
| Fecha hasta      | date `dd/mm/yyyy` | sí        | Default = último día del mes      |
| Código documento | combo           | sí          | BV, FV, NC, ND, NV, etc.          |
| Serie / Número   | text            | no          |                                    |
| Cliente          | modal           | no          | abre `CargarClienteFacturar`      |
| Usuario          | modal           | no          | abre `CargarListaUsuario`         |
| Observación      | text            | no          | LIKE sobre `CbFactura.cobs`       |
| Variante         | text            | no          | LIKE sobre `LnFactura.cobser_variante` |
| Modo             | radio           | sí          | TLista (cabecera) / TDetallado (línea) |

### 5.2 Botones

* **Ejecutar** → POST a `ConsultaDocumento.aspx/ConsultasDocumentoPricipal`
  con payload `{ consultadocumentos: [ { ... } ] }`. Renderiza dos
  DataTables (visible y exportable).
* **Limpiar** → resetea los inputs y destruye las DataTables.
* **Icono ojo / impresora** en cada fila → llama a `ConsultaPDF` con el
  `id_cbfact` y abre el PDF base64 en una nueva pestaña.
* **Click derecho sobre la tabla** → exporta a Excel (HTML→XLS) la
  tabla `tableExport` / `Div_DetalladoExpor`.

### 5.3 Prueba rápida

Con los datos de prueba cargados:

1. Tienda = `T001 - Tienda 1`
2. Fecha desde = `01/05/2026`, fecha hasta = `31/05/2026`
3. Código documento = `BV`
4. Click **Ejecutar**

Resultado esperado (modo TLista):

| cdoc | serie | nro | usuario | cliente            | total |
|------|-------|-----|---------|--------------------|-------|
| BV   | B001  | 2   | ADMIN   | CLIENTE GENERAL    | 2.00  |
| BV   | B001  | 3   | ADMIN   | CLIENTE GENERAL    | 1.50  |

Resultado esperado (modo TDetallado): una fila por cada `LnFactura`
asociada, con `ART003 - AGUA SAN LUIS`.

---

## 6. Uso de ConsultaFormasPago.php

URL: `/pages/Consultas/ConsultaFormasPago.php`
(menu **Ventas → Consultas → Forma de Pago**).

### 6.1 Filtros obligatorios

| Campo            | Tipo            | Obligatorio | Observación                       |
|------------------|-----------------|-------------|-----------------------------------|
| Caja             | combo           | sí          | Cargado por `CargarCaja` (factura) |
| Fecha desde      | date `dd/mm/yyyy` | sí        | Default = primer día del mes      |
| Fecha hasta      | date `dd/mm/yyyy` | sí        | Default = último día del mes      |
| Cod Doc          | combo           | sí          | Cargado por `CargarNumeradorCobranza` (BV/FV/NC/ND...) |
| Serie / Número   | text            | no          |                                    |
| Cliente          | modal           | no          |                                    |
| Tipo Tarjeta     | combo / text    | no          | Filtra por `cnom_tarje` (VISA, MASTERCARD, EFECTIVO, …) |
| Usuario          | modal           | no          |                                    |
| Modo             | radio           | sí          | TLista (1 fila por doc) / TDetallado (1 fila por línea) |

### 6.2 Botones

* **Ejecutar** → POST a `ConsultaFormasPago.aspx/ConsultaFormasPagoPricipal`
  con payload `{ FormaPago: [ { ... } ] }`. Luego, si hay resultados,
  llama a `DatosAdicionales` con el mismo array para calcular los
  totales (Efectivo, Tarjeta, NC, ND, Total).
* **Click sobre una fila** (modo Lista) → llama a `ConsultaListCobranzaId`
  con `id_cbcajac` y abre un modal con cada línea de `LnCobranza`
  (`cnom_tarje`, `nmonto`, `cnum_tarje`, `cnum_opera`).

### 6.3 Prueba rápida

Con los datos de prueba cargados:

1. Caja = `CAJ01`
2. Fecha desde = `01/05/2026`, fecha hasta = `31/05/2026`
3. Cod Doc = `BV` (deja todos los demás vacíos)
4. Click **Ejecutar**

Resultado esperado (modo TLista):

| cdoc | serie | nro | usuario | cliente         | Efectivo | Tarjeta | NC   | ND  | Total |
|------|-------|-----|---------|-----------------|----------|---------|------|-----|-------|
| BV   | B001  | 2   | ADMIN   | CLIENTE GENERAL | 2.00     | 0.00    | 0.00 | 0.00| 2.00  |
| BV   | B001  | 3   | ADMIN   | CLIENTE GENERAL | 0.00     | 1.50    | 0.00 | 0.00| 1.50  |

Cambiando Cod Doc a `NC`:

| cdoc | serie | nro | usuario | cliente         | Efectivo | Tarjeta | NC   | ND  | Total |
|------|-------|-----|---------|-----------------|----------|---------|------|-----|-------|
| NC   | NC01  | 1   | ADMIN   | CLIENTE GENERAL | 0.00     | 0.00    | 2.00 | 0.00| 2.00  |

Y los totales que aparecen abajo (`txt_IPEfectivo`, `txt_IPTarjeta`,
`txt_IPNC`, `txt_IPND`, `txt_IPV`) deberían sumar las columnas
correspondientes.

---

## 7. Solución de problemas

| Síntoma                                              | Causa probable                                              | Cómo arreglarlo                                  |
|------------------------------------------------------|-------------------------------------------------------------|--------------------------------------------------|
| `Could not find stored procedure 'sp_...'`           | No se ejecutó `MODIFY_910_FIX_63` (o `MODIFY_911_FIX_64`)     | Correr ambos scripts en `DatPos_EMP01`           |
| **Vista previa** abre el modal vacío                  | No se ejecutó `MODIFY_911_FIX_64`                            | Correrlo: re-crea `sp_consultadatosdocref` con todos los indices |
| **Vista previa** no hace nada al hacer click          | Faltaban `qrcode.js` / `html2canvas.min.js` / `Numerosaletras.js` en la página, o JS `.length()` tipeado como función | Fix incluido: scripts cargados desde `ConsultaDocumento.php` y `.length` corregido en `ConsultaDocumento5.js` |
| **Ver detalle** no abre el modal                      | El icono solo cargaba datos por AJAX pero no abria el modal Bootstrap | Fix incluido: `ModalBuscarDoc` ahora hace `$('#MdFacturacion').modal('show')` y `$('#modalBuscarDoc').modal('show')` |
| Empresa/RUC/Dirección vienen vacíos en el PDF        | Los hidden inputs traían `<%Response.Write(...)%>` (ASP literal sin procesar) | Fix incluido: en `ConsultaDocumento.php` se reemplazó por `<?= e($o->...) ?>` |
| **Ver detalle** no abre nada                          | Browser cacheado con la versión anterior del API             | Hard-reload (`Ctrl+F5`) tras pull de la PR       |
| Tabla principal vacía pero no hay error              | No hay `CbFactura` / `CbCobranza` en el rango de fechas      | Correr `NEW_999` y `NEW_1000`                    |
| Combo Cod Doc vacío en Consulta Formas de Pago       | `NumeradorCaja` no tiene `cdoc_tipo` para `ccod_cia=EMP01`   | Verificar `NumeradorCaja`; el seed lo actualiza  |
| Totales `Efectivo/Tarjeta/...` aparecen como `NaN`   | El SP no devuelve los campos esperados                       | Verificar la versión del script `MODIFY_910`     |
| Click sobre el documento no muestra detalle          | Falta `id_cbcajac` en la fila                                | Verificar `LnCobranza.id_cbcajac` y `CbCobranza` |
| Sesión expirada (`response.d=='-1'`)                 | Sesión PHP perdida                                           | Re-loguear en `index.php`                        |

---

## 8. Resumen del flujo de llamadas

### Consulta Documento

```
ConsultaDocumento.php  (view)
  ├─ document.ready
  │   ├─ CargarMenu()
  │   ├─ ConsultaColumnas()              → ConfigGeneral.aspx/CargarColumnas
  │   ├─ CargarMesActual()
  │   ├─ CargarTienda()                  → ConfigGeneral.aspx/CargarTienda
  │   └─ CargarNumeradorFactura()        → ConfigGeneral.aspx/CargarNumeradorFactura
  │
  ├─ Click "Cliente"   → ModalConsultarClientes()
  │     └─ ConsultaDocumento.aspx/CargarClienteFacturar
  │           SP: sp_cargarclientefacturar
  │
  ├─ Click "Usuario"   → ModalConsultarUsuarios()
  │     └─ ConfigGeneral.aspx/CargarListaUsuario
  │
  ├─ Click "Ejecutar"  → Ejecutar()
  │     └─ ConsultaDocumento.aspx/ConsultasDocumentoPricipal
  │           SP: sp_consultasdocumentopricipal
  │     └─ ConsultaDocumento.aspx/DatosAdicionales (totalizador PHP)
  │     └─ ConsultaDocumento.aspx/DatosAdicionales3 (totalizador PHP)
  │
  └─ Click "Imprimir"  → Imprimir(row)
        └─ ConsultaDocumento.aspx/ConsultaPDF
              SP: sp_consultapdf
```

### Consulta Formas de Pago

```
ConsultaFormasPago.php (view)
  ├─ document.ready
  │   ├─ CargarMenu()
  │   ├─ ConsultaColumnas()
  │   ├─ CargarMesActual()
  │   ├─ CargarNumeradorCobranza()       → ConsultaFormasPago.aspx/CargarNumeradorCobranza
  │   │   SP: sp_cargarnumeradorcobranza
  │   └─ CargarCaja()                    → Facturacion.aspx/CargarCaja
  │
  ├─ Click "Ejecutar"  → Ejecutar()
  │     └─ ConsultaFormasPago.aspx/ConsultaFormasPagoPricipal
  │           SP: sp_consultaformaspagop
  │     └─ ConsultaFormasPago.aspx/DatosAdicionales (totalizador PHP)
  │
  └─ Click sobre fila  → ConsultaListCobranzaId(row)
        └─ ConsultaFormasPago.aspx/ConsultaListCobranzaId
              SP: sp_consultalistcobranzaid
```
