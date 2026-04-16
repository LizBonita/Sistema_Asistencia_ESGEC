# 📋 Guía de Instalación — Sistema de Asistencia con Huella Dactilar

## Escuela Secundaria "Emperador Cuauhtémoc" — Clave 12DES0020I

---

## 📦 Requisitos de Hardware

| Componente | Especificación |
|---|---|
| **Computadora** | Windows 10/11 (64 bits) con mínimo 8 GB RAM |
| **Lector de huellas** | DigitalPersona U.are.U 4500 (USB) |
| **Conexión** | Internet estable (para sincronizar con el servidor) |

---

## 🛠️ Paso 1: Instalar Software Base

### 1.1 Instalar WSL2 (Windows Subsystem for Linux)

Abrir **PowerShell como Administrador** y ejecutar:

```powershell
wsl --install -d Ubuntu
```

Reiniciar la computadora cuando lo pida. Después del reinicio:
- Se abrirá Ubuntu automáticamente
- Crear un usuario y contraseña (pueden ser simples, ej: `admin` / `admin`)

### 1.2 Instalar USBIPD (para pasar el USB al Linux)

Descargar e instalar desde:  
🔗 https://github.com/dorssel/usbipd-win/releases  
Buscar el archivo `.msi` más reciente e instalarlo.

### 1.3 Instalar XAMPP (solo si se necesita servidor local)

> **Nota:** Si el sistema solo se usará en línea (Hostinger), este paso es opcional.

Descargar de: https://www.apachefriends.org/  
Instalar con las opciones por defecto.

---

## 🐧 Paso 2: Configurar Linux (WSL)

Abrir **Ubuntu** desde el menú inicio y ejecutar estos comandos uno por uno:

```bash
# Actualizar sistema
sudo apt update && sudo apt upgrade -y

# Instalar librería del lector de huellas
sudo apt install -y libfprint-2-2 libfprint-2-tod1 gir1.2-fprint-2.0 fprintd python3-gi

# Instalar librería de WebSocket para Python
pip3 install websockets --break-system-packages
```

### Verificar que todo quedó instalado:

```bash
python3 -c "import gi; gi.require_version('FPrint', '2.0'); from gi.repository import FPrint; print('OK: libfprint listo')"
python3 -c "import websockets; print('OK: websockets listo')"
```

Si ambos dicen "OK", todo está correcto ✅

---

## 📂 Paso 3: Descargar el Sistema

### Opción A: Desde GitHub (recomendado)

```powershell
cd C:\xampp\htdocs
git clone https://github.com/LizBonita/Sistema_Asistencia_ESGEC.git sistema_asistencia
```

### Opción B: Copiar archivos manualmente

Copiar toda la carpeta `sistema_asistencia` a:
```
C:\xampp\htdocs\sistema_asistencia\
```

---

## 🔌 Paso 4: Conectar el Lector de Huellas

1. Conectar el **U.are.U 4500** a un puerto USB de la computadora
2. Abrir **PowerShell como Administrador**
3. Verificar que Windows lo detecta:

```powershell
usbipd list
```

Debe aparecer algo como:
```
1-1    05ba:000a  U.are.U® 4500 Fingerprint Reader    Not shared
```

4. Tomar nota del **BUSID** (ej: `1-1`)

> ⚠️ **Importante:** Si el BUSID es diferente a `1-1`, editar el archivo 
> `fingerprint-agent/start_agent.bat` y cambiar `--busid 1-1` por el correcto.

---

## 🗄️ Paso 5: Configurar Base de Datos

### Para uso LOCAL (XAMPP):

1. Abrir XAMPP → Iniciar **Apache** y **MySQL**
2. Abrir http://localhost/phpmyadmin
3. Crear base de datos: `sistema_asistencia_db`
4. Importar el archivo `sql/migracion_huellas.sql`

### Para Hostinger (producción):

1. Ir a **Hostinger → Bases de datos → phpMyAdmin**
2. Ejecutar el contenido de `sql/migracion_huellas.sql`

