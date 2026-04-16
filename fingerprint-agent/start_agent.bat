@echo off
chcp 65001 >nul 2>&1
:: ===================================================
::  ESCANER DE HUELLA DACTILAR
::  Escuela Secundaria "Emperador Cuauhtemoc"
:: ===================================================

title Escaner de Huella - Sistema de Asistencia

color 1F
echo.
echo  ==================================================
echo    SISTEMA DE ASISTENCIA ESCOLAR
echo    Escaner de Huella Dactilar - U.are.U 4500
echo  ==================================================
echo.

:: Verificar permisos de administrador
net session >nul 2>&1
if errorlevel 1 (
    color 4F
    echo  [!] Se necesitan permisos de administrador.
    echo      Reabriendo...
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo  [OK] Permisos de administrador verificados.
echo.

:: Paso 1: Activar WSL
echo  [1/3] Activando sistema Linux (WSL)...
wsl -d Ubuntu -- echo listo >nul 2>&1
start /B wsl -d Ubuntu -- sleep 99999
timeout /t 3 /nobreak >nul
echo         Listo.
echo.

:retry_usb
:: Paso 2: Auto-detectar y conectar escaner USB
echo  [2/3] Buscando escaner de huella...

:: Buscar el BUSID del U.are.U automaticamente (VID 05ba)
set BUSID=
for /f "tokens=1" %%i in ('powershell -Command "& \"C:\Program Files\usbipd-win\usbipd.exe\" list 2>$null | Select-String '05ba:000a' | ForEach-Object { ($_ -split '\s+')[0] }"') do set BUSID=%%i

if "%BUSID%"=="" (
    echo.
    echo  [!] No se encontro el lector de huellas.
    echo      Conecte el U.are.U 4500 por USB y presione
    echo      cualquier tecla para buscar de nuevo.
    echo.
    pause >nul
    goto :retry_usb
)

echo         Escaner encontrado en puerto %BUSID%
echo         Conectando a Linux...

:: Lanzar usbipd en BACKGROUND para que no bloquee
start "" /B "C:\Program Files\usbipd-win\usbipd.exe" attach --wsl --busid %BUSID% --auto-attach
timeout /t 5 /nobreak >nul
echo         Listo.
echo.

:start_agent
:: Paso 3: Iniciar agente
echo  [3/3] Iniciando escaner...
echo.
echo  ==================================================
echo   ESCANER LISTO - Puede usar la huella dactilar
echo.
echo   Abra el sistema en: localhost/sistema_asistencia
echo   y use la funcion de huella dactilar.
echo.
echo   NO CIERRE esta ventana mientras use el escaner.
echo  ==================================================
echo.

wsl -d Ubuntu -u root -- bash -c "PYTHONUNBUFFERED=1 python3 /mnt/c/xampp/htdocs/sistema_asistencia/fingerprint-agent/agent.py 2>&1"

:: Si el agente termina, reiniciar
echo.
echo  [!] El escaner se detuvo. Reiniciando en 3 seg...
timeout /t 3 /nobreak >nul
goto :retry_usb
