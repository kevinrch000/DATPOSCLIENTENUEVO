<?php
/**
 * DatPOS - Entidad AperturaCaja
 * Reemplaza: BE/BEAperturaCaja.vb
 */

#[AllowDynamicProperties]
class BEAperturaCaja {
    public $id_cbcajac = '';
    public $id_turno = '';
    public $ccod_tienda = '';
    public $cdsc_tienda = '';
    public $ccod_caja = '';
    public $cdsc_caja = '';
    public $ccod_usuario = '';
    public $cdsc_usuario = '';
    public $nmonto_ini = 0;
    public $nmonto_fin = 0;
    public $ntot_entreg = 0;
    public $ndiferencia = 0;
    public $dfecha_ini = '';
    public $dfecha_fin = '';
    public $cdsc_cbcajac = '';
    public $id_usuario_turno = '';
    public $cstatus = '';
    public $dfchdoc_ini = '';
    public $dfchdoc_fin = '';
    public $item = '';
    public $ilogo = '';
}
?>