<?php
/**
 * DatPOS - Data Access: Cliente
 * Reemplaza: DA/DACliente.vb (subset usado por Facturación)
 *
 * Stored Procedures:
 *   - sp_clientepordefecto       (@ccod_cia)            ← solo 1 parámetro en VB
 *   - sp_consultarclientestodos  (@ccod_cia, @texto, @ccod_usuario, @tipodoc)
 *   - webDatpos_cargarClientePredeterminado (@ccod_cia)
 *   - sp_consultaclientes        (@ccod_cia)
 *   - webDatpos_ConsultaCliente  (@codigo, @ccod_cia)
 */

require_once __DIR__ . '/../config/database.php';

class DACliente
{
    public function ClientePorDefecto($objConex)
    {
        return Database::selectStoredTenant('sp_clientepordefecto', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    public function CargarClientePredeterminado($objConex)
    {
        return Database::selectStoredTenant('webDatpos_cargarClientePredeterminado', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    public function ConsultarClientesTodos($texto, $tipodoc, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarclientestodos', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@texto' => $texto,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@tipodoc' => $tipodoc
        ), $objConex);
    }

    public function ConsultarClientes($objConex)
    {
        return Database::selectStoredTenant('sp_consultaclientes', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }

    public function ConsultarCliente($codigo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultaCliente', array(
            '@codigo' => $codigo,
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }
}
?>
