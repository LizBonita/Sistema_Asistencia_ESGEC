<?php
require_once 'config.php';

$texto = isset($_GET['texto']) ? $_GET['texto'] : '';

if ($texto == '') {
    echo json_encode([]);
    exit();
}

$searchTerm = "%{$texto}%";
$stmt = $conn->prepare("SELECT u.*, r.nombre as rol_nombre FROM usuarios u 
                        LEFT JOIN roles r ON u.rol_id = r.id 
                        WHERE u.nombre_completo LIKE ? OR u.usuario LIKE ? OR CAST(u.rol_id AS CHAR) LIKE ?
                        LIMIT 50");
$stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($usuarios);
?>