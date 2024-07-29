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
?>