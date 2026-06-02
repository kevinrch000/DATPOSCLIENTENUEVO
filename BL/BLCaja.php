<?php
/**
 * DatPOS - Business Logic: Caja
 * Reemplaza: BL/BLCaja.vb
 */

require_once __DIR__ . '/../DA/DACaja.php';

class BLCaja
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DACaja();
    }

    public function ConsultarCajas($ccod_empresa, $objConex)
    {
        return $this->objDA->ConsultarCajas($ccod_empresa, $objConex);
    }

    public function ConsultarCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return $this->objDA->ConsultarCaja($ccod_empresa, $ccod_caja, $objConex);
    }

    public function ConsultarCajasDispo($ccod_empresa, $ccod_tiend, $objConex)
    {
        return $this->objDA->ConsultarCajasDispo($ccod_empresa, $ccod_tiend, $objConex);
    }

    public function ConsultarCajasEmpActivos($ccod_empresa, $objConex)
    {
        return $this->objDA->ConsultarCajasEmpActivos($ccod_empresa, $objConex);
    }

    public function ConsultarCajasActivos($ccod_empresa, $objConex)
    {
        return $this->objDA->ConsultarCajasActivos($ccod_empresa, $objConex);
    }

    public function ConsultarTiendaCajas($ccod_empresa, $ccod_tiend, $objConex)
    {
        return $this->objDA->ConsultarTiendaCajas($ccod_empresa, $ccod_tiend, $objConex);
    }

    public function LimpiarTiendasCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return $this->objDA->LimpiarTiendasCaja($ccod_empresa, $ccod_caja, $objConex);
    }

    public function AsignarTiendaCaja($ccod_empresa, $ccod_tiend, $ccod_caja, $ccod_usuario, $objConex)
    {
        return $this->objDA->AsignarTiendaCaja($ccod_empresa, $ccod_tiend, $ccod_caja, $ccod_usuario, $objConex);
    }

    public function EditarCaja($ccod_empresa, $ccod_caja, $cdesc_caja, $ccod_sunat, $cserial, $ctip_doc, $nnum_ini, $cestado, $objConex)
    {
        return $this->objDA->EditarCaja($ccod_empresa, $ccod_caja, $cdesc_caja, $ccod_sunat, $cserial, $ctip_doc, $nnum_ini, $cestado, $objConex);
    }

    public function EliminarCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return $this->objDA->EliminarCaja($ccod_empresa, $ccod_caja, $objConex);
    }

    public function ConsultarNumeradores($ccod_cia, $ccod_caja, $objConex)
    {
        return $this->objDA->ConsultarNumeradores($ccod_cia, $ccod_caja, $objConex);
    }

    public function EliminarNumeradores($ccod_cia, $ccod_caja, $objConex)
    {
        return $this->objDA->EliminarNumeradores($ccod_cia, $ccod_caja, $objConex);
    }
}
?>