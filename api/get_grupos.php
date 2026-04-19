<?php
// api/get_grupos.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $stmt = $db->query("SELECT id, nombre FROM grupos ORDER BY nombre ASC");
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($grupos, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}