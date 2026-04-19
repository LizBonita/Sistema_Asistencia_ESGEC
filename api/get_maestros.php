<?php
// api/get_maestros.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $stmt = $db->query("
        SELECT m.id, m.usuario_id, m.tipo_contrato, m.fecha_registro,
               u.nombre_completo, u.usuario, u.rol_id
        FROM maestros m
        JOIN usuarios u ON m.usuario_id = u.id
        ORDER BY u.nombre_completo ASC
    ");
    $maestros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($maestros, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}