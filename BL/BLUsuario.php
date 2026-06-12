<?php
/**
 * DatPOS - Business Logic: Usuario
 * Reemplaza: BL/BLUsuario.vb
 * 
 * Fachada que delega al DA (igual que el original VB.NET)
 */

require_once __DIR__ . '/../DA/DAUsuario.php';

class BLUsuario {

    private $objDA;

    public function __construct() {
        $this->objDA = new DAUsuario();
    }

    public function validarUsuario($usuario, $clave) {
        return $this->objDA->validarUsuario($usuario, $clave);
    }

    public function consultarUsuario($codigo, $objBE) {
        return $this->objDA->consultarUsuario($codigo, $objBE);
    }

    /** Alias usado por usuario_api.php :: ConsultarUsuario (detalle por código) */
    public function consultarUsuarioDetalle($codigo, $objBE) {
        return $this->objDA->consultarUsuario($codigo, $objBE);
    }

    public function consultarUsuarios($objBE) {
        return $this->objDA->consultarUsuarios($objBE);
    }

    public function consultarUsuarioTurno($objBE) {
        return $this->objDA->consultarUsuarioTurno($objBE);
    }

    public function insertarUsuario($objBE, $objConex) {
        return $this->objDA->insertarUsuario($objBE, $objConex);
    }

    public function editarUsuario($objBE, $objConex) {
        return $this->objDA->editarUsuario($objBE, $objConex);
    }

    public function eliminarUsuario($cod, $obj) {
        return $this->objDA->eliminarUsuario($cod, $obj);
    }

    public function cambiarContrasena($cambioClave, $objBE) {
        return $this->objDA->cambiarContrasena($cambioClave, $objBE);
    }

    public function cargarFotoUsuario($obj) {
        return $this->objDA->cargarFotoUsuario($obj);
    }

    /**
     * Buscar usuario por username para login (sin filtrar por password).
     * El SP retorna la fila con hashes de password para verificacion PHP-side.
     */
    public function buscarUsuarioLogin($usuario) {
        return $this->objDA->buscarUsuarioLogin($usuario);
    }

    /**
     * Buscar usuario por username para login en la BD Tenant (DatPos_EMP01).
     * Se usa como segundo origen de login (empleados creados por un admin).
     */
    public function buscarUsuarioLoginTenant($usuario, $objConex) {
        return $this->objDA->buscarUsuarioLoginTenant($usuario, $objConex);
    }

    /** Lista de empresas (DatPosAdmin) para resolver metadata del tenant. */
    public function consultarEmpresas() {
        return $this->objDA->consultarEmpresas();
    }

    /** Migrar password a bcrypt en la BD Tenant. */
    public function migrarPasswordBcryptTenant($ccod_empresa, $ccod_usuario, $bcryptHash, $objConex) {
        return $this->objDA->migrarPasswordBcryptTenant($ccod_empresa, $ccod_usuario, $bcryptHash, $objConex);
    }

    /**
     * Migrar password a bcrypt en BD Admin.
     * Tambien reemplaza cpassw con MD5 para eliminar texto plano.
     */
    public function migrarPasswordBcrypt($ccod_usuario, $bcryptHash, $md5Hash = null) {
        return $this->objDA->migrarPasswordBcrypt($ccod_usuario, $bcryptHash, $md5Hash);
    }
}
?>
