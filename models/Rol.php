<?php
// models/Rol.php

class Rol {

    private $conn;
    private $table_name = "roles";

    public $id;
    public $nombre;

    public function __construct($db){
        $this->conn = $db;
    }

    // Leer todos los roles
    public function readAll(){
        $query = "SELECT id, nombre FROM " . $this->table_name . " ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>