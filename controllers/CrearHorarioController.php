<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}
if ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador') {
    die("Acceso denegado.");
}

include_once '../config/database.php';
include_once '../models/Horario.php';

$database = new Database();
$db = $database->getConnection();
$horario = new Horario($db);

// 🔍 Recibir datos del formulario
$maestro_id = $_POST['maestro_id'] ?? '';
$materia_id = $_POST['materia_id'] ?? '';
$grupo_id = $_POST['grupo_id'] ?? '';
$dia_semana = $_POST['dia_semana'] ?? '';
$hora_inicio = $_POST['hora_inicio'] ?? '';
$hora_fin = $_POST['hora_fin'] ?? '';
$tolerancia_entrada = $_POST['tolerancia_entrada'] ?? '';
$limite_retardo = $_POST['limite_retardo'] ?? '';

// 🧾 Validación básica
if (
    empty($maestro_id) || empty($materia_id) || empty($grupo_id) ||
    empty($dia_semana) || empty($hora_inicio) || empty($hora_fin) ||
    !is_numeric($tolerancia_entrada) || !is_numeric($limite_retardo)
) {
    header('Location: ../views/gestion_horarios.php?message=❌ Error: Todos los campos son obligatorios.');
    exit();
}

// 🚨 Validar IDs en la base de datos
try {
    // Verificar que el maestro exista
    $check_maestro = $db->prepare("SELECT id FROM maestros WHERE id = ?");
    $check_maestro->execute([$maestro_id]);
    if ($check_maestro->rowCount() === 0) {
        header('Location: ../views/gestion_horarios.php?message=❌ Error: El maestro seleccionado no existe.');
        exit();
    }

    // Verificar que la materia exista
    $check_materia = $db->prepare("SELECT id FROM materias WHERE id = ?");
    $check_materia->execute([$materia_id]);
    if ($check_materia->rowCount() === 0) {
        header('Location: ../views/gestion_horarios.php?message=❌ Error: La materia seleccionada no existe.');
        exit();
    }

    // Verificar que el grupo exista
    $check_grupo = $db->prepare("SELECT id FROM grupos WHERE id = ?");
    $check_grupo->execute([$grupo_id]);
    if ($check_grupo->rowCount() === 0) {
        header('Location: ../views/gestion_horarios_por_maestro.php?message=❌ Error: El grupo seleccionado no existe.');
        exit();
    }
} catch (Exception $e) {
    header('Location: ../views/gestion_horarios_por_maestro.php?message=❌ Error interno: ' . urlencode($e->getMessage()));
    exit();
}

// 📝 Asignar valores al modelo
$horario->maestro_id = (int)$maestro_id;
$horario->materia_id = (int)$materia_id;
$horario->grupo_id = (int)$grupo_id;
$horario->dia_semana = htmlspecialchars(trim($dia_semana));
$horario->hora_inicio = htmlspecialchars(trim($hora_inicio));
$horario->hora_fin = htmlspecialchars(trim($hora_fin));
$horario->tolerancia_entrada = (int)$tolerancia_entrada;
$horario->limite_retardo = (int)$limite_retardo;

// 🚀 Crear horario
if ($horario->create()) {
    header('Location: ../views/gestion_horarios_por_maestro.php?message=✅ Horario guardado correctamente!');
} else {
    $err = $horario->conn->errorInfo();
    header('Location: ../views/gestion_horarios_por_maestro.php?message=❌ Error SQL: ' . urlencode($err[2]));
}
?>