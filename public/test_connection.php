<?php
require_once '../src/config/database.php';
require_once '../src/utils/Database.php';

$database = new Database();
$db = $database->getConnection();

if ($db) {
    echo "Conexión exitosa a la base de datos.";
} else {
    echo "Error al conectar a la base de datos.";
}
?>