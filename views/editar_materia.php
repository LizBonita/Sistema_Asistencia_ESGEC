<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Materia</title>
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
    include_once '../models/Materia.php';

    $database = new Database();
    $db = $database->getConnection();
    $materia = new Materia($db);

    $materia_id = $_GET['id'] ?? null;

    if ($materia_id) {
        $materia->id_materia = $materia_id;
        if (!$materia->readOne()) {
            header('Location: gestion_materias.php?message=' . urlencode('Materia no encontrada.'));
            exit();
        }
    } else {
        header('Location: gestion_materias.php');
        exit();
    }
    ?>
    <header>
        <h1>Editar Materia: <?php echo htmlspecialchars($materia->nombre_materia); ?></h1>
        <nav>
            <a href="gestion_materias.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/ActualizarMateriaController.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $materia->id_materia; ?>">

            <label for="nombre_materia">Nombre de la Materia:</label>
            <input type="text" name="nombre_materia" id="nombre_materia" value="<?php echo htmlspecialchars($materia->nombre_materia); ?>" required>

            <button type="submit">Actualizar Materia</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>