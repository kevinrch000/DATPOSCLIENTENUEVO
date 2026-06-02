<?php
/**
 * DatPOS - Data Access: Usuario
 * Reemplaza: DA/DAUsuario.vb
 * 
 * Cada método invoca un stored procedure con parámetros NOMBRADOS
 * exactamente como lo hacía el código VB.NET original:
 *   cmd.Parameters.Add(New SqlParameter("@ccod_usuario", usuario))
 *   → array('@ccod_usuario' => $usuario)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../BE/BEUsuario.php';

class DAUsuario
{

    /**
     * Valida usuario contra BD Admin (sp_validarusuario)
     * Equivale a: DAUsuario.ValidarUsuario()
     * 
     * VB.NET original:
     *   cmd.Parameters.Add(New SqlParameter("@ccod_usuario", usuario))
     *   cmd.Parameters.Add(New SqlParameter("@cpassw", clave))
     *   cmd.CommandText = "sp_validarusuario"
     */
    public function validarUsuario($usuario, $clave)
    {
        return Database::selectStored('sp_validarusuario', array(
            '@ccod_usuario' => $usuario,
            '@cpassw' => $clave
        ));
    }

    /**
     * Consulta datos del usuario en BD Tenant (webDatpos_consultaUsuario)
     * Equivale a: DAUsuario.ConsultarUsuario()
     * 
     * VB.NET original:
     *   cmd.CommandText = "webDatpos_consultaUsuario"
     *   cmd.Parameters.Add(New SqlParameter("@ccod_usuario", codigo))
     */
    public function consultarUsuario($codigo, $objUsuario)
    {
        return Database::selectStoredTenant(
            'webDatpos_consultaUsuario',
            array('@ccod_usuario' => $codigo),
            $objUsuario
        );
    }

    /**
     * Consulta todos los usuarios de una empresa
     * Equivale a: DAUsuario.ConsultarUsuarios()
     * 
     * VB.NET original:
     *   cmd.Parameters.Add(New SqlParameter("@ccod_cia", objBE.ccod_empresa))
     *   cmd.CommandText = "webDatpos_consultaUsuario"
     */
    public function consultarUsuarios($objUsuario)
    {
        return Database::selectStoredTenant(
            'webDatpos_consultaUsuario',
            array('@ccod_cia' => $objUsuario->ccod_empresa),
            $objUsuario
        );
    }

    /**
     * Consulta turno del usuario
     * Equivale a: DAUsuario.ConsultarUsuarioTurno()
     * 
     * VB.NET original:
     *   cmd.Parameters.Add(New SqlParameter("@ccod_cia", objConex.ccod_empresa))
     *   cmd.Parameters.Add(New SqlParameter("@ccod_usuario", objConex.ccod_usuario))
     *   cmd.CommandText = "sp_consultarusuarioturno"
     */
    public function consultarUsuarioTurno($objUsuario)
    {
        return Database::selectStoredTenant(
            'sp_consultarusuarioturno',
            array(
                '@ccod_cia' => $objUsuario->ccod_empresa,
                '@ccod_usuario' => $objUsuario->ccod_usuario
            ),
            $objUsuario
        );
    }

    /**
     * Insertar usuario en BD Tenant
     * Equivale a: DAUsuario.InsertarUsuario()
     */
    public function insertarUsuario($objBE, $objConex)
    {
        $imageBytes = !empty($objBE->ifoto) ? base64_decode($objBE->ifoto) : '';

        return Database::executeStoredTenant(
            'sp_insertarusuarios',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdirec' => $objBE->cdirec,
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@id_rol' => $objBE->id_rol,
                '@id_estado' => $objBE->id_estado,
                '@ccod_tiend' => $objBE->ccod_tiend,
                '@ccod_almacen' => $objBE->ccod_almacen,
                '@ccod_caja' => $objBE->ccod_caja,
                '@ifoto' => $imageBytes,
                '@cusu_crea' => $objConex->ccod_usuario,
                '@ctarifas' => $objConex->ctarifas,
                '@nusuario_extra' => $objConex->nusuario_extra,
                '@cperm_descn' => $objBE->cperm_descn
            ),
            $objConex
        );
    }

    /**
     * Editar usuario en BD Tenant
     * Equivale a: DAUsuario.EditarUsuario()
     */
    public function editarUsuario($objBE, $objConex)
    {
        $imageBytes = !empty($objBE->ifoto) ? base64_decode($objBE->ifoto) : '';

        return Database::executeStoredTenant(
            'sp_editarusuario',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cdirec' => $objBE->cdirec,
                '@cdsc_usuario' => $objBE->cdsc_usuario,
                '@cpassw' => $objBE->cpassw,
                '@id_rol' => $objBE->id_rol,
                '@id_estado' => $objBE->id_estado,
                '@ccod_tiend' => $objBE->ccod_tiend,
                '@ccod_almacen' => $objBE->ccod_almacen,
                '@ccod_caja' => $objBE->ccod_caja,
                '@ifoto' => $imageBytes,
                '@cperm_descn' => $objBE->cperm_descn
            ),
            $objConex
        );
    }

    /**
     * Eliminar usuario en BD Tenant
     * Equivale a: DAUsuario.EliminarUsuario()
     */
    public function eliminarUsuario($cod, $objUsuario)
    {
        return Database::executeStoredTenant(
            'sp_eliminarusuario',
            array(
                '@ccod_cia' => $objUsuario->ccod_empresa,
                '@ccod_usuario' => $cod
            ),
            $objUsuario
        );
    }

    /**
     * Cambiar contraseña en BD Tenant
     * Equivale a: DAUsuario.CambiarContrasena()
     */
    public function cambiarContrasena($cambioClave, $objBE)
    {
        return Database::selectStoredTenant(
            'webDatpos_cambiarContrasena',
            array(
                '@ccod_cia' => $objBE->ccod_empresa,
                '@ccod_usuario' => $objBE->ccod_usuario,
                '@cpassw' => $cambioClave->cpassw,
                '@cpasswordnueva' => $cambioClave->cpasswordnueva
            ),
            $objBE
        );
    }

    /**
     * Cargar foto del usuario
     * Equivale a: DAUsuario.CargarFotoUsuario()
     */
    public function cargarFotoUsuario($objConex)
    {
        return Database::selectStoredTenant(
            'webDatpos_cargarFotoUsuario',
            array(
                '@ccod_cia' => $objConex->ccod_empresa,
                '@ccod_usuario' => $objConex->ccod_usuario
            ),
            $objConex
        );
    }

    /**
     * Buscar usuario por username para login (JWT/bcrypt).
     * Retorna datos del usuario + hashes de password (cpassw, cpassw_bcrypt)
     * sin filtrar por contrasena (la verificacion se hace en PHP).
     * Usa sp_buscarusuario_login en BD Admin.
     */
    public function buscarUsuarioLogin($usuario)
    {
        return Database::selectStored('sp_buscarusuario_login', array(
            '@ccod_usuario' => $usuario
        ));
    }

    /**
     * Buscar usuario por username para login en la BD Tenant (DatPos_EMP01).
     * Usado cuando el usuario NO existe en DatPosAdmin: se trata de un empleado
     * creado por un admin desde Administracion -> Usuarios.
     * Retorna datos del usuario + hashes (cpassw, cpassw_bcrypt) + id_rol
     * sin filtrar por contrasena (la verificacion se hace en PHP).
     *
     * @param string    $usuario   Codigo de usuario
     * @param BEUsuario $objConex  Contexto con cnomser/cnombre_bd del tenant
     */
    public function buscarUsuarioLoginTenant($usuario, $objConex)
    {
        return Database::selectStoredTenant('sp_buscarusuario_login', array(
            '@ccod_usuario' => $usuario
        ), $objConex);
    }

    /**
     * Listar empresas registradas en DatPosAdmin (sp_consultarempresas).
     * Se usa para resolver la metadata de la empresa (moneda, RUC, BD, servidor)
     * en el login directo de empleados del tenant.
     * Columnas: [0]ccod_empresa [1]cdescripcion [2]cdoc [3]cnum_tribu [4]cnomser [5]cnombre_bd
     */
    public function consultarEmpresas()
    {
        return Database::selectStored('sp_consultarempresas', array());
    }

    /**
     * Migrar password a bcrypt en BD Tenant (DatPos_EMP01).
     */
    public function migrarPasswordBcryptTenant($ccod_empresa, $ccod_usuario, $bcryptHash, $objConex)
    {
        return Database::executeStoredTenant('sp_migrar_password_bcrypt', array(
            '@ccod_empresa'  => $ccod_empresa,
            '@ccod_usuario'  => $ccod_usuario,
            '@cpassw_bcrypt' => $bcryptHash
        ), $objConex);
    }

    /**
     * Migrar password a bcrypt en BD Admin.
     * Actualiza cpassw_bcrypt con el nuevo hash y reemplaza cpassw con MD5
     * (para eliminar texto plano de la BD).
     */
    public function migrarPasswordBcrypt($ccod_usuario, $bcryptHash, $md5Hash = null)
    {
        $params = array(
            '@ccod_usuario' => $ccod_usuario,
            '@cpassw_bcrypt' => $bcryptHash
        );
        if ($md5Hash !== null) {
            $params['@cpassw_md5'] = $md5Hash;
        }
        return Database::executeStored('sp_migrar_password_bcrypt', $params);
    }
}
?>