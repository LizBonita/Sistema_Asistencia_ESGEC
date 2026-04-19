"""
TEST DEFINITIVO: WinBio con WINBIO_FLAG_RAW
Segun la documentacion de Microsoft, WinBioCaptureSample
REQUIERE que la sesion se abra con FLAG_RAW o FLAG_MAINTENANCE.
"""
import ctypes
from ctypes import wintypes
import sys
import os
import struct

# ─── Constantes ──────────────────────────────────────────────────
WINBIO_TYPE_FINGERPRINT       = 0x00000008
WINBIO_POOL_SYSTEM            = 0x01
WINBIO_FLAG_DEFAULT           = 0x00000000
WINBIO_FLAG_BASIC             = 0x00010000
WINBIO_FLAG_ADVANCED          = 0x00020000
WINBIO_FLAG_RAW               = 0x00000002
WINBIO_FLAG_MAINTENANCE       = 0x00000020
WINBIO_NO_PURPOSE_AVAILABLE   = 0x00
WINBIO_PURPOSE_VERIFY         = 0x01
WINBIO_PURPOSE_IDENTIFY       = 0x02
WINBIO_PURPOSE_ENROLL         = 0x08
WINBIO_DATA_FLAG_RAW          = 0x01
WINBIO_DATA_FLAG_INTERMEDIATE = 0x02
WINBIO_DATA_FLAG_PROCESSED    = 0x04
S_OK = 0

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

def hresult_name(hr):
    hru = hr & 0xFFFFFFFF
    names = {
        0x80070005: "E_ACCESSDENIED",
        0x80098005: "CANCELLED",
        0x80098003: "BAD_CAPTURE",
        0x80070057: "E_INVALIDARG",
        0x80004001: "E_NOTIMPL",
        0x80098001: "NOT_AVAILABLE",
        0x800704EC: "E_DISABLED",
        0x80098002: "WRONG_SESSION",
        0x80098011: "INVALID_SENSOR_MODE",
    }
    return names.get(hru, f"UNKNOWN({hru:#010x})")

def try_session_and_capture(winbio, flag_name, flag_value, purpose, data_flag):
    """Intentar abrir sesion con un flag especifico y capturar."""
    session_handle = ctypes.c_uint32(0)
    
    hr = winbio.WinBioOpenSession(
        WINBIO_TYPE_FINGERPRINT,
        WINBIO_POOL_SYSTEM,
        flag_value,
        None,
        ctypes.c_size_t(0),
        None,
        ctypes.byref(session_handle)
    )
    
    if hr != S_OK:
        return f"OPEN FAILED: {hresult_name(hr)}"
    
    # Cancelar automaticamente despues de 1 segundo para no esperar
    import threading
    def cancel_after(handle, delay):
        import time
        time.sleep(delay)
        winbio.WinBioCancel(handle)
    
    t = threading.Thread(target=cancel_after, args=(session_handle, 1.0))
    t.start()
    
    unit_id = wintypes.ULONG(0)
    sample_ptr = ctypes.POINTER(WINBIO_BIR)()
    sample_size = ctypes.c_size_t(0)
    reject_detail = wintypes.ULONG(0)
    
    hr = winbio.WinBioCaptureSample(
        session_handle, purpose, data_flag,
        ctypes.byref(unit_id), ctypes.byref(sample_ptr),
        ctypes.byref(sample_size), ctypes.byref(reject_detail)
    )
    
    t.join()
    
    hru = hr & 0xFFFFFFFF
    if hr == S_OK:
        result = f">>> EXITO! {sample_size.value} bytes capturados!"
        winbio.WinBioFree(sample_ptr)
    elif hru == 0x80098005:
        result = "PARAMETROS ACEPTADOS (cancelado - significaria que FUNCIONA con dedo)"
    elif hru == 0x80070005:
        result = "ACCESO DENEGADO"
    else:
        result = f"ERROR: {hresult_name(hr)}"
    
    winbio.WinBioCloseSession(session_handle)
    return result

def main():
    print("=" * 65)
    print("  TEST DEFINITIVO: WinBio con FLAG_RAW y FLAG_MAINTENANCE")
    print("=" * 65)
    print()
    
    winbio = ctypes.windll.winbio
    
    # Verificar que hay un escaner
    from test_scanner import WINBIO_VERSION, WINBIO_UNIT_SCHEMA
    unit_array = ctypes.POINTER(WINBIO_UNIT_SCHEMA)()
    unit_count = ctypes.c_size_t(0)
    hr = winbio.WinBioEnumBiometricUnits(
        WINBIO_TYPE_FINGERPRINT, ctypes.byref(unit_array), ctypes.byref(unit_count)
    )
    if hr != S_OK or unit_count.value == 0:
        print("ERROR: No se encontro escaner. Instala el driver WBF primero.")
        sys.exit(1)
    print(f"Escaner encontrado: {unit_array[0].Description}")
    winbio.WinBioFree(unit_array)
    print()
    
    # ─── Probar TODAS las combinaciones de flags de sesion ─────────
    session_flags = [
        ("FLAG_DEFAULT",     WINBIO_FLAG_DEFAULT),
        ("FLAG_RAW",         WINBIO_FLAG_RAW),
        ("FLAG_MAINTENANCE", WINBIO_FLAG_MAINTENANCE),
        ("FLAG_BASIC",       WINBIO_FLAG_BASIC),
        ("FLAG_ADVANCED",    WINBIO_FLAG_ADVANCED),
    ]
    
    purposes = [
        ("NO_PURPOSE", WINBIO_NO_PURPOSE_AVAILABLE),
        ("VERIFY",     WINBIO_PURPOSE_VERIFY),
        ("IDENTIFY",   WINBIO_PURPOSE_IDENTIFY),
    ]
    
    data_flags = [
        ("RAW",          WINBIO_DATA_FLAG_RAW),
        ("INTERMEDIATE", WINBIO_DATA_FLAG_INTERMEDIATE),
        ("PROCESSED",    WINBIO_DATA_FLAG_PROCESSED),
    ]
    
    print("NO pongas el dedo - solo estamos probando parametros.")
    print("(cada prueba se cancela automaticamente)")
    print()
    
    for sf_name, sf_val in session_flags:
        print(f"=== Session: {sf_name} ({sf_val:#010x}) ===")
        for p_name, p_val in purposes:
            for df_name, df_val in data_flags:
                label = f"  Purpose={p_name}, DataFlag={df_name}"
                result = try_session_and_capture(winbio, sf_name, sf_val, p_val, df_val)
                
                # Resaltar exitos y parametros aceptados
                if "EXITO" in result or "ACEPTADOS" in result:
                    print(f"  *** {label}: {result} ***")
                elif "DENEGADO" in result:
                    print(f"  {label}: {result}")
                else:
                    print(f"  {label}: {result}")
        print()
    
    print("=" * 65)
    print("  TEST COMPLETADO")
    print("=" * 65)

if __name__ == "__main__":
    main()
