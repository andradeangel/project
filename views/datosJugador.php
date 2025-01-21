<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scape Rooms La Puerta</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <a href="/"><img src="../images/logo.png" class="logo-img" alt="Logo de la pagina"></a>
        </div>

        <div class="login-forms text-center">
            <div id="personal-data-container" class="">
                <h2 class="text-light">Datos del jugador</h2>
                <?php
                    include("../controllers/datosJugadorController.php"); 
                ?>
                <form class="mt-3" id="personal-data-form" method="POST" action="">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg" id="name" name="nombre" placeholder="Nombre" required>
                    </div>
                    <div class="form-group">
                        <input type="number" class="form-control form-control-lg" id="age" name="edad" placeholder="Edad" required min="15" max="60">
                    </div>
                    <div class="form-group">
                        <select class="form-control form-control-lg" id="gender" name="genero">
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <button type="submit" name="btnDatosJugador" class="btn btn-primary btn-block btn-lg">¡Empezar a Jugar!</button>
                    <button type="button" onclick="goBack()" class="btn btn-secondary btn-block btn-lg mt-2">Volver</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = '/';
        }
    </script>
</body>
</html>