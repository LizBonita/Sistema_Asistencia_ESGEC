<?php
// api/get_horarios.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $stmt = $db->query("
        SELECT h.id, h.id_grupo, h.id_materia, h.dia, h.hora_inicio, h.hora_fin,
               g.nombre AS nombre_grupo,
               mat.nombre AS nombre_materia
        FROM horarios h
        LEFT JOIN grupos g ON h.id_grupo = g.id
        LEFT JOIN materias mat ON h.id_materia = mat.id
        ORDER BY h.dia, h.hora_inicio ASC
    ");
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($horarios, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}