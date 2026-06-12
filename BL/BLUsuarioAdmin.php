<?php
/**
 * DatPOS - BL: Usuario (Administración)
 */
require_once __DIR__ . '/../DA/DAUsuarioAdmin.php';

class BLUsuarioAdmin {
    private $objDA;
    public function __construct() { $this->objDA = new DAUsuarioAdmin(); }
    public function consultarUsuarios($o) { return $this->objDA->consultarUsuarios($o); }
    public function consultarUsuario($c, $o) { return $this->objDA->consultarUsuario($c, $o); }
    public function insertarUsuarioAdmin($b, $o) { return $this->objDA->insertarUsuarioAdmin($b, $o); }
    public function insertarUsuario($b, $o) { return $this->objDA->insertarUsuario($b, $o); }
    public function editarUsuarioAdmin($b, $o) { return $this->objDA->editarUsuarioAdmin($b, $o); }
    public function editarUsuario($b, $o) { return $this->objDA->editarUsuario($b, $o); }
    public function eliminarUsuarioAdmin($u, $o) { return $this->objDA->eliminarUsuarioAdmin($u, $o); }
    public function eliminarUsuario($u, $o) { return $this->objDA->eliminarUsuario($u, $o); }
    public function consultarAlmEmpActivos($t, $o) { return $this->objDA->consultarAlmEmpActivos($t, $o); }
    public function consultarCajasEmpActivos($t, $o) { return $this->objDA->consultarCajasEmpActivos($t, $o); }
    public function validarUsuario($u, $c) { return $this->objDA->validarUsuario($u, $c); }
    public function cambiarContrasena($cc, $o) { return $this->objDA->cambiarContrasena($cc, $o); }
    public function consultarUsuarioTurno($o) { return $this->objDA->consultarUsuarioTurno($o); }
    public function cargarFotoUsuario($o) { return $this->objDA->cargarFotoUsuario($o); }
}
?>
