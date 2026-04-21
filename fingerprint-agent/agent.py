#!/usr/bin/env python3
"""
Agente de Huella Dactilar — WebSocket Bridge
Sistema de Asistencia - Escuela Secundaria "Emperador Cuauhtemoc"

Corre dentro de WSL2 y sirve como puente entre el navegador web
y el escaner U.are.U 4500 via libfprint.

Uso: python3 agent.py
WebSocket: ws://localhost:8765
"""

import asyncio
import json
import base64
import struct
import os
import sys
import time
import threading
from datetime import datetime

import gi
gi.require_version("FPrint", "2.0")
from gi.repository import FPrint, GLib

try:
    import websockets
except ImportError:
    print("Instalando websockets...")
    os.system("pip3 install websockets --break-system-packages 2>/dev/null || pip3 install websockets")
    import websockets

# ─── Configuracion ────────────────────────────────────────────
WS_HOST = "0.0.0.0"
WS_PORT = 8765
UPLOADS_DIR = "/mnt/c/xampp/htdocs/sistema_asistencia/uploads/huellas"

# ─── Variables globales ───────────────────────────────────────
device = None
device_lock = threading.Lock()
ctx = None
ctx_initialized = False


def init_context():
    """Inicializar el contexto UNA SOLA VEZ."""
    global ctx, ctx_initialized
    if not ctx_initialized:
        ctx = FPrint.Context()
        ctx_initialized = True


def open_device():
    """Abrir el dispositivo (reutilizando el contexto existente)."""
    global device, ctx
    init_context()
    devs = ctx.get_devices()
    if len(devs) == 0:
        raise RuntimeError("No se encontro escaner de huella")
    device = devs[0]
    try:
        device.open_sync()
    except GLib.Error as e:
        if "already open" in str(e).lower() or "busy" in str(e).lower():
            pass  # Ya esta abierto, seguimos
        else:
            raise
    print("[OK] Escaner abierto: " + device.get_name())


def close_device():
    """Cerrar el dispositivo (no destruye el contexto)."""
    global device
    if device:
        try:
            device.close_sync()
        except:
            pass
        device = None


def image_to_bmp_bytes(img):
    """Convertir FpImage a bytes BMP."""
    w = img.get_width()
    h = img.get_height()
    data = bytes(img.get_data())

    row_size = (w + 3) & ~3
    pal_size = 256 * 4
    pix_size = row_size * h
    file_size = 14 + 40 + pal_size + pix_size

    bmp = bytearray()
    # File header
    bmp += b"BM"
    bmp += struct.pack("<I", file_size)
    bmp += struct.pack("<HH", 0, 0)
    bmp += struct.pack("<I", 14 + 40 + pal_size)
    # DIB header
    bmp += struct.pack("<I", 40)
    bmp += struct.pack("<i", w)
    bmp += struct.pack("<i", -h)  # top-down
    bmp += struct.pack("<HH", 1, 8)  # 1 plane, 8bpp
    bmp += struct.pack("<I", 0)  # no compression
    bmp += struct.pack("<I", pix_size)
    bmp += struct.pack("<ii", 19685, 19685)  # 500 DPI
    bmp += struct.pack("<II", 256, 256)
    # Palette (grayscale)
    for i in range(256):
        bmp += struct.pack("BBBB", i, i, i, 0)
    # Pixels
    for y in range(h):
        row = data[y * w : (y + 1) * w]
        bmp += row
        pad = row_size - w
        if pad > 0:
            bmp += b"\x00" * pad

    return bytes(bmp)


def save_image(bmp_bytes, maestro_id, suffix=""):
    """Guardar imagen BMP en disco y retornar ruta relativa."""
    os.makedirs(UPLOADS_DIR, exist_ok=True)
    ts = datetime.now().strftime("%Y%m%d_%H%M%S")
    filename = "huella_m{}_{}{}.bmp".format(maestro_id, ts, suffix)
    filepath = os.path.join(UPLOADS_DIR, filename)
    with open(filepath, "wb") as f:
        f.write(bmp_bytes)
    return "uploads/huellas/" + filename


