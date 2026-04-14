<?php
// index.php - Punto de entrada opcional
// Este archivo puede redirigir a la página de login o al dashboard si ya está logueado

session_start();

// Verificar si ya hay una sesión iniciada
if(isset($_SESSION['user_id'])){
    header('Location: views/inicio.php');
} else {
    header('Location: views/login.php');
}
exit();
?>