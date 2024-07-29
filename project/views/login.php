<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scape Rooms La Puerta</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <img src="../images/logo.png" class="logo-img" alt="Logo de la pagina">
        </div>

        <div class="login-forms text-center">
            <div id="login-container" class="">
                <h2 class="text-light">Inicio de Sesión</h2>
                <form id="login-form" class="mt-4">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg" id="login-id" placeholder="Número de identificación" required>
                    </div>
                    <div class="form-group">
                        <input type="password" class="form-control form-control-lg" id="login-password" placeholder="Contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg">Ingresar</button>
                    <a href="/project" class="text-info text-decoration-none"><button type="button" class="btn btn-secondary btn-block btn-lg mt-2">Volver</button></a>
                </form>
            </div>  
        </div>
    </div>
</body>
</html>
