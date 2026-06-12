<?php
/**
 * DatPOS - Data Access: UnidadMedida
 * Reemplaza: DA/DAUnidadMedida.vb
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUnidadMedida.php';

class DAUnidadMedida {

    public function consultarCodigoUnidadMedida($codigo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarCodigoUnidadMedida',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_unidadmedida' => $codigo
            ),
            $objConex
        );
    }

    public function consultarUnidadMedida($objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultarUnidadMedida',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    public function eliminarUnidadMedida($codigo, $objConex) {
        return Database::selectStoredTenant(
            'sp_eliminarUnidadMedida',
            array(
                '@ccod_unidadmedida' => $codigo,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function insertarUnidadMedida($objBE, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_insertarUnidadMedida',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_unidadmedida' => $objBE->ccod_unidadmedida,
                '@csim_unidadmedida' => $objBE->csim_unidadmedida,
                '@cdsc_unidadmedida' => $objBE->cdsc_unidadmedida,
                '@cstatus' => $objBE->cstatus,
                '@ccod_tributario' => $objBE->ccod_tributario
            ),
            $objConex
        );
    }

    public function editarUnidadMedida($objBE, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_editarUnidadMedida',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@ccod_unidadmedida' => $objBE->ccod_unidadmedida,
                '@csim_unidadmedida' => $objBE->csim_unidadmedida,
                '@cdsc_unidadmedida' => $objBE->cdsc_unidadmedida,
                '@cstatus' => $objBE->cstatus,
                '@ccod_tributario' => $objBE->ccod_tributario
            ),
            $objConex
        );
    }
}
?>
