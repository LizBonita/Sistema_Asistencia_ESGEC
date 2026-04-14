<?php
// controllers/ActualizarGrupoController.php

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina')) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Grupo.php';
include_once '../models/Maestro.php';
include_once '../models/Materia.php';

$database = new Database();
$db = $database->getConnection();
$grupo = new Grupo($db);
$maestro = new Maestro($db);
$materia = new Materia($db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $grupo->id_grupo = $_POST['id'];
    $grupo->nombre_grupo = $_POST['nombre_grupo'];
    $grupo->id_maestro = $_POST['id_maestro'];
    $grupo->id_materia = $_POST['id_materia'];

    // Validaciones básicas
    if (empty($grupo->nombre_grupo)) {
        $message = "El nombre del grupo es obligatorio.";
    } else {
        if ($grupo->update()) {
            $message = "Grupo actualizado correctamente.";
        } else {
            $message = "No se pudo actualizar el grupo.";
        }
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_grupos.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/gestion_grupos.php');
    exit();
}
?>