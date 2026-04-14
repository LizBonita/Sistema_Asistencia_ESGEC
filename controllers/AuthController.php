<?php
// controllers/AuthController.php - Versión de Diagnóstico

session_start();

include_once '../config/database.php';
include_once '../models/Usuario.php';

$database = new Database();
$db = $database->getConnection();

$usuario_model = new Usuario($db);

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    echo "<!-- Paso 1: POST recibido -->\n"; // Diagnosticar entrada al bloque POST
    $usuario_ingresado = trim($_POST['usuario']);
    $password_ingresado = $_POST['password'];

    // Opcional: Verificar valores recibidos (borrar después de probar)
    // var_dump($usuario_ingresado, $password_ingresado);

    echo "<!-- Paso 2: Llamando a login -->\n"; // Diagnosticar llamada a login
    $login_result = $usuario_model->login($usuario_ingresado, $password_ingresado);
    echo "<!-- Paso 3: Resultado de login: " . var_export($login_result, true) . " -->\n"; // Diagnosticar resultado de login

    if($login_result){ // <-- Aquí es crucial
        echo "<!-- Paso 4: Login exitoso, seteando sesión -->\n"; // Diagnosticar éxito
        $_SESSION['user_id'] = $usuario_model->id;
        $_SESSION['user_nombre'] = $usuario_model->nombre_completo;
        $_SESSION['user_rol_id'] = $usuario_model->rol_id;
        // Asegúrate de que getRolNombre funcione correctamente
        $rol_nombre = $usuario_model->getRolNombre($usuario_model->rol_id);
        $_SESSION['user_rol_nombre'] = $rol_nombre;

        echo "<!-- Paso 5: Redirigiendo a dashboard -->\n"; // Diagnosticar redirección
        header('Location: ../views/dashboard_admin.php');
        exit(); // Importante
    } else { // <-- Este else es para cuando login() devuelve FALSE
        echo "<!-- Paso 6: Login fallido -->\n"; // Diagnosticar fallo
        $error_message = "Usuario o contraseña incorrectos.";
        include_once '../views/login.php'; // Volver a mostrar login con error
    }
} else { // <-- Este else es para cuando no es POST
    echo "<!-- Paso 7: Acceso sin POST -->\n"; // Diagnosticar acceso incorrecto
    header('Location: ../views/login.php');
    exit();
}

// Si llega aquí, algo raro pasó
echo "<!-- Paso 8: Fin del script (esto no debería verse si hay redirección) -->\n";
?>