<?php
    require_once '../database.php';
    require_once '../models/eventoModel.php';

    session_start();
    $evento_model = new EventoModel($conexion);
    $tematica = $evento_model->getTematica($_SESSION['evento_id']);
    $juegos = $evento_model->getJuegos($_SESSION['evento_id']);
    $jugadores = $evento_model->getJugadores($_SESSION['evento_id']);
    $_SESSION['jugadores'] = $jugadores;
?>