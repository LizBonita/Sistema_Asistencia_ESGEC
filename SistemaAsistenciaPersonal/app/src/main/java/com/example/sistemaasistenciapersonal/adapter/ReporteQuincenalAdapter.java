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

    private final List<ReporteQuincenal> lista;

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
        holder.tvNombreMaestro.setText(r.nombre_maestro != null ? r.nombre_maestro : "—");
        holder.tvContrato.setText(r.tipo_contrato != null ? r.tipo_contrato : "");
        holder.tvRetardos.setText(String.valueOf(r.retardos));
        holder.tvMinutos.setText(String.valueOf(r.minutos_tardanza));
        holder.tvAusencias.setText(String.valueOf(r.ausencias));
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvNombreMaestro, tvContrato, tvRetardos, tvMinutos, tvAusencias;

        ViewHolder(View itemView) {
            super(itemView);
            tvNombreMaestro = itemView.findViewById(R.id.tvNombreMaestro);
            tvContrato = itemView.findViewById(R.id.tvContrato);
            tvRetardos = itemView.findViewById(R.id.tvRetardos);
            tvMinutos = itemView.findViewById(R.id.tvMinutos);
            tvAusencias = itemView.findViewById(R.id.tvAusencias);
        }
    }
}