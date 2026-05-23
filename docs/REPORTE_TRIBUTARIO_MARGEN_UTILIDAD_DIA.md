# Reporte Tributario y Consulta Margen Utilidad por Día

Este documento describe la arquitectura, dependencias y forma correcta
de usar las pantallas:

- `pages/Reportes/ReporteTributario.php`
- `pages/Consultas/ConsultaMargenUtilidadDia.php`

## 1. Arquitectura

```
Pagina PHP (UI)
    |
    v
JS controlador (assets/Javascript/<Pagina>.js)
    |  AJAX  POST  'XxxPagina.aspx/<Metodo>'
    v
facturacion_adapter.js
    |  reescribe a 'api/consultadocumento_api.php?method=<Metodo>'
    v
api/consultadocumento_api.php  (PHP/PDO)
    |  Database::selectStoredTenant('webDatpos_<SP>', @params)
    v
SQL Server  (SPs en DatPos_EMP01)
```

Las pantallas no llaman directamente a stored procedures: la API mapea
el `method` recibido a un SP y traduce las filas en arreglo de objetos
con las claves exactas que cada `DataTable` lee.

## 2. Reporte Tributario

### 2.1 Archivos involucrados

| Capa | Archivo |
|------|---------|
| UI   | `pages/Reportes/ReporteTributario.php` |
| JS   | `assets/Javascript/ReporteTributario.js` |
| API  | `case 'ConsultaTributarioPrincipal'`, `case 'DescargarArchivoPDF/XML/XMLCDR'`, `case 'CargarCliente'` en `api/consultadocumento_api.php` |
| SP   | `webDatpos_ConsultaTributarioPrincipal`, `webDatpos_DescargarArchivoPDF`, `webDatpos_DescargarArchivoXML`, `webDatpos_DescargarArchivoXMLCDR` (definidos en `scripts/sql/MODIFY_912_FIX_65_Tributario_MargenDia.sql`). |

### 2.2 Flujo

1. Al cargar la pagina:
   - `CargarTienda()` y `CargarMesActual()` (Filtros.js / Comun.js)
     poblan el dropdown de tiendas y las fechas por defecto.
2. El usuario llena los filtros y hace click en **Ejecutar**:
   - `Ejecutar()` valida los obligatorios y arma el payload
     `{ ReporteTributario: [ { ccod_tienda, dfch_desde, dfch_hasta,
     cdoc, cdoc_serie, cdoc_nro, cstatus_tributario, ccod_coa } ] }`.
   - Se envia a `ReporteTributario.aspx/ConsultaTributarioPrincipal`
     -> `api/consultadocumento_api.php?method=ConsultaTributarioPrincipal`.
3. La API ejecuta `webDatpos_ConsultaTributarioPrincipal` y mapea las
   8 columnas devueltas a las claves que lee DataTables:
   `ccod_coa` (nombre del cliente), `cdoc`, `cdoc_serie`, `cdoc_nro`,
   `ntotal`, `dfch_doc`, `cstatus_tributario`, `pdf`, `xml`, `zip`.
   Las 3 ultimas son HTML con un `<i>` que dispara la descarga.
4. Al hacer click en el icono PDF/XML/CDR el JS llama
   `DescargarArchivoPDF/XML/XMLCDR(row)`, que pega a la API. Los SPs
   correspondientes devuelven el binario + cdoc/cserie/nnumero; la
   API codifica el binario en base64 y el JS arma un `<a download>`
   con nombre `cdoc-cdoc_serie-cdoc_nro.pdf/xml/xmlcdr`.

### 2.3 Filtros

| Campo | Obligatorio | Comportamiento |
|-------|-------------|----------------|
| Tienda | Si | Filtra por `CbFactura.ccod_tiend`. |
| Fecha desde / hasta | Si | DD/MM/YYYY; el SP usa `TRY_CONVERT(..,103)`. Inclusive (`fchHasta` se suma 1 dia internamente). |
| Cod. Doc. | Si | Default BV. Otras opciones: FV/NC/ND. |
| Serie / Nro | No | Filtros opcionales por `cserie` / `nnumero`. |
| Estado tributario | No | Codigos 1/4/5/6/8 (Pendiente/Aceptado/Aceptado obs/Error/Anulado). Si la base tiene otros valores (como 'P') solo coinciden si el filtro queda vacio. |
| Cliente | No | Codigo de cliente (`ccod_coa` = RUC tras FIX_50). |

