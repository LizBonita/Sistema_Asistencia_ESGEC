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
        holder.tvNombre.setText(j.nombre_maestro);
        holder.tvMotivo.setText(j.motivo);
        holder.tvFecha.setText("Solicitud: " + j.fecha_solicitud);
        holder.tvEstado.setText("Estado: " + j.estado);
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