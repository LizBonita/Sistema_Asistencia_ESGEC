package com.example.sistemaasistenciapersonal.ui.maestros;

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
import com.example.sistemaasistenciapersonal.adapter.MaestroAdapter;
import com.example.sistemaasistenciapersonal.model.Maestro;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class GestionMaestrosFragment extends Fragment {

    private RecyclerView rvMaestros;
    private SwipeRefreshLayout swipeRefresh;
    private TextView tvConteo;
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
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        tvConteo = view.findViewById(R.id.tvConteo);

        rvMaestros.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new MaestroAdapter(lista);
        rvMaestros.setAdapter(adapter);

        if (swipeRefresh != null) {
            swipeRefresh.setColorSchemeColors(0xFF163B73, 0xFF006847);
            swipeRefresh.setOnRefreshListener(this::cargarMaestros);
        }

        cargarMaestros();
    }

    private void cargarMaestros() {
        if (swipeRefresh != null) swipeRefresh.setRefreshing(true);

        ApiService.getClient().create(ApiInterface.class)
                .getMaestros()
                .enqueue(new Callback<List<Maestro>>() {
                    @Override
                    public void onResponse(@NonNull Call<List<Maestro>> call, @NonNull Response<List<Maestro>> response) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (response.isSuccessful() && response.body() != null) {
                            lista.clear();
                            lista.addAll(response.body());
                            adapter.notifyDataSetChanged();
                            if (tvConteo != null) tvConteo.setText(lista.size() + " docentes en la planta académica");
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<List<Maestro>> call, @NonNull Throwable t) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (tvConteo != null) tvConteo.setText("Sin conexión al servidor");
                        Toast.makeText(getContext(), "Error de conexión", Toast.LENGTH_SHORT).show();
                    }
                });
    }
}