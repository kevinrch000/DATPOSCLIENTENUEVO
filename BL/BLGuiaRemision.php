<?php
/**
 * DatPOS - Business Logic: GuiaRemision
 * Reemplaza: BL/BLGuiaRemision.vb
 */

require_once __DIR__ . '/../DA/DAGuiaRemision.php';

class BLGuiaRemision
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DAGuiaRemision();
    }

    public function ConsultarGuiaRemision($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarGuiaRemision($ccod_cia, $objConex);
    }

    public function ConsultarOperaciones($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarOperaciones($ccod_cia, $objConex);
    }

    public function ConsultarAlmacenes($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarAlmacenes($ccod_cia, $objConex);
    }

    public function ObtenerNumerador($ccod_cia, $objConex)
    {
        return $this->objDA->ObtenerNumerador($ccod_cia, $objConex);
    }

    public function ConsultarNumerador($ccod_cia, $ccod_alm, $objConex)
    {
        return $this->objDA->ConsultarNumerador($ccod_cia, $ccod_alm, $objConex);
    }

    public function ConsultarCodigoAuxiliar($ccod_cia, $codigo, $objConex)
    {
        return $this->objDA->ConsultarCodigoAuxiliar($ccod_cia, $codigo, $objConex);
    }

    public function ObtenerDetalleGuiaRemision($ccod_cia, $id_cbguia, $objConex)
    {
        return $this->objDA->ObtenerDetalleGuiaRemision($ccod_cia, $id_cbguia, $objConex);
    }

    public function ObtenerGuiaRemision($ccod_cia, $id_cbguia, $objConex)
    {
        return $this->objDA->ObtenerGuiaRemision($ccod_cia, $id_cbguia, $objConex);
    }

    public function ConsultarArticulosSalida($ccod_cia, $texto, $objConex)
    {
        return $this->objDA->ConsultarArticulosSalida($ccod_cia, $texto, $objConex);
    }

    public function InsertarGuiaRemision($ccod_cia, $ccod_alm, $ccod_tipooper, $dfch_doc, $serie, $numero, $ccod_coa, $direccion, $motivo, $cusu_crea, $objConex)
    {
        return $this->objDA->InsertarGuiaRemision($ccod_cia, $ccod_alm, $ccod_tipooper, $dfch_doc, $serie, $numero, $ccod_coa, $direccion, $motivo, $cusu_crea, $objConex);
    }
}
?>