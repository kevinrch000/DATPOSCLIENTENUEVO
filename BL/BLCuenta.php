<?php
/**
 * DatPOS - Business Logic: Cuenta
 * Reemplaza: BL/BLCuenta.vb
 */

require_once __DIR__ . '/../DA/DACuenta.php';

class BLCuenta
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DACuenta();
    }

    public function InsertarCuenta($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $objConex)
    {
        return $this->objDA->InsertarCuenta($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $objConex);
    }

    public function InsertarCuentaCompleto($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $ntot_desct, $ntot_impbruto, $ntot_igv, $ntot_impneto, $objConex)
    {
        return $this->objDA->InsertarCuentaCompleto($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $ntot_desct, $ntot_impbruto, $ntot_igv, $ntot_impneto, $objConex);
    }

    public function ConsultarCuentas($ccod_cia, $ccod_tiend, $ccod_caja, $ctip_cuenta, $objConex)
    {
        return $this->objDA->ConsultarCuentas($ccod_cia, $ccod_tiend, $ccod_caja, $ctip_cuenta, $objConex);
    }

    public function ConsultarCuentaDetalles($id_cbcuenta, $objConex)
    {
        return $this->objDA->ConsultarCuentaDetalles($id_cbcuenta, $objConex);
    }

    public function ConsultarCuentaDetallesFull($id_cbcuenta, $objConex)
    {
        return $this->objDA->ConsultarCuentaDetallesFull($id_cbcuenta, $objConex);
    }
}
?>