<?php
// models/Asistencia.php

class Asistencia {

    private $conn;
    private $table_name = "asistencias";

    public $id;
    public $maestro_id;
    public $fecha;
    public $hora_entrada;
    public $hora_salida;
    public $estado_entrada;
    public $estado_salida;
    public $minutos_retraso;

    // Campos para mostrar nombres
    public $nombre_maestro;

    public function __construct($db){
        $this->conn = $db;
    }

    // Leer asistencias de un día específico (por defecto hoy)
    public function readByDate($date = null){
        if (!$date) {
            $date = date('Y-m-d');
        }
        $query = "SELECT a.id, CONCAT(u.nombre_completo) as nombre_maestro, a.fecha, a.hora_entrada, a.hora_salida, a.estado_entrada, a.estado_salida, a.minutos_retraso
                  FROM " . $this->table_name . " a
                  LEFT JOIN maestros m ON a.maestro_id = m.id
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  WHERE a.fecha = ?
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $date);
        $stmt->execute();
        return $stmt;
    }

    // Crear una nueva asistencia (usado por la integración biométrica)
    public function create(){
        $query = "INSERT INTO " . $this->table_name . " SET maestro_id=:maestro_id, fecha=:fecha, hora_entrada=:hora_entrada, estado_entrada=:estado_entrada, minutos_retraso=:minutos_retraso";
        $stmt = $this->conn->prepare($query);

        $this->maestro_id=htmlspecialchars(strip_tags($this->maestro_id));
        $this->fecha=htmlspecialchars(strip_tags($this->fecha));
        $this->hora_entrada=htmlspecialchars(strip_tags($this->hora_entrada));
        $this->estado_entrada=htmlspecialchars(strip_tags($this->estado_entrada));
        $this->minutos_retraso=htmlspecialchars(strip_tags($this->minutos_retraso));

        $stmt->bindParam(":maestro_id", $this->maestro_id);
        $stmt->bindParam(":fecha", $this->fecha);
        $stmt->bindParam(":hora_entrada", $this->hora_entrada);
        $stmt->bindParam(":estado_entrada", $this->estado_entrada);
        $stmt->bindParam(":minutos_retraso", $this->minutos_retraso);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Leer una asistencia por ID
    public function readOne(){
        $query = "SELECT a.id, a.maestro_id, a.fecha, a.hora_entrada, a.hora_salida, a.estado_entrada, a.estado_salida, a.minutos_retraso
                  FROM " . $this->table_name . " a
                  WHERE a.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->maestro_id = $row['maestro_id'];
            $this->fecha = $row['fecha'];
            $this->hora_entrada = $row['hora_entrada'];
            $this->hora_salida = $row['hora_salida'];
            $this->estado_entrada = $row['estado_entrada'];
            $this->estado_salida = $row['estado_salida'];
            $this->minutos_retraso = $row['minutos_retraso'];
            return true;
        }
        return false;
    }

    // Actualizar una asistencia (por ejemplo, al registrar salida o justificar)
    public function update(){
        // Ejemplo para actualizar salida
        $query = "UPDATE " . $this->table_name . " SET hora_salida=:hora_salida, estado_salida=:estado_salida WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->hora_salida=htmlspecialchars(strip_tags($this->hora_salida));
        $this->estado_salida=htmlspecialchars(strip_tags($this->estado_salida));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':hora_salida', $this->hora_salida);
        $stmt->bindParam(':estado_salida', $this->estado_salida);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Eliminar una asistencia
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>