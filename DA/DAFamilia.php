<?php
/**
 * DatPOS - Data Access: Familia
 * Reemplaza: DA/DAFamilia.vb
 * 
 * Stored Procedures:
 *   - sp_consultafamiliasactivas (@ccod_cia)
 *   - sp_consultafamilia (@ccod_cia, @codigo)
 *   - sp_consultafamilias (@ccod_cia)
 *   - webDatpos_insertarFamilia (@ccod_lin, @ccod_cia, @cdsc_lin, @cstatus, @ccolor, @ccod_usuario)
 *   - sp_editarfamilia (@ccod_lin, @ccod_cia, @cdsc_lin, @cstatus, @ccolor, @ccod_usuario)
 *   - sp_eliminarfamilia (@ccod_lin, @ccod_cia)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEFamilia.php';

class DAFamilia {

    /**
     * Consultar familias activas
     * SP: sp_consultafamiliasactivas
     */
    public function consultarFamiliasActivas($objConex) {
        return Database::selectStoredTenant(
            'sp_consultafamiliasactivas',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    /**
     * Consultar una familia por código
     * SP: sp_consultafamilia
     */
    public function consultarFamilia($codigo, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultafamilia',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@codigo'   => $codigo
            ),
            $objConex
        );
    }

    /**
     * Consultar todas las familias
     * SP: sp_consultafamilias
     */
    public function consultarFamilias($objConex) {
        return Database::selectStoredTenant(
            'sp_consultafamilias',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }

    /**
     * Insertar familia
     * SP: webDatpos_insertarFamilia
     */
    public function insertarFamilia($objBE, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_insertarFamilia',
            array(
                '@ccod_lin'     => $objBE->ccod_lin,
                '@ccod_cia'     => $objConex->ccod_empresa,
                '@cdsc_lin'     => $objBE->cdsc_lin,
                '@cstatus'      => $objBE->cstatus,
                '@ccolor'       => $objBE->ccolor,
                '@ccod_usuario' => $objConex->ccod_usuario
            ),
            $objConex
        );
    }

    /**
     * Editar familia
     * SP: sp_editarfamilia
     */
    public function editarFamilia($objBE, $objConex) {
        return Database::selectStoredTenant(
            'sp_editarfamilia',
            array(
                '@ccod_lin'     => $objBE->ccod_lin,
                '@ccod_cia'     => $objConex->ccod_empresa,
                '@cdsc_lin'     => $objBE->cdsc_lin,
                '@cstatus'      => $objBE->cstatus,
                '@ccolor'       => $objBE->ccolor,
                '@ccod_usuario' => $objConex->ccod_usuario
            ),
            $objConex
        );
    }

    /**
     * Eliminar familia
     * SP: sp_eliminarfamilia
     */
    public function eliminarFamilia($cod, $objConex) {
        return Database::selectStoredTenant(
            'sp_eliminarfamilia',
            array(
                '@ccod_lin' => $cod,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }
}
?>
