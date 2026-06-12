<?php
/**
 * DatPOS - Clase de Conexión a SQL Server (Multi-Tenant)
 * Reemplaza: DA/DAConexionSQL.vb
 * 
 * Soporta dos modos de conexión:
 *   1. Conexión Admin (cadena fija desde config) → selectStored / executeStored
 *   2. Conexión Tenant (dinámica por usuario)    → selectStoredTenant / executeStoredTenant
 * 
 * IMPORTANTE: Los parámetros se pasan como array asociativo:
 *   array('@ccod_usuario' => 'ADMIN', '@cpassw' => '123456')
 * Esto replica exactamente el SqlParameter("@name", value) de VB.NET
 */

class Database
{
    // ============================================================
    // Configuración de la conexión ADMIN (DatPosAdmin)
    // ============================================================
    private static $adminServer = 'localhost\\SQLEXPRESS';
    private static $adminDatabase = 'DatPosAdmin';
    private static $adminUser = '';       // Vacío = Integrated Security (Windows Auth)
    private static $adminPassword = '';

    // Credenciales del tenant (hardcoded en el original)
    private static $tenantUser = 'U76GY';
    private static $tenantPassword = 'ADM';

    // ============================================================
    // Construir la query SQL con parámetros nombrados
    // Convierte: array('@ccod_usuario' => 'ADMIN', '@cpassw' => '123')
    // En:        "EXEC sp_name @ccod_usuario = ?, @cpassw = ?"
    //            + array('ADMIN', '123') para sqlsrv_query
    // ============================================================
    private static function buildQuery($spName, $params)
    {
        if (empty($params)) {
            return array("EXEC {$spName}", array());
        }

        $placeholders = array();
        $values = array();

        foreach ($params as $name => $value) {
            // Asegurar que el nombre empiece con @
            $paramName = (strpos($name, '@') === 0) ? $name : '@' . $name;
            $placeholders[] = "{$paramName} = ?";
            $values[] = $value;
        }

        $sql = "EXEC {$spName} " . implode(", ", $placeholders);
        return array($sql, $values);
    }

    // ============================================================
    // Obtener conexión ADMIN (equivale a "cadenaconexion" del Web.config)
    // ============================================================
    public static function getAdminConnection()
    {
        $adminServer = getenv('DATPOS_ADMIN_SERVER') ?: self::$adminServer;
        $adminDatabase = getenv('DATPOS_ADMIN_DATABASE') ?: self::$adminDatabase;
        $adminUser = getenv('DATPOS_ADMIN_USER');
        $adminPassword = getenv('DATPOS_ADMIN_PASSWORD');
        if ($adminUser === false) { $adminUser = self::$adminUser; }
        if ($adminPassword === false) { $adminPassword = self::$adminPassword; }

        $connectionInfo = array(
            "Database" => $adminDatabase,
            "CharacterSet" => "UTF-8",
            "TrustServerCertificate" => true,
            "ReturnDatesAsStrings" => true
        );

        // Si hay usuario, usar SQL Auth; si no, Windows Auth
        if (!empty($adminUser)) {
            $connectionInfo["UID"] = $adminUser;
            $connectionInfo["PWD"] = $adminPassword;
        }

        $conn = sqlsrv_connect($adminServer, $connectionInfo);

        if ($conn === false) {
            error_log("Error conexión Admin: " . print_r(sqlsrv_errors(), true));
            return false;
        }

        return $conn;
    }

