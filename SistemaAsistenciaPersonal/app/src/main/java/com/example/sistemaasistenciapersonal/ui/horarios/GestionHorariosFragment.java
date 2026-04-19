package com.example.sistemaasistenciapersonal.ui.horarios;

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
import com.example.sistemaasistenciapersonal.adapter.HorarioAdapter;
import com.example.sistemaasistenciapersonal.model.Horario;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionHorariosFragment extends Fragment {

    private RecyclerView rvHorarios;
    private SwipeRefreshLayout swipeRefresh;
    private TextView tvConteo;
    private HorarioAdapter adapter;
    private List<Horario> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_horarios, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvHorarios = view.findViewById(R.id.rvHorarios);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        tvConteo = view.findViewById(R.id.tvConteo);

        rvHorarios.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new HorarioAdapter(lista);
        rvHorarios.setAdapter(adapter);

        if (swipeRefresh != null) {
            swipeRefresh.setColorSchemeColors(0xFF163B73, 0xFF006847);
            swipeRefresh.setOnRefreshListener(this::cargarHorarios);
        }

        cargarHorarios();
    }

    private void cargarHorarios() {
        if (swipeRefresh != null) swipeRefresh.setRefreshing(true);

        ApiService.getClient().create(ApiInterface.class)
                .getHorarios()
                .enqueue(new Callback<List<Horario>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Horario>> call, @NonNull Response<List<Horario>> response) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                            if (tvConteo != null) tvConteo.setText(lista.size() + " horarios configurados");
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Horario>> call, @NonNull Throwable t) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (tvConteo != null) tvConteo.setText("Sin conexión al servidor");
                        Toast.makeText(getContext(), "Error de conexión", Toast.LENGTH_SHORT).show();
                    }
                });
    }
}