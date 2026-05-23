<?php
/**
 * DatPOS - Data Access: Ingreso
 * Reemplaza: DA/DAInventario.vb (parte ingresos) + DA/DAInventarioDetalle.vb
 */

require_once __DIR__ . '/../config/database.php';

class DAIngreso
{
    // ============================================================
    // CABECERA (al_cbinve)
    // ============================================================
    public function ConsultarIngresos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultaringresos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarIngreso($ccod_cia, $codigo, $objConex)
    {
        return Database::selectStoredTenant('sp_consultaringreso', array(
            '@ccod_cia' => $ccod_cia,
            '@codigo'   => $codigo
        ), $objConex);
    }

    public function InsertarInventario($obj, $objConex)
    {
        // Devuelve ID generado (SCOPE_IDENTITY)
        return Database::executeStoredTenantReturnId('webDatpos_insertarinventario', array(
            '@ccod_cia'      => $objConex->ccod_empresa,
            '@ccod_tienda'   => ($obj['ccod_tienda'] ?? '') ?: $objConex->ccod_tiend,
            '@ccod_alm'      => $obj['ccod_alm'] ?? '',
            '@dfecha'        => $obj['dfecha'] ?? date('Y-m-d'),
            '@ctipo'         => $obj['ctipo'] ?? '',
            '@vserie'        => $obj['vserie'] ?? '',
            '@vobservacion'  => $obj['vobservacion'] ?? '',
            '@ccod_usuario'  => $objConex->ccod_usuario,
            '@ccod_coa'      => $obj['ccod_coa'] ?? '',
            '@ntotal'        => $obj['ntotal'] ?? 0
        ), $objConex);
    }

    public function EditarInventario($obj, $objConex)
    {
        return Database::executeStoredTenant('sp_editarinventario', array(
            '@ccod_cia'      => $objConex->ccod_empresa,
            '@ccod_tienda'   => ($obj['ccod_tienda'] ?? '') ?: $objConex->ccod_tiend,
            '@ccod_alm'      => $obj['ccod_alm'] ?? '',
            '@dfecha'        => $obj['dfecha'] ?? date('Y-m-d'),
            '@ctipo'         => $obj['ctipo'] ?? '',
            '@vserie'        => $obj['vserie'] ?? '',
            '@nnumero'       => intval($obj['nnumero'] ?? 0),
            '@vobservacion'  => $obj['vobservacion'] ?? '',
            '@id_cbinve'     => intval($obj['id_cbinve'] ?? 0)
        ), $objConex);
    }

    public function EliminarInventario($id, $objConex)
    {
        return Database::executeStoredTenant('sp_eliminarinventario', array(
            '@id' => intval($id)
        ), $objConex);
    }

    // ============================================================
    // DETALLE (al_lninve)
    // ============================================================
    public function ConsultarInventarioDetalle($ccod_cia, $id, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarinventariodetalle', array(
            '@ccod_cia' => $ccod_cia,
            '@id'       => intval($id)
        ), $objConex);
    }

    public function InsertarInventarioDetalle($cab, $det, $objConex)
    {
        return Database::executeStoredTenant('sp_insertarinventariodetalle', array(
            '@ccod_cia'      => $objConex->ccod_empresa,
            '@ccod_usuario'  => $objConex->ccod_usuario,
            '@ccod_tienda'   => ($cab['ccod_tienda'] ?? '') ?: $objConex->ccod_tiend,
            '@almacen'       => $cab['ccod_alm'] ?? '',
            '@ccod_articulo' => $det['ccod_articulo'] ?? '',
            '@ncantidad'     => intval($det['ncantidad'] ?? 0),
            '@ncosto'        => floatval($det['ncosto'] ?? 0),
            '@id_cbinve'     => intval($cab['id_cbinve'] ?? 0)
        ), $objConex);
    }

    public function EditarInventarioDetalle($det, $objConex)
    {
        return Database::executeStoredTenant('sp_editarinventariodetalle', array(
            '@id_lninve'     => intval($det['id_lninve'] ?? 0),
            '@ccod_articulo' => $det['ccod_articulo'] ?? '',
            '@ncantidad'     => intval($det['ncantidad'] ?? 0),
            '@ncosto'        => floatval($det['ncosto'] ?? 0)
        ), $objConex);
    }

    public function EliminarInventarioDetalle($id_lninve, $objConex)
    {
        return Database::executeStoredTenant('sp_eliminarinventariodetalle', array(
            '@id_lninve' => intval($id_lninve)
        ), $objConex);
    }

    public function EliminarInventarioDetalleTodo($id_cbinve, $objConex)
    {
        return Database::executeStoredTenant('sp_eliminarinventariodetalletodo', array(
            '@id_cbinve' => intval($id_cbinve)
        ), $objConex);
    }

    // ============================================================
    // CATÁLOGOS
    // ============================================================
    public function ConsultarArticulos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultararticulosactivos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarProveedor($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('webDatpos_consultarProveedor', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ValidarArticulo($ccod_cia, $ccod_articulo, $objConex)
    {
        // appDatpos_validarArticulo devuelve solo (ccod_articulo, cdsc_articulo, uni_medi)
        // SP creado en migrations/FIX_30_Operaciones_SPs_Faltantes.sql
        return Database::selectStoredTenant('appDatpos_validarArticulo', array(
            '@ccod_cia'      => $ccod_cia,
            '@ccod_articulo' => $ccod_articulo
        ), $objConex);
    }

    public function ConsultarAlmEmpActivos($ccod_cia, $ccod_tiend, $objConex)
    {
        // FIX_20: sp_consultaalmempactivos(@ccod_tiend, @ccod_cia)
        // Si el SP devuelve vacio, hacemos fallback a sp_consultaalmacenesactivos
        $rows = Database::selectStoredTenant('sp_consultaalmempactivos', array(
            '@ccod_tiend' => strval($ccod_tiend ?? ''),
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
        // Devuelve el primer numerador del almacén (sin filtrar por tip_doc).
        // SP creado en FIX_30_Operaciones_SPs_Faltantes.sql
        return Database::selectStoredTenant('appDatpos_consultaNumeradorPorAlm', array(
            '@ccod_cia' => $ccod_cia,
            '@ccod_alm' => $ccod_alm
        ), $objConex);
    }

    public function ObtenerIvg($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('appDatpos_ObtenerIGV', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }

    public function ConsultarTiposOperacionActivosIngresos($ccod_cia, $objConex)
    {
        return Database::selectStoredTenant('sp_consultartiposoperacionactivosingresos', array(
            '@ccod_cia' => $ccod_cia
        ), $objConex);
    }
}
?>
