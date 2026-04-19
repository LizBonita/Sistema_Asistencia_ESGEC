package com.example.sistemaasistenciapersonal;

import android.os.Bundle;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import androidx.fragment.app.Fragment;
import androidx.fragment.app.FragmentManager;
import androidx.fragment.app.FragmentTransaction;
import com.example.sistemaasistenciapersonal.ui.justificaciones.JustificacionesPendientesFragment;
import com.example.sistemaasistenciapersonal.ui.usuarios.GestionUsuariosFragment;
import com.example.sistemaasistenciapersonal.ui.maestros.GestionMaestrosFragment;
import com.example.sistemaasistenciapersonal.ui.materias.GestionMateriasFragment;
import com.example.sistemaasistenciapersonal.ui.grupos.GestionGruposFragment;
import com.example.sistemaasistenciapersonal.ui.horarios.GestionHorariosFragment;
import com.example.sistemaasistenciapersonal.ui.asistencias.VerAsistenciasFragment;
import com.example.sistemaasistenciapersonal.ui.reportes.ReporteQuincenalFragment;
import java.util.Arrays;
import java.util.List;

public class ModuleActivity extends AppCompatActivity {

    // Módulos que solo admin/director pueden acceder
    private static final List<String> ADMIN_ONLY_MODULES = Arrays.asList(
            "usuarios", "maestros", "materias", "grupos"
    );

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_module);

        String moduleName = getIntent().getStringExtra("MODULE_NAME");
        String userRol = getIntent().getStringExtra("USER_ROL");

        if (moduleName == null) {
            finish();
            return;
        }

        // ═══ RESTRICCIÓN POR ROL ═══
        // Rol 3 = Maestro, no puede acceder a módulos admin
        if ("3".equals(userRol) && ADMIN_ONLY_MODULES.contains(moduleName)) {
            Toast.makeText(this,
                    "⚠ No tienes permiso para acceder a este módulo",
                    Toast.LENGTH_LONG).show();
            finish();
            return;
        }

        Fragment fragment = null;
        String title = "";

        switch (moduleName) {
            case "usuarios":
                fragment = new GestionUsuariosFragment();
                title = "Gestionar Usuarios";
                break;
            case "maestros":
                fragment = new GestionMaestrosFragment();
                title = "Gestionar Maestros";
                break;
            case "materias":
                fragment = new GestionMateriasFragment();
                title = "Gestionar Materias";
                break;
            case "grupos":
                fragment = new GestionGruposFragment();
                title = "Gestionar Grupos";
                break;
            case "horarios":
                fragment = new GestionHorariosFragment();
                title = "Gestionar Horarios";
                break;
            case "asistencias":
                fragment = new VerAsistenciasFragment();
                title = "Asistencias Diarias";
                break;
            case "reporte":
                fragment = new ReporteQuincenalFragment();
                title = "Reporte Quincenal";
                break;
            case "justificaciones":
                fragment = new JustificacionesPendientesFragment();
                title = "Justificaciones Pendientes";
                break;
            default:
                Toast.makeText(this, "Módulo no reconocido: " + moduleName, Toast.LENGTH_SHORT).show();
                finish();
                return;
        }

        // Configurar el título de la Activity
        if (getSupportActionBar() != null) {
            getSupportActionBar().setTitle(title);
            getSupportActionBar().setDisplayHomeAsUpEnabled(true);
        }

        // Cargar el fragment
        FragmentManager fm = getSupportFragmentManager();
        FragmentTransaction transaction = fm.beginTransaction();
        transaction.replace(R.id.module_fragment_container, fragment);
        transaction.commit();
    }

    @Override
    public boolean onSupportNavigateUp() {
        getOnBackPressedDispatcher().onBackPressed();
        return true;
    }
}