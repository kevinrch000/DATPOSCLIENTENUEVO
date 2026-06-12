# DatPOS PHP — Setup desde cero

Esta carpeta contiene **todo lo que tu compañero necesita** para levantar el proyecto en su máquina:

```
scripts/
├── sql/                    SQL en orden (010_…sql → 690_…sql)
├── run_all_safe.bat        Aplica todos los SQL (recomendado — no se detiene ante errores, genera log)
├── run_all_sql.bat         Alternativa simple (se detiene al primer error)
├── start_server.bat        Levanta el servidor PHP en :8080
└── README.md               Este archivo
```

---

## Requisitos

| Software | Versión | Notas |
|---|---|---|
| **SQL Server** | 2017+ (Express OK) | Se usa también SSMS para revisar/diagnosticar |
| **PHP** | 8.0+ | Con extensiones `sqlsrv` y `pdo_sqlsrv` (Microsoft Drivers for PHP for SQL Server) |
| **ODBC Driver 17 for SQL Server** | — | Necesario para que `sqlsrv` pueda conectar |
| **Navegador** | Chrome / Edge actual | — |

### Instalar extensiones PHP `sqlsrv`

1. Descarga los DLLs desde Microsoft → "PHP Drivers for SQL Server".
2. Copia `php_sqlsrv_<version>_nts_x64.dll` y `php_pdo_sqlsrv_<version>_nts_x64.dll` a `<php>/ext/`.
3. En `php.ini` agrega:
   ```ini
   extension=sqlsrv
   extension=pdo_sqlsrv
   ```
4. Verifica: `php -m` debe listar `sqlsrv` y `pdo_sqlsrv`.

---

## Setup paso a paso

### 1. Aplicar todos los SQL

**Solo ejecuta el batch** — crea las BDs automáticamente si no existen:

```bat
cd DatPOS_PHP\scripts
run_all_safe.bat
```

Si tu instancia SQL Server tiene otro nombre (no es `localhost\SQLEXPRESS`):

```bat
run_all_safe.bat "MI_PC\SQLEXPRESS"
```

El bat:
1. Crea `DatPosAdmin` y `DatPos_EMP01` si no existen
2. Corre los 80+ scripts en orden numérico
3. Guarda un log en `run_all_safe.log`
4. Al final muestra cuántos scripts fallaron

> Si el bat no puede conectar, verifica que SQL Server esté corriendo y que `sqlcmd` esté en el PATH (viene con SQL Server / SSMS).

**Opción B: manual**

Abre cada `.sql` en `scripts/sql/` en SSMS **en orden numérico** (010, 020, 030…) y presiona **F5**.

> Nota: cada script empieza con `USE DatPosAdmin;` o `USE DatPos_EMP01;`. SSMS respeta ese USE.

### 3. Configurar conexión PHP

Edita `DatPOS_PHP/config/database.php` líneas 20-23:

```php
private static $adminServer    = 'localhost\\SQLEXPRESS';   // tu instancia
private static $adminDatabase  = 'DatPosAdmin';
private static $adminUser      = '';                       // vacío = Windows Auth
private static $adminPassword  = '';
```

Si usas SQL Auth (usuario y contraseña), llena `adminUser` y `adminPassword`.

### 4. Levantar el servidor

```bat
cd DatPOS_PHP\scripts
start_server.bat
```

Abrir en navegador: **http://localhost:8080**

Login por defecto:
- Usuario: `ADMIN`
- Contraseña: `123456`

---

## Estructura del orden de SQL

| Rango | Qué hace |
|---|---|
| **010 – 140** | Tablas y datos seed iniciales (DatPosAdmin + DatPos_EMP01: tiendas, almacenes, usuarios, artículos, ventas, dashboard) |
| **150 – 250** | FIX_01 a FIX_11: arreglos de login, menús, accesos |
| **260 – 360** | FIX_12 a FIX_18: facturación, apertura de caja, datos de empresa para ticket |
| **370 – 390** | FIX_19: módulos almacén/home, GuiaRemision |
| **400 – 500** | FIX_20 a FIX_22: almacenes, tiendas, ubigeo, SPs faltantes (consultas, reportes, márgenes, pagos) |
| **510 – 570** | FIX_23 a FIX_28: unidad medida, numeradores, salidas/ingresos, listas de operaciones |
| **580 – 610** | FIX_30 a FIX_33: SPs de operaciones, validar artículo, transferencias |
| **620** | `FIX_FINAL_SeedData`: clasifica `TipoOperacion.ctipo_flag`, marca proveedores en COA |
| **630 – 660** | FIX_34 a FIX_37: SPs de consulta almacén, clientes, tiendas |
| **670** | FIX_38: corrige `id_rol=NULL` del usuario ADMIN (bug menú vacío al login) |
| **680** | FIX_39: crea **50 foreign keys** en DatPos_EMP01 + **7 FK** en DatPosAdmin (con correcciones de datos previas) |
| **690** | FIX_40: 5 FKs adicionales — conecta `TipoOperacion`, `ConfigGeneral` y `Menus` (auto-referencia) |
| **700** | Seed data demo: 8 familias, 20 artículos, 23 filas stock, 40 precios, 7 clientes/proveedores, 2 usuarios, numeradores |
| **710** | FIX_41: **SPs DEFINITIVOS** de Apertura/Cierre de Turno — corrige error `AperturaCaja table not found`, bug `ccod_usuario:"2"` y caja vacía; agrega JOINs para descripciones en la lista |
| **720 – 760** | FIX_42 a FIX_47: Dashboard KPIs con datos reales, diagramas de pie, GuiaRemision bugs, modales de búsqueda vacíos, filtros Operaciones almacén |
| **780 – 782** | FIX_48: JWT + bcrypt con migración automática desde MD5/plaintext; fix SP `webDatpos_consultaUsuario` columnas Tiendas |
| **783** | FIX_49: Logo de empresa — guardar, cargar y mostrar en header junto a foto de usuario |

