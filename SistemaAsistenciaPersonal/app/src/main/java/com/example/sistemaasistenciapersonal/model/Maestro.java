package com.example.sistemaasistenciapersonal.model;

public class Maestro {
    public int id;
    public int usuario_id;
    public String tipo_contrato;
    public String fecha_registro;
    // Campos del JOIN con usuarios
    public String nombre_completo;
    public String usuario;
    public int rol_id;
}