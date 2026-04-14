<?php
// models/Incidencia.php

class Incidencia {

    private $conn;
    private $table_name = "incidencias";

    public $id;
    public $asistencia_id;
    public $tipo_incidencia;
    public $descripcion;
    public $fecha_solicitud;
    public $aprobada;
    public $fecha_aprobacion_rechazo;
    public $comentario_director;

    // Campos para mostrar info de la asistencia
    public $nombre_maestro;
    public $fecha_asistencia;
    public $hora_entrada_asistencia;
    public $estado_entrada_asistencia;

    public function __construct($db){
        $this->conn = $db;
    }

    // Leer incidencias pendientes (no aprobadas ni rechazadas)
    public function readPendientes(){
        $query = "SELECT i.id, CONCAT(u.nombre_completo) as nombre_maestro, a.fecha as fecha_asistencia, a.hora_entrada as hora_entrada_asistencia, a.estado_entrada as estado_entrada_asistencia, i.tipo_incidencia, i.descripcion, i.fecha_solicitud
                  FROM " . $this->table_name . " i
                  LEFT JOIN asistencias a ON i.asistencia_id = a.id
                  LEFT JOIN maestros m ON a.maestro_id = m.id
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  WHERE i.aprobada IS NULL -- Pendiente
                  ORDER BY i.fecha_solicitud DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer todas las incidencias
    public function readAll(){
        $query = "SELECT i.id, CONCAT(u.nombre_completo) as nombre_maestro, a.fecha as fecha_asistencia, a.hora_entrada as hora_entrada_asistencia, a.estado_entrada as estado_entrada_asistencia, i.tipo_incidencia, i.descripcion, i.fecha_solicitud, i.aprobada, i.comentario_director
                  FROM " . $this->table_name . " i
                  LEFT JOIN asistencias a ON i.asistencia_id = a.id
                  LEFT JOIN maestros m ON a.maestro_id = m.id
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  ORDER BY i.fecha_solicitud DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear una nueva incidencia (solicitud de justificación)
    public function create(){
        $query = "INSERT INTO " . $this->table_name . " SET asistencia_id=:asistencia_id, tipo_incidencia=:tipo, descripcion=:descripcion";
        $stmt = $this->conn->prepare($query);

        $this->asistencia_id=htmlspecialchars(strip_tags($this->asistencia_id));
        $this->tipo_incidencia=htmlspecialchars(strip_tags($this->tipo_incidencia));
        $this->descripcion=htmlspecialchars(strip_tags($this->descripcion));

        $stmt->bindParam(":asistencia_id", $this->asistencia_id);
        $stmt->bindParam(":tipo", $this->tipo_incidencia);
        $stmt->bindParam(":descripcion", $this->descripcion);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Aprobar o rechazar una incidencia
    public function aprobarRechazar($aprobada, $comentario = ''){
        $aprobada_val = $aprobada ? 1 : 0; // Convertir booleano a 0 o 1
        $query = "UPDATE " . $this->table_name . " SET aprobada=:aprobada, fecha_aprobacion_rechazo=NOW(), comentario_director=:comentario WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':aprobada', $aprobada_val);
        $stmt->bindParam(':comentario', $comentario);

        if($stmt->execute()){
            // Opcional: Actualizar el estado de la asistencia original si se aprueba
            if($aprobada) {
                 // Por ejemplo, cambiar estado_entrada a 'Justificado'
                 $asistencia = new Asistencia($this->conn);
                 $asistencia->id = $this->asistencia_id;
                 $asistencia->estado_entrada = 'Justificado';
                 $asistencia->update();
            }
            return true;
        }
        return false;
    }

    // Leer una incidencia por ID
    public function readOne(){
        $query = "SELECT i.id, i.asistencia_id, i.tipo_incidencia, i.descripcion, i.fecha_solicitud, i.aprobada, i.fecha_aprobacion_rechazo, i.comentario_director
                  FROM " . $this->table_name . " i
                  WHERE i.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->asistencia_id = $row['asistencia_id'];
            $this->tipo_incidencia = $row['tipo_incidencia'];
            $this->descripcion = $row['descripcion'];
            $this->fecha_solicitud = $row['fecha_solicitud'];
            $this->aprobada = $row['aprobada'];
            $this->fecha_aprobacion_rechazo = $row['fecha_aprobacion_rechazo'];
            $this->comentario_director = $row['comentario_director'];
            return true;
        }
        return false;
    }

    // Eliminar una incidencia
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>