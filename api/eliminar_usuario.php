<?php
session_start();
ob_clean();
header("Content-Type: application/json; charset=utf-8");
require_once 'config.php';

// ✅ Soporta tanto GET como QUERY PARAM para compatibilidad
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($id === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
    exit();
}

try {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id=?");
    if ($stmt->execute([$id])) {
        echo json_encode(['status' => 'success', 'message' => 'Eliminado']);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'ID no existe']);
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>