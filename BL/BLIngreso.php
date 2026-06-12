<?php
/**
 * DatPOS - Business Logic: Ingreso
 * Reemplaza: BL/BLIngreso.vb
 */

require_once __DIR__ . '/../DA/DAIngreso.php';

class BLIngreso
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DAIngreso();
    }

    public function ConsultarIngresos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarIngresos($ccod_cia, $objConex);
    }

    public function ConsultarIngreso($ccod_cia, $codigo, $objConex)
    {
        return $this->objDA->ConsultarIngreso($ccod_cia, $codigo, $objConex);
    }

    public function ConsultarInventarioDetalle($ccod_cia, $id, $objConex)
    {
        return $this->objDA->ConsultarInventarioDetalle($ccod_cia, $id, $objConex);
    }

    public function ConsultarArticulos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarArticulos($ccod_cia, $objConex);
    }

    public function ConsultarProveedor($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarProveedor($ccod_cia, $objConex);
    }

    public function ValidarArticulo($ccod_cia, $ccod_articulo, $objConex)
    {
        return $this->objDA->ValidarArticulo($ccod_cia, $ccod_articulo, $objConex);
    }

    public function ConsultarAlmEmpActivos($ccod_cia, $ccod_tiend, $objConex)
    {
        return $this->objDA->ConsultarAlmEmpActivos($ccod_cia, $ccod_tiend, $objConex);
    }

    public function ConsultarNumerador($ccod_cia, $ccod_alm, $objConex)
    {
        return $this->objDA->ConsultarNumerador($ccod_cia, $ccod_alm, $objConex);
    }

    public function ObtenerIvg($ccod_cia, $objConex)
    {
        return $this->objDA->ObtenerIvg($ccod_cia, $objConex);
    }

    public function InsertarIngreso($ccod_cia, $ccod_tienda, $ccod_alm, $dfecha, $ctipo, $vserie, $vobservacion, $ccod_usuario, $objConex)
    {
        return $this->objDA->InsertarIngreso($ccod_cia, $ccod_tienda, $ccod_alm, $dfecha, $ctipo, $vserie, $vobservacion, $ccod_usuario, $objConex);
    }

    public function EliminarIngreso($ccod_cia, $id_cbinve, $objConex)
    {
        return $this->objDA->EliminarIngreso($ccod_cia, $id_cbinve, $objConex);
    }
}
?>