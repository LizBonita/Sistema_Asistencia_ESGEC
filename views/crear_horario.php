<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador') {
    die("Acceso denegado.");
}

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener maestro preseleccionado (si viene de ?maestro_id=1)
$maestro_id = $_GET['maestro_id'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Horario - Sistema de Asistencia</title>

    <!-- Fuentes y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* Estilos básicos */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f3f6f9;
            color: #0b1220;
            margin: 0;
            padding: 0;
        }

        form {
            max-width: 500px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        select, input[type="time"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 16px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
        }

        button {
            background: #009B48;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        button:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>

<h1 style="text-align: center;">Crear Nuevo Horario</h1>

<form action="../controllers/CrearHorarioController.php" method="POST">
    <label for="maestro_id">Maestro:</label>
    <select name="maestro_id" id="maestro_id" required>
        <option value="">Selecciona un maestro</option>
        <?php
        $query_maestros = "SELECT m.id, u.nombre_completo FROM maestros m LEFT JOIN usuarios u ON m.usuario_id = u.id";
        $stmt_maestros = $db->prepare($query_maestros);
        $stmt_maestros->execute();
        while ($row_maestro = $stmt_maestros->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($maestro_id == $row_maestro['id']) ? ' selected' : '';
            echo "<option value='" . htmlspecialchars($row_maestro['id']) . "'$selected>" . htmlspecialchars($row_maestro['nombre_completo']) . "</option>";
        }
        ?>
    </select>

    <label for="materia_id">Materia:</label>
    <select name="materia_id" id="materia_id" required>
        <option value="">Selecciona una materia</option>
        <?php
        $query_materias = "SELECT id, nombre FROM materias";
        $stmt_materias = $db->prepare($query_materias);
        $stmt_materias->execute();
        while ($row_materia = $stmt_materias->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . htmlspecialchars($row_materia['id']) . "'>" . htmlspecialchars($row_materia['nombre']) . "</option>";
        }
        ?>
    </select>

    <label for="grupo_id">Grupo:</label>
    <select name="grupo_id" id="grupo_id" required>
        <option value="">Selecciona un grupo</option>
        <?php
        $query_grupos = "SELECT id, nombre FROM grupos";
        $stmt_grupos = $db->prepare($query_grupos);
        $stmt_grupos->execute();
        while ($row_grupo = $stmt_grupos->fetch(PDO::FETCH_ASSOC)) {
            echo "<option value='" . htmlspecialchars($row_grupo['id']) . "'>" . htmlspecialchars($row_grupo['nombre']) . "</option>";
        }
        ?>
    </select>

    <label for="dia_semana">Día de la Semana:</label>
    <select name="dia_semana" id="dia_semana" required>
        <option value="">Selecciona un día</option>
        <option value="Lunes">Lunes</option>
        <option value="Martes">Martes</option>
        <option value="Miércoles">Miércoles</option>
        <option value="Jueves">Jueves</option>
        <option value="Viernes">Viernes</option>
    </select>

    <label for="hora_inicio">Hora de Inicio:</label>
    <input type="time" name="hora_inicio" id="hora_inicio" required>

    <label for="hora_fin">Hora de Fin:</label>
    <input type="time" name="hora_fin" id="hora_fin" required>

    <label for="tolerancia_entrada">Tolerancia de Entrada (minutos):</label>
    <input type="number" name="tolerancia_entrada" id="tolerancia_entrada" required placeholder="Ej. 10">

    <label for="limite_retardo">Límite de Retardo (minutos):</label>
    <input type="number" name="limite_retardo" id="limite_retardo" required placeholder="Ej. 15">

    <button type="submit"><i class="fas fa-clock"></i> Agregar Horario</button>
</form>

</body>
</html>