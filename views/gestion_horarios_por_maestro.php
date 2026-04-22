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

// Consulta: horarios agrupados por maestro
$query = "
    SELECT 
        u.id AS usuario_id,
        u.nombre_completo,
        m.nombre AS materia,
        g.nombre AS grupo,
        h.dia_semana,
        h.hora_inicio,
        h.hora_fin,
        h.tolerancia_entrada,
        h.limite_retardo,
        h.id AS horario_id
    FROM horarios h
    JOIN maestros ma ON h.maestro_id = ma.id
    JOIN usuarios u ON ma.usuario_id = u.id
    JOIN materias m ON h.materia_id = m.id
    JOIN grupos g ON h.grupo_id = g.id
    ORDER BY u.nombre_completo, h.dia_semana, h.hora_inicio
";
$stmt = $db->prepare($query);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Agrupar por maestro
$horariosPorMaestro = [];
foreach ($rows as $row) {
    $nombre = $row['nombre_completo'];
    if (!isset($horariosPorMaestro[$nombre])) {
        $horariosPorMaestro[$nombre] = [
            'usuario_id' => $row['usuario_id'],
            'horarios' => []
        ];
    }
    $horariosPorMaestro[$nombre]['horarios'][] = $row;
}

// Obtener lista de maestros para el filtro
$maestrosQuery = "
    SELECT u.id, u.nombre_completo
    FROM usuarios u
    INNER JOIN roles r ON u.rol_id = r.id
    WHERE r.nombre = 'Maestro'
    ORDER BY u.nombre_completo
";
$stmtMaestros = $db->prepare($maestrosQuery);
$stmtMaestros->execute();
$maestrosList = $stmtMaestros->fetchAll(PDO::FETCH_ASSOC);

$totalHorarios = count($rows);
$totalMaestrosConHorario = count($horariosPorMaestro);
$totalMaestros = count($maestrosList);

function obtenerIniciales(string $nombre): string
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return 'HR';
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

    return $iniciales !== '' ? $iniciales : 'HR';
}

function formatearHoraSimple(string $hora): string
{
    $timestamp = strtotime($hora);
    return $timestamp ? date('H:i', $timestamp) : $hora;
}