def do_enroll(maestro_id, dedo="right-index-finger", ws_send=None):
    """
    Realizar enrollment (registro de huella).
    ws_send: callable para enviar mensajes intermedios al frontend via queue.
    Retorna dict con template_b64, imagen_b64, imagen_path.
    """
    global device

    with device_lock:
        # Reiniciar dispositivo
        close_device()
        open_device()

        template = FPrint.Print.new(device)

        # Detectar número de etapas que requiere el escáner
        total_stages = device.get_nr_enroll_stages()
        if total_stages < 1:
            total_stages = 5  # fallback

        print("[ENROLL] Iniciando enrollment: {} etapas requeridas".format(total_stages))

        # Notificar inicio — primera captura
        if ws_send:
            try:
                ws_send(json.dumps({
                    "status": "scanning",
                    "action": "enroll",
                    "stage": 1,
                    "total_stages": total_stages,
                    "message": "Captura 1 de {} — Coloca tu dedo".format(total_stages),
                }))
            except:
                pass

        # ── Progress callback: libfprint lo llama después de CADA captura ──
        def enroll_progress_cb(device, enroll_stage, fp_print, user_data=None):
            """Callback llamado por libfprint después de cada captura individual."""
            stage_num = enroll_stage + 1  # libfprint usa 0-indexed
            print("[ENROLL] Progreso: etapa {} de {} completada".format(stage_num, total_stages))

            if ws_send:
                # Notificar que esta etapa se completó
                try:
                    ws_send(json.dumps({
                        "status": "stage_complete",
                        "action": "enroll",
                        "stage": stage_num,
                        "total_stages": total_stages,
                        "message": "Captura {} completada".format(stage_num),
                    }))
                except:
                    pass

                # Si hay más etapas, notificar que espere la siguiente
                if stage_num < total_stages:
                    try:
                        ws_send(json.dumps({
                            "status": "scanning",
                            "action": "enroll",
                            "stage": stage_num + 1,
                            "total_stages": total_stages,
                            "message": "Captura {} de {} — Coloca tu dedo".format(
                                stage_num + 1, total_stages),
                        }))
                    except:
                        pass

        # ── enroll_sync CON progress callback ──
        # enroll_sync(template, cancellable, progress_cb, progress_data)
        try:
            enrolled_print = device.enroll_sync(
                template, None, enroll_progress_cb, None)
        except TypeError:
            # Fallback: si la versión de libfprint no soporta progress_cb
            print("[ENROLL] WARN: progress_cb no soportado, usando modo simple")
            enrolled_print = device.enroll_sync(template, None)
        except GLib.Error as e:
            print("[ENROLL] Error: {}".format(e))
            close_device()
            return {"status": "error", "message": str(e)}

        print("[ENROLL] Enrollment completado exitosamente")

        # Serializar template
        template_bytes = template.serialize()
        if hasattr(template_bytes, "get_data"):
            template_raw = bytes(template_bytes.get_data())
        else:
            template_raw = bytes(template_bytes)
        template_b64 = base64.b64encode(template_raw).decode("ascii")

        # ── Cerrar y reabrir limpio para capturar imagen final ──
        close_device()
        time.sleep(0.3)
        open_device()

        imagen_b64 = ""
        imagen_path = ""
        print("[INFO] Pon tu dedo de nuevo para capturar imagen final...")
        try:
            img = device.capture_sync(True, None)
            if img:
                bmp = image_to_bmp_bytes(img)
                imagen_b64 = base64.b64encode(bmp).decode("ascii")
                imagen_path = save_image(bmp, maestro_id)
                print("[OK] Imagen capturada (" + str(len(imagen_b64)) + " chars base64)")
        except Exception as img_err:
            print("[AVISO] No se capturo imagen: " + str(img_err))

        close_device()

    return {
        "status": "ok",
        "template": template_b64,
        "imagen_base64": imagen_b64,
        "imagen_path": imagen_path,
        "etapas": stage,
        "total_stages": total_stages,
    }


