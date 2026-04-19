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
import java.util.Map;

public class MaestroHorarioAdapter extends RecyclerView.Adapter<MaestroHorarioAdapter.ViewHolder> {

    private List<Maestro> lista;
    private Map<Integer, Integer> conteoPorMaestro; // maestro_id -> count
    private OnMaestroHorarioListener listener;

    public interface OnMaestroHorarioListener {
        void onVerHorarios(Maestro maestro);
        void onAgregarHorario(Maestro maestro);
    }

    public MaestroHorarioAdapter(List<Maestro> lista, Map<Integer, Integer> conteo, OnMaestroHorarioListener listener) {
        this.lista = lista;
        this.conteoPorMaestro = conteo;
        this.listener = listener;
    }

    public void actualizarConteo(Map<Integer, Integer> nuevoConteo) {
        this.conteoPorMaestro = nuevoConteo;
        notifyDataSetChanged();
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_maestro_horario, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Maestro m = lista.get(position);

        String nombre = (m.nombre_completo != null && !m.nombre_completo.isEmpty())
                ? m.nombre_completo : "Maestro #" + m.id;
        holder.tvNombreMaestro.setText(nombre);

        // Inicial
        if (nombre.length() > 0) {
            // Obtener iniciales (primera y segunda palabra)
            String[] partes = nombre.trim().split("\\s+");
            String iniciales = String.valueOf(partes[0].charAt(0));
            if (partes.length > 1) iniciales += partes[1].charAt(0);
            holder.tvInicial.setText(iniciales);
        }

        // Cantidad de horarios
        int cantidad = conteoPorMaestro.containsKey(m.id) ? conteoPorMaestro.get(m.id) : 0;
        holder.tvCantidadHorarios.setText(cantidad + " horario" + (cantidad != 1 ? "s" : "") + " asignado" + (cantidad != 1 ? "s" : ""));

        holder.tvMaestroId.setText("Maestro #" + m.id);

        // Listeners
        holder.btnVerHorarios.setOnClickListener(v -> {
            if (listener != null) listener.onVerHorarios(m);
        });
        holder.btnAgregar.setOnClickListener(v -> {
            if (listener != null) listener.onAgregarHorario(m);
        });
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvInicial, tvNombreMaestro, tvCantidadHorarios, tvMaestroId;
        View btnVerHorarios, btnAgregar;

        ViewHolder(View itemView) {
            super(itemView);
            tvInicial = itemView.findViewById(R.id.tvInicial);
            tvNombreMaestro = itemView.findViewById(R.id.tvNombreMaestro);
            tvCantidadHorarios = itemView.findViewById(R.id.tvCantidadHorarios);
            tvMaestroId = itemView.findViewById(R.id.tvMaestroId);
            btnVerHorarios = itemView.findViewById(R.id.btnVerHorarios);
            btnAgregar = itemView.findViewById(R.id.btnAgregar);
        }
    }
}
