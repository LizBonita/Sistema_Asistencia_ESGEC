package com.example.sistemaasistenciapersonal.ui.usuarios;

import android.app.AlertDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.EditText;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;

import com.example.sistemaasistenciapersonal.ApiInterface;
import com.example.sistemaasistenciapersonal.ApiService;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.adapter.UsuarioAdapter;
import com.example.sistemaasistenciapersonal.model.ResponseUsuario;
import com.example.sistemaasistenciapersonal.model.Usuario;
import com.google.android.material.floatingactionbutton.FloatingActionButton;

import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

import java.util.ArrayList;
import java.util.List;

public class GestionUsuariosFragment extends Fragment implements UsuarioAdapter.OnUserClickListener {

    private RecyclerView rvUsuarios;
    private UsuarioAdapter adapter;
    private List<Usuario> lista = new ArrayList<>();
    private List<Usuario> listaOriginal = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_usuarios, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        // 1. Configurar RecyclerView
        rvUsuarios = view.findViewById(R.id.rvUsuarios);
        if (rvUsuarios != null) {
            rvUsuarios.setLayoutManager(new LinearLayoutManager(getContext()));
            adapter = new UsuarioAdapter(lista, this);
            rvUsuarios.setAdapter(adapter);
        }

        // 2. Configurar FAB Agregar Nuevo ✅ CORREGIDO
        FloatingActionButton fabAgregar = view.findViewById(R.id.fabAgregar);
        if (fabAgregar != null) {
            fabAgregar.setOnClickListener(v -> abrirDialogUsuario(null));
        } else {
            mostrarMensaje("❌ ID fabAgregar no encontrado en XML");
        }

        // 3. Configurar Buscador en tiempo real ✅ CORREGIDO (TextWatcher no es functional interface)
        EditText etSearch = view.findViewById(R.id.etBusqueda);
        if (etSearch != null) {
            etSearch.addTextChangedListener(new android.text.TextWatcher() {
                @Override
                public void beforeTextChanged(CharSequence s, int start, int count, int after) {}

                @Override
                public void onTextChanged(CharSequence s, int start, int before, int count) {
                    buscarEnFiltro(s.toString());
                }

                @Override
                public void afterTextChanged(android.text.Editable s) {}
            });
        }