$nombreSesion = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$rolSesion = htmlspecialchars($_SESSION['user_rol_nombre'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8');
$inicialesSesion = htmlspecialchars(obtenerIniciales($_SESSION['user_nombre'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios por Maestro - Sistema de Asistencia Escolar</title>

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

        .main-card-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-main,
        .btn-secondary,
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
        }

        .btn-main {
            background: linear-gradient(135deg, var(--color-secundario), var(--color-principal));
            color: #fff;
            box-shadow: var(--sombra-sm);
        }

        .btn-main:hover {
            transform: translateY(-2px);
            filter: brightness(1.03);
        }

        .btn-secondary,
        .btn-filter {
            background: rgba(27,57,106,0.08);
            color: var(--color-principal);
            border: 1px solid rgba(27,57,106,0.12);
        }

        body.dark-mode .btn-secondary,
        body.dark-mode .btn-filter {
            background: rgba(255,255,255,0.06);
            color: #e5edf9;
            border-color: rgba(255,255,255,0.08);
        }

        .btn-secondary:hover,
        .btn-filter:hover {
            transform: translateY(-2px);
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
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
            font-size: 2rem;
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
            align-items: center;
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

        .select-box {
            min-width: 280px;
        }

        .select-box select {
            width: 100%;
            height: 48px;
            border-radius: 16px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            padding: 0 14px;
            outline: none;
            transition: .18s ease;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.35);
        }

        body.dark-mode .select-box select {
            background: var(--color-superficie-2);
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
        .select-box select:focus {
            border-color: rgba(27,57,106,0.28);
            box-shadow: 0 0 0 4px rgba(27,57,106,0.10);
        }

        .maestros-grid-wrap {
            padding: 18px 24px 24px;
        }

        .maestros-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .maestro-card {
            background: var(--color-superficie);
            border-radius: 22px;
            padding: 18px;
            box-shadow: var(--sombra-sm);
            border: 1px solid var(--color-borde-suave);
            cursor: pointer;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease;
        }

        .maestro-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--sombra-md);
            border-color: rgba(27,57,106,0.18);
        }

        .maestro-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
        }

        .maestro-card-info {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .maestro-avatar {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, rgba(39,132,211,0.92), rgba(34,197,94,0.92));
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 900;
            font-size: .95rem;
            flex-shrink: 0;
        }

        .maestro-card-info strong {
            display: block;
            font-size: .98rem;
            color: var(--color-texto);
            margin-bottom: 2px;
        }

        .maestro-card-info small {
            color: var(--color-texto-suave);
            font-size: .84rem;
        }

        .maestro-card-meta {
            margin-top: 14px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .meta-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .82rem;
            font-weight: 800;
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        .btn-add-horario-card {
            background: rgba(0, 155, 72, 0.15) !important;
            color: #009B48 !important;
            border: 1px solid rgba(0, 155, 72, 0.3) !important;
            padding: 10px 14px;
            border-radius: 12px;
            font-size: .84rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
            transition: all .18s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-add-horario-card:hover {
            background: rgba(0, 155, 72, 0.25) !important;
            color: #007E33 !important;
            transform: translateY(-1px);
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

        .empty-state p {
            color: var(--color-texto-suave);
            margin-bottom: 18px;
        }

        /* =========================
           MODALES
        ========================= */
        .modal,
        .modal-ver-horarios {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 999;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .modal.show,
        .modal-ver-horarios.show {
            display: flex;
        }

        .modal-content {
            width: 100%;
            max-width: 560px;
            max-height: 90vh;
            background: var(--color-superficie);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-lg);
            animation: modalShow .18s ease;
            display: flex;
            flex-direction: column;
        }

        .modal-content.modal-lg {
            max-width: 760px;
            max-height: 86vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        @keyframes modalShow {
            from {
                opacity: 0;
                transform: translateY(10px) scale(.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-header {
            padding: 18px 20px;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: linear-gradient(135deg, var(--color-principal), var(--color-secundario));
            flex-shrink: 0;
        }

        .modal-header h2,
        .modal-header h3 {
            font-size: 1.12rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 0;
        }

        .close,
        .modal-close {
            width: 42px;
            height: 42px;
            display: grid;
            place-items: center;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.18);
            background: rgba(255,255,255,0.14);
            color: #fff;
            cursor: pointer;
            font-size: 1.05rem;
            transition: .18s ease;
            line-height: 1;
        }

        .close:hover,
        .modal-close:hover {
            background: rgba(255,255,255,0.22);
            transform: scale(1.03);
        }

        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
            min-height: 0;
        }

        .modal-description {
            color: var(--color-texto-suave);
            margin-bottom: 16px;
            font-size: .95rem;
        }

        .form-grid {
            display: grid;
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-weight: 800;
            color: var(--color-principal);
            font-size: .92rem;
        }

        body.dark-mode .form-group label {
            color: #dce8ff;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap i {
            position: absolute;
            top: 50%;
            left: 14px;
            transform: translateY(-50%);
            color: var(--color-texto-suave);
            z-index: 1;
        }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            min-height: 50px;
            border-radius: 16px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            padding: 12px 14px 12px 42px;
            outline: none;
            transition: .18s ease;
        }

        body.dark-mode .input-wrap input,
        body.dark-mode .input-wrap select {
            background: var(--color-superficie-2);
        }

        .input-wrap input:focus,
        .input-wrap select:focus {
            border-color: rgba(27,57,106,0.28);
            box-shadow: 0 0 0 4px rgba(27,57,106,0.10);
        }

        .input-flex {
            display: flex;
            gap: 8px;
            align-items: stretch;
        }

        .btn-icon-clean {
            padding: 0 14px;
            border: 1px solid var(--color-borde-suave);
            border-radius: 16px;
            background: rgba(27,57,106,0.08);
            color: var(--color-principal);
            cursor: pointer;
            font-weight: 800;
        }

        .modal-footer,
        .modal-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
            flex-shrink: 0;
            padding-top: 16px;
            background: var(--color-superficie);
            position: sticky;
            bottom: 0;
        }

        .modal-footer button,
        .modal-actions button {
            border: none;
            cursor: pointer;
        }

        .btn-close-modal {
            background: rgba(27,57,106,0.08);
            color: var(--color-principal);
            border: 1px solid rgba(27,57,106,0.12);
            padding: 12px 18px;
            border-radius: 16px;
            font-weight: 800;
        }

        .btn-save-modal {
            background: linear-gradient(135deg, var(--color-secundario), var(--color-principal));
            color: #fff;
            padding: 12px 18px;
            border-radius: 16px;
            font-weight: 800;
            box-shadow: var(--sombra-sm);
        }

        .horarios-lista {
            display: grid;
            gap: 12px;
        }

        .horario-item-modal {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px;
            border: 1px solid var(--color-borde-suave);
            border-radius: 18px;
            background: rgba(27,57,106,0.03);
        }

        .horario-icon {
            width: 42px;
            height: 42px;
            background: rgba(14, 77, 146, 0.08);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--color-principal);
            flex-shrink: 0;
        }

        .horario-details {
            flex: 1;
            min-width: 0;
        }

        .horario-details strong {
            display: block;
            color: var(--color-texto);
            font-size: .96rem;
            margin-bottom: 4px;
        }

        .horario-details span {
            display: block;
            color: var(--color-texto-suave);
            font-size: .86rem;
        }

        .horario-actions-modal {
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .horario-actions-modal a {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            color: var(--color-texto-suave);
            transition: all .18s ease;
            background: rgba(255,255,255,0.7);
            border: 1px solid var(--color-borde-suave);
        }

        .horario-actions-modal a:hover {
            background: rgba(14,77,146,0.08);
            color: var(--color-principal);
        }

        .no-data {
            color: var(--color-texto-suave);
            text-align: center;
            padding: 36px 0;
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
        }

        @media (max-width: 900px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }

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

            .maestros-grid {
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
            .maestros-grid-wrap {
                padding-left: 14px;
                padding-right: 14px;
            }

            .main-card-head {
                padding-top: 18px;
                padding-bottom: 16px;
            }

            .main-card-actions,
            .tools-left,
            .tools-right,
            .select-box,
            .search-box {
                width: 100%;
            }

            .main-card-actions .btn-main,
            .tools-left .btn-filter,
            .tools-right .btn-main,
            .tools-right .btn-secondary {
                width: 100%;
            }

            .tools-row {
                flex-direction: column;
                align-items: stretch;
            }

            .maestro-card-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-add-horario-card {
                justify-content: center;
                width: 100%;
            }

            .modal-content,
            .modal-content.modal-lg {
                max-width: 100%;
                max-height: 92vh;
                border-radius: 20px;
            }

            .horario-item-modal {
                flex-direction: column;
            }

            .horario-actions-modal {
                width: 100%;
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

            .input-flex {
                flex-direction: column;
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
                        <img src="../assets/img/logo_secretaria.png" alt="Logo Secretaría">
                        <img src="../assets/img/logo_escuelaaa.png" alt="Logo Escuela">
                    </div>

                    <div class="brand-text">
                        <h1>Sistema de Asistencia Escolar</h1>
                        <p>Secundaria “Emperador Cuauhtémoc” • Horarios por maestro</p>
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
                        <h2>Horarios por maestro</h2>
                        <p>Consulta, visualiza y registra horarios manteniendo exactamente la misma funcionalidad del archivo original.</p>
                    </div>

                    <div class="main-card-actions">
                        <button id="openModalGlobal" class="btn-main" type="button">
                            <i class="fas fa-plus"></i>
                            Agregar horario
                        </button>
                    </div>
                </div>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Horarios registrados</small>
                            <strong><?php echo (int)$totalHorarios; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Maestros con horario</small>
                            <strong><?php echo (int)$totalMaestrosConHorario; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-chalkboard-user"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Total maestros</small>
                            <strong><?php echo (int)$totalMaestros; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                </div>

                <div class="tools-row">
                    <div class="tools-left">
                        <div class="select-box">
                            <select id="filterMaestro">
                                <option value="">Todos los maestros</option>
                                <?php foreach ($maestrosList as $m): ?>
                                    <option value="<?php echo $m['id']; ?>">
                                        <?php echo htmlspecialchars($m['nombre_completo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <button type="button" class="btn-filter" onclick="filterMaestros()">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            Filtrar
                        </button>
                    </div>

                    <div class="tools-right">
                        <div class="search-box">
                            <i class="fa-solid fa-search"></i>
                            <input type="text" id="searchMaestros" placeholder="Buscar maestro...">
                        </div>
                    </div>
                </div>

                <main class="maestros-grid-wrap">
                    <?php if (empty($horariosPorMaestro)): ?>
                        <div class="empty-state">
                            <i class="fa-solid fa-calendar-xmark"></i>
                            <h3>No hay horarios asignados aún</h3>
                            <p>Cuando registres horarios para los maestros, aquí aparecerán agrupados por docente.</p>
                            <button type="button" class="btn-main" onclick="openModal()">
                                <i class="fa-solid fa-plus"></i>
                                Agregar primer horario
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="maestros-grid" id="maestrosGrid">
                            <?php foreach ($horariosPorMaestro as $nombre => $data): ?>
                                <?php
                                    $usuarioId = (int)$data['usuario_id'];
                                    $totalHorariosMaestro = count($data['horarios']);
                                    $busqueda = mb_strtolower($nombre, 'UTF-8');
                                ?>
                                <div class="maestro-card" data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>" onclick="abrirModalVerHorarios('<?php echo $usuarioId; ?>')">
                                    <div class="maestro-card-header">
                                        <div class="maestro-card-info">
                                            <div class="maestro-avatar">
                                                <?php echo htmlspecialchars(obtenerIniciales($nombre), ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($nombre); ?></strong>
                                                <small><?php echo $totalHorariosMaestro; ?> horario<?php echo $totalHorariosMaestro !== 1 ? 's' : ''; ?> asignado<?php echo $totalHorariosMaestro !== 1 ? 's' : ''; ?></small>
                                            </div>
                                        </div>

                                        <button class="btn-add-horario-card" onclick="event.stopPropagation(); openModal(<?php echo $usuarioId; ?>)">
                                            <i class="fas fa-plus"></i>
                                            Agregar
                                        </button>
                                    </div>

                                    <div class="maestro-card-meta">
                                        <span class="meta-badge">
                                            <i class="fa-solid fa-calendar-week"></i>
                                            Ver horarios
                                        </span>
                                        <span class="meta-badge">
                                            <i class="fa-solid fa-layer-group"></i>
                                            Maestro #<?php echo $usuarioId; ?>
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </section>
    </div>

    <!-- Datos ocultos para cada maestro -->
    <div style="display:none;">
        <?php foreach ($horariosPorMaestro as $nombre => $data): ?>
            <div id="horarios-data-<?php echo $data['usuario_id']; ?>">
                <span class="maestro-nombre"><?php echo htmlspecialchars($nombre); ?></span>
                <?php foreach ($data['horarios'] as $h): ?>
                    <div class="horario-oculto" 
                         data-materia="<?php echo htmlspecialchars($h['materia']); ?>"
                         data-grupo="<?php echo htmlspecialchars($h['grupo']); ?>"
                         data-dia="<?php echo htmlspecialchars($h['dia_semana']); ?>"
                         data-inicio="<?php echo htmlspecialchars($h['hora_inicio']); ?>"
                         data-fin="<?php echo htmlspecialchars($h['hora_fin']); ?>"
                         data-id="<?php echo $h['horario_id']; ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Modal VER Horarios -->
    <div id="verHorariosModal" class="modal-ver-horarios">
        <div class="modal-content modal-lg">
            <div class="modal-header">
                <h2><i class="fas fa-calendar-week"></i> <span id="modalMaestroNombre"></span></h2>
                <button type="button" class="close" onclick="cerrarVerHorarios()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalHorariosLista" class="horarios-lista"></div>
            </div>
        </div>
    </div>

    <!-- Modal CREAR Horario -->
    <div id="createHorarioModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-clock"></i> Agregar nuevo horario</h2>
                <button type="button" class="close" onclick="closeModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <p class="modal-description">
                    Registra un nuevo horario manteniendo la misma lógica y rutas que ya usa tu sistema.
                </p>

                <form id="createHorarioForm" method="POST" action="../controllers/CrearHorarioController.php">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="modalMaestroId">Maestro</label>
                            <div class="input-flex">
                                <div class="input-wrap" style="flex:1;">
                                    <i class="fa-solid fa-user"></i>
                                    <select name="maestro_id" id="modalMaestroId" required>
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
                                </div>

                                <button type="button" class="btn-icon-clean" onclick="limpiarMaestroSeleccionado()" title="Limpiar selección">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="materia_id">Materia</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-book"></i>
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
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="grupo_id">Grupo</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-users-rectangle"></i>
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
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="dia_semana">Día de la semana</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-calendar-day"></i>
                                <select name="dia_semana" id="dia_semana" required>
                                    <option value="">Selecciona un día</option>
                                    <option value="Lunes">Lunes</option>
                                    <option value="Martes">Martes</option>
                                    <option value="Miércoles">Miércoles</option>
                                    <option value="Jueves">Jueves</option>
                                    <option value="Viernes">Viernes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="hora_inicio">Hora de inicio</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-clock"></i>
                                <input type="time" name="hora_inicio" id="hora_inicio" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="hora_fin">Hora de fin</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-clock"></i>
                                <input type="time" name="hora_fin" id="hora_fin" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="tolerancia_entrada">Tolerancia de entrada (minutos)</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hourglass-start"></i>
                                <input type="number" name="tolerancia_entrada" id="tolerancia_entrada" required placeholder="Ej. 10">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="limite_retardo">Límite de retardo (minutos)</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-hourglass-end"></i>
                                <input type="number" name="limite_retardo" id="limite_retardo" required placeholder="Ej. 15">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn-close-modal" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn-save-modal">
                            <i class="fas fa-check"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<script>
const body = document.body;
const themeToggle = document.getElementById('themeToggle');
const savedTheme = localStorage.getItem('gestion_horarios_theme');

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
        localStorage.setItem('gestion_horarios_theme', isDark ? 'dark' : 'light');

        themeToggle.innerHTML = isDark
            ? '<i class="fa-solid fa-sun"></i><span>Modo claro</span>'
            : '<i class="fa-solid fa-moon"></i><span>Modo noche</span>';
    });
}

// Búsqueda visual de maestros
const searchInput = document.getElementById('searchMaestros');
const maestroCards = Array.from(document.querySelectorAll('.maestro-card'));

if (searchInput) {
    searchInput.addEventListener('input', function () {
        const term = this.value.trim().toLowerCase();

        maestroCards.forEach(card => {
            const text = (card.dataset.search || '').toLowerCase();
            card.style.display = text.includes(term) ? '' : 'none';
        });
    });
}

// === MODAL CREAR HORARIO ===
const modal = document.getElementById('createHorarioModal');
const openModalGlobalBtn = document.getElementById('openModalGlobal');
const maestroInput = document.getElementById('modalMaestroId');

function showFlexModal(el) {
    el.classList.add('show');
}

function hideFlexModal(el) {
    el.classList.remove('show');
}

function openModal(maestro_id = null) {
    document.getElementById('createHorarioForm').reset();

    if (!maestro_id) {
        maestro_id = sessionStorage.getItem('ultimo_maestro_id');
    }

    if (maestro_id) {
        maestroInput.value = maestro_id;
        sessionStorage.setItem('ultimo_maestro_id', maestro_id);
    } else {
        maestroInput.value = '';
    }

    showFlexModal(modal);
}

function closeModal() {
    hideFlexModal(modal);
}

function limpiarMaestroSeleccionado() {
    maestroInput.value = '';
    sessionStorage.removeItem('ultimo_maestro_id');
}

if (openModalGlobalBtn) {
    openModalGlobalBtn.onclick = () => openModal();
}

// === MODAL VER HORARIOS ===
const verHorariosModal = document.getElementById('verHorariosModal');
const modalMaestroNombre = document.getElementById('modalMaestroNombre');
const modalHorariosLista = document.getElementById('modalHorariosLista');

function abrirModalVerHorarios(usuario_id) {
    sessionStorage.setItem('ultimo_maestro_id', usuario_id);

    const dataDiv = document.getElementById('horarios-data-' + usuario_id);
    if (!dataDiv) {
        alert('No se encontraron datos para este maestro.');
        return;
    }

    const nombre = dataDiv.querySelector('.maestro-nombre').textContent;
    modalMaestroNombre.textContent = nombre;

    const horarios = dataDiv.querySelectorAll('.horario-oculto');

    if (horarios.length === 0) {
        modalHorariosLista.innerHTML = '<p class="no-data">Este maestro no tiene horarios asignados.</p>';
    } else {
        let html = '';

        horarios.forEach(h => {
            const materia = h.getAttribute('data-materia');
            const grupo = h.getAttribute('data-grupo');
            const dia = h.getAttribute('data-dia');
            const inicio = h.getAttribute('data-inicio');
            const fin = h.getAttribute('data-fin');
            const id = h.getAttribute('data-id');

            html += `
                <div class="horario-item-modal">
                    <div class="horario-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="horario-details">
                        <strong>${materia}</strong>
                        <span>${grupo} • ${dia}</span>
                        <span>${inicio} – ${fin}</span>
                    </div>
                    <div class="horario-actions-modal">
                        <a href="editar_horario.php?id=${id}" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="eliminar_horario.php?id=${id}" onclick="return confirm('¿Eliminar este horario?');" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
            `;
        });

        modalHorariosLista.innerHTML = html;
    }

    showFlexModal(verHorariosModal);
}

function cerrarVerHorarios() {
    hideFlexModal(verHorariosModal);
}

// Cerrar modales al hacer clic fuera
window.onclick = (e) => {
    if (e.target === modal) closeModal();
    if (e.target === verHorariosModal) cerrarVerHorarios();
};

// Filtro original
function filterMaestros() {
    const select = document.getElementById('filterMaestro');
    const valor = select.value;

    if (valor) {
        window.location.href = '?maestro_id=' + valor;
    } else {
        window.location.href = window.location.pathname;
    }
}
</script>

</body>
</html>
