<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Asistencia</title>
    <!-- Puedes enlazar tu archivo CSS aquí -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        <form action="../controllers/AuthController.php" method="POST"> <!-- El form enviará a un controlador -->
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>
    </div>

    <!-- Puedes enlazar tu archivo JS aquí -->
    <script src="../assets/js/script.js"></script>
</body>
</html>