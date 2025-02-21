<?php
    require_once("../database.php");
    require_once("../utils/logger.php");

    custom_session_start('admin_session');

    // Inicializar variables de control de intentos
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['last_attempt_time'] = 0;
    }

    if(isset($_POST["btnLogin"])) {
        $current_time = time();
        
        // Verificar si debe esperar
        if ($_SESSION['login_attempts'] >= 3 && ($current_time - $_SESSION['last_attempt_time']) < 30) {
            $tiempo_restante = 30 - ($current_time - $_SESSION['last_attempt_time']);
            $_SESSION['error'] = "Demasiados intentos fallidos. Por favor, espere $tiempo_restante segundos.";
            header("Location: ../views/login.php");
            exit();
        }

        if(empty($_POST["id"]) || empty($_POST["password"])) {
            $_SESSION['error'] = "Todos los campos son obligatorios";
            header("Location: ../views/login.php");
            exit();
        } else {
            $ci = $_POST["id"];
            $password = $_POST["password"];
            
            $ci = $conexion->real_escape_string($ci);
            
            $sql = "SELECT * FROM usuarios WHERE ci=?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("s", $ci);
            $stmt->execute();
            $resultado = $stmt->get_result();
            
            if($usuario = $resultado->fetch_assoc()) {
                // Verificar si el usuario está activo
                if ($usuario['idEstado'] != 2) {
                    $_SESSION['error'] = "Error de credenciales"; // Usuario inactivo
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    header("Location: ../views/login.php");
                    exit();
                }

                if(password_verify($password, $usuario['password'])) {
                    // Reiniciar contadores en caso de éxito
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['last_attempt_time'] = 0;
                    
                    $_SESSION['admin_id'] = $usuario['id'];
                    $_SESSION['admin_name'] = $usuario['nombres'];
                    $_SESSION['admin_role'] = $usuario['nombre_rol'];
                    log_activity("Administrador con ID " . $usuario['id'] . " ha iniciado sesión exitosamente");
                    header("Location: ../views/eventos.php");
                    exit();
                } else {
                    $_SESSION['login_attempts']++;
                    $_SESSION['last_attempt_time'] = time();
                    if ($_SESSION['login_attempts'] >= 3) {
                        $_SESSION['error'] = "Espere 30 segundos para volver a intentarlo";
                    } else {
                        $_SESSION['error'] = "Error de credenciales";
                    }
                    header("Location: ../views/login.php");
                    exit();
                }
            } else {
                $_SESSION['login_attempts']++;
                $_SESSION['last_attempt_time'] = time();
                $_SESSION['error'] = "Error de credenciales";
                header("Location: ../views/login.php");
                exit();
            }
        }
    } else {
        header("Location: ../views/login.php");
        exit();
    }
?>
