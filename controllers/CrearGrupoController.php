<?php
session_start();
include_once '../config/database.php';
include_once '../models/Grupo.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instancia del modelo Grupo
$grupo = new Grupo($db);

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

// Validar que el nombre no esté vacío
if (empty($nombre)) {
    // Redirigir con mensaje de error
    header('Location: ../views/gestion_grupos.php?message=El nombre del grupo es obligatorio.');
    exit();
}

// Asignar el nombre al objeto Grupo
$grupo->nombre = $nombre;

// Intentar crear el grupo
if ($grupo->create()) {
    // Éxito: redirigir con mensaje
    header('Location: ../views/gestion_grupos.php?message=Grupo creado exitosamente.');
    exit();
} else {
    // Error: redirigir con mensaje
    header('Location: ../views/gestion_grupos.php?message=Error al crear el grupo.');
    exit();
}
?>