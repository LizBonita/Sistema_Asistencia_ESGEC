<?php
// ✅ PRIMERO: CORS & HEADERS CRÍTICOS
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT, DELETE, GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

session_start();
ob_clean(); // 👈 BORRA SPACE BASURA QUE ROMPE JSON

require_once 'config.php';

// ✅ VERIFICAR MÉTODO
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Solo POST permitido']);
    exit();
}

// ✅ LEER JSON RAW
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// ✅ DEBUG OUTPUT (COMENTAR AL PROBAR)
// print_r($data); // Descomenta para ver qué recibes

$nombre_completo = isset($data['nombre_completo']) ? trim($data['nombre_completo']) : '';
$usuario = isset($data['usuario']) ? trim($data['usuario']) : '';
$password_hash = isset($data['password_hash']) ? trim($data['password_hash']) : '';
$rol_id = isset($data['rol_id']) ? (int)$data['rol_id'] : 0;

// ✅ VALIDACIÓN ESCRITA EXPLICITA
if ($nombre_completo === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'nombre_completo requerido']);
    exit();
}
if ($usuario === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'usuario requerido']);
    exit();
}
if ($password_hash === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'password_hash requerido']);
    exit();
}
if ($rol_id < 1) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'rol_id debe ser > 0']);
    exit();
}

// ✅ INSERTAR A LA BASE DE DATOS
try {
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre_completo, usuario, password_hash, rol_id, fecha_registro) VALUES (?, ?, ?, ?, NOW())");
    if ($stmt->execute([$nombre_completo, $usuario, $password_hash, $rol_id])) {
        $new_id = $conn->lastInsertId();
        
        $stmt_user = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt_user->execute([$new_id]);
        $user_data = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        // ✅ RESPUESTA EXITOSA
        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'message' => 'Usuario creado correctamente',
            'user' => $user_data
        ]);
    } else {
        throw new Exception("Error SQL insert");
    }
} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>