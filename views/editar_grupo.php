<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo</title>
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
    include_once '../models/Grupo.php';

    $database = new Database();
    $db = $database->getConnection();
    $grupo = new Grupo($db);

    $grupo_id = $_GET['id'] ?? null;

    if ($grupo_id) {
        $grupo->id = $grupo_id; // Usa $grupo->id, no $grupo->id_grupo
        if (!$grupo->readOne()) {
            header('Location: gestion_grupos.php?message=' . urlencode('Grupo no encontrado.'));
            exit();
        }
    } else {
        header('Location: gestion_grupos.php');
        exit();
    }
    ?>
    <header>
        <h1>Editar Grupo: <?php echo htmlspecialchars($grupo->nombre); ?></h1>
        <nav>
            <a href="gestion_grupos.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/ActualizarGrupoController.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $grupo->id; ?>">

            <label for="nombre_grupo">Nombre del Grupo:</label>
            <input type="text" name="nombre_grupo" id="nombre_grupo" value="<?php echo htmlspecialchars($grupo->nombre); ?>" required>

            <button type="submit">Actualizar Grupo</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>