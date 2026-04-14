<?php
// models/Usuario.php

class Usuario {

    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre_completo;
    public $usuario;
    public $password_hash; // Ahora representa la contraseña en texto plano
    public $rol_id;
    public $fecha_registro;

    public function __construct($db){
        $this->conn = $db;
    }

    // Método para verificar credenciales (CONTRASEÑA EN TEXTO PLANO - NO SEGURO)
    public function login($usuario, $password_plana) {
        $query = "SELECT id, nombre_completo, usuario, password_hash, rol_id FROM " . $this->table_name . " WHERE usuario = ? LIMIT 0,1";
        $stmt = $this->conn->prepare( $query );
        $stmt->bindParam(1, $usuario);
        $stmt->execute();
        $num = $stmt->rowCount();

        if($num > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Comparar directamente la contraseña plana con la almacenada (NO SEGURO)
            if($password_plana === $row['password_hash']){
                // Credenciales correctas
                $this->id = $row['id'];
                $this->nombre_completo = $row['nombre_completo'];
                $this->usuario = $row['usuario'];
                $this->rol_id = $row['rol_id'];
                return true;
            }
        }
        return false;
    }

    // Método para obtener el rol del usuario logueado
    public function getRolNombre($rol_id) {
        $query = "SELECT nombre FROM roles WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $rol_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row['nombre'] : null;
    }

    // --- NUEVOS MÉTODOS PARA CRUD ---

    // Leer todos los usuarios con nombre del rol
    public function readAll(){
        // ASEGÚRATE DE INCLUIR u.rol_id EN EL SELECT
        $query = "SELECT u.id, u.nombre_completo, u.usuario, u.rol_id, r.nombre as rol_nombre, u.fecha_registro 
                  FROM " . $this->table_name . " u
                  LEFT JOIN roles r ON u.rol_id = r.id
                  ORDER BY u.nombre_completo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Crear un nuevo usuario (CONTRASEÑA EN TEXTO PLANO - NO SEGURO)
    public function create($password_plana_para_guardar){
        // Asignar directamente la contraseña plana a la variable del objeto
        $this->password_hash = $password_plana_para_guardar;

        $query = "INSERT INTO " . $this->table_name . " SET nombre_completo=:nombre, usuario=:usuario, password_hash=:password_hash, rol_id=:rol_id";

        $stmt = $this->conn->prepare($query);

        $this->nombre_completo=htmlspecialchars(strip_tags($this->nombre_completo));
        $this->usuario=htmlspecialchars(strip_tags($this->usuario));
        // La contraseña ya está asignada arriba
        $this->rol_id=htmlspecialchars(strip_tags($this->rol_id));

        $stmt->bindParam(":nombre", $this->nombre_completo);
        $stmt->bindParam(":usuario", $this->usuario);
        $stmt->bindParam(":password_hash", $this->password_hash); // Usar la contraseña plana
        $stmt->bindParam(":rol_id", $this->rol_id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Leer un usuario por ID
    public function readOne(){
        $query = "SELECT u.id, u.nombre_completo, u.usuario, u.password_hash, u.rol_id, u.fecha_registro 
                  FROM " . $this->table_name . " u
                  WHERE u.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row){
            $this->id = $row['id'];
            $this->nombre_completo = $row['nombre_completo'];
            $this->usuario = $row['usuario'];
            $this->password_hash = $row['password_hash']; // Aunque no es buena idea mostrarla
            $this->rol_id = $row['rol_id'];
            $this->fecha_registro = $row['fecha_registro'];
            return true;
        }
        return false;
    }

    // Actualizar un usuario (sin cambiar la contraseña aquí)
    public function update(){
        $query = "UPDATE " . $this->table_name . " SET nombre_completo=:nombre, usuario=:usuario, rol_id=:rol_id WHERE id=:id";

        $stmt = $this->conn->prepare($query);

        $this->id=htmlspecialchars(strip_tags($this->id));
        $this->nombre_completo=htmlspecialchars(strip_tags($this->nombre_completo));
        $this->usuario=htmlspecialchars(strip_tags($this->usuario));
        $this->rol_id=htmlspecialchars(strip_tags($this->rol_id));

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre_completo);
        $stmt->bindParam(':usuario', $this->usuario);
        $stmt->bindParam(':rol_id', $this->rol_id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }

    // Eliminar un usuario
    public function delete(){
        // Precaución: Eliminar un usuario puede afectar maestros, asistencias, etc. si no usas CASCADE
        $query = "DELETE FROM " . $this->table_name . " WHERE id=:id";
        $stmt = $this->conn->prepare($query);
        $this->id=htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()){
            return true;
        }
        return false;
    }
}
?>