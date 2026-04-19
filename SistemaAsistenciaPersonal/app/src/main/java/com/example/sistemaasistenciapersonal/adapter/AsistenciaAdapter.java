package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Asistencia;
import java.util.List;

public class AsistenciaAdapter extends RecyclerView.Adapter<AsistenciaAdapter.ViewHolder> {

    private final List<Asistencia> lista;

    public AsistenciaAdapter(List<Asistencia> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_asistencia, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Asistencia a = lista.get(position);

        // Mostrar nombre del maestro en vez de ID
        String nombre = (a.nombre_maestro != null && !a.nombre_maestro.isEmpty())
                ? a.nombre_maestro : "Maestro #" + a.maestro_id;
        holder.tvMaestroId.setText(nombre);
        holder.tvFecha.setText(a.fecha != null ? a.fecha : "");

        // Entrada
        String entrada = a.hora_entrada != null ? a.hora_entrada : "—";
        String estadoE = a.estado_entrada != null ? " (" + a.estado_entrada + ")" : "";
        holder.tvEntrada.setText("Entrada: " + entrada + estadoE);

        // Salida
        String salida = a.hora_salida != null ? a.hora_salida : "Sin registro";
        String estadoS = a.estado_salida != null ? " (" + a.estado_salida + ")" : "";
        holder.tvSalida.setText("Salida: " + salida + estadoS);

        // Retraso — solo mostrar si hay
        if (a.minutos_retraso > 0) {
            holder.tvRetraso.setText("⚠ Retraso: " + a.minutos_retraso + " min");
            holder.tvRetraso.setVisibility(View.VISIBLE);
        } else {
            holder.tvRetraso.setText("✓ Puntual");
            holder.tvRetraso.setTextColor(0xFF006847);
            holder.tvRetraso.setVisibility(View.VISIBLE);
        }
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvMaestroId, tvFecha, tvEntrada, tvSalida, tvRetraso;

        ViewHolder(View itemView) {
            super(itemView);
            tvMaestroId = itemView.findViewById(R.id.tvMaestroId);
            tvFecha = itemView.findViewById(R.id.tvFecha);
            tvEntrada = itemView.findViewById(R.id.tvEntrada);
            tvSalida = itemView.findViewById(R.id.tvSalida);
            tvRetraso = itemView.findViewById(R.id.tvRetraso);
        }
    }
}