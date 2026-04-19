<?php
// api/login.php — Login para la app móvil Android
// Usa database.php para auto-detectar ambiente (local XAMPP vs Hostinger)

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

require_once __DIR__ . '/../config/database.php';

$response = ["success" => false, "message" => ""];

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexión a BD");
    }

    $usuario = $_POST['usuario'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        throw new Exception("Usuario y contraseña requeridos");
    }

    // Buscar usuario por nombre de usuario
    $stmt = $db->prepare("
        SELECT u.id, u.nombre_completo, u.usuario, u.password_hash, u.rol_id, r.nombre as rol_nombre
        FROM usuarios u
        LEFT JOIN roles r ON u.rol_id = r.id
        WHERE u.usuario = ?
        LIMIT 1
    ");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && $contrasena === $user['password_hash']) {
        // Login exitoso
        $response['success'] = true;
        $response['message'] = 'Inicio de sesión exitoso';
        $response['user'] = [
            'id'               => intval($user['id']),
            'nombre'           => $user['nombre_completo'],
            'nombre_completo'  => $user['nombre_completo'],
            'usuario'          => $user['usuario'],
            'rol'              => intval($user['rol_id']),
            'rol_nombre'       => $user['rol_nombre'] ?? 'Sin rol'
        ];
    } else {
        $response['message'] = 'Credenciales incorrectas';
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);