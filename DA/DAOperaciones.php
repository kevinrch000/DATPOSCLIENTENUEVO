<?php
/**
 * DatPOS - Data Access: Operaciones
 * Reemplaza: DA/DAOperaciones.vb
 */

require_once __DIR__ . '/../config/database.php';

class DAOperaciones
{
    public function ConsultarTipoOperacion($ccod_cia, $ccod_tipoper, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartipooperacion', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_tipoper' => $ccod_tipoper
        ), $objConex);
    }

    public function ConsultarTiposOperacion($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartiposoperacion', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTiposOperacionActivosIngresos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartiposoperacionactivosingresos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarOperTransferencia($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarOperTransferencia', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTiposDocumentoPago($ccod_cia, $ccod_caja, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartiposdocumentopago', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_caja' => $ccod_caja
        ), $objConex);
    }

    public function ConsultarTipoDocumento($objConex)
    {
        return Database::selectStoredTenant('sp_consultatipodocumento', array(), $objConex);
    }

    public function ConsultarIngresos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultaringresos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarSalidas($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarSalidas', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarSalida($ccod_cia, $codigo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@codigo' => $codigo
        ), $objConex);
    }

    public function ConsultarTransferencias($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarTransferencias', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTransferencia($ccod_cia, $codigo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarTransferencia', array(
            '@ccod_cia' => $ccod_cia,
            '@codigo' => $codigo
        ), $objConex);
    }

    public function ConsultarProveedor($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarProveedor', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarVentasTienda($ccod_cia, $ccod_tienda, $ccod_caja, $fchDesde, $fchHasta, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarVentasTienda', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_tienda' => $ccod_tienda,
            '@ccod_caja' => $ccod_caja,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta
        ), $objConex);
    }

    public function ConsultarArticulosSalida($ccod_cia, $almacen, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarArticulosSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@almacen' => $almacen
        ), $objConex);
    }

    public function ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarInventarioDetalleSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@id' => $id
        ), $objConex);
    }
}
?>