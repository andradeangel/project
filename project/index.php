<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scape Rooms La Puerta</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <img src="images/logo.png" class="logo-img" alt="Logo de la pagina">
        </div>

        <div class="login-forms text-center">
            <div class="player-login text-center" id="invitation-code-container">
                <h2 class="text-light">Acceso a Escape Room</h2>
                <?php
                    include("database.php");
                    include("controllers/inicioController.php");
                ?>
                <form class="mt-4" id="invitation-code-form" method="POST">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg text-center" name="codigoAccess" id="invitation-code" placeholder="Ingresa tu código de acceso" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="btnAccess">Ingresar</button>
                </form>
                <p class="text-light mt-3" id="login-link">¿Eres Administrador o Game Master? <a href="/project/views/login.php" class="text-info" >Inicia sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>
