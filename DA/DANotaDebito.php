<?php
/**
 * DatPOS - Data Access: NotaDebito
 * Reemplaza: DA/DANotaDebito.vb
 */

require_once __DIR__ . '/../config/database.php';

class DANotaDebito
{
    public function GenerarNotaDebito($id_cbfact, $ccod_usuario, $nmonto_aplicado, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_generarNotaDebito', array(
            '@id_cbfact' => $id_cbfact,
            '@ccod_usuario' => $ccod_usuario,
            '@nmonto_aplicado' => $nmonto_aplicado,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }
}
?>