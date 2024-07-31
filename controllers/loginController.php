<?php
require_once("../database.php");

session_start();

if(isset($_POST["btnLogin"])) {
    if(empty($_POST["id"]) || empty($_POST["password"])) {
        $_SESSION['error'] = "Todos los campos son obligatorios";
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
                $_SESSION['user_role'] = $usuario['rol'];
                header("Location: ../views/eventos.php");
                exit();
            } else {
                $_SESSION['error'] = "Credenciales inválidas. Por favor, intente de nuevo.";
                header("Location: ../views/login.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Credenciales inválidas. Por favor, intente de nuevo.";
            header("Location: ../views/login.php");
            exit();
        }
    }
} else {
    header("Location: ../views/login.php");
    exit();
}
?>