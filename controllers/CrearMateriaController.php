<?php
session_start();
include_once '../config/database.php';
include_once '../models/Materia.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instancia del modelo Materia
$materia = new Materia($db);

// Obtener datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';

// Validar que el nombre no esté vacío
if (empty($nombre)) {
    // Redirigir con mensaje de error
    header('Location: ../views/gestion_materias.php?message=El nombre de la materia es obligatorio.');
    exit();
}

// Asignar el nombre al objeto Materia
$materia->nombre = $nombre;

// Intentar crear la materia
if ($materia->create()) {
    // Éxito: redirigir con mensaje
    header('Location: ../views/gestion_materias.php?message=Materia creada exitosamente.');
    exit();
} else {
    // Error: redirigir con mensaje
    header('Location: ../views/gestion_materias.php?message=Error al crear la materia.');
    exit();
}
?>