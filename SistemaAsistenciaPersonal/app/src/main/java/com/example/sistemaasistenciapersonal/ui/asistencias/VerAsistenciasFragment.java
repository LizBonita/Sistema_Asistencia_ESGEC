package com.example.sistemaasistenciapersonal.ui.asistencias;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import android.widget.Toast;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
import androidx.swiperefreshlayout.widget.SwipeRefreshLayout;
import com.example.sistemaasistenciapersonal.ApiInterface;
import com.example.sistemaasistenciapersonal.ApiService;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.adapter.AsistenciaAdapter;
import com.example.sistemaasistenciapersonal.model.Asistencia;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class VerAsistenciasFragment extends Fragment {

    private RecyclerView rvAsistencias;
    private SwipeRefreshLayout swipeRefresh;
    private TextView tvConteo;
    private AsistenciaAdapter adapter;
    private List<Asistencia> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_ver_asistencias, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvAsistencias = view.findViewById(R.id.rvAsistencias);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        tvConteo = view.findViewById(R.id.tvConteo);

        rvAsistencias.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new AsistenciaAdapter(lista);
        rvAsistencias.setAdapter(adapter);

        if (swipeRefresh != null) {
            swipeRefresh.setColorSchemeColors(0xFF163B73, 0xFF006847);
            swipeRefresh.setOnRefreshListener(this::cargarAsistenciasHoy);
        }

        cargarAsistenciasHoy();
    }

    private void cargarAsistenciasHoy() {
        if (swipeRefresh != null) swipeRefresh.setRefreshing(true);

        ApiService.getClient().create(ApiInterface.class)
                .getAsistenciasDiarias()
                .enqueue(new Callback<List<Asistencia>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Asistencia>> call, @NonNull Response<List<Asistencia>> response) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();

                            // Contar puntuales vs retardos
                            int puntuales = 0, retardos = 0;
                            for (Asistencia a : lista) {
                                if (a.minutos_retraso > 0) retardos++;
                                else puntuales++;
                            }
                            if (tvConteo != null) {
                                tvConteo.setText(lista.size() + " registros hoy · " + puntuales + " puntuales · " + retardos + " retardos");
                            }
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Asistencia>> call, @NonNull Throwable t) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (tvConteo != null) tvConteo.setText("Sin conexión al servidor");
                        Toast.makeText(getContext(), "Error de conexión", Toast.LENGTH_SHORT).show();
                    }
                });
    }
}