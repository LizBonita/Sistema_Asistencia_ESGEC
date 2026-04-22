<?php
class Horario {

    public $conn;
    private $table_name = "horarios";

    // Propiedades que coinciden con la tabla
    public $id;
    public $maestro_id;
    public $materia_id;
    public $grupo_id;
    public $dia_semana;
    public $hora_inicio;
    public $hora_fin;
    public $tolerancia_entrada;
    public $limite_retardo;

    public function __construct($db){
        $this->conn = $db;
    }

    // Crear un horario
    public function create(){
        $query = "INSERT INTO " . $this->table_name . " 
                  (maestro_id, materia_id, grupo_id, dia_semana, hora_inicio, hora_fin, tolerancia_entrada, limite_retardo)
                  VALUES (:maestro_id, :materia_id, :grupo_id, :dia_semana, :hora_inicio, :hora_fin, :tolerancia_entrada, :limite_retardo)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':maestro_id', $this->maestro_id);
        $stmt->bindParam(':materia_id', $this->materia_id);
        $stmt->bindParam(':grupo_id', $this->grupo_id);
        $stmt->bindParam(':dia_semana', $this->dia_semana);
        $stmt->bindParam(':hora_inicio', $this->hora_inicio);
        $stmt->bindParam(':hora_fin', $this->hora_fin);
        $stmt->bindParam(':tolerancia_entrada', $this->tolerancia_entrada);
        $stmt->bindParam(':limite_retardo', $this->limite_retardo);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Leer horarios
    public function read(){
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY dia_semana, hora_inicio";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Leer un horario por ID
    public function readOne(){
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->maestro_id = $row['maestro_id'];
            $this->materia_id = $row['materia_id'];
            $this->grupo_id = $row['grupo_id'];
            $this->dia_semana = $row['dia_semana'];
            $this->hora_inicio = $row['hora_inicio'];
            $this->hora_fin = $row['hora_fin'];
            $this->tolerancia_entrada = $row['tolerancia_entrada'];
            $this->limite_retardo = $row['limite_retardo'];
            return true;
        }
        return false;
    }

    // Actualizar un horario
    public function update(){
        $query = "UPDATE " . $this->table_name . " 
                  SET grupo_id = :grupo_id,
                      dia_semana = :dia_semana,
                      hora_inicio = :hora_inicio,
                      hora_fin = :hora_fin
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitizar datos
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->grupo_id = htmlspecialchars(strip_tags($this->grupo_id));
        $this->dia_semana = htmlspecialchars(strip_tags($this->dia_semana));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));

        // Bind parameters
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':grupo_id', $this->grupo_id);
        $stmt->bindParam(':dia_semana', $this->dia_semana);
        $stmt->bindParam(':hora_inicio', $this->hora_inicio);
        $stmt->bindParam(':hora_fin', $this->hora_fin);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Eliminar un horario
    public function delete(){
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
}
?>