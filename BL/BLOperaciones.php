<?php
/**
 * DatPOS - Business Logic: Operaciones
 * Reemplaza: BL/BLOperaciones.vb
 */

require_once __DIR__ . '/../DA/DAOperaciones.php';

class BLOperaciones
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DAOperaciones();
    }

    public function ConsultarTipoOperacion($ccod_cia, $ccod_tipoper, $objConex)
    {
        return $this->objDA->ConsultarTipoOperacion($ccod_cia, $ccod_tipoper, $objConex);
    }

    public function ConsultarTiposOperacion($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarTiposOperacion($ccod_cia, $objConex);
    }

    public function ConsultarTiposOperacionActivosIngresos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarTiposOperacionActivosIngresos($ccod_cia, $objConex);
    }

    public function ConsultarOperTransferencia($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarOperTransferencia($ccod_cia, $objConex);
    }

    public function ConsultarTiposDocumentoPago($ccod_cia, $ccod_caja, $objConex)
    {
        return $this->objDA->ConsultarTiposDocumentoPago($ccod_cia, $ccod_caja, $objConex);
    }

    public function ConsultarTipoDocumento($objConex)
    {
        return $this->objDA->ConsultarTipoDocumento($objConex);
    }

    public function ConsultarIngresos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarIngresos($ccod_cia, $objConex);
    }

    public function ConsultarSalidas($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarSalidas($ccod_cia, $objConex);
    }

    public function ConsultarSalida($ccod_cia, $codigo, $objConex)
    {
        return $this->objDA->ConsultarSalida($ccod_cia, $codigo, $objConex);
    }

    public function ConsultarTransferencias($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarTransferencias($ccod_cia, $objConex);
    }

    public function ConsultarTransferencia($ccod_cia, $codigo, $objConex)
    {
        return $this->objDA->ConsultarTransferencia($ccod_cia, $codigo, $objConex);
    }

    public function ConsultarProveedor($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarProveedor($ccod_cia, $objConex);
    }

    public function ConsultarVentasTienda($ccod_cia, $ccod_tienda, $ccod_caja, $fchDesde, $fchHasta, $objConex)
    {
        return $this->objDA->ConsultarVentasTienda($ccod_cia, $ccod_tienda, $ccod_caja, $fchDesde, $fchHasta, $objConex);
    }

    public function ConsultarArticulosSalida($ccod_cia, $almacen, $objConex)
    {
        return $this->objDA->ConsultarArticulosSalida($ccod_cia, $almacen, $objConex);
    }

    public function ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex)
    {
        return $this->objDA->ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex);
    }
}
?>