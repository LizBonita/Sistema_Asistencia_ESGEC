<?php
// controllers/CrearUsuarioController.php

session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador')){
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();
$usuario = new Usuario($db);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $usuario->nombre_completo = $_POST['nombre_completo'];
    $usuario->usuario = $_POST['usuario'];
    $password_plana = $_POST['password']; // Contraseña sin hashear
    $usuario->rol_id = $_POST['rol_id'];

    // Validaciones básicas
    if(empty($usuario->nombre_completo) || empty($usuario->usuario) || empty($password_plana) || empty($usuario->rol_id)) {
        $message = "Todos los campos son obligatorios.";
    } else {
        // Llamar al create pasando la contraseña plana para hashearla
        if($usuario->create($password_plana)){
            $message = "Usuario agregado correctamente.";
        } else {
            $message = "No se pudo agregar el usuario. Es posible que el nombre de usuario ya exista.";
        }
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_usuarios.php?message=' . urlencode($message));
    exit();
} else {
    // Si alguien accede directamente sin POST
    header('Location: ../views/gestion_usuarios.php');
    exit();
}
?>