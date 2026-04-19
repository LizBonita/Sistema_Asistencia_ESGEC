package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Maestro;
import java.util.List;

public class MaestroAdapter extends RecyclerView.Adapter<MaestroAdapter.ViewHolder> {

    private final List<Maestro> lista;

    public MaestroAdapter(List<Maestro> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_maestro, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Maestro m = lista.get(position);

        String nombre = (m.nombre_completo != null && !m.nombre_completo.isEmpty())
                ? m.nombre_completo : "Maestro #" + m.id;
        holder.tvId.setText(nombre);

        // Inicial en avatar
        if (holder.tvInicial != null && nombre.length() > 0) {
            holder.tvInicial.setText(String.valueOf(nombre.charAt(0)));
        }

        holder.tvUsuarioId.setText("@" + (m.usuario != null ? m.usuario : "ID: " + m.usuario_id));

        String contrato = m.tipo_contrato != null ? m.tipo_contrato.replace("_", " ") : "Sin contrato";
        holder.tvContrato.setText(contrato.substring(0, 1).toUpperCase() + contrato.substring(1));

        // Año de registro
        if (holder.tvFecha != null && m.fecha_registro != null && m.fecha_registro.length() >= 10) {
            holder.tvFecha.setText(m.fecha_registro.substring(0, 10));
        }
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvId, tvUsuarioId, tvContrato, tvFecha, tvInicial;

        ViewHolder(View itemView) {
            super(itemView);
            tvId = itemView.findViewById(R.id.tvId);
            tvUsuarioId = itemView.findViewById(R.id.tvUsuarioId);
            tvContrato = itemView.findViewById(R.id.tvContrato);
            tvFecha = itemView.findViewById(R.id.tvFecha);
            tvInicial = itemView.findViewById(R.id.tvInicial);
        }
    }
}