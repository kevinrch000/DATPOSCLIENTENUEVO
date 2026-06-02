# Consultas: Margen de Utilidad, Articulos Mas Vendidos, Ventas y Lista de Precios

Documento que cubre el funcionamiento de 4 pantallas de consulta del
modulo Ventas / Reportes. Todas presentaban el mismo problema: la
DataTable de la pantalla quedaba vacia al pulsar **Ejecutar** porque los
stored procedures que la API PHP invocaba no existian, devolvian columnas
en distinto orden / cantidad de la que el JS esperaba, o leian la clave
de payload equivocada.

## Arquitectura comun

```
HTML / PHP                JS controller              API PHP                       SQL Server (DatPos_EMP01)
-----------------------   ------------------------   ---------------------------   ---------------------------------
pages/Consultas/X.php --> assets/Javascript/X.js --> api/(consultadocumento|       webDatpos_<SP> (filtros + JOIN +
  filtros + DataTable        $.ajax(.aspx/Metodo)     consultaventa)_api.php          GROUP BY + TRY_CONVERT)
                                                       case 'Metodo': map payload
                                                       -> SP -> rearmar columnas
                                                          en el orden que lee la
                                                          DataTable
```

El adapter `assets/Javascript/facturacion_adapter.js` traduce las URLs
legacy `Pagina.aspx/Metodo` a `api/<archivo>_api.php?method=Metodo`. El
JS sigue enviando los mismos payloads que en el ASP.NET original, asi
que el `case` del API es el unico lugar donde se decide:

1. Que clave del payload leer (`ConsultaArticulo`, `MargenUtilidad`,
   `ArticulosMasVendidos`, `articulo`, etc.)
2. Que stored procedure llamar y con que nombres de parametro.
3. En que orden devolver las columnas para que coincidan con
   `columns: [ { data: 'xxx' } ]` de la DataTable.

## Pantallas

### 1. MargenUtilidad.php

**Filtros**: Fecha desde / Fecha hasta, Cliente, Tipo Doc, Serie, Nro.

**Flujo principal (Ejecutar)**:
- JS: `MargenUtilidad.aspx/MargenUtilidadPricipal` (Pricipal con typo legacy).
- Payload: `{ MargenUtilidad: [ { cdoc, cdoc_serie, cdoc_nro, n_fchDesde, n_fchHasta, ccoa_dsc } ] }`.
- SP: `webDatpos_MargenUtilidadPricipal` (creado en MODIFY_913).
- Devuelve 1 fila por documento con costo de cabecera (`CbFactura.costo`)
  vs total cobrado (`CbFactura.ntotal`) y margen calculado.

**Columnas DataTable**:

| Columna           | Origen                                                  |
|-------------------|---------------------------------------------------------|
| cdoc              | `CbFactura.cdoc`                                        |
| cdoc_serie        | `CbFactura.cserie`                                      |
| cdoc_nro          | `CbFactura.nnumero`                                     |
| ccoa_dsc          | `Coa.cdsc_coa` (fallback a `ccod_coa`)                  |
| nprecio           | `CbFactura.ntotal`                                      |
| ncosto            | `CbFactura.costo`                                       |
| n_margenUtilidad  | `ntotal - costo`                                        |
| n_marUtiPorcenta  | `100 * (ntotal-costo) / ntotal`                         |
| n_docRef          | reservado (vacio por ahora)                             |
| dfch_crea         | `fecha_emision` en formato `DD/MM/YYYY`                 |
| id_cbfact         | clave para `ModalBuscarDoc`                             |

**Detalle por documento (ModalBuscarDoc)**:
- `ConsultarMargenUtilidadArticuloDatos` -> cabecera (tienda, caja,
  usuario, cliente, doc) usando `webDatpos_ConsultarMargenUtilidadArticuloDatos`.
- `ConsultarMargenUtilidadArticulo` -> lineas (`LnFactura`) con costo y
  margen por linea usando `webDatpos_ConsultarMargenUtilidadArticulo`.

### 2. ConsultaArticulosMasVendidos.php

**Filtros**: Tienda, Fecha desde / hasta, Familia, Articulo.

**Flujo (Ejecutar)**:
- JS: `ConsultaArticulosMasVendidos.aspx/ConsultaArticulosMasVendidos`.
- Payload: `{ ArticulosMasVendidos: [ { ccod_articulo, ccod_tienda, n_fchDesde, n_fchHasta, ccod_lin } ] }`.
- SP: `webDatpos_ConsultaArticulosMasVendidos`.
- Agrupa por `(ccod_caja, ccod_lin, ccod_articulo)` y suma `ncantidad`.

**Columnas DataTable**: `ccod_caja, cdsc_caja, ccod_lin, ccod_articulo,
cdsc_articulo, ncantidad`.

### 3. ConsultasVenta.php

**Filtros**: Tienda, Fecha desde / hasta, Cliente, Articulo, Variante.

