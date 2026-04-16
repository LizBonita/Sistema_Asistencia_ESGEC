@echo off
:: ===================================================
::  ESCANER DE HUELLA DACTILAR
::  Escuela Secundaria "Emperador Cuauhtemoc"
::  
::  Este programa conecta el lector de huellas con
::  el sistema de asistencia en linea.
::  
::  Solo cierre esta ventana cuando termine de usar
::  el escaner de huellas.
:: ===================================================

title Escaner de Huella - Sistema de Asistencia

color 1F
echo.
echo  ╔══════════════════════════════════════════════╗
echo  ║                                              ║
echo  ║   SISTEMA DE ASISTENCIA ESCOLAR              ║
echo  ║   Escaner de Huella Dactilar                  ║
echo  ║   U.are.U 4500                                ║
echo  ║                                              ║
echo  ╚══════════════════════════════════════════════╝
echo.

:: Verificar permisos de administrador
net session >nul 2>&1
if errorlevel 1 (
    color 4F
    echo  [!] Se necesitan permisos de administrador.
    echo      Cerrando y reabriendo como administrador...
    echo.
    powershell -Command "Start-Process '%~f0' -Verb RunAs"
    exit /b
)

echo  [OK] Permisos de administrador verificados.
echo.

:: Paso 1: Activar WSL
echo  [1/3] Activando sistema Linux (WSL)...
wsl -d Ubuntu -- echo "WSL listo" >nul 2>&1
start /B wsl -d Ubuntu -- sleep 99999
timeout /t 3 /nobreak >nul
echo         Listo.
echo.

:: Paso 2: Conectar escaner USB
echo  [2/3] Conectando escaner de huella...
"C:\Program Files\usbipd-win\usbipd.exe" attach --wsl --busid 1-1 --auto-attach 2>nul
if errorlevel 1 (
    echo.
    echo  [!] No se pudo conectar el escaner.
    echo      Verifique que el lector de huellas este
    echo      conectado por USB a esta computadora.
    echo.
    echo  Presione cualquier tecla para intentar de nuevo
    echo  o cierre esta ventana para cancelar.
    pause >nul
    goto :retry_usb
)
timeout /t 2 /nobreak >nul
echo         Listo.
echo.

:: Paso 3: Iniciar agente
echo  [3/3] Iniciando escaner...
echo.
echo  ══════════════════════════════════════════════
echo   ESCANER LISTO
echo   Abra el sistema de asistencia en el navegador
echo   y use la funcion de huella dactilar.
echo.
echo   NO CIERRE ESTA VENTANA mientras use el escaner.
echo  ══════════════════════════════════════════════
echo.

wsl -d Ubuntu -u root -- bash -c "PYTHONUNBUFFERED=1 python3 /mnt/c/xampp/htdocs/sistema_asistencia/fingerprint-agent/agent.py 2>&1"

:: Si el agente termina, mostrar mensaje
echo.
echo  [!] El escaner se detuvo.
echo      Presione cualquier tecla para reiniciar
echo      o cierre esta ventana.
pause >nul
goto :retry_usb

:retry_usb
echo  [2/3] Reintentando conexion del escaner...
"C:\Program Files\usbipd-win\usbipd.exe" attach --wsl --busid 1-1 --auto-attach 2>nul
timeout /t 2 /nobreak >nul
goto :start_agent

:start_agent
echo  [3/3] Reiniciando escaner...
wsl -d Ubuntu -u root -- bash -c "PYTHONUNBUFFERED=1 python3 /mnt/c/xampp/htdocs/sistema_asistencia/fingerprint-agent/agent.py 2>&1"
goto :retry_usb
