<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

$response = ['success' => false, 'challengeId' => null, 'message' => ''];

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['challenge']) && isset($data['gameType'])) {
        if (!isset($_SESSION['pending_challenges'])) {
            $_SESSION['pending_challenges'] = [];
        }
        
        $challengeId = uniqid();
        $_SESSION['pending_challenges'][$challengeId] = [
            'challenge' => $data['challenge'],
            'gameType' => $data['gameType'],
            'eventoId' => $_SESSION['evento_id'] ?? 'No definido',
            'jugadorId' => $_SESSION['user_id'] ?? 'No definido',
            'jugadorNombre' => $_SESSION['user_name'] ?? 'No definido',
            'eventoNombre' => $_SESSION['evento_nombre'] ?? 'No definido',
            'gameId' => $_SESSION['current_game_id'] ?? 'No definido',
            'gameDescription' => $_SESSION['current_game_description'] ?? 'No definido',
            'estado' => 'pendiente'
        ];
        
        $response['success'] = true;
        $response['challengeId'] = $challengeId;
        $response['message'] = 'Desafío guardado correctamente';
    } else {
        $response['message'] = 'Datos de desafío incompletos';
    }
} catch (Exception $e) {
    $response['message'] = 'Error en el servidor: ' . $e->getMessage();
}

echo json_encode($response);
