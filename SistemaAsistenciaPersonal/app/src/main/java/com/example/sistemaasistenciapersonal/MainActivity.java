package com.example.sistemaasistenciapersonal;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import androidx.appcompat.app.AppCompatActivity;
import com.example.sistemaasistenciapersonal.utils.SessionManager;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Si ya hay sesión activa, ir directo al dashboard
        SessionManager session = new SessionManager(this);
        if (session.isLoggedIn()) {
            String rol = session.getUserRol();
            Intent intent;
            if ("3".equals(rol)) {
                intent = new Intent(this, DashboardMaestroActivity.class);
            } else {
                intent = new Intent(this, DashboardAdminActivity.class);
            }
            startActivity(intent);
            finish();
            return;
        }

        setContentView(R.layout.activity_main);

        Button btnIniciarSesion = findViewById(R.id.btnIniciarSesion);
        btnIniciarSesion.setOnClickListener(v -> openLogin());
    }

    // Método llamado al hacer clic en el botón
    public void onLoginClick(View view) {
        openLogin();
    }

    private void openLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        startActivity(intent);
    }
}