@echo off
REM ============================================================
REM DatPOS — Levanta el servidor PHP embebido en :8080
REM
REM Requisitos:
REM   - PHP 8+ con extensiones: sqlsrv, pdo_sqlsrv (Microsoft SQL).
REM   - php.exe debe estar en PATH.
REM
REM Acceso: http://localhost:8080
REM ============================================================
cd /d "%~dp0..\"
echo === DatPOS PHP server escuchando en http://localhost:8080 ===
echo === Ctrl+C para detener ===
php -S localhost:8080 router.php
