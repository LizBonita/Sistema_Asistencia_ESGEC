package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageButton;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Horario;
import java.util.List;

public class HorarioDetalleAdapter extends RecyclerView.Adapter<HorarioDetalleAdapter.ViewHolder> {

    private List<Horario> lista;
    private OnHorarioDeleteListener listener;

    public interface OnHorarioDeleteListener {
        void onDelete(Horario horario);
    }

    public HorarioDetalleAdapter(List<Horario> lista, OnHorarioDeleteListener listener) {
        this.lista = lista;
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_horario_detalle, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Horario h = lista.get(position);

        String materia = h.nombre_materia != null ? h.nombre_materia : "Materia #" + h.getMateriaId();
        holder.tvMateria.setText(materia);

        String grupo = h.nombre_grupo != null ? h.nombre_grupo : "Grupo #" + h.getGrupoId();
        String dia = h.getDia();
        if (dia.isEmpty()) dia = "Sin día";
        holder.tvDetalle.setText(grupo + " · " + dia);

        String horaInicio = h.hora_inicio != null ? h.hora_inicio : "--:--";
        String horaFin = h.hora_fin != null ? h.hora_fin : "--:--";
        holder.tvHorario.setText(horaInicio + " – " + horaFin);

        holder.btnEliminar.setOnClickListener(v -> {
            if (listener != null) listener.onDelete(h);
        });
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvMateria, tvDetalle, tvHorario;
        ImageButton btnEliminar;

        ViewHolder(View itemView) {
            super(itemView);
            tvMateria = itemView.findViewById(R.id.tvMateria);
            tvDetalle = itemView.findViewById(R.id.tvDetalle);
            tvHorario = itemView.findViewById(R.id.tvHorario);
            btnEliminar = itemView.findViewById(R.id.btnEliminar);
        }
    }
}
