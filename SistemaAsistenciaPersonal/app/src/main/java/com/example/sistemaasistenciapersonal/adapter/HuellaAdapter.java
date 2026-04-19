package com.example.sistemaasistenciapersonal.adapter;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.util.Base64;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;
import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;
import com.example.sistemaasistenciapersonal.R;
import com.example.sistemaasistenciapersonal.model.HuellaInfo;
import com.squareup.picasso.Picasso;
import java.util.List;

public class HuellaAdapter extends RecyclerView.Adapter<HuellaAdapter.ViewHolder> {

    private final List<HuellaInfo> lista;
    private static final String BASE_URL = "https://greenyellow-butterfly-472178.hostingersite.com/";

    public HuellaAdapter(List<HuellaInfo> lista) {
        this.lista = lista;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(parent.getContext())
                .inflate(R.layout.item_huella, parent, false);
        return new ViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        HuellaInfo h = lista.get(position);

        holder.tvNombreMaestro.setText(h.nombre_completo != null ? h.nombre_completo : "Maestro #" + h.maestro_id);

        String contrato = h.tipo_contrato != null ? h.tipo_contrato.replace("_", " ") : "";
        if (!contrato.isEmpty()) {
            contrato = contrato.substring(0, 1).toUpperCase() + contrato.substring(1);
        }
        holder.tvContrato.setText(contrato);

        // Estado
        if (h.tiene_huella == 1) {
            holder.tvEstado.setText("✓ Registrada");
            holder.tvEstado.setTextColor(0xFF006847);
            holder.tvEstado.setBackgroundColor(0xFFE8F5E9);

            holder.tvFechaHuella.setText("Registrada: " + (h.fecha_huella != null ? h.fecha_huella.substring(0, Math.min(10, h.fecha_huella.length())) : ""));
            holder.tvFechaHuella.setVisibility(View.VISIBLE);

            // Cargar imagen de la huella
            loadFingerprintImage(holder.ivHuella, h);
        } else {
            holder.tvEstado.setText("✗ Pendiente");
            holder.tvEstado.setTextColor(0xFFD32F2F);
            holder.tvEstado.setBackgroundColor(0xFFFFEBEE);
            holder.tvFechaHuella.setVisibility(View.GONE);

            // Icono placeholder
            holder.ivHuella.setImageResource(R.drawable.ic_lock);
            holder.ivHuella.setPadding(28, 28, 28, 28);
            holder.ivHuella.setColorFilter(0xFF94A3B8);
        }
    }

    private void loadFingerprintImage(ImageView imageView, HuellaInfo h) {
        // Prioridad 1: imagen base64
        if (h.imagen_base64 != null && !h.imagen_base64.isEmpty()) {
            try {
                String base64Data = h.imagen_base64;
                if (base64Data.contains(",")) {
                    base64Data = base64Data.substring(base64Data.indexOf(",") + 1);
                }
                byte[] decodedBytes = Base64.decode(base64Data, Base64.DEFAULT);
                Bitmap bitmap = BitmapFactory.decodeByteArray(decodedBytes, 0, decodedBytes.length);
                if (bitmap != null) {
                    imageView.setPadding(0, 0, 0, 0);
                    imageView.setColorFilter(null);
                    imageView.setImageBitmap(bitmap);
                    return;
                }
            } catch (Exception e) {
                // Fall through to URL
            }
        }

        // Prioridad 2: usar endpoint de conversión BMP→PNG
        if (h.maestro_id > 0) {
            String imageUrl = BASE_URL + "api/get_huella_imagen.php?maestro_id=" + h.maestro_id;
            imageView.setPadding(0, 0, 0, 0);
            imageView.setColorFilter(null);
            try {
                Picasso.get()
                        .load(imageUrl)
                        .placeholder(R.drawable.ic_lock)
                        .error(R.drawable.ic_lock)
                        .fit()
                        .centerCrop()
                        .into(imageView);
            } catch (Exception e) {
                imageView.setImageResource(R.drawable.ic_lock);
            }
        }
    }

    @Override
    public int getItemCount() {
        return lista.size();
    }

    static class ViewHolder extends RecyclerView.ViewHolder {
        TextView tvNombreMaestro, tvContrato, tvFechaHuella, tvEstado;
        ImageView ivHuella;

        ViewHolder(View itemView) {
            super(itemView);
            tvNombreMaestro = itemView.findViewById(R.id.tvNombreMaestro);
            tvContrato = itemView.findViewById(R.id.tvContrato);
            tvFechaHuella = itemView.findViewById(R.id.tvFechaHuella);
            tvEstado = itemView.findViewById(R.id.tvEstado);
            ivHuella = itemView.findViewById(R.id.ivHuella);
        }
    }
}
