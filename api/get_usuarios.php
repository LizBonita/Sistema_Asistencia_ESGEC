<?php
// api/get_usuarios.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $stmt = $db->query("
        SELECT u.id, u.nombre_completo, u.usuario, u.rol_id, u.fecha_registro,
               r.nombre AS rol_nombre
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id
        ORDER BY u.nombre_completo ASC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}