def do_verify(template_b64, maestro_id=0):
    """
    Verificar una huella contra un template almacenado.
    Retorna dict con match, imagen_b64.
    """
    global device

    with device_lock:
        close_device()
        open_device()

        # Deserializar template
        template_raw = base64.b64decode(template_b64)
        enrolled_print = FPrint.Print.deserialize(template_raw)

        # Verificar
        match = False
        try:
            result = device.verify_sync(enrolled_print)
            if isinstance(result, tuple):
                match = result[0]
            else:
                match = bool(result)
        except GLib.Error as e:
            print("Error verify: " + str(e))
            match = False

        # Capturar imagen
        imagen_b64 = ""
        imagen_path = ""
        try:
            img = device.capture_sync(True, None)
            if img:
                bmp = image_to_bmp_bytes(img)
                imagen_b64 = base64.b64encode(bmp).decode("ascii")
                if match:
                    imagen_path = save_image(bmp, maestro_id, "_verify")
        except:
            pass

        close_device()

    return {
        "status": "ok",
        "match": match,
        "maestro_id": maestro_id,
        "imagen_base64": imagen_b64,
        "imagen_path": imagen_path,
    }


def do_identify(templates_list):
    """
    Identificar una huella entre multiples templates.
    templates_list: [{"maestro_id": N, "template": "base64..."}, ...]
    Retorna dict con match, maestro_id.
    """
    global device

    with device_lock:
        close_device()
        open_device()

        # Deserializar todos los templates
        gallery = []
        maestro_map = {}
        for i, item in enumerate(templates_list):
            try:
                raw = base64.b64decode(item["template"])
                fp = FPrint.Print.deserialize(raw)
                gallery.append(fp)
                maestro_map[i] = item["maestro_id"]
            except Exception as e:
                print("Error deserializando template {}: {}".format(
                    item.get("maestro_id", "?"), e))

        if not gallery:
            close_device()
            return {"status": "error", "message": "No hay templates validos"}

        # Identificar
        match = False
        matched_maestro = None
        imagen_b64 = ""
        imagen_path = ""

        try:
            result = device.identify_sync(gallery)
            if isinstance(result, tuple) and len(result) >= 2:
                matched_print = result[0]
                found = result[1]
                if matched_print and found:
                    # Encontrar cual template coincidio
                    for i, fp in enumerate(gallery):
                        if fp == matched_print:
                            matched_maestro = maestro_map.get(i)
                            match = True
                            break
                    if not match and matched_print:
                        # Intentar por indice
                        match = True
                        matched_maestro = maestro_map.get(0)
        except GLib.Error as e:
            err_str = str(e)
            if "match" in err_str.lower():
                match = False
            else:
                print("Error identify: " + str(e))

        # Capturar imagen
        try:
            img = device.capture_sync(True, None)
            if img:
                bmp = image_to_bmp_bytes(img)
                imagen_b64 = base64.b64encode(bmp).decode("ascii")
                if match and matched_maestro:
                    imagen_path = save_image(bmp, matched_maestro, "_checkin")
        except:
            pass

        close_device()

    return {
        "status": "ok",
        "match": match,
        "maestro_id": matched_maestro,
        "imagen_base64": imagen_b64,
        "imagen_path": imagen_path,
    }


def do_capture_image(maestro_id=0):
    """Solo capturar imagen sin enrollment ni verificacion."""
    global device

    with device_lock:
        close_device()
        open_device()

        imagen_b64 = ""
        imagen_path = ""
        try:
            img = device.capture_sync(True, None)
            if img:
                bmp = image_to_bmp_bytes(img)
                imagen_b64 = base64.b64encode(bmp).decode("ascii")
                imagen_path = save_image(bmp, maestro_id, "_capture")
        except Exception as e:
            close_device()
            return {"status": "error", "message": str(e)}

        close_device()

    return {
        "status": "ok",
        "imagen_base64": imagen_b64,
        "imagen_path": imagen_path,
    }


# ─── WebSocket Handler ───────────────────────────────────────

