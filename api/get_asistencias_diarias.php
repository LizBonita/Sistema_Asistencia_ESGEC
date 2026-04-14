<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

try {
    $pdo = new PDO("mysql:host=localhost;dbname=sistema_asistencia_db", 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Opcional: filtrar por fecha actual
    $hoy = date('Y-m-d');
    $stmt = $pdo->prepare("SELECT * FROM asistencias WHERE fecha = ?");
    $stmt->execute([$hoy]);
    
    $asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($asistencias);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>