// com/example/sistemaasistenciapersonal/adapter/UsuarioAdapter.java

package com.example.sistemaasistenciapersonal.adapter;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.Button;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.Usuario;
import java.util.ArrayList;
import java.util.List;

public class UsuarioAdapter extends RecyclerView.Adapter<UsuarioAdapter.ViewHolder> {

    private List<Usuario> lista;
    private List<Usuario> listaOriginal;
    private OnUserClickListener listener;

    // CONSTRUCTOR CORRECTO
    public UsuarioAdapter(List<Usuario> lista, OnUserClickListener listener) {
        this.lista = lista != null ? new ArrayList<>(lista) : new ArrayList<>();
        this.listener = listener;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_usuario, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Usuario u = lista.get(position);
        holder.tvNombre.setText(u.nombre_completo);
        holder.tvUsuario.setText("@" + u.usuario);
        holder.tvRol.setText("Rol ID: " + u.rol_id);

        // 🗑️ BOTÓN ELIMINAR (CLAVE)
        holder.btnDelete.setOnClickListener(v -> {
            if (listener != null) {
                listener.onDeleteClick(u);
            } else {
                System.out.println("❌ Listener es null");
            }
        });

        // ✏️ ÍTE COMPLETO EDITABLE (si no hay botón editar en UI)
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onEditClick(u);
            }
        });
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    public void setListaCompleta(List<Usuario> nuevaLista) {
        this.lista = nuevaLista;
        this.listaOriginal = new ArrayList<>(nuevaLista);
        notifyDataSetChanged();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvNombre, tvUsuario, tvRol;
        Button btnDelete;

        ViewHolder(View itemView) {
            super(itemView);
            tvNombre = itemView.findViewById(R.id.tvNombre);
            tvUsuario = itemView.findViewById(R.id.tvUsuario);
            tvRol = itemView.findViewById(R.id.tvRol);
            btnDelete = itemView.findViewById(R.id.btnDelete);
        }
    }

    public interface OnUserClickListener {
        void onEditClick(Usuario usuario);
        void onDeleteClick(Usuario usuario);
    }
}