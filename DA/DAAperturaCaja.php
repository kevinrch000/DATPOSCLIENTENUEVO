<?php
/**
 * DatPOS - Data Access: AperturaCaja
 * Reemplaza: DA/DAAperturaCaja.vb
 * 
 *Stored procedures usados:
 *   - appDatpos_cierreCaja
 *   - appDatpos_abrirCaja
 *   - webDatpos_consultarCierreCaja
 *   - webDatpos_consultarIdCierreCaja
 *   - webDatpos_cargarCajaDeUsuario
 *   - webDatpos_cargarTurnoUsuario
 *   - webDatpos_cargarIdUsuario
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEAperturaCaja.php';

class DAAperturaCaja
{
    /**
     * Cierre de caja (appDatpos_cierreCaja)
     * VB.NET original:
     *   @id_turno, @ntot_entreg, @nmonto_fin, @ndiferencia, @CodCia, @CodUsu
     */
    public function CierreCaja($DatTurno, $obj)
    {
        return Database::selectStoredTenant('appDatpos_cierreCaja', array(
            '@id_turno' => $DatTurno->id_turno,
            '@ntot_entreg' => $DatTurno->ntot_entreg,
            '@nmonto_fin' => $DatTurno->nmonto_fin,
            '@ndiferencia' => $DatTurno->ndiferencia,
            '@CodCia' => $obj->ccod_empresa,
            '@CodUsu' => $obj->ccod_usuario
        ), $obj);
    }

    /**
     * Aperturar caja (appDatpos_abrirCaja)
     * VB.NET original:
     *   @CodTie, @IdUsuario, @CodCaj, @Monto, @CodCia, @CodUsu, @dfchdoc_ini
     */
    public function AperturarCaja($DatTurno, $obj)
    {
        return Database::selectStoredTenant('appDatpos_abrirCaja', array(
            '@CodTie' => $DatTurno->ccod_tienda,
            '@IdUsuario' => $DatTurno->ccod_usuario,
            '@CodCaj' => $DatTurno->ccod_caja,
            '@Monto' => $DatTurno->nmonto_ini,
            '@CodCia' => $obj->ccod_empresa,
            '@CodUsu' => $obj->ccod_usuario,
            '@dfchdoc_ini' => $DatTurno->dfchdoc_ini
        ), $obj);
    }

    /**
     * Consultar cierre de caja (webDatpos_consultarCierreCaja)
     * VB.NET original:
     *   @ccod_cia
     * Retorna: id_turno, ccod_tienda, ccod_usuario, ccod_caja, 
     *          nmonto_ini, nmonto_fin, dfchdoc_ini, dfchdoc_fin, cstatus
     */
    public function ConsultarCierreCaja($obj)
    {
        return Database::selectStoredTenant('webDatpos_consultarCierreCaja', array(
            '@ccod_cia' => $obj->ccod_empresa
        ), $obj);
    }

    /**
     * Consultar detalle de turno por id (webDatpos_consultarIdCierreCaja)
     * VB.NET original:
     *   @ccod_cia, @id_turno
     * Retorna: id_turno, ccod_tienda, ccod_usuario, ccod_caja, nmonto_ini, nmonto_fin,
     *          dfchdoc_ini, dfchdoc_fin, ccod_tienda(repeat), ccod_usuario(repeat), 
     *          ccod_caja(repeat), ntot_entreg, ndiferencia
     */
    public function ConsultarIdCierreCaja($id_turno, $obj)
    {
        return Database::selectStoredTenant('webDatpos_consultarIdCierreCaja', array(
            '@ccod_cia' => $obj->ccod_empresa,
            '@id_turno' => $id_turno
        ), $obj);
    }

    /**
     * Cargar caja de usuario (webDatpos_cargarCajaDeUsuario)
     * VB.NET original:
     *   @ccod_cia, @ccod_usuario
     * Retorna: ccod_caja, cdsc_caja
     */
    public function CargarCajaDeUsuario($ccod_usuario, $obj)
    {
        return Database::selectStoredTenant('webDatpos_cargarCajaDeUsuario', array(
            '@ccod_cia' => $obj->ccod_empresa,
            '@ccod_usuario' => $ccod_usuario
        ), $obj);
    }

    /**
     * Cargar turno de usuario (webDatpos_cargarTurnoUsuario)
     * VB.NET original:
     *   @ccod_cia, @ccod_tienda
     */
    public function CargarTurnoUsuario($id_usuario, $obj)
    {
        return Database::selectStoredTenant('webDatpos_cargarTurnoUsuario', array(
            '@ccod_cia' => $obj->ccod_empresa,
            '@ccod_tienda' => $id_usuario
        ), $obj);
    }

    /**
     * Cargar id usuario (webDatpos_cargarIdUsuario)
     * VB.NET original:
     *   @ccod_cia, @ccod_tienda
     * Retorna: ccod_usuario, cdsc_usuario
     */
    public function CargarIdUsuario($codigo, $obj)
    {
        return Database::selectStoredTenant('webDatpos_cargarIdUsuario', array(
            '@ccod_cia' => $obj->ccod_empresa,
            '@ccod_tienda' => $codigo
        ), $obj);
    }
}
?>