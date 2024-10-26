<?php
require_once '../database.php';
require_once '../models/eventoModel.php';

custom_session_start('player_session');
$evento_model = new EventoModel($conexion);
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
?>
