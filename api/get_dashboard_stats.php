<?php
// api/get_dashboard_stats.php — Estadísticas para el dashboard móvil
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    $hoy = date('Y-m-d');

    // Conteo de maestros
    $maestros = $db->query("SELECT COUNT(*) as total FROM maestros")->fetch()['total'];

    // Conteo de grupos
    $grupos = $db->query("SELECT COUNT(*) as total FROM grupos")->fetch()['total'];

    // Conteo de materias
    $materias = $db->query("SELECT COUNT(*) as total FROM materias")->fetch()['total'];

    // Conteo de usuarios
    $usuarios = $db->query("SELECT COUNT(*) as total FROM usuarios")->fetch()['total'];

    // Asistencias de hoy
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM asistencias WHERE fecha = ?");
    $stmt->execute([$hoy]);
    $asistencias_hoy = $stmt->fetch()['total'];

    // Puntuales hoy
    $stmt2 = $db->prepare("SELECT COUNT(*) as total FROM asistencias WHERE fecha = ? AND estado_entrada = 'A tiempo'");
    $stmt2->execute([$hoy]);
    $puntuales = $stmt2->fetch()['total'];

    // Retardos hoy
    $stmt3 = $db->prepare("SELECT COUNT(*) as total FROM asistencias WHERE fecha = ? AND estado_entrada = 'Retraso'");
    $stmt3->execute([$hoy]);
    $retardos = $stmt3->fetch()['total'];

    // Sin registrar hoy
    $sin_registrar = max(0, $maestros - $asistencias_hoy);

    echo json_encode([
        'success'          => true,
        'maestros'         => intval($maestros),
        'grupos'           => intval($grupos),
        'materias'         => intval($materias),
        'usuarios'         => intval($usuarios),
        'asistencias_hoy'  => intval($asistencias_hoy),
        'puntuales'        => intval($puntuales),
        'retardos'         => intval($retardos),
        'sin_registrar'    => intval($sin_registrar),
        'fecha'            => $hoy
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