### 2.4 Datos de prueba

Despues de correr `NEW_999_SEED_TestData_Ventas.sql` se puede ejecutar
`NEW_1001_SEED_TestData_Tributario.sql` para que las facturas semilla
tengan distintos `cstatus_tributario`:

| id_cbfact | doc-serie-nro | cstatus_tributario |
|-----------|---------------|---------------------|
| 2 | BV B001-2 | 4 (Aceptado) |
| 3 | BV B001-3 | 5 (Aceptado con obs) |
| 6 | NC NC01-1 | 1 (Pendiente) |
| 7 | ND ND01-1 | 6 (Error) |
| 8 | ND ND01-2 | 8 (Anulado) |

### 2.5 Prueba esperada

- Tienda `T001`, Fechas `01/05/2026` - `31/05/2026`, Cod Doc `BV`,
  Estado vacio -> 2 filas (`B001-2`, `B001-3`).
- Igual pero Cod Doc `NC` -> 1 fila (`NC01-1`).
- Tras correr `NEW_1001`, cambiar Estado a "Pendiente de envio" (1)
  con Cod Doc `NC` -> 1 fila (`NC01-1`).
- Click sobre el icono PDF (fa-file-pdf-o): se descarga un archivo
  `BV-B001-2.pdf` (vacio, porque `CbFactura.pdf` esta en NULL en el
  seed; el flujo termina sin error).

## 3. Consulta Margen Utilidad por Día

### 3.1 Archivos involucrados

| Capa | Archivo |
|------|---------|
| UI   | `pages/Consultas/ConsultaMargenUtilidadDia.php` |
| JS   | `assets/Javascript/ConsultaMargenUtilidadDia.js` |
| API  | `case 'MargenUtilidadDiaPricipal'`, `case 'DatosAdicionales'` en `api/consultadocumento_api.php` |
| SP   | `webDatpos_MargenUtilidadDiaPricipal` (definido en `scripts/sql/MODIFY_912_FIX_65_Tributario_MargenDia.sql`). |

### 3.2 Flujo

1. Al cargar la pagina:
   - `CargarTienda()`, `CargarCaja()`, `CargarMesActual()` pueblan los
     dropdowns y fechas iniciales.
2. El usuario llena Tienda + Caja + Fechas y hace click en **Ejecutar**:
   - `Ejecutar()` valida los obligatorios y arma el payload
     `{ MargenUtilidadDia: [ { ccod_tienda, ccod_caja, n_fchDesde,
     n_fchHasta } ] }`.
   - Se envia a
     `ConsultaMargenUtilidadDia.aspx/MargenUtilidadDiaPricipal`.
3. La API ejecuta `webDatpos_MargenUtilidadDiaPricipal`, que agrupa
   `CbFactura` por (`ccod_tiend`, `ccod_caja`, `CAST(fecha AS DATE)`)
   y devuelve 9 columnas mapeadas a:
   `ccod_tienda`, `cdsc_tienda`, `ccod_caja`, `cdsc_caja`,
   `nprecio`, `ncosto`, `n_margenUtilidad`, `n_marUtiPorcenta`,
   `dfch_crea`.
4. Si la consulta devolvio filas, el JS llama a
   `DatosAdicionales` con `{ Datos: <filas> }`. La API totaliza en
   PHP (sin SP) y devuelve `{ nprecio, ncosto, n_margenUtilidad }`
   que se muestran en la pestania **Datos Adicionales**.
5. Tab **Lista** muestra el grid principal; **Datos Adicionales**
   muestra los totales agregados.

### 3.3 Filtros

