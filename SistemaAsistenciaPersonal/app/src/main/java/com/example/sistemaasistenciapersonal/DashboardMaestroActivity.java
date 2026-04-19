package com.example.sistemaasistenciapersonal;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import com.example.sistemaasistenciapersonal.utils.SessionManager;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import java.text.SimpleDateFormat;
import java.util.Arrays;
import java.util.Date;
import java.util.List;
import java.util.Locale;

public class DashboardMaestroActivity extends AppCompatActivity {

    private SessionManager session;

    // Módulos permitidos para maestros
    private static final List<String> MODULOS_PERMITIDOS = Arrays.asList(
            "asistencias", "horarios", "justificaciones"
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard_admin); // Reutiliza el mismo layout

        session = new SessionManager(this);

        if (!session.isLoggedIn()) {
            startActivity(new Intent(this, LoginActivity.class));
            finish();
            return;
        }

        setupHeader();
        setupBottomNav();
    }

    private void setupHeader() {
        TextView tvSaludo = findViewById(R.id.tvSaludo);
        TextView tvSubtitulo = findViewById(R.id.tvSubtitulo);
        TextView tvFecha = findViewById(R.id.tvFecha);

        String nombre = session.getUserNombre();
        if (tvSaludo != null) {
            tvSaludo.setText("Hola, " + (nombre != null && !nombre.isEmpty() ? nombre : "Maestro"));
        }
        if (tvSubtitulo != null) {
            tvSubtitulo.setText("Escuela Secundaria General Emperador Cuauhtémoc");
        }
        if (tvFecha != null) {
            SimpleDateFormat sdf = new SimpleDateFormat("d 'de' MMMM 'de' yyyy", new Locale("es", "MX"));
            tvFecha.setText("Fecha: " + sdf.format(new Date()));
        }
    }

    private void setupBottomNav() {
        BottomNavigationView bottomNav = findViewById(R.id.bottom_nav);
        if (bottomNav == null) return;

        bottomNav.setOnItemSelectedListener(item -> {
            String moduleName = null;
            int itemId = item.getItemId();
            if (itemId == R.id.nav_horarios) {
                moduleName = "horarios";
            } else if (itemId == R.id.nav_asistencias) {
                moduleName = "asistencias";
            } else if (itemId == R.id.nav_justificaciones) {
                moduleName = "justificaciones";
            } else {
                // Módulos no permitidos para maestros
                Toast.makeText(this,
                        "⚠ No tienes permiso para acceder a este módulo",
                        Toast.LENGTH_SHORT).show();
                return false;
            }

            if (moduleName != null) {
                openModule(moduleName);
            }
            return true;
        });
    }

    // Método para manejar clics en módulos del grid (llamado desde XML)
    public void onModuleClick(View view) {
        String tag = view.getTag().toString();

        if (!MODULOS_PERMITIDOS.contains(tag)) {
            new AlertDialog.Builder(this)
                    .setTitle("🔒 Acceso Restringido")
                    .setMessage("No tienes permiso para acceder al módulo \"" +
                            tag.substring(0, 1).toUpperCase() + tag.substring(1) + "\".\n\n" +
                            "Este módulo solo está disponible para Director/Admin.")
                    .setPositiveButton("Entendido", null)
                    .setIcon(android.R.drawable.ic_dialog_alert)
                    .show();
            return;
        }

        openModule(tag);
    }

    // Logout (llamado desde XML)
    public void onLogoutClick(View view) {
        new AlertDialog.Builder(this)
                .setTitle("Cerrar Sesión")
                .setMessage("¿Seguro que deseas salir?")
                .setPositiveButton("Sí", (d, w) -> {
                    session.clearSession();
                    Intent intent = new Intent(this, LoginActivity.class);
                    intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                    startActivity(intent);
                    finish();
                })
                .setNegativeButton("No", null)
                .show();
    }

    private void openModule(String moduleName) {
        Intent intent = new Intent(this, ModuleActivity.class);
        intent.putExtra("MODULE_NAME", moduleName);
        intent.putExtra("USER_ROL", session.getUserRol());
        startActivity(intent);
    }
}