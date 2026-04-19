package com.example.sistemaasistenciapersonal.ui.grupos;

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
import com.example.sistemaasistenciapersonal.adapter.GrupoAdapter;
import com.example.sistemaasistenciapersonal.model.Grupo;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionGruposFragment extends Fragment {

    private RecyclerView rvGrupos;
    private GrupoAdapter adapter;
    private List<Grupo> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_gestion_grupos, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvGrupos = view.findViewById(R.id.rvGrupos);
        rvGrupos.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new GrupoAdapter(lista);
        rvGrupos.setAdapter(adapter);

        cargarGrupos();
    }

    private void cargarGrupos() {
        ApiService.getClient().create(ApiInterface.class)
                .getGrupos()
                .enqueue(new Callback<List<Grupo>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Grupo>> call, @NonNull Response<List<Grupo>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Grupo>> call, @NonNull Throwable t) {
                    }
                });
    }
}