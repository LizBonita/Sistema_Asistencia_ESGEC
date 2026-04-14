<?php
class Grupo {

    private $conn;
    private $table_name = "grupos";

    // Propiedades que coinciden con la tabla
    public $id;
    public $nombre;

    public function __construct($db){
        $this->conn = $db;
    }

    // Leer todos los grupos
    public function read(){
        $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

        // Leer todos los grupos ordenados
    public function readOrdered($order = 'id') {
        $allowed_orders = ['id', 'nombre'];
        if (!in_array($order, $allowed_orders)) {
            $order = 'id'; // Valor predeterminado si el orden no es válido
        }

        $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY $order";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }


    // Leer un grupo por ID
    public function readOne(){
        $query = "SELECT id, nombre FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->nombre = $row['nombre'];
            return true;
        }
        return false;
    }

    // Crear un nuevo grupo
    public function create(){
        $query = "INSERT INTO " . $this->table_name . " SET nombre=:nombre";

        $stmt = $this->conn->prepare($query);
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));

        $stmt->bindParam(":nombre", $this->nombre);

        if($stmt->execute()){
            return true; // Éxito
        }
        return false; // Error
    }

    // Actualizar un grupo
    public function update(){
        $query = "UPDATE " . $this->table_name . " SET nombre=:nombre WHERE id=:id";

        $stmt = $this->conn->prepare($query);
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Eliminar un grupo
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }
}
?>