<?php
// api/get_horarios.php
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->getConnection();
    if (!$db) { throw new Exception("Error de conexión a BD"); }

    // Detectar las columnas reales de la tabla horarios
    $cols = $db->query("SHOW COLUMNS FROM horarios")->fetchAll(PDO::FETCH_COLUMN);
    
    $colGrupo = in_array('id_grupo', $cols) ? 'id_grupo' : (in_array('grupo_id', $cols) ? 'grupo_id' : null);
    $colMateria = in_array('id_materia', $cols) ? 'id_materia' : (in_array('materia_id', $cols) ? 'materia_id' : null);
    $colMaestro = in_array('id_maestro', $cols) ? 'id_maestro' : (in_array('maestro_id', $cols) ? 'maestro_id' : null);
    $colDia = in_array('dia', $cols) ? 'dia' : (in_array('dia_semana', $cols) ? 'dia_semana' : null);

    // Construir SELECT dinámico
    $selectParts = ["h.id"];
    $joinParts = [];
    
    if ($colDia) $selectParts[] = "h.$colDia AS dia";
    if (in_array('hora_inicio', $cols)) $selectParts[] = "h.hora_inicio";
    if (in_array('hora_fin', $cols)) $selectParts[] = "h.hora_fin";
    if (in_array('tolerancia_entrada', $cols)) $selectParts[] = "h.tolerancia_entrada";
    if (in_array('limite_retardo', $cols)) $selectParts[] = "h.limite_retardo";
    
    if ($colGrupo) {
        $selectParts[] = "h.$colGrupo AS grupo_id";
        $selectParts[] = "g.nombre AS nombre_grupo";
        $joinParts[] = "LEFT JOIN grupos g ON h.$colGrupo = g.id";
    }
    if ($colMateria) {
        $selectParts[] = "h.$colMateria AS materia_id";
        $selectParts[] = "mat.nombre AS nombre_materia";
        $joinParts[] = "LEFT JOIN materias mat ON h.$colMateria = mat.id";
    }
    if ($colMaestro) {
        $selectParts[] = "h.$colMaestro AS maestro_id";
        $selectParts[] = "u.nombre_completo AS nombre_maestro";
        $joinParts[] = "LEFT JOIN maestros m ON h.$colMaestro = m.id";
        $joinParts[] = "LEFT JOIN usuarios u ON m.usuario_id = u.id";
    }
    
    $sql = "SELECT " . implode(", ", $selectParts) . " FROM horarios h " . implode(" ", $joinParts);
    
    // Filtro por maestro_id si se pasa
    $params = [];
    $maestro_id = $_GET['maestro_id'] ?? 0;
    if ($maestro_id > 0 && $colMaestro) {
        $sql .= " WHERE h.$colMaestro = ?";
        $params[] = $maestro_id;
    }
    
    if ($colDia) $sql .= " ORDER BY h.$colDia, h.hora_inicio ASC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($horarios, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}