<?php
/**
 * DatPOS - Data Access: Tienda
 * Reemplaza: DA/DATienda.vb (subset usado por Facturación)
 *
 * Stored Procedures:
 *   - sp_consultartienda         (@ccod_cia, @ccod_tienda)
 *   - webDatpos_consultartiendas (@ccod_cia)
 *   - sp_consultartiendasactivas (@ccod_cia)
 */

require_once __DIR__ . '/../config/database.php';

class DATienda
{
    public function ConsultarTienda($codigo, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartienda', array(
            '@ccod_empresa' => $objConex->ccod_empresa,
            '@ccod_tiend'   => $codigo
        ), $objConex);
    }

    public function ConsultarTiendas($objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultartiendas', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    public function ConsultarTiendasActivas($objConex)
    {
        return Database::selectStoredTenant('sp_consultartiendasactivas', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }
}
?>
