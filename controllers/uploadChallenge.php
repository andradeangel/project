<?php
session_start();
$response = ['success' => false];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challenge']) && isset($data['gameType'])) {
    if (!isset($_SESSION['pending_challenges'])) {
        $_SESSION['pending_challenges'] = [];
    }
    
    $challengeId = uniqid();
    $_SESSION['pending_challenges'][$challengeId] = [
        'challenge' => $data['challenge'],
        'gameType' => $data['gameType'],
        'eventoId' => $_SESSION['evento_id'],
        'jugadorId' => $_SESSION['user_id'],
        'jugadorNombre' => $_SESSION['user_name'],
        'eventoNombre' => $_SESSION['evento_nombre'],
        'estado' => 'pendiente'
    ];
    
    $response['success'] = true;
}

echo json_encode($response);