<?php
// api/huella_registrar.php
// Registra template + imagen de huella dactilar
// Imagen: recibe base64 → decodifica → guarda BMP en uploads/huellas/

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Huella.php';

$response = ["success" => false, "message" => "", "debug" => []];

try {
    $raw = file_get_contents("php://input");
    $response["debug"]["raw_length"] = strlen($raw);
    
    $input = json_decode($raw, true);
    if (!$input) {
        throw new Exception("No se recibieron datos (json_decode falló)");
    }

    $maestro_id = intval($input['maestro_id'] ?? 0);
    $template = $input['template'] ?? '';
    $imagen_base64_raw = $input['imagen_base64'] ?? '';
    $dedo = $input['dedo'] ?? 'right-index-finger';

    $response["debug"]["maestro_id"] = $maestro_id;
    $response["debug"]["template_len"] = strlen($template);
    $response["debug"]["base64_len"] = strlen($imagen_base64_raw);

    if ($maestro_id <= 0) throw new Exception("maestro_id invalido");
    if (empty($template)) throw new Exception("template vacio");

    // ── Guardar imagen BMP desde base64 ──
    $imagen_path = '';
    if (!empty($imagen_base64_raw)) {
        $uploadsDir = __DIR__ . '/../uploads/huellas';
        
        if (!is_dir($uploadsDir)) {
            @mkdir($uploadsDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $filename = "huella_m{$maestro_id}_{$timestamp}.bmp";
        $filepath = $uploadsDir . '/' . $filename;

        $imageData = base64_decode($imagen_base64_raw);
        $response["debug"]["decoded_size"] = strlen($imageData);

        $written = @file_put_contents($filepath, $imageData);
        
        if ($written !== false && file_exists($filepath)) {
            $imagen_path = 'uploads/huellas/' . $filename;
            $response["debug"]["file_saved"] = true;
            $response["debug"]["file_path"] = $imagen_path;
        } else {
            $response["debug"]["file_saved"] = false;
            $response["debug"]["dir_exists"] = is_dir($uploadsDir);
            $response["debug"]["dir_writable"] = is_writable($uploadsDir);
            $response["debug"]["error"] = error_get_last();
        }
    } else {
        $response["debug"]["no_image"] = "imagen_base64 está vacío";
    }

    $db = (new Database())->getConnection();
    if (!$db) throw new Exception("Error de conexion a BD");

    $huella = new Huella($db);
    $huella->maestro_id = $maestro_id;
    $huella->dedo = $dedo;
    $huella->template_data = $template;
    $huella->imagen_path = $imagen_path;
    $huella->imagen_base64 = ''; // No guardamos en BD, usamos archivo

    if ($huella->create()) {
        $response["success"] = true;
        $response["message"] = "Huella registrada exitosamente";
        $response["maestro_id"] = $maestro_id;
        $response["imagen_path"] = $imagen_path;
    } else {
        throw new Exception("Error al guardar en base de datos");
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
