package com.example.sistemaasistenciapersonal.ui.maestros;

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
import com.example.sistemaasistenciapersonal.adapter.MaestroAdapter;
import com.example.sistemaasistenciapersonal.model.Maestro;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionMaestrosFragment extends Fragment {

    private RecyclerView rvMaestros;
    private MaestroAdapter adapter;
    private List<Maestro> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_maestros, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvMaestros = view.findViewById(R.id.rvMaestros);
        rvMaestros.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new MaestroAdapter(lista);
        rvMaestros.setAdapter(adapter);

        cargarMaestros();
    }

    private void cargarMaestros() {
        ApiService.getClient().create(ApiInterface.class)
                .getMaestros()
                .enqueue(new Callback<List<Maestro>>() {  // ✅ CORREGIDO
                    @Override
                    public void onResponse(@NonNull Call<List<Maestro>> call, @NonNull Response<List<Maestro>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Maestro>> call, @NonNull Throwable t) {
                        // Log.e("Maestros", "Error: " + t.getMessage());
                    }
                });
    }
}