<?php
// controllers/EliminarGrupoController.php

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina')) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Grupo.php';

$database = new Database();
$db = $database->getConnection();
$grupo = new Grupo($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $grupo->id_grupo = $_GET['id'];

    if ($grupo->delete()) {
        $message = "Grupo eliminado correctamente.";
    } else {
        $message = "No se pudo eliminar el grupo. Puede tener datos asociados (Horarios, etc.).";
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_grupos.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/gestion_grupos.php');
    exit();
}
?>