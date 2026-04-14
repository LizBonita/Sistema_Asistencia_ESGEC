<?php
// ✅ ESTAS SON LAS DOS LÍNEAS MÁS IMPORTANTES: NO TOLERAN ESPACIOS
session_start();
ob_clean(); // Borra espacios basura que rompen JSON

require_once 'config.php';

$stmt = $conn->prepare("SELECT * FROM usuarios ORDER BY id DESC LIMIT 10");
$stmt->execute();
$resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($resultado === null || empty($resultado)) {
    $resultado = [];
}

echo json_encode($resultado);
exit();