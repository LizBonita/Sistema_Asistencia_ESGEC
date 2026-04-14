<?php
// views/logout.php
session_start();
session_destroy();
header('Location: inicio.php'); // Redirige a la nueva página principal
exit();
?>