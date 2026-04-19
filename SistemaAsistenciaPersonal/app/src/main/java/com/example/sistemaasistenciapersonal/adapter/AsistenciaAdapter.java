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

        // Nombre del maestro
        String nombre = (a.nombre_maestro != null && !a.nombre_maestro.isEmpty())
                ? a.nombre_maestro : "Maestro #" + a.maestro_id;
        holder.tvMaestroId.setText(nombre);
        holder.tvFecha.setText(a.fecha != null ? a.fecha : "");

        // Entrada
        String entrada = a.hora_entrada != null ? a.hora_entrada : "—";
        String estadoE = a.estado_entrada != null ? " (" + a.estado_entrada + ")" : "";
        holder.tvEntrada.setText(entrada + estadoE);

        // Salida
        String salida = a.hora_salida != null ? a.hora_salida : "Sin registro";
        String estadoS = a.estado_salida != null ? " (" + a.estado_salida + ")" : "";
        holder.tvSalida.setText(salida + estadoS);

        // Badge de estado
        if (a.minutos_retraso > 0) {
            holder.tvRetraso.setText("⚠ " + a.minutos_retraso + " min");
            holder.tvRetraso.setTextColor(0xFFD32F2F);
            holder.tvRetraso.setBackgroundColor(0xFFFFEBEE);
        } else {
            holder.tvRetraso.setText("✓ Puntual");
            holder.tvRetraso.setTextColor(0xFF006847);
            holder.tvRetraso.setBackgroundColor(0xFFE8F5E9);
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