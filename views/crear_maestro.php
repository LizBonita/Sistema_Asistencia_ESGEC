<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Maestro</title>
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

    // Obtener lista de usuarios disponibles
    $stmt_usuarios = $maestro->getUsuariosDisponiblesParaMaestro();
    ?>
    <header>
        <h1>Agregar Nuevo Maestro</h1>
        <nav>
            <a href="gestion_maestros.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/CrearMaestroController.php" method="POST">
            <label for="usuario_id">Usuario Asociado:</label>
            <select name="usuario_id" id="usuario_id" required>
                <option value="">Seleccionar Usuario...</option>
                <?php
                while ($row = $stmt_usuarios->fetch(PDO::FETCH_ASSOC)){
                    echo "<option value='{$row['id']}'>{$row['nombre_completo']} ({$row['usuario']}) - {$row['rol_nombre']}</option>";
                }
                ?>
            </select>

            <label for="tipo_contrato">Tipo de Contrato:</label>
            <select name="tipo_contrato" id="tipo_contrato" required>
                <option value="">Seleccionar...</option>
                <option value="tiempo_completo">Tiempo Completo</option>
                <option value="por_horas">Por Horas</option>
            </select>

            <button type="submit">Agregar Maestro</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>