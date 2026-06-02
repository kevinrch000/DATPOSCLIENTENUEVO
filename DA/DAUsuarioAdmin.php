<?php
require_once __DIR__ . '/../config/database.php';

class DAUsuarioAdmin {

    public function consultarUsuarios($objConex) {
        return Database::selectStored(
            'webDatpos_consultaUsuario',
            array('@ccod_cia' => $objConex->ccod_empresa)
        );
    }

    public function consultarUsuario($codigo, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_consultaUsuario',
            array(
                '@ccod_usuario' => $codigo
            ),
            $objConex
        );
    }

    public function validarUsuario($usuario, $clave) {
        return Database::selectStored(
            'sp_validarusuario',
            array(
                '@ccod_usuario' => $usuario,
                '@cpassw' => $clave
            )
        );
    }

    public function cambiarContrasena($cambioClave, $objConex) {
        return Database::selectStoredTenant(
            'webDatpos_cambiarContrasena',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@cpassw' => $cambioClave->cpassw,
                '@cpasswordnueva' => $cambioClave->cpasswordnueva
            ),
            $objConex
        );
    }

    public function consultarUsuarioTurno($objConex) {
        return Database::selectStoredTenant(
            'sp_consultarusuarioturno',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario
            ),
            $objConex
        );
    }

    public function cargarFotoUsuario($objConex) {
        return Database::selectStoredTenant(
            'webDatpos_cargarFotoUsuario',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario
            ),
            $objConex
        );
    }

    public function insertarUsuarioAdmin($objBE, $objConex) {
        return Database::selectStored(
            'sp_insertarusuarios',
            array(
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@cdirec' => $objBE->cdirec ?? '',
                '@id_rol' => $objBE->id_rol,
                '@ccod_empresa' => $objConex->ccod_empresa,
                '@id_estado' => $objBE->id_estado ?? 1,
                '@cusu_crea' => $objConex->ccod_usuario,
                '@ctarifas' => $objConex->ctarifas ?? '',
                '@nusuario_extra' => $objConex->nusuario_extra ?? ''
            )
        );
    }

    public function insertarUsuario($objBE, $objConex) {
        $imageBytes = !empty($objBE->ifoto) ? base64_decode($objBE->ifoto) : '';
        return Database::executeStoredTenant(
            'sp_insertarusuarios',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdirec' => $objBE->cdirec ?? '',
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@id_rol' => $objBE->id_rol,
                '@id_estado' => $objBE->id_estado ?? 1,
                '@ccod_tiend' => $objBE->ccod_tiend,
                '@ccod_almacen' => $objBE->ccod_almacen ?? '',
                '@ccod_caja' => $objBE->ccod_caja ?? '',
                '@ifoto' => $imageBytes,
                '@cusu_crea' => $objConex->ccod_usuario,
                '@ctarifas' => $objConex->ctarifas ?? '',
                '@nusuario_extra' => $objConex->nusuario_extra ?? '',
                '@cperm_descn' => $objBE->cperm_descn ?? '0'
            ),
            $objConex
        );
    }

    public function editarUsuarioAdmin($objBE, $objConex) {
        return Database::executeStored(
            'sp_editarusuariocliente',
            array(
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@cdirec' => $objBE->cdirec ?? '',
                '@id_rol' => $objBE->id_rol,
                '@ccod_empresa' => $objConex->ccod_empresa,
                '@id_estado' => $objBE->id_estado ?? 1
            )
        );
    }

    public function editarUsuario($objBE, $objConex) {
        $imageBytes = !empty($objBE->ifoto) ? base64_decode($objBE->ifoto) : '';
        return Database::executeStoredTenant(
            'sp_editarusuario',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdirec' => $objBE->cdirec ?? '',
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@id_rol' => $objBE->id_rol,
                '@id_estado' => $objBE->id_estado ?? 1,
                '@ccod_tiend' => $objBE->ccod_tiend,
                '@ccod_almacen' => $objBE->ccod_almacen ?? '',
                '@ccod_caja' => $objBE->ccod_caja ?? '',
                '@ifoto' => $imageBytes,
                '@cperm_descn' => $objBE->cperm_descn ?? '0'
            ),
            $objConex
        );
    }

    public function eliminarUsuarioAdmin($usuario, $objConex) {
        return Database::executeStored(
            'sp_eliminarusuariocliente',
            array(
                '@ccod_usuario' => $usuario,
                '@ccod_empresa' => $objConex->ccod_empresa
            )
        );
    }

    public function eliminarUsuario($usuario, $objConex) {
        return Database::executeStoredTenant(
            'sp_eliminarusuario',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $usuario
            ),
            $objConex
        );
    }

    public function cambioContrasenaAdmin($cambioClave, $objConex) {
        return Database::executeStored(
            'webDatpos_cambiarContrasena',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario,
                '@cpassw' => $cambioClave->cpassw,
                '@cpasswordnueva' => $cambioClave->cpasswordnueva
            )
        );
    }

    public function consultarAlmEmpActivos($tienda, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultaalmempactivos',
            array(
                '@ccod_tiend' => $tienda,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }

    public function consultarCajasEmpActivos($tienda, $objConex) {
        return Database::selectStoredTenant(
            'sp_consultacajasempactivos',
            array(
                '@ccod_tiend' => $tienda,
                '@ccod_cia' => $objConex->ccod_empresa
            ),
            $objConex
        );
    }
}
?>
