package com.example.sistemaasistenciapersonal.ui.justificaciones;

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
import com.example.sistemaasistenciapersonal.adapter.JustificacionAdapter;
import com.example.sistemaasistenciapersonal.model.Justificacion;
import com.example.sistemaasistenciapersonal.utils.SessionManager;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class JustificacionesPendientesFragment extends Fragment {

    private RecyclerView rvJustificaciones;
    private JustificacionAdapter adapter;
    private List<Justificacion> lista = new ArrayList<>();
    private SessionManager session;

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_justificaciones_pendientes, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        session = new SessionManager(getContext());

        rvJustificaciones = view.findViewById(R.id.rvJustificaciones);
        rvJustificaciones.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new JustificacionAdapter(lista);
        rvJustificaciones.setAdapter(adapter);

        cargarJustificaciones();
    }

    private void cargarJustificaciones() {
        int idUsuario = session.getUserId();
        if (idUsuario == -1) return;

        ApiService.getClient().create(ApiInterface.class)
                .getJustificacionesPendientes(idUsuario)
                .enqueue(new Callback<List<Justificacion>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Justificacion>> call, @NonNull Response<List<Justificacion>> response) {
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Justificacion>> call, @NonNull Throwable t) {
                        // Manejar error
                    }
                });
    }
}