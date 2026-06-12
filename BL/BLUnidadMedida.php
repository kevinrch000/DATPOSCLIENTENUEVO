<?php
/**
 * DatPOS - Business Logic: UnidadMedida
 * Reemplaza: BL/BLUnidadMedida.vb
 */

require_once __DIR__ . '/../DA/DAUnidadMedida.php';

class BLUnidadMedida {

    private $objDA;

    public function __construct() {
        $this->objDA = new DAUnidadMedida();
    }

    public function consultarCodigoUnidadMedida($codigo, $objConex) {
        return $this->objDA->consultarCodigoUnidadMedida($codigo, $objConex);
    }

    public function consultarUnidadMedida($objConex) {
        return $this->objDA->consultarUnidadMedida($objConex);
    }

    public function eliminarUnidadMedida($codigo, $objConex) {
        return $this->objDA->eliminarUnidadMedida($codigo, $objConex);
    }

    public function insertarUnidadMedida($objBE, $objConex) {
        return $this->objDA->insertarUnidadMedida($objBE, $objConex);
    }

    public function editarUnidadMedida($objBE, $objConex) {
        return $this->objDA->editarUnidadMedida($objBE, $objConex);
    }
}
?>
