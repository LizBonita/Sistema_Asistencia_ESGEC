<?php
// views/inicio.php
session_start();
$user_logged_in = isset($_SESSION['user_id']);

$user_nombre = $user_logged_in ? ($_SESSION['user_nombre'] ?? '') : '';
$user_rol    = $user_logged_in ? ($_SESSION['user_rol_nombre'] ?? '') : '';

// ===== Iniciales seguras (sin depender de mbstring) =====
function _firstChar($s) {
  $s = trim((string)$s);
  if ($s === '') return '';
  if (function_exists('mb_substr')) return mb_substr($s, 0, 1, 'UTF-8');
  return substr($s, 0, 1);
}
function _upper($s) {
  if (function_exists('mb_strtoupper')) return mb_strtoupper($s, 'UTF-8');
  return strtoupper($s);
}
function obtenerIniciales(string $nombre): string {
  $nombre = trim(preg_replace('/\s+/', ' ', $nombre));
  if ($nombre === '') return '??';

  $partes = explode(' ', $nombre);
  $ini = '';

  $ini .= _upper(_firstChar($partes[0]));
  if (count($partes) >= 2) {
    $ini .= _upper(_firstChar($partes[count($partes) - 1]));
  } else {
    $ini .= _upper(_firstChar($partes[0]));
  }

  return $ini;
}

