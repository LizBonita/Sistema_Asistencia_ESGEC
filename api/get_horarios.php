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
    
    // Determinar nombres de FK (id_grupo vs grupo_id, id_materia vs materia_id, etc.)
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
    
    if ($colGrupo) {
        $selectParts[] = "h.$colGrupo AS id_grupo";
        $selectParts[] = "g.nombre AS nombre_grupo";
        $joinParts[] = "LEFT JOIN grupos g ON h.$colGrupo = g.id";
    }
    if ($colMateria) {
        $selectParts[] = "h.$colMateria AS id_materia";
        $selectParts[] = "mat.nombre AS nombre_materia";
        $joinParts[] = "LEFT JOIN materias mat ON h.$colMateria = mat.id";
    }
    if ($colMaestro) {
        $selectParts[] = "h.$colMaestro AS id_maestro";
    }
    
    $sql = "SELECT " . implode(", ", $selectParts) . " FROM horarios h " . implode(" ", $joinParts);
    if ($colDia) $sql .= " ORDER BY h.$colDia, h.hora_inicio ASC";
    
    $stmt = $db->query($sql);
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($horarios, JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}