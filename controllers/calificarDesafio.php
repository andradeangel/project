<?php
session_start();
$response = ['success' => false];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challengeId']) && isset($data['status'])) {
    $challengeId = $data['challengeId'];
    $status = $data['status'];

    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $_SESSION['pending_challenges'][$challengeId]['estado'] = $status;
        $response['success'] = true;

        // Aquí podrías agregar lógica adicional, como actualizar el puntaje del jugador
        // basado en si el desafío fue aprobado o rechazado
    }
}

echo json_encode($response);