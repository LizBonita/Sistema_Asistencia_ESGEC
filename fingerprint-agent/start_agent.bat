@echo off
:: ===================================================
:: Iniciar Agente de Huella Dactilar
:: Escuela Secundaria "Emperador Cuauhtemoc"
:: ===================================================
echo.
echo  ==========================================
echo   Iniciando Agente de Huella Dactilar...
echo  ==========================================
echo.

:: 1. Mantener WSL activo
echo [1/3] Activando WSL Ubuntu...
start /B wsl -d Ubuntu -- sleep 99999

:: Esperar a que WSL arranque
timeout /t 3 /nobreak >nul

:: 2. Pasar USB del escaner a Linux
echo [2/3] Pasando escaner USB a Linux...
"C:\Program Files\usbipd-win\usbipd.exe" attach --wsl --busid 1-1 --auto-attach 2>nul
if errorlevel 1 (
    echo     Nota: El escaner puede necesitar permisos de admin.
    echo     Ejecuta este script como Administrador si falla.
)

timeout /t 2 /nobreak >nul

:: 3. Iniciar el agente Python en WSL
echo [3/3] Iniciando agente WebSocket...
echo.
wsl -d Ubuntu -u root -- python3 /mnt/c/Users/elvis/Downloads/sistema_asistencia/fingerprint-agent/agent.py
