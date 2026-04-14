<?php
// test_insert.php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

echo "<h2>🔍 Prueba de inserción directa en tabla horarios</h2>";

// 🔎 Paso 1: Verificar conexión
if (!$db) {
    echo "<p style='color:red'>❌ Error: No se conectó a la base de datos.</p>";
    exit();
}
echo "<p>✅ Conexión OK</p>";

// 🔎 Paso 2: Verificar si la tabla existe
$table_check = $db->query("SHOW TABLES LIKE 'horarios'");
if ($table_check->rowCount() === 0) {
    echo "<p style='color:red'>❌ Tabla 'horarios' no existe.</p>";
    exit();
}
echo "<p>✅ Tabla 'horarios' existe</p>";

// 🔎 Paso 3: Intentar INSERT directo (valores seguros)
$sql = "INSERT INTO horarios (maestro_id, materia_id, grupo_id, dia_semana, hora_inicio, hora_fin, tolerancia_entrada, limite_retardo) 
        VALUES (1, 1, 1, 'Lunes', '09:00:00', '10:00:00', 10, 15)";

try {
    $stmt = $db->prepare($sql);
    $result = $stmt->execute();
    
    if ($result) {
        $last_id = $db->lastInsertId();
        echo "<p style='color:green'>✅ ¡ÉXITO! Horario insertado con ID = $last_id</p>";
        
        // Mostrar lo que se insertó
        $check = $db->query("SELECT * FROM horarios WHERE id = $last_id");
        $row = $check->fetch(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($row);
        echo "</pre>";
    } else {
        $error = $stmt->errorInfo();
        echo "<p style='color:red'>❌ Fallo al insertar:<br>";
        print_r($error);
        echo "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>💥 Excepción: " . $e->getMessage() . "</p>";
}
?>