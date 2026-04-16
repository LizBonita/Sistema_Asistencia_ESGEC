<?php
// views/gestion_huellas.php
// Panel de administración de huellas dactilares — Diseño Premium
session_start();
date_default_timezone_set('America/Mexico_City');
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
$user_nombre = $_SESSION['user_nombre'] ?? '';
$user_rol    = $_SESSION['user_rol_nombre'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Gestión de Huellas - Sistema de Asistencia</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    :root {
      --primary: #0A3A6F;
      --primary-light: #0E4D92;
      --accent: #009B48;
      --accent-glow: rgba(0,155,72,.15);
      --danger: #e53935;
      --warning: #ff9800;
      --bg: #f0f4f8;
      --surface: #ffffff;
      --text: #0b1220;
      --text-muted: #64748b;
      --border: rgba(14,77,146,.1);
      --shadow: 0 4px 24px rgba(15,23,42,.08);
      --shadow-lg: 0 12px 40px rgba(15,23,42,.12);
      --radius: 16px;
    }

    * { margin:0; padding:0; box-sizing:border-box }

    body {
      font-family: 'Inter', sans-serif;
      min-height: 100vh;
      background:
        radial-gradient(ellipse 800px 400px at 0% 0%, rgba(10,58,111,.08), transparent),
        radial-gradient(ellipse 800px 400px at 100% 0%, rgba(0,155,72,.06), transparent),
        var(--bg);
      color: var(--text);
    }

    .page { max-width: 1100px; margin: 0 auto; padding: 24px 20px 40px }

    /* ── Header ── */
    .page-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 12px;
    }

    .page-header .title-group { display:flex; align-items:center; gap:14px }

    .title-icon {
      width: 48px; height: 48px;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 22px;
      box-shadow: 0 4px 16px rgba(10,58,111,.25);
    }

    .page-header h1 { font-size: 24px; font-weight: 800; color: var(--primary) }
    .page-header .sub { font-size: 13px; color: var(--text-muted); margin-top: 2px }

    .back-btn {
      display: inline-flex; align-items: center; gap: 8px;
      background: var(--surface); color: var(--primary);
      text-decoration: none; padding: 10px 20px;
      border-radius: 12px; font-weight: 600; font-size: 14px;
      border: 1px solid var(--border);
      box-shadow: var(--shadow);
      transition: all .2s;
    }
    .back-btn:hover { transform: translateY(-1px); box-shadow: var(--shadow-lg) }

    /* ── Status Bar ── */
    .status-bar {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 14px 20px;
      margin-bottom: 20px;
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 12px;
      box-shadow: var(--shadow);
    }

    .agent-info { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500 }

    .status-dot {
      width: 10px; height: 10px; border-radius: 50%;
      display: inline-block;
    }
    .status-dot.on { background: #00c853; box-shadow: 0 0 8px rgba(0,200,83,.5) }
    .status-dot.off { background: var(--danger); box-shadow: 0 0 8px rgba(229,57,53,.4) }

    .stats-row { display: flex; gap: 8px; flex-wrap: wrap }

    .stat-chip {
      padding: 6px 14px; border-radius: 20px;
      font-size: 13px; font-weight: 600;
      display: flex; align-items: center; gap: 6px;
    }
    .stat-chip.total { background: rgba(14,77,146,.08); color: var(--primary-light) }
    .stat-chip.enrolled { background: var(--accent-glow); color: var(--accent) }
    .stat-chip.pending { background: rgba(255,152,0,.1); color: var(--warning) }

    /* ── Search ── */
    .search-bar {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 10px 16px;
      margin-bottom: 20px;
      display: flex; align-items: center; gap: 10px;
      box-shadow: var(--shadow);
    }
    .search-bar i { color: var(--text-muted); font-size: 16px }
    .search-bar input {
      border: none; outline: none; flex: 1;
      font-size: 14px; font-family: inherit; color: var(--text);
      background: transparent;
    }

    /* ── Grid ── */
    .maestros-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 16px;
    }

    .maestro-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      padding: 0;
      transition: all .3s;
      overflow: hidden;
      box-shadow: var(--shadow);
    }
    .maestro-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-lg);
    }
    .maestro-card.has-fp { border-left: 4px solid var(--accent) }
    .maestro-card.no-fp { border-left: 4px solid var(--warning) }

    .card-body { padding: 18px 20px }

    .card-top { display: flex; align-items: flex-start; gap: 14px; margin-bottom: 14px }

    .avatar {
      width: 44px; height: 44px; border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-weight: 700; font-size: 16px; color: #fff;
      flex-shrink: 0;
    }
    .avatar.enrolled { background: linear-gradient(135deg, var(--primary), var(--accent)) }
    .avatar.pending { background: linear-gradient(135deg, var(--warning), #f57c00) }

    .card-info { flex: 1; min-width: 0 }
    .card-name {
      font-size: 15px; font-weight: 700; color: var(--primary);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .card-contract { font-size: 12px; color: var(--text-muted); margin-top: 2px }

    .card-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 14px }

    .badge {
      display: inline-flex; align-items: center; gap: 5px;
      padding: 4px 10px; border-radius: 8px;
      font-size: 11px; font-weight: 600; letter-spacing: .3px;
    }
    .badge.registered { background: rgba(0,155,72,.1); color: var(--accent) }
    .badge.pending { background: rgba(255,152,0,.1); color: #e65100 }

    .badge-date {
      font-size: 11px; color: var(--text-muted);
      display: flex; align-items: center; gap: 4px;
    }

    .fp-thumb-row { display: flex; align-items: center; gap: 12px; margin-bottom: 14px }

    .fp-thumb {
      width: 52px; height: 62px;
      border-radius: 10px; object-fit: cover;
      border: 2px solid var(--primary-light);
      box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }

    .fp-thumb-label {
      font-size: 12px; color: var(--text-muted); line-height: 1.5;
    }
    .fp-thumb-label strong { color: var(--text); display: block }

    .btn-action {
      border: none; width: 100%;
      padding: 11px 18px; border-radius: 12px;
      font-size: 13px; font-weight: 600; font-family: inherit;
      cursor: pointer; transition: all .25s;
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }

    .btn-enroll {
      background: linear-gradient(135deg, var(--primary), var(--accent));
      color: #fff;
      box-shadow: 0 4px 14px rgba(10,58,111,.2);
    }
    .btn-enroll:hover {
      box-shadow: 0 6px 20px rgba(10,58,111,.3);
      transform: translateY(-1px);
    }

    .btn-re-enroll {
      background: rgba(14,77,146,.06); color: var(--primary-light);
      border: 1px solid var(--border);
    }
    .btn-re-enroll:hover { background: rgba(14,77,146,.12) }

    .btn-action:disabled { opacity: .5; cursor: not-allowed; transform: none }

    /* ── Modal ── */
    .modal-overlay {
      display: none; position: fixed;
      top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0,0,0,.55);
      backdrop-filter: blur(4px);
      z-index: 1000;
      align-items: center; justify-content: center;
    }
    .modal-overlay.active { display: flex }

    .modal {
      background: var(--surface);
      border-radius: 22px; padding: 36px;
      max-width: 440px; width: 92%;
      text-align: center;
      box-shadow: 0 24px 60px rgba(0,0,0,.3);
      animation: modalIn .35s cubic-bezier(.16,1,.3,1);
    }

    @keyframes modalIn {
      from { opacity: 0; transform: translateY(20px) scale(.95) }
      to { opacity: 1; transform: translateY(0) scale(1) }
    }

    .modal-icon {
      width: 64px; height: 64px; border-radius: 18px;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 16px; color: #fff; font-size: 28px;
      box-shadow: 0 6px 20px rgba(10,58,111,.25);
    }

    .modal h2 { color: var(--primary); margin-bottom: 6px; font-size: 20px; font-weight: 700 }
    .modal .modal-sub { color: var(--text-muted); font-size: 14px; margin-bottom: 20px }
    .modal #fp-status { font-size: 15px; margin: 16px 0; font-weight: 500 }
    .modal #fp-image {
      display: none; max-width: 160px;
      border-radius: 12px; border: 3px solid var(--primary-light);
      margin: 12px auto; box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }

    .btn-close-modal {
      background: rgba(0,0,0,.05); color: #666;
      border: none; padding: 11px 28px; border-radius: 12px;
      cursor: pointer; font-size: 14px; font-weight: 500;
      font-family: inherit; margin-top: 16px; transition: all .2s;
    }
    .btn-close-modal:hover { background: rgba(0,0,0,.1) }

    /* ── Responsive ── */
    @media (max-width: 640px) {
      .maestros-grid { grid-template-columns: 1fr }
      .page-header h1 { font-size: 20px }
      .status-bar { flex-direction: column; align-items: flex-start }
    }
  </style>
</head>
<body>
  <div class="page">
    <div class="page-header">
      <div class="title-group">
        <div class="title-icon"><i class="fas fa-fingerprint"></i></div>
        <div>
          <h1>Gestión de Huellas</h1>
          <div class="sub">Administrar huellas dactilares del personal</div>
        </div>
      </div>
      <a href="inicio.php" class="back-btn"><i class="fas fa-arrow-left"></i> Volver al panel</a>
    </div>

    <div class="status-bar">
      <div class="agent-info">
        <span class="status-dot" id="agent-dot"></span>
        <span id="agent-status">Verificando agente...</span>
      </div>
      <div class="stats-row">
        <div class="stat-chip total"><i class="fas fa-users"></i> <b id="stat-total">-</b> total</div>
        <div class="stat-chip enrolled"><i class="fas fa-check-circle"></i> <b id="stat-enrolled">-</b> registradas</div>
        <div class="stat-chip pending"><i class="fas fa-clock"></i> <b id="stat-pending">-</b> pendientes</div>
      </div>
    </div>

    <div class="search-bar">
      <i class="fas fa-search"></i>
      <input type="text" id="searchInput" placeholder="Buscar maestro por nombre...">
    </div>

    <div class="maestros-grid" id="maestros-grid">
      <div style="grid-column:1/-1;text-align:center;padding:50px;color:var(--text-muted)">
        <i class="fas fa-spinner fa-spin" style="font-size:28px;color:var(--primary-light)"></i>
        <p style="margin-top:12px;font-weight:500">Cargando personal...</p>
      </div>
    </div>
  </div>

  <!-- Modal de Enrollment -->
  <div class="modal-overlay" id="enroll-modal">
    <div class="modal">
      <div class="modal-icon"><i class="fas fa-fingerprint"></i></div>
      <h2>Registrar Huella</h2>
      <p class="modal-sub" id="modal-teacher-name">Maestro</p>
      <div id="fp-status"><span style="color:var(--text-muted)">Preparando escáner...</span></div>
      <img id="fp-image" alt="Huella capturada" />
      <div id="modal-result"></div>
      <button class="btn-close-modal" onclick="closeModal()">Cerrar</button>
    </div>
  </div>

  <script src="../assets/js/fingerprint.js"></script>
  <script>
    let currentMaestroId = null;

    function getInitials(name) {
      return name.split(' ').filter(Boolean).slice(0,2).map(w => w[0]).join('').toUpperCase();
    }

    // Cargar lista de maestros
    function loadMaestros() {
      fetch('../api/huella_listar.php')
        .then(r => r.json())
        .then(data => {
          if (!data.success) return;

          const grid = document.getElementById('maestros-grid');
          let enrolled = 0, pending = 0;

          grid.innerHTML = data.maestros.map(m => {
            const has = parseInt(m.tiene_huella);
            if (has) enrolled++; else pending++;

            const initials = getInitials(m.nombre_completo);
            const contract = m.tipo_contrato || 'Sin contrato';
            const contractLabel = contract === 'tiempo_completo' ? 'Tiempo completo'
              : contract === 'medio_tiempo' ? 'Medio tiempo'
              : contract === 'Administrativo' ? '👔 Administrativo'
              : contract;

            return `
              <div class="maestro-card ${has ? 'has-fp' : 'no-fp'}" data-name="${m.nombre_completo.toLowerCase()}">
                <div class="card-body">
                  <div class="card-top">
                    <div class="avatar ${has ? 'enrolled' : 'pending'}">${initials}</div>
                    <div class="card-info">
                      <div class="card-name" title="${m.nombre_completo}">${m.nombre_completo}</div>
                      <div class="card-contract">${contractLabel}</div>
                    </div>
                  </div>

                  <div class="card-meta">
                    ${has
                      ? `<span class="badge registered"><i class="fas fa-check-circle"></i> Registrada</span>`
                      : `<span class="badge pending"><i class="fas fa-clock"></i> Pendiente</span>`
                    }
                    ${m.fecha_huella
                      ? `<span class="badge-date"><i class="far fa-calendar"></i> ${m.fecha_huella}</span>`
                      : ''
                    }
                  </div>

                  ${has && m.imagen_path
                    ? `<div class="fp-thumb-row">
                        <img class="fp-thumb" src="../${m.imagen_path}" alt="Huella" onerror="this.parentElement.style.display='none'" />
                        <div class="fp-thumb-label">
                          <strong>Huella verificada</strong>
                          Dedo índice derecho
                        </div>
                      </div>`
                    : (has && m.imagen_base64
                      ? `<div class="fp-thumb-row">
                          <img class="fp-thumb" src="data:image/bmp;base64,${m.imagen_base64}" alt="Huella" />
                          <div class="fp-thumb-label">
                            <strong>Huella verificada</strong>
                            Dedo índice derecho
                          </div>
                        </div>`
                      : '')
                  }

                  <button class="btn-action ${has ? 'btn-re-enroll' : 'btn-enroll'}"
                          onclick="startEnroll(${m.maestro_id}, '${m.nombre_completo.replace(/'/g, "\\'")}')">
                    <i class="fas fa-fingerprint"></i>
                    ${has ? 'Re-registrar huella' : 'Registrar huella'}
                  </button>
                </div>
              </div>
            `;
          }).join('');

          document.getElementById('stat-total').textContent = data.total;
          document.getElementById('stat-enrolled').textContent = enrolled;
          document.getElementById('stat-pending').textContent = pending;
        })
        .catch(err => {
          document.getElementById('maestros-grid').innerHTML =
            `<div style="grid-column:1/-1;text-align:center;padding:50px;color:var(--danger)">
              <i class="fas fa-exclamation-triangle" style="font-size:28px"></i>
              <p style="margin-top:10px">Error cargando maestros: ${err.message}</p>
            </div>`;
        });
    }

    // Buscador
    document.getElementById('searchInput').addEventListener('input', function() {
      const term = this.value.trim().toLowerCase();
      document.querySelectorAll('.maestro-card').forEach(card => {
        const name = card.dataset.name || '';
        card.style.display = name.includes(term) ? '' : 'none';
      });
    });

    // Iniciar enrollment
    function startEnroll(maestroId, nombre) {
      currentMaestroId = maestroId;
      document.getElementById('modal-teacher-name').textContent = nombre;
      document.getElementById('enroll-modal').classList.add('active');
      document.getElementById('fp-image').style.display = 'none';
      document.getElementById('modal-result').innerHTML = '';

      FP.enroll(maestroId, 'right-index-finger', (result) => {
        if (result.status === 'ok' && result.saved) {
          document.getElementById('modal-result').innerHTML =
            `<div style="background:linear-gradient(135deg,rgba(0,155,72,.1),rgba(0,155,72,.05));color:var(--accent);padding:14px;border-radius:12px;margin-top:12px;font-weight:600;border:1px solid rgba(0,155,72,.2)">
              <i class="fas fa-check-circle"></i> Huella guardada exitosamente
            </div>`;
          setTimeout(loadMaestros, 1500);
        } else if (result.status === 'error') {
          document.getElementById('modal-result').innerHTML =
            `<div style="background:rgba(229,57,53,.08);color:var(--danger);padding:14px;border-radius:12px;margin-top:12px;font-weight:600;border:1px solid rgba(229,57,53,.2)">
              <i class="fas fa-times-circle"></i> ${result.message || 'Error al registrar'}
            </div>`;
        }
      });
    }

    function closeModal() {
      document.getElementById('enroll-modal').classList.remove('active');
    }

    // Verificar agente
    function checkAgent() {
      FP.connect(() => {
        FP.onMessage = (data) => {
          if (data.status === 'ok') {
            document.getElementById('agent-dot').className = 'status-dot on';
            document.getElementById('agent-status').textContent = 'Conectado — ' + (data.scanner || 'Escáner listo');
          }
        };
        FP._send({ action: 'status' });
      });

      setTimeout(() => {
        if (!FP.connected) {
          document.getElementById('agent-dot').className = 'status-dot off';
          document.getElementById('agent-status').innerHTML = '<span style="color:var(--danger)">Desconectado</span> — Ejecuta <code>start_agent.bat</code>';
        }
      }, 2000);
    }

    // Inicializar
    loadMaestros();
    checkAgent();
  </script>
</body>
</html>
