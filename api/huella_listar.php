<?php
// api/huella_listar.php
// Lista maestros con estado de huella registrada

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Huella.php';

$response = ["success" => false, "message" => ""];

try {
    $db = (new Database())->getConnection();
    if (!$db) {
        throw new Exception("Error de conexion a BD");
    }

    $huella = new Huella($db);
    $stmt = $huella->listarMaestrosConEstado();
    $maestros = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $maestros[] = $row;
    }

    $response["success"] = true;
    $response["maestros"] = $maestros;
    $response["total"] = count($maestros);

} catch (Exception $e) {
    $response["message"] = $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE);
