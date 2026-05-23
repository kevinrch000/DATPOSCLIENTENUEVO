<?php
/**
 * DatPOS - Business Logic: AperturaCaja
 * Reemplaza: BL/BLAperturaCaja.vb
 */

require_once __DIR__ . '/../DA/DAAperturaCaja.php';

class BLAperturaCaja
{
    private $objDA;

    public function __construct()
    {
        $this->objDA = new DAAperturaCaja();
    }

    public function CierreCaja($DatTurno, $obj)
    {
        return $this->objDA->CierreCaja($DatTurno, $obj);
    }

    public function AperturarCaja($DatTurno, $obj)
    {
        return $this->objDA->AperturarCaja($DatTurno, $obj);
    }

    public function ConsultarCierreCaja($obj)
    {
        return $this->objDA->ConsultarCierreCaja($obj);
    }

    public function ConsultarIdCierreCaja($id_turno, $obj)
    {
        return $this->objDA->ConsultarIdCierreCaja($id_turno, $obj);
    }

    public function CargarTurnoUsuario($id_usuario, $obj)
    {
        return $this->objDA->CargarTurnoUsuario($id_usuario, $obj);
    }

    public function CargarIdUsuario($codigo, $obj)
    {
        return $this->objDA->CargarIdUsuario($codigo, $obj);
    }

    public function CargarCajaDeUsuario($ccod_usuario, $obj)
    {
        return $this->objDA->CargarCajaDeUsuario($ccod_usuario, $obj);
    }
}
?>