| Campo | Obligatorio | Comportamiento |
|-------|-------------|----------------|
| Tienda | Si | Filtra por `CbFactura.ccod_tiend`. |
| Caja | Si | Filtra por `CbFactura.ccod_caja`. |
| Fecha desde / hasta | Si | DD/MM/YYYY o ISO; el SP usa `TRY_CONVERT`. |

### 3.4 Datos de prueba

Reutiliza el seed `NEW_999_SEED_TestData_Ventas.sql`. Con los datos
sembrados ahi, la consulta Tienda `T001`, Caja `CAJ01`, Fechas
`01/05/2026` - `31/05/2026` deberia devolver 1 fila (todas las
facturas son del 13/05/2026, agrupadas por dia):

| Tienda | Caja | Importe Total | Costo Total | Margen Utilidad | % | Fecha |
|--------|------|---------------|-------------|------------------|----|-------|
| T001 | CAJ01 | 5.00 | 1.00 | 4.00 | 80.00 | 13/05/2026 |

(Se excluyen las facturas con `cstatus='A'` -> BV B001-2 y ND ND01-2;
quedan BV B001-3 + NC NC01-1 + ND ND01-1 = `1.50 + 2.00 + 1.50 = 5.00`).

En la pestania **Datos Adicionales** se debe ver:
- Importe Total: 5.00
- Costo Total: 1.00
- Margen Utilidad: 4.00

## 4. Orden de ejecucion de scripts SQL

Para que las pantallas funcionen end-to-end sobre una base nueva:

```
scripts/sql/010_* .. 690_*                (scripts base)
scripts/sql/MODIFY_910_FIX_63_*           (Consulta Documento / Formas Pago)
scripts/sql/MODIFY_911_FIX_64_*           (DocRef indices)
scripts/sql/MODIFY_912_FIX_65_*           (Reporte Tributario + Margen Utilidad Dia)  <-- NUEVO
scripts/sql/NEW_999_SEED_TestData_Ventas.sql
scripts/sql/NEW_1000_SEED_TestData_Cobranzas.sql
scripts/sql/NEW_1001_SEED_TestData_Tributario.sql  (opcional: pone estados tributarios variados)
```

## 5. Solucion de problemas

| Sintoma | Causa probable | Solucion |
|---------|----------------|----------|
| Tabla queda vacia al ejecutar | El SP nunca corrio porque el formato de fecha no coincide. | Verificar que se aplico `MODIFY_912_FIX_65_*`. Internamente usa `TRY_CONVERT` con estilo 103 (DD/MM/YYYY) y 120 (ISO). |
| Tabla vacia tras MODIFY_912 | No hay facturas para los filtros (tienda/caja/fechas). | Correr `NEW_999_SEED_TestData_Ventas.sql`. Usar Tienda `T001`, Caja `CAJ01`, fechas que incluyan 13/05/2026. |
| `alert(error)` "Internal Server Error" | El SP fallo (p.ej. `Tiendas`/`Cajas` no existen, o `CbFactura.cstatus_tributario` no existe). | Revisar log de PHP / SQL Server. Verificar que el script base `030_02_EMP01_Tablas.sql` esta aplicado. |
| El filtro "Estado tributario" no devuelve resultados | Los seeds tenian `cstatus_tributario='P'`, pero el dropdown usa codigos numericos. | Correr `NEW_1001_SEED_TestData_Tributario.sql` para variar los estados. |
| El icono PDF no descarga nada | El campo `pdf` de `CbFactura` esta NULL (el seed no genera PDFs reales). | Comportamiento esperado en demo; en produccion el flujo de emision electronica popula `pdf`/`xml`/`xml_cdr`. |
| Columna "Cliente" muestra el RUC y no el nombre | Tras FIX_50, `ccod_coa` ES el RUC. El API mapea `cdsc_coa` al campo `ccod_coa` que lee el JS para mostrar el nombre. | Si quieres mostrar el RUC, cambia la asignacion en `api/consultadocumento_api.php` case `ConsultaTributarioPrincipal` linea 95: `'ccod_coa' => strval($f[X] ?? '')` donde X = indice del campo deseado. |
