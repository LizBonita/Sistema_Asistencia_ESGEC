<?php
// ✅ LA PRIMERA LÍNEA DEBE SER ESTO EXACTAMENTE
header("Content-Type: application/json; charset=utf-8");
session_start();
ob_clean();

$host = "localhost";
$dbname = "sistema_asistencia_db"; 
$username = "root";
$password = ""; 

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'DB Error']);
    exit();
}
?>