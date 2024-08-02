<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Andrade Ángel">
    <title>Evento</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <img src="../images/logo.png" class="logo-img" alt="Logo de la pagina">
        </div>

        <div class="evento-forms text-center">
            <div id="evento-container" class="">
                <h2 class="text-light">Nombre del Evento</h2>
                <form id="evento-form" class="mt-4" method="POST" action="">
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="">Empezar</button>
                    <button type="button" onclick="" class="btn btn-light btn-block btn-lg mt-2">Mostrar Tabla de posiciones</button>
                    <button type="button" onclick="goBack()" class="btn btn-secondary btn-block btn-lg mt-2">Abandonar juego</button>
                </form>
            </div>  
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
