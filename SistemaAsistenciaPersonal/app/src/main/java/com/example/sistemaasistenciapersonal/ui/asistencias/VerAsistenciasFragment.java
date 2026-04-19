package com.example.sistemaasistenciapersonal.ui.asistencias;

import android.os.Bundle;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;
import androidx.recyclerview.widget.LinearLayoutManager;
import androidx.recyclerview.widget.RecyclerView;
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
        rvAsistencias.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new AsistenciaAdapter(lista);
        rvAsistencias.setAdapter(adapter);

        cargarAsistenciasHoy();
    }

    private void cargarAsistenciasHoy() {
        ApiService.getClient().create(ApiInterface.class)
                .getAsistenciasDiarias()
                .enqueue(new Callback<List<Asistencia>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Asistencia>> call, @NonNull Response<List<Asistencia>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Asistencia>> call, @NonNull Throwable t) {
                    }
                });
    }
}