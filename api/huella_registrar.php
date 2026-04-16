<?php
// api/huella_registrar.php
// Registra template + imagen de huella dactilar para un maestro

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Huella.php';

$response = ["success" => false, "message" => ""];

try {
    $input = json_decode(file_get_contents("php://input"), true);

    if (!$input) {
        throw new Exception("No se recibieron datos");
    }

    $maestro_id = intval($input['maestro_id'] ?? 0);
    $template = $input['template'] ?? '';
    $imagen_path = $input['imagen_path'] ?? '';
    $imagen_base64 = $input['imagen_base64'] ?? '';
    $dedo = $input['dedo'] ?? 'right-index-finger';

    if ($maestro_id <= 0) {
        throw new Exception("maestro_id invalido");
    }
    if (empty($template)) {
        throw new Exception("template vacio");
    }

    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexion a BD");
    }

    $huella = new Huella($db);
    $huella->maestro_id = $maestro_id;
    $huella->dedo = $dedo;
    $huella->template_data = $template;
    $huella->imagen_path = $imagen_path;
    $huella->imagen_base64 = $imagen_base64;

    if ($huella->create()) {
        $response["success"] = true;
        $response["message"] = "Huella registrada exitosamente";
        $response["maestro_id"] = $maestro_id;
    } else {
        throw new Exception("Error al guardar en base de datos");
    }

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
