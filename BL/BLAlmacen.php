<?php
/**
 * DatPOS - Business Logic: Almacen
 * Reemplaza: BL/BLAlmacen.vb
 */
require_once __DIR__ . '/../DA/DAAlmacen.php';

class BLAlmacen {
    private $objDA;

    public function __construct() {
        $this->objDA = new DAAlmacen();
    }

    public function consultarAlmacenes($objConex) {
        return $this->objDA->consultarAlmacenes($objConex);
    }

    public function consultarAlmacen($codigo, $objConex) {
        return $this->objDA->consultarAlmacen($codigo, $objConex);
    }

    public function consultarAlmacenesActivos($objConex) {
        return $this->objDA->consultarAlmacenesActivos($objConex);
    }

    public function insertarAlmacen($objBE, $numeradores, $objConex) {
        return $this->objDA->insertarAlmacen($objBE, $numeradores, $objConex);
    }

    public function editarAlmacen($objBE, $numeradores, $objConex) {
        return $this->objDA->editarAlmacen($objBE, $numeradores, $objConex);
    }

    public function eliminarAlmacen($cod, $objConex) {
        return $this->objDA->eliminarAlmacen($cod, $objConex);
    }

    public function consultarNumerador($almacen, $objConex) {
        return $this->objDA->consultarNumerador($almacen, $objConex);
    }
}
?>
