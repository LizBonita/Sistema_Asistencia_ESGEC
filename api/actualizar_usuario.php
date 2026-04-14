<?php
session_start();
ob_clean();
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Método inválido']);
    exit();
}

$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

$id = isset($data['id']) ? (int)$data['id'] : null;
$nombre_completo = isset($data['nombre_completo']) ? trim($data['nombre_completo']) : '';
$usuario = isset($data['usuario']) ? trim($data['usuario']) : '';
$password_hash = isset($data['password_hash']) ? trim($data['password_hash']) : '';
$rol_id = isset($data['rol_id']) ? (int)$data['rol_id'] : null;

if ($id === null || $nombre_completo === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit();
}

try {
    $stmt = $conn->prepare("UPDATE usuarios SET nombre_completo=?, usuario=?, password_hash=?, rol_id=? WHERE id=?");
    if ($stmt->execute([$nombre_completo, $usuario, $password_hash, $rol_id, $id])) {
        echo json_encode(['status' => 'success', 'message' => 'Actualizado']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ID no existe']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>