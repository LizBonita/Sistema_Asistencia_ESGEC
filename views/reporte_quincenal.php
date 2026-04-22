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

// ==========================================
// CONVERTIR LOGOS A BASE64 CON VALIDACIÓN
// ==========================================
$logoEscuelaPath = __DIR__ . '/../assets/img/logo_escuelaaa.png';
$logoSecretariaPath = __DIR__ . '/../assets/img/logo_secretaria.png';

$logoEscuelaBase64 = '';
$logoSecretariaBase64 = '';
$logoEscuelaError = '';
$logoSecretariaError = '';

if (file_exists($logoEscuelaPath)) {
    $imageData = file_get_contents($logoEscuelaPath);
    if ($imageData !== false) {
        $logoEscuelaBase64 = 'image/png;base64,' . base64_encode($imageData);
    } else {
        $logoEscuelaError = 'No se pudo leer el archivo';
    }
} else {
    $logoEscuelaError = 'Archivo no existe: ' . $logoEscuelaPath;
}

if (file_exists($logoSecretariaPath)) {
    $imageData = file_get_contents($logoSecretariaPath);
    if ($imageData !== false) {
        $logoSecretariaBase64 = 'image/png;base64,' . base64_encode($imageData);
    } else {
        $logoSecretariaError = 'No se pudo leer el archivo';
    }
} else {
    $logoSecretariaError = 'Archivo no existe: ' . $logoSecretariaPath;
}

// Obtener fechas del GET o calcular la quincena actual
$start_date_str = $_GET['start_date'] ?? '';
$end_date_str = $_GET['end_date'] ?? '';

if (empty($start_date_str) || empty($end_date_str)) {
    $today = new DateTime();
    $day = $today->format('j');

    if ($day <= 15) {
        $start_date = new DateTime($today->format('Y-m-01'));
        $end_date = new DateTime($today->format('Y-m-15'));
    } else {
        $start_date = new DateTime($today->format('Y-m-16'));
        $end_date = new DateTime($today->format('Y-m-t'));
    }

    $start_date_str = $start_date->format('Y-m-d');
    $end_date_str = $end_date->format('Y-m-d');
}

// Consulta para obtener minutos de retardo acumulados por maestro
$query = "
SELECT
    CONCAT(u.nombre_completo) as nombre_maestro,
    SUM(a.minutos_retraso) as minutos_acumulados,
    COUNT(a.id) as dias_con_retraso
FROM
    asistencias a
LEFT JOIN maestros m ON a.maestro_id = m.id
LEFT JOIN usuarios u ON m.usuario_id = u.id
WHERE
    a.fecha BETWEEN ? AND ?
    AND a.minutos_retraso > 0
GROUP BY
    a.maestro_id, u.nombre_completo
ORDER BY
    minutos_acumulados DESC;
";

$stmt = $db->prepare($query);
$stmt->bindParam(1, $start_date_str);
$stmt->bindParam(2, $end_date_str);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

$totalMaestros = count($resultados);
$totalMinutos = array_sum(array_map(fn($r) => (int)$r['minutos_acumulados'], $resultados));
$totalDias = array_sum(array_map(fn($r) => (int)$r['dias_con_retraso'], $resultados));

function obtenerIniciales(string $nombre): string
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return 'RP';
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

    return $iniciales !== '' ? $iniciales : 'RP';
}

function formatearFechaBonita(string $fecha): string
{
    $timestamp = strtotime($fecha);
    return $timestamp ? date('d/m/Y', $timestamp) : $fecha;
}