**Flujo (Ejecutar)**:
- JS: `ConsultasVenta.aspx/ConsultasVentaPricipal`.
- Payload: `{ ConsultaArticulo: [ { ccod_articulo, ccod_tienda, ccod_coa, n_fchDesde, n_fchHasta, cobser_variante } ] }`.
- SP: `webDatpos_ConsultasVentaPricipal`.
- Devuelve **una fila por linea de factura** (`LnFactura` + cabecera).

**Columnas DataTable**: `ccod_coa, ccod_articulo, cdsc_articulo,
ncantidad, nprecio, nimpuesto, nisc, ndescuento, nimporte_neto,
dfch_doc, cobser_variante, cstatus`.

**Tab "Datos Adicionales"**:
- JS hace POST a `DatosAdicionales` con la lista entera mostrada en
  pantalla (`{ VentasPorArticulo: [...] }`).
- La API suma `ncantidad`, `nprecio * ncantidad` (importe bruto),
  `nimpuesto`, `nisc`, `ndescuento`, `nimporte_neto` y devuelve un
  objeto con 6 totales. No se llama SP adicional para evitar
  duplicacion.

**Detalle (`ModalBuscarDoc` -> `ConsultaListArticulos`)**:
- JS envia `{ id_fact: <id_cbfact> }`.
- SP `webDatpos_consultaListArticulos` devuelve las lineas de esa
  factura con `ccod_articulo, cdsc_articulo, ncantidad, nprecio,
  nimpuesto, nisc, ndescuento, nimporte_neto`.

### 4. ConsultaListPrecio.php

**Filtros**: Lista de precios, Familia, Unidad de medida, Articulo
(codigo / descripcion).

**Flujo (Ejecutar)**:
- JS: `ConsultaListPrecio.aspx/ConsultaListPrecioPricipal`.
- Payload: `{ articulo: [ { ccod_cblistpre, ccod_articulo, cdsc_articulo, ccod_lin, ccod_unidadmedida } ] }`.
- SP: `webDatpos_ConsultaListPrecioPricipal` (LnListaPrecio JOIN
  CbListaPrecio JOIN Articulos JOIN Familias JOIN UnidadMedida).

**Columnas DataTable**: `ccod_cblistpre, cdsc_cblistpre, ccod_articulo,
cdsc_articulo, cdsc_lin, csim_unidadmedida, npre_uni`.

**Combo "Lista de Precios"** (`CargarListPrecio`):
- El SP `sp_consultarlistaspreciosactivos` (definido en
  `080_07_EMP01_TipoOper_Config_Caja.sql`) devuelve `ccod_cblistpre,
  cdsc_cblistpre` filtrando por `cstatus='A'`. Para mantener
  compatibilidad con scripts legacy que esperaban `id_cblistpre /
  cdsc_listpre`, la API replica los mismos valores bajo ambos juegos
  de claves.
- `MODIFY_914_FIX_67_CargarListPrecio.sql` garantiza idempotentemente
  que `sp_consultarlistaspreciosactivos` y un alias `sp_cargarlistprecio`
  existan.

