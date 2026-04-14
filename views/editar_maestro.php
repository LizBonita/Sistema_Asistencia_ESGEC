<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Maestro</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="container">
    <?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header('Location: login.php');
        exit();
    }
    if($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador'){
         die("Acceso denegado.");
    }

    include_once '../config/database.php';
    include_once '../models/Maestro.php';

    $database = new Database();
    $db = $database->getConnection();
    $maestro = new Maestro($db);

    // Obtener ID del maestro a editar desde la URL
    $maestro_id = $_GET['id'] ?? null;

    if($maestro_id) {
        $maestro->id = $maestro_id;
        if(!$maestro->readOne()){ // Carga los datos del maestro
            header('Location: gestion_maestros.php?message=' . urlencode('Maestro no encontrado.'));
            exit();
        }
    } else {
        header('Location: gestion_maestros.php');
        exit();
    }

    ?>
    <header>
        <h1>Editar Maestro: <?php echo htmlspecialchars($maestro->nombre_completo_usuario); ?></h1>
        <nav>
            <a href="gestion_maestros.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/ActualizarMaestroController.php" method="POST">
            <!-- Campo oculto para enviar el ID -->
            <input type="hidden" name="id" value="<?php echo $maestro->id; ?>">
            
            <!-- Mostrar info del usuario (solo lectura) -->
            <p><strong>Nombre Completo:</strong> <?php echo htmlspecialchars($maestro->nombre_completo_usuario); ?></p>
            <p><strong>Usuario:</strong> <?php echo htmlspecialchars($maestro->nombre_usuario); ?></p>
            <p><strong>Rol Actual:</strong> <?php echo htmlspecialchars($maestro->rol_nombre_usuario); ?></p>

            <label for="tipo_contrato">Nuevo Tipo de Contrato:</label>
            <select name="tipo_contrato" id="tipo_contrato" required>
                <option value="">Seleccionar...</option>
                <option value="tiempo_completo" <?php echo $maestro->tipo_contrato == 'tiempo_completo' ? 'selected' : ''; ?>>Tiempo Completo</option>
                <option value="por_horas" <?php echo $maestro->tipo_contrato == 'por_horas' ? 'selected' : ''; ?>>Por Horas</option>
            </select>

            <button type="submit">Actualizar Maestro</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>