    // ============================================================
    // Obtener conexión TENANT (dinámica, equivale a OtraConexion)
    // ============================================================
    public static function getTenantConnection($objUsuario)
    {
        if (!$objUsuario) {
            error_log("getTenantConnection: objUsuario es NULL");
            return false;
        }
        
        $server = getenv('DATPOS_TENANT_SERVER') ?: ($objUsuario->cnomser ?? '');
        $database = getenv('DATPOS_TENANT_DATABASE') ?: ($objUsuario->cnombre_bd ?? '');
        $tenantUser = getenv('DATPOS_TENANT_USER');
        $tenantPassword = getenv('DATPOS_TENANT_PASSWORD');
        if ($tenantUser === false) { $tenantUser = self::$tenantUser; }
        if ($tenantPassword === false) { $tenantPassword = self::$tenantPassword; }
        
        if (empty($server) || empty($database)) {
            error_log("getTenantConnection: server o database vacíos. cnmiser='$server', cnombre_bd='$database'");
            return false;
        }

        $connectionInfo = array(
            "Database" => $database,
            "UID" => $tenantUser,
            "PWD" => $tenantPassword,
            "CharacterSet" => "UTF-8",
            "TrustServerCertificate" => true,
            "ReturnDatesAsStrings" => true
        );

        $conn = sqlsrv_connect($server, $connectionInfo);

        if ($conn === false) {
            error_log("Error conexión Tenant [{$server}/{$database}]: " . print_r(sqlsrv_errors(), true));
            return false;
        }

        // El login U76GY tiene default_language=Español, lo que hace que las conversiones
        // implícitas string→DATETIME usen formato dmy. Forzamos us_english + ymd para que
        // los SPs que hacen CONVERT(NVARCHAR,GETDATE(),120) y similares funcionen sin error 242.
        sqlsrv_query($conn, "SET LANGUAGE us_english; SET DATEFORMAT ymd;");

        return $conn;
    }

    /**
     * Retorna los datos de conexión del tenant para uso directo con sqlsrv_connect
     * Se usa en DAAlmacen, DARoles, etc. que manejan transacciones propias
     */
    public static function buildTenantConnectionString($objUsuario)
    {
        return array(
            'server' => $objUsuario->cnomser,
            'connectionInfo' => array(
                "Database" => $objUsuario->cnombre_bd,
                "UID" => self::$tenantUser,
                "PWD" => self::$tenantPassword,
                "CharacterSet" => "UTF-8",
                "TrustServerCertificate" => true
            )
        );
    }

