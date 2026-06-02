<?php
/**
 * DatPOS - Data Access: Variante
 * Reemplaza: DA/DAVariante.vb (subset usado por Facturación)
 *
 * Stored Procedures:
 *   - sp_consultarvariantesactivas    (@ccod_cia, @ccod_articulo)
 *   - sp_consultarsubvariantesactivas (@ccod_cia, @id_cbvariante)
 */

require_once __DIR__ . '/../config/database.php';

class DAVariante
{
    public function ConsultarVariantesActivas($id_articulo, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarvariantesactivas', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_articulo' => $id_articulo
        ), $objConex);
    }

    public function ConsultarSubVariantesActivas($id_variante, $objConex)
    {
        return Database::selectStoredTenant('sp_consultarsubvariantesactivas', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@id_cbvariante' => $id_variante
        ), $objConex);
    }
}
?>
