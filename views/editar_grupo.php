<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
if ($_SESSION['user_rol_nombre'] !== 'Director' && $_SESSION['user_rol_nombre'] !== 'Jefe de Oficina' && $_SESSION['user_rol_nombre'] !== 'Administrador') { die("Acceso denegado."); }

include_once '../config/database.php';
include_once '../models/Grupo.php';

$database = new Database();
$db = $database->getConnection();
$grupo = new Grupo($db);

$grupo_id = $_GET['id'] ?? null;
if ($grupo_id) {
    $grupo->id = $grupo_id;
    if (!$grupo->readOne()) { header('Location: gestion_grupos.php?message=' . urlencode('Grupo no encontrado.')); exit(); }
} else { header('Location: gestion_grupos.php'); exit(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo - Sistema de Asistencia</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root{--primary:#1B396A;--primary-2:#245f9f;--accent:#25b05f;--bg:#eef3f8;--surface:#fff;--surface-2:rgba(255,255,255,.86);--border:rgba(27,57,106,.12);--text:#0f172a;--text-muted:#5f6f87;--white:#fff;--shadow-sm:0 10px 24px rgba(15,23,42,.10);--shadow-md:0 18px 40px rgba(15,23,42,.12);--radius:22px;--radius-sm:14px}
        body.dark-mode{--bg:rgb(19,16,34);--surface:rgba(30,28,48,.95);--surface-2:rgba(38,36,58,.92);--border:rgba(255,255,255,.08);--text:#f3f5f8;--text-muted:#b8c2d1;--shadow-sm:0 10px 24px rgba(0,0,0,.28);--shadow-md:0 18px 40px rgba(0,0,0,.34)}
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Roboto',sans-serif;background:radial-gradient(900px 420px at 0% 0%,rgba(39,132,211,.12),transparent 55%),radial-gradient(900px 420px at 100% 0%,rgba(34,197,94,.10),transparent 55%),var(--bg);color:var(--text);min-height:100vh}a{text-decoration:none;color:inherit}button,input,select{font-family:inherit}
        .top-header{width:100%;border-radius:0 0 var(--radius) var(--radius);background:linear-gradient(135deg,#245f9f 0%,#1B396A 25%,#228e8e 65%,#25b05f 100%);box-shadow:var(--shadow-md);border-bottom:1px solid rgba(255,255,255,.12)}
        .top-header-inner{width:min(900px,calc(100% - 32px));margin:0 auto;display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 0;flex-wrap:wrap}
        .header-title{display:flex;align-items:center;gap:14px;color:var(--white)}.header-title .icon-box{width:52px;height:52px;border-radius:16px;background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.18);display:grid;place-items:center;font-size:1.3rem}.header-title h1{font-size:1.5rem;font-weight:900}.header-title p{font-size:.9rem;opacity:.9;font-weight:500}
        .header-actions{display:flex;gap:12px}.header-btn{display:inline-flex;align-items:center;gap:10px;min-height:48px;padding:12px 20px;border-radius:999px;font-size:.95rem;font-weight:800;color:var(--white);background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.16);box-shadow:var(--shadow-sm);backdrop-filter:blur(10px);transition:.18s ease;cursor:pointer}.header-btn:hover{transform:translateY(-2px);background:rgba(255,255,255,.20)}.theme-toggle{cursor:pointer}
        .page-wrapper{width:min(600px,calc(100% - 32px));margin:28px auto 40px}
        .form-card{background:var(--surface);border-radius:var(--radius);box-shadow:var(--shadow-md);border:1px solid var(--border);overflow:hidden;animation:slideUp .4s ease}@keyframes slideUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
        .form-card-head{padding:24px 28px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:14px}.form-card-head .card-icon{width:48px;height:48px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--primary));display:grid;place-items:center;color:#fff;font-size:1.2rem;flex-shrink:0}.form-card-head h2{font-size:1.35rem;font-weight:900;color:var(--primary)}body.dark-mode .form-card-head h2{color:#dce8ff}.form-card-head p{color:var(--text-muted);font-size:.9rem;margin-top:2px}
        .form-body{padding:24px 28px 28px}
        .form-group{margin-bottom:20px}.form-group label{display:flex;align-items:center;gap:8px;font-weight:700;font-size:.92rem;color:var(--primary);margin-bottom:8px}body.dark-mode .form-group label{color:#c5d5f0}.form-group label i{font-size:.85rem;opacity:.7}
        .form-control{width:100%;padding:14px 16px;border-radius:var(--radius-sm);border:1px solid var(--border);background:var(--surface-2);color:var(--text);font-size:1rem;transition:.2s ease;outline:none}body.dark-mode .form-control{background:rgba(255,255,255,.05)}.form-control:focus{border-color:var(--primary-2);box-shadow:0 0 0 4px rgba(27,57,106,.12)}
        .form-actions{display:flex;align-items:center;gap:14px;margin-top:8px;flex-wrap:wrap}
        .btn-submit{display:inline-flex;align-items:center;gap:10px;padding:14px 28px;border-radius:16px;border:none;cursor:pointer;font-weight:800;font-size:1rem;background:linear-gradient(135deg,var(--accent),var(--primary));color:#fff;box-shadow:var(--shadow-sm);transition:.18s ease}.btn-submit:hover{transform:translateY(-2px);filter:brightness(1.05)}
        .btn-cancel{display:inline-flex;align-items:center;gap:8px;padding:14px 22px;border-radius:16px;border:1px solid var(--border);background:transparent;color:var(--text-muted);font-weight:700;cursor:pointer;transition:.18s ease}.btn-cancel:hover{background:rgba(27,57,106,.05)}
        @media(max-width:600px){.top-header-inner{flex-direction:column;align-items:flex-start}}
    </style>
</head>
<body>
<div class="top-header">
    <div class="top-header-inner">
        <div class="header-title">
            <div class="icon-box"><i class="fas fa-layer-group"></i></div>
            <div><h1>Editar Grupo</h1><p>Modifica el nombre del grupo</p></div>
        </div>
        <div class="header-actions">
            <a href="gestion_grupos.php" class="header-btn"><i class="fas fa-arrow-left"></i> Volver</a>
            <div class="header-btn theme-toggle" onclick="document.body.classList.toggle('dark-mode')"><i class="fas fa-moon"></i></div>
        </div>
    </div>
</div>
<div class="page-wrapper">
    <div class="form-card">
        <div class="form-card-head">
            <div class="card-icon"><i class="fas fa-pen"></i></div>
            <div><h2><?php echo htmlspecialchars($grupo->nombre); ?></h2><p>Actualiza el nombre de este grupo</p></div>
        </div>
        <div class="form-body">
            <form action="../controllers/ActualizarGrupoController.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $grupo->id; ?>">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Nombre del Grupo</label>
                    <input type="text" name="nombre_grupo" id="nombre_grupo" class="form-control" value="<?php echo htmlspecialchars($grupo->nombre); ?>" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                    <a href="gestion_grupos.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>