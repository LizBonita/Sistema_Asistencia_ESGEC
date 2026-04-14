<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO("mysql:host=localhost;dbname=sistema_asistencia;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$inicio = $_GET['inicio'] ?? date('Y-m-01');
$fin = $_GET['fin'] ?? date('Y-m-t');

$stmt = $pdo->prepare("
    SELECT 
        m.nombre AS nombre_maestro,
        COUNT(*) AS retardos,
        SUM(TIMESTAMPDIFF(MINUTE, h.hora_inicio, a.hora_entrada)) AS minutos_tardanza,
        0 AS ausencias
    FROM asistencias a
    JOIN maestros m ON a.id_maestro = m.id
    JOIN horarios h ON m.id_horario = h.id
    WHERE a.fecha BETWEEN ? AND ?
      AND a.estado = 'tardanza'
    GROUP BY a.id_maestro
");
$stmt->execute([$inicio, $fin]);
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result);
?>