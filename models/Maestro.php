<?php
// models/Maestro.php

class Maestro {

    private $conn;
    private $table_name = "maestros";

    public $id;
    public $usuario_id;
    public $tipo_contrato;
    public $nombre_completo_usuario; // Campo extraído de la tabla usuarios para mostrar
    public $fecha_registro; // Añadido para manejar fecha_registro si es necesario


    public function __construct($db){
        $this->conn = $db;
    }

    // Leer todos los maestros con su info de usuario
    public function read(){
        $query = "SELECT m.id, u.nombre_completo as nombre_completo_usuario, m.tipo_contrato, m.fecha_registro 
                  FROM " . $this->table_name . " m
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear un nuevo maestro (requiere ID de usuario existente)
    public function create(){
        // La lógica para crear un maestro implica crear también un usuario.
        // Esto se haría típicamente en un controlador específico que maneje ambas tablas.
        // Por ahora, simplificamos. Suponemos que usuario_id ya existe.
        $query = "INSERT INTO " . $this->table_name . " SET usuario_id=:usuario_id, tipo_contrato=:tipo";

        $stmt = $this->conn->prepare($query);
        $this->usuario_id=htmlspecialchars(strip_tags($this->usuario_id));
        $this->tipo_contrato=htmlspecialchars(strip_tags($this->tipo_contrato));

        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":tipo", $this->tipo_contrato);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Leer un maestro por ID
    public function readOne(){
        $query = "SELECT m.id, u.nombre_completo as nombre_completo_usuario, m.tipo_contrato, m.fecha_registro 
                  FROM " . $this->table_name . " m
                  LEFT JOIN usuarios u ON m.usuario_id = u.id
                  WHERE m.id = ?
                  LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->nombre_completo_usuario = $row['nombre_completo_usuario'];
            $this->tipo_contrato = $row['tipo_contrato'];
            $this->fecha_registro = $row['fecha_registro'];
            return true;
        }
        return false;
    }

    // Actualizar un maestro
    public function update(){
        $query = "UPDATE " . $this->table_name . " SET tipo_contrato=:tipo WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->tipo_contrato=htmlspecialchars(strip_tags($this->tipo_contrato));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':tipo', $this->tipo_contrato);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Eliminar un maestro (y su usuario asociado por CASCADE)
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Método para obtener la lista de usuarios que pueden ser maestros (rol Maestro o Admin)
    // Este método es útil para el CRUD de Maestros, para seleccionar el usuario al crear/editar
    public function getUsuariosDisponiblesParaMaestro() {
        $query = "SELECT u.id, u.nombre_completo, u.usuario, r.nombre as rol_nombre
                  FROM usuarios u
                  JOIN roles r ON u.rol_id = r.id
                  WHERE r.nombre IN ('Maestro', 'Administrador', 'Director') -- Ajusta los roles según necesites
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>