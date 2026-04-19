package com.example.sistemaasistenciapersonal.model;

public class Asistencia {
    public int id;
    public int maestro_id;
    public String fecha;
    public String hora_entrada;
    public String hora_salida;
    public String estado_entrada;
    public String estado_salida;
    public int minutos_retraso;
    // Campos del JOIN
    public String nombre_maestro;
    public String tipo_contrato;
}