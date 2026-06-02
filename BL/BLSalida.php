<?php
/**
 * DatPOS - Business Logic: Salida
 * Reemplaza: BL/BLSalida.vb
 */

require_once __DIR__ . '/../DA/DASalida.php';

class BLSalida
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DASalida();
    }

    public function ConsultarSalidas($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarSalidas($ccod_cia, $objConex);
    }

    public function ConsultarSalida($ccod_cia, $codigo, $objConex)
    {
        return $this->objDA->ConsultarSalida($ccod_cia, $codigo, $objConex);
    }

    public function ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex)
    {
        return $this->objDA->ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex);
    }

    public function ConsultarArticulosSalida($ccod_cia, $almacen, $objConex)
    {
        return $this->objDA->ConsultarArticulosSalida($ccod_cia, $almacen, $objConex);
    }

    public function ConsultarAlmacenes($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarAlmacenes($ccod_cia, $objConex);
    }

    public function ConsultarNumerador($ccod_cia, $ccod_alm, $objConex)
    {
        return $this->objDA->ConsultarNumerador($ccod_cia, $ccod_alm, $objConex);
    }

    public function InsertarSalida($ccod_cia, $ccod_tienda, $ccod_alm, $dfecha, $ctipo, $vserie, $vobservacion, $ccod_usuario, $objConex)
    {
        return $this->objDA->InsertarSalida($ccod_cia, $ccod_tienda, $ccod_alm, $dfecha, $ctipo, $vserie, $vobservacion, $ccod_usuario, $objConex);
    }

    public function EliminarSalida($ccod_cia, $id_cbinve, $objConex)
    {
        return $this->objDA->EliminarSalida($ccod_cia, $id_cbinve, $objConex);
    }
}
?>