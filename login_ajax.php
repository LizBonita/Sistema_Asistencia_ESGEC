<?php
// login_ajax.php

// Activar errores para depuración (esto puede revelar el problema)
// Quita esta línea una vez funcione, no es seguro mostrar errores en prod
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Iniciar sesión
session_start();

// Limpiar cualquier salida previa que pueda haber ocurrido antes de este script
ob_clean();

// Verificar si es una petición POST y si es AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action']) && $_POST['action'] === 'login') {
    // Incluir los archivos necesarios
    include_once 'config/database.php';
    include_once 'models/Usuario.php';

    $database = new Database();
    $db = $database->getConnection();

    // Verificar conexión a la base de datos
    if (!$db) {
        // Asegurar que se envíe el header antes de cualquier output
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
        exit();
    }

    $usuario_model = new Usuario($db);

    // Obtener datos del formulario
    $usuario_ingresado = trim($_POST['usuario']);
    $password_ingresado = $_POST['password'];

    // Intentar login
    if ($usuario_model->login($usuario_ingresado, $password_ingresado)) {
        // Login exitoso
        $_SESSION['user_id'] = $usuario_model->id;
        $_SESSION['user_nombre'] = $usuario_model->nombre_completo;
        $_SESSION['user_rol_id'] = $usuario_model->rol_id;
        $_SESSION['user_rol_nombre'] = $usuario_model->getRolNombre($usuario_model->rol_id);

        // Asegurar que se envíe el header antes de cualquier output
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    } else {
        // Login fallido
        // Asegurar que se envíe el header antes de cualquier output
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Usuario o contraseña incorrectos.']);
    }
} else {
    // Si no es una petición válida de login, enviar un error
    // Asegurar que se envíe el header antes de cualquier output
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Petición no válida.']);
}

exit(); // Asegurar que no se ejecute más código
?>