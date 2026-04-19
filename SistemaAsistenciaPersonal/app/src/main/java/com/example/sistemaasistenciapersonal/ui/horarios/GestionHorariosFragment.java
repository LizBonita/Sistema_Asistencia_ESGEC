package com.example.sistemaasistenciapersonal.ui.horarios;

import android.app.TimePickerDialog;
import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.TextView;
import android.widget.Toast;
import android.widget.ProgressBar;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.appcompat.app.AlertDialog;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import com.example.sistemaasistenciapersonal.ApiInterface;
import com.example.sistemaasistenciapersonal.ApiService;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.adapter.HorarioDetalleAdapter;
import com.example.sistemaasistenciapersonal.adapter.MaestroHorarioAdapter;
import com.example.sistemaasistenciapersonal.model.*;
import com.google.android.material.bottomsheet.BottomSheetDialog;
import com.google.android.material.floatingactionbutton.ExtendedFloatingActionButton;
import com.google.android.material.textfield.TextInputEditText;
import java.util.*;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionHorariosFragment extends Fragment implements MaestroHorarioAdapter.OnMaestroHorarioListener {

    private RecyclerView rvMaestros;
    private SwipeRefreshLayout swipeRefresh;
    private TextView tvStatHorarios, tvStatConHorario, tvStatTotalMaestros;
    private ExtendedFloatingActionButton fabAgregar;
    private MaestroHorarioAdapter adapter;

    private List<Maestro> listaMaestros = new ArrayList<>();
    private List<Horario> todosHorarios = new ArrayList<>();
    private List<Materia> listaMaterias = new ArrayList<>();
    private List<Grupo> listaGrupos = new ArrayList<>();
    private Map<Integer, Integer> conteoPorMaestro = new HashMap<>();

    private ApiInterface api;

    @Nullable @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_horarios, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        api = ApiService.getClient().create(ApiInterface.class);

        rvMaestros = view.findViewById(R.id.rvMaestros);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        tvStatHorarios = view.findViewById(R.id.tvStatHorarios);
        tvStatConHorario = view.findViewById(R.id.tvStatConHorario);
        tvStatTotalMaestros = view.findViewById(R.id.tvStatTotalMaestros);
        fabAgregar = view.findViewById(R.id.fabAgregar);

        rvMaestros.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new MaestroHorarioAdapter(listaMaestros, conteoPorMaestro, this);
        rvMaestros.setAdapter(adapter);

        if (swipeRefresh != null) {
            swipeRefresh.setColorSchemeColors(0xFF163B73, 0xFF006847);
            swipeRefresh.setOnRefreshListener(this::cargarDatos);
        }

        fabAgregar.setOnClickListener(v -> mostrarDialogoAgregar(null));

        cargarDatos();
    }

    private void cargarDatos() {
        if (swipeRefresh != null) swipeRefresh.setRefreshing(true);

        // Cargar maestros
        api.getMaestros().enqueue(new Callback<List<Maestro>>() {
            @Override
            public void onResponse(@NonNull Call<List<Maestro>> call, @NonNull Response<List<Maestro>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    listaMaestros.clear();
                    listaMaestros.addAll(response.body());
                    if (tvStatTotalMaestros != null) tvStatTotalMaestros.setText(String.valueOf(listaMaestros.size()));
                    cargarHorarios();
                } else {
                    if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                }
            }
            @Override
            public void onFailure(@NonNull Call<List<Maestro>> call, @NonNull Throwable t) {
                if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                Toast.makeText(getContext(), "Error al cargar maestros", Toast.LENGTH_SHORT).show();
            }
        });

        // Cargar materias y grupos en paralelo para el formulario
        api.getMaterias().enqueue(new Callback<List<Materia>>() {
            @Override
            public void onResponse(@NonNull Call<List<Materia>> call, @NonNull Response<List<Materia>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    listaMaterias.clear();
                    listaMaterias.addAll(response.body());
                }
            }
            @Override public void onFailure(@NonNull Call<List<Materia>> call, @NonNull Throwable t) {}
        });

        api.getGrupos().enqueue(new Callback<List<Grupo>>() {
            @Override
            public void onResponse(@NonNull Call<List<Grupo>> call, @NonNull Response<List<Grupo>> response) {
                if (response.isSuccessful() && response.body() != null) {
                    listaGrupos.clear();
                    listaGrupos.addAll(response.body());
                }
            }
            @Override public void onFailure(@NonNull Call<List<Grupo>> call, @NonNull Throwable t) {}
        });
    }

    private void cargarHorarios() {
        api.getHorarios().enqueue(new Callback<List<Horario>>() {
            @Override
            public void onResponse(@NonNull Call<List<Horario>> call, @NonNull Response<List<Horario>> response) {
                if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                if (response.isSuccessful() && response.body() != null) {
                    todosHorarios.clear();
                    todosHorarios.addAll(response.body());
                    actualizarStats();
                }
            }
            @Override
            public void onFailure(@NonNull Call<List<Horario>> call, @NonNull Throwable t) {
                if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
            }
        });
    }

    private void actualizarStats() {
        conteoPorMaestro.clear();
        for (Horario h : todosHorarios) {
            int mid = h.getMaestroId();
            conteoPorMaestro.put(mid, conteoPorMaestro.containsKey(mid) ? conteoPorMaestro.get(mid) + 1 : 1);
        }

        if (tvStatHorarios != null) tvStatHorarios.setText(String.valueOf(todosHorarios.size()));
        if (tvStatConHorario != null) tvStatConHorario.setText(String.valueOf(conteoPorMaestro.size()));

        adapter.actualizarConteo(conteoPorMaestro);
    }

    // =============================================
    // Ver horarios de un maestro (Bottom Sheet)
    // =============================================
    @Override
    public void onVerHorarios(Maestro maestro) {
        BottomSheetDialog bottomSheet = new BottomSheetDialog(requireContext());
        View sheetView = LayoutInflater.from(getContext()).inflate(R.layout.bottom_sheet_horarios_maestro, null);
        bottomSheet.setContentView(sheetView);

        TextView tvTitulo = sheetView.findViewById(R.id.tvTituloMaestro);
        RecyclerView rvHorarios = sheetView.findViewById(R.id.rvHorariosMaestro);
        TextView tvVacio = sheetView.findViewById(R.id.tvVacio);
        ProgressBar progressBar = sheetView.findViewById(R.id.progressBar);
        View btnCerrar = sheetView.findViewById(R.id.btnCerrar);

        String nombre = maestro.nombre_completo != null ? maestro.nombre_completo : "Maestro #" + maestro.id;
        tvTitulo.setText(nombre);
        btnCerrar.setOnClickListener(v -> bottomSheet.dismiss());

        rvHorarios.setLayoutManager(new LinearLayoutManager(getContext()));
        progressBar.setVisibility(View.VISIBLE);

        // Filtrar horarios locales para este maestro
        List<Horario> horariosMaestro = new ArrayList<>();
        for (Horario h : todosHorarios) {
            if (h.getMaestroId() == maestro.id) {
                horariosMaestro.add(h);
            }
        }

        progressBar.setVisibility(View.GONE);
        if (horariosMaestro.isEmpty()) {
            tvVacio.setVisibility(View.VISIBLE);
        } else {
            tvVacio.setVisibility(View.GONE);
            HorarioDetalleAdapter detalleAdapter = new HorarioDetalleAdapter(horariosMaestro, horario -> {
                // Confirmar eliminación
                new AlertDialog.Builder(requireContext())
                        .setTitle("Eliminar horario")
                        .setMessage("¿Eliminar " + (horario.nombre_materia != null ? horario.nombre_materia : "este horario") + "?")
                        .setPositiveButton("Eliminar", (d, w) -> eliminarHorario(horario, bottomSheet))
                        .setNegativeButton("Cancelar", null)
                        .show();
            });
            rvHorarios.setAdapter(detalleAdapter);
        }

        bottomSheet.show();
    }

    // =============================================
    // Agregar horario (Diálogo)
    // =============================================
    @Override
    public void onAgregarHorario(Maestro maestro) {
        mostrarDialogoAgregar(maestro);
    }

    private void mostrarDialogoAgregar(@Nullable Maestro maestroPreseleccionado) {
        AlertDialog.Builder builder = new AlertDialog.Builder(requireContext(), android.R.style.Theme_Material_Light_NoActionBar_Fullscreen);
        View dialogView = LayoutInflater.from(getContext()).inflate(R.layout.dialog_agregar_horario, null);

        AlertDialog dialog = new AlertDialog.Builder(requireContext())
                .setView(dialogView)
                .setCancelable(true)
                .create();

        // Spinners
        Spinner spinnerMaestro = dialogView.findViewById(R.id.spinnerMaestro);
        Spinner spinnerMateria = dialogView.findViewById(R.id.spinnerMateria);
        Spinner spinnerGrupo = dialogView.findViewById(R.id.spinnerGrupo);
        Spinner spinnerDia = dialogView.findViewById(R.id.spinnerDia);
        TextInputEditText etHoraInicio = dialogView.findViewById(R.id.etHoraInicio);
        TextInputEditText etHoraFin = dialogView.findViewById(R.id.etHoraFin);
        TextInputEditText etTolerancia = dialogView.findViewById(R.id.etTolerancia);
        TextInputEditText etLimiteRetardo = dialogView.findViewById(R.id.etLimiteRetardo);
        View btnCancelar = dialogView.findViewById(R.id.btnCancelar);
        View btnGuardar = dialogView.findViewById(R.id.btnGuardar);

        // === Poblar spinners ===
        // Maestros
        List<String> nombresMaestros = new ArrayList<>();
        for (Maestro m : listaMaestros) {
            nombresMaestros.add(m.nombre_completo != null ? m.nombre_completo : "Maestro #" + m.id);
        }
        spinnerMaestro.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_spinner_dropdown_item, nombresMaestros));

        // Preseleccionar maestro
        if (maestroPreseleccionado != null) {
            for (int i = 0; i < listaMaestros.size(); i++) {
                if (listaMaestros.get(i).id == maestroPreseleccionado.id) {
                    spinnerMaestro.setSelection(i);
                    break;
                }
            }
        }

        // Materias
        List<String> nombresMaterias = new ArrayList<>();
        for (Materia m : listaMaterias) {
            nombresMaterias.add(m.nombre != null ? m.nombre : "Materia #" + m.id);
        }
        spinnerMateria.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_spinner_dropdown_item, nombresMaterias));

        // Grupos
        List<String> nombresGrupos = new ArrayList<>();
        for (Grupo g : listaGrupos) {
            nombresGrupos.add(g.nombre != null ? g.nombre : "Grupo #" + g.id);
        }
        spinnerGrupo.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_spinner_dropdown_item, nombresGrupos));

        // Días de la semana
        String[] dias = {"Lunes", "Martes", "Miércoles", "Jueves", "Viernes"};
        spinnerDia.setAdapter(new ArrayAdapter<>(requireContext(), android.R.layout.simple_spinner_dropdown_item, dias));

        // Time pickers
        etHoraInicio.setOnClickListener(v -> {
            new TimePickerDialog(getContext(), (tp, h, m) ->
                    etHoraInicio.setText(String.format("%02d:%02d", h, m)), 7, 0, true).show();
        });
        etHoraFin.setOnClickListener(v -> {
            new TimePickerDialog(getContext(), (tp, h, m) ->
                    etHoraFin.setText(String.format("%02d:%02d", h, m)), 8, 0, true).show();
        });

        // Botones
        btnCancelar.setOnClickListener(v -> dialog.dismiss());
        btnGuardar.setOnClickListener(v -> {
            int idxMaestro = spinnerMaestro.getSelectedItemPosition();
            int idxMateria = spinnerMateria.getSelectedItemPosition();
            int idxGrupo = spinnerGrupo.getSelectedItemPosition();
            int idxDia = spinnerDia.getSelectedItemPosition();

            if (idxMaestro < 0 || idxMateria < 0 || idxGrupo < 0) {
                Toast.makeText(getContext(), "Selecciona todos los campos", Toast.LENGTH_SHORT).show();
                return;
            }

            String horaInicio = etHoraInicio.getText().toString().trim();
            String horaFin = etHoraFin.getText().toString().trim();
            if (horaInicio.isEmpty() || horaFin.isEmpty()) {
                Toast.makeText(getContext(), "Selecciona las horas", Toast.LENGTH_SHORT).show();
                return;
            }

            // Construir el objeto horario
            Horario nuevoHorario = new Horario();
            nuevoHorario.maestro_id = listaMaestros.get(idxMaestro).id;
            nuevoHorario.materia_id = listaMaterias.get(idxMateria).id;
            nuevoHorario.grupo_id = listaGrupos.get(idxGrupo).id;
            nuevoHorario.dia_semana = dias[idxDia];
            nuevoHorario.hora_inicio = horaInicio;
            nuevoHorario.hora_fin = horaFin;

            String tolStr = etTolerancia.getText().toString().trim();
            String limStr = etLimiteRetardo.getText().toString().trim();
            nuevoHorario.tolerancia_entrada = tolStr.isEmpty() ? 10 : Integer.parseInt(tolStr);
            nuevoHorario.limite_retardo = limStr.isEmpty() ? 15 : Integer.parseInt(limStr);

            guardarHorario(nuevoHorario, dialog);
        });

        dialog.show();
    }

    private void guardarHorario(Horario horario, AlertDialog dialog) {
        api.agregarHorario(horario).enqueue(new Callback<ResponseGenerico>() {
            @Override
            public void onResponse(@NonNull Call<ResponseGenerico> call, @NonNull Response<ResponseGenerico> response) {
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    Toast.makeText(getContext(), "✓ Horario agregado", Toast.LENGTH_SHORT).show();
                    dialog.dismiss();
                    cargarDatos(); // Refrescar
                } else {
                    String msg = response.body() != null ? response.body().message : "Error desconocido";
                    Toast.makeText(getContext(), "Error: " + msg, Toast.LENGTH_LONG).show();
                }
            }
            @Override
            public void onFailure(@NonNull Call<ResponseGenerico> call, @NonNull Throwable t) {
                Toast.makeText(getContext(), "Error de conexión: " + t.getMessage(), Toast.LENGTH_LONG).show();
            }
        });
    }

    private void eliminarHorario(Horario horario, BottomSheetDialog bottomSheet) {
        api.eliminarHorario(horario.id).enqueue(new Callback<ResponseGenerico>() {
            @Override
            public void onResponse(@NonNull Call<ResponseGenerico> call, @NonNull Response<ResponseGenerico> response) {
                if (response.isSuccessful() && response.body() != null && response.body().success) {
                    Toast.makeText(getContext(), "Horario eliminado", Toast.LENGTH_SHORT).show();
                    bottomSheet.dismiss();
                    cargarDatos(); // Refrescar
                } else {
                    Toast.makeText(getContext(), "Error al eliminar", Toast.LENGTH_SHORT).show();
                }
            }
            @Override
            public void onFailure(@NonNull Call<ResponseGenerico> call, @NonNull Throwable t) {
                Toast.makeText(getContext(), "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        });
    }
}