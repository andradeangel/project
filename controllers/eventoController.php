<?php
require_once '../database.php';
require_once '../models/eventoModel.php';

session_start();
$evento_model = new EventoModel($conexion);
$tematica = $evento_model->getTematica($_SESSION['evento_id']);
$juegos = $evento_model->getJuegos($_SESSION['evento_id']);
$jugadores = $evento_model->getJugadores($_SESSION['evento_id']);
$_SESSION['jugadores'] = $jugadores;

// Obtener información del jugador actual
if (isset($_SESSION['user_id'])) {
    $jugador_actual = $evento_model->getJugadorActual($_SESSION['user_id']);
    $_SESSION['jugador_actual'] = $jugador_actual;
} else {
    $_SESSION['jugador_actual'] = null;
}
?>