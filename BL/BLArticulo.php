<?php
/**
 * DatPOS - Business Logic: Articulo
 * Reemplaza: BL/BLArticulo.vb
 */
require_once __DIR__ . '/../DA/DAArticulo.php';

class BLArticulo {
    private $objDA;

    public function __construct() {
        $this->objDA = new DAArticulo();
    }

    public function consultarArticulos($objConex) {
        return $this->objDA->consultarArticulos($objConex);
    }

    public function consultarArticulo($codigo, $objConex) {
        return $this->objDA->consultarArticulo($codigo, $objConex);
    }

    public function consultarVarianteArticulo($codigo, $objConex) {
        return $this->objDA->consultarVarianteArticulo($codigo, $objConex);
    }

    public function consultarDetalleVarianteArticulo($ccod_articulo, $objConex) {
        return $this->objDA->consultarDetalleVarianteArticulo($ccod_articulo, $objConex);
    }

    public function insertarArticulo($objBE, $objConex, $cabVariantes, $detVariantes) {
        return $this->objDA->insertarArticulo($objBE, $objConex, $cabVariantes, $detVariantes);
    }

    public function editarArticulo($objBE, $objConex, $cabVariantes, $detVariantes) {
        return $this->objDA->editarArticulo($objBE, $objConex, $cabVariantes, $detVariantes);
    }

    public function eliminarArticuloUsuario($cabVariantes, $articulo, $objConex) {
        return $this->objDA->eliminarArticulo($cabVariantes, $articulo, $objConex);
    }
}
?>
