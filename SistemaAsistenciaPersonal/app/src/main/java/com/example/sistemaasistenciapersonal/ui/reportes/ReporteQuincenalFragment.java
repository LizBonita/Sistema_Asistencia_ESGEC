package com.example.sistemaasistenciapersonal.ui.reportes;

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
import com.example.sistemaasistenciapersonal.adapter.ReporteQuincenalAdapter;
import com.example.sistemaasistenciapersonal.model.ReporteQuincenal;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class ReporteQuincenalFragment extends Fragment {

    private RecyclerView rvReportes;
    private ReporteQuincenalAdapter adapter;
    private List<ReporteQuincenal> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_reporte_quincenal, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvReportes = view.findViewById(R.id.rvReportes);
        rvReportes.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new ReporteQuincenalAdapter(lista);
        rvReportes.setAdapter(adapter);

        cargarReporteQuincenal();
    }

    private void cargarReporteQuincenal() {
        ApiService.getClient().create(ApiInterface.class)
                .getReporteQuincenal()
                .enqueue(new Callback<List<ReporteQuincenal>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<ReporteQuincenal>> call, @NonNull Response<List<ReporteQuincenal>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<ReporteQuincenal>> call, @NonNull Throwable t) {
                    }
                });
    }
}