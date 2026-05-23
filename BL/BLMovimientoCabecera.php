<?php
/**
 * DatPOS - Business Logic: MovimientoCabecera
 * Reemplaza: BL/BLMovimientoCabecera.vb
 */

require_once __DIR__ . '/../DA/DAMovimientoCabecera.php';

class BLMovimientoCabecera
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DAMovimientoCabecera();
    }

    public function ValidarFacturacion($objConex)
    {
        return $this->objDA->ValidarFacturacion($objConex);
    }

    public function ValidarAlFacturar($cdoc, $objConex)
    {
        return $this->objDA->ValidarAlFacturar($cdoc, $objConex);
    }

    public function ObtenerIGV($objConex)
    {
        return $this->objDA->ObtenerIGV($objConex);
    }

    public function InsertarMovimientoCabecera($objBE, $objBED, $objConex, $cantidad_bienes, $objCobranzaDetalle, $id_apertura)
    {
        return $this->objDA->InsertarMovimientoCabecera($objBE, $objBED, $objConex, $cantidad_bienes, $objCobranzaDetalle, $id_apertura);
    }

    public function ConsultarSunatFactura($id, $objConex)
    {
        return $this->objDA->ConsultarSunatFactura($id, $objConex);
    }

    public function ConsultarSunatFacturaDetalle($id, $objConex)
    {
        return $this->objDA->ConsultarSunatFacturaDetalle($id, $objConex);
    }

    public function ConsultarDocumentoCabecera($id, $objConex)
    {
        return $this->objDA->ConsultarDocumentoCabecera($id, $objConex);
    }

    public function ConsultarDocumentoDetalle($id, $objConex)
    {
        return $this->objDA->ConsultarDocumentoDetalle($id, $objConex);
    }

    public function ConsultarDocumentoCobranza($id, $objConex)
    {
        return $this->objDA->ConsultarDocumentoCobranza($id, $objConex);
    }

    public function InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $objConex)
    {
        return $this->objDA->InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $objConex);
    }

    public function ConsultaDocumentosSunat($objconsulta, $obj)
    {
        return $this->objDA->ConsultaDocumentosSunat($objconsulta, $obj);
    }

    public function AnularDocumento($id_cbfact, $motivo, $objConex)
    {
        return $this->objDA->AnularDocumento($id_cbfact, $motivo, $objConex);
    }

    public function ActualizarStock($ccod_cia, $ccod_alm, $ccod_articulo, $ncantidad, $ncosto, $signo, $objConex)
    {
        return $this->objDA->ActualizarStock($ccod_cia, $ccod_alm, $ccod_articulo, $ncantidad, $ncosto, $signo, $objConex);
    }

    public function ValidarStockArticulos($ccod_cia, $ccod_alm, $producto, $objConex)
    {
        return $this->objDA->ValidarStockArticulos($ccod_cia, $ccod_alm, $producto, $objConex);
    }
}
?>