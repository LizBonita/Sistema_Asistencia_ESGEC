<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Horario.php';

$database = new Database();
$db = $database->getConnection();
$horario = new Horario($db);

// Obtener datos del formulario
$horario->id = $_POST['id'];
$horario->grupo_id = $_POST['id_grupo'];
$horario->dia_semana = $_POST['dia_semana'];
$horario->hora_inicio = $_POST['hora_inicio'];
$horario->hora_fin = $_POST['hora_fin'];

// Actualizar el horario
if ($horario->update()) {
    header('Location: ../views/gestion_horarios_por_maestro.php?message=' . urlencode('Horario actualizado correctamente.'));
    exit();
} else {
    header('Location: ../views/editar_horario.php?id=' . $horario->id . '&error=' . urlencode('Error al actualizar el horario.'));
    exit();
}
?>