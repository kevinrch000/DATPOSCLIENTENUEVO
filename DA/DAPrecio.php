<?php
/**
 * DatPOS - Data Access: Precio
 * Reemplaza: DA/DAPrecio.vb (subset usado por Facturación)
 *
 * Stored Procedures:
 *   - sp_consultararticulopreciocodigo (@ccod_cia, @ccod_usuario, @codigo, @ccod_almacen)
 *   - sp_consultararticuloprecio       (@ccod_cia, @ccod_usuario, @codigo, @ccod_almacen)
 */

require_once __DIR__ . '/../config/database.php';

class DAPrecio
{
    public function ResolverCodigoArticulo($codigo, $objConex)
    {
        if ($codigo === '' || $codigo === null) return '';

        $conn = Database::getTenantConnection($objConex);
        if (!$conn) return '';

        $sql = "SELECT TOP 1 ccod_articulo FROM Articulos "
             . "WHERE ccod_cia = ? AND (ccod_articulo = ? OR CAST(id_articulo AS VARCHAR(50)) = ?)";
        $stmt = sqlsrv_query($conn, $sql, array($objConex->ccod_empresa, $codigo, $codigo));
        $ccod_articulo = '';

        if ($stmt) {
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $ccod_articulo = $row['ccod_articulo'];
            }
            sqlsrv_free_stmt($stmt);
        }

        sqlsrv_close($conn);
        return $ccod_articulo;
    }

    public function ConsultarArticuloPrecioCodigo($codigo, $objConex)
    {
        return Database::selectStoredTenant('sp_consultararticulopreciocodigo', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objConex->ccod_almacen
        ), $objConex);
    }

    public function LSConsultarArticuloPrecioCodigo($codigo, $ccod_cblistpre, $objConex)
    {
        return Database::selectStoredTenant('sp_lsconsultararticulopreciocodigo', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objConex->ccod_almacen,
            '@ccod_cblistpre' => $ccod_cblistpre
        ), $objConex);
    }

    public function ConsultarArticuloPrecio($codigo, $objConex)
    {
        return Database::selectStoredTenant('sp_consultararticuloprecio', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objConex->ccod_almacen
        ), $objConex);
    }

    public function LSConsultarArticuloPrecio($codigo, $ccod_cblistpre, $objConex)
    {
        return Database::selectStoredTenant('sp_lsconsultararticuloprecio', array(
            '@ccod_cia' => $objConex->ccod_empresa,
            '@ccod_usuario' => $objConex->ccod_usuario,
            '@codigo' => $codigo,
            '@ccod_almacen' => $objConex->ccod_almacen,
            '@ccod_cblistpre' => $ccod_cblistpre
        ), $objConex);
    }
}
?>