---

## Verificación post-setup

Después de correr todo, en SSMS verifica:

```sql
USE DatPos_EMP01;

-- Datos seed: debe haber al menos 1 fila en cada tabla clave
SELECT 'Tiendas'       AS tabla, COUNT(*) AS filas FROM Tiendas       WHERE ccod_cia='EMP01'
UNION SELECT 'Almacenes',        COUNT(*) FROM Almacenes              WHERE ccod_cia='EMP01'
UNION SELECT 'Cajas',            COUNT(*) FROM Cajas                  WHERE ccod_cia='EMP01'
UNION SELECT 'Usuarios',         COUNT(*) FROM Usuarios               WHERE ccod_cia='EMP01'
UNION SELECT 'Articulos',        COUNT(*) FROM Articulos              WHERE ccod_cia='EMP01'
UNION SELECT 'TipoOperacion',    COUNT(*) FROM TipoOperacion          WHERE ccod_cia='EMP01'
UNION SELECT 'Coa',              COUNT(*) FROM Coa                    WHERE ccod_cia='EMP01';

-- Usuario ADMIN debe tener id_rol=1 (si es NULL el menú aparece vacío)
SELECT ccod_usuario, id_rol, cperm_descn FROM Usuarios WHERE ccod_cia='EMP01';

-- Foreign keys: debe haber 55 en EMP01 y 7 en DatPosAdmin
SELECT DB_NAME() AS base, COUNT(*) AS total_fks FROM sys.foreign_keys;
USE DatPosAdmin;
SELECT DB_NAME() AS base, COUNT(*) AS total_fks FROM sys.foreign_keys;
```

---

## Estado de la migración (al pasar el proyecto)

### Funcionando
- **Login + sesión multitenant** con bcrypt (migración automática desde MD5/plaintext en primer login)
- **Dashboard** con tabs (Reporte, Del Día, Ventas por Artículo, Kardex, Clientes) — KPIs y diagramas de pie con datos reales
- **Almacén → Tablas**: Almacenes, Unidad de Medida, Familias, Artículos, Tipos de Operación (CRUD completo)
- **Almacén → Operaciones**: Ingresos Directos, Salidas Directas, Transferencias (CRUD completo)
- **Almacén → Operaciones**: Guía de Remisión (combos pre-cargados)
- **Modales globales** (lupas para Cliente/Artículo/Proveedor) con fix de z-index y backdrop
- **Vista por tab Lista** como pestaña por defecto en todas las páginas CRUD
- **Consultas → Consulta saldo**: cálculo del importe por artículos
- **Administración → Usuario**: Crear / Editar / Eliminar, con validaciones JS
- **Administración → Roles**: Crear / Editar / Eliminar / Listar, con validaciones JS
- **Administración → Consulta Tiendas**: Consulta de tiendas
- **Administración → Configuración General**: Editar configuración, subir logo de empresa
- **Logo de empresa**: se guarda en BD (`ConfigGeneral.ilogo` VARBINARY), se muestra en el header junto a la foto del usuario en todas las páginas
- **Reportes** (Informe Venta, Turno, Almacén, Kardex, Saldo): fechas convertidas a ISO para evitar error de formato `dd/mm/yyyy`

### Migrado base, falta smoke test
- **Ventas → Apertura/Cierre Caja**
- **Ventas → Facturación** (estándar)
- **Ventas → Nota Crédito / Débito / Anulación**
- **Ventas → Precios** (lista de precios CRUD)
- **Ventas → Clientes** (asociados)
- **Reporte Tributario**
- **SUNAT** envío JSON (paridad con VB original, deshabilitado por defecto via `DATPOS_SUNAT_ENVIO_ENABLED=1`)

### Pendiente
- QA real end-to-end del flujo completo de venta.
- Convertir formato fecha `dd/mm/yyyy` → ISO en los otros ~40 endpoints de API (home_api, consultadocumento_api, etc.) — solo se arreglaron los Informe*.php.
- Implementar UBL 2.1 / firma digital / CDR si se decide expandir alcance SUNAT.

---

## Si tu compañero encuentra un error

1. Mirar la consola de `php -S` (donde corre `start_server.bat`). Los errores del lado servidor aparecen ahí.
2. Mirar la consola del navegador (F12 → Console / Network) para ver qué API se llamó y qué respondió.
3. Para errores SQL, los responses del API incluyen el mensaje exacto en `response.d[2]` o en el log.

## Estructura general del proyecto

```
DatPOS_PHP/
├── api/             Endpoints AJAX (todos los WebMethods migrados)
├── BE/              Business Entities (POJOs equivalentes a clases VB BE.*)
├── BL/              Business Logic
├── DA/              Data Access (capa de stored procedures)
├── assets/          JS / CSS / imágenes copiados del proyecto VB original
├── assets-patch/    Parches mínimos al JS original sin tocarlo (modal_fix.js, operaciones_patch.js)
├── config/          database.php (conexión SQL Server)
├── includes/        auth.php, helpers.php, layout_master.php (Site.Master en PHP)
├── pages/           Las .aspx migradas a .php por módulo
├── scripts/         (este folder) Setup SQL + batch helpers
└── router.php       Servidor PHP, intercepta .aspx → API
```

> Nota: en la tabla `Usuarios` de `DatPos_EMP01`, la columna de empresa es `ccod_cia`. En la tabla `Usuarios` de `DatPosAdmin`, la columna de empresa es `ccod_empresa`.
