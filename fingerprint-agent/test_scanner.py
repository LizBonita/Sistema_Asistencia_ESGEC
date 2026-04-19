"""
Test rápido: verificar que Python puede ver el escáner U.are.U 4500
a través de la Windows Biometric Framework (winbio.dll)
"""
import ctypes
from ctypes import wintypes
import sys

# ─── Constantes WinBio ───────────────────────────────────────────
WINBIO_TYPE_FINGERPRINT = 0x00000008
S_OK = 0

# ─── Estructuras ─────────────────────────────────────────────────
class WINBIO_VERSION(ctypes.Structure):
    _fields_ = [
        ("MajorVersion", wintypes.DWORD),
        ("MinorVersion", wintypes.DWORD),
    ]

class WINBIO_UNIT_SCHEMA(ctypes.Structure):
    _fields_ = [
        ("UnitId",           wintypes.ULONG),
        ("PoolType",         wintypes.ULONG),
        ("BiometricFactor",  wintypes.ULONG),
        ("SensorSubType",    wintypes.ULONG),
        ("Capabilities",     wintypes.ULONG),
        ("DeviceInstanceId", ctypes.c_wchar * 256),
        ("Description",      ctypes.c_wchar * 256),
        ("Manufacturer",     ctypes.c_wchar * 256),
        ("Model",            ctypes.c_wchar * 256),
        ("SerialNumber",     ctypes.c_wchar * 256),
        ("FirmwareVersion",  WINBIO_VERSION),
    ]

def main():
    print("=" * 60)
    print("  TEST: Conexión con escáner de huellas via WinBio")
    print("=" * 60)
    print()
    
    try:
        winbio = ctypes.windll.winbio
    except OSError as e:
        print(f"❌ ERROR: No se pudo cargar winbio.dll: {e}")
        sys.exit(1)
    
    print("✅ winbio.dll cargado correctamente")
    print()
    
    # ─── Enumerar dispositivos biométricos ────────────────────────
    unit_array = ctypes.POINTER(WINBIO_UNIT_SCHEMA)()
    unit_count = ctypes.c_size_t(0)
    
    hr = winbio.WinBioEnumBiometricUnits(
        WINBIO_TYPE_FINGERPRINT,
        ctypes.byref(unit_array),
        ctypes.byref(unit_count)
    )
    
    if hr != S_OK:
        print(f"❌ ERROR al enumerar dispositivos: HRESULT = {hr:#010x}")
        sys.exit(1)
    
    count = unit_count.value
    print(f"🔍 Dispositivos biométricos encontrados: {count}")
    print()
    
    if count == 0:
        print("❌ No se encontró ningún escáner de huellas.")
        print("   Verifica que el U.are.U 4500 esté conectado y")
        print("   el driver WBF esté instalado correctamente.")
        sys.exit(1)
    
    for i in range(count):
        unit = unit_array[i]
        print(f"  ┌─ Dispositivo #{i + 1}")
        print(f"  │  ID:           {unit.UnitId}")
        print(f"  │  Descripción:  {unit.Description}")
        print(f"  │  Fabricante:   {unit.Manufacturer}")
        print(f"  │  Modelo:       {unit.Model}")
        print(f"  │  Serial:       {unit.SerialNumber}")
        print(f"  │  Firmware:     {unit.FirmwareVersion.MajorVersion}.{unit.FirmwareVersion.MinorVersion}")
        print(f"  │  Capacidades:  {unit.Capabilities:#010x}")
        print(f"  └────────────────────────────────")
        print()
    
    # Liberar memoria
    winbio.WinBioFree(unit_array)
    
    print("✅ ¡Escáner detectado correctamente!")
    print("   El sistema puede comunicarse con el U.are.U 4500.")
    print()
    print("Siguiente paso: probar captura de huella...")

if __name__ == "__main__":
    main()
