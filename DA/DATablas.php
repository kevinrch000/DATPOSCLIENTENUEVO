<?php
/**
 * DatPOS - Data Access: Reportes
 * Reemplaza: DA/DATabla.vb / Reportes
 */

require_once __DIR__ . '/../config/database.php';

class DATablas
{
    public function ConsultarMotivos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarmotivos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ReporteVentaPrincipal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_reporteVentaPrincipal', array(
            '@ccod_tienda' => $ccod_tienda,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@cdoc' => $cdoc,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ReporteVentaImporteTotal($ccod_tienda, $fchDesde, $fchHasta, $cdoc, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_reporteVentaImporteTotal', array(
            '@ccod_tienda' => $ccod_tienda,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@cdoc' => $cdoc,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ReporteAlmacenPrincipal($ccod_almacen, $fchDesde, $fchHasta, $ctipo, $ccod_articulo, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_reporteAlmacenPrincipal', array(
            '@ccod_almacen' => $ccod_almacen,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@ctipo' => $ctipo,
            '@ccod_articulo' => $ccod_articulo,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ReporteTributarioPrincipal($ccod_tienda, $fchDesde, $fchHasta, $ccod_coa, $ccod_cia, $cstatus_tributario, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_reporteTributarioPrincipal', array(
            '@ccod_tienda' => $ccod_tienda,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@ccod_coa' => $ccod_coa,
            '@ccod_cia' => $ccod_cia,
            '@cstatus_tributario' => $cstatus_tributario
        ), $objConex);
    }
}
?>