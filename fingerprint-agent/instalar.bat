@echo off
:: ===================================================
::  INSTALADOR - Escaner de Huella Dactilar
::  Escuela Secundaria "Emperador Cuauhtemoc"
::
::  Este script:
::  1. Crea un acceso directo en el escritorio
::  2. Configura el inicio automatico con Windows
::  3. Verifica que todo este instalado
:: ===================================================

title Instalador - Escaner de Huella

:: Verificar permisos de administrador
net session >nul 2>&1
if errorlevel 1 (
    echo  Reabriendo como administrador...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

color 1F
echo.
echo  ╔══════════════════════════════════════════════╗
echo  ║   INSTALADOR DEL ESCANER DE HUELLA           ║
echo  ║   Sistema de Asistencia Escolar               ║
echo  ╚══════════════════════════════════════════════╝
echo.

set SCRIPT_PATH=%~dp0start_agent.bat
set DESKTOP=%USERPROFILE%\Desktop

:: 1. Verificar requisitos
echo  [1/4] Verificando requisitos...

where wsl >nul 2>&1
if errorlevel 1 (
    echo    [X] WSL no esta instalado. 
    echo        Ejecute: wsl --install
    goto :error
)
echo    [OK] WSL instalado

if exist "C:\Program Files\usbipd-win\usbipd.exe" (
    echo    [OK] USBIPD instalado
) else (
    echo    [X] USBIPD no esta instalado.
    echo        Descargue de: https://github.com/dorssel/usbipd-win/releases
    goto :error
)

if exist "C:\xampp\xampp-control.exe" (
    echo    [OK] XAMPP instalado
) else (
    echo    [!] XAMPP no detectado (solo necesario para pruebas locales)
)

echo.

:: 2. Crear acceso directo en escritorio
echo  [2/4] Creando acceso directo en escritorio...

powershell -Command "$WshShell = New-Object -ComObject WScript.Shell; $Shortcut = $WshShell.CreateShortcut('%DESKTOP%\Escaner de Huella.lnk'); $Shortcut.TargetPath = '%SCRIPT_PATH%'; $Shortcut.WorkingDirectory = '%~dp0'; $Shortcut.Description = 'Iniciar escaner de huella dactilar para el sistema de asistencia'; $Shortcut.Save()"

if exist "%DESKTOP%\Escaner de Huella.lnk" (
    echo    [OK] Acceso directo creado en el escritorio
) else (
    echo    [!] No se pudo crear el acceso directo
)
echo.

:: 3. Configurar inicio automatico con Windows
echo  [3/4] Configurando inicio automatico...

schtasks /create /tn "EscAner Huella - Sistema Asistencia" /tr "\"%SCRIPT_PATH%\"" /sc ONLOGON /rl HIGHEST /f >nul 2>&1

if errorlevel 1 (
    echo    [!] No se pudo configurar el inicio automatico.
    echo        Puede iniciar manualmente desde el escritorio.
) else (
    echo    [OK] El escaner se iniciara automaticamente al
    echo         encender la computadora.
)
echo.

:: 4. Verificar instalacion de dependencias en WSL
echo  [4/4] Verificando dependencias en Linux (WSL)...

wsl -d Ubuntu -u root -- bash -c "dpkg -l | grep -q libfprint && echo '[OK] libfprint instalado' || echo '[X] libfprint NO instalado - ejecute: sudo apt install libfprint-2-2 libfprint-2-tod1 gir1.2-fprint-2.0 fprintd'"
wsl -d Ubuntu -u root -- bash -c "python3 -c 'import websockets' 2>/dev/null && echo '[OK] websockets instalado' || echo '[X] websockets NO instalado - ejecute: pip3 install websockets'"

echo.
echo  ══════════════════════════════════════════════
echo   INSTALACION COMPLETADA
echo.
echo   - Acceso directo creado en el escritorio
echo   - El escaner se iniciara al encender la PC
echo   - Para uso manual: doble click en
echo     "Escaner de Huella" en el escritorio
echo  ══════════════════════════════════════════════
echo.
pause
exit /b

:error
echo.
echo  [!] La instalacion no se pudo completar.
echo      Resuelva los problemas indicados arriba
echo      y ejecute este instalador de nuevo.
echo.
pause
exit /b