async def handler(websocket):
    """Manejar conexiones WebSocket."""
    addr = websocket.remote_address
    print("[{}] Cliente conectado: {}".format(
        datetime.now().strftime("%H:%M:%S"), addr))

    try:
        async for message in websocket:
            try:
                data = json.loads(message)
                action = data.get("action", "")
                print("[{}] Accion: {}".format(
                    datetime.now().strftime("%H:%M:%S"), action))

                if action == "status":
                    await websocket.send(json.dumps({
                        "status": "ok",
                        "message": "Agente de huella activo",
                        "scanner": "DigitalPersona U.are.U 4500",
                    }))

                elif action == "enroll":
                    maestro_id = data.get("maestro_id", 0)
                    dedo = data.get("dedo", "right-index-finger")

                    # Cola thread-safe para mensajes intermedios
                    import queue
                    msg_queue = queue.Queue()

                    def ws_send_sync(msg):
                        """Encolar mensaje para envio async."""
                        msg_queue.put(msg)

                    # Avisar que empieza el enrollment
                    await websocket.send(json.dumps({
                        "status": "scanning",
                        "message": "Preparando escáner...",
                        "action": "enroll",
                    }))

                    # Lanzar enrollment en hilo y hacer polling de mensajes
                    loop = asyncio.get_event_loop()
                    enroll_future = loop.run_in_executor(
                        None, do_enroll, maestro_id, dedo, ws_send_sync)

                    while not enroll_future.done():
                        # Enviar mensajes encolados
                        while not msg_queue.empty():
                            try:
                                queued_msg = msg_queue.get_nowait()
                                await websocket.send(queued_msg)
                            except:
                                pass
                        await asyncio.sleep(0.1)

                    # Enviar mensajes restantes
                    while not msg_queue.empty():
                        try:
                            queued_msg = msg_queue.get_nowait()
                            await websocket.send(queued_msg)
                        except:
                            pass

                    result = enroll_future.result()
                    await websocket.send(json.dumps(result))

                elif action == "verify":
                    template_b64 = data.get("template", "")
                    maestro_id = data.get("maestro_id", 0)

                    await websocket.send(json.dumps({
                        "status": "scanning",
                        "message": "Pon tu dedo en el escaner...",
                        "action": "verify",
                    }))

                    result = await asyncio.to_thread(
                        do_verify, template_b64, maestro_id)
                    await websocket.send(json.dumps(result))

                elif action == "identify":
                    templates = data.get("templates", [])

                    await websocket.send(json.dumps({
                        "status": "scanning",
                        "message": "Pon tu dedo en el escaner...",
                        "action": "identify",
                    }))

                    result = await asyncio.to_thread(
                        do_identify, templates)
                    await websocket.send(json.dumps(result))

                elif action == "capture":
                    maestro_id = data.get("maestro_id", 0)

                    await websocket.send(json.dumps({
                        "status": "scanning",
                        "message": "Pon tu dedo en el escaner...",
                        "action": "capture",
                    }))

                    result = await asyncio.to_thread(
                        do_capture_image, maestro_id)
                    await websocket.send(json.dumps(result))

                else:
                    await websocket.send(json.dumps({
                        "status": "error",
                        "message": "Accion no reconocida: " + action,
                    }))

            except json.JSONDecodeError:
                await websocket.send(json.dumps({
                    "status": "error",
                    "message": "JSON invalido",
                }))
            except Exception as e:
                print("[ERROR] " + str(e))
                await websocket.send(json.dumps({
                    "status": "error",
                    "message": str(e),
                }))

    except websockets.exceptions.ConnectionClosed:
        print("[{}] Cliente desconectado: {}".format(
            datetime.now().strftime("%H:%M:%S"), addr))


async def main():
    """Iniciar servidor WebSocket."""
    print("=" * 55)
    print("  Agente de Huella Dactilar - U.are.U 4500")
    print("  Escuela Secundaria 'Emperador Cuauhtemoc'")
    print("=" * 55)
    print()
    print("  WebSocket: ws://localhost:{}".format(WS_PORT))
    print("  Imagenes:  {}".format(UPLOADS_DIR))
    print()

    # Verificar que el escaner esta disponible
    try:
        open_device()
        print("[OK] Escaner listo")
        close_device()
    except Exception as e:
        print("[AVISO] Escaner no disponible: " + str(e))
        print("        El agente iniciara sin escaner.")
        print("        Conecta el escaner y reintenta.")

    print()
    print("Esperando conexiones...")
    print()

    async with websockets.serve(handler, WS_HOST, WS_PORT):
        await asyncio.Future()  # Corre indefinidamente


if __name__ == "__main__":
    asyncio.run(main())
