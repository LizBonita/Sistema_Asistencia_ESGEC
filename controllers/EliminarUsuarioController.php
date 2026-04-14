<?php
// controllers/EliminarUsuarioController.php

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

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id'])){
    $usuario->id = $_GET['id'];

    // Precaución: Verificar si tiene maestros u otros datos asociados
    // (Podría requerir lógica adicional o usar CONSTRAINTS en la BD)
    if($usuario->delete()){
        $message = "Usuario eliminado correctamente.";
    } else {
        $message = "No se pudo eliminar el usuario. Puede tener datos asociados (Maestro, etc.).";
    }
    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_usuarios.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/gestion_usuarios.php');
    exit();
}
?>