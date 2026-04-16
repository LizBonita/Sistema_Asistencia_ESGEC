<?php
// models/Huella.php

class Huella {

    private $conn;
    private $table_name = "huellas_dactilares";

    public $id;
    public $maestro_id;
    public $dedo;
    public $template_data;
    public $imagen_path;
    public $imagen_base64;
    public $fecha_registro;
    public $activo;

    // Campos join
    public $nombre_maestro;

    public function __construct($db){
        $this->conn = $db;
    }

    // Registrar una nueva huella
    public function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  (maestro_id, dedo, template_data, imagen_path, imagen_base64) 
                  VALUES (:maestro_id, :dedo, :template_data, :imagen_path, :imagen_base64)
                  ON DUPLICATE KEY UPDATE 
                  template_data = VALUES(template_data),
                  imagen_path = VALUES(imagen_path),
                  imagen_base64 = VALUES(imagen_base64),
                  fecha_registro = CURRENT_TIMESTAMP,
                  activo = 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":maestro_id", $this->maestro_id);
        $stmt->bindParam(":dedo", $this->dedo);
        $stmt->bindParam(":template_data", $this->template_data);
        $stmt->bindParam(":imagen_path", $this->imagen_path);
        $stmt->bindParam(":imagen_base64", $this->imagen_base64);

        return $stmt->execute();
    }

    // Obtener template de un maestro especifico
    public function getByMaestro($maestro_id){
        $query = "SELECT h.id, h.maestro_id, h.dedo, h.template_data, h.imagen_path, h.fecha_registro
                  FROM " . $this->table_name . " h
                  WHERE h.maestro_id = ? AND h.activo = 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $maestro_id);
        $stmt->execute();
        return $stmt;
    }

    // Obtener TODOS los templates activos (para identify)
    public function getAllTemplates(){
        $query = "SELECT h.maestro_id, h.template_data, h.dedo,
                         u.nombre_completo as nombre_maestro
                  FROM " . $this->table_name . " h
                  JOIN maestros m ON h.maestro_id = m.id
                  JOIN usuarios u ON m.usuario_id = u.id
                  WHERE h.activo = 1
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Listar maestros con estado de huella (tiene/no tiene)
    public function listarMaestrosConEstado(){
        $query = "SELECT m.id as maestro_id, u.nombre_completo, m.tipo_contrato,
                         CASE WHEN h.id IS NOT NULL THEN 1 ELSE 0 END as tiene_huella,
                         h.imagen_path, h.imagen_base64, h.fecha_registro as fecha_huella
                  FROM maestros m
                  JOIN usuarios u ON m.usuario_id = u.id
                  LEFT JOIN " . $this->table_name . " h ON m.id = h.maestro_id AND h.activo = 1
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Eliminar (desactivar) huella
    public function deactivate($maestro_id){
        $query = "UPDATE " . $this->table_name . " SET activo = 0 WHERE maestro_id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $maestro_id);
        return $stmt->execute();
    }
}
