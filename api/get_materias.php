<?php
// api/get_materias.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $stmt = $db->query("SELECT id, nombre, clave FROM materias ORDER BY nombre ASC");
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($materias, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}