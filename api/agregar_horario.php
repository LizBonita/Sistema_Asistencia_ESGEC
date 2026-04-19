<?php
// api/agregar_horario.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $data = json_decode(file_get_contents("php://input"));
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No se recibieron datos']);
        exit;
    }

    $maestro_id = $data->maestro_id ?? 0;
    $materia_id = $data->materia_id ?? 0;
    $grupo_id = $data->grupo_id ?? 0;
    $dia_semana = $data->dia_semana ?? '';
    $hora_inicio = $data->hora_inicio ?? '';
    $hora_fin = $data->hora_fin ?? '';
    $tolerancia_entrada = $data->tolerancia_entrada ?? 10;
    $limite_retardo = $data->limite_retardo ?? 15;

    if (!$maestro_id || !$materia_id || !$grupo_id || !$dia_semana || !$hora_inicio || !$hora_fin) {
        echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO horarios (maestro_id, materia_id, grupo_id, dia_semana, hora_inicio, hora_fin, tolerancia_entrada, limite_retardo) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$maestro_id, $materia_id, $grupo_id, $dia_semana, $hora_inicio, $hora_fin, $tolerancia_entrada, $limite_retardo]);

    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Horario agregado correctamente', 'id' => $db->lastInsertId()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al insertar horario']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
