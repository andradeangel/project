<?php
require_once("../database.php");
custom_session_start('admin_session');

// Verificar si ya existe una sesión activa
if (isset($_SESSION['admin_id'])) {
    // Redirigir al panel de control si ya hay una sesión
    header("Location: eventos.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scape Rooms La Puerta</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .password-container {
            position: relative;
        }
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #777;
        }
    </style>
</head>
<body>
    <div class="container d-flex flex-column justify-content-center align-items-center vh-100">
        <div class="logo">
            <a href="/"><img src="../images/logo.png" class="logo-img" alt="Logo de la pagina"></a>
        </div>

        <div class="login-forms text-center">
            <div id="login-container" class="">
                <h2 class="text-light">Inicio de Sesión</h2>
                <?php
                if(isset($_SESSION['error'])) {
                    echo "<div class='alert alert-danger mb-3'>" . $_SESSION['error'] . "</div>";
                    if($_SESSION['login_attempts'] >= 3) {
                        echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                var inputs = document.querySelectorAll('input');
                                var submitBtn = document.querySelector('button[name=\"btnLogin\"]');
                                inputs.forEach(input => input.disabled = true);
                                submitBtn.disabled = true;
                                
                                var timeLeft = " . (isset($_SESSION['last_attempt_time']) ? (30 - (time() - $_SESSION['last_attempt_time'])) : 30) . ";
                                var timer = setInterval(function() {
                                    timeLeft--;
                                    if(timeLeft <= 0) {
                                        clearInterval(timer);
                                        inputs.forEach(input => input.disabled = false);
                                        submitBtn.disabled = false;
                                        document.querySelector('.alert-danger').style.display = 'none';
                                    } else {
                                        document.querySelector('.alert-danger').textContent = 'Espere ' + timeLeft + ' segundos para volver a intentarlo';
                                    }
                                }, 1000);
                            });
                        </script>";
                    }
                    unset($_SESSION['error']);
                }
                ?>
                <form id="login-form" class="mt-4" method="POST" action="../controllers/loginController.php">
                    <div class="form-group">
                        <input type="text" class="form-control form-control-lg" name="id" id="login-id" placeholder="Número de identificación" required>
                    </div>
                    <div class="form-group password-container">
                        <input type="password" class="form-control form-control-lg" name="password" id="login-password" placeholder="Contraseña" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" name="btnLogin" style="background-color: none; border-color: #ff2424;">Ingresar</button>
                    <button type="button" onclick="goBack()" class="btn btn-secondary btn-block btn-lg mt-2">Volver</button>
                </form>
            </div>  
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = '/';
        }

        function togglePassword() {
            const passwordInput = document.getElementById('login-password');
            const toggleIcon = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>