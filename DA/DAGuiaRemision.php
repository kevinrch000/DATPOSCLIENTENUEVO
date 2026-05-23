<?php
/**
 * DatPOS - Data Access: Guía de Remisión
 * Reemplaza: DA/DAGuiaRemision.vb + parte de DA/DAInventario.vb
 *
 * Una guía puede ser:
 *  - Translado (operacion='04'): movimiento entre almacenes (origen+destino, doble stock)
 *  - Salida (operacion='01'/'14'): solo descuenta del almacén origen
 *  - Ingreso (operacion='02'): solo suma al almacén destino
 *
 * Toda la lógica de stock la maneja webDatpos_InsertarGuia + sp_DetalleGuia(Salida|Ingreso)
 * + webDatpos_insertarLnTransferencia (caso translado).
 */

require_once __DIR__ . '/../config/database.php';

class DAGuiaRemision
{
    // CONSULTAS
    public function ConsultarGuiaRemision($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarGuiaRemision', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ObtenerGuiaRemision($ccod_cia, $id_cbinve, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ObtenerGuiaRemision', array(
            '@ccod_cia'  => $ccod_cia,
            '@id_cbinve' => intval($id_cbinve)
        ), $objConex);
    }

    public function ObtenerDetalleGuiaRemision($ccod_cia, $id_cbinve, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ObtenerDetalleGuiaRemision', array(
            '@ccod_cia'  => $ccod_cia,
            '@id_cbinve' => intval($id_cbinve)
        ), $objConex);
    }

    public function ObtenerNumerador($ccod_cia, $tipo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ObtenerNumerador', array(
            '@ccod_cia' => $ccod_cia,
            '@tipo'     => $tipo
        ), $objConex);
    }

    public function ConsultarAlamcenes($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultarAlamcenes', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarOperaciones($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultarOperaciones', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarCodigoAuxiliar($ccod_cia, $cproveedor, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_ConsultarCodigoAuxiliar', array(
            '@ccod_cia'   => $ccod_cia,
            '@cproveedor' => $cproveedor
        ), $objConex);
    }

    // INSERCIÓN — los 3 modos comparten webDatpos_InsertarGuia para la cabecera.
    private function _insertarCabeceraGuia($cab, $objConex)
    {
        // FK_CbGuia_Coa(ccod_cia, ccod_coa) -> Coa. ccod_coa puede ser NULL en
        // CbGuia, así que convertir '' a NULL evita la violación cuando el
        // frontend aún no asoció un código Coa.
        $ccod_coa_in = trim(strval($cab['ccod_coa'] ?? ''));
        $ccod_coa_val = ($ccod_coa_in === '') ? null : $ccod_coa_in;

        $params = array(
            '@ccod_cia'             => array('value' => $objConex->ccod_empresa),
            '@ccod_guia'            => array('value' => $cab['ccod_guia'] ?? ''),
            '@cserie_guia'          => array('value' => $cab['cserie_guia'] ?? ''),
            '@cnum_ruc_rem'         => array('value' => $cab['cnum_ruc_rem'] ?? ''),
            '@cnom_rzn_soc_rem'     => array('value' => $cab['cnom_rzn_soc_rem'] ?? ''),
            '@cnum_ruc_dest'        => array('value' => $cab['cnum_ruc_dest'] ?? ''),
            '@cnom_rzn_soc_dest'    => array('value' => $cab['cnom_rzn_soc_dest'] ?? ''),
            '@cnum_ruc_proy'        => array('value' => $cab['cnum_ruc_proy'] ?? ''),
            '@cdsc_coa'             => array('value' => $cab['cdsc_coa'] ?? ''),
            '@cdomicilio_partida'   => array('value' => $cab['cdomicilio_partida'] ?? ''),
            '@ccod_ubi_partida'     => array('value' => $cab['ccod_ubi_partida'] ?? ''),
            '@cdomicilio_llegada'   => array('value' => $cab['cdomicilio_llegada'] ?? ''),
            '@ccod_ubi_llegada'     => array('value' => $cab['ccod_ubi_llegada'] ?? ''),
            '@ctrans_nombre'        => array('value' => $cab['ctrans_nombre'] ?? ''),
            '@ctrans_ruc'           => array('value' => $cab['ctrans_ruc'] ?? ''),
            '@ccod_unid_peso_bruto' => array('value' => $cab['ccod_unid_peso_bruto'] ?? 'KGM'),
            '@nmnt_tot_peso_bruto'  => array('value' => floatval($cab['nmnt_tot_peso_bruto'] ?? 0)),
            '@cdesc_motiv_tras'     => array('value' => $cab['cdesc_motiv_tras'] ?? ''),
            '@nobs'                 => array('value' => $cab['nobs'] ?? ''),
            '@ctrans_placa'         => array('value' => $cab['ctrans_placa'] ?? ''),
            '@ctrans_licencia'      => array('value' => $cab['ctrans_licencia'] ?? ''),
            '@ntotal'               => array('value' => floatval($cab['ntotal'] ?? 0)),
            '@cusu_crea'            => array('value' => $objConex->ccod_usuario),
            '@ccod_almOrigen'       => array('value' => $cab['ccod_almOrigen'] ?? ''),
            '@ctipoOrigen'          => array('value' => $cab['ctipoOrigen'] ?? ''),
            '@cserieOrigen'         => array('value' => $cab['cserieOrigen'] ?? ''),
            '@ccod_almDestino'      => array('value' => $cab['ccod_almDestino'] ?? ($cab['ccod_almOrigen'] ?? '')),
            '@ctipoDestino'         => array('value' => $cab['ctipoDestino'] ?? ''),
            '@cserieDestino'        => array('value' => $cab['cserieDestino'] ?? ''),
            '@dfec_fin'             => array('value' => $cab['dfec_fin'] ?? date('Ymd')),
            '@cdoc_ref'             => array('value' => $cab['cdoc_ref'] ?? ''),
            '@cod_tip_cpe'          => array('value' => $cab['cod_tip_cpe'] ?? '09'),
            '@ccod_coa'             => array('value' => $ccod_coa_val),
            '@id_cbinve'            => array('direction' => 'output', 'type' => 'INT'),
            '@numero'               => array('direction' => 'output'),
            '@fchEmision'           => array('direction' => 'output'),
        );
        return Database::executeStoredTenantWithOutput('webDatpos_InsertarGuia', $params, $objConex);
    }

    public function InsertarGuiaTranslado($cab, $detalles, $objConex)
    {
        $out = $this->_insertarCabeceraGuia($cab, $objConex);
        if ($out === false || empty($out['@id_cbinve'])) {
            return array(false, 'ERR', 'No se generó id_cbinve', '');
        }
        $id_cbinve = intval($out['@id_cbinve']);

        // Translado: usa webDatpos_insertarLnTransferencia (descuenta origen + suma destino)
        foreach ($detalles as $d) {
            if (intval($d['state'] ?? 0) === 3) continue;
            $detParams = array(
                '@ccod_cia'         => array('value' => $objConex->ccod_empresa),
                '@ccod_usuario'     => array('value' => $objConex->ccod_usuario),
                '@ccod_articulo'    => array('value' => $d['ccod_articulo'] ?? ''),
                '@ccod_artSunat'    => array('value' => $d['ccod_artSunat'] ?? ''),
                '@cdsc_articulo'    => array('value' => $d['cdsc_articulo'] ?? ''),
                '@ncantidad'        => array('value' => intval($d['ncantidad'] ?? 0)),
                '@ncosto'           => array('value' => floatval($d['ncosto'] ?? 0)),
                '@id_cbinve'        => array('value' => $id_cbinve),
                '@ccod_alm_salida'  => array('value' => $cab['ccod_almOrigen'] ?? ''),
                '@ccod_alm_ingreso' => array('value' => $cab['ccod_almDestino'] ?? ''),
                '@ErrorNumber'      => array('direction' => 'output'),
                '@ErrorMessage'     => array('direction' => 'output'),
                '@Error'            => array('direction' => 'output'),
            );
            $detOut = Database::executeStoredTenantWithOutput('webDatpos_insertarLnTransferencia', $detParams, $objConex);
            if ($detOut === false || (isset($detOut['@Error']) && $detOut['@Error'] === '1')) {
                return array(false, 'ERR', $detOut['@ErrorMessage'] ?? 'Error en línea', $id_cbinve);
            }
        }
        return array(true, 'OK', '', $id_cbinve);
    }

    public function InsertarGuiaVentaCompraSalida($cab, $detalles, $objConex)
    {
        $out = $this->_insertarCabeceraGuia($cab, $objConex);
        if ($out === false || empty($out['@id_cbinve'])) {
            return array(false, 'ERR', 'No se generó id_cbinve', '');
        }
        $id_cbinve = intval($out['@id_cbinve']);

        foreach ($detalles as $d) {
            if (intval($d['state'] ?? 0) === 3) continue;
            $r = Database::executeStoredTenant('sp_DetalleGuiaSalida', array(
                '@ccod_cia'      => $objConex->ccod_empresa,
                '@cusu_crea'     => $objConex->ccod_usuario,
                '@ccod_articulo' => $d['ccod_articulo'] ?? '',
                '@ccod_artSunat' => $d['ccod_artSunat'] ?? '',
                '@cdsc_articulo' => $d['cdsc_articulo'] ?? '',
                '@ncantidad'     => intval($d['ncantidad'] ?? 0),
                '@ncosto'        => floatval($d['ncosto'] ?? 0),
                '@id_cbinve'     => $id_cbinve,
                '@ccod_alm'      => $cab['ccod_almOrigen'] ?? ''
            ), $objConex);
            if (!$r) return array(false, 'ERR', 'Error en línea (salida)', $id_cbinve);
        }
        return array(true, 'OK', '', $id_cbinve);
    }

    public function InsertarGuiaVentaCompraIngreso($cab, $detalles, $objConex)
    {
        $out = $this->_insertarCabeceraGuia($cab, $objConex);
        if ($out === false || empty($out['@id_cbinve'])) {
            return array(false, 'ERR', 'No se generó id_cbinve', '');
        }
        $id_cbinve = intval($out['@id_cbinve']);

        foreach ($detalles as $d) {
            if (intval($d['state'] ?? 0) === 3) continue;
            $r = Database::executeStoredTenant('sp_DetalleGuiaIngreso', array(
                '@ccod_cia'      => $objConex->ccod_empresa,
                '@cusu_crea'     => $objConex->ccod_usuario,
                '@ccod_alm'      => $cab['ccod_almDestino'] ?? ($cab['ccod_almOrigen'] ?? ''),
                '@ccod_articulo' => $d['ccod_articulo'] ?? '',
                '@ccod_artSunat' => $d['ccod_artSunat'] ?? '',
                '@cdsc_articulo' => $d['cdsc_articulo'] ?? '',
                '@ncantidad'     => intval($d['ncantidad'] ?? 0),
                '@ncosto'        => floatval($d['ncosto'] ?? 0),
                '@id_cbinve'     => $id_cbinve
            ), $objConex);
            if (!$r) return array(false, 'ERR', 'Error en línea (ingreso)', $id_cbinve);
        }
        return array(true, 'OK', '', $id_cbinve);
    }

    /**
     * Actualiza la cabecera CbGuia identificada por (ccod_cia, id_cbinve).
     * No toca CbInventario ni LnInventario (no se recalcula stock; los
     * articulos no se editan via este flujo).
     */
    public function ActualizarGuia($id_cbinve, $cab, $objConex)
    {
        $ccod_coa_in  = trim(strval($cab['ccod_coa'] ?? ''));
        $ccod_coa_val = ($ccod_coa_in === '') ? null : $ccod_coa_in;

        $params = array(
            '@ccod_cia'             => array('value' => $objConex->ccod_empresa),
            '@id_cbinve'            => array('value' => intval($id_cbinve)),
            '@ccod_guia'            => array('value' => $cab['ccod_guia'] ?? ''),
            '@cserie_guia'          => array('value' => $cab['cserie_guia'] ?? ''),
            '@cnum_ruc_rem'         => array('value' => $cab['cnum_ruc_rem'] ?? ''),
            '@cnom_rzn_soc_rem'     => array('value' => $cab['cnom_rzn_soc_rem'] ?? ''),
            '@cnum_ruc_dest'        => array('value' => $cab['cnum_ruc_dest'] ?? ''),
            '@cnom_rzn_soc_dest'    => array('value' => $cab['cnom_rzn_soc_dest'] ?? ''),
            '@cnum_ruc_proy'        => array('value' => $cab['cnum_ruc_proy'] ?? ''),
            '@cdsc_coa'             => array('value' => $cab['cdsc_coa'] ?? ''),
            '@cdomicilio_partida'   => array('value' => $cab['cdomicilio_partida'] ?? ''),
            '@ccod_ubi_partida'     => array('value' => $cab['ccod_ubi_partida'] ?? ''),
            '@cdomicilio_llegada'   => array('value' => $cab['cdomicilio_llegada'] ?? ''),
            '@ccod_ubi_llegada'     => array('value' => $cab['ccod_ubi_llegada'] ?? ''),
            '@ctrans_nombre'        => array('value' => $cab['ctrans_nombre'] ?? ''),
            '@ctrans_ruc'           => array('value' => $cab['ctrans_ruc'] ?? ''),
            '@ccod_unid_peso_bruto' => array('value' => $cab['ccod_unid_peso_bruto'] ?? 'KGM'),
            '@nmnt_tot_peso_bruto'  => array('value' => floatval($cab['nmnt_tot_peso_bruto'] ?? 0)),
            '@cdesc_motiv_tras'     => array('value' => $cab['cdesc_motiv_tras'] ?? ''),
            '@nobs'                 => array('value' => $cab['nobs'] ?? ''),
            '@ctrans_placa'         => array('value' => $cab['ctrans_placa'] ?? ''),
            '@ctrans_licencia'      => array('value' => $cab['ctrans_licencia'] ?? ''),
            '@ccod_almOrigen'       => array('value' => $cab['ccod_almOrigen'] ?? ''),
            '@ctipoOrigen'          => array('value' => $cab['ctipoOrigen'] ?? ''),
            '@cserieOrigen'         => array('value' => $cab['cserieOrigen'] ?? ''),
            '@ccod_almDestino'      => array('value' => $cab['ccod_almDestino'] ?? ($cab['ccod_almOrigen'] ?? '')),
            '@ctipoDestino'         => array('value' => $cab['ctipoDestino'] ?? ''),
            '@cserieDestino'        => array('value' => $cab['cserieDestino'] ?? ''),
            '@dfec_fin'             => array('value' => $cab['dfec_fin'] ?? date('Ymd')),
            '@cdoc_ref'             => array('value' => $cab['cdoc_ref'] ?? ''),
            '@cod_tip_cpe'          => array('value' => $cab['cod_tip_cpe'] ?? '09'),
            '@ccod_coa'             => array('value' => $ccod_coa_val),
            '@rowcount'             => array('direction' => 'output', 'type' => 'INT'),
        );
        $out = Database::executeStoredTenantWithOutput('webDatpos_ActualizarGuia', $params, $objConex);
        if ($out === false) {
            return array(false, 'ERR', 'Error al actualizar la cabecera de la guia', $id_cbinve);
        }
        $rows = intval($out['@rowcount'] ?? 0);
        if ($rows === 0) {
            return array(false, 'NOT_FOUND', 'Guia no encontrada (id_cbinve='.$id_cbinve.')', $id_cbinve);
        }
        return array(true, 'OK', '', $id_cbinve);
    }
}
?>
