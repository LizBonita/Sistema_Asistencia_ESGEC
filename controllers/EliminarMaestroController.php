<?php
// controllers/EliminarMaestroController.php

session_start();
// Verificar sesión y rol
if(!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador')){
    // Si no está logueado o no tiene permiso, redirigir al login
    header('Location: ../views/login.php');
    exit();
}

// Incluir archivos necesarios
include_once '../config/database.php';
include_once '../models/Maestro.php';

// Obtener conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Instanciar el modelo de Maestro
$maestro = new Maestro($db);

// Verificar si se recibió un ID por GET
if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])){
    // Sanitizar y asignar el ID recibido
    $maestro->id = filter_var($_GET['id'], FILTER_VALIDATE_INT); // Validar que sea un entero

    // Verificar si el ID es válido (no nulo o falso después de validar)
    if($maestro->id !== false && $maestro->id > 0) {
        // Intentar eliminar el maestro
        if($maestro->delete()){
            $message = "Maestro eliminado correctamente.";
        } else {
            // Si delete() falla, puede ser porque hay datos relacionados (asistencias, horarios)
            // Esto depende de cómo tengas configuradas las restricciones en la base de datos (CASCADE, RESTRICT, etc.)
            $message = "No se pudo eliminar el maestro. Puede tener datos asociados (asistencias, horarios, etc.).";
        }
    } else {
        // Si el ID no es válido
        $message = "ID de maestro inválido.";
    }

    // Redirigir de vuelta a la lista de maestros con el mensaje
    header('Location: ../views/gestion_maestros.php?message=' . urlencode($message));
    exit(); // Importante: terminar la ejecución después de redirigir
} else {
    // Si no se accede vía GET o no se proporciona un ID, redirigir a la lista
    header('Location: ../views/gestion_maestros.php');
    exit();
}
?>