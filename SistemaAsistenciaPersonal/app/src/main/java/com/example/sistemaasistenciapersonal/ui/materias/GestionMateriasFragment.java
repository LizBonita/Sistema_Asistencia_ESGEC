package com.example.sistemaasistenciapersonal.ui.materias;

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
import com.example.sistemaasistenciapersonal.adapter.MateriaAdapter;
import com.example.sistemaasistenciapersonal.model.Materia;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionMateriasFragment extends Fragment {

    private RecyclerView rvMaterias;
    private MateriaAdapter adapter;
    private List<Materia> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_materias, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvMaterias = view.findViewById(R.id.rvMaterias);
        rvMaterias.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new MateriaAdapter(lista);
        rvMaterias.setAdapter(adapter);

        cargarMaterias();
    }

    private void cargarMaterias() {
        ApiService.getClient().create(ApiInterface.class)
                .getMaterias()
                .enqueue(new Callback<List<Materia>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Materia>> call, @NonNull Response<List<Materia>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Materia>> call, @NonNull Throwable t) {
                    }
                });
    }
}