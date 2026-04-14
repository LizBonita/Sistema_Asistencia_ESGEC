<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Horario</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
    if ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina') {
        die("Acceso denegado.");
    }

    include_once '../config/database.php';
    include_once '../models/Horario.php';
    include_once '../models/Grupo.php';

    $database = new Database();
    $db = $database->getConnection();
    $horario = new Horario($db);
    $grupo = new Grupo($db);

    $horario_id = $_GET['id'] ?? null;

    if ($horario_id) {
        $horario->id = $horario_id; // Usa $horario->id, no $horario->id_horario
        if (!$horario->readOne()) {
            header('Location: gestion_horarios_por_maestro.php?message=' . urlencode('Horario no encontrado.'));
            exit();
        }
    } else {
        header('Location: gestion_horarios_por_maestro.php');
        exit();
    }

    $stmt_grupos = $grupo->read(); // Ahora funciona correctamente
    $dias_semana = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
    ?>
    <header>
        <h1>Editar Horario</h1>
        <nav>
            <a href="gestion_horarios_por_maestro.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/ActualizarHorarioController.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $horario->id; ?>">

            <label for="id_grupo">Grupo:</label>
            <select name="id_grupo" id="id_grupo" required>
                <option value="">Seleccionar Grupo...</option>
                <?php while ($row = $stmt_grupos->fetch(PDO::FETCH_ASSOC)): ?>
                    <option value="<?php echo $row['id']; ?>" <?php echo $horario->grupo_id == $row['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label for="dia_semana">Día de la Semana:</label>
            <select name="dia_semana" id="dia_semana" required>
                <option value="">Seleccionar Día...</option>
                <?php foreach ($dias_semana as $dia): ?>
                    <option value="<?php echo $dia; ?>" <?php echo $horario->dia_semana == $dia ? 'selected' : ''; ?>>
                        <?php echo $dia; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="hora_inicio">Hora de Inicio:</label>
            <input type="time" name="hora_inicio" id="hora_inicio" value="<?php echo htmlspecialchars($horario->hora_inicio); ?>" required>

            <label for="hora_fin">Hora de Fin:</label>
            <input type="time" name="hora_fin" id="hora_fin" value="<?php echo htmlspecialchars($horario->hora_fin); ?>" required>

            <button type="submit">Actualizar Horario</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>