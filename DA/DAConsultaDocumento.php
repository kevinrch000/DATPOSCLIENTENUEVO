<?php
/**
 * DatPOS - Data Access: ConsultaDocumento
 * Reemplaza: DA/DAConsultaDocumento.vb (parte usada por AperturaCaja)
 *
 * Stored Procedures:
 *   - webDatpos_consultaTienda (@ccod_cia)
 */

require_once __DIR__ . '/../config/database.php';

class DAConsultaDocumento {

    /**
     * Consultar tiendas de la empresa
     * SP: webDatpos_consultaTienda
     * Retorna: ccod_tiend, cnombr
     */
    public function ConsultaTienda($objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultaTienda',
            array('@ccod_cia' => $objConex->ccod_empresa),
            $objConex
        );
    }
}
?>
