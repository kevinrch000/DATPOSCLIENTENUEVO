<?php
/**
 * DatPOS - Business Logic: NotaDebito
 * Reemplaza: BL/BLNotaDebito.vb
 */

require_once __DIR__ . '/../DA/DANotaDebito.php';

class BLNotaDebito
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DANotaDebito();
    }

    public function GenerarNotaDebito($id_cbfact, $ccod_usuario, $nmonto_aplicado, $ccod_cia, $objConex)
    {
        return $this->objDA->GenerarNotaDebito($id_cbfact, $ccod_usuario, $nmonto_aplicado, $ccod_cia, $objConex);
    }
}
?>