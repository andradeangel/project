<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scape Rooms La Puerta</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./assets/css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <img src="assets/images/logo.png" class="logo-img" alt="Logo de la pagina">
        </div>

        <div class="login-forms text-center">
            <div class="player-login text-center" id="invitation-code-container">
                <h2 class="text-light">Acceso a Escape Room</h2>
                <form class="mt-4" id="invitation-code-form" method="POST">
                    <?php
                        include("../src/controllers/EventoController.php");
                        include("../src/config/database.php");
                    ?>
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg text-center" name="codigoAccess" id="invitation-code" placeholder="Ingresa tu código de acceso" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="btnAccess" >Ingresar</button>
                </form>
                <p class="text-light mt-3" id="login-link">¿Eres Administrador o Game Master? <a href="#" class="text-info" onclick="showLoginForm()">Inicia sesión</a></p>
            </div>

            <div id="personal-data-container" class="d-none">
                <h2 class="text-light">Datos del jugador</h2>
                <form class="mt-3" id="personal-data-form" method="get" action="">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg" id="name" placeholder="Nombre" required>
                    </div>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-lg" id="age" placeholder="Edad" required>
                    </div>
                    <div class="form-group">
                        <select class="form-control form-control-lg" id="gender">
                            <option value="male">Masculino</option>
                            <option value="female">Femenino</option>
                            <option value="other">Otro</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">¡Empezar a Jugar!</button>
                    <button type="button" class="btn btn-secondary btn-block btn-lg mt-2" onclick="goBack()">Volver</button>
                </form>
            </div>

            <div id="login-container" class="d-none">
                <h2 class="text-light">Inicio de Sesión</h2>
                <form id="login-form" class="mt-4">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg" id="login-id" placeholder="Número de identificación" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control form-control-lg" id="login-password" placeholder="Contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Ingresar</button>
                    <button type="button" class="btn btn-secondary btn-block btn-lg mt-2" onclick="goBack()">Volver</button>
                </form>
            </div>  
        </div>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
        <script src="./assets/js/main.js"></script>
    </div>
</body>
</html>
