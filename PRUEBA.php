<?php
require_once __DIR__ . '/config/database.php';

echo "ADMIN_SERVER env: " . (getenv('DATPOS_ADMIN_SERVER') ?: '(vacio)') . "\n";
echo "TENANT_SERVER env: " . (getenv('DATPOS_TENANT_SERVER') ?: '(vacio)') . "\n";

// Probar conexion Admin
$c1 = Database::getAdminConnection();
echo $c1 ? "Admin OK\n" : "Admin FAIL: " . print_r(sqlsrv_errors(), true) . "\n";

// Probar conexion Tenant simulada
$u = new stdClass();
$u->cnomser     = getenv('DATPOS_TENANT_SERVER') ?: 'localhost\\SQLEXPRESS';
$u->cnombre_bd  = 'DatPos_EMP01';
$c2 = Database::getTenantConnection($u);
echo $c2 ? "Tenant OK\n" : "Tenant FAIL: " . print_r(sqlsrv_errors(), true) . "\n";