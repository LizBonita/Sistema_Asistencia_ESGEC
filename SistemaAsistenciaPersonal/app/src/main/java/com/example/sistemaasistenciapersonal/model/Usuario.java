// com/example/sistemaasistenciapersonal/model/Usuario.java

package com.example.sistemaasistenciapersonal.model;

import android.os.Parcel;
import android.os.Parcelable;

import com.google.gson.annotations.SerializedName;

public class Usuario implements Parcelable {

    // ⚠️ ESTOS SERIALIZADOS DEBEN COINCIDIR EXACTAMENTE CON LOS CAMPOS JSON QUE ESPERA PHP
    @SerializedName("id")
    public int id = 0;

    @SerializedName("nombre_completo")
    public String nombre_completo;

    @SerializedName("usuario")
    public String usuario;

    @SerializedName("password_hash")
    public String password_hash;

    @SerializedName("rol_id")
    public int rol_id;

    @SerializedName("fecha_registro")
    public String fecha_registro = "";
    public String nombre;
    public int rol;

    // Constructor vacío necesario para Gson
    public Usuario() {}

    // Constructor con datos
    public Usuario(int id, String nombre_completo, String usuario, String password_hash, int rol_id, String fecha_registro) {
        this.id = id;
        this.nombre_completo = nombre_completo;
        this.usuario = usuario;
        this.password_hash = password_hash;
        this.rol_id = rol_id;
        this.fecha_registro = fecha_registro;
    }

    // Getters necesarios para acceso
    public int getId() { return id; }
    public String getNombreCompleto() { return nombre_completo; }
    public String getUsuario() { return usuario; }
    public String getPasswordHash() { return password_hash; }
    public int getRolId() { return rol_id; }
    public String getFechaRegistro() { return fecha_registro; }

    // Método para crear nuevo usuario con datos default
    public static Usuario nuevoUsuario(String nombre, String usuario, String pass, int rolId) {
        return new Usuario(0, nombre, usuario, pass, rolId, "");
    }

    // Para Parcelable (si lo usaste)
    protected Usuario(Parcel in) {
        id = in.readInt();
        nombre_completo = in.readString();
        usuario = in.readString();
        password_hash = in.readString();
        rol_id = in.readInt();
        fecha_registro = in.readString();
    }

    @Override
    public void writeToParcel(Parcel dest, int flags) {
        dest.writeInt(id);
        dest.writeString(nombre_completo);
        dest.writeString(usuario);
        dest.writeString(password_hash);
        dest.writeInt(rol_id);
        dest.writeString(fecha_registro);
    }

    @Override
    public int describeContents() {
        return 0;
    }

    public static final Creator<Usuario> CREATOR = new Creator<Usuario>() {
        @Override
        public Usuario createFromParcel(Parcel in) {
            return new Usuario(in);
        }

        @Override
        public Usuario[] newArray(int size) {
            return new Usuario[size];
        }
    };
}