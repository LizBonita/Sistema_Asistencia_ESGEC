<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Grupo.php';

$database = new Database();
$db = $database->getConnection();
$grupo = new Grupo($db);

// Obtener datos del formulario
$grupo->id = $_POST['id'];
$grupo->nombre = $_POST['nombre_grupo'];

// Actualizar el grupo
if ($grupo->update()) {
    header('Location: ../views/gestion_grupos.php?message=' . urlencode('Grupo actualizado correctamente.'));
    exit();
} else {
    header('Location: ../views/editar_grupo.php?id=' . $grupo->id . '&error=' . urlencode('Error al actualizar el grupo.'));
    exit();
}
?>