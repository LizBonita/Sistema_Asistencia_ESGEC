package com.example.sistemaasistenciapersonal.adapter;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.ReporteQuincenal;
import java.util.List;

public class ReporteQuincenalAdapter extends RecyclerView.Adapter<ReporteQuincenalAdapter.ViewHolder> {

    private List<ReporteQuincenal> lista;

    public ReporteQuincenalAdapter(List<ReporteQuincenal> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_reporte, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        ReporteQuincenal r = lista.get(position);
        holder.tvNombre.setText(r.nombre_maestro);
        holder.tvRetardos.setText("Retardos: " + r.retardos);
        holder.tvMinutos.setText("Minutos: " + r.minutos_tardanza);
        holder.tvAusencias.setText("Ausencias: " + r.ausencias);
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvNombre, tvRetardos, tvMinutos, tvAusencias;

        ViewHolder(View itemView) {
            super(itemView);
            tvNombre = itemView.findViewById(R.id.tvNombre);
            tvRetardos = itemView.findViewById(R.id.tvRetardos);
            tvMinutos = itemView.findViewById(R.id.tvMinutos);
            tvAusencias = itemView.findViewById(R.id.tvAusencias);
        }
    }
}