<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Asistencia</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="dashboard-container">
    <?php
    session_start();
    if(!isset($_SESSION['user_id'])){
        header('Location: login.php');
        exit();
    }
    ?>
    <header>
        <h1><i class="fas fa-chalkboard-teacher"></i> Bienvenido, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?> (<?php echo htmlspecialchars($_SESSION['user_rol_nombre']); ?>)</h1>
        <nav>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </nav>
    </header>

    <main>
        <h2><i class="fas fa-tachometer-alt"></i> Panel de Control</h2>
        <ul>
            <li><a href="gestion_usuarios.php"><i class="fas fa-users"></i> Gestionar Usuarios</a></li>
            <li><a href="gestion_maestros.php"><i class="fas fa-user-graduate"></i> Gestionar Maestros</a></li>
            <li><a href="gestion_materias.php"><i class="fas fa-book"></i> Gestionar Materias</a></li>
            <li><a href="gestion_grupos.php"><i class="fas fa-users-class"></i> Gestionar Grupos</a></li>
            <li><a href="gestion_horarios.php"><i class="fas fa-calendar-alt"></i> Gestionar Horarios</a></li>
            <li><a href="asistencias_diarias.php"><i class="fas fa-list-check"></i> Ver Asistencias Diarias</a></li>
            <li><a href="reporte_quincenal.php"><i class="fas fa-chart-bar"></i> Reporte Quincenal</a></li>
            <li><a href="incidencias_pendientes.php"><i class="fas fa-exclamation-triangle"></i> Justificaciones Pendientes</a></li>
        </ul>
    </main>
    <!-- <footer>
        <p>&copy; <?php echo date('Y'); ?> Sistema de Asistencia Escolar. Todos los derechos reservados.</p>
    </footer> -->
</div>

<script src="../assets/js/script.js"></script>
</body>
</html>