$user_iniciales = $user_logged_in ? obtenerIniciales($user_nombre) : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Asistencia - Escuela Secundaria "Emperador Cuauhtémoc"</title>

  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    html{ scroll-behavior:smooth; }

    /* ==========================================================
       THEME TOKENS (LIGHT por defecto)
    ========================================================== */
    :root{
      --verde:#009B48;
      --azul:#0E4D92;
      --azul2:#0A3A6F;

      --shadow: 0 18px 46px rgba(0,0,0,.18);
      --shadow-soft: 0 12px 30px rgba(0,0,0,.10);
      --radius: 18px;
      --radius-lg: 26px;
      --ring: 0 0 0 4px rgba(14,77,146,.16);

      --page-bg:
        radial-gradient(900px 480px at 10% -10%, rgba(0,155,72,.14), transparent 55%),
        radial-gradient(900px 480px at 90% -10%, rgba(14,77,146,.16), transparent 60%),
        linear-gradient(180deg, #ffffff 0%, #f3f6f9 60%, #eef2f6 100%);
      --page-text:#0b1220;
      --page-muted:#5f6b7a;

      --glass-bg: rgba(255,255,255,.14);
      --glass-border: rgba(255,255,255,.18);

      --topbar-bg: linear-gradient(135deg, rgba(14,77,146,.92), rgba(0,155,72,.86));
      --topbar-text: #ffffff;

      --panel-bg: rgba(255,255,255,.97);
      --panel-border: rgba(14,77,146,.14);
      --panel-text: var(--azul2);
      --panel-muted: var(--page-muted);

      --menu-item-bg: #ffffff;
      --menu-item-border: rgba(14,77,146,.14);
      --menu-item-hover-bg: rgba(14,77,146,.04);
      --menu-item-text: var(--azul2);

      --search-bg: rgba(14,77,146,.05);
      --search-border: rgba(14,77,146,.12);
      --search-text: var(--azul2);

      --card-bg: rgba(255,255,255,.90);
      --card-border: rgba(14,77,146,.14);
      --card-title: var(--azul2);
      --card-muted: var(--page-muted);

      --section-bg: rgba(255,255,255,.92);
      --feature-bg: rgba(255,255,255,.96);
      --feature-border: rgba(14,77,146,.14);
    }

    /* ==========================================================
       DARK THEME OVERRIDES
    ========================================================== */
    :root[data-theme="dark"]{
      --page-bg:
        radial-gradient(900px 520px at 10% 0%, rgba(0,155,72,.18), transparent 60%),
        radial-gradient(900px 520px at 90% 0%, rgba(14,77,146,.20), transparent 60%),
        linear-gradient(180deg, #060914, #0B1020);
      --page-text:#EAF0FF;
      --page-muted: rgba(234,240,255,.72);

      --glass-bg: rgba(255,255,255,.08);
      --glass-border: rgba(255,255,255,.14);

      --topbar-bg: linear-gradient(135deg, rgba(8,12,26,.78), rgba(8,12,26,.42));
      --topbar-text:#EAF0FF;

      --panel-bg: rgba(10,14,28,.92);
      --panel-border: rgba(255,255,255,.14);
      --panel-text: #EAF0FF;
      --panel-muted: rgba(234,240,255,.72);

      --menu-item-bg: rgba(255,255,255,.06);
      --menu-item-border: rgba(255,255,255,.10);
      --menu-item-hover-bg: rgba(255,255,255,.10);
      --menu-item-text: #EAF0FF;

      --search-bg: rgba(255,255,255,.06);
      --search-border: rgba(255,255,255,.10);
      --search-text: #EAF0FF;

      --card-bg: rgba(255,255,255,.06);
      --card-border: rgba(255,255,255,.12);
      --card-title: #EAF0FF;
      --card-muted: rgba(234,240,255,.70);

      --section-bg: rgba(255,255,255,.06);
      --feature-bg: rgba(255,255,255,.06);
      --feature-border: rgba(255,255,255,.10);
    }

    *{margin:0;padding:0;box-sizing:border-box;}
    html,body{height:100%;}
    body{
      font-family:'Roboto',sans-serif;
      color:var(--page-text);
      background: var(--page-bg);
      line-height:1.6;
      overflow-x:hidden;
    }
    a{color:inherit;text-decoration:none;}
    .inicio-container{min-height:100vh;display:flex;flex-direction:column;}

    .wrap{
      width: min(1180px, calc(100% - 32px));
      margin: 0 auto;
    }

    /* =========================
       TOPBAR (GRID fijo)
    ========================= */
    .topbar{
      position: sticky;
      top: 0;
      z-index: 100;
      background: var(--topbar-bg);
      border-bottom: 1px solid var(--glass-border);
      box-shadow: 0 14px 30px rgba(0,0,0,.18);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      isolation:isolate;
    }

    .scroll-progress{
      position:absolute;
      left:0; bottom:0;
      height: 3px;
      width: 0%;
      background: linear-gradient(90deg, var(--azul), var(--verde));
      box-shadow: 0 6px 16px rgba(0,0,0,.22);
      transition: width .08s linear;
    }

    .topbar-inner{
      padding: 12px 0;
      display: grid;
      grid-template-columns: 1fr auto;
      grid-template-rows: auto auto;
      grid-template-areas:
        "brand rightTop"
        "welcome rightBottom";
      gap: 10px 12px;
      align-items: center;
      color: var(--topbar-text);
    }

    .brand{ grid-area: brand; display:flex; align-items:center; gap: 12px; min-width: 0; }

    .welcome-slot{ grid-area: welcome; display:flex; align-items:center; min-width:0; }
    .rightTop{
      grid-area: rightTop;
      display:flex;
      align-items:center;
      justify-content:flex-end;
      gap:10px;
      flex-wrap:wrap;
    }
    .rightBottom{
      grid-area: rightBottom;
      display:flex;
      align-items:center;
      justify-content:flex-end;
      gap:10px;
      flex-wrap:wrap;
    }

    .brand-logos{
      display:flex;
      align-items:center;
      gap:12px;
      padding: 10px 12px;
      border-radius: 16px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      box-shadow: 0 10px 24px rgba(0,0,0,.12);
    }

    .brand-logos img{
      height: 46px;
      width:auto;
      object-fit:contain;
      filter: drop-shadow(0 6px 10px rgba(0,0,0,.22));
      display:block;
    }

    .brand-text{
      color: var(--topbar-text);
      min-width:0;
      display:flex;
      flex-direction:column;
      gap: 2px;
    }
    .brand-text .title{
      font-weight: 900;
      letter-spacing:.2px;
      font-size: 1.05rem;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }
    .brand-text .subtitle{
      opacity:.92;
      font-weight: 700;
      font-size: .86rem;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
    }

    .pill{
      display:inline-flex;
      align-items:center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 999px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      box-shadow: 0 10px 24px rgba(0,0,0,.12);
      color: var(--topbar-text);
      font-weight: 900;
      font-size:.92rem;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      max-width: 620px;
    }

    .status-pill{
      display:inline-flex;
      align-items:center;
      gap: 10px;
      padding: 10px 12px;
      border-radius: 999px;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      box-shadow: 0 10px 24px rgba(0,0,0,.12);
      color: var(--topbar-text);
      font-weight: 900;
      font-size:.90rem;
      white-space:nowrap;
    }
    .dot{
      width:10px;height:10px;border-radius:999px;
      background: #30d158;
      box-shadow: 0 0 0 4px rgba(48,209,88,.18);
    }

    .btn{
      border:none;
      cursor:pointer;
      text-decoration:none;

      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 10px;

      padding: 10px 14px;
      border-radius: 999px;
      font-weight: 900;
      transition: transform .18s ease, background .18s ease, box-shadow .18s ease, filter .18s ease, border-color .18s ease;
      box-shadow: 0 14px 30px rgba(0,0,0,.18);
      white-space:nowrap;
      outline:none;

      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--topbar-text);
    }
    .btn:focus{ box-shadow: var(--ring); }
    .btn:hover{ transform: translateY(-1px); background: rgba(255,255,255,.18); }

    .btn-login-modal{
      background: rgba(255,255,255,.92);
      color: var(--azul2);
      border: none;
    }

    .btn-logout{
      background: var(--glass-bg);
      color: var(--topbar-text);
      border: 1px solid var(--glass-border);
    }

    /* THEME TOGGLE */
    .btn-theme [data-icon="sun"]{ display:none; }
    :root[data-theme="dark"] .btn-theme [data-icon="sun"]{ display:inline-block; }
    :root[data-theme="dark"] .btn-theme [data-icon="moon"]{ display:none; }

    /* ==========================================================
       AVATAR en pill
    ========================================================== */
    .pill .avatar{
      width: 38px;
      height: 38px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      font-weight: 1000;
      letter-spacing: .5px;
      flex-shrink: 0;

      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 12px 26px rgba(0,0,0,.18);
      border: 1px solid rgba(255,255,255,.22);
    }
    .pill .role-tag{
      opacity:.95;
      font-weight: 1000;
    }

    /* =========================
       MENU DESPLEGABLE
    ========================= */
    .modules-wrap{ position: relative; display:inline-flex; align-items:center; }

    .btn-modules-toggle{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding: 10px 14px;
      border-radius: 999px;

      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--topbar-text);

      font-weight: 900;
      font-size: .93rem;

      transition: transform .18s ease, background .18s ease;
      box-shadow: 0 14px 30px rgba(0,0,0,.18);
      cursor:pointer;
      outline:none;
    }
    .btn-modules-toggle:hover{ transform: translateY(-1px); background: rgba(255,255,255,.20); }
    .btn-modules-toggle:focus{ box-shadow: var(--ring); }
    .btn-modules-toggle .modules-chevron{ transition: transform .2s ease; }
    .btn-modules-toggle.is-open .modules-chevron{ transform: rotate(180deg); }

    .modules-panel{
      position:absolute;
      top: calc(100% + 12px);
      right: 0;

      width: min(620px, calc(100vw - 28px));
      overflow:hidden;

      max-height: 0;
      opacity: 0;
      transform: translateY(-10px) scale(.985);
      transition: max-height .28s ease, opacity .22s ease, transform .22s ease;

      border-radius: 20px;
      box-shadow: 0 26px 70px rgba(0,0,0,.30);
      background: var(--panel-bg);
      border: 1px solid var(--panel-border);
      z-index: 250;
    }
    .modules-panel.is-open{
      max-height: 70vh;
      opacity: 1;
      transform: translateY(0) scale(1);
    }

    .modules-header{
      padding: 14px 14px 12px;
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 10px;

      background:
        radial-gradient(700px 220px at 10% 0%, rgba(0,155,72,.12), transparent 60%),
        radial-gradient(700px 220px at 90% 0%, rgba(14,77,146,.12), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.99), rgba(255,255,255,.94));
      border-bottom: 1px solid rgba(14,77,146,.10);
    }
    :root[data-theme="dark"] .modules-header{
      background:
        radial-gradient(700px 220px at 10% 0%, rgba(0,155,72,.14), transparent 60%),
        radial-gradient(700px 220px at 90% 0%, rgba(14,77,146,.14), transparent 60%),
        rgba(255,255,255,.03);
      border-bottom: 1px solid rgba(255,255,255,.10);
    }

    .modules-header .left{
      display:flex;
      flex-direction:column;
      gap: 6px;
      min-width: 0;
      flex: 1;
    }
    .modules-header strong{
      color: var(--panel-text);
      font-size: 1rem;
      letter-spacing:.2px;
      display:flex;
      align-items:center;
      gap: 10px;
      font-weight: 900;
    }
    .modules-header small{
      color: var(--panel-muted);
      font-weight: 700;
    }

    .modules-search{
      margin-top: 6px;
      display:flex;
      align-items:center;
      gap: 10px;
      background: var(--search-bg);
      border: 1px solid var(--search-border);
      border-radius: 14px;
      padding: 10px 12px;
    }
    .modules-search i{ color: var(--search-text); opacity:.9; }
    .modules-search input{
      width: 100%;
      border:none;
      outline:none;
      background: transparent;
      font-weight: 700;
      color: var(--search-text);
    }
    .modules-search input::placeholder{ color: var(--panel-muted); font-weight: 700; }

    .modules-header .badge{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 8px 10px;
      border-radius: 999px;
      background: rgba(14,77,146,.06);
      border: 1px solid rgba(14,77,146,.12);
      color: var(--panel-text);
      font-weight: 900;
      font-size: .82rem;
      white-space:nowrap;
      flex-shrink:0;
    }
    :root[data-theme="dark"] .modules-header .badge{
      background: rgba(255,255,255,.06);
      border: 1px solid rgba(255,255,255,.10);
    }

    .menu-modulos-grid{
      display:grid;
      grid-template-columns: 1fr;
      gap: 10px;
      padding: 14px;
      max-height: calc(70vh - 120px);
      overflow:auto;
      overscroll-behavior: contain;
      -webkit-overflow-scrolling: touch;
    }
    @media (min-width: 920px){
      .menu-modulos-grid{ grid-template-columns: repeat(2, 1fr); }
    }

    .menu-item{
      display:flex;
      align-items:center;
      gap: 12px;
      padding: 12px 14px;
      border-radius: 16px;

      background: var(--menu-item-bg);
      border: 1px solid var(--menu-item-border);
      color: var(--menu-item-text);
      font-weight: 900;

      transition: transform .18s ease, border-color .18s ease, background .18s ease, box-shadow .18s ease;
      box-shadow: 0 10px 22px rgba(0,0,0,.06);
      will-change: transform;
    }
    .menu-item:hover{
      transform: translateX(4px);
      background: var(--menu-item-hover-bg);
      box-shadow: 0 18px 42px rgba(0,0,0,.12);
    }
    .menu-item i{
      width: 46px;
      height: 46px;
      border-radius: 16px;
      display:grid;
      place-items:center;

      background: linear-gradient(135deg, var(--azul), var(--verde));
      color:#fff;
      font-size: 1.15rem;
      flex-shrink:0;
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
    }

    /* =========================
       HERO + PARALLAX
    ========================= */
    .hero{
      position: relative;
      margin: 18px auto 0;
      border-radius: var(--radius-lg);
      overflow: hidden;
      min-height: 640px;
      box-shadow: var(--shadow);

      background-image: url('../assets/img/fachada2.jpeg');
      background-size: cover;
      background-position: center 0px;
      background-repeat: no-repeat;
      isolation: isolate;

      width: min(1180px, calc(100% - 32px));
    }

    .hero::before{
      content:"";
      position:absolute;
      inset:0;
      background:
        radial-gradient(900px 440px at 15% 10%, rgba(0,155,72,.58), transparent 55%),
        radial-gradient(900px 440px at 85% 0%, rgba(14,77,146,.78), transparent 55%),
        linear-gradient(180deg, rgba(0,0,0,.32), rgba(0,0,0,.70));
      z-index:0;
    }
    :root[data-theme="dark"] .hero::before{
      background:
        radial-gradient(900px 480px at 15% 10%, rgba(0,155,72,.45), transparent 60%),
        radial-gradient(900px 480px at 85% 0%, rgba(14,77,146,.60), transparent 60%),
        linear-gradient(180deg, rgba(0,0,0,.26), rgba(0,0,0,.82));
    }

    .hero::after{
      content:"";
      position:absolute;
      inset:-80px;
      background:
        radial-gradient(circle at 18% 20%, rgba(255,255,255,.16), transparent 45%),
        radial-gradient(circle at 80% 30%, rgba(255,255,255,.12), transparent 52%),
        radial-gradient(circle at 55% 85%, rgba(255,255,255,.08), transparent 52%);
      opacity:.65;
      z-index:0;
      pointer-events:none;
      animation: glowMove 10s ease-in-out infinite;
    }
    @keyframes glowMove{
      0%{ transform: translate(0,0); opacity:.55; }
      50%{ transform: translate(18px,-10px); opacity:.70; }
      100%{ transform: translate(0,0); opacity:.55; }
    }

    .hero .grain{
      position:absolute;
      inset:0;
      z-index:0;
      pointer-events:none;
      opacity:.12;
      background:
        repeating-linear-gradient(135deg,
          rgba(255,255,255,.12) 0px,
          rgba(255,255,255,.12) 1px,
          transparent 1px,
          transparent 10px);
      mix-blend-mode: overlay;
    }

    .hero-inner{
      position:relative;
      z-index:1;
      padding: 34px 22px 18px;
      display:grid;
      grid-template-columns: 1.15fr .85fr;
      gap: 16px;
      align-items: stretch;
    }

    .hero-title{
      color:#fff;
      font-size: clamp(2.0rem, 3.6vw, 3.1rem);
      line-height:1.10;
      font-weight: 900;
      margin-bottom: 10px;
      letter-spacing:.2px;
      text-shadow: 0 18px 42px rgba(0,0,0,.38);
    }
    .hero-sub{
      color: rgba(255,255,255,.92);
      font-size: 1.06rem;
      max-width: 860px;
      font-weight: 600;
    }

    .hero-chips{
      display:flex;
      flex-wrap:wrap;
      gap: 10px;
      margin-top: 14px;
    }
    .chip{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 999px;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.18);
      color:#fff;
      font-weight: 900;
      box-shadow: 0 10px 24px rgba(0,0,0,.14);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }

    .hero-actions{
      margin-top: 16px;
      display:flex;
      gap: 10px;
      flex-wrap:wrap;
    }

    .btn-hero{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap:10px;
      padding: 12px 16px;
      border-radius: 999px;
      font-weight: 900;
      border: 1px solid rgba(255,255,255,.20);
      box-shadow: 0 16px 34px rgba(0,0,0,.20);
      cursor:pointer;
      transition: transform .18s ease, background .18s ease, filter .18s ease;
      white-space:nowrap;
      outline:none;
      color:#fff;
      background: rgba(255,255,255,.12);
    }
    .btn-hero.primary{
      background: rgba(255,255,255,.92);
      color: var(--azul2);
      border: none;
    }
    .btn-hero.primary:hover{ transform: translateY(-1px); background:#fff; }
    .btn-hero.ghost:hover{ transform: translateY(-1px); background: rgba(255,255,255,.18); }

    .hero-card{
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.18);
      border-radius: 22px;
      box-shadow: 0 22px 60px rgba(0,0,0,.28);
      padding: 16px;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      color:#fff;
    }
    .hero-card h3{
      font-size: 1.05rem;
      font-weight: 900;
      margin-bottom: 10px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .hero-card ul{ list-style:none; display:flex; flex-direction:column; gap: 10px; }
    .hero-card li{ display:flex; gap: 10px; align-items:flex-start; color: rgba(255,255,255,.92); font-weight: 700; }
    .hero-card li i{ margin-top: 2px; }

    .scroll-indicator{
      position:absolute;
      left: 50%;
      bottom: 12px;
      transform: translateX(-50%);
      z-index:2;
      display:flex;
      align-items:center;
      gap:10px;
      color: rgba(255,255,255,.85);
      font-weight: 800;
      font-size: .90rem;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.16);
      padding: 10px 12px;
      border-radius: 999px;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }
    .mouse{
      width: 18px;
      height: 26px;
      border-radius: 12px;
      border: 2px solid rgba(255,255,255,.70);
      position:relative;
    }
    .mouse::after{
      content:"";
      width: 4px;
      height: 6px;
      border-radius: 4px;
      background: rgba(255,255,255,.80);
      position:absolute;
      left: 50%;
      top: 5px;
      transform: translateX(-50%);
      animation: wheel 1.2s ease-in-out infinite;
    }
    @keyframes wheel{
      0%{ opacity:.2; transform: translate(-50%, 0); }
      50%{ opacity:1; transform: translate(-50%, 8px); }
      100%{ opacity:.2; transform: translate(-50%, 0); }
    }

    /* ==========================================================
       MINI DASHBOARD (solo logueado)
    ========================================================== */
    .dashboard{
      width: min(1180px, calc(100% - 32px));
      margin: 14px auto 0;
      border-radius: 20px;
      background: var(--section-bg);
      border: 1px solid var(--feature-border);
      box-shadow: var(--shadow-soft);
      padding: 18px 16px;
    }

    .dashboard-head{
      display:flex;
      align-items:flex-start;
      justify-content:space-between;
      gap: 12px;
      margin-bottom: 12px;
    }

    .dashboard-head .title{
      display:flex;
      align-items:center;
      gap: 10px;
      font-weight: 1000;
      font-size: 1.25rem;
      color: var(--card-title);
    }

    .dashboard-head .subtitle{
      color: var(--page-muted);
      font-weight: 700;
      margin-top: 4px;
    }

    .dash-actions{
      display:flex;
      align-items:center;
      gap: 10px;
      flex-wrap: wrap;
      justify-content:flex-end;
    }

    .dash-actions a{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 999px;
      font-weight: 900;

      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--page-text);
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
      transition: transform .18s ease, background .18s ease;
    }
    .dash-actions a:hover{
      transform: translateY(-1px);
      background: rgba(255,255,255,.18);
    }

    .dash-grid{
      display:grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 12px;
    }

    .dash-card{
      position:relative;
      border-radius: 18px;
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      box-shadow: 0 12px 26px rgba(0,0,0,.08);
      padding: 14px;

      display:flex;
      flex-direction:column;
      gap: 10px;

      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .5s ease;
      overflow:hidden;

      opacity: 0;
      transform: translateY(10px);
    }
    .dash-card.is-visible{
      opacity: 1;
      transform: translateY(0);
    }

    .dash-card::before{
      content:"";
      position:absolute;
      left:0; top:0; bottom:0;
      width: 6px;
      background: linear-gradient(180deg, var(--azul), var(--verde));
      opacity: .9;
    }

    .dash-card:hover{
      transform: translateY(-2px);
      box-shadow: 0 20px 45px rgba(0,0,0,.14);
      border-color: rgba(14,77,146,.22);
    }

    .dash-top{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
    }

    .dash-ic{
      width: 46px;
      height: 46px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 12px 26px rgba(0,0,0,.12);
      flex-shrink:0;
    }

    .dash-meta .kpi{
      font-size: 1.55rem;
      font-weight: 1000;
      line-height: 1.05;
      color: var(--card-title);
    }

    .dash-meta .label{
      color: var(--page-muted);
      font-weight: 800;
      margin-top: 2px;
    }

    .dash-foot{
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
      margin-top: 2px;
    }

    .dash-foot .hint{
      color: var(--page-muted);
      font-weight: 700;
      font-size: .92rem;
    }

    .dash-foot .go{
      display:inline-flex;
      align-items:center;
      gap: 8px;
      font-weight: 1000;
      color: var(--azul2);
      opacity: .95;
    }
    :root[data-theme="dark"] .dash-foot .go{ color: #EAF0FF; }

    @media (max-width: 1050px){
      .dash-grid{ grid-template-columns: repeat(2, minmax(0,1fr)); }
    }

    @media (max-width: 768px){
      .dashboard{ width: calc(100% - 28px); }
      .dash-grid{ grid-template-columns: 1fr; }
      .dashboard-head{ flex-direction: column; align-items: stretch; }
      .dash-actions{ justify-content: flex-start; }
    }

    /* =========================
       STATS + BENEFICIOS
    ========================= */
    .stats{
      width: min(1180px, calc(100% - 32px));
      margin: -16px auto 0;
      position:relative;
      z-index: 3;
      padding-bottom: 6px;
    }

    .stats-grid{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }

    .stat-card{
      background: var(--card-bg);
      border: 1px solid var(--card-border);
      border-radius: 20px;
      box-shadow: var(--shadow-soft);
      padding: 14px;

      display:flex;
      align-items:center;
      gap: 12px;

      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);

      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .5s ease;
      opacity: 0;
      transform: translateY(10px);
    }
    .stat-card.is-visible{
      opacity: 1;
      transform: translateY(0);
    }

    .stat-ic{
      width: 50px;
      height: 50px;
      border-radius: 18px;
      display:grid;
      place-items:center;
      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
      flex-shrink:0;
      font-size: 1.2rem;
    }
    .stat-meta .num{
      font-weight: 900;
      font-size: 1.45rem;
      color: var(--card-title);
      line-height:1.1;
    }
    .stat-meta .label{
      color: var(--card-muted);
      font-weight: 800;
      font-size: .92rem;
      margin-top: 2px;
    }
    .stat-meta .hint{
      color: var(--card-muted);
      font-weight: 700;
      font-size: .84rem;
      margin-top: 2px;
      white-space:nowrap;
      overflow:hidden;
      text-overflow:ellipsis;
      max-width: 520px;
      opacity: .92;
    }

    .section{
      width: min(1180px, calc(100% - 32px));
      margin: 14px auto 0;
      border-radius: 20px;
      background: var(--section-bg);
      border: 1px solid var(--feature-border);
      box-shadow: var(--shadow-soft);
      padding: 20px 16px;
    }

    .section-title{
      color: var(--card-title);
      font-weight: 900;
      font-size: 1.35rem;
      display:flex;
      align-items:center;
      gap: 10px;
      margin-bottom: 12px;
    }

    .grid4{
      display:grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
    }

    .feature{
      background: var(--feature-bg);
      border: 1px solid var(--feature-border);
      border-radius: 18px;
      padding: 14px;
      box-shadow: 0 10px 22px rgba(0,0,0,.07);
      transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .5s ease;
      opacity: 0;
      transform: translateY(10px);
      color: var(--page-text);
    }
    .feature.is-visible{ opacity: 1; transform: translateY(0); }
    .feature:hover{ transform: translateY(-2px); box-shadow: 0 18px 40px rgba(0,0,0,.12); }
    .feature .icon{
      width: 46px;
      height: 46px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      color:#fff;
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
      margin-bottom: 10px;
      font-size: 1.15rem;
    }
    .feature h4{ margin: 0 0 6px; font-weight: 900; }
    .feature p{ margin: 0; color: var(--page-muted); font-weight: 600; font-size: .95rem; }

    /* =========================
       SOPORTE + IR ARRIBA
    ========================= */
    .support-fab{
      position: fixed;
      right: 16px;
      bottom: 16px;
      z-index: 1400;
      display:flex;
      align-items:center;
      gap: 10px;
      padding: 12px 14px;
      border-radius: 18px;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      color:#fff;
      border: 1px solid rgba(255,255,255,.20);
      box-shadow: 0 20px 50px rgba(0,0,0,.26);
      cursor:pointer;
      transition: transform .18s ease, filter .18s ease;
      outline:none;
    }
    .support-fab:hover{ transform: translateY(-2px); filter: brightness(1.03); }
    .support-fab .label{ font-weight: 900; white-space:nowrap; }

    .to-top{
      position: fixed;
      left: 16px;
      bottom: 16px;
      z-index: 1400;
      width: 54px;
      height: 54px;
      border-radius: 18px;
      display:grid;
      place-items:center;
      cursor:pointer;

      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color: var(--page-text);
      box-shadow: 0 20px 50px rgba(0,0,0,.22);

      opacity: 0;
      pointer-events:none;
      transform: translateY(8px);
      transition: opacity .2s ease, transform .2s ease, background .2s ease;
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
    }
    :root[data-theme="dark"] .to-top{ color: #EAF0FF; }
    .to-top.is-visible{ opacity: 1; pointer-events:auto; transform: translateY(0); }
    .to-top:hover{ background: rgba(255,255,255,.18); }

    /* =========================
       MODAL SOPORTE
    ========================= */
    .support-modal{
      display:none;
      position: fixed;
      inset: 0;
      z-index: 1500;
      background: rgba(0,0,0,.55);
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      padding: 18px;
    }
    .support-dialog{
      width: min(560px, 100%);
      margin: 10vh auto 0;
      background: var(--panel-bg);
      border: 1px solid var(--panel-border);
      border-radius: 22px;
      box-shadow: 0 26px 70px rgba(0,0,0,.30);
      overflow:hidden;
      animation: pop .18s ease;
      color: var(--panel-text);
    }
    .support-head{
      padding: 16px;
      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
    }
    .support-head h3{
      margin:0;
      font-size: 1.05rem;
      font-weight: 900;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .support-close{
      width: 42px;
      height: 42px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      color:#fff;
      font-size: 22px;
      font-weight: 900;
      cursor:pointer;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      line-height:1;
    }
    .support-body{
      padding: 16px;
      background:
        radial-gradient(700px 240px at 15% 0%, rgba(0,155,72,.10), transparent 60%),
        radial-gradient(700px 240px at 85% 0%, rgba(14,77,146,.10), transparent 60%),
        rgba(255,255,255,.02);
    }
    .support-card{
      background: var(--menu-item-bg);
      border: 1px solid var(--menu-item-border);
      border-radius: 18px;
      box-shadow: 0 10px 22px rgba(0,0,0,.07);
      padding: 14px;
      margin-bottom: 10px;
    }
    .support-row{ display:flex; align-items:flex-start; gap: 12px; }
    .support-ic{
      width: 46px;
      height: 46px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
      flex-shrink:0;
    }
    .support-txt strong{ display:block; font-weight: 900; margin-bottom: 2px; }
    .support-txt p{ margin:0; color: var(--panel-muted); font-weight: 700; }

    .support-actions{
      display:flex;
      gap: 10px;
      flex-wrap:wrap;
      margin-top: 12px;
    }
    .support-actions a{
      display:inline-flex;
      align-items:center;
      justify-content:center;
      gap: 8px;
      padding: 10px 12px;
      border-radius: 999px;
      font-weight: 900;
      border: 1px solid var(--menu-item-border);
      background: var(--menu-item-hover-bg);
      color: var(--panel-text);
      transition: transform .18s ease, background .18s ease;
    }
    .support-actions a:hover{ transform: translateY(-1px); background: rgba(255,255,255,.18); }

    /* =========================
       FOOTER
    ========================= */
    footer{
      margin-top: 18px;
      background: var(--topbar-bg);
      color:#fff;
      padding: 24px 0;
      border-top: 1px solid var(--glass-border);
    }
    .footer-content{
      display:grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 14px;
      align-items:flex-start;
    }
    .footer-column{
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      border-radius: 18px;
      padding: 14px;
      box-shadow: 0 12px 26px rgba(0,0,0,.10);
    }
    .footer-column h3{
      color:#fff;
      font-weight: 900;
      margin-bottom: 10px;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .footer-column h3:before{
      content:"";
      width: 10px;
      height: 10px;
      border-radius: 999px;
      background:#fff;
      opacity:.85;
    }
    .footer-column p,
    .footer-column a{
      color: rgba(255,255,255,.88);
      display:block;
      margin: 6px 0;
      font-size: .95rem;
      font-weight: 600;
    }
    .footer-column a:hover{ color:#fff; text-decoration: underline; }
    .footer-social{
      display:flex;
      justify-content:center;
      gap: 12px;
      margin-top: 14px;
    }
    .footer-social a{
      width: 44px;
      height: 44px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      background: var(--glass-bg);
      border: 1px solid var(--glass-border);
      color:#fff;
      transition: transform .18s ease, background .18s ease;
    }
    .footer-social a:hover{ transform: translateY(-2px); background: rgba(255,255,255,.18); }
    .footer-copyright{
      margin-top: 14px;
      text-align:center;
      color: rgba(255,255,255,.82);
      font-size: .92rem;
      font-weight: 600;
    }

    /* =========================
       MODAL LOGIN (Huella + Reloj)
    ========================= */
    .modal{
      display:none;
      position:fixed;
      inset:0;
      z-index:1000;
      background: rgba(0,0,0,.55);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      padding: 18px;
    }
    .modal-content{
      background: rgba(255,255,255,.96);
      margin: 6vh auto 0;
      padding: 0;
      border: 1px solid rgba(14,77,146,.18);
      border-radius: 26px;
      width: 92%;
      max-width: 440px;
      box-shadow: 0 26px 70px rgba(0,0,0,.25);
      overflow:hidden;
      position:relative;
      animation: pop .22s ease;
    }
    :root[data-theme="dark"] .modal-content{
      background: rgba(10,14,28,.96);
      border-color: rgba(255,255,255,.14);
    }
    @keyframes pop{ from{ transform: translateY(12px) scale(.97); opacity:.8;} to{ transform: translateY(0) scale(1); opacity:1;} }
    .modal-header{
      background: linear-gradient(135deg, var(--azul), var(--verde));
      color:#fff;
      padding: 18px 20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      gap: 10px;
    }
    .modal-header h2{
      margin:0;
      font-size: 1.1rem;
      font-weight: 900;
      display:flex;
      align-items:center;
      gap: 10px;
    }
    .close{
      width: 42px;
      height: 42px;
      border-radius: 16px;
      display:grid;
      place-items:center;
      color:#fff;
      font-size: 24px;
      font-weight: 900;
      cursor:pointer;
      background: rgba(255,255,255,.14);
      border: 1px solid rgba(255,255,255,.22);
      line-height:1;
      transition: background .18s;
    }
    .close:hover{ background: rgba(255,255,255,.25); }

    .modal-body{
      padding: 24px 20px 22px;
      background:
        radial-gradient(700px 240px at 15% 0%, rgba(0,155,72,.10), transparent 60%),
        radial-gradient(700px 240px at 85% 0%, rgba(14,77,146,.10), transparent 60%),
        linear-gradient(180deg, rgba(255,255,255,.98), rgba(255,255,255,.90));
      text-align:center;
    }
    :root[data-theme="dark"] .modal-body{
      background:
        radial-gradient(700px 240px at 15% 0%, rgba(0,155,72,.14), transparent 60%),
        radial-gradient(700px 240px at 85% 0%, rgba(14,77,146,.14), transparent 60%),
        rgba(255,255,255,.03);
    }

    /* ── Reloj del modal ── */
    .login-clock{
      font-size: 3.2rem;
      font-weight: 900;
      color: var(--azul2);
      font-variant-numeric: tabular-nums;
      letter-spacing: 1px;
      line-height: 1;
      margin-bottom: 4px;
    }
    :root[data-theme="dark"] .login-clock{ color: #EAF0FF; }

    .login-clock-seconds{
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--verde);
      vertical-align: super;
      margin-left: 2px;
    }

    .login-date{
      color: var(--page-muted);
      font-weight: 700;
      font-size: .95rem;
      margin-bottom: 20px;
      text-transform: capitalize;
    }

    /* ── Área del escáner ── */
    .fp-scan-area{
      position: relative;
      background: linear-gradient(135deg, rgba(14,77,146,.05), rgba(0,155,72,.05));
      border: 2px dashed rgba(14,77,146,.18);
      border-radius: 20px;
      padding: 28px 16px 22px;
      margin-bottom: 18px;
      transition: all .35s ease;
      overflow: hidden;
    }
    :root[data-theme="dark"] .fp-scan-area{
      background: rgba(255,255,255,.04);
      border-color: rgba(255,255,255,.12);
    }

    .fp-scan-area.scanning{
      border-color: #2196f3;
      border-style: solid;
      animation: scanPulse 1.8s ease-in-out infinite;
    }
    .fp-scan-area.success{
      border-color: #00c853;
      border-style: solid;
      background: rgba(0,200,83,.06);
    }
    .fp-scan-area.error{
      border-color: #f44336;
      border-style: solid;
      background: rgba(244,67,54,.06);
    }

    @keyframes scanPulse{
      0%,100%{ box-shadow: 0 0 0 0 rgba(33,150,243,.2); }
      50%{ box-shadow: 0 0 0 14px rgba(33,150,243,0); }
    }

    .fp-scan-icon{
      font-size: 52px;
      margin-bottom: 10px;
      display: block;
      transition: color .3s, transform .3s;
    }
    .fp-scan-icon.idle{ color: rgba(14,77,146,.28); }
    .fp-scan-icon.scanning{ color: #2196f3; animation: fpSpin 2s linear infinite; }
    .fp-scan-icon.success{ color: #00c853; transform: scale(1.1); }
    .fp-scan-icon.error{ color: #f44336; }
    :root[data-theme="dark"] .fp-scan-icon.idle{ color: rgba(234,240,255,.25); }

    @keyframes fpSpin{ 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }

    .fp-scan-text{
      font-size: .95rem;
      font-weight: 700;
      color: var(--page-muted);
    }

    /* ── Botón principal de huella ── */
    .btn-fingerprint{
      width: 100%;
      border: none;
      cursor: pointer;
      padding: 16px 20px;
      border-radius: 16px;
      font-weight: 900;
      font-size: 1.05rem;
      color: #fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 14px 30px rgba(14,77,146,.22);
      transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      letter-spacing: .3px;
    }
    .btn-fingerprint:hover:not(:disabled){
      transform: translateY(-2px);
      filter: brightness(1.04);
      box-shadow: 0 18px 40px rgba(14,77,146,.28);
    }
    .btn-fingerprint:active:not(:disabled){ transform: translateY(0); }
    .btn-fingerprint:disabled{
      opacity: .7;
      cursor: not-allowed;
      transform: none;
    }
    .btn-fingerprint i{ font-size: 1.3rem; }

    /* ── Resultado / feedback ── */
    .fp-result-box{
      margin-top: 14px;
      padding: 14px;
      border-radius: 16px;
      font-weight: 800;
      font-size: .95rem;
      animation: fadeInUp .3s ease;
      display: none;
    }
    .fp-result-box.show{ display: block; }
    .fp-result-box.success{
      color: #0A5C2B;
      background: rgba(0,200,83,.10);
      border: 1px solid rgba(0,155,72,.22);
    }
    .fp-result-box.error{
      color: #7f0e1c;
      background: rgba(255,234,236,.90);
      border: 1px solid rgba(127,14,28,.18);
    }
    :root[data-theme="dark"] .fp-result-box.success{
      color: #4ADE80;
      background: rgba(0,200,83,.12);
      border-color: rgba(0,200,83,.25);
    }
    :root[data-theme="dark"] .fp-result-box.error{
      color: #FCA5A5;
      background: rgba(244,67,54,.12);
      border-color: rgba(244,67,54,.25);
    }

    @keyframes fadeInUp{
      from{ opacity:0; transform: translateY(8px); }
      to{ opacity:1; transform: translateY(0); }
    }

    .fp-welcome-name{
      font-size: 1.3rem;
      font-weight: 900;
      color: var(--azul2);
      margin: 6px 0 2px;
    }
    :root[data-theme="dark"] .fp-welcome-name{ color: #EAF0FF; }

    .fp-welcome-tipo{
      font-size: 1rem;
      font-weight: 700;
    }

    .fp-hint{
      color: var(--page-muted);
      font-size: .82rem;
      font-weight: 600;
      margin-top: 10px;
    }

    .modal-message{
      margin-top: 10px;
      padding: 12px;
      border-radius: 14px;
      font-weight: 900;
      font-size: .95rem;
      color: #7f0e1c;
      background: rgba(255,234,236,.90);
      border: 1px solid rgba(127,14,28,.18);
    }

    /* =========================
       TOAST
    ========================= */
    .toast{
      position: fixed;
      top: 16px;
      right: 16px;
      z-index: 2000;
      min-width: 260px;
      max-width: min(420px, calc(100% - 32px));
      padding: 12px 14px;
      border-radius: 16px;
      background: var(--panel-bg);
      border: 1px solid var(--panel-border);
      box-shadow: 0 26px 70px rgba(0,0,0,.25);
      display:flex;
      align-items:flex-start;
      gap: 10px;
      opacity: 0;
      transform: translateY(-8px);
      pointer-events:none;
      transition: opacity .22s ease, transform .22s ease;
    }
    .toast.is-show{
      opacity: 1;
      transform: translateY(0);
      pointer-events:auto;
    }
    .toast .ic{
      width: 40px;
      height: 40px;
      border-radius: 14px;
      display:grid;
      place-items:center;
      color:#fff;
      background: linear-gradient(135deg, var(--azul), var(--verde));
      box-shadow: 0 14px 30px rgba(0,0,0,.18);
      flex-shrink:0;
    }
    .toast .txt strong{
      display:block;
      font-weight: 900;
      color: var(--panel-text);
      margin-bottom: 2px;
    }
    .toast .txt p{
      margin:0;
      color: var(--panel-muted);
      font-weight: 700;
      font-size: .92rem;
    }
    .toast .x{
      margin-left:auto;
      cursor:pointer;
      width: 34px;
      height: 34px;
      border-radius: 12px;
      display:grid;
      place-items:center;
      background: rgba(255,255,255,.10);
      border: 1px solid rgba(255,255,255,.18);
      color: var(--panel-text);
      font-weight: 900;
      line-height:1;
    }

    /* =========================
       RESPONSIVE (GRID)
    ========================= */
    @media (max-width: 1050px){
      .hero-inner{ grid-template-columns: 1fr; }
      .grid4{ grid-template-columns: repeat(2, 1fr); }
      .stats-grid{ grid-template-columns: 1fr; }
      .footer-content{ grid-template-columns: 1fr; }
      .brand-text{ display:none; }
    }

    @media (max-width: 768px){
      .topbar-inner{
        grid-template-columns: 1fr;
        grid-template-areas:
          "brand"
          "rightTop"
          "welcome"
          "rightBottom";
      }

      .rightTop, .rightBottom{
        justify-content:center;
      }

      .welcome-slot{
        justify-content:center;
      }

      .pill{
        max-width:100%;
        justify-content:center;
      }

      .status-pill, .btn, .btn-modules-toggle{
        width:100%;
        justify-content:center;
      }

      .btn-theme .theme-label{ display:none; }
      .modules-wrap{ width:100%; }
      .modules-panel{ left: 0; right: 0; width: 100%; }

      .hero{ width: calc(100% - 28px); min-height: 720px; }
      .stats{ width: calc(100% - 28px); margin-top: -12px; }
      .section{ width: calc(100% - 28px); }
      .grid4{ grid-template-columns: 1fr; }

      .support-fab .label{ display:none; }
      .support-fab{ width: 54px; height: 54px; padding: 0; justify-content:center; border-radius: 18px; }
    }

    @media (prefers-reduced-motion: reduce){
      *{ transition:none !important; animation:none !important; }
      .hero::after{ display:none !important; }
      .scroll-indicator .mouse::after{ animation:none !important; }
    }
  </style>
</head>

<body>
<div class="inicio-container">

  <!-- TOAST -->
  <div class="toast" id="toast">
    <div class="ic"><i class="fa-solid fa-bell"></i></div>
    <div class="txt">
      <strong id="toastTitle">Aviso</strong>
      <p id="toastMsg">Mensaje</p>
    </div>
    <div class="x" id="toastClose">✕</div>
  </div>

  <!-- TOPBAR -->
  <header class="topbar">
    <div class="scroll-progress" id="scrollProgress" aria-hidden="true"></div>

    <div class="wrap topbar-inner">

      <!-- brand -->
      <div class="brand">
        <div class="brand-logos">
          <img src="../assets/img/logo_secretaria.png" alt="Logo Secretaría de Educación">
          <img src="../assets/img/logo_escuelaaa.png" alt="Logo Escuela Secundaria">
        </div>
        <div class="brand-text">
          <div class="title">Sistema de Asistencia Escolar</div>
          <div class="subtitle">Secundaria “Emperador Cuauhtémoc” • Clave 12DES0020I</div>
        </div>
      </div>

      <!-- rightTop -->
      <div class="rightTop">
        <div class="status-pill" title="Estado del sistema">
          <span class="dot"></span> Sistema en línea
        </div>

        <button type="button" class="btn btn-theme" id="themeToggle" aria-label="Cambiar tema" aria-pressed="false" title="Alt+T">
          <i class="fa-solid fa-moon" data-icon="moon"></i>
          <i class="fa-solid fa-sun" data-icon="sun"></i>
          <span class="theme-label" id="themeLabel">Modo noche</span>
        </button>
      </div>

      <!-- welcome -->
      <div class="welcome-slot">
        <?php if ($user_logged_in): ?>
          <div class="pill" title="Sesión activa">
            <div class="avatar" aria-hidden="true"><?php echo htmlspecialchars($user_iniciales); ?></div>
            <i class="fa-solid fa-circle-check"></i>
            <span>
              Bienvenido, <?php echo htmlspecialchars($user_nombre); ?>
              (<span class="role-tag"><?php echo htmlspecialchars($user_rol); ?></span>)
            </span>
          </div>
        <?php endif; ?>
      </div>

      <!-- rightBottom -->
      <div class="rightBottom">
        <?php if ($user_logged_in): ?>

          <div class="modules-wrap">
            <button type="button" class="btn-modules-toggle" id="modulesToggle" aria-expanded="false" aria-controls="modulesMenu" title="Alt+M">
              <i class="fa-solid fa-grid-2"></i> <span>Módulos</span>
              <i class="fa-solid fa-chevron-down modules-chevron"></i>
            </button>

            <div class="modules-panel" id="modulesMenu">
              <div class="modules-header">
                <div class="left">
                  <strong><i class="fa-solid fa-layer-group"></i> Accesos rápidos</strong>
                  <small>Filtra y entra al módulo que necesitas</small>

                  <div class="modules-search">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="modulesSearch" placeholder="Buscar módulo...">
                  </div>
                </div>

                <div class="badge"><i class="fa-solid fa-shield-halved"></i> Seguro</div>
              </div>

              <div class="menu-modulos-grid" id="modulesGrid">
                <a href="gestion_usuarios.php" class="menu-item"><i class="fas fa-users"></i> Gestionar Usuarios</a>
                <a href="gestion_maestros.php" class="menu-item"><i class="fas fa-user-graduate"></i> Gestionar Maestros</a>
                <a href="gestion_materias.php" class="menu-item"><i class="fas fa-book"></i> Gestionar Materias</a>
                <a href="gestion_grupos.php" class="menu-item"><i class="fas fa-users-class"></i> Gestionar Grupos</a>
                <a href="gestion_horarios_por_maestro.php" class="menu-item"><i class="fas fa-calendar-alt"></i> Gestionar Horarios</a>
                <a href="asistencias_diarias.php" class="menu-item"><i class="fas fa-list-check"></i> Ver Asistencias Diarias</a>
                <a href="reporte_quincenal.php" class="menu-item"><i class="fas fa-chart-bar"></i> Reporte Quincenal</a>
                <a href="incidencias_pendientes.php" class="menu-item"><i class="fas fa-exclamation-triangle"></i> Justificaciones Pendientes</a>
                <a href="checkin_huella.php" class="menu-item" style="background:linear-gradient(135deg,rgba(0,155,72,.1),rgba(14,77,146,.1));border:1px solid rgba(0,155,72,.25)"><i class="fas fa-fingerprint" style="color:#009B48"></i> Check-in Biométrico</a>
                <?php if (in_array($user_rol, ['Director', 'Administrador', 'Admin'])): ?>
                <a href="gestion_huellas.php" class="menu-item" style="background:linear-gradient(135deg,rgba(14,77,146,.1),rgba(0,155,72,.1));border:1px solid rgba(14,77,146,.25)"><i class="fas fa-fingerprint" style="color:#0E4D92"></i> Gestión de Huellas</a>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <a href="logout.php" class="btn btn-logout"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>

        <?php else: ?>

          <a href="#loginModal" class="btn btn-login-modal" id="openLoginModal" title="Alt+L">
            <i class="fas fa-fingerprint"></i> Registrar Asistencia
          </a>

        <?php endif; ?>
      </div>

    </div>
  </header>

  <!-- MODAL LOGIN (Huella + Reloj) -->
  <div id="loginModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-fingerprint"></i> Registro de Asistencia</h2>
        <span class="close">&times;</span>
      </div>

      <div class="modal-body">
        <!-- Reloj en tiempo real -->
        <div class="login-clock" id="loginClock">
          <span id="loginClockHM">--:--</span><span class="login-clock-seconds" id="loginClockSec">00</span>
        </div>
        <div class="login-date" id="loginDate">cargando fecha...</div>

        <!-- Área del escáner -->
        <div class="fp-scan-area" id="fpScanArea">
          <span class="fp-scan-icon idle" id="fpScanIcon"><i class="fas fa-fingerprint"></i></span>
          <div class="fp-scan-text" id="fpScanText">Presiona el botón para registrar tu asistencia</div>
        </div>

        <!-- Botón principal -->
        <button type="button" class="btn-fingerprint" id="btnFingerprintLogin">
          <i class="fas fa-fingerprint"></i>
          <span id="btnFpText">Registrar Asistencia</span>
        </button>

        <!-- Resultado/feedback -->
        <div class="fp-result-box" id="fpResultBox"></div>

        <p class="fp-hint">
          <i class="fas fa-info-circle"></i> 
          Tu huella inicia sesión y registra entrada/salida automáticamente
        </p>
      </div>
    </div>
  </div>

  <!-- HERO -->
  <section class="hero" id="heroParallax">
    <div class="grain"></div>

    <div class="hero-inner">
      <div>
        <h1 class="hero-title">Sistema de Asistencia Escolar</h1>
        <p class="hero-sub">
          Control de entradas y salidas mediante huella dactilar, con reportes y gestión escolar
          en una experiencia moderna y clara.
        </p>

        <div class="hero-chips">
          <span class="chip"><i class="fa-solid fa-fingerprint"></i> Huella dactilar</span>
          <span class="chip"><i class="fa-solid fa-shield-halved"></i> Acceso por roles</span>
          <span class="chip"><i class="fa-solid fa-clock"></i> Tiempo real</span>
        </div>

        <?php if (!$user_logged_in): ?>
          <div class="hero-actions">
            <a href="#loginModal" class="btn-hero primary" id="openLoginModalFromLanding">
              <i class="fa-solid fa-fingerprint"></i> Registrar Asistencia
            </a>
            <a href="#beneficios" class="btn-hero ghost">
              <i class="fa-solid fa-circle-info"></i> Ver beneficios
            </a>
          </div>
        <?php else: ?>
          <div class="hero-actions">
            <a href="#dashboard" class="btn-hero primary">
              <i class="fa-solid fa-gauge-high"></i> Panel rápido
            </a>
            <a href="#" class="btn-hero ghost" onclick="document.getElementById('modulesToggle')?.click(); return false;">
              <i class="fa-solid fa-grid-2"></i> Abrir módulos
            </a>
          </div>
        <?php endif; ?>
      </div>

      <aside class="hero-card">
        <h3><i class="fa-solid fa-list-check"></i> Lo que podrás hacer</h3>
        <ul>
          <li><i class="fa-solid fa-check"></i> Control de entradas y salidas con validación</li>
          <li><i class="fa-solid fa-check"></i> Consulta de asistencias diarias</li>
          <li><i class="fa-solid fa-check"></i> Reportes quincenales</li>
          <li><i class="fa-solid fa-check"></i> Gestión de usuarios, grupos, materias y horarios</li>
          <li><i class="fa-solid fa-check"></i> Revisión de incidencias / justificaciones</li>
        </ul>
      </aside>
    </div>

    <div class="scroll-indicator" title="Desliza para ver más">
      <div class="mouse"></div>
      Desliza
    </div>
  </section>

  <!-- MINI DASHBOARD (solo logueado) -->
  <?php if ($user_logged_in): ?>
    <section class="dashboard" id="dashboard">
      <div class="dashboard-head">
        <div>
          <div class="title"><i class="fa-solid fa-gauge-high"></i> Panel rápido</div>
          <div class="subtitle">Accesos y resumen del sistema para hoy.</div>
        </div>

        <div class="dash-actions">
          <a href="checkin_huella.php" style="background:linear-gradient(135deg,#0E4D92,#009B48);color:#fff"><i class="fa-solid fa-fingerprint"></i> Check-in Biométrico</a>
          <a href="asistencias_diarias.php"><i class="fa-solid fa-list-check"></i> Asistencias</a>
          <a href="incidencias_pendientes.php"><i class="fa-solid fa-triangle-exclamation"></i> Pendientes</a>
          <a href="reporte_quincenal.php"><i class="fa-solid fa-chart-column"></i> Reportes</a>
        </div>
      </div>

      <div class="dash-grid">
        <a class="dash-card reveal" href="asistencias_diarias.php" title="Ver asistencias diarias">
          <div class="dash-top">
            <div class="dash-ic"><i class="fa-solid fa-calendar-day"></i></div>
            <div class="dash-meta">
              <div class="kpi">Hoy</div>
              <div class="label">Asistencias diarias</div>
            </div>
          </div>
          <div class="dash-foot">
            <div class="hint">Consulta entradas/salidas del día.</div>
            <div class="go">Ir <i class="fa-solid fa-arrow-right"></i></div>
          </div>
        </a>

        <a class="dash-card reveal" href="incidencias_pendientes.php" title="Ver justificaciones pendientes">
          <div class="dash-top">
            <div class="dash-ic"><i class="fa-solid fa-file-circle-exclamation"></i></div>
            <div class="dash-meta">
              <div class="kpi">Pend.</div>
              <div class="label">Justificaciones</div>
            </div>
          </div>
          <div class="dash-foot">
            <div class="hint">Revisa y valida solicitudes.</div>
            <div class="go">Ir <i class="fa-solid fa-arrow-right"></i></div>
          </div>
        </a>

        <a class="dash-card reveal" href="reporte_quincenal.php" title="Ver reporte quincenal">
          <div class="dash-top">
            <div class="dash-ic"><i class="fa-solid fa-chart-line"></i></div>
            <div class="dash-meta">
              <div class="kpi">Quinc.</div>
              <div class="label">Reporte quincenal</div>
            </div>
          </div>
          <div class="dash-foot">
            <div class="hint">Resumen por periodo.</div>
            <div class="go">Ir <i class="fa-solid fa-arrow-right"></i></div>
          </div>
        </a>

        <a class="dash-card reveal" href="gestion_horarios_por_maestro.php" title="Gestionar horarios">
          <div class="dash-top">
            <div class="dash-ic"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="dash-meta">
              <div class="kpi">Gest.</div>
              <div class="label">Horarios</div>
            </div>
          </div>
          <div class="dash-foot">
            <div class="hint">Administra horarios por maestro.</div>
            <div class="go">Ir <i class="fa-solid fa-arrow-right"></i></div>
          </div>
        </a>
      </div>
    </section>
  <?php endif; ?>

  <!-- STATS -->
  <section class="stats" id="stats">
    <div class="stats-grid">
      <div class="stat-card reveal">
        <div class="stat-ic"><i class="fa-solid fa-grid-2"></i></div>
        <div class="stat-meta">
          <div class="num"><span class="countup" id="countModules" data-target="0">0</span></div>
          <div class="label">Módulos disponibles</div>
          <div class="hint">Accesos rápidos en el menú</div>
        </div>
      </div>

      <div class="stat-card reveal">
        <div class="stat-ic"><i class="fa-solid fa-award"></i></div>
        <div class="stat-meta">
          <div class="num"><span class="countup" data-target="4">0</span></div>
          <div class="label">Beneficios clave</div>
          <div class="hint">Precisión • Rapidez • Seguridad • Reportes</div>
        </div>
      </div>

      <div class="stat-card reveal">
        <div class="stat-ic"><i class="fa-solid fa-calendar-days"></i></div>
        <div class="stat-meta">
          <div class="num"><span class="countup" data-target="<?php echo (int)date('Y'); ?>">0</span></div>
          <div class="label">Año</div>
          <div class="hint">Sistema actualizado y disponible</div>
        </div>
      </div>
    </div>
  </section>

  <!-- BENEFICIOS -->
  <section class="section" id="beneficios">
    <div class="section-title"><i class="fa-solid fa-award"></i> Beneficios del sistema</div>

    <div class="grid4">
      <div class="feature reveal">
        <div class="icon"><i class="fa-solid fa-bullseye"></i></div>
        <h4>Precisión</h4>
        <p>Registros claros y confiables para evitar errores y confusiones.</p>
      </div>

      <div class="feature reveal">
        <div class="icon"><i class="fa-solid fa-bolt"></i></div>
        <h4>Rapidez</h4>
        <p>Entrada y salida en segundos con validación inmediata.</p>
      </div>

      <div class="feature reveal">
        <div class="icon"><i class="fa-solid fa-shield-halved"></i></div>
        <h4>Seguridad</h4>
        <p>Acceso por roles y control de acciones dentro del sistema.</p>
      </div>

      <div class="feature reveal">
        <div class="icon"><i class="fa-solid fa-chart-column"></i></div>
        <h4>Reportes</h4>
        <p>Consulta diaria y reportes quincenales para mejor control.</p>
      </div>
    </div>
  </section>

  <!-- SOPORTE -->
  <button class="support-fab" id="supportFab" aria-label="Soporte">
    <i class="fa-solid fa-headset"></i>
    <span class="label">Soporte</span>
  </button>

  <!-- IR ARRIBA -->
  <button class="to-top" id="toTop" aria-label="Ir arriba" title="Ir arriba">
    <i class="fa-solid fa-arrow-up"></i>
  </button>

  <!-- MODAL SOPORTE -->
  <div class="support-modal" id="supportModal" role="dialog" aria-modal="true">
    <div class="support-dialog">
      <div class="support-head">
        <h3><i class="fa-solid fa-headset"></i> Soporte y contacto</h3>
        <div class="support-close" id="supportClose" aria-label="Cerrar">✕</div>
      </div>

      <div class="support-body">
        <div class="support-card">
          <div class="support-row">
            <div class="support-ic"><i class="fa-solid fa-school"></i></div>
            <div class="support-txt">
              <strong>Escuela Secundaria “Emperador Cuauhtémoc”</strong>
              <p>Ixcateopan de Cuauhtémoc, Guerrero • Clave 12DES0020I</p>
            </div>
          </div>
        </div>

        <div class="support-card">
          <div class="support-row">
            <div class="support-ic"><i class="fa-solid fa-phone"></i></div>
            <div class="support-txt">
              <strong>Teléfono</strong>
              <p>7363669118</p>
            </div>
          </div>
        </div>

        <div class="support-card">
          <div class="support-row">
            <div class="support-ic"><i class="fa-solid fa-envelope"></i></div>
            <div class="support-txt">
              <strong>Correo</strong>
              <p>12des0020i@seg.gob.mx</p>
            </div>
          </div>

          <div class="support-actions">
            <a href="tel:7363669118"><i class="fa-solid fa-phone"></i> Llamar</a>
            <a href="mailto:12des0020i@seg.gob.mx?subject=Soporte%20Sistema%20de%20Asistencia"><i class="fa-solid fa-envelope"></i> Enviar correo</a>
            <a href="https://www.facebook.com/emperador.cuauhtemoc.10?locale=es_ES" target="_blank" rel="noopener">
              <i class="fab fa-facebook-f"></i> Facebook
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- FOOTER -->
  <footer>
    <div class="wrap">
      <div class="footer-content">
        <div class="footer-column">
          <h3>Contacto</h3>
          <p>Escuela Secundaria General "Emperador Cuauhtémoc"</p>
          <p>Calle Josefa Ortiz De Domínguez No.37</p>
          <p>Ixcateopan De Cuauhtémoc, Guerrero, CP. 40430</p>
          <p>Teléfono: 7363669118</p>
          <p>Email: 12des0020i@seg.gob.mx</p>
        </div>

        <div class="footer-column">
          <h3>Desarrollado por</h3>
          <p>Lizbeth Grande Romero</p>
          <p>Matrícula: 5724100007</p>
          <p>Universidad Tecnológica de la Región Norte de Guerrero</p>
          <p>Periodo de Estadía: 05/01/2026 - 30/04/2026</p>
        </div>

        <div class="footer-column">
          <h3>Enlaces Rápidos</h3>
          <a href="#">Sobre el Proyecto</a>
          <a href="#">Documentación</a>
          <a href="#">Soporte Técnico</a>
        </div>
      </div>

      <div class="footer-social">
        <a href="https://www.facebook.com/emperador.cuauhtemoc.10?locale=es_ES" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
      </div>

      <div class="footer-copyright">
        &copy; <?php echo date('Y'); ?> Sistema de Asistencia Escolar. Todos los derechos reservados.
      </div>
    </div>
  </footer>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // ===== TOAST =====
  const toast = document.getElementById('toast');
  const toastTitle = document.getElementById('toastTitle');
  const toastMsg = document.getElementById('toastMsg');
  const toastClose = document.getElementById('toastClose');
  let toastTimer = null;

  function showToast(title, msg){
    if (!toast) return;
    toastTitle.textContent = title;
    toastMsg.textContent = msg;
    toast.classList.add('is-show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.remove('is-show'), 2800);
  }
  toastClose?.addEventListener('click', () => toast.classList.remove('is-show'));

  // ==========================================================
  // THEME (Claro/Oscuro) + persistencia
  // ==========================================================
  const THEME_KEY = 'sae_theme';
  const root = document.documentElement;
  const btnTheme = document.getElementById('themeToggle');
  const themeLabel = document.getElementById('themeLabel');

  function getSystemTheme(){
    return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme){
    if (theme === 'dark') root.setAttribute('data-theme', 'dark');
    else root.removeAttribute('data-theme');

    btnTheme?.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
    if (themeLabel) themeLabel.textContent = (theme === 'dark') ? 'Modo día' : 'Modo noche';
  }

  const savedTheme = localStorage.getItem(THEME_KEY);
  const initialTheme = savedTheme || getSystemTheme();
  applyTheme(initialTheme);

  btnTheme?.addEventListener('click', () => {
    const current = root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem(THEME_KEY, next);
    applyTheme(next);
    showToast('Tema', next === 'dark' ? 'Modo noche activado' : 'Modo día activado');
  });

  if (!savedTheme && window.matchMedia) {
    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    mq.addEventListener?.('change', () => applyTheme(getSystemTheme()));
  }

  // Toast bienvenida 1 vez por pestaña
  const isLogged = <?php echo $user_logged_in ? 'true' : 'false'; ?>;
  const userName = <?php echo json_encode($user_nombre); ?>;
  if (isLogged && userName) {
    const k = 'welcome_once';
    if (!sessionStorage.getItem(k)) {
      sessionStorage.setItem(k, '1');
      showToast('Bienvenido', userName);
    }
  }

  // ==========================================================
  // SCROLL PROGRESS + TO TOP
  // ==========================================================
  const scrollProgress = document.getElementById('scrollProgress');
  const toTop = document.getElementById('toTop');

  function onScrollUI(){
    const doc = document.documentElement;
    const max = Math.max(1, doc.scrollHeight - doc.clientHeight);
    const p = Math.min(1, Math.max(0, doc.scrollTop / max));
    if (scrollProgress) scrollProgress.style.width = (p * 100).toFixed(2) + '%';

    if (toTop) {
      if (doc.scrollTop > 420) toTop.classList.add('is-visible');
      else toTop.classList.remove('is-visible');
    }
  }
  window.addEventListener('scroll', onScrollUI, { passive:true });
  window.addEventListener('resize', onScrollUI);
  onScrollUI();

  toTop?.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

  // ==========================================================
  // MODAL LOGIN (HUELLA + RELOJ EN TIEMPO REAL)
  // ==========================================================
  const openModalBtn = document.getElementById('openLoginModal');
  const openModalBtnLanding = document.getElementById('openLoginModalFromLanding');
  const modal = document.getElementById('loginModal');
  const closeBtn = document.querySelector('.close');

  // ── Reloj en tiempo real para el modal ──
  let loginClockInterval = null;

  function updateLoginClock() {
    const now = new Date();
    const hm = now.toLocaleTimeString('es-MX', {
      hour: '2-digit', minute: '2-digit', hour12: true, timeZone: 'America/Mexico_City'
    });
    const sec = String(now.toLocaleTimeString('es-MX', {
      second: '2-digit', timeZone: 'America/Mexico_City'
    })).slice(-2);
    const dateStr = now.toLocaleDateString('es-MX', {
      weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
      timeZone: 'America/Mexico_City'
    });

    const clockHM = document.getElementById('loginClockHM');
    const clockSec = document.getElementById('loginClockSec');
    const dateEl = document.getElementById('loginDate');

    if (clockHM) clockHM.textContent = hm;
    if (clockSec) clockSec.textContent = sec;
    if (dateEl) dateEl.textContent = dateStr;
  }

  function startLoginClock() {
    updateLoginClock();
    if (loginClockInterval) clearInterval(loginClockInterval);
    loginClockInterval = setInterval(updateLoginClock, 1000);
  }

  function stopLoginClock() {
    if (loginClockInterval) { clearInterval(loginClockInterval); loginClockInterval = null; }
  }

  // ── Abrir / cerrar modal ──
  function resetFpModal() {
    const area = document.getElementById('fpScanArea');
    const icon = document.getElementById('fpScanIcon');
    const text = document.getElementById('fpScanText');
    const btn = document.getElementById('btnFingerprintLogin');
    const btnText = document.getElementById('btnFpText');
    const result = document.getElementById('fpResultBox');

    if (area) area.className = 'fp-scan-area';
    if (icon) { icon.className = 'fp-scan-icon idle'; icon.innerHTML = '<i class="fas fa-fingerprint"></i>'; }
    if (text) text.textContent = 'Presiona el botón para registrar tu asistencia';
    if (btn) btn.disabled = false;
    if (btnText) btnText.textContent = 'Registrar Asistencia';
    if (result) { result.className = 'fp-result-box'; result.innerHTML = ''; }
  }

  const openModal = function(event) {
    event.preventDefault();
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    resetFpModal();
    startLoginClock();
  };

  const closeModal = function() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
    stopLoginClock();
    resetFpModal();
  };

  openModalBtn?.addEventListener('click', openModal);
  openModalBtnLanding?.addEventListener('click', openModal);

  closeBtn && (closeBtn.onclick = closeModal);

  window.addEventListener('click', function(event){
    if (event.target === modal) closeModal();
  });

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && modal && modal.style.display === 'block') closeModal();
  });

  // ==========================================================
  // MENU DESPLEGABLE (MÓDULOS) + BUSCADOR
  // ==========================================================
  const modulesToggle = document.getElementById('modulesToggle');
  const modulesMenu = document.getElementById('modulesMenu');
  const modulesSearch = document.getElementById('modulesSearch');
  const modulesGrid = document.getElementById('modulesGrid');

  function openModules() {
    modulesMenu?.classList.add('is-open');
    modulesToggle?.classList.add('is-open');
    modulesToggle?.setAttribute('aria-expanded', 'true');
    setTimeout(() => modulesSearch?.focus(), 50);
  }
  function closeModules() {
    modulesMenu?.classList.remove('is-open');
    modulesToggle?.classList.remove('is-open');
    modulesToggle?.setAttribute('aria-expanded', 'false');
    if (modulesSearch) {
      modulesSearch.value = '';
      modulesGrid?.querySelectorAll('a.menu-item').forEach(a => a.style.display = '');
    }
  }

  if (modulesToggle && modulesMenu) {
    modulesToggle.addEventListener('click', (e) => {
      e.stopPropagation();
      const isOpen = modulesMenu.classList.contains('is-open');
      if (isOpen) closeModules();
      else openModules();
    });

    document.addEventListener('click', (e) => {
      if (!modulesToggle.contains(e.target) && !modulesMenu.contains(e.target)) {
        closeModules();
      }
    });

    modulesMenu.addEventListener('click', (e) => {
      if (e.target.closest('a')) {
        if (window.innerWidth <= 768) closeModules();
      }
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modulesMenu.classList.contains('is-open')) closeModules();
    });

    modulesSearch?.addEventListener('input', () => {
      const q = (modulesSearch.value || '').trim().toLowerCase();
      const items = modulesGrid?.querySelectorAll('a.menu-item') || [];
      items.forEach(a => {
        const txt = (a.textContent || '').toLowerCase();
        a.style.display = txt.includes(q) ? '' : 'none';
      });
    });
  }

  // ==========================================================
  // PARALLAX SUAVE (desactivado en móvil)
  // ==========================================================
  const hero = document.getElementById('heroParallax');
  const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const isMobile = window.matchMedia('(max-width: 768px)').matches;

  if (hero && !prefersReduced && !isMobile) {
    let ticking = false;

    const updateParallax = () => {
      const rect = hero.getBoundingClientRect();
      const viewportH = window.innerHeight || 800;
      const visible = Math.max(0, Math.min(1, (viewportH - rect.top) / (viewportH + rect.height)));
      const offset = Math.round((visible * 52) - 26);
      hero.style.backgroundPosition = `center ${offset}px`;
      ticking = false;
    };

    const onScroll = () => {
      if (!ticking) {
        window.requestAnimationFrame(updateParallax);
        ticking = true;
      }
    };

    updateParallax();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', onScroll);
  }

  // ==========================================================
  // REVEAL + COUNTUP
  // ==========================================================
  const countModules = document.getElementById('countModules');
  if (countModules && modulesMenu) {
    const links = modulesMenu.querySelectorAll('a.menu-item');
    countModules.dataset.target = String(links.length || 0);
  }

  function animateCount(el, target, duration=900) {
    const startTime = performance.now();
    const easeOut = (t) => 1 - Math.pow(1 - t, 3);
    const tick = (now) => {
      const p = Math.min(1, (now - startTime) / duration);
      el.textContent = String(Math.round(target * easeOut(p)));
      if (p < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
  }

  const revealEls = document.querySelectorAll('.reveal');
  const countups = document.querySelectorAll('.countup');
  const stats = document.getElementById('stats');

  if (!prefersReduced) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { threshold: 0.18 });
    revealEls.forEach(el => io.observe(el));
  } else {
    revealEls.forEach(el => el.classList.add('is-visible'));
  }

  if (stats && countups.length && !prefersReduced) {
    let played = false;
    const io2 = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!played && entry.isIntersecting) {
          played = true;
          countups.forEach(el => {
            const target = parseInt(el.dataset.target || '0', 10);
            animateCount(el, isNaN(target) ? 0 : target, 950);
          });
          io2.disconnect();
        }
      });
    }, { threshold: 0.35 });
    io2.observe(stats);
  } else {
    countups.forEach(el => el.textContent = el.dataset.target || '0');
  }

  // ==========================================================
  // Animar dashboard (porque .dash-card tiene reveal)
  // ==========================================================
  // (ya entra en el observer porque tiene clase .reveal)

  // ==========================================================
  // SOPORTE (FAB + MODAL)
  // ==========================================================
  const supportFab = document.getElementById('supportFab');
  const supportModal = document.getElementById('supportModal');
  const supportClose = document.getElementById('supportClose');

  function openSupport() {
    supportModal.style.display = 'block';
    document.body.style.overflow = 'hidden';
  }
  function closeSupport() {
    supportModal.style.display = 'none';
    document.body.style.overflow = '';
  }

  supportFab?.addEventListener('click', openSupport);
  supportClose?.addEventListener('click', closeSupport);
  supportModal?.addEventListener('click', (e) => { if (e.target === supportModal) closeSupport(); });

  // ==========================================================
  // ATAJOS (Alt+T tema, Alt+M módulos, Alt+L login)
  // ==========================================================
  document.addEventListener('keydown', (e) => {
    if (!e.altKey) return;
    const k = (e.key || '').toLowerCase();
    if (k === 't') { e.preventDefault(); btnTheme?.click(); }
    if (k === 'm') { e.preventDefault(); modulesToggle?.click(); }
    if (k === 'l') { e.preventDefault(); openModalBtn?.click(); }
  });
});
</script>

