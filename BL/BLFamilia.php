<?php
/**
 * DatPOS - Business Logic: Familia
 * Reemplaza: BL/BLFamilia.vb
 */

require_once __DIR__ . '/../DA/DAFamilia.php';

class BLFamilia {

    private $objDA;

    public function __construct() {
        $this->objDA = new DAFamilia();
    }

    public function consultarFamiliasActivas($objConex) {
        return $this->objDA->consultarFamiliasActivas($objConex);
    }

    public function consultarFamilias($objConex) {
        return $this->objDA->consultarFamilias($objConex);
    }

    public function consultarFamilia($codigo, $objConex) {
        return $this->objDA->consultarFamilia($codigo, $objConex);
    }

    public function insertarFamilia($objBE, $objConex) {
        return $this->objDA->insertarFamilia($objBE, $objConex);
    }

    public function editarFamilia($objBE, $objConex) {
        return $this->objDA->editarFamilia($objBE, $objConex);
    }

    public function eliminarFamilia($cod, $objConex) {
        return $this->objDA->eliminarFamilia($cod, $objConex);
    }
}
?>
