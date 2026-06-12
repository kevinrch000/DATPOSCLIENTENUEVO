<?php
require_once __DIR__ . '/../config/database.php';

class DAConsultaVenta {

    public function ConsultasVentaPricipal($consultaArticulo, $obj) {
        return Database::selectStoredTenant(
            'webDatpos_consultaVentaPrincipal',
            array(
                '@Codigo' => $consultaArticulo->ccod_articulo,
                '@cliente' => $consultaArticulo->ccod_coa,
                '@fechadesde' => $consultaArticulo->n_fchDesde,
                '@fechahasta' => $consultaArticulo->n_fchHasta,
                '@ccod_tienda' => $consultaArticulo->ccod_tienda,
                '@cdsc_lnvariante' => $consultaArticulo->cobser_variante,
                '@ccod_cia' => $obj->ccod_empresa
            ),
            $obj
        );
    }

    public function ConsultaArticulosMasVendidos($articulosMasVendidos, $obj) {
        return Database::selectStoredTenant(
            'webDatpos_consultaArticulosMasVendidos',
            array(
                '@fechadesde' => $articulosMasVendidos->n_fchDesde,
                '@fechahasta' => $articulosMasVendidos->n_fchHasta,
                '@ccod_tienda' => $articulosMasVendidos->ccod_tienda,
                '@ccod_lin' => $articulosMasVendidos->ccod_lin,
                '@ccod_articulo' => $articulosMasVendidos->ccod_articulo,
                '@ccod_cia' => $obj->ccod_empresa
            ),
            $obj
        );
    }

    public function CargarArticuloVentas($objVentas, $obj) {
        return Database::selectStoredTenant(
            'webDatpos_cargarArticuloVentas',
            array(
                '@cliente' => $objVentas->ccoa_dsc,
                '@fechadesde' => $objVentas->n_fchDesde,
                '@fechahasta' => $objVentas->n_fchHasta,
                '@ccod_tienda' => $objVentas->ccod_tienda,
                '@ccod_cia' => $obj->ccod_empresa
            ),
            $obj
        );
    }

    public function CargarArticulo($obj) {
        return Database::selectStoredTenant(
            'webDatpos_cargarArticuloSoloBienes',
            array('@ccod_cia' => $obj->ccod_empresa),
            $obj
        );
    }

    public function CargarGraBarConsVent($codigo, $cliente, $tienda, $fchDesde, $fchHasta, $obj) {
        return Database::selectStoredTenant(
            'webDatpos_cargarGraBarConsVent',
            array(
                '@Codigo' => $codigo,
                '@cliente' => $cliente,
                '@fechadesde' => $fchDesde,
                '@fechahasta' => $fchHasta,
                '@ccod_tienda' => $tienda,
                '@ccod_cia' => $obj->ccod_empresa
            ),
            $obj
        );
    }

    public function CargarGraBarConsVentMenos($codigo, $cliente, $tienda, $fchDesde, $fchHasta, $obj) {
        return Database::selectStoredTenant(
            'webDatpos_cargarGraBarConsVentMenos',
            array(
                '@Codigo' => $codigo,
                '@cliente' => $cliente,
                '@fechadesde' => $fchDesde,
                '@fechahasta' => $fchHasta,
                '@ccod_tienda' => $tienda,
                '@ccod_cia' => $obj->ccod_empresa
            ),
            $obj
        );
    }
}
?>