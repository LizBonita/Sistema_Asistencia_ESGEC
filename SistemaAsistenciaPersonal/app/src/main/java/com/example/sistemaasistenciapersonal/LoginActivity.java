package com.example.sistemaasistenciapersonal;

import android.content.Intent;
import android.os.Bundle;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;

import com.example.sistemaasistenciapersonal.model.LoginResponse;
import com.example.sistemaasistenciapersonal.utils.SessionManager;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class LoginActivity extends AppCompatActivity {

    private EditText editUsuario, editContrasena;
    private Button btnLogin;
    private SessionManager session;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        // Si ya tiene sesión activa, ir directo al dashboard
        session = new SessionManager(this);
        if (session.isLoggedIn()) {
            navegarSegunRol(Integer.parseInt(session.getUserRol()));
            return;
        }

        setContentView(R.layout.activity_login);

        editUsuario = findViewById(R.id.editUsuario);
        editContrasena = findViewById(R.id.editContrasena);
        btnLogin = findViewById(R.id.btnLogin);

        btnLogin.setOnClickListener(v -> login());
    }

    private void login() {
        String usuario = editUsuario.getText().toString().trim();
        String pass = editContrasena.getText().toString().trim();

        if (usuario.isEmpty() || pass.isEmpty()) {
            Toast.makeText(this, "Usuario y contraseña requeridos", Toast.LENGTH_SHORT).show();
            return;
        }

        btnLogin.setEnabled(false);
        btnLogin.setText("Conectando...");

        ApiService.getClient().create(ApiInterface.class)
                .login(usuario, pass)
                .enqueue(new Callback<LoginResponse>() {
                    @Override
                    public void onResponse(Call<LoginResponse> call, Response<LoginResponse> response) {
                        btnLogin.setEnabled(true);
                        btnLogin.setText("Iniciar Sesión");

                        if (response.isSuccessful() && response.body() != null) {
                            LoginResponse res = response.body();
                            if (res.success && res.user != null) {
                                // Guardar sesión completa
                                session.saveSession(
                                    res.user.id,
                                    String.valueOf(res.user.rol),
                                    res.user.nombre != null ? res.user.nombre : res.user.nombre_completo
                                );

                                navegarSegunRol(res.user.rol);
                            } else {
                                Toast.makeText(LoginActivity.this,
                                    res.message != null ? res.message : "Credenciales incorrectas",
                                    Toast.LENGTH_SHORT).show();
                            }
                        } else {
                            Toast.makeText(LoginActivity.this, "Error del servidor: " + response.code(), Toast.LENGTH_SHORT).show();
                        }
                    }

                    @Override
                    public void onFailure(Call<LoginResponse> call, Throwable t) {
                        btnLogin.setEnabled(true);
                        btnLogin.setText("Iniciar Sesión");
                        Toast.makeText(LoginActivity.this,
                            "Sin conexión al servidor.\nVerifica tu internet.",
                            Toast.LENGTH_LONG).show();
                    }
                });
    }

    private void navegarSegunRol(int rolId) {
        Intent intent;
        if (rolId == 1 || rolId == 4) {
            // Rol 1 = Director, Rol 4 = Admin
            intent = new Intent(this, DashboardAdminActivity.class);
        } else if (rolId == 3) {
            // Rol 3 = Maestro
            intent = new Intent(this, DashboardMaestroActivity.class);
        } else {
            // Cualquier otro rol → Dashboard Admin con permisos limitados
            intent = new Intent(this, DashboardAdminActivity.class);
        }
        startActivity(intent);
        finish();
    }
}