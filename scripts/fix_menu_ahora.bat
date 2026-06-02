@echo off
REM Aplica SOLO el script 720 para restaurar el menú
REM (fix para FK_Menus_MenuPadre que rompía el INSERT con nid_menupadre=0)

set SERVER=%~1
if "%SERVER%"=="" set SERVER=localhost\SQLEXPRESS

echo === Restaurando menu en %SERVER% ===
sqlcmd -S "%SERVER%" -E -b -i "%~dp0sql\720_FIX_42_Menu_ConForeignKey.sql"
if errorlevel 1 (
    echo [ERROR] El script fallo. Revisa la consola arriba.
) else (
    echo [OK] Menu restaurado correctamente.
    echo Puedes recargar el navegador y el menu deberia aparecer.
)
pause
