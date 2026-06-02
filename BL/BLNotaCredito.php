<?php
/**
 * DatPOS - Business Logic: NotaCredito
 * Reemplaza: BL/BLNotaCredito.vb
 */

require_once __DIR__ . '/../DA/DANotaCredito.php';

class BLNotaCredito
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DANotaCredito();
    }

    public function InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return $this->objDA->InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex);
    }

    public function DetalleNotaCredito($id_cbfact, $objConex)
    {
        return $this->objDA->DetalleNotaCredito($id_cbfact, $objConex);
    }

    public function ConsultarDocumentosNotaCredito($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarDocumentosNotaCredito($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex);
    }

    public function NotaCreditoPrincipal($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex)
    {
        return $this->objDA->NotaCreditoPrincipal($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex);
    }

    public function GenerarNotaCredito($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return $this->objDA->GenerarNotaCredito($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex);
    }

    public function GenerarNotaCreditoDescuento($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return $this->objDA->GenerarNotaCreditoDescuento($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex);
    }

    public function GenerarNotaCreditoDevolucion($ccod_cia, $id_cbfact, $motivo, $ccod_usuario, $objConex)
    {
        return $this->objDA->GenerarNotaCreditoDevolucion($ccod_cia, $id_cbfact, $motivo, $ccod_usuario, $objConex);
    }

    public function ConsultarNotaCredito($id_cbfact, $ccod_cia, $objConex)
    {
        return $this->objDA->ConsultarNotaCredito($id_cbfact, $ccod_cia, $objConex);
    }
}
?>