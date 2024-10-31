<?php
require_once('../database.php');
require_once('../controllers/monitoreoController.php');
custom_session_start('admin_session');

$response = ['success' => false, 'message' => '', 'nuevoPuntaje' => 0];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challengeId']) && isset($data['status'])) {
    $challengeId = $data['challengeId'];
    $status = $data['status'];

    error_log("Intentando calificar desafío: " . $challengeId);
    error_log("Estado actual de la sesión: " . print_r($_SESSION, true));

    $controller = new MonitoreoController($conexion);

    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $_SESSION['pending_challenges'][$challengeId]['estado'] = $status;
        $_SESSION['pending_challenges'][$challengeId]['calificado'] = true;

        if ($status === 'aprobado') {
            if ($controller->aprobarDesafio($challengeId)) {
                $jugadorId = $_SESSION['pending_challenges'][$challengeId]['jugadorId'];
                
                // Actualizar juego_actual además del puntaje
                $sql = "UPDATE jugadores SET juego_actual = juego_actual + 1 WHERE id = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("i", $jugadorId);
                $stmt->execute();
                
                $nuevoPuntaje = $controller->getJugadorPuntaje($jugadorId);
                $response['nuevoPuntaje'] = $nuevoPuntaje;
                $response['success'] = true;
                $response['message'] = 'Desafío aprobado y puntaje actualizado';
            } else {
                $response['message'] = 'Error al actualizar el puntaje';
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Desafío reprobado';
        }
    } else {
        error_log("Desafío no encontrado en la sesión. Challenge ID: " . $challengeId);
        $response['message'] = 'Desafío no encontrado';
    }
}

echo json_encode($response);
