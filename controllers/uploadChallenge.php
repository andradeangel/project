<?php
custom_session_start('player_session');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$response = ['success' => false, 'challengeId' => null, 'message' => ''];

try {
    $input = file_get_contents('php://input');
    error_log("Input recibido: " . substr($input, 0, 100) . "..."); // Loguea los primeros 100 caracteres del input

    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Error al decodificar JSON: ' . json_last_error_msg());
    }

    error_log("Datos decodificados: " . print_r($data, true));

    if (isset($data['challenge']) && isset($data['gameType'])) {
        if (!isset($_SESSION['pending_challenges'])) {
            $_SESSION['pending_challenges'] = [];
        }
        
        $challengeId = uniqid();
        $_SESSION['pending_challenges'][$challengeId] = [
            'challenge' => substr($data['challenge'], 0, 100) . "...", // Guardamos solo los primeros 100 caracteres para el log
            'gameType' => $data['gameType'],
            'eventoId' => $_SESSION['player_evento_id'] ?? 'No definido',
            'jugadorId' => $_SESSION['player_id'] ?? 'No definido',
            'jugadorNombre' => $_SESSION['jugador_actual']['nombres'] ?? 'No definido',
            'eventoNombre' => $_SESSION['player_evento_nombre'] ?? 'No definido',
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
    error_log("Error en uploadChallenge.php: " . $e->getMessage());
}

error_log("Respuesta enviada: " . json_encode($response));
echo json_encode($response);
exit;
