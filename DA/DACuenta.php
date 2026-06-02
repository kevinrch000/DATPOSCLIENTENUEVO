<?php
/**
 * DatPOS - Data Access: Cuenta
 * Reemplaza: DA/DACuenta.vb
 */

require_once __DIR__ . '/../config/database.php';

class DACuenta
{
    public function InsertarCuenta($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $objConex)
    {
        return Database::selectStoredTenant('sp_insertarcuenta', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_coa' => $ccod_coa,
            '@ccod_tiend' => $ccod_tiend,
            '@ccod_caja' => $ccod_caja,
            '@etiqueta' => $etiqueta,
            '@ccod_usuario' => $ccod_usuario,
            '@ctip_cuenta' => $ctip_cuenta
        ), $objConex);
    }

    public function InsertarCuentaCompleto($ccod_cia, $ccod_coa, $ccod_tiend, $ccod_caja, $etiqueta, $ccod_usuario, $ctip_cuenta, $ntot_desct, $ntot_impbruto, $ntot_igv, $ntot_impneto, $objConex)
    {
        return Database::selectStoredTenant('sp_lsinsertarcuenta', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_coa' => $ccod_coa,
            '@ccod_tiend' => $ccod_tiend,
            '@ccod_caja' => $ccod_caja,
            '@etiqueta' => $etiqueta,
            '@ccod_usuario' => $ccod_usuario,
            '@ctip_cuenta' => $ctip_cuenta,
            '@ntot_desct' => $ntot_desct,
            '@ntot_impbruto' => $ntot_impbruto,
            '@ntot_igv' => $ntot_igv,
            '@ntot_impneto' => $ntot_impneto
        ), $objConex);
    }

    public function ConsultarCuentas($ccod_cia, $ccod_tiend, $ccod_caja, $ctip_cuenta, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarcuentas', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_tiend' => $ccod_tiend,
            '@ccod_caja' => $ccod_caja,
            '@ctip_cuenta' => $ctip_cuenta
        ), $objConex);
    }

    public function ConsultarCuentaDetalles($id_cbcuenta, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarcuentadetalles', array(
            '@id_cbcuenta' => $id_cbcuenta
        ), $objConex);
    }

    public function ConsultarCuentaDetallesFull($id_cbcuenta, $objConex)
    {
        return Database::selectStoredTenant('sp_lsconsultarcuentadetalles', array(
            '@id_cbcuenta' => $id_cbcuenta
        ), $objConex);
    }
}
?>