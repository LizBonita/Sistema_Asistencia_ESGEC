<?php
// Script para rellenar imagen_base64 desde los archivos BMP existentes
// Ejecutar una sola vez: php scripts/rellenar_base64.php

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
$stmt = $db->query("SELECT id, imagen_path FROM huellas_dactilares WHERE imagen_base64 IS NULL AND imagen_path IS NOT NULL AND imagen_path != ''");

$updated = 0;
$errors = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $filepath = __DIR__ . '/../' . $row['imagen_path'];
    
    if (file_exists($filepath)) {
        $imageData = file_get_contents($filepath);
        $base64 = base64_encode($imageData);
        
        $update = $db->prepare("UPDATE huellas_dactilares SET imagen_base64 = ? WHERE id = ?");
        $update->execute([$base64, $row['id']]);
        
        echo "✅ ID {$row['id']}: {$row['imagen_path']} → base64 (" . strlen($base64) . " chars)\n";
        $updated++;
    } else {
        echo "❌ ID {$row['id']}: Archivo no encontrado: {$filepath}\n";
        $errors++;
    }
}

echo "\n═══ Resultado: {$updated} actualizadas, {$errors} errores ═══\n";
