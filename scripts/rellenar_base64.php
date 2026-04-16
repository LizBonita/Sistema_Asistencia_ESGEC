<?php
// Script para rellenar imagen_base64 desde los archivos BMP existentes
// Ejecutar desde navegador: https://dominio.com/scripts/rellenar_base64.php
// O desde CLI: php scripts/rellenar_base64.php

// Detectar si es web o CLI
$isWeb = php_sapi_name() !== 'cli';
if ($isWeb) {
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Migración Base64</title>
    <style>body{font-family:monospace;background:#1a1a2e;color:#eee;padding:30px}
    .ok{color:#00c853}.err{color:#f44336}h2{color:#0E4D92}</style></head><body>';
    echo '<h2>🔄 Migración de imágenes BMP → Base64</h2><pre>';
}

require_once __DIR__ . '/../config/database.php';

$db = (new Database())->getConnection();
if (!$db) {
    echo "❌ Error de conexión a BD\n";
    exit;
}

$stmt = $db->query("SELECT id, imagen_path FROM huellas_dactilares WHERE (imagen_base64 IS NULL OR imagen_base64 = '') AND imagen_path IS NOT NULL AND imagen_path != ''");

$updated = 0;
$errors = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $filepath = __DIR__ . '/../' . $row['imagen_path'];
    
    if (file_exists($filepath)) {
        $imageData = file_get_contents($filepath);
        $base64 = base64_encode($imageData);
        
        $update = $db->prepare("UPDATE huellas_dactilares SET imagen_base64 = ? WHERE id = ?");
        $update->execute([$base64, $row['id']]);
        
        $msg = "✅ ID {$row['id']}: {$row['imagen_path']} → base64 (" . strlen($base64) . " chars)";
        echo $isWeb ? "<span class='ok'>$msg</span>\n" : "$msg\n";
        $updated++;
    } else {
        $msg = "❌ ID {$row['id']}: Archivo no encontrado: {$filepath}";
        echo $isWeb ? "<span class='err'>$msg</span>\n" : "$msg\n";
        $errors++;
    }
}

echo "\n═══ Resultado: {$updated} actualizadas, {$errors} errores ═══\n";

if ($isWeb) {
    echo '</pre>';
    if ($updated > 0) {
        echo '<p style="color:#00c853;font-size:18px">✅ ¡Migración completada! Recarga Gestión de Huellas para ver las imágenes.</p>';
    }
    echo '<a href="../views/gestion_huellas.php" style="color:#4fc3f7">← Volver a Gestión de Huellas</a>';
    echo '</body></html>';
}
