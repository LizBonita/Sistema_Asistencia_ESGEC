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
include_once '../models/Rol.php'; // Necesitamos obtener los roles

$database = new Database();
$db = $database->getConnection();
$rol = new Rol($db);

$stmt_roles = $rol->readAll(); // Asumiendo que creas este modelo y método
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Usuario - Sistema de Asistencia</title>

    <!-- Fuentes y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* =========================
           PALETA / BASE
        ========================= */
        :root {
            --verde: #009B48; /* Verde bandera */
            --azul: #0E4D92; /* Azul marino */
            --azul2: #0A3A6F;

            --bg: #f3f6f9;
            --surface: #ffffff;
            --text: #0b1220;
            --muted: #5f6b7a;

            --border: rgba(14, 77, 146, .14);
            --shadow: 0 16px 38px rgba(0, 0, 0, .14);
            --shadow-soft: 0 10px 26px rgba(0, 0, 0, .08);

            --radius: 18px;
            --radius-sm: 12px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Roboto', sans-serif;
            color: var(--text);
            background:
                radial-gradient(900px 480px at 10% -10%, rgba(0, 155, 72, .16), transparent 55%),
                radial-gradient(900px 480px at 90% -10%, rgba(14, 77, 146, .18), transparent 60%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 60%, #eef2f6 100%);
            line-height: 1.6;
        }

        /* =========================
           BANNER / NAV
        ========================= */
        .inicio-nav-bar {
            position: sticky;
            top: 0;
            z-index: 50;

            background: linear-gradient(135deg, rgba(14, 77, 146, .96), rgba(0, 155, 72, .90));
            border-bottom: 1px solid rgba(255, 255, 255, .18);
            box-shadow: 0 12px 26px rgba(0, 0, 0, .18);

            padding: 12px 22px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .inicio-nav-bar-logos {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .18);
            box-shadow: 0 12px 26px rgba(0, 0, 0, .10);
        }

        .inicio-nav-bar-logo {
            height: 46px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 6px 10px rgba(0, 0, 0, .18));
        }

        .welcome-msg {
            color: #fff;
            font-weight: 600;
            font-size: .95rem;
            margin-right: auto;
            padding: 10px 12px;
            border-radius: 999px;
            background: rgba(255, 255, 255, .12);
            border: 1px solid rgba(255, 255, 255, .18);
            box-shadow: 0 12px 26px rgba(0, 0, 0, .10);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }

        .btn-logout {
            border: none;
            cursor: pointer;
            text-decoration: none;

            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;

            padding: 10px 14px;
            border-radius: 999px;
            font-weight: 700;
            transition: transform .18s ease, box-shadow .18s ease, background .18s ease, color .18s ease;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .18);
            white-space: nowrap;

            background: rgba(255, 255, 255, .12);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, .22);
        }

        .btn-logout:hover {
            transform: translateY(-1px);
            background: rgba(255, 255, 255, .18);
        }

        /* =========================
           CONTENIDO PRINCIPAL
        ========================= */
        .container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            margin: 24px 22px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        header h1 {
            font-size: 2rem;
            font-weight: 900;
            color: var(--azul2);
            letter-spacing: 0.5px;
        }

        header nav a {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 14px;
            background: rgba(255, 255, 255, .92);
            color: var(--azul2);
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .12);
        }

        header nav a:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .16);
        }

        main {
            margin: 24px 22px;
            padding: 24px;
            background: var(--surface);
            border-radius: var(--radius);
            box-shadow: var(--shadow-soft);
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        label {
            font-weight: 600;
            color: var(--azul2);
        }

        input,
        select,
        button {
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        input:focus,
        select:focus {
            border-color: var(--azul);
            box-shadow: 0 0 0 4px rgba(14, 77, 146, .16);
        }

        button {
            background: var(--verde);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .12);
        }

        button:hover {
            background: #008037;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .16);
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 768px) {
            header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }

            header h1 {
                font-size: 1.6rem;
            }

            input,
            select,
            button {
                padding: 10px 12px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Barra de Navegación Superior -->
    <nav class="inicio-nav-bar">
        <div class="inicio-nav-bar-logos">
            <img src="../assets/img/logo_secretaria.png" alt="Logo Secretaría de Educación" class="inicio-nav-bar-logo">
            <img src="../assets/img/logo_escuelaaa.png" alt="Logo Escuela Secundaria" class="inicio-nav-bar-logo">
        </div>

        <div class="welcome-msg">
            <i class="fa-solid fa-circle-check"></i>
            Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?>
            (<span class="user-role"><?php echo htmlspecialchars($_SESSION['user_rol_nombre']); ?></span>)
        </div>

        <!-- Botón Cerrar Sesión -->
        <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </nav>

    <!-- Contenido principal -->
    <header>
        <h1>Agregar Nuevo Usuario</h1>
        <nav>
            <a href="gestion_usuarios.php"><i class="fas fa-arrow-left"></i> Volver a la Lista</a>
        </nav>
    </header>

    <main>
        <form action="../controllers/CrearUsuarioController.php" method="POST">
            <label for="nombre_completo">Nombre Completo:</label>
            <input type="text" name="nombre_completo" id="nombre_completo" required placeholder="Ej. Juan Pérez López">

            <label for="usuario">Nombre de Usuario:</label>
            <input type="text" name="usuario" id="usuario" required placeholder="Ej. juanperez">

            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required placeholder="********">

            <label for="rol_id">Rol:</label>
            <select name="rol_id" id="rol_id" required>
                <option value="" disabled selected>Seleccionar Rol...</option>
                <?php
                while ($row = $stmt_roles->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                }
                ?>
            </select>

            <button type="submit">Agregar Usuario</button>
        </form>
    </main>
</div>

</body>
</html>