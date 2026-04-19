package com.example.sistemaasistenciapersonal.ui.huellas;

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
import com.example.sistemaasistenciapersonal.adapter.HuellaAdapter;
import com.example.sistemaasistenciapersonal.model.HuellaInfo;
import com.example.sistemaasistenciapersonal.model.HuellaListResponse;
import java.util.ArrayList;
import java.util.List;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class HuellasFragment extends Fragment {

    private RecyclerView rvHuellas;
    private SwipeRefreshLayout swipeRefresh;
    private TextView tvConteo;
    private HuellaAdapter adapter;
    private List<HuellaInfo> lista = new ArrayList<>();

    @Nullable
    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        return inflater.inflate(R.layout.fragment_huellas, container, false);
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);

        rvHuellas = view.findViewById(R.id.rvHuellas);
        swipeRefresh = view.findViewById(R.id.swipeRefresh);
        tvConteo = view.findViewById(R.id.tvConteo);

        rvHuellas.setLayoutManager(new LinearLayoutManager(getContext()));
        adapter = new HuellaAdapter(lista);
        rvHuellas.setAdapter(adapter);

        if (swipeRefresh != null) {
            swipeRefresh.setColorSchemeColors(0xFF163B73, 0xFFE65100);
            swipeRefresh.setOnRefreshListener(this::cargarHuellas);
        }

        cargarHuellas();
    }

    private void cargarHuellas() {
        if (swipeRefresh != null) swipeRefresh.setRefreshing(true);

        ApiService.getClient().create(ApiInterface.class)
                .getHuellas()
                .enqueue(new Callback<HuellaListResponse>() {
                    @Override
                    public void onResponse(@NonNull Call<HuellaListResponse> call, @NonNull Response<HuellaListResponse> response) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (response.isSuccessful() && response.body() != null && response.body().success) {
                            lista.clear();
                            lista.addAll(response.body().maestros);
                            adapter.notifyDataSetChanged();

                            // Contar registradas vs pendientes
                            int registradas = 0;
                            for (HuellaInfo h : lista) {
                                if (h.tiene_huella == 1) registradas++;
                            }
                            int pendientes = lista.size() - registradas;

                            if (tvConteo != null) {
                                tvConteo.setText(registradas + " huellas registradas · " + pendientes + " pendientes");
                            }
                        }
                    }

                    @Override
                    public void onFailure(@NonNull Call<HuellaListResponse> call, @NonNull Throwable t) {
                        if (swipeRefresh != null) swipeRefresh.setRefreshing(false);
                        if (tvConteo != null) tvConteo.setText("Sin conexión al servidor");
                        Toast.makeText(getContext(), "Error de conexión", Toast.LENGTH_SHORT).show();
                    }
                });
    }
}
