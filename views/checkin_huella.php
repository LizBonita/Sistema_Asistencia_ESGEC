<?php
// views/checkin_huella.php
// Vista de Check-in por Huella Dactilar — Modo Kiosko
// NO requiere login. La huella ES la autenticación.
date_default_timezone_set('America/Mexico_City');
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Check-in Biométrico - Sistema de Asistencia</title>
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
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      padding:20px;
    }

    .checkin-container{
      background:rgba(255,255,255,.97);
      border-radius:26px;
      box-shadow:0 18px 46px rgba(0,0,0,.18);
      padding:40px;
      max-width:520px;
      width:100%;
      text-align:center;
      border:1px solid rgba(14,77,146,.14);
    }

    .school-name{
      font-size:13px;
      color:#5f6b7a;
      text-transform:uppercase;
      letter-spacing:2px;
      margin-bottom:8px;
    }

    h1{
      font-size:28px;
      color:#0A3A6F;
      margin-bottom:6px;
    }

    .subtitle{
      color:#5f6b7a;
      font-size:15px;
      margin-bottom:30px;
    }

    .scanner-area{
      background:linear-gradient(135deg,rgba(14,77,146,.06),rgba(0,155,72,.06));
      border-radius:18px;
      padding:40px 20px;
      margin:20px 0;
      position:relative;
      overflow:hidden;
      border:2px dashed rgba(14,77,146,.2);
      transition:all .3s;
    }

    .scanner-area.scanning{
      border-color:#2196f3;
      animation:pulse-border 1.5s infinite;
    }

    .scanner-area.success{
      border-color:#00c853;
      background:rgba(0,200,83,.08);
    }

    .scanner-area.error{
      border-color:#f44336;
      background:rgba(244,67,54,.08);
    }

    @keyframes pulse-border{
      0%,100%{border-color:#2196f3; box-shadow:0 0 0 0 rgba(33,150,243,.3)}
      50%{border-color:#64b5f6; box-shadow:0 0 0 12px rgba(33,150,243,0)}
    }

    .scanner-icon{
      font-size:64px;
      margin-bottom:15px;
      display:block;
    }

    .scanner-icon.default{ color:rgba(14,77,146,.3); }
    .scanner-icon.scanning{ color:#2196f3; animation:spin 2s linear infinite; }
    .scanner-icon.success{ color:#00c853; }
    .scanner-icon.error{ color:#f44336; }

    @keyframes spin{
      0%{transform:rotate(0)} 100%{transform:rotate(360deg)}
    }

    #fp-status{
      font-size:16px;
      font-weight:500;
      margin:10px 0;
    }

    #fp-image{
      display:none;
      max-width:200px;
      border-radius:12px;
      margin:15px auto;
      box-shadow:0 4px 16px rgba(0,0,0,.15);
      border:3px solid #0E4D92;
    }

    #fp-result{
      margin:15px 0;
    }

    @keyframes fadeIn{
      from{opacity:0;transform:translateY(10px)}
      to{opacity:1;transform:translateY(0)}
    }

    .btn-checkin{
      background:linear-gradient(135deg,#0E4D92,#009B48);
      color:#fff;
      border:none;
      padding:18px 40px;
      font-size:18px;
      font-weight:700;
      border-radius:14px;
      cursor:pointer;
      transition:all .3s;
      width:100%;
      margin-top:10px;
      letter-spacing:.5px;
    }

    .btn-checkin:hover{
      transform:translateY(-2px);
      box-shadow:0 8px 24px rgba(14,77,146,.3);
    }

    .btn-checkin:active{
      transform:translateY(0);
    }

    .btn-checkin:disabled{
      opacity:.6;
      cursor:not-allowed;
      transform:none;
    }

    .clock{
      font-size:48px;
      font-weight:700;
      color:#0A3A6F;
      margin:10px 0;
      font-variant-numeric:tabular-nums;
    }

    .date{
      color:#5f6b7a;
      font-size:15px;
      margin-bottom:20px;
    }

    .back-link{
      display:inline-block;
      margin-top:20px;
      color:#0E4D92;
      text-decoration:none;
      font-weight:500;
    }

    .back-link:hover{ text-decoration:underline; }

    .history{
      margin-top:25px;
      padding-top:20px;
      border-top:1px solid rgba(14,77,146,.12);
    }

    .history h3{
      font-size:14px;
      color:#5f6b7a;
      text-transform:uppercase;
      letter-spacing:1px;
      margin-bottom:10px;
    }

    .history-item{
      display:flex;
      align-items:center;
      gap:10px;
      padding:8px 12px;
      background:rgba(14,77,146,.04);
      border-radius:10px;
      margin:6px 0;
      font-size:14px;
    }

    .history-item .dot{
      width:8px;height:8px;border-radius:50%;
    }

    .history-item .dot.entrada{background:#00c853}
    .history-item .dot.salida{background:#2196f3}
  </style>
</head>
<body>
  <div class="checkin-container">
    <div class="school-name">Escuela Secundaria "Emperador Cuauhtémoc"</div>
    <h1><i class="fas fa-fingerprint"></i> Check-in Biométrico</h1>
    <p class="subtitle">Registro de asistencia por huella dactilar</p>

    <div class="clock" id="clock">--:--:--</div>
    <div class="date" id="date"></div>

    <div class="scanner-area" id="scanner-area">
      <span class="scanner-icon default" id="scanner-icon"><i class="fas fa-fingerprint"></i></span>
      <div id="fp-status"><span style="color:#5f6b7a">Presiona el botón para iniciar</span></div>
      <img id="fp-image" alt="Huella capturada" />
    </div>

    <div id="fp-result"></div>

    <div class="btn-checkin" id="btn-checkin" style="cursor:default">
      <i class="fas fa-fingerprint"></i> &nbsp; Coloque su dedo en el escáner
    </div>

    <a href="inicio.php" class="back-link">
      <i class="fas fa-arrow-left"></i> Volver al inicio
    </a>

    <div class="history" id="history-section" style="display:none">
      <h3>Registros recientes</h3>
      <div id="history-list"></div>
    </div>
  </div>

  <script src="../assets/js/fingerprint.js"></script>
  <script>
    // Reloj en tiempo real
    function updateClock() {
      const now = new Date();
      document.getElementById('clock').textContent =
        now.toLocaleTimeString('es-MX', {hour:'2-digit',minute:'2-digit',second:'2-digit',timeZone:'America/Mexico_City'});
      document.getElementById('date').textContent =
        now.toLocaleDateString('es-MX', {weekday:'long',year:'numeric',month:'long',day:'numeric',timeZone:'America/Mexico_City'});
    }
    setInterval(updateClock, 1000);
    updateClock();

    // Scanner area visual states
    function setScannerState(state) {
      const area = document.getElementById('scanner-area');
      const icon = document.getElementById('scanner-icon');
      area.className = 'scanner-area ' + state;

      const iconClasses = {
        '': 'fas fa-fingerprint',
        'scanning': 'fas fa-spinner',
        'success': 'fas fa-check-circle',
        'error': 'fas fa-times-circle',
      };
      icon.innerHTML = '<i class="' + (iconClasses[state] || iconClasses['']) + '"></i>';
      icon.className = 'scanner-icon ' + (state || 'default');
    }

    // ══════════ MODO KIOSKO: Auto-scan continuo ══════════
    let scanning = false;

    function startAutoScan() {
      if (scanning) return;
      scanning = true;

      const btn = document.getElementById('btn-checkin');
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> &nbsp; Esperando huella...';
      btn.style.background = 'linear-gradient(135deg, #0E4D92, #009B48)';
      setScannerState('scanning');

      document.getElementById('fp-image').style.display = 'none';
      document.getElementById('fp-result').innerHTML = '';
      document.getElementById('fp-status').innerHTML = '<span style="color:#0E4D92">Coloque su dedo en el escáner</span>';

      FP.checkIn((result) => {
        scanning = false;

        if (result.match && result.attendance && result.attendance.success) {
          setScannerState('success');
          btn.innerHTML = '<i class="fas fa-check"></i> &nbsp; ' + (result.attendance.nombre || 'Registrado');
          btn.style.background = '#009B48';

          // Mostrar nombre grande
          document.getElementById('fp-result').innerHTML = `
            <div style="font-size:20px;font-weight:700;color:#0A3A6F;margin:8px 0">
              ${result.attendance.nombre || 'Maestro'}
            </div>
            <div style="font-size:16px;color:${result.attendance.tipo === 'entrada' ? '#009B48' : '#0E4D92'};font-weight:500">
              ${result.attendance.tipo === 'entrada' ? '🟢 ENTRADA' : '🔵 SALIDA'} registrada — ${result.attendance.hora || ''}
            </div>
          `;

          addToHistory(result.attendance);

          // Auto-reiniciar después de 4 segundos
          setTimeout(() => {
            resetAndScan();
          }, 4000);

        } else if (result.match === false) {
          setScannerState('error');
          btn.innerHTML = '<i class="fas fa-times"></i> &nbsp; Huella no reconocida';
          btn.style.background = '#e53935';

          // Reiniciar después de 3 segundos
          setTimeout(() => {
            resetAndScan();
          }, 3000);

        } else {
          // Error de conexión u otro
          setTimeout(() => {
            resetAndScan();
          }, 3000);
        }
      });
    }

    function resetAndScan() {
      setScannerState('');
      const btn = document.getElementById('btn-checkin');
      btn.innerHTML = '<i class="fas fa-fingerprint"></i> &nbsp; Coloque su dedo en el escáner';
      btn.style.background = 'linear-gradient(135deg, #0E4D92, #009B48)';
      document.getElementById('fp-result').innerHTML = '';
      document.getElementById('fp-image').style.display = 'none';

      // Esperar 1 segundo y volver a escanear
      setTimeout(() => startAutoScan(), 1000);
    }

    // Historial de registros en la sesión
    const historyItems = [];
    function addToHistory(att) {
      historyItems.unshift(att);
      const section = document.getElementById('history-section');
      const list = document.getElementById('history-list');
      section.style.display = 'block';
      list.innerHTML = historyItems.slice(0, 8).map(h => `
        <div class="history-item">
          <span class="dot ${h.tipo}"></span>
          <strong>${h.nombre || 'Maestro'}</strong>
          <span style="margin-left:auto;color:#5f6b7a">${h.tipo === 'entrada' ? '🟢 Entrada' : '🔵 Salida'} ${h.hora || ''}</span>
        </div>
      `).join('');
    }

    // ══════════ ARRANQUE AUTOMÁTICO ══════════
    // Conectar al WebSocket y comenzar escaneo continuo
    FP.connect(() => {
      startAutoScan();
    });
  </script>
</body>
</html>