---

## 🚀 Paso 6: Instalar el Escáner (Una sola vez)

1. Navegar a la carpeta del proyecto:
   ```
   C:\xampp\htdocs\sistema_asistencia\fingerprint-agent\
   ```

2. **Doble click** en `instalar.bat`

3. Aceptar los permisos de administrador cuando pregunte

Esto hará automáticamente:
- ✅ Verificar que todo esté instalado
- ✅ Crear un acceso directo **"Escáner de Huella"** en el escritorio
- ✅ Configurar que el escáner se inicie automáticamente al encender la PC

---

## ✅ Paso 7: Verificar que Funciona

1. **Doble click** en el acceso directo **"Escáner de Huella"** del escritorio
2. Esperar a que diga **"ESCÁNER LISTO"**
3. Abrir el navegador: http://localhost/sistema_asistencia/views/inicio.php
4. Iniciar sesión como Director
5. Ir a **Módulos → Gestión de Huellas**
6. Registrar la huella de un maestro
7. Ir a **Check-in Biométrico** y poner el dedo → debe reconocer al maestro

---

## 📱 Uso Diario

### Para el Director/Administrador:

1. Al encender la computadora, el escáner se inicia solo
   - Si no se inició, hacer doble click en **"Escáner de Huella"** del escritorio
2. Abrir el sistema en el navegador
3. Los maestros pasan su dedo por el lector al llegar (entrada) y al salir (salida)

### Vista Kiosko (Check-in):

Para poner la computadora como kiosko de asistencia:
1. Abrir `http://localhost/sistema_asistencia/views/checkin_huella.php`
2. Poner el navegador en pantalla completa (F11)
3. Los maestros solo necesitan poner su dedo

### Registrar nuevos maestros:

1. Iniciar sesión como Director
2. Ir a **Módulos → Gestión de Huellas**
3. Click en **"Registrar Huella"** del maestro
4. El maestro pone su dedo 5 veces en el lector
5. La huella queda registrada con imagen de verificación

---

## 🔧 Solución de Problemas

### "Escáner desconectado"
- Verificar que el cable USB esté bien conectado
- Reiniciar el programa "Escáner de Huella" desde el escritorio
- Si persiste, reiniciar la computadora

### "No se encontró escáner de huella"
- Desconectar y reconectar el USB del lector
- Verificar en PowerShell: `usbipd list` — debe mostrar el dispositivo
- Si el BUSID cambió, actualizar `start_agent.bat`

### "Error de conexión a base de datos"
- Verificar que XAMPP → Apache y MySQL estén encendidos (para uso local)
- Para Hostinger: verificar conexión a internet

### El agente no inicia
- Ejecutar `start_agent.bat` como **Administrador** (click derecho → Ejecutar como administrador)
- Abrir Ubuntu y verificar: `python3 -c "import websockets; print('OK')"`

---

## 📊 Arquitectura del Sistema

```
┌─────────── HOSTINGER (Internet) ───────────────┐
│  🌐 Sitio web del sistema de asistencia         │
│  📱 API para la app móvil                       │
│  💾 Base de datos MySQL (en la nube)            │
└─────────────────────────────────────────────────┘
                    ↕ Internet
┌─────────── COMPUTADORA DE LA ESCUELA ──────────┐
│  🌐 Navegador → abre el sitio web              │
│  🐍 Agente Python (corre en segundo plano)     │
│  🐧 WSL2 Ubuntu + libfprint                    │
│  🔌 U.are.U 4500 conectado por USB             │
└─────────────────────────────────────────────────┘
```

El escáner de huella **solo funciona desde la computadora de la escuela** donde está conectado el lector USB. El resto del sistema (ver asistencias, reportes, gestión) funciona desde cualquier dispositivo con internet.

---

## 📞 Soporte Técnico

- **Desarrollador:** [Lizbeth]
- **Repositorio:** https://github.com/LizBonita/Sistema_Asistencia_ESGEC
- **Última actualización:** Abril 2026
