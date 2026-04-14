<?php
class Materia {
    private $conn;
    public $id_materia;
    public $nombre_materia;
    public $nivel;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Método para leer materias ordenadas
    public function readOrdered($order = 'id') {
        $allowed_orders = ['id', 'nombre'];
        if (!in_array($order, $allowed_orders)) {
            $order = 'id'; // Valor predeterminado si el orden no es válido
        }

        $query = "SELECT id, nombre FROM materias ORDER BY $order";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    public function read() {
        $query = "SELECT id, nombre FROM materias ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT id, nombre FROM materias WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_materia);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->nombre_materia = $row['nombre'];
            return true;
        }
        return false;
    }

    public function create() {
        $query = "INSERT INTO materias (nombre) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->nombre_materia);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE materias SET nombre  = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->nombre_materia);
        $stmt->bindParam(3, $this->id_materia);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM materias WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_materia);
        return $stmt->execute();
    }
}
?>