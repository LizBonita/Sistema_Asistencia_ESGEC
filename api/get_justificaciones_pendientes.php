<?php
// api/get_justificaciones_pendientes.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $id_maestro = $_GET['id_maestro'] ?? ($_GET['id'] ?? 0);

    $query = "
        SELECT 
            j.id, j.id_maestro, j.motivo, j.fecha_solicitud, 
            j.fecha_inicio, j.fecha_fin, j.estado,
            u.nombre_completo AS nombre_maestro
        FROM justificaciones j
        JOIN maestros m ON j.id_maestro = m.id
        JOIN usuarios u ON m.usuario_id = u.id
    ";

    if ($id_maestro > 0) {
        $query .= " WHERE j.estado = 'pendiente' AND j.id_maestro = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$id_maestro]);
    } else {
        // Si no se pasa id, devolver todas las pendientes (para admin)
        $query .= " WHERE j.estado = 'pendiente' ORDER BY j.fecha_solicitud DESC";
        $stmt = $db->prepare($query);
        $stmt->execute();
    }

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}