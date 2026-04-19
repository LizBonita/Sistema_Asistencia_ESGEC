<?php
// api/get_asistencias_diarias.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $fecha = $_GET['fecha'] ?? date('Y-m-d');

    $stmt = $db->prepare("
        SELECT a.id, a.maestro_id, a.fecha, a.hora_entrada, a.hora_salida,
               a.estado_entrada, a.estado_salida, a.minutos_retraso,
               u.nombre_completo AS nombre_maestro,
               m.tipo_contrato
        FROM asistencias a
        JOIN maestros m ON a.maestro_id = m.id
        JOIN usuarios u ON m.usuario_id = u.id
        WHERE a.fecha = ?
        ORDER BY a.hora_entrada ASC
    ");
    $stmt->execute([$fecha]);
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($asistencias, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}