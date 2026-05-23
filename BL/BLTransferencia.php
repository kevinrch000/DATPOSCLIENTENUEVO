<?php
/**
 * DatPOS - Business Logic: Transferencia
 * Reemplaza: BL/BLTransferencia.vb
 */

require_once __DIR__ . '/../DA/DATransferencia.php';

class BLTransferencia
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DATransferencia();
    }

    public function ConsultarTransferencias($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarTransferencias($ccod_cia, $objConex);
    }

    public function ConsultarTransferencia($ccod_cia, $id_cbtransf, $objConex)
    {
        return $this->objDA->ConsultarTransferencia($ccod_cia, $id_cbtransf, $objConex);
    }

    public function ConsultarDetalleTransferencia($ccod_cia, $id_cbtransf, $objConex)
    {
        return $this->objDA->ConsultarDetalleTransferencia($ccod_cia, $id_cbtransf, $objConex);
    }

    public function ConsultarArticulosTransferencia($ccod_cia, $texto, $objConex)
    {
        return $this->objDA->ConsultarArticulosTransferencia($ccod_cia, $texto, $objConex);
    }

    public function ConsultarAlmEmpActivos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarAlmEmpActivos($ccod_cia, $objConex);
    }

    public function InsertarTransferencia($ccod_cia, $ccod_tienda, $ccod_almOrigen, $ccod_almDestino, $dfecha, $vobservacion, $ccod_usuario, $ntotal, $objConex)
    {
        return $this->objDA->InsertarTransferencia($ccod_cia, $ccod_tienda, $ccod_almOrigen, $ccod_almDestino, $dfecha, $vobservacion, $ccod_usuario, $ntotal, $objConex);
    }
}
?>