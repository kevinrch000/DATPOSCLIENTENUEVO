<?php
/**
 * DatPOS - Data Access: Empresa
 * Reemplaza: DA/DAEmpresa.vb (subset usado por Facturación)
 *
 * Stored Procedures:
 *   - sp_consultarimpuestos (@ccod_cia)
 */

require_once __DIR__ . '/../config/database.php';

class DAEmpresa
{
    public function ConsultarImpuestos($objConex)
    {
        return Database::selectStoredTenant('sp_consultarimpuestos', array(
            '@ccod_cia' => $objConex->ccod_empresa
        ), $objConex);
    }
}
?>
