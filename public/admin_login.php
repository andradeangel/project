<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    // Lógica de autenticación para el administrador y game master

    echo "Correo: " . htmlspecialchars($email) . "<br>";
    echo "Contraseña: " . htmlspecialchars($password);
}
?>
