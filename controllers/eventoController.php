<?php
require_once '../database.php';
require_once '../models/eventoModel.php';

custom_session_start('player_session');
$evento_model = new EventoModel($conexion);

// Obtener datos del evento
if (isset($_SESSION['player_evento_id'])) {
    $eventoId = $_SESSION['player_evento_id'];
    $query = "SELECT * FROM eventos WHERE id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param('i', $eventoId);
    $stmt->execute();
    $evento = $stmt->get_result()->fetch_assoc();

    if ($evento) {
        $_SESSION['evento_actual'] = [
            'id' => $evento['id'],
            'nombre' => $evento['nombre'],
            'descripcion' => $evento['descripcion']
        ];
    }
}

$tematica = $evento_model->getTematica($_SESSION['player_evento_id']);
$juegos = $evento_model->getJuegos($_SESSION['player_evento_id']);
$jugadores = $evento_model->getJugadores($_SESSION['player_evento_id']);
$_SESSION['jugadores'] = $jugadores;

// Obtener información del jugador actual
if (isset($_SESSION['player_id'])) {
    $jugador_actual = $evento_model->getJugadorActual($_SESSION['player_id']);
    $_SESSION['jugador_actual'] = $jugador_actual;
} else {
    $_SESSION['jugador_actual'] = null;
}

// Agregar log para depuración
error_log("Datos del evento: " . print_r($evento ?? 'No hay datos del evento', true));
error_log("Datos de sesión: " . print_r($_SESSION, true));
?>
