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
include_once '../models/Asistencia.php';

$database = new Database();
$db = $database->getConnection();
$asistencia = new Asistencia($db);

// Obtener la fecha del GET o usar hoy por defecto
$selected_date = $_GET['date'] ?? date('Y-m-d');
$stmt = $asistencia->readByDate($selected_date);
$asistencias = $stmt->fetchAll(PDO::FETCH_ASSOC);
$num = count($asistencias);

function obtenerIniciales(string $nombre): string
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return 'AS';
    }

    $partes = preg_split('/\s+/', $nombre);
    $iniciales = '';

    foreach ($partes as $parte) {
        if ($parte !== '') {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1, 'UTF-8'), 'UTF-8');
        }

        if (mb_strlen($iniciales, 'UTF-8') >= 2) {
            break;
        }
    }

    return $iniciales !== '' ? $iniciales : 'AS';
}

function formatearFechaBonita(string $fecha): string
{
    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
}

function claseEstado(?string $estado): string
{
    $estado = mb_strtolower(trim((string)$estado), 'UTF-8');

    if (strpos($estado, 'puntual') !== false) {
        return 'estado-puntual';
    }

    if (strpos($estado, 'retraso') !== false || strpos($estado, 'retardo') !== false) {
        return 'estado-retraso';
    }

    return 'estado-falta';
}

function iconoEstado(?string $estado): string
{
    $estado = mb_strtolower(trim((string)$estado), 'UTF-8');

    if (strpos($estado, 'puntual') !== false) {
        return 'check-circle';
    }

    if (strpos($estado, 'retraso') !== false || strpos($estado, 'retardo') !== false) {
        return 'exclamation-circle';
    }

    return 'times-circle';
}

$totalPuntuales = 0;
$totalRetrasos = 0;
$totalFaltas = 0;

foreach ($asistencias as $registro) {
    $estadoEntrada = mb_strtolower(trim((string)($registro['estado_entrada'] ?? '')), 'UTF-8');

    if (strpos($estadoEntrada, 'puntual') !== false) {
        $totalPuntuales++;
    } elseif (strpos($estadoEntrada, 'retraso') !== false || strpos($estadoEntrada, 'retardo') !== false) {
        $totalRetrasos++;
    } else {
        $totalFaltas++;
    }
}

