<?php
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "scaperoom";
    try {
        $conexion = new mysqli($host, $username, $password, $database);
        $conexion->set_charset("utf8");
        if ($conexion->connect_error) {
            throw new Exception("Error de conexión: " . $conexion->connect_error);
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }

    if (!function_exists('custom_session_start')) {
        function custom_session_start($session_name = 'default') {
            if (session_status() == PHP_SESSION_NONE) {
                session_name($session_name);
                session_start();
            } elseif (session_name() != $session_name) {
                session_write_close();
                session_name($session_name);
                session_start();
            }
        }
    }
?>
