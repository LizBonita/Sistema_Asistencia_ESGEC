<?php
// ✅ LA PRIMERA LÍNEA DEBE SER ESTO EXACTAMENTE
header("Content-Type: application/json; charset=utf-8");
session_start();
ob_clean();

// Detectar ambiente automáticamente
if ($_SERVER['HTTP_HOST'] === 'localhost' || 
    strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
    $host = "localhost";
    $dbname = "sistema_asistencia_db"; 
    $username = "root";
    $password = ""; 
} else {
    $host = "localhost";
    $dbname = "u596094670_sistema_asist";
    $username = "u596094670_Liz";
    $password = "LizE@110324";
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
    exit();
}
?>