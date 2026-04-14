<?php
// controllers/CrearMaestroController.php

session_start();
if(!isset($_SESSION['user_id']) || ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador')){
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Maestro.php';

$database = new Database();
$db = $database->getConnection();
$maestro = new Maestro($db);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $maestro->usuario_id = $_POST['usuario_id'];
    $maestro->tipo_contrato = $_POST['tipo_contrato'];

    if($maestro->create()){
        $message = "Maestro agregado correctamente.";
    } else {
        $message = "No se pudo agregar el maestro. Es posible que ya esté registrado.";
    }
    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/gestion_maestros.php?message=' . urlencode($message));
    exit();
} else {
    // Si alguien accede directamente sin POST
    header('Location: ../views/gestion_maestros.php');
    exit();
}
?>