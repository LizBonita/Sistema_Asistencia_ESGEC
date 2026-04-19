"""
Test de captura v2: probar multiples combinaciones de parametros WinBio
para encontrar cual funciona con el U.are.U 4500 WBF
"""
import ctypes
from ctypes import wintypes
import sys

# ─── Constantes ──────────────────────────────────────────────────
WINBIO_TYPE_FINGERPRINT    = 0x00000008
WINBIO_POOL_SYSTEM         = 0x01
WINBIO_POOL_PRIVATE        = 0x02
WINBIO_FLAG_DEFAULT        = 0x00000000
WINBIO_FLAG_RAW            = 0x00000002
S_OK = 0

# Purpose values
PURPOSE_VALUES = {
    "NO_PURPOSE":     0x00,
    "VERIFY":         0x01,
    "IDENTIFY":       0x02,
    "ENROLL":         0x08,
    "AUDIT":          0x80,
}

# Data flag values
FLAG_VALUES = {
    "RAW":            0x01,
    "INTERMEDIATE":   0x02,
    "PROCESSED":      0x04,
}

class WINBIO_BIR_DATA(ctypes.Structure):
    _fields_ = [
        ("Size",   wintypes.ULONG),
        ("Offset", wintypes.ULONG),
    ]

class WINBIO_BIR(ctypes.Structure):
    _fields_ = [
        ("HeaderBlock",       WINBIO_BIR_DATA),
        ("StandardDataBlock", WINBIO_BIR_DATA),
        ("VendorDataBlock",   WINBIO_BIR_DATA),
        ("SignatureBlock",    WINBIO_BIR_DATA),
    ]

def try_capture(winbio, purpose_name, purpose_val, flag_name, flag_val):
    """Intentar una captura con parametros especificos"""
    session_handle = ctypes.c_uint32(0)
    
    hr = winbio.WinBioOpenSession(
        WINBIO_TYPE_FINGERPRINT,
        WINBIO_POOL_SYSTEM,
        WINBIO_FLAG_DEFAULT,
        None,
        ctypes.c_size_t(0),
        None,
        ctypes.byref(session_handle)
    )
    
    if hr != S_OK:
        return f"  ERROR abrir sesion: {hr:#010x}"
    
    unit_id = wintypes.ULONG(0)
    sample_ptr = ctypes.POINTER(WINBIO_BIR)()
    sample_size = ctypes.c_size_t(0)
    reject_detail = wintypes.ULONG(0)
    
    hr = winbio.WinBioCaptureSample(
        session_handle,
        purpose_val,
        flag_val,
        ctypes.byref(unit_id),
        ctypes.byref(sample_ptr),
        ctypes.byref(sample_size),
        ctypes.byref(reject_detail)
    )
    
    if hr == S_OK:
        result = f"  >>> EXITO! Tamano: {sample_size.value} bytes"
        winbio.WinBioFree(sample_ptr)
    else:
        hru = hr & 0xFFFFFFFF
        error_names = {
            0x80070005: "E_ACCESSDENIED",
            0x80098005: "CANCELLED/TIMEOUT",
            0x80098003: "BAD_CAPTURE",
            0x80070057: "E_INVALIDARG",
            0x80004001: "E_NOTIMPL",
            0x80098001: "NOT_AVAILABLE",
        }
        name = error_names.get(hru, "DESCONOCIDO")
        result = f"  FALLO: {hru:#010x} ({name})"
    
    winbio.WinBioCloseSession(session_handle)
    return result

