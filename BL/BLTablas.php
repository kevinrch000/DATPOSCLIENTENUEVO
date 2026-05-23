<?php
/**
 * DatPOS - Business Logic: Tablas
 * Reemplaza: BL/BLTabla.vb
 */

require_once __DIR__ . '/../DA/DATablas.php';

class BLTablas
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DATablas();
    }

    public function ConsultarMotivos($ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarMotivos($ccod_cia, $objConex);
    }

    public function ReporteVentaPrincipal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex)
    {
        return $this->objDA->ReporteVentaPrincipal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex);
    }

    public function ReporteVentaImporteTotal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex)
    {
        return $this->objDA->ReporteVentaImporteTotal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex);
    }

    public function ReporteAlmacenPrincipal($ccod_almacen, $fchDesde, $fchHasta, $ctipo, $ccod_articulo, $ccod_cia, $objConex)
    {
        return $this->objDA->ReporteAlmacenPrincipal($ccod_almacen, $fchDesde, $fchHasta, $ctipo, $ccod_articulo, $ccod_cia, $objConex);
    }

    public function ReporteTributarioPrincipal($ccod_tienda, $fchDesde, $fchHasta, $ccod_coa, $ccod_cia, $cstatus_tributario, $objConex)
    {
        return $this->objDA->ReporteTributarioPrincipal($ccod_tienda, $fchDesde, $fchHasta, $ccod_coa, $ccod_cia, $cstatus_tributario, $objConex);
    }
}
?>