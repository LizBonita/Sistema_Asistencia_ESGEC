<?php
// controllers/EliminarMateriaController.php

session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina')) {
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Materia.php';

$database = new Database();
$db = $database->getConnection();
$materia = new Materia($db);

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])) {
    $materia->id_materia = $_GET['id'];

    if ($materia->delete()) {
        $message = "Materia eliminada correctamente.";
    } else {
        $message = "No se pudo eliminar la materia. Puede tener datos asociados (Grupos, etc.).";
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_materias.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/gestion_materias.php');
    exit();
}
?>