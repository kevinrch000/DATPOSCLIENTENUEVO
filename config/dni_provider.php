<?php
/**
 * Configuración del proveedor de consulta DNI.
 *
 * Para cambiar de servicio, ajusta provider/base_url/token y, si la respuesta
 * cambia de forma, agrega un normalizador en includes/dni_lookup.php.
 */
function dniProviderConfig()
{
    return array(
        'provider' => getenv('DATPOS_DNI_PROVIDER') ?: 'consultadni',
        'base_url' => getenv('DATPOS_DNI_API_URL') ?: 'https://www.consultadni.com/api/v1/dni/simple',
        'token' => getenv('DATPOS_DNI_API_TOKEN') ?: 'cdni_81ea3032661e17d072944a0ab4cbc3b3',
        'timeout' => intval(getenv('DATPOS_DNI_API_TIMEOUT') ?: 5),
    );
}
?>