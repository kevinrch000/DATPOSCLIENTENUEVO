<?php
/**
 * DatPOS - Data Access: Transferencias entre almacenes
 * Reemplaza: DA/DAInventario.vb (parte InsertarCbTransferencia / InsertarLnTransferencia)
 *
 * Una transferencia genera DOS movimientos atómicos:
 *  - SALIDA en almacén origen (descuenta stock)
 *  - INGRESO en almacén destino (suma stock)
 * Toda la lógica de stock la maneja el SP webDatpos_insertarLnTransferencia
 * vía _stock_actualizar (signo -1 origen, +1 destino).
 */

require_once __DIR__ . '/../config/database.php';

class DATransferencia
{
    public function ConsultarTransferencias($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarTransferencias', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTransferencia($ccod_cia, $codigo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarTransferencia', array(
            '@ccod_cia' => $ccod_cia,
            '@codigo'   => $codigo
        ), $objConex);
    }

    public function ConsultarInventarioDetalle($ccod_cia, $id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarinventariodetalle', array(
            '@ccod_cia' => $ccod_cia,
            '@id'       => intval($id)
        ), $objConex);
    }

    /**
     * InsertarCbTransferencia + N InsertarLnTransferencia.
     * Retorna ['ok'=>bool, 'id_cbinve'=>string, 'error'=>string]
     */
    public function InsertarTransferencia($cab, $detalles, $objConex)
    {
        $params = array(
            '@ccod_cia'        => array('value' => $objConex->ccod_empresa),
            '@ccod_tienda'     => array('value' => ($cab['ccod_tienda'] ?? '') ?: $objConex->ccod_tiend),
            '@ccod_almOrigen'  => array('value' => $cab['ccod_almOrigen'] ?? ''),
            '@ctipoOrigen'     => array('value' => $cab['ctipoOrigen'] ?? ''),
            '@cserieOrigen'    => array('value' => $cab['vserieOrigen'] ?? ($cab['cserieOrigen'] ?? '')),
            '@nnumeroOrigen'   => array('value' => intval($cab['nnumeroOrigen'] ?? 0)),
            '@ccod_almDestino' => array('value' => $cab['ccod_almDestino'] ?? ''),
            '@ctipoDestino'    => array('value' => $cab['ctipoDestino'] ?? ''),
            '@cserieDestino'   => array('value' => $cab['vserieDestino'] ?? ($cab['cserieDestino'] ?? '')),
            '@nnumeroDestino'  => array('value' => intval($cab['nnumeroDestino'] ?? 0)),
            '@dfecha'          => array('value' => $cab['dfecha'] ?? date('Ymd')),
            '@vobservacion'    => array('value' => $cab['vobservacion'] ?? ''),
            '@ccod_usuario'    => array('value' => $objConex->ccod_usuario),
            '@ntotal'          => array('value' => floatval($cab['ntotal'] ?? 0)),
            '@id_cbinve'       => array('direction' => 'output'),
            '@ErrorNumber'     => array('direction' => 'output'),
        );

        $out = Database::executeStoredTenantWithOutput('webDatpos_insertarCbTransferencia', $params, $objConex);
        if ($out === false || (isset($out['@ErrorNumber']) && $out['@ErrorNumber'] !== '0' && $out['@ErrorNumber'] !== '')) {
            return array('ok' => false, 'id_cbinve' => '', 'error' => 'Error CbTransferencia: '.($out['@ErrorNumber'] ?? '?'));
        }
        $id_cbinve = strval($out['@id_cbinve'] ?? '');
        if ($id_cbinve === '') {
            return array('ok' => false, 'id_cbinve' => '', 'error' => 'No se generó id_cbinve para transferencia');
        }

        // Detalle: SP descuenta stock origen y suma destino automáticamente
        foreach ($detalles as $d) {
            if (intval($d['state'] ?? 0) === 3) continue;

            $detParams = array(
                '@ccod_cia'         => array('value' => $objConex->ccod_empresa),
                '@ccod_usuario'     => array('value' => $objConex->ccod_usuario),
                '@ccod_articulo'    => array('value' => $d['ccod_articulo'] ?? ''),
                '@ccod_artSunat'    => array('value' => ''),
                '@cdsc_articulo'    => array('value' => ''),
                '@ncantidad'        => array('value' => intval($d['ncantidad'] ?? 0)),
                '@ncosto'           => array('value' => floatval($d['ncosto'] ?? 0)),
                '@id_cbinve'        => array('value' => intval($id_cbinve)),
                '@ccod_alm_salida'  => array('value' => $cab['ccod_almOrigen'] ?? ''),
                '@ccod_alm_ingreso' => array('value' => $cab['ccod_almDestino'] ?? ''),
                '@ErrorNumber'      => array('direction' => 'output'),
                '@ErrorMessage'     => array('direction' => 'output'),
                '@Error'            => array('direction' => 'output'),
            );
            $detOut = Database::executeStoredTenantWithOutput('webDatpos_insertarLnTransferencia', $detParams, $objConex);
            if ($detOut === false || (isset($detOut['@Error']) && $detOut['@Error'] === '1')) {
                return array('ok' => false, 'id_cbinve' => $id_cbinve, 'error' => $detOut['@ErrorMessage'] ?? 'Error línea transferencia');
            }
        }
        return array('ok' => true, 'id_cbinve' => $id_cbinve, 'error' => '');
    }

    // CATÁLOGOS
    public function ConsultarArticulosSalida($ccod_cia, $almacen, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarArticulosSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@almacen'  => $almacen
        ), $objConex);
    }

    public function ConsultarArticulosActivos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultararticulosactivos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarAlmEmpActivos($ccod_cia, $objConex)
    {
        // FIX_20: sp_consultaalmempactivos(@ccod_tiend, @ccod_cia)
        // Si vuelve vacio caemos a sp_consultaalmacenesactivos
        $rows = Database::selectStoredTenant('sp_consultaalmempactivos', array(
            '@ccod_tiend' => strval($objConex->ccod_tiend ?? ''),
            '@ccod_cia'   => strval($ccod_cia)
        ), $objConex);
        if (empty($rows)) {
            $rows = Database::selectStoredTenant('sp_consultaalmacenesactivos', array(
                '@ccod_cia' => strval($ccod_cia)
            ), $objConex);
        }
        return $rows;
    }

    public function ConsultarNumerador($ccod_cia, $ccod_alm, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_consultaNumeradorAlmacen', array(
            '@ccod_alm' => $ccod_alm,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarNumeradorSalida($ccod_cia, $ccod_alm, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_consultaNumeradorSalida', array(
            '@ccod_alm' => $ccod_alm,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function CargarTiposOperacionTransferencia($ccod_cia, $objConex)
    {
        // Tipos de operación de transferencia (ingreso por transferencia: IPT)
        return Database::selectStoredTenant('webDatpos_consultarOperTransferencia', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function CargarTiposOperacionTransferenciaSalida($ccod_cia, $objConex)
    {
        // Salida por transferencia (SPT) — reusamos el mismo SP de Salidas si no hay específico
        return Database::selectStoredTenant('sp_consultarTiposOperacionSalisa', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ValidarArticuloAlmacenSalida($ccod_cia, $ccod_articulo, $ccod_alm, $ncantidad, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_validarArticuloAlmacenSalida', array(
            '@ccod_cia'      => $ccod_cia,
            '@ccod_articulo' => $ccod_articulo,
            '@ccod_alm'      => $ccod_alm,
            '@ncantidad'     => floatval($ncantidad)
        ), $objConex);
    }
}
?>
