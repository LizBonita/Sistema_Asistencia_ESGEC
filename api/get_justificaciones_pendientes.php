<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$id_maestro = $_GET['id'] ?? 0;

$pdo = new PDO("mysql:host=localhost;dbname=sistema_asistencia;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->prepare("
    SELECT 
        j.id, j.id_maestro, j.motivo, j.fecha_solicitud, 
        j.fecha_inicio, j.fecha_fin, j.estado,
        m.nombre AS nombre_maestro
    FROM justificaciones j
    JOIN maestros m ON j.id_maestro = m.id
    WHERE j.estado = 'pendiente' AND j.id_maestro = ?
");
$stmt->execute([$id_maestro]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($result);
?>