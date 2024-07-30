<?php
    session_start();
    if(!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
?>
<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Panel de Control</title>
        <link rel="icon" href="../images/ico.png">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="../css/styles.css">
    </head>

    <body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <a href=""><img src="../images/logo.png" class="logo-img" alt="Logo de la pagina"></a>
        </div>

        
    </div>
    </body>
</html>