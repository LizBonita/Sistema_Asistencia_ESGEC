<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
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
    include_once '../models/Usuario.php';
    include_once '../models/Rol.php';

    $database = new Database();
    $db = $database->getConnection();
    $usuario = new Usuario($db);
    $rol = new Rol($db);

    $stmt_roles = $rol->readAll();

    $usuario_id = $_GET['id'] ?? null;

    if($usuario_id) {
        $usuario->id = $usuario_id;
        if(!$usuario->readOne()){
            header('Location: gestion_usuarios.php?message=' . urlencode('Usuario no encontrado.'));
            exit();
        }
    } else {
        header('Location: gestion_usuarios.php');
        exit();
    }
    ?>
    <header>
        <h1>Editar Usuario: <?php echo htmlspecialchars($usuario->nombre_completo); ?></h1>
        <nav>
            <a href="gestion_usuarios.php">Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/ActualizarUsuarioController.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $usuario->id; ?>">

            <label for="nombre_completo">Nombre Completo:</label>
            <input type="text" name="nombre_completo" id="nombre_completo" value="<?php echo htmlspecialchars($usuario->nombre_completo); ?>" required>

            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" name="usuario" id="usuario" value="<?php echo htmlspecialchars($usuario->usuario); ?>" required>

            <!-- No se edita la contraseña aquí por simplicidad y seguridad -->
            <p><em>Para cambiar la contraseña, se requiere una funcionalidad específica (no implementada aquí).</em></p>

            <label for="rol_id">Rol:</label>
            <select name="rol_id" id="rol_id" required>
                <option value="">Seleccionar Rol...</option>
                <?php
                $stmt_roles->execute(); // Volver a ejecutar para iterar
                while ($row = $stmt_roles->fetch(PDO::FETCH_ASSOC)){
                    $selected = $row['id'] == $usuario->rol_id ? 'selected' : '';
                    echo "<option value='{$row['id']}' {$selected}>{$row['nombre']}</option>";
                }
                ?>
            </select>

            <button type="submit">Actualizar Usuario</button>
        </form>
    </main>
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>