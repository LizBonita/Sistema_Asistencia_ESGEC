<?php
// scripts/test_uploads.php
// Test: ¿Se puede escribir en uploads/huellas desde PHP?
header('Content-Type: text/html; charset=utf-8');
echo '<h2>Test de escritura en uploads/huellas</h2><pre>';

$dir = __DIR__ . '/../uploads/huellas';
echo "Directorio: $dir\n";
echo "¿Existe? " . (is_dir($dir) ? 'SI' : 'NO') . "\n";

if (!is_dir($dir)) {
    echo "Creando...\n";
    $created = @mkdir($dir, 0755, true);
    echo "¿Creado? " . ($created ? 'SI' : 'NO') . "\n";
}

echo "¿Escribible? " . (is_writable($dir) ? 'SI' : 'NO') . "\n";
echo "Permisos: " . substr(sprintf('%o', fileperms($dir)), -4) . "\n";

// Intentar escribir un archivo de prueba
$testFile = $dir . '/test_' . time() . '.txt';
$written = @file_put_contents($testFile, 'test escribir archivo');
echo "\n¿Se pudo escribir test? " . ($written !== false ? "SI ($written bytes)" : 'NO') . "\n";
echo "Archivo: $testFile\n";

if (file_exists($testFile)) {
    echo "¡ARCHIVO CREADO EXITOSAMENTE!\n";
    @unlink($testFile); // Limpiar
    echo "(archivo de prueba eliminado)\n";
}

// Listar archivos existentes
echo "\nArchivos en uploads/huellas:\n";
$files = @scandir($dir);
if ($files) {
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $size = filesize($dir . '/' . $f);
        echo "  - $f ($size bytes)\n";
    }
} else {
    echo "  (no se puede listar)\n";
}

echo '</pre>';
