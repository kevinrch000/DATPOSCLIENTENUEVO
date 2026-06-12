<?php
/**
 * DatPOS - Business Logic: Documentos
 */

require_once __DIR__ . '/../DA/DocumentosDA.php';

class DocumentosBL {
    private $da;

    public function __construct() {
        $this->da = new DocumentosDA();
    }

    /**
     * Obtener PDF
     */
    public function obtenerPdf($serie, $correlativo, $objConex) {
        return $this->da->obtenerPdf($serie, $correlativo, $objConex);
    }

    /**
     * Obtener XML
     */
    public function obtenerXml($serie, $correlativo, $objConex) {
        return $this->da->obtenerXml($serie, $correlativo, $objConex);
    }

    /**
     * Obtener CDR
     */
    public function obtenerCdr($serie, $correlativo, $objConex) {
        return $this->da->obtenerCdr($serie, $correlativo, $objConex);
    }

    /**
     * Obtener datos completos de comprobante
     */
    public function obtenerDatosComprobante($serie, $correlativo, $objConex) {
        return $this->da->obtenerDatosComprobante($serie, $correlativo, $objConex);
    }
}
?>