    // ============================================================
    // selectStored — Ejecuta SP en BD Admin, retorna array de filas
    // Equivale a: DAConexionSQL.selectstored()
    // 
    // Uso: Database::selectStored('sp_validarusuario', array(
    //          '@ccod_usuario' => 'ADMIN',
    //          '@cpassw'       => '123456'
    //      ));
    // ============================================================
    public static function selectStored($spName, $params = array())
    {
        $conn = self::getAdminConnection();
        if (!$conn)
            return array();

        list($sql, $values) = self::buildQuery($spName, $params);

        $stmt = sqlsrv_query($conn, $sql, $values);

        $results = array();
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $results[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("Error selectStored [{$spName}]: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);
        return $results;
    }

    // ============================================================
    // executeStored — Ejecuta SP en BD Admin, retorna true/false
    // Equivale a: DAConexionSQL.executestored()
    // ============================================================
    public static function executeStored($spName, $params = array())
    {
        $conn = self::getAdminConnection();
        if (!$conn)
            return false;

        list($sql, $values) = self::buildQuery($spName, $params);

        $stmt = sqlsrv_query($conn, $sql, $values);

        $success = ($stmt !== false);
        if ($stmt) {
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("Error executeStored [{$spName}]: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);
        return $success;
    }

    // ============================================================
    // selectStoredTenant — Ejecuta SP en BD del Tenant, retorna array
    // Equivale a: DAConexionSQL.selectstored_OtraConexion()
    // ============================================================
    public static function selectStoredTenant($spName, $params = array(), $objUsuario = null)
    {
        if (!$objUsuario)
            return array();

        $conn = self::getTenantConnection($objUsuario);
        if (!$conn)
            return array();

        list($sql, $values) = self::buildQuery($spName, $params);

        $stmt = sqlsrv_query($conn, $sql, $values);

        $results = array();
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_NUMERIC)) {
                $results[] = $row;
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("Error selectStoredTenant [{$spName}]: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);
        return $results;
    }

    // ============================================================
    // executeStoredTenant — Ejecuta SP en BD del Tenant, retorna true/false
    // Equivale a: DAConexionSQL.executestored_OtraConexion()
    // ============================================================
    public static function executeStoredTenant($spName, $params = array(), $objUsuario = null)
    {
        if (!$objUsuario)
            return false;

        $conn = self::getTenantConnection($objUsuario);
        if (!$conn)
            return false;

        list($sql, $values) = self::buildQuery($spName, $params);

        $stmt = sqlsrv_query($conn, $sql, $values);

        $success = ($stmt !== false);
        if ($stmt) {
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("Error executeStoredTenant [{$spName}]: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);
        return $success;
    }

    // ============================================================
    // executeStoredTenantReturnId — Ejecuta SP y retorna scalar (ID)
    // Equivale a: DAConexionSQL.executestored_OtraConexion_Id()
    // ============================================================
    public static function executeStoredTenantReturnId($spName, $params = array(), $objUsuario = null)
    {
        if (!$objUsuario)
            return 0;

        $conn = self::getTenantConnection($objUsuario);
        if (!$conn)
            return 0;

        list($sql, $values) = self::buildQuery($spName, $params);

        $stmt = sqlsrv_query($conn, $sql, $values);

        $id = 0;
        if ($stmt) {
            if (sqlsrv_fetch($stmt)) {
                $id = sqlsrv_get_field($stmt, 0);
            }
            sqlsrv_free_stmt($stmt);
        }

        sqlsrv_close($conn);
        return intval($id);
    }

    // ============================================================
    // Ejecutar SP con parámetros de OUTPUT
    // Para SPs que retornan valores via OUTPUT params
    // ============================================================
    public static function executeStoredTenantWithOutput($spName, &$params, $objUsuario = null)
    {
        if (!$objUsuario)
            return false;

        $conn = self::getTenantConnection($objUsuario);
        if (!$conn)
            return false;

        // Construir la query SQL con parámetros
        $paramParts = array();
        $paramValues = array();
        $declarations = "";
        $outputSelect = array();

        foreach ($params as $name => &$paramDef) {
            $paramName = (strpos($name, '@') === 0) ? $name : '@' . $name;

            if (isset($paramDef['direction']) && $paramDef['direction'] === 'output') {
                // Tipo SQL del OUTPUT param (default NVARCHAR(200) para texto)
                $sqlType = $paramDef['type'] ?? 'NVARCHAR(200)';
                $declarations .= "DECLARE {$paramName} {$sqlType};\n";
                $paramParts[] = "{$paramName} = {$paramName} OUTPUT";
                $outputSelect[] = "{$paramName} AS [{$name}]";
            } else {
                $paramParts[] = "{$paramName} = ?";
                $paramValues[] = $paramDef['value'];
            }
        }

        $sql = $declarations;
        $sql .= "EXEC {$spName} " . implode(", ", $paramParts) . ";\n";
        if (!empty($outputSelect)) {
            $sql .= "SELECT " . implode(", ", $outputSelect) . ";";
        }

        $stmt = sqlsrv_query($conn, $sql, $paramValues);

        $result = array('success' => false);
        if ($stmt) {
            $result['success'] = true;
            if (!empty($outputSelect)) {
                // Avanzar a través de cualquier resultset previo (rows-affected, etc.)
                // hasta encontrar el del SELECT con los OUTPUT params.
                $row = false;
                do {
                    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                    if ($row !== null && $row !== false) break;
                } while (sqlsrv_next_result($stmt));

                if ($row) {
                    foreach ($row as $key => $val) {
                        if (isset($params[$key])) {
                            $params[$key]['value'] = $val;
                        }
                        $result[$key] = $val;
                    }
                }
            }
            sqlsrv_free_stmt($stmt);
        } else {
            error_log("Error executeStoredTenantWithOutput [{$spName}]: " . print_r(sqlsrv_errors(), true));
        }

        sqlsrv_close($conn);
        return $result;
    }
}
?>