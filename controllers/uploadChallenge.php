<?php
error_log("Iniciando uploadChallenge.php");
require_once('../database.php');
require_once('monitoreoController.php');
custom_session_start('player_session');

// Establecer los headers
header('Content-Type: application/json');
$response = ['success' => false, 'challengeId' => null, 'message' => ''];

try {
    // Obtener y decodificar los datos
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (isset($data['challenge']) && isset($data['gameType'])) {
        if (!isset($_SESSION['pending_challenges'])) {
            $_SESSION['pending_challenges'] = [];
        }
        
        // Verificar que tenemos todos los datos de sesión necesarios
        error_log("Datos de sesión: " . print_r($_SESSION, true));
        
        $challengeId = uniqid();
        $_SESSION['pending_challenges'][$challengeId] = [
            'challenge' => $data['challenge'],
            'gameType' => $data['gameType'],
            'eventoId' => $_SESSION['evento_actual']['id'],
            'jugadorId' => $_SESSION['jugador_actual']['id'],
            'jugadorNombre' => $_SESSION['jugador_actual']['nombres'],
            'eventoNombre' => $_SESSION['evento_actual']['nombre'],
            'gameId' => $data['juego_id'],
            'gameDescription' => $_SESSION['current_game_description'],
            'tematica' => $_SESSION['evento_actual']['tematica'],
            'estado' => 'pendiente'
        ];
        
        // Log para depuración
        error_log("Nuevo desafío guardado: " . print_r($_SESSION['pending_challenges'][$challengeId], true));
        
        // Guardar también en la sesión del administrador
        session_write_close(); // Cerrar la sesión actual
        custom_session_start('admin_session'); // Abrir sesión de admin

        if (!isset($_SESSION['pending_challenges'])) {
            $_SESSION['pending_challenges'] = [];
        }

        $_SESSION['pending_challenges'][$challengeId] = [
            'challenge' => $data['challenge'],
            'gameType' => $data['gameType'],
            'eventoId' => $_SESSION['evento_actual']['id'],
            'jugadorId' => $_SESSION['jugador_actual']['id'],
            'jugadorNombre' => $_SESSION['jugador_actual']['nombres'],
            'eventoNombre' => $_SESSION['evento_actual']['nombre'],
            'gameId' => $data['juego_id'],
            'gameDescription' => $_SESSION['current_game_description'],
            'tematica' => $_SESSION['evento_actual']['tematica'],
            'estado' => 'pendiente'
        ];

        session_write_close(); // Cerrar sesión de admin
        custom_session_start('player_session'); // Volver a la sesión del jugador
        
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
// Enviar la respuesta
echo json_encode($response);
exit;

