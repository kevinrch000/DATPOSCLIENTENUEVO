<?php
/**
 * DatPOS - Data Access: Salida
 * Reemplaza: DA/DAInventario.vb (parte salidas) + DA/DAInventarioDetalle.vb (parte salidas)
 */

require_once __DIR__ . '/../config/database.php';

class DASalida
{
    // ============================================================
    // CABECERA / DETALLE — webDatpos_insertarSalida usa OUTPUT params
    // ============================================================
    public function ConsultarSalidas($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarSalidas', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarSalida($ccod_cia, $codigo, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@codigo'   => $codigo
        ), $objConex);
    }

    public function ConsultarInventarioDetalleSalida($ccod_cia, $id, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarInventarioDetalleSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@id'       => intval($id)
        ), $objConex);
    }

    /**
     * InsertarInventarioSalidas — equivalente a BL.BLInventario.InsertarInventarioSalidas
     * Llama webDatpos_insertarSalida (cabecera, OUTPUT @id_cbinve) +
     * webDatpos_insertarDetalleSalidaInventario por cada línea (descuenta stock).
     * Retorna ['ok' => bool, 'id_cbinve' => string, 'error' => string]
     */
    public function InsertarInventarioSalidas($cab, $detalles, $objConex)
    {
        $params = array(
            '@ccod_cia'     => array('value' => $objConex->ccod_empresa),
            '@ccod_tienda'  => array('value' => ($cab['ccod_tienda'] ?? '') ?: $objConex->ccod_tiend),
            '@ccod_alm'     => array('value' => $cab['ccod_alm'] ?? ''),
            '@dfecha'       => array('value' => $cab['dfecha'] ?? date('Ymd')),
            '@ctipo'        => array('value' => $cab['ctipo'] ?? ''),
            '@vserie'       => array('value' => $cab['vserie'] ?? ''),
            '@vobservacion' => array('value' => $cab['vobservacion'] ?? ''),
            '@ccod_usuario' => array('value' => $objConex->ccod_usuario),
            '@ntotal'       => array('value' => floatval($cab['ntotal'] ?? 0)),
            '@id_cbinve'    => array('direction' => 'output'),
            '@ErrorNumber'  => array('direction' => 'output'),
            '@ErrorMessage' => array('direction' => 'output'),
        );

        $out = Database::executeStoredTenantWithOutput('webDatpos_insertarSalida', $params, $objConex);

        if ($out === false || (isset($out['@ErrorNumber']) && $out['@ErrorNumber'] !== '0' && $out['@ErrorNumber'] !== '')) {
            return array('ok' => false, 'id_cbinve' => '', 'error' => $out['@ErrorMessage'] ?? 'Error al insertar cabecera');
        }
        $id_cbinve = strval($out['@id_cbinve'] ?? '');
        if ($id_cbinve === '') {
            return array('ok' => false, 'id_cbinve' => '', 'error' => 'No se generó id_cbinve');
        }

        // Detalle: cada línea descuenta stock vía _stock_actualizar (signo -1)
        foreach ($detalles as $d) {
            if (intval($d['state'] ?? 0) === 3) continue;

            $detParams = array(
                '@ccod_cia'      => array('value' => $objConex->ccod_empresa),
                '@ccod_usuario'  => array('value' => $objConex->ccod_usuario),
                '@ccod_articulo' => array('value' => $d['ccod_articulo'] ?? ''),
                '@ncantidad'     => array('value' => intval($d['ncantidad'] ?? 0)),
                '@ncosto'        => array('value' => floatval($d['ncosto'] ?? 0)),
                '@id_cbinve'     => array('value' => $id_cbinve),
                '@almacen'       => array('value' => $cab['ccod_alm'] ?? ''),
                '@ErrorNumber'   => array('direction' => 'output'),
                '@ErrorMessage'  => array('direction' => 'output'),
            );
            $detOut = Database::executeStoredTenantWithOutput('webDatpos_insertarDetalleSalidaInventario', $detParams, $objConex);
            if ($detOut === false || (isset($detOut['@ErrorNumber']) && $detOut['@ErrorNumber'] !== '0' && $detOut['@ErrorNumber'] !== '')) {
                return array('ok' => false, 'id_cbinve' => $id_cbinve, 'error' => $detOut['@ErrorMessage'] ?? 'Error al insertar detalle');
            }
        }
        return array('ok' => true, 'id_cbinve' => $id_cbinve, 'error' => '');
    }

    public function EliminarInventarioDetalleTodo($id_cbinve, $objConex)
    {
        return Database::executeStoredTenant('sp_eliminarinventariodetalletodo', array(
            '@id_cbinve' => intval($id_cbinve)
        ), $objConex);
    }

    public function EliminarInventario($id, $objConex)
    {
        return Database::executeStoredTenant('sp_eliminarinventario', array(
            '@id' => intval($id)
        ), $objConex);
    }

    // ============================================================
    // CATÁLOGOS / VALIDACIONES
    // ============================================================
    public function ConsultarArticulosSalida($ccod_cia, $almacen, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarArticulosSalida', array(
            '@ccod_cia' => $ccod_cia,
            '@almacen'  => $almacen
        ), $objConex);
    }

    public function ConsultarAlmEmpActivos($ccod_cia, $objConex)
    {
        // FIX_20: sp_consultaalmempactivos(@ccod_tiend, @ccod_cia)
        // Si devuelve vacio hacemos fallback a sp_consultaalmacenesactivos
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

    public function ConsultarNumeradorSalida($ccod_cia, $ccod_alm, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_consultaNumeradorSalida', array(
            '@ccod_alm' => $ccod_alm,
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTiposOperacionActivosSalidas($ccod_cia, $objConex)
    {
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
