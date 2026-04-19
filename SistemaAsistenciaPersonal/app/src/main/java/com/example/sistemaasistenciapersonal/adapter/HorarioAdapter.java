package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Horario;
import java.util.List;

public class HorarioAdapter extends RecyclerView.Adapter<HorarioAdapter.ViewHolder> {

    private List<Horario> lista;

    public HorarioAdapter(List<Horario> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_horario, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Horario h = lista.get(position);
        holder.tvDia.setText(h.dia != null ? h.dia : "—");
        holder.tvHora.setText((h.hora_inicio != null ? h.hora_inicio : "") + " - " + (h.hora_fin != null ? h.hora_fin : ""));

        // Mostrar nombres reales en vez de IDs
        String grupo = (h.nombre_grupo != null) ? h.nombre_grupo : "Grupo #" + h.id_grupo;
        String materia = (h.nombre_materia != null) ? h.nombre_materia : "Materia #" + h.id_materia;
        holder.tvGrupo.setText("Grupo: " + grupo);
        holder.tvMateria.setText("Materia: " + materia);
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvDia, tvHora, tvGrupo, tvMateria;

        ViewHolder(View itemView) {
            super(itemView);
            tvDia = itemView.findViewById(R.id.tvDia);
            tvHora = itemView.findViewById(R.id.tvHora);
            tvGrupo = itemView.findViewById(R.id.tvGrupo);
            tvMateria = itemView.findViewById(R.id.tvMateria);
        }
    }
}