**Modal "Seleccione Articulo"** (icono lupa al lado de "Codigo de
Articulo"):
- JS llama `ConsultaListPrecio.aspx/CargarArticuloListPrecio` con
  payload `{ objArticuloListPrecio: [ { ccod_cblistpre, ccod_articulo,
  cdsc_articulo, ccod_lin, ccod_unidadmedida } ] }`.
- SP canonico: `webDatpos_CargarArticuloListPrecio` (creado en
  MODIFY_915). Acepta `%%%` como "todos" en familia y unidad de
  medida y devuelve `(ccod_articulo, cdsc_articulo)` para los
  articulos cargados en la lista seleccionada. Alias defensivo:
  `sp_cargararticulolistprecio`.
- Al seleccionar una fila y pulsar "Seleccionar", `PasaDatosCodEmpresa()`
  copia `ccod_articulo` en `#txtCodArticulo`.

## SPs creados / recreados

Archivo: `scripts/sql/MODIFY_913_FIX_66_MargenU_MasVendidos_Ventas_ListPrecio.sql`.

| SP                                                       | Pantalla                                                                                           |
|----------------------------------------------------------|----------------------------------------------------------------------------------------------------|
| `webDatpos_MargenUtilidadPricipal`                       | MargenUtilidad.php -> Ejecutar                                                                     |
| `webDatpos_ConsultarMargenUtilidadArticuloDatos`         | MargenUtilidad.php -> ModalBuscarDoc (cabecera)                                                    |
| `webDatpos_ConsultarMargenUtilidadArticulo`              | MargenUtilidad.php -> ModalBuscarDoc (lineas)                                                      |
| `webDatpos_ConsultaArticulosMasVendidos`                 | ConsultaArticulosMasVendidos.php -> Ejecutar                                                       |
| `webDatpos_ConsultaListPrecioPricipal`                   | ConsultaListPrecio.php -> Ejecutar                                                                 |
| `webDatpos_ConsultasVentaPricipal`                       | ConsultasVenta.php -> Ejecutar                                                                     |
| `webDatpos_consultaListArticulos`                        | ConsultasVenta.php -> ModalBuscarDoc                                                               |

Todos los SPs usan `COALESCE(TRY_CONVERT(DATETIME,@param,103), TRY_CONVERT(DATETIME,@param,120))`
para aceptar fechas en `DD/MM/YYYY` (cuando el calendario las envia con
ese formato) o `YYYY-MM-DD` ISO. Se excluye `cstatus='A'` (anuladas).

## Como probar (con el seed actual)

Pre-requisitos:
1. Aplicar scripts base `010..690` sobre `DatPos_EMP01`.
2. Aplicar `MODIFY_910..913` en orden.
3. Aplicar `NEW_999_SEED_TestData_Ventas.sql` y `NEW_1000_SEED_TestData_Cobranzas.sql`.
   El seed deja: 2 BV (`B001-2`, `B001-3`), 1 NC (`NC01-1`), 2 ND
   (`ND01-1` activa, `ND01-2` anulada). Todas con `ART003` y fecha
   `13/05/2026`, tienda `T001`, caja `CAJ01`.

### MargenUtilidad
- Filtros: Fecha desde `01/05/2026`, Hasta `31/05/2026`, Tipo Doc `BV`
  -> 2 filas (`B001-2` y `B001-3`).
- `ncosto` aparece en cero porque el seed deja `costo=0` y `ncosto=0`
  en las lineas. El margen sale 100 %, lo cual es esperable. Para ver
  margenes intermedios actualiza manualmente `CbFactura.costo` / o
  `LnFactura.ncosto` antes de filtrar.
- Click sobre el icono **Buscar Doc**: deben llenarse cabecera (T001,
  ALM001, CAJ01, CLI000, ADMIN) y lineas (`ART003`).

### ConsultaArticulosMasVendidos
- Filtros: Tienda `T001`, Fecha desde `01/05/2026`, Hasta `31/05/2026`,
  Familia vacio -> 1 fila: `CAJ01 - "" - ART003 - AGUA SAN LUIS - 3.0000`.
- Repetir con Familia `FAM001`: 0 filas (ART003 no tiene familia
  cargada en este seed).

### ConsultasVenta
- Filtros: Tienda `T001`, Fecha desde `01/05/2026`, Hasta `31/05/2026`
  -> 4 filas (las 3 lineas activas + la ND01-1 que tiene cantidad 0).
  La ND01-2 anulada queda fuera.
- Tab **Datos Adicionales**: `Cantidad total 3.00`, `Importe bruto 5.50`,
  `IGV ~0.85`, `Importe neto ~5.50`.
- Click sobre el icono **Ver Doc**: detalle de las lineas de esa
  factura.

### ConsultaListPrecio
- Cargar combo "Lista de Precios" -> debe traer `LP001`.
- Filtros: Lista `LP001`, Familia vacio, U.M. vacio -> 15 filas
  (ART006-ART020).
- Filtros: Lista `LP001`, Familia `FAM001` -> 3 filas (ART006-ART008,
  bebidas).

## Solucion de problemas

| Sintoma                                                                | Causa probable                                                                | Donde mirar                                                                                            |
|------------------------------------------------------------------------|--------------------------------------------------------------------------------|--------------------------------------------------------------------------------------------------------|
| "Could not find stored procedure 'webDatpos_...'"                      | No se aplico `MODIFY_913_FIX_66`.                                              | `EXEC dbo.sp_helptext 'webDatpos_MargenUtilidadPricipal'`                                              |
| Pantalla vacia pero hay datos en BD                                    | Filtros con `fchDesde > fchHasta` o tienda equivocada                          | Network tab del navegador: ver payload enviado vs filtros del seed (T001, 13/05/2026, EMP01)            |
| `Conversion failed when converting date and/or time from character string` | Formato de fecha distinto a 103/120 (raro hoy). Forzar `DD/MM/YYYY` desde el calendario. | Console del navegador, request payload                                                                  |
| Combo Lista de Precios sin opciones                                    | `CbListaPrecio` vacia o sin `cstatus='A'`.                                     | `SELECT * FROM CbListaPrecio WHERE ccod_cia='EMP01'`                                                   |
| ConsultaArticulosMasVendidos: filtra por Familia y todo desaparece     | Articulos sin `ccod_lin` cargado en `Articulos`.                               | `SELECT ccod_articulo, ccod_lin FROM Articulos WHERE ccod_cia='EMP01'`                                  |
| ConsultaVentas: Datos Adicionales en cero                              | Se ejecuto `DatosAdicionales` antes que `Ejecutar`, o el resultado venia vacio. | Ver `Network` tab: el body debe llevar `VentasPorArticulo` con al menos 1 elemento.                     |
