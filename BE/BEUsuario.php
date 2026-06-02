<?php
/**
 * DatPOS - Entidad Usuario
 * Reemplaza: BE/BEUsuario.vb
 */

#[AllowDynamicProperties]
class BEUsuario {
    // Datos de autenticación / Admin
    public $id_ctusu          = 0;
    public $ccod_usuario      = '';
    public $cdsc_usuario      = '';
    public $cpassw            = '';
    public $cdirec            = '';
    public $id_rol            = 0;
    public $cdsc_rol          = '';
    public $id_empresa        = 0;
    public $ccod_empresa      = '';
    public $id_estado         = 0;
    public $estado            = '';

    // Datos de conexión al tenant
    public $cnombre_bd        = '';
    public $cnomser           = '';
    public $cdescripcion      = '';

    // Datos de tienda/almacen/caja
    public $ccod_tiend        = '';
    public $cdsc_tienda       = '';
    public $ccod_almacen      = '';
    public $ccod_caja         = '';

    // Datos tributarios / empresa
    public $cnum_tribu        = '';
    public $contrasena        = '';
    public $ntienda_extra     = 0;
    public $nusuario_extra    = 0;
    public $ctarifas          = '';

    // Imágenes
    public $ifoto             = '';
    public $ilogo             = '';

    // Moneda
    public $cnombre_moneda    = '';
    public $csimbolo_moneda   = '';

    // Contraseña
    public $cpasswordnueva    = '';
    public $cperm_descn       = '';
    public $item              = '';

    // Dirección tienda
    public $cdirc_tienda      = '';
    public $cdomicilio        = '';
    public $cprovincia        = '';
    public $cdistrito         = '';
    public $cdepartamento     = '';

    // Datos tienda adicionales
    public $ctelf_tienda           = '';
    public $cprovincia_tienda      = '';
    public $cdistrito_tienda       = '';
    public $cdepartamento_tienda   = '';

    // Rol y facturación
    public $rolMaster         = 0;
    public $ctip_facturador   = '';
    public $dfch_vencimiento  = '';
    public $ccod_cliente_emis = '';
    public $ctoken            = '';
}
?>
