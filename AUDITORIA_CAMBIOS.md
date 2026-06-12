# Reporte de Auditoría de Cambios Realizados
**DatPOS - Sistema de Facturación Electrónica**
*Fecha: 05 de Junio de 2026*

Este documento consolida la auditoría de todos los cambios de código, configuración de base de datos, adición de dependencias y lógica empresarial introducidos en el sistema DatPOS.

---

## 1. Stack Tecnológico de las Funcionalidades

*   **Backend:** PHP 8.x, Microsoft SQL Server (vía extensión `sqlsrv`), JWT custom para autenticación de sesiones multi-tenant.
*   **Frontend:** jQuery 2.1.1, Bootstrap 3, SweetAlert2 (alertas estilizadas).
*   **Servicios y Dependencias Externas:**
    *   **PHPMailer:** Para el envío de facturas por email (configurado en `libs/PHPMailer`).
    *   **FPDF v1.86 (Nueva):** Biblioteca de generación de documentos PDF en el backend (configurada en `libs/fpdf`).
    *   **SimpleZip (Nueva):** Generador binario de archivos ZIP escrito en PHP puro (desarrollado para omitir la dependencia de la extensión `zip` ausente en el servidor local).

---

## 2. Cambios en Base de Datos (SQL Server)

Se utilizaron los siguientes Stored Procedures y consultas para las descargas del módulo **Consultas > Doc. Electrónicos**:

1.  **Stored Procedures de Consulta Recreados:**
    *   `webDatpos_ConsultaTributarioPrincipal`: Reordenado para coincidir exactamente con el orden y claves que el frontend JS espera de las columnas en DataTables (id_cbfact, cdsc_coa, cdoc, cserie, nnumero, ntotal, fecha_emision, cstatus_tributario). Soporta formatos de fecha `DD/MM/YYYY` y `YYYY-MM-DD` mediante conversiones internas seguras.
    *   `webDatpos_DescargarArchivoPDF` / `webDatpos_DescargarArchivoXML` / `webDatpos_DescargarArchivoXMLCDR`: Modificados para devolver 4 columnas esenciales (datos del archivo, `cdoc`, `cserie`, `nnumero`) que permiten al frontend nombrar los archivos de forma dinámica.
2.  **Tablas de SQL Server Consultadas:**
    *   `CbFactura`: Recupera el estado general, datos de venta (totales, IGV, fecha, número) y columnas binarias (`pdf`, `xml`, `xml_cdr`).
    *   `LnFactura`: Recupera el desglose de los ítems de venta (cantidad, precio, importe neto, descripción del artículo, código).
    *   `Coa`: Obtiene la descripción, RUC/DNI y dirección del cliente/adquirente.
    *   `Tiendas`: Obtiene la dirección, teléfono, nombre y correo del local emisor para plasmarlo en las cabeceras de los comprobantes generados.

---

## 3. Módulo 1: Envío de Factura por WhatsApp

*   **Lógica de Negocio y Formato:** Se estructuró el envío de mensajes de WhatsApp formateando el mensaje sin caracteres especiales conflictivos, organizando la información de manera limpia (Serie, Número, Cliente, Fecha, Monto, Detalle).
*   **Tratamiento de Emojis:** Se eliminaron emojis conflictivos o caracteres que causaban errores de codificación en la transmisión por URL-encoding de la API del navegador.
*   **Integración en Ventana de Confirmación:** Se configuró el modal de SweetAlert2 "Operación Completada" para capturar el teléfono celular y disparar la llamada al enlace de API de WhatsApp de forma interactiva cuando la opción esté habilitada (`WhatsApp: ON`).

---

## 4. Módulo 2: Descarga de Comprobantes Electrónicos (3 Formatos)

### Capa de Acceso a Datos (DA)
*   **[NUEVO] `DA/DocumentosDA.php`:**
    *   Métodos `obtenerPdf`, `obtenerXml` y `obtenerCdr` para leer datos binarios/streams directamente de las columnas varbinary de SQL Server.
    *   Método `obtenerDatosComprobante($serie, $correlativo, $objConex)`: Consulta cruzada unificada que extrae los datos de cabecera (`CbFactura`), cliente (`Coa`), detalles (`LnFactura`) y tienda emisora (`Tiendas`) para permitir la renderización bajo demanda.

### Capa de Lógica de Negocio (BL)
*   **[NUEVO] `BL/DocumentosBL.php`:**
    *   Clase puente que encapsula las llamadas de los endpoints hacia la capa de datos (`DocumentosDA`).

