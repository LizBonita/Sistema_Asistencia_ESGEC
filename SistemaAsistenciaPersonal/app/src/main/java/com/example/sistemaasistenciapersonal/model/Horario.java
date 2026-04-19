package com.example.sistemaasistenciapersonal.model;

public class Horario {
    public int id;
    public int maestro_id;
    public int id_maestro; // alias
    public int materia_id;
    public int id_materia; // alias
    public int grupo_id;
    public int id_grupo; // alias
    public String dia_semana;
    public String dia; // alias
    public String hora_inicio;
    public String hora_fin;
    public String tolerancia_entrada; // Puede venir como TIME "00:00:10" o como int
    public String limite_retardo;     // Puede venir como TIME "00:00:15" o como int
    // Campos del JOIN
    public String nombre_grupo;
    public String nombre_materia;
    public String nombre_maestro;

    // Helpers para obtener valores sin importar qué alias use la API
    public int getMaestroId() {
        return maestro_id > 0 ? maestro_id : id_maestro;
    }
    public int getMateriaId() {
        return materia_id > 0 ? materia_id : id_materia;
    }
    public int getGrupoId() {
        return grupo_id > 0 ? grupo_id : id_grupo;
    }
    public String getDia() {
        return dia_semana != null && !dia_semana.isEmpty() ? dia_semana : (dia != null ? dia : "");
    }
}