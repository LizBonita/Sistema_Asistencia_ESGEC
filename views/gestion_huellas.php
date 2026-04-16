<?php
// views/gestion_huellas.php
// Panel de administración de huellas dactilares
session_start();
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
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{
      font-family:'Roboto',sans-serif;
      min-height:100vh;
      background:
        radial-gradient(900px 480px at 10% -10%, rgba(0,155,72,.14), transparent 55%),
        radial-gradient(900px 480px at 90% -10%, rgba(14,77,146,.16), transparent 60%),
        linear-gradient(180deg, #ffffff 0%, #f3f6f9 60%, #eef2f6 100%);
      padding:20px;
      color:#0b1220;
    }

    .container{max-width:900px;margin:0 auto}

    .header{
      display:flex;
      align-items:center;
      justify-content:space-between;
      margin-bottom:25px;
      flex-wrap:wrap;
      gap:12px;
    }

    .header h1{
      font-size:26px;
      color:#0A3A6F;
    }

    .header .back-btn{
      background:rgba(14,77,146,.08);
      color:#0E4D92;
      text-decoration:none;
      padding:10px 20px;
      border-radius:10px;
      font-weight:500;
      transition:all .2s;
    }

    .header .back-btn:hover{
      background:rgba(14,77,146,.14);
    }

    .status-bar{
      background:rgba(255,255,255,.97);
      border:1px solid rgba(14,77,146,.14);
      border-radius:14px;
      padding:16px 20px;
      margin-bottom:20px;
      display:flex;
      align-items:center;
      justify-content:space-between;
      flex-wrap:wrap;
      gap:10px;
    }

    .status-bar .agent{
      display:flex;
      align-items:center;
      gap:8px;
      font-size:14px;
    }

    .status-dot{
      width:10px;height:10px;border-radius:50%;
      display:inline-block;
    }

    .status-dot.on{background:#00c853}
    .status-dot.off{background:#f44336}

    .stats{display:flex;gap:15px;flex-wrap:wrap}
    .stat{
      background:rgba(14,77,146,.06);
      padding:8px 16px;
      border-radius:8px;
      font-size:13px;
      font-weight:500;
    }

    .stat b{color:#0E4D92}

    .maestros-grid{
      display:grid;
      grid-template-columns:repeat(auto-fill,minmax(280px,1fr));
      gap:16px;
    }

    .maestro-card{
      background:rgba(255,255,255,.97);
      border:1px solid rgba(14,77,146,.14);
      border-radius:16px;
      padding:20px;
      transition:all .3s;
    }

    .maestro-card:hover{
      transform:translateY(-2px);
      box-shadow:0 8px 24px rgba(0,0,0,.1);
    }

    .maestro-card .name{
      font-size:16px;
      font-weight:700;
      color:#0A3A6F;
      margin-bottom:4px;
    }

    .maestro-card .contract{
      font-size:13px;
      color:#5f6b7a;
      margin-bottom:12px;
    }

    .maestro-card .fp-status{
      display:flex;
      align-items:center;
      gap:8px;
      font-size:13px;
      margin-bottom:12px;
    }

    .badge{
      display:inline-block;
      padding:4px 10px;
      border-radius:6px;
      font-size:12px;
      font-weight:600;
    }

    .badge.registered{background:rgba(0,200,83,.12);color:#00c853}
    .badge.pending{background:rgba(255,152,0,.12);color:#ff9800}

    .fp-thumb{
      width:60px;height:70px;
      border-radius:8px;
      object-fit:cover;
      border:2px solid #0E4D92;
      margin:8px 0;
    }

    .btn{
      border:none;
      padding:10px 18px;
      border-radius:10px;
      font-size:13px;
      font-weight:600;
      cursor:pointer;
      transition:all .2s;
      width:100%;
    }

    .btn-enroll{
      background:linear-gradient(135deg,#0E4D92,#009B48);
      color:#fff;
    }

    .btn-enroll:hover{
      box-shadow:0 4px 14px rgba(14,77,146,.3);
      transform:translateY(-1px);
    }

    .btn-re-enroll{
      background:rgba(14,77,146,.08);
      color:#0E4D92;
    }

    .btn-re-enroll:hover{
      background:rgba(14,77,146,.15);
    }

    .btn:disabled{
      opacity:.5;cursor:not-allowed;transform:none;
    }

    /* Modal de enrollment */
    .modal-overlay{
      display:none;
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,.5);
      z-index:1000;
      align-items:center;
      justify-content:center;
    }

    .modal-overlay.active{display:flex}

    .modal{
      background:#fff;
      border-radius:20px;
      padding:35px;
      max-width:440px;
      width:95%;
      text-align:center;
      box-shadow:0 18px 46px rgba(0,0,0,.25);
      animation:fadeIn .3s;
    }

    @keyframes fadeIn{
      from{opacity:0;transform:scale(.95)}
      to{opacity:1;transform:scale(1)}
    }

    .modal h2{
      color:#0A3A6F;
      margin-bottom:8px;
      font-size:22px;
    }

    .modal p{color:#5f6b7a;font-size:14px;margin-bottom:20px}

    .modal #fp-status{font-size:16px;margin:15px 0}

    .modal #fp-image{
      display:none;
      max-width:180px;
      border-radius:10px;
      border:3px solid #0E4D92;
      margin:12px auto;
    }

    .modal .btn-close{
      background:rgba(0,0,0,.06);
      color:#666;
      border:none;
      padding:10px 24px;
      border-radius:10px;
      cursor:pointer;
      font-size:14px;
      margin-top:15px;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h1><i class="fas fa-fingerprint"></i> Gestión de Huellas</h1>
      <a href="inicio.php" class="back-btn"><i class="fas fa-arrow-left"></i> Volver</a>
    </div>

    <div class="status-bar">
      <div class="agent">
        <span class="status-dot" id="agent-dot"></span>
        <span id="agent-status">Verificando agente...</span>
      </div>
      <div class="stats">
        <div class="stat"><b id="stat-total">-</b> maestros</div>
        <div class="stat"><b id="stat-enrolled">-</b> con huella</div>
        <div class="stat"><b id="stat-pending">-</b> pendientes</div>
      </div>
    </div>

    <div class="maestros-grid" id="maestros-grid">
      <div style="grid-column:1/-1;text-align:center;padding:40px;color:#5f6b7a">
        <i class="fas fa-spinner fa-spin" style="font-size:24px"></i>
        <p style="margin-top:10px">Cargando maestros...</p>
      </div>
    </div>
  </div>

  <!-- Modal de Enrollment -->
  <div class="modal-overlay" id="enroll-modal">
    <div class="modal">
      <h2><i class="fas fa-fingerprint"></i> Registrar Huella</h2>
      <p id="modal-teacher-name">Maestro</p>
      <div id="fp-status"><span style="color:#5f6b7a">Preparando escáner...</span></div>
      <img id="fp-image" alt="Huella capturada" />
      <div id="modal-result"></div>
      <button class="btn-close" onclick="closeModal()">Cerrar</button>
    </div>
  </div>

  <script src="../assets/js/fingerprint.js"></script>
  <script>
    let currentMaestroId = null;

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

            return `
              <div class="maestro-card">
                <div class="name">${m.nombre_completo}</div>
                <div class="contract">${m.tipo_contrato || 'Sin contrato'}</div>
                <div class="fp-status">
                  ${has
                    ? `<span class="badge registered">✅ Huella registrada</span>`
                    : `<span class="badge pending">⏳ Sin huella</span>`
                  }
                </div>
                ${has && m.imagen_path
                  ? `<img class="fp-thumb" src="../${m.imagen_path}" alt="Huella" onerror="this.style.display='none'" />`
                  : ''
                }
                ${m.fecha_huella ? `<div style="font-size:12px;color:#5f6b7a;margin-bottom:8px">Registrada: ${m.fecha_huella}</div>` : ''}
                <button class="btn ${has ? 'btn-re-enroll' : 'btn-enroll'}"
                        onclick="startEnroll(${m.maestro_id}, '${m.nombre_completo.replace(/'/g, "\\'")}')">
                  <i class="fas fa-fingerprint"></i>
                  ${has ? 'Re-registrar' : 'Registrar Huella'}
                </button>
              </div>
            `;
          }).join('');

          document.getElementById('stat-total').textContent = data.total;
          document.getElementById('stat-enrolled').textContent = enrolled;
          document.getElementById('stat-pending').textContent = pending;
        })
        .catch(err => {
          document.getElementById('maestros-grid').innerHTML =
            `<div style="grid-column:1/-1;text-align:center;padding:40px;color:#f44336">
              Error cargando maestros: ${err.message}
            </div>`;
        });
    }

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
            `<div style="background:#00c853;color:#fff;padding:14px;border-radius:10px;margin-top:10px">
              ✅ Huella guardada exitosamente
            </div>`;
          // Recargar lista
          setTimeout(loadMaestros, 1500);
        } else if (result.status === 'error') {
          document.getElementById('modal-result').innerHTML =
            `<div style="background:#f44336;color:#fff;padding:14px;border-radius:10px;margin-top:10px">
              ❌ ${result.message || 'Error al registrar'}
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
            document.getElementById('agent-status').textContent = 'Agente conectado — ' + (data.scanner || 'Escáner listo');
          }
        };
        FP._send({ action: 'status' });
      });

      // Si no conecta en 2 seg
      setTimeout(() => {
        if (!FP.connected) {
          document.getElementById('agent-dot').className = 'status-dot off';
          document.getElementById('agent-status').textContent = 'Agente desconectado — Ejecuta start_agent.bat';
        }
      }, 2000);
    }

    // Inicializar
    loadMaestros();
    checkAgent();
  </script>
</body>
</html>
