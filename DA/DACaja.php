<?php
/**
 * DatPOS - Data Access: Caja
 * Reemplaza: DA/DACaja.vb
 */

require_once __DIR__ . '/../config/database.php';

class DACaja
{
    public function ConsultarCajas($ccod_empresa, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarcajas', array(
            '@ccod_empresa' => $ccod_empresa
        ), $objConex);
    }

    public function ConsultarCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarcaja', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }

    public function ConsultarCajasDispo($ccod_empresa, $ccod_tiend, $objConex)
    {
        return Database::selectStoredTenant('sp_consultacajasdispo', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_tiend' => $ccod_tiend
        ), $objConex);
    }

    public function ConsultarCajasEmpActivos($ccod_empresa, $objConex)
    {
        return Database::selectStoredTenant('sp_consultacajasempactivos', array(
            '@ccod_empresa' => $ccod_empresa
        ), $objConex);
    }

    public function ConsultarCajasActivos($ccod_empresa, $objConex)
    {
        return Database::selectStoredTenant('sp_consultacajasactivos', array(
            '@ccod_empresa' => $ccod_empresa
        ), $objConex);
    }

    public function ConsultarTiendaCajas($ccod_empresa, $ccod_tiend, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartiendacajas', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_tiend' => $ccod_tiend
        ), $objConex);
    }

    public function LimpiarTiendasCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_limpiartiendascaja', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }

    public function AsignarTiendaCaja($ccod_empresa, $ccod_tiend, $ccod_caja, $ccod_usuario, $objConex)
    {
        return Database::selectStoredTenant('sp_asignartiendacaja', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_tiend' => $ccod_tiend,
            '@ccod_caja' => $ccod_caja,
            '@ccod_usuario' => $ccod_usuario
        ), $objConex);
    }

    public function EditarCaja($ccod_empresa, $ccod_caja, $cdesc_caja, $ccod_sunat, $cserial, $ctip_doc, $nnum_ini, $cestado, $objConex)
    {
        return Database::selectStoredTenant('sp_editarcaja', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_caja' => $ccod_caja,
            '@cdesc_caja' => $cdesc_caja,
            '@ccod_sunat' => $ccod_sunat,
            '@cserial' => $cserial,
            '@ctip_doc' => $ctip_doc,
            '@nnum_ini' => $nnum_ini,
            '@cestado' => $cestado
        ), $objConex);
    }

    public function EliminarCaja($ccod_empresa, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_eliminarcaja', array(
            '@ccod_empresa' => $ccod_empresa,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }

    public function ConsultarNumeradores($ccod_cia, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarnumeradores', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }

    public function EliminarNumeradores($ccod_cia, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_eliminarnumeradores', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }
}
?>