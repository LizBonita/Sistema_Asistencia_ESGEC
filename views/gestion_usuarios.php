<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (
    !isset($_SESSION['user_rol_nombre']) ||
    ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Administrador')
) {
    die('Acceso denegado.');
}

include_once '../config/database.php';
include_once '../models/Usuario.php';
include_once '../models/Rol.php';

$database = new Database();
$db = $database->getConnection();

$usuarioModel = new Usuario($db);
$rolModel = new Rol($db);

$stmtUsuarios = $usuarioModel->readAll();
$usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);

$stmtRoles = $rolModel->readAll();
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

$totalUsuarios = count($usuarios);

$rolesMap = [];
foreach ($roles as $rolItem) {
    $rolesMap[$rolItem['id']] = $rolItem['nombre'];
}

$totalAdministradores = 0;
$totalDirectores = 0;

foreach ($usuarios as $usuario) {
    $rolNombre = $rolesMap[$usuario['rol_id']] ?? 'Sin rol';
    $rolNombreLower = mb_strtolower(trim($rolNombre), 'UTF-8');

    if ($rolNombreLower === 'administrador') {
        $totalAdministradores++;
    }

    if ($rolNombreLower === 'director') {
        $totalDirectores++;
    }
}

function obtenerIniciales(string $nombre): string
{
    $nombre = trim($nombre);

    if ($nombre === '') {
        return 'US';
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

    return $iniciales !== '' ? $iniciales : 'US';
}

$nombreSesion = htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8');
$rolSesion = htmlspecialchars($_SESSION['user_rol_nombre'] ?? 'Sin rol', ENT_QUOTES, 'UTF-8');
$inicialesSesion = htmlspecialchars(obtenerIniciales($_SESSION['user_nombre'] ?? 'Usuario'), ENT_QUOTES, 'UTF-8');

$mensaje = isset($_GET['message']) ? trim((string) $_GET['message']) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de usuarios - Sistema de Asistencia Escolar</title>

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
            --color-borde: rgba(255,255,255,0.16);
            --color-borde-suave: rgba(27,57,106,0.12);

            --color-texto: #0f172a;
            --color-texto-suave: #5f6f87;
            --color-blanco: #ffffff;

            --color-success: #22c55e;
            --color-danger: #dc2626;

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
           HEADER COMPLETO
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

        .welcome-pill i {
            opacity: .95;
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
        .btn-secondary {
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

        .btn-secondary {
            background: rgba(27,57,106,0.08);
            color: var(--color-principal);
            border: 1px solid rgba(27,57,106,0.12);
        }

        body.dark-mode .btn-secondary {
            background: rgba(255,255,255,0.06);
            color: #e5edf9;
            border-color: rgba(255,255,255,0.08);
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
        }

        .flash-message {
            margin: 18px 24px 0;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(34,197,94,0.12);
            border: 1px solid rgba(34,197,94,0.18);
            color: #166534;
            font-weight: 700;
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

        .search-box input:focus {
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

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 9px 12px;
            border-radius: 999px;
            font-size: .84rem;
            font-weight: 800;
            white-space: nowrap;
        }

        .role-admin {
            background: rgba(37, 99, 235, 0.12);
            color: #1d4ed8;
        }

        .role-director {
            background: rgba(34,197,94,0.12);
            color: #15803d;
        }

        .role-default {
            background: rgba(100,116,139,0.12);
            color: #475569;
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

        .btn-edit {
            background: rgba(37,99,235,0.10);
            color: #1d4ed8;
        }

        .btn-edit:hover {
            transform: translateY(-1px);
            background: rgba(37,99,235,0.16);
        }

        .btn-delete {
            background: rgba(220,38,38,0.10);
            color: #b91c1c;
        }

        .btn-delete:hover {
            transform: translateY(-1px);
            background: rgba(220,38,38,0.16);
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
            margin-bottom: 18px;
        }

        /* =========================
           TARJETAS MÓVIL
        ========================= */
        .mobile-cards {
            display: none;
            padding: 0 24px 24px;
            gap: 14px;
        }

        .mobile-user-card {
            background: var(--color-superficie);
            border: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-sm);
            border-radius: 20px;
            padding: 18px;
        }

        .mobile-user-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .mobile-user-content {
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

        /* =========================
           MODAL
        ========================= */
        .modal {
            position: fixed;
            inset: 0;
            z-index: 999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 18px;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            width: 100%;
            max-width: 520px;
            background: var(--color-superficie);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid var(--color-borde-suave);
            box-shadow: var(--sombra-lg);
            animation: modalShow .18s ease;
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
        }

        .modal-header h3 {
            font-size: 1.1rem;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
        }

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
        }

        .modal-close:hover {
            background: rgba(255,255,255,0.22);
            transform: scale(1.03);
        }

        .modal-body {
            padding: 20px;
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
        }

        .input-wrap input,
        .input-wrap select {
            width: 100%;
            height: 50px;
            border-radius: 16px;
            border: 1px solid var(--color-borde-suave);
            background: var(--color-superficie);
            color: var(--color-texto);
            padding: 0 14px 0 42px;
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

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            color: var(--color-texto-suave);
            cursor: pointer;
            font-size: .95rem;
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 18px;
        }

        .modal-message {
            display: none;
            margin-top: 14px;
            padding: 12px 14px;
            border-radius: 14px;
            font-weight: 700;
            font-size: .92rem;
        }

        .modal-message.success {
            display: block;
            background: rgba(34,197,94,0.12);
            color: #166534;
            border: 1px solid rgba(34,197,94,0.18);
        }

        .modal-message.error {
            display: block;
            background: rgba(220,38,38,0.12);
            color: #991b1b;
            border: 1px solid rgba(220,38,38,0.18);
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

            .main-card-actions {
                width: 100%;
            }

            .main-card-actions .btn-main,
            .main-card-actions .btn-secondary {
                width: 100%;
            }

            .tools-row {
                flex-direction: column;
                align-items: stretch;
            }

            .search-box {
                width: 100%;
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

            .modal-content {
                border-radius: 20px;
            }

            .modal-body,
            .modal-header {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>
</head>
<body>

    <!-- HEADER -->
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
                        <p>Secundaria “Emperador Cuauhtémoc” • Clave 12DES0020I</p>
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
                        <h2>Gestión de usuarios</h2>
                        <p>Administra cuentas, revisa roles y controla el acceso al sistema de forma clara y ordenada.</p>
                    </div>

                    <div class="main-card-actions">
                        <button type="button" class="btn-main" id="openModalBtn">
                            <i class="fa-solid fa-user-plus"></i>
                            Agregar nuevo usuario
                        </button>
                    </div>
                </div>

                <?php if ($mensaje !== ''): ?>
                    <div class="flash-message">
                        <i class="fa-solid fa-circle-check"></i>
                        <?php echo htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                <?php endif; ?>

                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Usuarios registrados</small>
                            <strong><?php echo (int) $totalUsuarios; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Administradores</small>
                            <strong><?php echo (int) $totalAdministradores; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-user-shield"></i>
                        </div>
                    </div>

                    <div class="summary-item">
                        <div class="summary-info">
                            <small>Directores</small>
                            <strong><?php echo (int) $totalDirectores; ?></strong>
                        </div>
                        <div class="summary-icon">
                            <i class="fa-solid fa-school"></i>
                        </div>
                    </div>
                </div>

                <?php if ($totalUsuarios > 0): ?>
                    <div class="tools-row">
                        <div class="search-box">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="searchUsers" placeholder="Buscar por nombre, usuario o rol...">
                        </div>
                    </div>

                    <!-- TABLA DESKTOP -->
                    <div class="table-wrapper">
                        <div class="table-scroll">
                            <table id="usersTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre completo</th>
                                        <th>Usuario</th>
                                        <th>Rol</th>
                                        <th>Fecha de registro</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios as $row): ?>
                                        <?php
                                            $id = (int) $row['id'];
                                            $nombreCompleto = (string) ($row['nombre_completo'] ?? '');
                                            $usuarioNombre = (string) ($row['usuario'] ?? '');
                                            $rolNombre = $rolesMap[$row['rol_id']] ?? 'Sin rol';
                                            $fechaRegistro = !empty($row['fecha_registro'])
                                                ? date('d/m/Y h:i A', strtotime($row['fecha_registro']))
                                                : 'Sin fecha';

                                            $rolClass = 'role-default';
                                            $rolLower = mb_strtolower(trim($rolNombre), 'UTF-8');

                                            if ($rolLower === 'administrador') {
                                                $rolClass = 'role-admin';
                                            } elseif ($rolLower === 'director') {
                                                $rolClass = 'role-director';
                                            }

                                            $busqueda = mb_strtolower($nombreCompleto . ' ' . $usuarioNombre . ' ' . $rolNombre, 'UTF-8');
                                        ?>
                                        <tr data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                            <td>#<?php echo $id; ?></td>
                                            <td>
                                                <div class="table-user-cell">
                                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales($nombreCompleto), ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="table-user-data">
                                                        <strong><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></strong>
                                                        <span>Cuenta registrada en el sistema</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td>
                                                <span class="role-badge <?php echo $rolClass; ?>">
                                                    <i class="fa-solid fa-badge-check"></i>
                                                    <?php echo htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="date-chip">
                                                    <i class="fa-regular fa-calendar"></i>
                                                    <?php echo htmlspecialchars($fechaRegistro, ENT_QUOTES, 'UTF-8'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="actions-cell">
                                                    <a class="btn-table btn-edit" href="editar_usuario.php?id=<?php echo $id; ?>">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                        Editar
                                                    </a>

                                                    <a
                                                        class="btn-table btn-delete"
                                                        href="../controllers/EliminarUsuarioController.php?id=<?php echo $id; ?>"
                                                        onclick="return confirm('¿Estás seguro de eliminar al usuario <?php echo addslashes(htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8')); ?>?');"
                                                    >
                                                        <i class="fa-solid fa-trash"></i>
                                                        Eliminar
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- TARJETAS MOBILE -->
                    <div class="mobile-cards" id="mobileCards">
                        <?php foreach ($usuarios as $row): ?>
                            <?php
                                $id = (int) $row['id'];
                                $nombreCompleto = (string) ($row['nombre_completo'] ?? '');
                                $usuarioNombre = (string) ($row['usuario'] ?? '');
                                $rolNombre = $rolesMap[$row['rol_id']] ?? 'Sin rol';
                                $fechaRegistro = !empty($row['fecha_registro'])
                                    ? date('d/m/Y h:i A', strtotime($row['fecha_registro']))
                                    : 'Sin fecha';

                                $rolClass = 'role-default';
                                $rolLower = mb_strtolower(trim($rolNombre), 'UTF-8');

                                if ($rolLower === 'administrador') {
                                    $rolClass = 'role-admin';
                                } elseif ($rolLower === 'director') {
                                    $rolClass = 'role-director';
                                }

                                $busqueda = mb_strtolower($nombreCompleto . ' ' . $usuarioNombre . ' ' . $rolNombre, 'UTF-8');
                            ?>
                            <article class="mobile-user-card" data-search="<?php echo htmlspecialchars($busqueda, ENT_QUOTES, 'UTF-8'); ?>">
                                <div class="mobile-user-top">
                                    <div class="table-user-avatar"><?php echo htmlspecialchars(obtenerIniciales($nombreCompleto), ENT_QUOTES, 'UTF-8'); ?></div>
                                    <div class="table-user-data">
                                        <strong><?php echo htmlspecialchars($nombreCompleto, ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span>ID #<?php echo $id; ?></span>
                                    </div>
                                </div>

                                <div class="mobile-user-content">
                                    <div class="mobile-field">
                                        <small>Usuario</small>
                                        <span><?php echo htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8'); ?></span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Rol</small>
                                        <span class="role-badge <?php echo $rolClass; ?>">
                                            <i class="fa-solid fa-badge-check"></i>
                                            <?php echo htmlspecialchars($rolNombre, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>

                                    <div class="mobile-field">
                                        <small>Fecha de registro</small>
                                        <span class="date-chip">
                                            <i class="fa-regular fa-calendar"></i>
                                            <?php echo htmlspecialchars($fechaRegistro, ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </div>
                                </div>

                                <div class="mobile-actions">
                                    <a class="btn-table btn-edit" href="editar_usuario.php?id=<?php echo $id; ?>">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                        Editar
                                    </a>

                                    <a
                                        class="btn-table btn-delete"
                                        href="../controllers/EliminarUsuarioController.php?id=<?php echo $id; ?>"
                                        onclick="return confirm('¿Estás seguro de eliminar al usuario <?php echo addslashes(htmlspecialchars($usuarioNombre, ENT_QUOTES, 'UTF-8')); ?>?');"
                                    >
                                        <i class="fa-solid fa-trash"></i>
                                        Eliminar
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-users-slash"></i>
                        <h3>No se encontraron usuarios</h3>
                        <p>Aún no hay registros disponibles. Puedes comenzar creando el primer usuario.</p>
                        <button type="button" class="btn-main" id="openModalEmpty">
                            <i class="fa-solid fa-user-plus"></i>
                            Crear primer usuario
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <!-- MODAL -->
    <div class="modal" id="createUserModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fa-solid fa-user-plus"></i>
                    Agregar nuevo usuario
                </h3>
                <button type="button" class="modal-close" id="closeModalBtn">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <p class="modal-description">
                    Completa el formulario para registrar un nuevo usuario dentro del sistema.
                </p>

                <form id="createUserForm" method="POST" action="../controllers/CrearUsuarioController.php">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nombre_completo">Nombre completo</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-id-card"></i>
                                <input type="text" name="nombre_completo" id="nombre_completo" required placeholder="Ej. Juan Pérez López">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="usuario">Nombre de usuario</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user"></i>
                                <input type="text" name="usuario" id="usuario" required placeholder="Ej. juanperez">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Contraseña</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" id="password" required placeholder="********">
                                <button type="button" class="toggle-password" id="togglePassword">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="rol_id">Rol</label>
                            <div class="input-wrap">
                                <i class="fa-solid fa-user-tag"></i>
                                <select name="rol_id" id="rol_id" required>
                                    <option value="" disabled selected>Seleccionar rol...</option>
                                    <?php foreach ($roles as $rol): ?>
                                        <option value="<?php echo (int) $rol['id']; ?>">
                                            <?php echo htmlspecialchars($rol['nombre'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-main" id="submitBtn">
                            <i class="fa-solid fa-user-plus"></i>
                            Guardar usuario
                        </button>

                        <button type="button" class="btn-secondary" id="cancelModalBtn">
                            <i class="fa-solid fa-ban"></i>
                            Cancelar
                        </button>
                    </div>
                </form>

                <div class="modal-message" id="modalMessage"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const body = document.body;
            const themeToggle = document.getElementById('themeToggle');
            const searchInput = document.getElementById('searchUsers');

            const desktopRows = Array.from(document.querySelectorAll('#usersTable tbody tr'));
            const mobileCards = Array.from(document.querySelectorAll('#mobileCards .mobile-user-card'));

            const modal = document.getElementById('createUserModal');
            const openModalBtn = document.getElementById('openModalBtn');
            const openModalEmpty = document.getElementById('openModalEmpty');
            const closeModalBtn = document.getElementById('closeModalBtn');
            const cancelModalBtn = document.getElementById('cancelModalBtn');
            const form = document.getElementById('createUserForm');
            const modalMessage = document.getElementById('modalMessage');
            const passwordInput = document.getElementById('password');
            const togglePassword = document.getElementById('togglePassword');
            const submitBtn = document.getElementById('submitBtn');

            // MODO NOCHE
            const savedTheme = localStorage.getItem('gestion_usuarios_theme');
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
                    localStorage.setItem('gestion_usuarios_theme', isDark ? 'dark' : 'light');

                    themeToggle.innerHTML = isDark
                        ? '<i class="fa-solid fa-sun"></i><span>Modo claro</span>'
                        : '<i class="fa-solid fa-moon"></i><span>Modo noche</span>';
                });
            }

            // BUSCADOR
            function filterItems() {
                if (!searchInput) return;

                const term = searchInput.value.trim().toLowerCase();

                desktopRows.forEach(row => {
                    const text = (row.dataset.search || '').toLowerCase();
                    row.style.display = text.includes(term) ? '' : 'none';
                });

                mobileCards.forEach(card => {
                    const text = (card.dataset.search || '').toLowerCase();
                    card.style.display = text.includes(term) ? '' : 'none';
                });
            }

            if (searchInput) {
                searchInput.addEventListener('input', filterItems);
            }

            // MODAL
            function openModal() {
                if (!modal) return;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';

                setTimeout(() => {
                    const firstField = document.getElementById('nombre_completo');
                    if (firstField) firstField.focus();
                }, 80);
            }

            function closeModal() {
                if (!modal) return;
                modal.classList.remove('active');
                document.body.style.overflow = '';
                resetModalMessage();
            }

            function resetModalMessage() {
                if (!modalMessage) return;
                modalMessage.textContent = '';
                modalMessage.className = 'modal-message';
            }

            function showModalMessage(text, type) {
                if (!modalMessage) return;
                modalMessage.textContent = text;
                modalMessage.className = 'modal-message ' + type;
            }

            if (openModalBtn) openModalBtn.addEventListener('click', openModal);
            if (openModalEmpty) openModalEmpty.addEventListener('click', openModal);
            if (closeModalBtn) closeModalBtn.addEventListener('click', closeModal);
            if (cancelModalBtn) cancelModalBtn.addEventListener('click', closeModal);

            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeModal();
                });
            }

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && modal && modal.classList.contains('active')) {
                    closeModal();
                }
            });

            // PASSWORD
            if (togglePassword && passwordInput) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    togglePassword.innerHTML = isPassword
                        ? '<i class="fa-solid fa-eye-slash"></i>'
                        : '<i class="fa-solid fa-eye"></i>';
                });
            }

            // ENVÍO FORMULARIO
            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    resetModalMessage();

                    const originalText = submitBtn.innerHTML;
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando...';

                    const iframe = document.createElement('iframe');
                    iframe.name = 'submit-target-' + Date.now();
                    iframe.style.display = 'none';
                    document.body.appendChild(iframe);

                    form.target = iframe.name;
                    form.method = 'POST';
                    form.action = '../controllers/CrearUsuarioController.php';

                    let handled = false;

                    function onSuccess() {
                        if (handled) return;
                        handled = true;

                        showModalMessage('✅ Usuario creado correctamente. Recargando vista...', 'success');

                        setTimeout(() => {
                            closeModal();
                            window.location.reload();
                        }, 1200);
                    }

                    function onError() {
                        if (handled) return;
                        handled = true;

                        showModalMessage('Ocurrió un problema al registrar el usuario. Verifica la información e inténtalo nuevamente.', 'error');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }

                    iframe.onload = function () {
                        try {
                            const doc = iframe.contentDocument || iframe.contentWindow.document;
                            const text = (doc.body && doc.body.textContent) ? doc.body.textContent.trim().toLowerCase() : '';

                            if (text.includes('error') || text.includes('warning') || text.includes('denegado')) {
                                onError();
                            } else {
                                onSuccess();
                            }
                        } catch (error) {
                            onSuccess();
                        } finally {
                            setTimeout(() => {
                                iframe.remove();
                            }, 1000);
                        }
                    };

                    form.submit();

                    setTimeout(() => {
                        if (!handled) {
                            onSuccess();
                        }
                    }, 2800);
                });
            }
        });
    </script>
</body>
</html>