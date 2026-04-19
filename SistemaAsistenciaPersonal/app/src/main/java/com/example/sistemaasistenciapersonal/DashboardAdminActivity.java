package com.example.sistemaasistenciapersonal;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AlertDialog;
import androidx.appcompat.app.AppCompatActivity;
import com.example.sistemaasistenciapersonal.model.DashboardStats;
import com.example.sistemaasistenciapersonal.utils.SessionManager;
import com.google.android.material.bottomnavigation.BottomNavigationView;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

public class DashboardAdminActivity extends AppCompatActivity {

    private BottomNavigationView bottomNav;
    private SessionManager session;

    // KPI TextViews
    private TextView tvSaludo, tvSubtitulo, tvFecha;
    private TextView tvMaestrosCount, tvGruposCount, tvMateriasCount;
    private TextView tvAsistenciasHoy, tvPuntuales, tvRetardos, tvSinRegistrar;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dashboard_admin);

        session = new SessionManager(this);

        // Verificar sesión
        if (!session.isLoggedIn()) {
            startActivity(new Intent(this, LoginActivity.class));
            finish();
            return;
        }

        initViews();
        setupBottomNav();
        setupHeader();
        loadDashboardStats();
    }

    @Override
    protected void onResume() {
        super.onResume();
        loadDashboardStats(); // Refrescar al volver
    }

    private void initViews() {
        bottomNav = findViewById(R.id.bottom_nav);
        tvSaludo = findViewById(R.id.tvSaludo);
        tvSubtitulo = findViewById(R.id.tvSubtitulo);
        tvFecha = findViewById(R.id.tvFecha);
        tvMaestrosCount = findViewById(R.id.tvMaestrosCount);
        tvGruposCount = findViewById(R.id.tvGruposCount);
        tvMateriasCount = findViewById(R.id.tvMateriasCount);
        tvAsistenciasHoy = findViewById(R.id.tvAsistenciasHoy);
        tvPuntuales = findViewById(R.id.tvPuntuales);
        tvRetardos = findViewById(R.id.tvRetardos);
        tvSinRegistrar = findViewById(R.id.tvSinRegistrar);
    }

    private void setupHeader() {
        // Saludo dinámico
        String nombre = session.getUserNombre();
        String rol = session.getUserRol();
        String rolDisplay = "Administrador";
        if ("1".equals(rol)) rolDisplay = "Director(a)";
        else if ("3".equals(rol)) rolDisplay = "Maestro(a)";
        else if ("4".equals(rol)) rolDisplay = "Administrador";

        if (tvSaludo != null) {
            tvSaludo.setText("Bienvenido, " + rolDisplay);
        }
        if (tvSubtitulo != null) {
            tvSubtitulo.setText("Escuela Secundaria General Emperador Cuauhtémoc");
        }

        // Fecha dinámica
        if (tvFecha != null) {
            SimpleDateFormat sdf = new SimpleDateFormat("d 'de' MMMM 'de' yyyy", new Locale("es", "MX"));
            tvFecha.setText("Fecha: " + sdf.format(new Date()));
        }
    }

    private void loadDashboardStats() {
        ApiService.getClient().create(ApiInterface.class)
                .getDashboardStats()
                .enqueue(new Callback<DashboardStats>() {
                    @Override
                    public void onResponse(Call<DashboardStats> call, Response<DashboardStats> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            DashboardStats s = response.body();
                            if (tvMaestrosCount != null) tvMaestrosCount.setText(String.valueOf(s.maestros));
                            if (tvGruposCount != null) tvGruposCount.setText(String.valueOf(s.grupos));
                            if (tvMateriasCount != null) tvMateriasCount.setText(String.valueOf(s.materias));
                            if (tvAsistenciasHoy != null) tvAsistenciasHoy.setText(String.valueOf(s.asistencias_hoy));
                            if (tvPuntuales != null) tvPuntuales.setText(String.valueOf(s.puntuales));
                            if (tvRetardos != null) tvRetardos.setText(String.valueOf(s.retardos));
                            if (tvSinRegistrar != null) tvSinRegistrar.setText(String.valueOf(s.sin_registrar));
                        }
                    }

                    @Override
                    public void onFailure(Call<DashboardStats> call, Throwable t) {
                        Toast.makeText(DashboardAdminActivity.this,
                                "Sin conexión al servidor", Toast.LENGTH_SHORT).show();
                    }
                });
    }

    private void setupBottomNav() {
        bottomNav.setOnItemSelectedListener(item -> {
            String moduleName = null;
            int itemId = item.getItemId();
            if (itemId == R.id.nav_usuarios) {
                moduleName = "usuarios";
            } else if (itemId == R.id.nav_maestros) {
                moduleName = "maestros";
            } else if (itemId == R.id.nav_horarios) {
                moduleName = "horarios";
            } else if (itemId == R.id.nav_asistencias) {
                moduleName = "asistencias";
            } else if (itemId == R.id.nav_justificaciones) {
                moduleName = "justificaciones";
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