<?php
// config/database.php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Detectar ambiente automáticamente
        if ($_SERVER['HTTP_HOST'] === 'localhost' || 
            strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false) {
            // ─── XAMPP Local ───
            $this->host     = 'localhost';
            $this->db_name  = 'sistema_asistencia_db';
            $this->username = 'root';
            $this->password = '';
        } else {
            // ─── Hostinger Producción ───
            $this->host     = 'localhost';
            $this->db_name  = 'u596094670_sistema_asist';
            $this->username = 'u596094670_Liz';
            $this->password = 'LizE@110324';
        }
    }

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                                  $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Establecer charset UTF8 para evitar problemas con acentos
            $this->conn->exec("set names utf8mb4");
        } catch(PDOException $exception) {
            return null;
        }
        return $this->conn;
    }
}
// Omitir la etiqueta de cierre de PHP