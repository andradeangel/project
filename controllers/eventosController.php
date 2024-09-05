<?php
require_once("../database.php");
require_once("../models/eventosModel.php");

$orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'e.fechaInicio';
$orderDir = isset($_GET['orderDir']) ? $_GET['orderDir'] : 'DESC';

$eventos = obtenerEventos($conexion, $orderBy, $orderDir);
$sprints = obtenerSprints($conexion);  // Necesitamos crear esta función en el modelo

function formatDate($date) {
    return date('d/m/y - H:i', strtotime($date));
}

// Manejar la creación de nuevos eventos
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $fechaInicio = $_POST['fechaInicio'];
    $fechaFin = $_POST['fechaFin'];
    $sprint = $_POST['sprint'];
    $descripcion = $_POST['descripcion'];

    $resultado = crearEvento($conexion, $nombre, $fechaInicio, $fechaFin, $sprint, $descripcion);

    header('Content-Type: application/json');
    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al crear el evento']);
    }
    exit;
}
?>