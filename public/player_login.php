<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $access_code = $_POST['access_code'];
    // Lógica de autenticación para el jugador

    echo "Código de Acceso: " . htmlspecialchars($access_code);
}
?>