$nombreSesion = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$rolSesion = htmlspecialchars($_SESSION['user_rol_nombre'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8');
$inicialesSesion = htmlspecialchars(obtenerIniciales($_SESSION['user_nombre'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');
$periodoBonito = formatearFechaBonita($start_date_str) . ' - ' . formatearFechaBonita($end_date_str);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Quincenal - Sistema de Asistencia</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Librerías para exportar -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

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
        input {
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
            font-size: 1.55rem;
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
            align-items: flex-start;
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
            align-items: flex-start;
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

        .campo-fecha {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .campo-fecha label {
            font-weight: 800;
            color: var(--color-principal);
            font-size: .9rem;
        }

        .campo-fecha input[type="date"] {
            height: 48px;
            padding: 0 14px;
            border-radius: 14px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            outline: none;
        }

        body.dark-mode .campo-fecha input[type="date"] {
            background: var(--color-superficie-2);
        }

        .btn-filter,
        .btn-export {
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

        .btn-filter {
            background: linear-gradient(135deg, var(--color-secundario), var(--color-principal));
            color: #fff;
            box-shadow: var(--sombra-sm);
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            filter: brightness(1.03);
        }

        .btn-pdf {
            background: rgba(220,38,38,0.12);
            color: #b91c1c;
            border: 1px solid rgba(220,38,38,0.18);
        }

        .btn-excel {
            background: rgba(22,163,74,0.12);
            color: #15803d;
            border: 1px solid rgba(22,163,74,0.18);
        }

        .btn-export:hover {
            transform: translateY(-2px);
        }

        .search-box {
            position: relative;
            width: min(100%, 360px);
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
        .campo-fecha input[type="date"]:focus {
            border-color: rgba(27,57,106,0.28);
            box-shadow: 0 0 0 4px rgba(27,57,106,0.10);
        }

        .alert-error {
            margin: 16px 24px 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.18);
            color: #b91c1c;
            font-size: .92rem;
        }

        .alert-error strong {
            display: inline-block;
            margin-bottom: 6px;
        }

        .alert-error ul {
            margin-top: 8px;
            margin-left: 20px;
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
            min-width: 980px;
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
            min-width: 240px;
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

        .metric-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .84rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .badge-minutos {
            color: #b45309;
            background: rgba(245,158,11,0.16);
        }

        .badge-dias {
            color: #1d4ed8;
            background: rgba(37,99,235,0.12);
        }

        .badge-promedio {
            color: #15803d;
            background: rgba(22,163,74,0.12);
        }

        .mobile-cards {
            display: none;
            padding: 0 24px 24px;
            gap: 14px;
        }

        .mobile-reporte-card {
            background: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-sm);
            border-radius: 20px;
            padding: 18px;
        }

        .mobile-reporte-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .mobile-reporte-content {
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

            .btn-filter,
            .btn-export {
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
                        <p>Reporte quincenal de retardos</p>
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
                        <h2>Reporte quincenal de retardos</h2>
                        <p>Consulta el acumulado de minutos de retardo por maestro dentro del periodo seleccionado y exporta el reporte en PDF o Excel.</p>
                    </div>
                </div>

                <?php if ($logoEscuelaError || $logoSecretariaError): ?>
                    <div class="alert-error">
                        <strong><i class="fas fa-exclamation-triangle"></i> Advertencia:</strong>
                        Algunos logos no se cargaron correctamente para el PDF.
                        <ul>
                            <?php if ($logoEscuelaError): ?>
                                <li>Logo Escuela: <?php echo htmlspecialchars($logoEscuelaError, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endif; ?>
                            <?php if ($logoSecretariaError): ?>
                                <li>Logo Secretaría: <?php echo htmlspecialchars($logoSecretariaError, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Maestros con retraso</small>
                            <strong><?php echo (int)$totalMaestros; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Minutos acumulados</small>
                            <strong><?php echo (int)$totalMinutos; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Días con retraso</small>
                            <strong><?php echo (int)$totalDias; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Periodo</small>
                            <strong style="font-size:1rem;"><?php echo htmlspecialchars($periodoBonito, ENT_QUOTES, 'UTF-8'); ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-calendar-range"></i>
                        </div>
                    </div>
                </div>

                <div class="tools-row">
                    <div class="tools-left">
                        <form method="GET" class="fecha-form-card">
                            <div class="campo-fecha">
                                <label for="start_date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Fecha inicio
                                </label>
                                <input type="date" name="start_date" id="start_date" value="<?php echo htmlspecialchars($start_date_str, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <div class="campo-fecha">
                                <label for="end_date">
                                    <i class="fas fa-calendar-alt"></i>
                                    Fecha fin
                                </label>
                                <input type="date" name="end_date" id="end_date" value="<?php echo htmlspecialchars($end_date_str, ENT_QUOTES, 'UTF-8'); ?>" required>
                            </div>

                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i>
                                Generar reporte
                            </button>
                        </form>
                    </div>

                    <div class="tools-right">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchReporte" placeholder="Buscar maestro...">
                        </div>

                        <button class="btn-export btn-pdf" onclick="exportarPDF()">
                            <i class="fas fa-file-pdf"></i>
                            Descargar PDF
                        </button>

                        <button class="btn-export btn-excel" onclick="exportarExcel()">
                            <i class="fas fa-file-excel"></i>
                            Descargar Excel
                        </button>
                    </div>
                </div>

                <?php if (count($resultados) > 0): ?>
                    <div class="table-wrapper">
                        <div class="table-scroll">
                            <table id="tablaReporte">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre del maestro</th>
                                        <th>Minutos acumulados</th>
                                        <th>Días con retraso</th>
                                        <th>Promedio min/día</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $contador = 1; ?>
                                    <?php foreach ($resultados as $row): ?>
                                        <?php
                                            $promedio = $row['dias_con_retraso'] > 0
                                                ? round($row['minutos_acumulados'] / $row['dias_con_retraso'], 1)
                                                : 0;

                                            $busqueda = mb_strtolower((string)$row['nombre_maestro'], 'UTF-8');
                                        ?>
                                        <tr data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>#<?php echo $contador++; ?></td>

                                            <td>
                                                <div class="table-user-cell">
                                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales((string)$row['nombre_maestro']), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="table-user-data">
                                                        <strong><?php echo htmlspecialchars($row['nombre_maestro'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <span>Docente con registro de retardo</span>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                <span class="metric-badge badge-minutos">
                                                    <i class="fas fa-clock"></i>
                                                    <?php echo (int)$row['minutos_acumulados']; ?> min
                                                </span>
                                            </td>

                                            <td>
                                                <span class="metric-badge badge-dias">
                                                    <i class="fas fa-calendar-day"></i>
                                                    <?php echo (int)$row['dias_con_retraso']; ?> días
                                                </span>
                                            </td>

                                            <td>
                                                <span class="metric-badge badge-promedio">
                                                    <i class="fas fa-chart-line"></i>
                                                    <?php echo htmlspecialchars((string)$promedio, ENT_QUOTES, 'UTF-8'); ?> min/día
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mobile-cards" id="mobileCards">
                        <?php $contador = 1; ?>
                        <?php foreach ($resultados as $row): ?>
                            <?php
                                $promedio = $row['dias_con_retraso'] > 0
                                    ? round($row['minutos_acumulados'] / $row['dias_con_retraso'], 1)
                                    : 0;

                                $busqueda = mb_strtolower((string)$row['nombre_maestro'], 'UTF-8');
                            ?>
                            <article class="mobile-reporte-card" data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mobile-reporte-top">
                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales((string)$row['nombre_maestro']), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="table-user-data">
                                        <strong><?php echo htmlspecialchars($row['nombre_maestro'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span>Registro #<?php echo $contador++; ?></span>
                                    </div>
                                </div>

                                <div class="mobile-reporte-content">
                                    <div class="mobile-field">
                                        <small>Minutos acumulados</small>
                                        <span class="metric-badge badge-minutos">
                                            <i class="fas fa-clock"></i>
                                            <?php echo (int)$row['minutos_acumulados']; ?> min
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Días con retraso</small>
                                        <span class="metric-badge badge-dias">
                                            <i class="fas fa-calendar-day"></i>
                                            <?php echo (int)$row['dias_con_retraso']; ?> días
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Promedio min/día</small>
                                        <span class="metric-badge badge-promedio">
                                            <i class="fas fa-chart-line"></i>
                                            <?php echo htmlspecialchars((string)$promedio, ENT_QUOTES, 'UTF-8'); ?> min/día
                                        </span>
                                    </div>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-clipboard-check"></i>
                        <h3>No se encontraron registros de retraso</h3>
                        <p>Los maestros tuvieron asistencia puntual en el periodo seleccionado.</p>
                        <p><strong><?php echo htmlspecialchars($periodoBonito, ENT_QUOTES, 'UTF-8'); ?></strong></p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

<script>
const body = document.body;
const themeToggle = document.getElementById('themeToggle');
const savedTheme = localStorage.getItem('gestion_reporte_retardos_theme');

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
        localStorage.setItem('gestion_reporte_retardos_theme', isDark ? 'dark' : 'light');

        themeToggle.innerHTML = isDark
            ? '<i class="fa-solid fa-sun"></i><span>Modo claro</span>'
            : '<i class="fa-solid fa-moon"></i><span>Modo noche</span>';
    });
}

// Búsqueda visual
const searchInput = document.getElementById('searchReporte');
const desktopRows = Array.from(document.querySelectorAll('#tablaReporte tbody tr'));
const mobileCards = Array.from(document.querySelectorAll('#mobileCards .mobile-reporte-card'));

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

// Datos globales para exportación
const reporteData = {
    fechaInicio: '<?php echo $start_date_str; ?>',
    fechaFin: '<?php echo $end_date_str; ?>',
    maestros: <?php echo json_encode($resultados, JSON_UNESCAPED_UNICODE); ?>,
    logoEscuela: '<?php echo $logoEscuelaBase64; ?>',
    logoSecretaria: '<?php echo $logoSecretariaBase64; ?>'
};

console.log('=== DEBUG LOGOS ===');
console.log('Logo Escuela:', reporteData.logoEscuela ? '✅ Cargado (' + reporteData.logoEscuela.length + ' chars)' : '❌ No cargado');
console.log('Logo Secretaría:', reporteData.logoSecretaria ? '✅ Cargado (' + reporteData.logoSecretaria.length + ' chars)' : '❌ No cargado');

// Exportar a PDF con membrete oficial
function exportarPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'letter');

    const colorAzul = [14, 77, 146];
    const colorVerde = [0, 155, 72];

    try {
        if (reporteData.logoEscuela && reporteData.logoEscuela.length > 100) {
            doc.addImage(reporteData.logoEscuela, 'PNG', 50, 80, 110, 110, undefined, 'FAST', 0.08);
            console.log('✅ Marca de agua agregada');
        } else {
            console.warn('⚠️ Logo escuela no válido para marca de agua');
        }
    } catch(e) {
        console.error('❌ Error marca de agua:', e);
    }

    try {
        if (reporteData.logoEscuela && reporteData.logoEscuela.length > 100) {
            doc.addImage(reporteData.logoEscuela, 'PNG', 14, 14, 25, 25);
            console.log('✅ Logo escuela agregado al encabezado');
        } else {
            console.warn('⚠️ Logo escuela no válido');
            doc.setDrawColor(200, 200, 200);
            doc.rect(14, 14, 25, 25, 'S');
            doc.setFontSize(6);
            doc.text('LOGO', 26.5, 28, { align: 'center' });
        }
    } catch(e) {
        console.error('❌ Error logo escuela:', e);
    }

    try {
        if (reporteData.logoSecretaria && reporteData.logoSecretaria.length > 100) {
            doc.addImage(reporteData.logoSecretaria, 'PNG', 170, 14, 25, 25);
            console.log('✅ Logo secretaría agregado al encabezado');
        } else {
            console.warn('⚠️ Logo secretaría no válido');
            doc.setDrawColor(200, 200, 200);
            doc.rect(170, 14, 25, 25, 'S');
            doc.setFontSize(6);
            doc.text('LOGO', 182.5, 28, { align: 'center' });
        }
    } catch(e) {
        console.error('❌ Error logo secretaría:', e);
    }

    doc.setTextColor(50, 50, 50);
    doc.setFontSize(8);
    doc.setFont('helvetica', 'bold');
    doc.text('GOBIERNO DEL ESTADO LIBRE Y SOBERANO DE GUERRERO', 105, 18, { align: 'center' });

    doc.setFontSize(7);
    doc.setFont('helvetica', 'normal');
    doc.text('SECRETARÍA DE EDUCACIÓN', 105, 23, { align: 'center' });

    doc.setFontSize(6);
    doc.text('SUBSECRETARÍA DE EDUCACIÓN BÁSICA', 105, 27, { align: 'center' });
    doc.text('DIRECCIÓN GENERAL DE EDUCACIÓN SECUNDARIA', 105, 31, { align: 'center' });
    doc.text('DEPARTAMENTO DE SECUNDARIAS GENERALES', 105, 35, { align: 'center' });

    doc.setFontSize(7);
    doc.setFont('helvetica', 'bold');
    doc.text('ESCUELA SECUNDARIA GENERAL "EMPERADOR CUAUHTÉMOC"', 105, 40, { align: 'center' });

    doc.setDrawColor(...colorVerde);
    doc.setLineWidth(0.5);
    doc.line(14, 44, 196, 44);

    doc.setFontSize(10);
    doc.setFont('helvetica', 'bold');
    doc.setTextColor(...colorAzul);
    doc.text('REPORTE QUINCENAL DE RETARDOS', 105, 52, { align: 'center' });

    doc.setFontSize(8);
    doc.setFont('helvetica', 'normal');
    doc.setTextColor(100, 100, 100);
    doc.text(`Periodo: ${formatearFecha(reporteData.fechaInicio)} al ${formatearFecha(reporteData.fechaFin)}`, 105, 58, { align: 'center' });
    doc.text(`Fecha de emisión: ${new Date().toLocaleDateString('es-MX')}`, 105, 63, { align: 'center' });

    const tableColumn = ['#', 'Nombre del Maestro', 'Minutos Acumulados', 'Días con Retraso', 'Promedio'];
    const tableRows = [];

    let contador = 1;
    reporteData.maestros.forEach(row => {
        const promedio = row.dias_con_retraso > 0 ? (row.minutos_acumulados / row.dias_con_retraso).toFixed(1) : 0;
        tableRows.push([
            contador++,
            row.nombre_maestro,
            `${row.minutos_acumulados} min`,
            `${row.dias_con_retraso} días`,
            `${promedio} min/día`
        ]);
    });

    doc.autoTable({
        head: [tableColumn],
        body: tableRows,
        startY: 69,
        theme: 'striped',
        headStyles: {
            fillColor: colorAzul,
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            fontSize: 8
        },
        bodyStyles: { fontSize: 8 },
        alternateRowStyles: { fillColor: [250, 250, 250] },
        margin: { left: 14, right: 14 },
        didDrawPage: function(data) {
            doc.setFontSize(7);
            doc.setTextColor(150, 150, 150);
            doc.text('Página ' + data.pageNumber, 105, 285, { align: 'center' });
        }
    });

    const finalY = doc.lastAutoTable.finalY + 10;
    doc.setDrawColor(...colorAzul);
    doc.line(14, finalY, 196, finalY);

    doc.setFontSize(7);
    doc.setTextColor(100, 100, 100);
    doc.text('Este reporte es generado automáticamente por el Sistema de Control de Asistencia.', 105, finalY + 5, { align: 'center' });
    doc.text('Ciclo Escolar ' + new Date().getFullYear(), 105, finalY + 9, { align: 'center' });

    const firmaY = finalY + 20;
    doc.setDrawColor(150, 150, 150);

    doc.line(20, firmaY, 80, firmaY);
    doc.setFontSize(8);
    doc.setTextColor(50, 50, 50);
    doc.text('___________________________', 50, firmaY + 4, { align: 'center' });
    doc.text('DIRECTOR', 50, firmaY + 9, { align: 'center' });

    doc.line(130, firmaY, 190, firmaY);
    doc.text('___________________________', 160, firmaY + 4, { align: 'center' });
    doc.text('SUBDIRECTOR', 160, firmaY + 9, { align: 'center' });

    doc.setDrawColor(...colorVerde);
    doc.setLineWidth(0.5);
    doc.circle(105, firmaY + 6, 12, 'S');
    doc.setFontSize(6);
    doc.setTextColor(...colorVerde);
    doc.setFont('helvetica', 'bold');
    doc.text('OFICIAL', 105, firmaY + 4, { align: 'center' });
    doc.text('SELLO', 105, firmaY + 8, { align: 'center' });

    doc.setFontSize(6);
    doc.setTextColor(150, 150, 150);
    doc.setFont('helvetica', 'normal');
    doc.text('Calle Josefa Ortiz de Domínguez No.37, Barrio de tierra blanca C.P. 40430 Ixcateopan de Cuauhtémoc, Gro.', 105, 292, { align: 'center' });
    doc.text('TEL. (736) 36 6-91-18', 105, 295, { align: 'center' });

    const codigoUnico = 'RPT-' + reporteData.fechaInicio.replace(/-/g, '') + '-' + Date.now().toString().substr(-6);
    doc.setFontSize(6);
    doc.setTextColor(100, 100, 100);
    doc.text(`Código de verificación: ${codigoUnico}`, 14, 292);

    doc.save(`Reporte_Retardos_${reporteData.fechaInicio}_al_${reporteData.fechaFin}.pdf`);
}

// Exportar a Excel
function exportarExcel() {
    const wb = XLSX.utils.book_new();

    const datosExcel = reporteData.maestros.map((row, index) => ({
        '#': index + 1,
        'Maestro': row.nombre_maestro,
        'Minutos Acumulados': row.minutos_acumulados,
        'Días con Retraso': row.dias_con_retraso,
        'Promedio (min/día)': row.dias_con_retraso > 0 ? (row.minutos_acumulados / row.dias_con_retraso).toFixed(1) : 0
    }));

    const totalMinutos = reporteData.maestros.reduce((sum, row) => sum + parseInt(row.minutos_acumulados), 0);
    const totalDias = reporteData.maestros.reduce((sum, row) => sum + parseInt(row.dias_con_retraso), 0);

    datosExcel.push({
        '#': 'TOTAL',
        'Maestro': '',
        'Minutos Acumulados': totalMinutos,
        'Días con Retraso': totalDias,
        'Promedio (min/día)': ''
    });

    const ws = XLSX.utils.json_to_sheet(datosExcel);

    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const address = XLSX.utils.encode_col(C) + "1";
        if (!ws[address]) continue;
        ws[address].s = {
            font: { bold: true, color: { rgb: "FFFFFF" } },
            fill: { fgColor: { rgb: "0E4D92" } },
            alignment: { horizontal: "center" }
        };
    }

    XLSX.utils.book_append_sheet(wb, ws, 'Reporte Retardos');

    const infoData = [
        ['REPORTE QUINCENAL DE RETARDOS'],
        [''],
        ['Periodo:', `${formatearFecha(reporteData.fechaInicio)} al ${formatearFecha(reporteData.fechaFin)}`],
        ['Fecha de emisión:', new Date().toLocaleDateString('es-MX')],
        ['Total de maestros con retraso:', reporteData.maestros.length],
        ['Total de minutos acumulados:', totalMinutos]
    ];

    const wsInfo = XLSX.utils.aoa_to_sheet(infoData);
    XLSX.utils.book_append_sheet(wb, wsInfo, 'Información');

    XLSX.writeFile(wb, `Reporte_Retardos_${reporteData.fechaInicio}_al_${reporteData.fechaFin}.xlsx`);
}

function formatearFecha(fechaStr) {
    const fecha = new Date(fechaStr);
    return fecha.toLocaleDateString('es-MX', { day: '2-digit', month: 'short', year: 'numeric' });
}
</script>

</body>
</html>
