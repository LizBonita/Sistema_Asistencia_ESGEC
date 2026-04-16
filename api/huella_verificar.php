<?php
// api/huella_verificar.php
// Obtiene todos los templates para identify, y luego registra asistencia si hay match

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Huella.php';
require_once __DIR__ . '/../models/Asistencia.php';

$response = ["success" => false, "message" => ""];

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexion a BD");
    }

    // GET: Retorna todos los templates para identify
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $huella = new Huella($db);
        $stmt = $huella->getAllTemplates();
        $templates = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $templates[] = [
                "maestro_id" => intval($row['maestro_id']),
                "template" => $row['template_data'],
                "nombre" => $row['nombre_maestro'],
            ];
        }
        $response["success"] = true;
        $response["templates"] = $templates;
        $response["total"] = count($templates);
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // POST: Registrar asistencia despues de match exitoso
    $input = json_decode(file_get_contents("php://input"), true);
    if (!$input) {
        throw new Exception("No se recibieron datos");
    }

    $maestro_id = intval($input['maestro_id'] ?? 0);
    $tipo = $input['tipo'] ?? 'entrada'; // entrada o salida
    $imagen_path = $input['imagen_path'] ?? '';

    if ($maestro_id <= 0) {
        throw new Exception("maestro_id invalido");
    }

    $fecha_hoy = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // Verificar si ya tiene asistencia hoy
    $check = $db->prepare("SELECT id, hora_entrada, hora_salida FROM asistencias 
                           WHERE maestro_id = ? AND fecha = ? LIMIT 1");
    $check->execute([$maestro_id, $fecha_hoy]);
    $asistencia_existente = $check->fetch(PDO::FETCH_ASSOC);

    if ($asistencia_existente) {
        if ($asistencia_existente['hora_salida']) {
            // Ya tiene entrada Y salida
            $response["success"] = true;
            $response["message"] = "Ya registraste entrada y salida hoy";
            $response["tipo"] = "completo";
        } else {
            // Tiene entrada pero no salida — registrar salida
            $update = $db->prepare("UPDATE asistencias SET hora_salida = ?, estado_salida = 'A Tiempo' WHERE id = ?");
            $update->execute([$hora_actual, $asistencia_existente['id']]);
            $response["success"] = true;
            $response["message"] = "Salida registrada: " . $hora_actual;
            $response["tipo"] = "salida";
            $response["hora"] = $hora_actual;
        }
    } else {
        // No tiene asistencia hoy — registrar entrada
        $asistencia = new Asistencia($db);
        $asistencia->maestro_id = $maestro_id;
        $asistencia->fecha = $fecha_hoy;
        $asistencia->hora_entrada = $hora_actual;
        $asistencia->estado_entrada = 'A Tiempo'; // TODO: calcular segun horario
        $asistencia->minutos_retraso = 0;

        if ($asistencia->create()) {
            $response["success"] = true;
            $response["message"] = "Entrada registrada: " . $hora_actual;
            $response["tipo"] = "entrada";
            $response["hora"] = $hora_actual;
        } else {
            throw new Exception("Error al registrar asistencia");
        }
    }

    $response["maestro_id"] = $maestro_id;

    // Obtener nombre del maestro
    $nombre_stmt = $db->prepare("SELECT u.nombre_completo FROM maestros m 
                                  JOIN usuarios u ON m.usuario_id = u.id 
                                  WHERE m.id = ?");
    $nombre_stmt->execute([$maestro_id]);
    $nombre_row = $nombre_stmt->fetch(PDO::FETCH_ASSOC);
    $response["nombre"] = $nombre_row ? $nombre_row['nombre_completo'] : 'Desconocido';

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
