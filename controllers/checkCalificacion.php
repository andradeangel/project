<?php
session_start();
$response = ['calificado' => false, 'status' => null];

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['challengeId'])) {
    $challengeId = $data['challengeId'];
    if (isset($_SESSION['pending_challenges'][$challengeId])) {
        $challenge = $_SESSION['pending_challenges'][$challengeId];
        if (isset($challenge['calificado']) && $challenge['calificado']) {
            $response['calificado'] = true;
            $response['status'] = $challenge['estado'];
            // Ahora sí eliminamos el desafío de la sesión
            unset($_SESSION['pending_challenges'][$challengeId]);
        }
    }
}

echo json_encode($response);
