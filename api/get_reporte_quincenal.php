<?php
// api/get_reporte_quincenal.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $inicio = $_GET['inicio'] ?? date('Y-m-01');
    $fin = $_GET['fin'] ?? date('Y-m-t');

    $stmt = $db->prepare("
        SELECT 
            u.nombre_completo AS nombre_maestro,
            m.tipo_contrato,
            COUNT(CASE WHEN a.estado_entrada = 'Retraso' THEN 1 END) AS retardos,
            COALESCE(SUM(a.minutos_retraso), 0) AS minutos_tardanza,
            0 AS ausencias
        FROM maestros m
        JOIN usuarios u ON m.usuario_id = u.id
        LEFT JOIN asistencias a ON a.maestro_id = m.id AND a.fecha BETWEEN ? AND ?
        GROUP BY m.id, u.nombre_completo, m.tipo_contrato
        ORDER BY u.nombre_completo ASC
    ");
    $stmt->execute([$inicio, $fin]);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($result, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}