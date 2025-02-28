<?php
require_once("database.php");
require_once("utils/logger.php");
include("controllers/inicioController.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andrade Ángel">  
    <title>Escape Rooms La Puerta</title>
    <link rel="icon" href="images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <a href=""><img src="images/logo.png" class="logo-img" alt="Logo de la pagina"></a>
        </div>

        <div class="login-forms text-center">
            <div class="player-login text-center" id="invitation-code-container">
                <h2 class="text-light">Acceso a Escape Room</h2>
                <?php
                    // Mostrar mensajes de error aquí
                    if (isset($_SESSION['error_message'])) {
                        echo "<div class='alert alert-danger mb-3'>" . $_SESSION['error_message'] . "</div>";
                        unset($_SESSION['error_message']);
                    }
                ?>
                <form class="mt-4" id="invitation-code-form" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg text-center" name="codigoAccess" id="invitation-code" placeholder="Ingresa tu código de acceso" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="btnAccess">Ingresar</button>
                </form>
                <p class="text-light mt-3" id="login-link">¿Eres Administrador o Game Master? <a href="/views/login.php" class="text-info" >Inicia sesión</a></p>
            </div>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $codigoAcceso = $_POST['codigoAccess'] ?? '';
        
        log_activity("Intento de ingreso a evento con código: " . $codigoAcceso);
        
        // ... resto del código de validación ...
    }
    ?>
</body>
</html>
