<?php
session_start();
require_once('../database.php');
require_once('../controllers/monitoreoController.php');

$response = ['success' => false, 'message' => '', 'nuevoPuntaje' => 0];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challengeId']) && isset($data['status'])) {
    $challengeId = $data['challengeId'];
    $status = $data['status'];

    $controller = new MonitoreoController($conexion);

    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $_SESSION['pending_challenges'][$challengeId]['estado'] = $status;
        $_SESSION['pending_challenges'][$challengeId]['calificado'] = true;

        if ($status === 'aprobado') {
            if ($controller->aprobarDesafio($challengeId)) {
                $jugadorId = $_SESSION['pending_challenges'][$challengeId]['jugadorId'];
                $nuevoPuntaje = $controller->getJugadorPuntaje($jugadorId);
                error_log("Nuevo puntaje para jugador $jugadorId: $nuevoPuntaje");
                $response['nuevoPuntaje'] = $nuevoPuntaje;
                $response['success'] = true;
                $response['message'] = 'Desafío aprobado y puntaje actualizado';
            } else {
                error_log("Fallo al aprobar desafío: $challengeId");
                $response['message'] = 'Error al actualizar el puntaje';
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Desafío reprobado';
        }
    } else {
        $response['message'] = 'Desafío no encontrado';
    }
} else {
    $response['message'] = 'Datos incompletos';
}

echo json_encode($response);
