<?php
/**
 * DatPOS - Data Access: Documentos
 * Permite descargar archivos PDF, XML y CDR desde la tabla CbFactura del tenant
 */

require_once __DIR__ . '/../config/database.php';

class DocumentosDA {
    
    /**
     * Helper para leer datos de columnas binarias o texto que sqlsrv pueda retornar como recursos
     */
    private function getFieldData($val) {
        if (is_resource($val)) {
            return stream_get_contents($val);
        }
        return $val;
    }

    /**
     * Obtener el archivo PDF binario de un comprobante
     */
    public function obtenerPdf($serie, $correlativo, $objConex) {
        $conn = Database::getTenantConnection($objConex);
        if (!$conn) {
            error_log("DocumentosDA::obtenerPdf - Error de conexión al tenant.");
            return null;
        }
        
        $sql = "SELECT pdf FROM CbFactura WHERE ccod_cia = ? AND cserie = ? AND nnumero = ?";
        $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $serie, $correlativo));
        $data = null;
        if ($stmt) {
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $data = $this->getFieldData($row[0]);
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("DocumentosDA::obtenerPdf - Error en query: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_close($conn);
        return $data;
    }

    /**
     * Obtener el archivo XML de un comprobante
     */
    public function obtenerXml($serie, $correlativo, $objConex) {
        $conn = Database::getTenantConnection($objConex);
        if (!$conn) {
            error_log("DocumentosDA::obtenerXml - Error de conexión al tenant.");
            return null;
        }
        
        $sql = "SELECT xml FROM CbFactura WHERE ccod_cia = ? AND cserie = ? AND nnumero = ?";
        $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $serie, $correlativo));
        $data = null;
        if ($stmt) {
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $data = $this->getFieldData($row[0]);
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("DocumentosDA::obtenerXml - Error en query: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_close($conn);
        return $data;
    }

    /**
     * Obtener la constancia de recepción CDR (ZIP) de un comprobante
     */
    public function obtenerCdr($serie, $correlativo, $objConex) {
        $conn = Database::getTenantConnection($objConex);
        if (!$conn) {
            error_log("DocumentosDA::obtenerCdr - Error de conexión al tenant.");
            return null;
        }
        
        $sql = "SELECT xml_cdr FROM CbFactura WHERE ccod_cia = ? AND cserie = ? AND nnumero = ?";
        $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $serie, $correlativo));
        $data = null;
        if ($stmt) {
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $data = $this->getFieldData($row[0]);
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("DocumentosDA::obtenerCdr - Error en query: " . print_r(sqlsrv_errors(), true));
        }
        sqlsrv_close($conn);
        return $data;
    }

    /**
     * Obtener todos los datos necesarios (cabecera, detalles y tienda) para generar los archivos en caliente
     */
    public function obtenerDatosComprobante($serie, $correlativo, $objConex) {
        $conn = Database::getTenantConnection($objConex);
        if (!$conn) {
            error_log("DocumentosDA::obtenerDatosComprobante - Error de conexión al tenant.");
            return null;
        }

        // 1. Obtener cabecera y datos de la tienda y cliente
        $sqlHeader = "SELECT F.id_cbfact, F.cdoc, F.cserie, F.nnumero, F.fecha_emision,
                             F.nsubtotal, F.nimpuesto, F.ntotal, F.nvuelto, F.ntot_entreg, F.cobs,
                             C.cdsc_coa, C.cruc_coa, C.cdirc_coa, F.ccod_tiend, F.ccod_caja,
                             T.cnombr, T.cdirec, T.ctelef, T.cmail
                      FROM CbFactura F
                      LEFT JOIN Coa C ON C.ccod_cia = F.ccod_cia AND C.ccod_coa = F.ccod_coa
                      LEFT JOIN Tiendas T ON T.ccod_cia = F.ccod_cia AND T.ccod_tiend = F.ccod_tiend
                      WHERE F.ccod_cia = ? AND F.cserie = ? AND F.nnumero = ?";
        
        $stmtHeader = sqlsrv_query($conn, $sqlHeader, array($objConex->ccod_empresa, $serie, $correlativo));
        $header = null;
        if ($stmtHeader) {
            if ($row = sqlsrv_fetch_array($stmtHeader, SQLSRV_FETCH_ASSOC)) {
                $header = $row;
            }
            sqlsrv_free_stmt($stmtHeader);
        } else {
            error_log("DocumentosDA::obtenerDatosComprobante - Error en cabecera: " . print_r(sqlsrv_errors(), true));
        }

        if (!$header) {
            sqlsrv_close($conn);
            return null;
        }

        // 2. Obtener detalles de la factura
        $sqlDetails = "SELECT id_articulo, cdsc_articulo, nprecio, ncantidad, nimporte_neto
                       FROM LnFactura
                       WHERE ccod_cia = ? AND id_cbfact = ?
                       ORDER BY corden ASC, id_lnfact ASC";
        
        $stmtDetails = sqlsrv_query($conn, $sqlDetails, array($objConex->ccod_empresa, $header['id_cbfact']));
        $details = array();
        if ($stmtDetails) {
            while ($row = sqlsrv_fetch_array($stmtDetails, SQLSRV_FETCH_ASSOC)) {
                $details[] = $row;
            }
            sqlsrv_free_stmt($stmtDetails);
        } else {
            error_log("DocumentosDA::obtenerDatosComprobante - Error en detalles: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);

        return array(
            'header' => $header,
            'details' => $details
        );
    }
}
?>
