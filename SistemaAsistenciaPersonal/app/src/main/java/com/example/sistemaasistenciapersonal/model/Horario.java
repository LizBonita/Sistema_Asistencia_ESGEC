package com.example.sistemaasistenciapersonal.model;

public class Horario {
    public int id;
    public int id_grupo;
    public int id_materia;
    public String dia;
    public String hora_inicio;
    public String hora_fin;
    // Campos del JOIN
    public String nombre_grupo;
    public String nombre_materia;
}