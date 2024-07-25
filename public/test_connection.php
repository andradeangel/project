<?php
// public/test_connection.php

require_once '../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        echo "¡Conexión exitosa a la base de datos!";
        
        // Información adicional sobre la conexión
        echo "<br>Versión del servidor: " . $db->getAttribute(PDO::ATTR_SERVER_VERSION);
        echo "<br>Versión del cliente: " . $db->getAttribute(PDO::ATTR_CLIENT_VERSION);
        
        // Intentar una consulta simple
        $stmt = $db->query("SELECT 1");
        if ($stmt) {
            echo "<br>Consulta de prueba ejecutada con éxito.";
        }
    }
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>