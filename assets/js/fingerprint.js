/**
 * fingerprint.js — Cliente WebSocket para Agente de Huella Dactilar
 * Sistema de Asistencia - Escuela Secundaria "Emperador Cuauhtémoc"
 */

const FP = {
  ws: null,
  wsUrl: 'ws://localhost:8765',
  baseUrl: window.location.hostname === 'localhost' 
    ? '/sistema_asistencia/' 
    : '/',
  connected: false,
  onMessage: null,
  reconnectTimer: null,

  // ─── Conexion WebSocket ──────────────────────────────

  connect(onReady) {
    if (this.ws && this.ws.readyState === WebSocket.OPEN) {
      if (onReady) onReady();
      return;
    }

    try {
      this.ws = new WebSocket(this.wsUrl);
    } catch (e) {
      this._setStatus('error', 'No se pudo crear conexión WebSocket');
      return;
    }

    this.ws.onopen = () => {
      this.connected = true;
      this._setStatus('connected', 'Escáner conectado');
      if (onReady) onReady();
    };

    this.ws.onmessage = (event) => {
      try {
        const data = JSON.parse(event.data);
        if (this.onMessage) this.onMessage(data);
      } catch (e) {
        console.error('Error parseando respuesta:', e);
      }
    };

    this.ws.onclose = () => {
      this.connected = false;
      this._setStatus('disconnected', 'Escáner desconectado');
    };

    this.ws.onerror = () => {
      this.connected = false;
      this._setStatus('error', 'Error de conexión. ¿Está corriendo el agente?');
    };
  },

  disconnect() {
    if (this.ws) {
      this.ws.close();
      this.ws = null;
      this.connected = false;
    }
  },

  _send(data) {
    if (!this.ws || this.ws.readyState !== WebSocket.OPEN) {
      this._setStatus('error', 'No hay conexión con el escáner');
      return false;
    }
    this.ws.send(JSON.stringify(data));
    return true;
  },

  _setStatus(type, msg) {
    const statusEl = document.getElementById('fp-status');
    if (!statusEl) return;
    const colors = {
      connected: '#00c853',
      disconnected: '#ff9800',
      error: '#f44336',
      scanning: '#2196f3',
      success: '#00c853',
    };
    const icons = {
      connected: '🟢',
      disconnected: '🟠',
      error: '🔴',
      scanning: '🔵',
      success: '✅',
    };
    statusEl.innerHTML = `<span style="color:${colors[type] || '#999'}">${icons[type] || '⚪'} ${msg}</span>`;
  },

  // ─── Acciones ────────────────────────────────────────

  checkStatus() {
    this.connect(() => {
      this._send({ action: 'status' });
    });
  },

  /**
   * Registrar huella de un maestro
   * @param {number} maestroId 
   * @param {string} dedo 
   * @param {function} callback(result)
   */
  enroll(maestroId, dedo, callback) {
    this._setStatus('scanning', 'Pon tu dedo en el escáner (5 veces)...');

    this.onMessage = (data) => {
      if (data.status === 'scanning') {
        this._setStatus('scanning', data.message);
        return;
      }

      if (data.status === 'ok' && data.template) {
        this._setStatus('success', '¡Huella registrada exitosamente!');

        // Mostrar imagen si existe
        if (data.imagen_base64) {
          this._showImage(data.imagen_base64);
        }

        // Guardar en backend PHP
        fetch(this.baseUrl + 'api/huella_registrar.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            maestro_id: maestroId,
            template: data.template,
            imagen_path: data.imagen_path || '',
            imagen_base64: data.imagen_base64 || '',
            dedo: dedo || 'right-index-finger',
          }),
        })
          .then(r => r.json())
          .then(phpResult => {
            if (callback) callback({ ...data, saved: phpResult.success });
          })
          .catch(err => {
            console.error('Error guardando en BD:', err);
            if (callback) callback({ ...data, saved: false });
          });

      } else if (data.status === 'error') {
        this._setStatus('error', data.message);
        if (callback) callback(data);
      }
    };

    this.connect(() => {
      this._send({ action: 'enroll', maestro_id: maestroId, dedo: dedo });
    });
  },

  /**
   * Check-in biométrico: identificar maestro y registrar asistencia
   * @param {function} callback(result)
   */
  checkIn(callback) {
    this._setStatus('scanning', 'Cargando templates...');

    // 1. Obtener todos los templates del backend
    fetch(this.baseUrl + 'api/huella_verificar.php')
      .then(r => r.json())
      .then(data => {
        if (!data.success || !data.templates || data.templates.length === 0) {
          this._setStatus('error', 'No hay huellas registradas');
          if (callback) callback({ status: 'error', message: 'No hay huellas registradas' });
          return;
        }

        this._setStatus('scanning', `Pon tu dedo en el escáner... (${data.total} maestros registrados)`);

        // 2. Enviar templates al agente para identify
        this.onMessage = (agentData) => {
          if (agentData.status === 'scanning') {
            this._setStatus('scanning', agentData.message);
            return;
          }

          if (agentData.status === 'ok') {
            if (agentData.match && agentData.maestro_id) {
              // 3. Match encontrado — registrar asistencia en PHP
              this._setStatus('success', '¡Huella reconocida!');

              if (agentData.imagen_base64) {
                this._showImage(agentData.imagen_base64);
              }

              fetch(this.baseUrl + 'api/huella_verificar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                  maestro_id: agentData.maestro_id,
                  imagen_path: agentData.imagen_path || '',
                }),
              })
                .then(r => r.json())
                .then(phpResult => {
                  if (phpResult.success) {
                    this._showResult(phpResult);
                  }
                  if (callback) callback({ ...agentData, attendance: phpResult });
                })
                .catch(err => {
                  console.error('Error registrando asistencia:', err);
                });

            } else {
              this._setStatus('error', 'Huella no reconocida. Intenta de nuevo.');
              if (agentData.imagen_base64) {
                this._showImage(agentData.imagen_base64);
              }
              if (callback) callback(agentData);
            }
          } else if (agentData.status === 'error') {
            this._setStatus('error', agentData.message);
            if (callback) callback(agentData);
          }
        };

        this.connect(() => {
          this._send({ action: 'identify', templates: data.templates });
        });
      })
      .catch(err => {
        this._setStatus('error', 'Error cargando templates: ' + err.message);
      });
  },

  // ─── UI Helpers ──────────────────────────────────────

  _showImage(base64Bmp) {
    const imgEl = document.getElementById('fp-image');
    if (!imgEl) return;
    imgEl.src = 'data:image/bmp;base64,' + base64Bmp;
    imgEl.style.display = 'block';
  },

  _showResult(result) {
    const resultEl = document.getElementById('fp-result');
    if (!resultEl) return;

    const tipoColors = {
      entrada: '#00c853',
      salida: '#2196f3',
      completo: '#ff9800',
    };

    const tipoIcons = {
      entrada: '🟢',
      salida: '🔵',
      completo: '🟠',
    };

    resultEl.innerHTML = `
      <div style="background:${tipoColors[result.tipo] || '#666'};color:#fff;padding:20px;border-radius:12px;text-align:center;margin:15px 0;animation:fadeIn .3s">
        <div style="font-size:48px">${tipoIcons[result.tipo] || '✅'}</div>
        <div style="font-size:24px;font-weight:700;margin:8px 0">${result.nombre || 'Maestro'}</div>
        <div style="font-size:18px">${result.message}</div>
        ${result.hora ? `<div style="font-size:32px;font-weight:700;margin-top:8px">${result.hora}</div>` : ''}
      </div>
    `;

    // Auto-limpiar después de 5 segundos
    setTimeout(() => {
      resultEl.innerHTML = '';
    }, 5000);
  },
};
