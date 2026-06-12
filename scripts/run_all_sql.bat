@echo off
REM ============================================================
REM DatPOS — Aplica todos los scripts SQL en orden
REM
REM Uso:
REM   run_all_sql.bat                       (server por defecto: localhost\SQLEXPRESS)
REM   run_all_sql.bat "TU_SERVER\INSTANCIA"
REM
REM Requisitos:
REM   - sqlcmd debe estar en PATH (viene con SQL Server / SSMS).
REM   - Las bases DatPosAdmin y DatPos_EMP01 deben existir vacias
REM     (los scripts crean tablas y datos seed).
REM ============================================================
setlocal EnableDelayedExpansion
cd /d "%~dp0sql"

set SERVER=%~1
if "%SERVER%"=="" set SERVER=localhost\SQLEXPRESS

echo === Aplicando SQL en %SERVER% ===
echo.

set FAIL=0
for %%f in (*.sql) do (
    echo --- %%f ---
    sqlcmd -S "%SERVER%" -E -b -i "%%f"
    if errorlevel 1 (
        echo.
        echo XXX ERROR aplicando %%f
        set FAIL=1
        goto :end
    )
    echo.
)

:end
if !FAIL! equ 1 (
    echo.
    echo === Hubo errores. Revisa el script anterior. ===
    exit /b 1
) else (
    echo.
    echo === Todo aplicado correctamente ===
)
endlocal