### Ayudantes y Generación en Caliente
*   **[NUEVO] `api/documentos/documentos_helper.php`:**
    *   **Clase `SimpleZip`:** Implementa la especificación binaria de archivos ZIP sin compresión (método *Store*). Genera local file headers, central directories y end of central directory records en bytes puros usando `pack()`. Resuelve el error de "carpeta comprimida inválida" de Windows cuando PHP no tiene la extensión `zip` instalada.
    *   **Clase `DocumentosHelper`:**
        *   `generarPdf($header, $details, $tienda)`: Construye un documento PDF tamaño A4 en FPDF con el logotipo o nombre del emisor, RUC, tipo de comprobante, número formateado (`B001-000000XX`), datos de cliente, tabla limpia de productos con sus columnas correspondientes y el bloque de totales (Subtotal, IGV 18%, Total).
        *   `generarXml($header, $details, $tienda)`: Compone un XML con la sintaxis exacta del estándar UBL 2.1 de SUNAT.
        *   `generarCdr($header)`: Crea la Constancia de Recepción de SUNAT en XML (`ApplicationResponse`) y la empaqueta dentro del ZIP usando `SimpleZip`.

### Endpoints del Servidor (API)
*   **[NUEVO] `api/documentos/descargar_pdf.php`:**
    *   Detecta si el archivo PDF almacenado es inválido (no empieza con `%PDF` o contiene el texto plano mock del entorno local de pruebas).
    *   Si es inválido, genera la estructura en caliente mediante `DocumentosHelper::generarPdf()`.
    *   Sirve el resultado directo como descarga adjunta con cabecera `Content-Type: application/pdf` y nombre `{serie}-{correlativo}.pdf`.
*   **[NUEVO] `api/documentos/descargar_xml.php`:**
    *   Detecta placeholders de prueba e interactúa con `DocumentosHelper::generarXml()`.
    *   Sirve con cabeceras `Content-Type: application/xml` y nombre `{serie}-{correlativo}.xml`.
*   **[NUEVO] `api/documentos/descargar_cdr.php`:**
    *   Detecta placeholders de prueba y genera la respuesta ZIP utilizando `DocumentosHelper::generarCdr()`.
    *   Sirve con cabeceras `Content-Type: application/zip` y nombre `R-{serie}-{correlativo}.zip`.

### Vistas y Enrutamiento (Frontend y Rutas)
*   **[MODIFICADO] `router.php`:**
    *   Se le dio soporte al enrutador local de PHP (`php -S`) para reconocer las rutas de descarga limpias sin la extensión `.php` en caso de que sea requerido en producción.
*   **[MODIFICADO] `api/consultadocumento_api.php` (`case 'ConsultaTributarioPrincipal'`):**
    *   Modificada la salida de las columnas PDF, XML y ZIP para renderizar etiquetas HTML `<a>` con clases específicas (`btn-descargar-pdf`, `btn-descargar-xml`, `btn-descargar-cdr`) y atributos `data-serie` y `data-correlativo`.
*   **[MODIFICADO] `assets/Javascript/ReporteTributario.js`:**
    *   Se implementaron manejadores de eventos delegados en jQuery para las descargas.
    *   Realizan una validación rápida previa (`check=1`) vía AJAX al endpoint respectivo para comprobar si el documento existe en la base de datos (o puede ser generado).
    *   Si no existe, bloquea la acción y emite una alerta elegante en pantalla con SweetAlert (`Mensaje('Error', 'Archivo no encontrado', 'error')`), previniendo redirecciones del navegador a pantallas JSON en blanco.
    *   Si existe y es válido, asigna la ruta de descarga directa a `window.location.href` para realizar la descarga fluida.

---

## 5. Hotfixes Especiales de Entorno y Codificación

1.  **Compatibilidad con PHP 8.2+:**
    *   En `documentos_helper.php`, se reemplazó la llamada a la función obsoleta/depreciada `utf8_decode()` por codificación robusta basada en la extensión `iconv` (`iconv('UTF-8', 'windows-1252//TRANSLIT', $str)`). Esto elimina por completo los warnings de PHP en los logs.
2.  **Soporte de placeholders de base de datos:**
    *   Se diseñó una validación defensiva en la que si el contenido binario devuelto por SQL Server contiene las cadenas de texto del script de mock (`Mock PDF`, `Mock XML Content` o `Mock ZIP`), se tratan dinámicamente como inexistentes y se fuerza la generación inmediata de archivos con datos del comprobante para evitar entregar archivos corruptos al usuario.

---
*Fin de la Auditoría.*
