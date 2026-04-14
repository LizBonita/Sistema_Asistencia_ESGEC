<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$pdo = new PDO("mysql:host=localhost;dbname=sistema_asistencia;charset=utf8", 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SELECT id, nombre, clave FROM materias");
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($result);
?>