$nombreSesion = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$rolSesion = htmlspecialchars($_SESSION['user_rol_nombre'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8');
$inicialesSesion = htmlspecialchars(obtenerIniciales($_SESSION['user_nombre'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$fechaBonita = formatearFechaBonita($selected_date);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencias Diarias - Sistema de Asistencia Escolar</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --color-principal: #1B396A;
            --color-principal-2: #245f9f;
            --color-secundario: #25b05f;
            --color-secundario-2: #28c07a;

            --color-fondo: #eef3f8;
            --color-superficie: #ffffff;
            --color-superficie-2: rgba(255,255,255,0.86);
            --color-borde-suave: rgba(27,57,106,0.12);

            --color-texto: #0f172a;
            --color-texto-suave: #5f6f87;
            --color-blanco: #ffffff;

            --color-success: #22c55e;
            --color-danger: #dc2626;
            --color-warning: #f59e0b;

            --sombra-sm: 0 10px 24px rgba(15, 23, 42, 0.10);
            --sombra-md: 0 18px 40px rgba(15, 23, 42, 0.12);
            --sombra-lg: 0 22px 60px rgba(15, 23, 42, 0.18);

            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 18px;
            --radius-sm: 14px;
            --radius-xs: 12px;
        }

        body.dark-mode {
            --color-fondo: rgb(19, 16, 34);
            --color-superficie: rgba(30, 28, 48, 0.95);
            --color-superficie-2: rgba(38, 36, 58, 0.92);
            --color-borde-suave: rgba(255,255,255,0.08);
            --color-texto: #f3f5f8;
            --color-texto-suave: #b8c2d1;
            --sombra-sm: 0 10px 24px rgba(0, 0, 0, 0.28);
            --sombra-md: 0 18px 40px rgba(0, 0, 0, 0.34);
            --sombra-lg: 0 22px 60px rgba(0, 0, 0, 0.42);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background:
                radial-gradient(900px 420px at 0% 0%, rgba(39, 132, 211, 0.12), transparent 55%),
                radial-gradient(900px 420px at 100% 0%, rgba(34, 197, 94, 0.10), transparent 55%),
                var(--color-fondo);
            color: var(--color-texto);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button,
        input,
        select {
            font-family: inherit;
        }

        .page-wrapper {
            width: min(1500px, calc(100% - 32px));
            margin: 22px auto 0;
            padding: 0 0 28px;
        }

        /* =========================
           HEADER
        ========================= */
        .top-header {
            width: 100%;
            margin: 0;
            border-radius: 0 0 var(--radius-xl) var(--radius-xl);
            background: linear-gradient(135deg, #245f9f 0%, #1B396A 25%, #228e8e 65%, #25b05f 100%);
            box-shadow: var(--sombra-lg);
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.12);
        }

        .top-header-inner {
            width: min(1500px, calc(100% - 32px));
            margin: 0 auto;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px;
            padding: 26px 0 18px;
        }

        .brand-area {
            display: flex;
            flex-direction: column;
            gap: 14px;
            min-width: 0;
            flex: 1;
        }

        .brand-main {
            display: flex;
            align-items: center;
            gap: 16px;
            min-width: 0;
        }

        .brand-logos {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 20px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.14);
            box-shadow: var(--sombra-sm);
            flex-shrink: 0;
        }

        .brand-logos img {
            height: 56px;
            width: auto;
            object-fit: contain;
        }

        .brand-text {
            min-width: 0;
            color: var(--color-blanco);
        }

        .brand-text h1 {
            font-size: clamp(1.45rem, 2vw, 2rem);
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 8px;
        }

        .brand-text p {
            font-size: clamp(.92rem, 1.05vw, 1.15rem);
            font-weight: 700;
            opacity: .95;
        }

        .welcome-pill {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            max-width: 100%;
            padding: 12px 16px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.18);
            color: var(--color-blanco);
            font-weight: 700;
            box-shadow: var(--sombra-sm);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }

        .welcome-pill span {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .welcome-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(27,176,109,0.9), rgba(27,57,106,0.88));
            color: #fff;
            font-weight: 900;
            font-size: 1rem;
            flex-shrink: 0;
            border: 1px solid rgba(255,255,255,0.16);
        }

        .header-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            align-items: center;
            gap: 14px;
            width: min(100%, 860px);
            flex-shrink: 0;
        }

        .header-pill,
        .header-link,
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 54px;
            padding: 12px 20px;
            border-radius: 999px;
            font-size: 1rem;
            font-weight: 800;
            color: var(--color-blanco);
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: var(--sombra-sm);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            transition: .18s ease;
            white-space: nowrap;
        }

        .header-link:hover,
        .theme-toggle:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.18);
        }

        .header-pill .status-dot {
            width: 11px;
            height: 11px;
            border-radius: 50%;
            background: var(--color-success);
            box-shadow: 0 0 0 6px rgba(34,197,94,0.18);
            flex-shrink: 0;
        }

        .theme-toggle {
            cursor: pointer;
        }

        /* =========================
           CONTENIDO
        ========================= */
        .content-section {
            margin-top: 24px;
        }

        .main-card {
            background: var(--color-superficie);
            border-radius: var(--radius-xl);
            box-shadow: var(--sombra-md);
            border: 1px solid var(--color-borde-suave);
            overflow: hidden;
        }

        .main-card-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            padding: 24px 24px 18px;
            border-bottom: 1px solid var(--color-borde-suave);
            flex-wrap: wrap;
        }

        .main-card-title h2 {
            color: var(--color-principal);
            font-size: clamp(1.5rem, 2vw, 2rem);
            font-weight: 900;
            margin-bottom: 6px;
        }

        body.dark-mode .main-card-title h2 {
            color: #dce8ff;
        }

        .main-card-title p {
            color: var(--color-texto-suave);
            font-size: .98rem;
            font-weight: 500;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            padding: 20px 24px 10px;
        }

        .summary-item {
            background: linear-gradient(135deg, rgba(27,57,106,0.96), rgba(36,77,143,0.88));
            color: #fff;
            border-radius: 22px;
            padding: 18px;
            box-shadow: var(--sombra-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .summary-info small {
            display: block;
            font-size: .92rem;
            opacity: .9;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .summary-info strong {
            display: block;
            font-size: 1.7rem;
            line-height: 1;
            font-weight: 900;
        }

        .summary-icon {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: rgba(255,255,255,0.14);
            font-size: 1.25rem;
        }

        .tools-row {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
            padding: 16px 24px 0;
            flex-wrap: wrap;
        }

        .tools-left,
        .tools-right {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        .fecha-form-card {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            padding: 16px;
            background: rgba(27,57,106,0.04);
            border: 1px solid var(--color-borde-suave);
            border-radius: 18px;
        }

        .fecha-form-card label {
            font-weight: 800;
            color: var(--color-principal);
            font-size: .92rem;
        }

        .fecha-form-card input[type="date"] {
            height: 48px;
            padding: 0 14px;
            border-radius: 14px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            outline: none;
        }

        body.dark-mode .fecha-form-card input[type="date"] {
            background: var(--color-superficie-2);
        }

        .btn-filter {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            min-height: 48px;
            padding: 12px 18px;
            border-radius: 16px;
            font-weight: 800;
            transition: .18s ease;
            border: none;
            cursor: pointer;
            background: linear-gradient(135deg, var(--color-secundario), var(--color-principal));
            color: #fff;
            box-shadow: var(--sombra-sm);
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            filter: brightness(1.03);
        }

        .search-box {
            position: relative;
            width: min(100%, 380px);
        }

        .search-box i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--color-texto-suave);
            font-size: .95rem;
        }

        .search-box input {
            width: 100%;
            height: 48px;
            border-radius: 16px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            padding: 0 14px 0 42px;
            outline: none;
            transition: .18s ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.35);
        }

        body.dark-mode .search-box input {
            background: var(--color-superficie-2);
        }

        .search-box input:focus,
        .fecha-form-card input[type="date"]:focus {
            border-color: rgba(27,57,106,0.28);
            box-shadow: 0 0 0 4px rgba(27,57,106,0.10);
        }

        .table-wrapper {
            padding: 18px 24px 24px;
        }

        .table-scroll {
            width: 100%;
            overflow-x: auto;
            border-radius: 22px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        thead th {
            background: rgba(27,57,106,0.08);
            color: var(--color-principal);
            font-weight: 900;
            font-size: .92rem;
            text-align: left;
            padding: 16px;
            border-bottom: 1px solid var(--color-borde-suave);
            white-space: nowrap;
        }

        body.dark-mode thead th {
            color: #dbe6f9;
            background: rgba(255,255,255,0.06);
        }

        tbody td {
            padding: 16px;
            border-bottom: 1px solid var(--color-borde-suave);
            color: var(--color-texto);
            vertical-align: middle;
        }

        tbody tr:hover {
            background: rgba(27,57,106,0.03);
        }

        body.dark-mode tbody tr:hover {
            background: rgba(255,255,255,0.03);
        }

        .table-user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 220px;
        }

        .table-user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, rgba(39,132,211,0.16), rgba(34,197,94,0.16));
            color: var(--color-principal);
            font-weight: 900;
            font-size: .95rem;
            flex-shrink: 0;
        }

        body.dark-mode .table-user-avatar {
            color: #dce8ff;
        }

        .table-user-data strong {
            display: block;
            font-size: .96rem;
            font-weight: 800;
        }

        .table-user-data span {
            display: block;
            color: var(--color-texto-suave);
            font-size: .83rem;
            margin-top: 2px;
        }

        .date-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            background: rgba(27,57,106,0.07);
            color: var(--color-principal);
            font-weight: 700;
            font-size: .84rem;
        }

        body.dark-mode .date-chip {
            color: #dce8ff;
            background: rgba(255,255,255,0.06);
        }

        .estado-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .84rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .estado-puntual {
            color: #15803d;
            background: rgba(34,197,94,0.12);
        }

        .estado-retraso {
            color: #b45309;
            background: rgba(245,158,11,0.16);
        }

        .estado-falta {
            color: #b91c1c;
            background: rgba(220,38,38,0.12);
        }

        .hora-vacia {
            color: var(--color-texto-suave);
            font-style: italic;
        }

        .actions-cell {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .btn-table {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 12px;
            border-radius: 12px;
            font-size: .84rem;
            font-weight: 800;
            transition: .18s ease;
        }

        .btn-view {
            background: rgba(37,99,235,0.10);
            color: #1d4ed8;
        }

        .btn-view:hover {
            transform: translateY(-1px);
            background: rgba(37,99,235,0.16);
        }

        .mobile-cards {
            display: none;
            padding: 0 24px 24px;
            gap: 14px;
        }

        .mobile-asistencia-card {
            background: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-sm);
            border-radius: 20px;
            padding: 18px;
        }

        .mobile-asistencia-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .mobile-asistencia-content {
            display: grid;
            gap: 10px;
        }

        .mobile-field {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .mobile-field small {
            color: var(--color-texto-suave);
            font-weight: 700;
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        .mobile-field span {
            font-weight: 600;
            font-size: .95rem;
        }

        .mobile-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 14px;
        }

        .empty-state {
            margin: 24px;
            border: 1px dashed var(--color-borde-suave);
            border-radius: 22px;
            padding: 34px 22px;
            text-align: center;
            background: var(--color-superficie);
        }

        .empty-state i {
            font-size: 2rem;
            color: var(--color-principal);
            margin-bottom: 12px;
        }

        .empty-state h3 {
            font-size: 1.35rem;
            color: var(--color-principal);
            font-weight: 900;
            margin-bottom: 8px;
        }

        body.dark-mode .empty-state h3 {
            color: #dce8ff;
        }

        .empty-state p {
            color: var(--color-texto-suave);
            margin-bottom: 10px;
        }

        /* =========================
           RESPONSIVE
        ========================= */
        @media (max-width: 1180px) {
            .top-header-inner {
                flex-direction: column;
                width: min(100%, calc(100% - 24px));
                padding: 22px 0 16px;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 900px) {
            .brand-main {
                flex-direction: column;
                align-items: flex-start;
            }

            .brand-logos {
                width: fit-content;
            }

            .welcome-pill {
                width: 100%;
                border-radius: 20px;
            }

            .summary-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .page-wrapper {
                width: min(100%, calc(100% - 20px));
                margin: 16px auto 0;
                padding-bottom: 18px;
            }

            .top-header-inner {
                width: min(100%, calc(100% - 20px));
                padding: 16px 0 14px;
                gap: 16px;
            }

            .header-actions {
                width: 100%;
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }

            .header-pill,
            .header-link,
            .theme-toggle {
                width: 100%;
                min-height: 50px;
                padding: 12px 14px;
                font-size: .94rem;
            }

            .main-card-head,
            .summary-grid,
            .tools-row,
            .table-wrapper,
            .mobile-cards {
                padding-left: 14px;
                padding-right: 14px;
            }

            .main-card-head {
                padding-top: 18px;
                padding-bottom: 16px;
            }

            .tools-left,
            .tools-right,
            .fecha-form-card,
            .search-box {
                width: 100%;
            }

            .fecha-form-card {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-filter {
                width: 100%;
            }

            .tools-row {
                flex-direction: column;
                align-items: stretch;
            }

            .table-wrapper {
                display: none;
            }

            .mobile-cards {
                display: grid;
            }
        }

        @media (max-width: 560px) {
            .header-actions {
                grid-template-columns: 1fr;
            }

            .brand-logos img {
                height: 48px;
            }

            .brand-text h1 {
                font-size: 1.25rem;
            }

            .brand-text p {
                font-size: .92rem;
            }

            .welcome-pill {
                font-size: .92rem;
                padding: 12px 14px;
            }
        }
    </style>
</head>
<body>

    <header class="top-header">
        <div class="top-header-inner">
            <div class="brand-area">
                <div class="brand-main">
                    <div class="brand-logos">
                        <img src="../assets/img/logo_secretaria.png" alt="Logo Secretaría de Educación">
                        <img src="../assets/img/logo_escuelaaa.png" alt="Logo Escuela Secundaria">
                    </div>

                    <div class="brand-text">
                        <h1>Sistema de Asistencia Escolar</h1>
                        <p>Asistencias diarias • Consulta por fecha</p>
                    </div>
                </div>

                <div class="welcome-pill">
                    <div class="welcome-user-avatar"><?php echo $inicialesSesion; ?></div>
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Bienvenido, <?php echo $nombreSesion; ?> (<?php echo $rolSesion; ?>)</span>
                </div>
            </div>

            <div class="header-actions">
                <div class="header-pill">
                    <span class="status-dot"></span>
                    Sistema en línea
                </div>

                <button type="button" class="theme-toggle" id="themeToggle">
                    <i class="fa-solid fa-moon"></i>
                    <span>Modo noche</span>
                </button>

                <a href="modulos.php" class="header-link">
                    <i class="fa-solid fa-layer-group"></i>
                    Módulos
                </a>

                <a href="inicio.php" class="header-link">
                    <i class="fa-solid fa-arrow-left"></i>
                    Volver al panel
                </a>

                <a href="logout.php" class="header-link">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    Cerrar sesión
                </a>
            </div>
        </div>
    </header>

    <div class="page-wrapper">
        <section class="content-section">
            <div class="main-card">
                <div class="main-card-head">
                    <div class="main-card-title">
                        <h2>Asistencias del día</h2>
                        <p>Consulta las asistencias registradas por fecha manteniendo la misma lógica de búsqueda y detalle de tu archivo original.</p>
                    </div>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Asistencias encontradas</small>
                            <strong><?php echo (int)$num; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Fecha consultada</small>
                            <strong style="font-size:1.2rem;"><?php echo htmlspecialchars($fechaBonita, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-calendar-day"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Puntuales</small>
                            <strong><?php echo (int)$totalPuntuales; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Retrasos / faltas</small>
                            <strong><?php echo (int)($totalRetrasos + $totalFaltas); ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </div>
                </div>

                <div class="tools-row">
                    <div class="tools-left">
                        <form method="GET" class="fecha-form-card">
                            <label for="date">
                                <i class="fas fa-calendar-alt"></i>
                                Seleccionar fecha
                            </label>
                            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date, ENT_QUOTES, 'UTF-8'); ?>" required>
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i>
                                Buscar
                            </button>
                        </form>
                    </div>

                    <div class="tools-right">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchAsistencias" placeholder="Buscar por maestro o estado...">
                        </div>
                    </div>
                </div>

                <?php if ($num > 0): ?>
                    <div class="table-wrapper">
                        <div class="table-scroll">
                            <table id="asistenciasTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Maestro</th>
                                        <th>Fecha</th>
                                        <th>Hora entrada</th>
                                        <th>Hora salida</th>
                                        <th>Estado entrada</th>
                                        <th>Estado salida</th>
                                        <th>Minutos retraso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($asistencias as $row): ?>
                                        <?php
                                            $id = (int)($row['id'] ?? 0);
                                            $nombreMaestro = (string)($row['nombre_maestro'] ?? '');
                                            $fecha = (string)($row['fecha'] ?? '');
                                            $horaEntrada = $row['hora_entrada'] ?? null;
                                            $horaSalida = $row['hora_salida'] ?? null;
                                            $estadoEntrada = (string)($row['estado_entrada'] ?? 'Sin estado');
                                            $estadoSalida = (string)($row['estado_salida'] ?? 'Sin estado');
                                            $minutosRetraso = (int)($row['minutos_retraso'] ?? 0);

                                            $busqueda = mb_strtolower(
                                                $nombreMaestro . ' ' . $estadoEntrada . ' ' . $estadoSalida . ' ' . $fecha,
                                                'UTF-8'
                                            );
                                        ?>
                                        <tr data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>#<?php echo $id; ?></td>

                                            <td>
                                                <div class="table-user-cell">
                                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales($nombreMaestro), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="table-user-data">
                                                        <strong><?php echo htmlspecialchars($nombreMaestro, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <span>Registro de asistencia</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="date-chip">
                                                    <i class="fa-regular fa-calendar"></i>
                                                    <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if (!empty($horaEntrada)): ?>
                                                    <?php echo htmlspecialchars($horaEntrada, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php else: ?>
                                                    <span class="hora-vacia">N/A</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <?php if (!empty($horaSalida)): ?>
                                                    <?php echo htmlspecialchars($horaSalida, ENT_QUOTES, 'UTF-8'); ?>
                                                <?php else: ?>
                                                    <span class="hora-vacia">N/A</span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <span class="estado-badge <?php echo claseEstado($estadoEntrada); ?>">
                                                    <i class="fas fa-<?php echo iconoEstado($estadoEntrada); ?>"></i>
                                                    <?php echo htmlspecialchars($estadoEntrada, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <span class="estado-badge <?php echo claseEstado($estadoSalida); ?>">
                                                    <i class="fas fa-<?php echo iconoEstado($estadoSalida); ?>"></i>
                                                    <?php echo htmlspecialchars($estadoSalida, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>

                                            <td>
                                                <?php if ($minutosRetraso > 0): ?>
                                                    <span class="estado-badge estado-retraso">
                                                        <i class="fas fa-clock"></i>
                                                        <?php echo htmlspecialchars((string)$minutosRetraso, ENT_QUOTES, 'UTF-8'); ?> min
                                                    </span>
                                                <?php else: ?>
                                                    <span class="estado-badge estado-puntual">
                                                        <i class="fas fa-check"></i>
                                                        Sin retraso
                                                    </span>
                                                <?php endif; ?>
                                            </td>

                                            <td>
                                                <div class="actions-cell">
                                                    <a class="btn-table btn-view" href="detalle_asistencia.php?id=<?php echo $id; ?>">
                                                        <i class="fas fa-eye"></i>
                                                        Ver detalle
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mobile-cards" id="mobileCards">
                        <?php foreach ($asistencias as $row): ?>
                            <?php
                                $id = (int)($row['id'] ?? 0);
                                $nombreMaestro = (string)($row['nombre_maestro'] ?? '');
                                $fecha = (string)($row['fecha'] ?? '');
                                $horaEntrada = $row['hora_entrada'] ?? null;
                                $horaSalida = $row['hora_salida'] ?? null;
                                $estadoEntrada = (string)($row['estado_entrada'] ?? 'Sin estado');
                                $estadoSalida = (string)($row['estado_salida'] ?? 'Sin estado');
                                $minutosRetraso = (int)($row['minutos_retraso'] ?? 0);

                                $busqueda = mb_strtolower(
                                    $nombreMaestro . ' ' . $estadoEntrada . ' ' . $estadoSalida . ' ' . $fecha,
                                    'UTF-8'
                                );
                            ?>
                            <article class="mobile-asistencia-card" data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mobile-asistencia-top">
                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales($nombreMaestro), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="table-user-data">
                                        <strong><?php echo htmlspecialchars($nombreMaestro, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span>ID #<?php echo $id; ?></span>
                                    </div>
                                </div>

                                <div class="mobile-asistencia-content">
                                    <div class="mobile-field">
                                        <small>Fecha</small>
                                        <span class="date-chip">
                                            <i class="fa-regular fa-calendar"></i>
                                            <?php echo htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Hora entrada</small>
                                        <span><?php echo !empty($horaEntrada) ? htmlspecialchars($horaEntrada, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Hora salida</small>
                                        <span><?php echo !empty($horaSalida) ? htmlspecialchars($horaSalida, ENT_QUOTES, 'UTF-8') : 'N/A'; ?></span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Estado entrada</small>
                                        <span class="estado-badge <?php echo claseEstado($estadoEntrada); ?>">
                                            <i class="fas fa-<?php echo iconoEstado($estadoEntrada); ?>"></i>
                                            <?php echo htmlspecialchars($estadoEntrada, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Estado salida</small>
                                        <span class="estado-badge <?php echo claseEstado($estadoSalida); ?>">
                                            <i class="fas fa-<?php echo iconoEstado($estadoSalida); ?>"></i>
                                            <?php echo htmlspecialchars($estadoSalida, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Minutos retraso</small>
                                        <?php if ($minutosRetraso > 0): ?>
                                            <span class="estado-badge estado-retraso">
                                                <i class="fas fa-clock"></i>
                                                <?php echo htmlspecialchars((string)$minutosRetraso, ENT_QUOTES, 'UTF-8'); ?> min
                                            </span>
                                        <?php else: ?>
                                            <span class="estado-badge estado-puntual">
                                                <i class="fas fa-check"></i>
                                                Sin retraso
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="mobile-actions">
                                    <a class="btn-table btn-view" href="detalle_asistencia.php?id=<?php echo $id; ?>">
                                        <i class="fas fa-eye"></i>
                                        Ver detalle
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <h3>No se encontraron asistencias</h3>
                        <p>No hay registros para la fecha <strong><?php echo htmlspecialchars($selected_date, ENT_QUOTES, 'UTF-8'); ?></strong>.</p>
                        <p>Selecciona otra fecha para consultar la información.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <script>
        const body = document.body;
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('gestion_asistencias_theme');

        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            if (themeToggle) {
                themeToggle.innerHTML = '<i class="fa-solid fa-sun"></i><span>Modo claro</span>';
            }
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                body.classList.toggle('dark-mode');

                const isDark = body.classList.contains('dark-mode');
                localStorage.setItem('gestion_asistencias_theme', isDark ? 'dark' : 'light');

                themeToggle.innerHTML = isDark
                    ? '<i class="fa-solid fa-sun"></i><span>Modo claro</span>'
                    : '<i class="fa-solid fa-moon"></i><span>Modo noche</span>';
            });
        }

        const searchInput = document.getElementById('searchAsistencias');
        const desktopRows = Array.from(document.querySelectorAll('#asistenciasTable tbody tr'));
        const mobileCards = Array.from(document.querySelectorAll('#mobileCards .mobile-asistencia-card'));

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();

                desktopRows.forEach(row => {
                    const text = (row.dataset.search || '').toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });

                mobileCards.forEach(card => {
                    const text = (card.dataset.search || '').toLowerCase();
                    card.style.display = text.includes(term) ? '' : 'none';
                });
            });
        }
    </script>

</body>
</html>