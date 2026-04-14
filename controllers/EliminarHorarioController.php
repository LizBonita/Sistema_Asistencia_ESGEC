<?php
// controllers/EliminarHorarioController.php

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina')) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Horario.php';

$database = new Database();
$db = $database->getConnection();
$horario = new Horario($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $horario->id_horario = $_GET['id'];

    if ($horario->delete()) {
        $message = "Horario eliminado correctamente.";
    } else {
        $message = "No se pudo eliminar el horario.";
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_horarios.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/gestion_horarios.php');
    exit();
}
?>