        // 4. Cargar datos iniciales al iniciar
        cargarUsuarios("");
    }

    /**
     * 🔽 LEER USUARIOS DEL BACKEND
     */
    private void cargarUsuarios(String searchText) {
        ApiService.getClient().create(ApiInterface.class)
                .getUsuarios()
                .enqueue(new Callback<List<Usuario>>() {
                    @Override
                    public void onResponse(Call<List<Usuario>> call, Response<List<Usuario>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            listaOriginal.clear();
                            listaOriginal.addAll(lista);
                            adapter.setListaCompleta(lista);
                            mostrarMensaje("✅ " + lista.size() + " usuarios cargados");
                        } else {
                            mostrarMensaje("❌ Error Server: " + response.code());
                            System.out.println("❌ ERROR GET: " + response.message());
                        }
                    }

                    @Override
                    public void onFailure(Call<List<Usuario>> call, Throwable t) {
                        System.out.println("❌ DETALLE ERROR RETROFIT (GET): " + t.getMessage());
                        t.printStackTrace();
                        mostrarMensaje("❌ Sin conexión: Verifica servidor XAMPP");
                    }
                });
    }

    /**
     * 🔍 BUSCAR EN FILTRO LOCAL (Sin esperar al servidor)
     */
    private void buscarEnFiltro(String query) {
        String filtro = query.toLowerCase();
        lista.clear();

        if (filtro.isEmpty()) {
            lista.addAll(listaOriginal);
        } else {
            for (Usuario u : listaOriginal) {
                if (u.nombre_completo.toLowerCase().contains(filtro) ||
                        u.usuario.toLowerCase().contains(filtro) ||
                        Integer.toString(u.rol_id).contains(filtro)) {
                    lista.add(u);
                }
            }
        }
        adapter.setListaCompleta(lista);
    }

    /**
     * ➕ ABRIR DIALOG PARA AGREGAR/EDITAR
     */
    private void abrirDialogUsuario(Usuario u) {
        AlertDialog.Builder builder = new AlertDialog.Builder(requireContext());
        View view = LayoutInflater.from(requireContext()).inflate(R.layout.dialog_agregar_editar_usuario, null);

        TextView tvTitle = view.findViewById(R.id.tvTitleDialog);
        EditText etNombre = view.findViewById(R.id.etNombre);
        EditText etUsuario = view.findViewById(R.id.etUsuario);
        EditText etPassword = view.findViewById(R.id.etPassword);
        EditText etRolId = view.findViewById(R.id.etRolId);
        Button btnCerrar = view.findViewById(R.id.btnCancelar);
        Button btnGuardar = view.findViewById(R.id.btnGuardar);

        if (u != null) {
            tvTitle.setText("✏️ Editar Usuario #" + u.id);
            etNombre.setText(u.nombre_completo);
            etUsuario.setText(u.usuario);
            etRolId.setText(String.valueOf(u.rol_id));
            etPassword.setText("");
        } else {
            tvTitle.setText("➕ Nuevo Usuario");
        }

        builder.setView(view);
        AlertDialog dialogEditar = builder.create();
        dialogEditar.show();

        btnCerrar.setOnClickListener(v -> dialogEditar.dismiss());

        btnGuardar.setOnClickListener(v -> {
            String nombre = etNombre.getText().toString().trim();
            String usuario = etUsuario.getText().toString().trim();
            String password = etPassword.getText().toString().trim();

            int rolId;
            try {
                rolId = Integer.parseInt(etRolId.getText().toString().trim());
            } catch (NumberFormatException e) {
                Toast.makeText(getActivity(), "El Rol debe ser numérico", Toast.LENGTH_SHORT).show();
                return;
            }

            if (nombre.isEmpty() || usuario.isEmpty()) {
                Toast.makeText(getActivity(), "Complete Nombre y Usuario", Toast.LENGTH_SHORT).show();
                return;
            }

            guardardato(u, nombre, usuario, password, rolId);
            dialogEditar.dismiss();
        });
    }

    /**
     * 💾 GUARDAR DATOS (AGREGAR O EDITAR) - CON VALIDACIÓN EXTRA
     */
    private void guardardato(Usuario u, String nombre, String usuario, String pass, int rolId) {
        // VALIDACIÓN PRE-ENVÍO
        if (nombre == null || nombre.trim().isEmpty()) {
            mostrarMensaje("❌ Nombre inválido");
            return;
        }
        if (usuario == null || usuario.trim().isEmpty()) {
            mostrarMensaje("❌ Usuario inválido");
            return;
        }
        if (pass == null || pass.trim().isEmpty()) {
            mostrarMensaje("❌ Contraseña obligatoria");
            return;
        }
        if (rolId <= 0) {
            mostrarMensaje("❌ El Rol ID debe ser mayor a 0");
            return;
        }

        Usuario newUser = new Usuario(0, nombre.trim(), usuario.trim(), pass.trim(), rolId, "");

        if (u == null) {
            // 📥 AGREGAR
            ApiService.getClient().create(ApiInterface.class)
                    .agregarUsuario(newUser)
                    .enqueue(new Callback<ResponseUsuario>() {
                        @Override
                        public void onResponse(Call<ResponseUsuario> call, Response<ResponseUsuario> response) {
                            if (response.isSuccessful() && response.body() != null) {
                                ResponseUsuario result = response.body();
                                if ("success".equals(result.status)) {
                                    mostrarMensaje("✅ Usuario Agregado Correctamente");
                                    cargarUsuarios("");
                                } else {
                                    mostrarMensaje("❌ " + result.message);
                                }
                            } else {
                                try {
                                    String errorBody = response.errorBody() != null ? response.errorBody().string() : "";
                                    System.out.println("🔴 ERROR SERVER: " + errorBody);
                                } catch (Exception e) {
                                    System.out.println("🔴 ERROR PARSE: " + e.getMessage());
                                }
                                mostrarMensaje("❌ Error Server: " + response.code() + " Bad Request");
                            }
                        }

                        @Override
                        public void onFailure(Call<ResponseUsuario> call, Throwable t) {
                            System.out.println("❌ ERROR DE RED (ADD): " + t.getMessage());
                            t.printStackTrace();
                            mostrarMensaje("❌ Fallo de Red: " + t.getMessage());
                        }
                    });
        } else {
            // 📝 EDITAR
            u.nombre_completo = nombre.trim();
            u.usuario = usuario.trim();
            u.password_hash = pass.trim();
            u.rol_id = rolId;

            ApiService.getClient().create(ApiInterface.class)
                    .actualizarUsuario(u)
                    .enqueue(new Callback<ResponseUsuario>() {
                        @Override
                        public void onResponse(Call<ResponseUsuario> call, Response<ResponseUsuario> response) {
                            if (response.isSuccessful() && response.body() != null) {
                                ResponseUsuario result = response.body();
                                if ("success".equals(result.status)) {
                                    mostrarMensaje("✅ Usuario Actualizado Correctamente");
                                    cargarUsuarios("");
                                } else {
                                    mostrarMensaje("❌ " + result.message);
                                }
                            } else {
                                mostrarMensaje("❌ Error Update: " + response.code());
                            }
                        }

                        @Override
                        public void onFailure(Call<ResponseUsuario> call, Throwable t) {
                            System.out.println("❌ ERROR DE RED (EDIT): " + t.getMessage());
                            t.printStackTrace();
                            mostrarMensaje("❌ Fallo de Red: " + t.getMessage());
                        }
                    });
        }
    }

    /**
     * 🗑️ ELIMINAR USUARIO
     */
    private void confirmarEliminar(Usuario u) {
        new AlertDialog.Builder(requireContext())
                .setTitle("¿Eliminar Usuario?")
                .setMessage("¿Seguro que deseas eliminar a " + u.nombre_completo + "?\n\nEsta acción no se puede deshacer.")
                .setPositiveButton("Sí", (dialog, which) -> {
                    ApiService.getClient().create(ApiInterface.class)
                            .eliminarUsuario(u.id)
                            .enqueue(new Callback<ResponseUsuario>() {
                                @Override
                                public void onResponse(Call<ResponseUsuario> call, Response<ResponseUsuario> response) {
                                    if (response.isSuccessful() && response.body() != null) {
                                        ResponseUsuario result = response.body();
                                        if ("success".equals(result.status)) {
                                            mostrarMensaje("✅ Usuario Eliminado Exitosamente");
                                            cargarUsuarios("");
                                        } else {
                                            mostrarMensaje("❌ " + result.message);
                                        }
                                    } else {
                                        mostrarMensaje("❌ No Encontrado: " + response.code());
                                    }
                                }

                                @Override
                                public void onFailure(Call<ResponseUsuario> call, Throwable t) {
                                    System.out.println("❌ ERROR DE RED (DELETE): " + t.getMessage());
                                    t.printStackTrace();
                                    mostrarMensaje("❌ Fallo de Red: " + t.getMessage());
                                }
                            });
                })
                .setNegativeButton("No", null)
                .setIcon(android.R.drawable.ic_dialog_alert)
                .show();
    }

    // Interfaz del Adapter
    @Override
    public void onEditClick(Usuario u) {
        abrirDialogUsuario(u);
    }

    @Override
    public void onDeleteClick(Usuario u) {
        confirmarEliminar(u);
    }

    private void mostrarMensaje(String mensaje) {
        Toast.makeText(requireContext(), mensaje, Toast.LENGTH_SHORT).show();
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
    }
}