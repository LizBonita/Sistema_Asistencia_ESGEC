// com/example/sistemaasistenciapersonal/model/ResponseUsuario.java

package com.example.sistemaasistenciapersonal.model;

import com.google.gson.annotations.SerializedName;

public class ResponseUsuario {

    @SerializedName("status")
    public String status;

    @SerializedName("message")
    public String message;

    @SerializedName("user")
    public Usuario user;

    // Getters
    public String getStatus() { return status; }
    public String getMessage() { return message; }
    public Usuario getUser() { return user; }
}