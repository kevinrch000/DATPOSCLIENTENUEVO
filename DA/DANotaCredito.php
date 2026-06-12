<?php
/**
 * DatPOS - Data Access: NotaCredito
 * Reemplaza: DA/DANotaCredito.vb
 */

require_once __DIR__ . '/../config/database.php';

class DANotaCredito
{
    public function InsertarNotaCredito($id_cbfact, $cod_motivo, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('InsertarNotaCredito', array(
            '@id_cbfact' => $id_cbfact,
            '@cod_motivo' => $cod_motivo,
            '@nimp_aplicado' => $nimp_aplicado,
            '@cdsc_movito' => $cdsc_movito,
            '@ccod_usuario' => $ccod_usuario,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function DetalleNotaCredito($id_cbfact, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_DetalleNotaCredito', array(
            '@id_cbfact' => $id_cbfact
        ), $objConex);
    }

    public function ConsultarDocumentosNotaCredito($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultarDocumentosNotaCredito', array(
            '@cdoc_seri' => $cdoc_seri,
            '@serie' => $serie,
            '@correlativo' => $correlativo,
            '@ccod_tienda' => $ccod_tienda,
            '@ccod_coa' => $ccod_coa,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@CodCia' => $ccod_cia
        ), $objConex);
    }

    public function NotaCreditoPrincipal($cdoc_seri, $serie, $correlativo, $ccod_tienda, $ccod_coa, $fchDesde, $fchHasta, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_NotaCreditoPricipal', array(
            '@cdoc_seri' => $cdoc_seri,
            '@serie' => $serie,
            '@correlativo' => $correlativo,
            '@ccod_tienda' => $ccod_tienda,
            '@ccod_coa' => $ccod_coa,
            '@fchDesde' => $fchDesde,
            '@fchHasta' => $fchHasta,
            '@CodCia' => $ccod_cia
        ), $objConex);
    }

    public function GenerarNotaCredito($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_generarNotaCredito', array(
            '@id_cbfact' => $id_cbfact,
            '@nimp_aplicado' => $nimp_aplicado,
            '@cdsc_movito' => $cdsc_movito,
            '@ccod_usuario' => $ccod_usuario,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function GenerarNotaCreditoDescuento($id_cbfact, $nimp_aplicado, $cdsc_movito, $ccod_usuario, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_generarNotaCreditoDescuento', array(
            '@id_cbfact' => $id_cbfact,
            '@nimp_aplicado' => $nimp_aplicado,
            '@cdsc_movito' => $cdsc_movito,
            '@ccod_usuario' => $ccod_usuario,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function GenerarNotaCreditoDevolucion($ccod_cia, $id_cbfact, $motivo, $ccod_usuario, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_generarNotaCreditoDevolucion', array(
            '@ccod_cia' => $ccod_cia,
            '@id_cbfact' => $id_cbfact,
            '@motivo' => $motivo,
            '@ccod_usuario' => $ccod_usuario
        ), $objConex);
    }

    public function ConsultarNotaCredito($id_cbfact, $ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultarNotaCredito', array(
            '@id_cbfact' => $id_cbfact,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }
}
?>