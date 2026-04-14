<?php
// config/database.php

class Database {
    private $host = 'localhost'; // Cambia si tu host es diferente
    private $db_name = 'sistema_asistencia_db'; // El nombre de tu base de datos
    private $username = 'root'; // Tu usuario de MySQL
    private $password = ''; // Tu contraseña de MySQL
    public $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Establecer charset UTF8 para evitar problemas con acentos
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            // No imprimir el error aquí, solo devolver null o lanzar una excepción
            // echo "Error de conexión: " . $exception->getMessage(); // COMENTAR O ELIMINAR ESTA LINEA
            return null; // O manejar el error como prefieras
        }
        return $this->conn;
    }
}
// Omitir la etiqueta de cierre de PHP