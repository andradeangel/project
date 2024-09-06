<?php
require_once("../database.php");
require_once("../utils/logger.php");

session_start();

if(isset($_POST["btnLogin"])) {
    if(empty($_POST["id"]) || empty($_POST["password"])) {
        $_SESSION['error'] = "Todos los campos son obligatorios";
        log_activity("Intento de inicio de sesión fallido: campos vacíos", "warning");
        header("Location: ../views/login.php");
        exit();
    } else {
        $ci = $_POST["id"];
        $password = $_POST["password"];
        
        // Escapar los datos de entrada para prevenir inyección SQL
        $ci = $conexion->real_escape_string($ci);
        
        // Preparar la consulta SQL (ahora solo buscamos por CI)
        $sql = "SELECT * FROM usuarios WHERE ci=?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("s", $ci);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if($usuario = $resultado->fetch_assoc()) {
            // Verificar la contraseña hasheada
            if(password_verify($password, $usuario['password'])) {
                $_SESSION['user_id'] = $usuario['id'];
                $_SESSION['user_role'] = $usuario['idRol'];
                log_activity("Usuario con ID " . $usuario['id'] . " ha iniciado sesión exitosamente");
                header("Location: ../views/eventos.php");
                exit();
            } else {
                $_SESSION['error'] = "Credenciales inválidas. Por favor, intente de nuevo.";
                log_activity("Intento de inicio de sesión fallido para el usuario con CI: " . $ci, "warning");
                header("Location: ../views/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Credenciales inválidas. Por favor, intente de nuevo.";
            log_activity("Intento de inicio de sesión fallido para un CI no existente: " . $ci, "warning");
            header("Location: ../views/login.php");
            exit();
        }
    }
} else {
    header("Location: ../views/login.php");
    exit();
}
?>