<script src="../assets/js/fingerprint.js"></script>
<script>
// ══════════ LOGIN POR HUELLA DACTILAR (nueva lógica) ══════════
(function() {
  const btn = document.getElementById('btnFingerprintLogin');
  if (!btn) return;

  const btnText   = document.getElementById('btnFpText');
  const scanArea  = document.getElementById('fpScanArea');
  const scanIcon  = document.getElementById('fpScanIcon');
  const scanText  = document.getElementById('fpScanText');
  const resultBox = document.getElementById('fpResultBox');

  const baseUrl = window.location.hostname === 'localhost'
    ? '/sistema_asistencia/' : '/';

  // ── Helpers de estado visual ──
  function setScanState(state) {
    scanArea.className = 'fp-scan-area ' + state;

    const icons = {
      '': 'fas fa-fingerprint',
      'scanning': 'fas fa-spinner fa-spin',
      'success': 'fas fa-check-circle',
      'error': 'fas fa-times-circle',
    };
    scanIcon.innerHTML = '<i class="' + (icons[state] || icons['']) + '"></i>';
    scanIcon.className = 'fp-scan-icon ' + (state || 'idle');
  }

  function showResult(type, html) {
    resultBox.className = 'fp-result-box show ' + type;
    resultBox.innerHTML = html;
  }

  function resetBtn() {
    btn.disabled = false;
    btnText.textContent = 'Registrar Asistencia';
    btn.querySelector('i').className = 'fas fa-fingerprint';
  }

  // ── Click handler ──
  btn.addEventListener('click', function() {
    btn.disabled = true;
    btn.querySelector('i').className = 'fas fa-spinner fa-spin';
    btnText.textContent = 'Esperando huella...';
    setScanState('scanning');
    scanText.textContent = 'Coloca tu dedo en el escáner...';
    resultBox.className = 'fp-result-box';
    resultBox.innerHTML = '';

    FP.checkIn(function(result) {
      if (result.match && result.maestro_id) {
        // ✅ Huella reconocida
        setScanState('success');
        scanText.textContent = '¡Huella reconocida!';
        btn.querySelector('i').className = 'fas fa-check';
        btnText.textContent = 'Huella reconocida';

        // Llamar API de login + asistencia
        fetch(baseUrl + 'api/huella_login.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ maestro_id: result.maestro_id }),
        })
          .then(function(r) { return r.json(); })
          .then(function(data) {
            if (data.success) {
              const tipoEmoji = data.tipo === 'entrada' ? '🟢' : '🔵';
              const tipoLabel = data.tipo === 'entrada' ? 'Entrada' : 'Salida';
              const tipoColor = data.tipo === 'entrada' ? '#009B48' : '#0E4D92';

              showResult('success', 
                '<div class="fp-welcome-name">¡Bienvenido/a, ' + (data.nombre || 'Maestro') + '!</div>' +
                '<div class="fp-welcome-tipo" style="color:' + tipoColor + '">' + tipoEmoji + ' ' + tipoLabel + ' registrada — ' + (data.hora || '') + '</div>' +
                (data.estado && data.estado !== 'A tiempo' ? '<div style="margin-top:6px;font-size:.88rem;color:#e65100">⚠️ ' + data.estado + (data.minutos_retraso > 0 ? ' (' + data.minutos_retraso + ' min)' : '') + '</div>' : '') +
                '<div style="margin-top:8px;font-size:.82rem;opacity:.7">Redirigiendo al sistema...</div>'
              );

              setTimeout(function() { window.location.reload(); }, 2500);
            } else {
              showFpError(data.message || 'Error al registrar asistencia');
            }
          })
          .catch(function() { showFpError('Error de conexión con el servidor'); });

      } else if (result.match === false) {
        showFpError('Huella no reconocida. Intenta de nuevo.');
      } else {
        showFpError(result.message || 'Error del escáner');
      }
    });
  });

  function showFpError(msg) {
    setScanState('error');
    scanText.textContent = msg;
    showResult('error', '<i class="fas fa-exclamation-triangle"></i> ' + msg);
    resetBtn();

    // Auto-limpiar después de 4s
    setTimeout(function() {
      setScanState('');
      scanText.textContent = 'Presiona el botón para registrar tu asistencia';
      resultBox.className = 'fp-result-box';
      resultBox.innerHTML = '';
    }, 4000);
  }
})();
</script>

</body>
</html>