<?php
// controllers/AprobarIncidenciaController.php

session_start();
if(!isset($_SESSION['user_id']) || $_SESSION['user_rol_nombre'] !== 'Director'){
    header('Location: ../views/login.php');
    exit();
}

include_once '../config/database.php';
include_once '../models/Incidencia.php';

$database = new Database();
$db = $database->getConnection();
$incidencia = new Incidencia($db);

if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['id']) && isset($_GET['accion'])){
    $incidencia->id = $_GET['id'];
    $accion = $_GET['accion'];
    $comentario = $_POST['comentario'] ?? ''; // Comentario opcional enviado por POST si se usa un modal/form

    if($accion === 'aprobar') {
        if($incidencia->aprobarRechazar(true, $comentario)){
            $message = "Incidencia aprobada correctamente.";
        } else {
            $message = "Error al aprobar la incidencia.";
        }
    } elseif ($accion === 'rechazar') {
        if($incidencia->aprobarRechazar(false, $comentario)){
            $message = "Incidencia rechazada correctamente.";
        } else {
            $message = "Error al rechazar la incidencia.";
        }
    } else {
        $message = "Acción no válida.";
    }

    // Redirigir de vuelta a la lista con mensaje
    header('Location: ../views/incidencias_pendientes.php?message=' . urlencode($message));
    exit();
} else {
    header('Location: ../views/incidencias_pendientes.php');
    exit();
}
?>