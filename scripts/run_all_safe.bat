@echo off
REM ============================================================
REM DatPOS — Aplica todos los scripts SQL en orden (VERSION SEGURA)
REM
REM Diferencia con run_all_sql.bat:
REM   - NO se detiene ante el primer error
REM   - Registra todos los errores en run_all_safe.log
REM   - Al final muestra cuantos scripts fallaron
REM   - Crea las bases DatPosAdmin y DatPos_EMP01 si no existen
REM
REM Uso:
REM   run_all_safe.bat                       (server: localhost\SQLEXPRESS)
REM   run_all_safe.bat "TU_SERVER\INSTANCIA"
REM
REM Requisitos:
REM   - sqlcmd en PATH (viene con SQL Server / SSMS)
REM ============================================================
setlocal EnableDelayedExpansion
cd /d "%~dp0sql"

set SERVER=%~1
if "%SERVER%"=="" set SERVER=localhost\SQLEXPRESS

set LOGFILE=%~dp0run_all_safe.log
set ERRORS=0
set TOTAL=0

echo === Aplicando SQL en %SERVER% === > "%LOGFILE%"
echo Fecha: %date% %time% >> "%LOGFILE%"
echo. >> "%LOGFILE%"

echo === Aplicando SQL en %SERVER% ===
echo Log: %LOGFILE%
echo.

REM --- Crear bases de datos si no existen ---
echo Verificando bases de datos...
echo Verificando bases de datos... >> "%LOGFILE%"
sqlcmd -S "%SERVER%" -E -Q "IF NOT EXISTS (SELECT 1 FROM sys.databases WHERE name='DatPosAdmin') CREATE DATABASE DatPosAdmin; IF NOT EXISTS (SELECT 1 FROM sys.databases WHERE name='DatPos_EMP01') CREATE DATABASE DatPos_EMP01;" >> "%LOGFILE%" 2>&1
if errorlevel 1 (
    echo [ERROR] No se pudo conectar a SQL Server en %SERVER%
    echo [ERROR] No se pudo conectar a SQL Server en %SERVER% >> "%LOGFILE%"
    echo.
    echo Verifica que SQL Server este corriendo y que sqlcmd este en el PATH.
    echo Si tu instancia no es localhost\SQLEXPRESS, ejecuate asi:
    echo   run_all_safe.bat "TU_PC\SQLEXPRESS"
    pause
    exit /b 1
)
echo   [OK] Bases de datos listas
echo   [OK] Bases de datos listas >> "%LOGFILE%"
echo. >> "%LOGFILE%"
echo.

for %%f in (*.sql) do (
    set /a TOTAL+=1
    echo --- %%f ---
    echo --- %%f --- >> "%LOGFILE%"

    sqlcmd -S "%SERVER%" -E -b -i "%%f" >> "%LOGFILE%" 2>&1
    if errorlevel 1 (
        echo   [ERROR] %%f ^(ver log para detalles^)
        echo   [ERROR] %%f >> "%LOGFILE%"
        set /a ERRORS+=1
    ) else (
        echo   [OK]
        echo   [OK] >> "%LOGFILE%"
    )
    echo. >> "%LOGFILE%"
)

echo.
echo === Resumen ===
echo === Resumen === >> "%LOGFILE%"
echo Scripts totales : !TOTAL!
echo Scripts totales : !TOTAL! >> "%LOGFILE%"
echo Scripts con error: !ERRORS!
echo Scripts con error: !ERRORS! >> "%LOGFILE%"

if !ERRORS! gtr 0 (
    echo.
    echo Revisa el log: %LOGFILE%
    echo ATENCION: Algunos scripts fallaron. Revisa el log. >> "%LOGFILE%"
    exit /b 1
) else (
    echo Todo aplicado correctamente.
    echo OK: Todo aplicado correctamente. >> "%LOGFILE%"
)

endlocal
