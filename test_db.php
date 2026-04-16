<?php
// test_db.php — Diagnóstico temporal (BORRAR después de usar)
header('Content-Type: application/json');

$configs = [
    'config1' => [
        'host' => 'localhost',
        'db' => 'u596094670_sistema_asist',
        'user' => 'u596094670_Liz',
        'pass' => 'LizE@110324'
    ],
];

$results = [];
foreach ($configs as $name => $c) {
    try {
        $conn = new PDO(
            "mysql:host={$c['host']};dbname={$c['db']}",
            $c['user'], $c['pass']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $results[$name] = "OK - Conectado";
        
        // Verificar tablas
        $stmt = $conn->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $results[$name . '_tables'] = $tables;
    } catch (PDOException $e) {
        $results[$name] = "ERROR: " . $e->getMessage();
    }
}

$results['http_host'] = $_SERVER['HTTP_HOST'] ?? 'N/A';
$results['server_name'] = $_SERVER['SERVER_NAME'] ?? 'N/A';

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
