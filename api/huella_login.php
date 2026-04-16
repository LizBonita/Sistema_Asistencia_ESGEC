<?php
// api/huella_login.php
// Login por huella dactilar: identifica al maestro, registra asistencia e inicia sesión
date_default_timezone_set('America/Mexico_City');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit(0); }

session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Huella.php';
require_once __DIR__ . '/../models/Asistencia.php';

$response = ["success" => false, "message" => ""];

try {
    $input = json_decode(file_get_contents("php://input"), true);
    $maestro_id = intval($input['maestro_id'] ?? 0);

    if ($maestro_id <= 0) {
        throw new Exception("maestro_id inválido");
    }

    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexión a BD");
    }

    // 1. Obtener datos del maestro para la sesión
    $stmt = $db->prepare("
        SELECT m.id as maestro_id, u.id as user_id, u.nombre_completo, 
               u.usuario, r.nombre as rol_nombre, u.rol_id
        FROM maestros m 
        JOIN usuarios u ON m.usuario_id = u.id 
        LEFT JOIN roles r ON u.rol_id = r.id
        WHERE m.id = ?
    ");
    $stmt->execute([$maestro_id]);
    $maestro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$maestro) {
        throw new Exception("Maestro no encontrado");
    }

    // 2. Registrar asistencia CON control de retrasos
    // ─── Configuración de horarios ───
    $HORA_LIMITE_ENTRADA = '08:00:00';  // Hora máxima para entrar a tiempo
    $HORA_LIMITE_SALIDA  = '14:00:00';  // Hora mínima de salida normal

    $hoy = date('Y-m-d');
    $hora_actual = date('H:i:s');

    // Verificar si ya tiene entrada hoy
    $check = $db->prepare("
        SELECT id, hora_entrada, hora_salida 
        FROM asistencias 
        WHERE maestro_id = ? AND fecha = ?
        ORDER BY id DESC LIMIT 1
    ");
    $check->execute([$maestro_id, $hoy]);
    $registro_hoy = $check->fetch(PDO::FETCH_ASSOC);

    $tipo = 'entrada';
    if ($registro_hoy && $registro_hoy['hora_entrada'] && !$registro_hoy['hora_salida']) {
        // Ya tiene entrada sin salida → registrar salida
        $estado_salida = ($hora_actual < $HORA_LIMITE_SALIDA) ? 'Salida temprana' : 'A tiempo';
        $update = $db->prepare("UPDATE asistencias SET hora_salida = ?, estado_salida = ? WHERE id = ?");
        $update->execute([$hora_actual, $estado_salida, $registro_hoy['id']]);
        $tipo = 'salida';
    } else {
        // Nueva entrada — calcular retraso
        $estado_entrada = 'A tiempo';
        $minutos_retraso = 0;

        if ($hora_actual > $HORA_LIMITE_ENTRADA) {
            $entrada = new DateTime($hora_actual);
            $limite  = new DateTime($HORA_LIMITE_ENTRADA);
            $diff = $entrada->diff($limite);
            $minutos_retraso = ($diff->h * 60) + $diff->i;
            $estado_entrada = 'Retraso';
        }

        $insert = $db->prepare("
            INSERT INTO asistencias (maestro_id, fecha, hora_entrada, estado_entrada, minutos_retraso) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $insert->execute([$maestro_id, $hoy, $hora_actual, $estado_entrada, $minutos_retraso]);
    }

    // 3. Iniciar sesión PHP
    $_SESSION['user_id'] = $maestro['user_id'];
    $_SESSION['user_nombre'] = $maestro['nombre_completo'];
    $_SESSION['user_rol_id'] = $maestro['rol_id'];
    $_SESSION['user_rol_nombre'] = $maestro['rol_nombre'] ?? 'Maestro';

    $response["success"] = true;
    $response["message"] = "Login y asistencia registrados";
    $response["nombre"] = $maestro['nombre_completo'];
    $response["rol"] = $maestro['rol_nombre'] ?? 'Maestro';
    $response["tipo"] = $tipo;
    $response["hora"] = $hora_actual;
    $response["estado"] = $tipo === 'entrada' ? ($estado_entrada ?? 'A tiempo') : ($estado_salida ?? 'A tiempo');
    $response["minutos_retraso"] = $tipo === 'entrada' ? ($minutos_retraso ?? 0) : 0;
    $response["redirect"] = "inicio.php";

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
