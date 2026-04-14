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
include_once '../models/Horario.php';

$database = new Database();
$db = $database->getConnection();
$horario_model = new Horario($db);

$stmt = $horario_model->read();
$num = $stmt->rowCount();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Horarios - Sistema de Asistencia</title>

    <!-- Fuentes y estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        /* =========================
           PALETA / BASE
        ========================= */
        :root {
            --verde: #009B48;
            --azul: #0E4D92;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
        }

        table th,
        table td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        table th {
            background: rgba(14, 77, 146, .12);
            color: var(--azul2);
            font-weight: 700;
            font-size: 0.95rem;
        }

        table td {
            color: var(--text);
            font-size: 0.9rem;
        }

        table tr:hover {
            background: rgba(14, 77, 146, .04);
            transition: background 0.2s ease;
        }

        table a {
            color: var(--azul2);
            text-decoration: none;
            font-weight: 700;
            transition: color 0.2s ease;
        }

        table a:hover {
            color: var(--verde);
        }

        /* Botón "Agregar Nuevo Horario" */
        .btn-create-user {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 18px;
            border-radius: 14px;
            background: var(--verde);
            color: #fff;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px rgba(0, 0, 0, .12);
        }

        .btn-create-user:hover {
            background: #008037;
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(0, 0, 0, .16);
        }

        /* =========================
           VENTANA MODAL
        ========================= */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 1000;
            background: rgba(0, 0, 0, .55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            padding: 18px;
        }

        .modal-content {
            background: rgba(255, 255, 255, .92);
            margin: 10vh auto 0;
            padding: 0;
            border: 1px solid rgba(14, 77, 146, .18);
            border-radius: 20px;
            width: 92%;
            max-width: 520px;
            box-shadow: var(--shadow);
            overflow: hidden;
            position: relative;
            animation: pop .18s ease;
        }

        @keyframes pop {
            from { transform: translateY(10px); opacity: .85; }
            to { transform: translateY(0); opacity: 1; }
        }

        .modal-header {
            background: linear-gradient(135deg, var(--azul), var(--verde));
            color: #fff;
            padding: 16px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .close {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 24px;
            font-weight: 900;
            cursor: pointer;
            background: rgba(255, 255, 255, .14);
            border: 1px solid rgba(255, 255, 255, .22);
            transition: transform .15s ease, background .15s ease;
            line-height: 1;
        }

        .close:hover {
            transform: scale(1.03);
            background: rgba(255, 255, 255, .20);
        }

        .modal-body {
            padding: 16px 16px 24px;
            background:
                radial-gradient(700px 240px at 15% 0%, rgba(0, 155, 72, .10), transparent 60%),
                radial-gradient(700px 240px at 85% 0%, rgba(14, 77, 146, .10), transparent 60%),
                linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(255, 255, 255, .85));
            max-height: calc(85vh - 120px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .modal-body form {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .modal-body label {
            font-weight: 800;
            color: var(--azul2);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: .95rem;
        }

        .modal-body input[type="text"],
        .modal-body select,
        .modal-body input[type="time"],
        .modal-body input[type="number"] {
            padding: 12px 14px;
            border: 1px solid rgba(14, 77, 146, .18);
            border-radius: 14px;
            font-size: 1rem;
            outline: none;
            background: rgba(255, 255, 255, .92);
            transition: box-shadow .15s ease, border-color .15s ease;
        }

        .modal-body input:focus,
        .modal-body select:focus {
            border-color: rgba(14, 77, 146, .35);
            box-shadow: 0 0 0 4px rgba(14, 77, 146, .16);
        }

        .modal-body button {
            border: none;
            cursor: pointer;
            padding: 12px 14px;
            border-radius: 16px;
            font-weight: 900;
            color: #fff;
            background: linear-gradient(135deg, var(--verde), var(--azul));
            box-shadow: 0 14px 28px rgba(0, 0, 0, .16);
            transition: transform .18s ease, filter .18s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .modal-body button:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
        }

        .modal-message {
            margin-top: 10px;
            padding: 12px 12px;
            border-radius: 14px;
            font-weight: 800;
            font-size: .95rem;
            color: #7f0e1c;
            background: rgba(255, 234, 236, .90);
            border: 1px solid rgba(127, 14, 28, .18);
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

            table th,
            table td {
                padding: 10px 12px;
                font-size: 0.85rem;
            }

            .btn-create-user {
                width: 100%;
                text-align: center;
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
        <h1>Gestionar Horarios</h1>
        <nav>
            <a href="inicio.php"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        </nav>
    </header>

    <main>
        <?php
        // Mostrar mensaje si existe
        if (isset($_GET['message'])) {
            echo '<p style="color: green;">' . htmlspecialchars($_GET['message']) . '</p>';
        }
        ?>

        <?php
        if ($num > 0) {
            echo "<table>";
            echo "<tr>
                    <th>ID</th>
                    <th>Maestro</th>
                    <th>Materia</th>
                    <th>Grupo</th>
                    <th>Día</th>
                    <th>Hora Inicio</th>
                    <th>Hora Fin</th>
                    <th>Tolerancia</th>
                    <th>Límite Retardo</th>
                    <th>Acciones</th>
                  </tr>";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $id = $row['id'];
                $nombre_maestro = $row['nombre_maestro'];
                $nombre_materia = $row['nombre_materia'];
                $nombre_grupo = $row['nombre_grupo'];
                $dia_semana = $row['dia_semana'];
                $hora_inicio = $row['hora_inicio'];
                $hora_fin = $row['hora_fin'];
                $tolerancia_entrada = $row['tolerancia_entrada'];
                $limite_retardo = $row['limite_retardo'];

                echo "<tr>";
                echo "<td>" . htmlspecialchars($id) . "</td>";
                echo "<td>" . htmlspecialchars($nombre_maestro) . "</td>";
                echo "<td>" . htmlspecialchars($nombre_materia) . "</td>";
                echo "<td>" . htmlspecialchars($nombre_grupo) . "</td>";
                echo "<td>" . htmlspecialchars($dia_semana) . "</td>";
                echo "<td>" . htmlspecialchars($hora_inicio) . "</td>";
                echo "<td>" . htmlspecialchars($hora_fin) . "</td>";
                echo "<td>" . htmlspecialchars($tolerancia_entrada) . " min</td>";
                echo "<td>" . htmlspecialchars($limite_retardo) . " min</td>";
                echo "<td>
                        <a href='editar_horario.php?id=" . htmlspecialchars($id) . "'><i class='fas fa-edit'></i> Editar</a> |
                        <a href='eliminar_horario.php?id=" . htmlspecialchars($id) . "' onclick=\"return confirm('¿Estás seguro de eliminar este horario?');\">
                            <i class='fas fa-trash'></i> Eliminar
                        </a>
                      </td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No se encontraron horarios.</p>";
        }
        ?>
        <br>
        <button type="button" class="btn-create-user" id="openModal"><i class="fas fa-plus"></i> Agregar Nuevo Horario</button>
    </main>

    <!-- Ventana Modal -->
    <div id="createHorarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clock"></i> Agregar Nuevo Horario</h2>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="createHorarioForm" method="POST" action="../controllers/CrearHorarioController.php">
                    <label for="maestro_id">Maestro:</label>
                    <select name="maestro_id" id="maestro_id" required>
                        <option value="">Selecciona un maestro</option>
                        <?php
                        $query_maestros = "SELECT m.id, u.nombre_completo FROM maestros m LEFT JOIN usuarios u ON m.usuario_id = u.id";
                        $stmt_maestros = $db->prepare($query_maestros);
                        $stmt_maestros->execute();
                        while ($row_maestro = $stmt_maestros->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row_maestro['id']) . "'>" . htmlspecialchars($row_maestro['nombre_completo']) . "</option>";
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

                <div id="modalMessage" class="modal-message" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('createHorarioModal');
    const openModalBtn = document.getElementById('openModal');
    const closeBtn = document.querySelector('.close');
    const form = document.getElementById('createHorarioForm');
    const modalMessage = document.getElementById('modalMessage');

    // Abrir modal
    openModalBtn.onclick = () => modal.style.display = 'block';

    // Cerrar modal
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => {
        if (e.target === modal) modal.style.display = 'none';
    };

    // Enviar formulario (redirección directa, sin iframe)
    form.onsubmit = function (e) {
        e.preventDefault();
        
        // Mostrar estado de carga
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        submitBtn.disabled = true;

        // Enviar directamente (sin iframe)
        form.action = '../controllers/CrearHorarioController.php';
        form.method = 'POST';
        form.submit();
    };
});
</script>

</body>
</html>