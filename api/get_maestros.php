<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_asistencia_db", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("SELECT id, usuario_id, tipo_contrato, fecha_registro FROM maestros");
    $maestros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($maestros);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>