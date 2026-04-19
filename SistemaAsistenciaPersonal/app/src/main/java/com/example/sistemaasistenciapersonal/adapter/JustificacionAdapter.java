package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Justificacion;
import java.util.List;

public class JustificacionAdapter extends RecyclerView.Adapter<JustificacionAdapter.ViewHolder> {

    private List<Justificacion> lista;

    public JustificacionAdapter(List<Justificacion> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_justificacion, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Justificacion j = lista.get(position);
        holder.tvNombre.setText(j.nombre_maestro != null ? j.nombre_maestro : "Maestro #" + j.id_maestro);
        holder.tvMotivo.setText(j.motivo != null ? j.motivo : "Sin motivo");
        String fechas = (j.fecha_inicio != null ? j.fecha_inicio : "") + " — " + (j.fecha_fin != null ? j.fecha_fin : "");
        holder.tvFecha.setText(fechas);

        // Badge con color según estado
        String estado = j.estado != null ? j.estado : "pendiente";
        holder.tvEstado.setText(estado.substring(0, 1).toUpperCase() + estado.substring(1));
        switch (estado.toLowerCase()) {
            case "aprobado":
                holder.tvEstado.setTextColor(0xFF006847);
                holder.tvEstado.setBackgroundColor(0xFFE8F5E9);
                break;
            case "rechazado":
                holder.tvEstado.setTextColor(0xFFD32F2F);
                holder.tvEstado.setBackgroundColor(0xFFFFEBEE);
                break;
            default: // pendiente
                holder.tvEstado.setTextColor(0xFFE65100);
                holder.tvEstado.setBackgroundColor(0xFFFFF3E0);
                break;
        }
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvNombre, tvMotivo, tvFecha, tvEstado;

        ViewHolder(View itemView) {
            super(itemView);
            tvNombre = itemView.findViewById(R.id.tvNombre);
            tvMotivo = itemView.findViewById(R.id.tvMotivo);
            tvFecha = itemView.findViewById(R.id.tvFecha);
            tvEstado = itemView.findViewById(R.id.tvEstado);
        }
    }
}