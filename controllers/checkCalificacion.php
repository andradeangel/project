<?php
require_once('../database.php');
custom_session_start('player_session');

header('Content-Type: application/json');
$response = ['calificado' => false, 'status' => null, 'nuevoPuntaje' => null];

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (!isset($data['challengeId'])) {
        throw new Exception('ID del desafío no proporcionado');
    }

    $challengeId = $data['challengeId'];
    
    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $challenge = $_SESSION['pending_challenges'][$challengeId];
        if (isset($challenge['calificado']) && $challenge['calificado']) {
            $response['calificado'] = true;
            $response['status'] = $challenge['estado'];
            if (isset($challenge['nuevoPuntaje'])) {
                $response['nuevoPuntaje'] = $challenge['nuevoPuntaje'];
            }
            // Eliminamos el desafío de la sesión
            unset($_SESSION['pending_challenges'][$challengeId]);
        }
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

echo json_encode($response);
exit;
