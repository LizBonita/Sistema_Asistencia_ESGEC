package com.example.sistemaasistenciapersonal.model;

public class Justificacion {
    public int id;
    public int id_maestro;
    public String motivo;
    public String fecha_solicitud;
    public String fecha_inicio;
    public String fecha_fin;
    public String estado; // "pendiente", "aprobado", "rechazado"
    public String nombre_maestro;
}