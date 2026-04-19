<?php
// api/get_huella_imagen.php
// Convierte BMP a PNG y lo sirve como imagen para la app Android
header("Access-Control-Allow-Origin: *");

$maestro_id = $_GET['maestro_id'] ?? 0;
if (!$maestro_id) {
    http_response_code(400);
    exit;
}

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    $stmt = $db->prepare("SELECT imagen_path FROM huellas WHERE maestro_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$maestro_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$row || empty($row['imagen_path'])) {
        http_response_code(404);
        exit;
    }
    
    $filePath = __DIR__ . '/../' . $row['imagen_path'];
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit;
    }
    
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    
    // Intentar cargar según extensión
    $image = null;
    switch ($ext) {
        case 'bmp':
            if (function_exists('imagecreatefrombmp')) {
                $image = @imagecreatefrombmp($filePath);
            }
            // Fallback: leer raw BMP con GD
            if (!$image) {
                // Intentar con imagecreatefromstring para formatos soportados
                $data = file_get_contents($filePath);
                $image = @imagecreatefromstring($data);
            }
            break;
        case 'png':
            $image = @imagecreatefrompng($filePath);
            break;
        case 'jpg':
        case 'jpeg':
            $image = @imagecreatefromjpeg($filePath);
            break;
        default:
            $data = file_get_contents($filePath);
            $image = @imagecreatefromstring($data);
    }
    
    if (!$image) {
        // Si GD no puede procesar el BMP, servir el archivo raw
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        header("Content-Type: " . $mime);
        header("Cache-Control: public, max-age=86400");
        readfile($filePath);
        exit;
    }
    
    // Servir como PNG
    header("Content-Type: image/png");
    header("Cache-Control: public, max-age=86400");
    imagepng($image, null, 6);
    imagedestroy($image);
    
} catch (Exception $e) {
    http_response_code(500);
    echo $e->getMessage();
}