def try_locate_sensor(winbio):
    """Probar WinBioLocateSensor - no requiere privilegios especiales"""
    session_handle = ctypes.c_uint32(0)
    
    hr = winbio.WinBioOpenSession(
        WINBIO_TYPE_FINGERPRINT,
        WINBIO_POOL_SYSTEM,
        WINBIO_FLAG_DEFAULT,
        None,
        ctypes.c_size_t(0),
        None,
        ctypes.byref(session_handle)
    )
    
    if hr != S_OK:
        return f"ERROR abrir sesion: {hr:#010x}"
    
    unit_id = wintypes.ULONG(0)
    
    print()
    print("  PON TU DEDO EN EL ESCANER...")
    print("  (WinBioLocateSensor - sin privilegios especiales)")
    print()
    
    hr = winbio.WinBioLocateSensor(
        session_handle,
        ctypes.byref(unit_id)
    )
    
    winbio.WinBioCloseSession(session_handle)
    
    if hr == S_OK:
        return f"EXITO! Sensor detecto dedo en unidad {unit_id.value}"
    else:
        hru = hr & 0xFFFFFFFF
        return f"FALLO: {hru:#010x}"

def main():
    print("=" * 60)
    print("  TEST v2: Probando combinaciones de WinBio")
    print("=" * 60)
    print()
    
    winbio = ctypes.windll.winbio
    
    # ─── Test 1: Probar WinBioLocateSensor ────────────────────────
    print("[TEST 1] WinBioLocateSensor (detectar dedo sin capturar)")
    result = try_locate_sensor(winbio)
    print(f"  Resultado: {result}")
    print()
    
    # ─── Test 2: Probar todas las combinaciones de captura ────────
    print("[TEST 2] Probando combinaciones de CaptureSample...")
    print("  (NO pongas el dedo - solo probamos si acepta los parametros)")
    print()
    
    for p_name, p_val in PURPOSE_VALUES.items():
        for f_name, f_val in FLAG_VALUES.items():
            label = f"Purpose={p_name}, Flags={f_name}"
            print(f"  {label}:")
            
            # Abrimos sesion, mandamos captura, y cerramos rapidamente
            session_handle = ctypes.c_uint32(0)
            hr = winbio.WinBioOpenSession(
                WINBIO_TYPE_FINGERPRINT,
                WINBIO_POOL_SYSTEM,
                WINBIO_FLAG_DEFAULT,
                None,
                ctypes.c_size_t(0),
                None,
                ctypes.byref(session_handle)
            )
            if hr != S_OK:
                print(f"    -> No se pudo abrir sesion: {hr:#010x}")
                continue
            
            # Cancelar inmediatamente despues para no esperar al dedo
            # Usamos un thread para cancelar
            import threading
            def cancel_after(handle, delay):
                import time
                time.sleep(delay)
                winbio.WinBioCancel(handle)
            
            t = threading.Thread(target=cancel_after, args=(session_handle, 0.5))
            t.start()
            
            unit_id = wintypes.ULONG(0)
            sample_ptr = ctypes.POINTER(WINBIO_BIR)()
            sample_size = ctypes.c_size_t(0)
            reject_detail = wintypes.ULONG(0)
            
            hr = winbio.WinBioCaptureSample(
                session_handle,
                p_val,
                f_val,
                ctypes.byref(unit_id),
                ctypes.byref(sample_ptr),
                ctypes.byref(sample_size),
                ctypes.byref(reject_detail)
            )
            
            t.join()
            
            hru = hr & 0xFFFFFFFF
            if hr == S_OK:
                print(f"    -> EXITO (captura OK, {sample_size.value} bytes)")
                winbio.WinBioFree(sample_ptr)
            elif hru == 0x80098005:
                # Cancelado = significa que los parametros fueron ACEPTADOS!
                print(f"    -> PARAMETROS VALIDOS (cancelado por timeout)")
            elif hru == 0x80070005:
                print(f"    -> ACCESO DENEGADO")
            elif hru == 0x80070057:
                print(f"    -> PARAMETROS INVALIDOS")
            else:
                print(f"    -> Error: {hru:#010x}")
            
            winbio.WinBioCloseSession(session_handle)
    
    print()
    print("=" * 60)
    print("  TEST COMPLETADO")
    print("=" * 60)

if __name__ == "__main__":
    main()
