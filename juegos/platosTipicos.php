<?php
require_once('../database.php');
custom_session_start('player_session');

error_log("SESSION en platosTipicos.php: " . print_r($_SESSION, true));

if (!isset($_SESSION['jugador_actual']) || !isset($_SESSION['evento_actual'])) {
    error_log("Redirección a evento.php por falta de datos de sesión");
    header('Location: ../views/evento.php');
    exit;
}

$juego_id = $_GET['juego_id'] ?? null;
$descripcion = $_GET['descripcion'] ?? 'Descripción no disponible';
$_SESSION['current_game_id'] = $juego_id;
$_SESSION['current_game_description'] = $descripcion;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platos Típicos Paceños</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-image: url('https://abi.bo/images/Noticias/Sociedad/jul-22/thimpu.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Press Start 2P', cursive;
            color: #fff;    
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border: 3px solid #00ff00;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            width: 90%;
            max-width: 500px;
            animation: glow 2s infinite alternate;
        }
        @keyframes glow {
            from { box-shadow: 0 0 20px rgba(0, 255, 0, 0.5); }
            to { box-shadow: 0 0 30px rgba(0, 255, 0, 0.8); }
        }
        .card h1 {
            color: #00ff00;
            font-size: 1.5rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .card p {
            color: #fff;
            font-size: 0.8rem;
            margin-bottom: 20px;
            text-align: center;
        }
        .preview-container {
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .preview-container video {
            width: 100%;
            max-width: 300px;
            height: auto;
            margin: 10px 0;
            border: 2px solid #00ff00;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }
        .custom-file-upload, #submitBtn {
            background-color: #0a0;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            transition: all 0.3s;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.7rem;
            display: inline-block;
            margin: 20px auto;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }
        .custom-file-upload:hover {
            background-color: #0f0;
        }
        #submitBtn {
            display: none;
            background-color: #0f0;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.9);
        }
        #submitBtn:hover{
            background-color: #0f0;
            transform: scale(1.05);
        }
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        .overlay.show {
            display: flex;
        }
        .overlay .card {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border: 3px solid #00ff00;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            width: 90%;
            max-width: 500px;
            animation: glow 2s infinite alternate;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        .overlay .card .message {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1rem;
            color: #fff;
        }  
        .overlay .card .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #00ff00;
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 2s linear infinite;
            margin-top: 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }   
        .input-file-container {
            position: relative;
            display: inline-block;
        }
        
        input[type="file"] {
            display: none;
        }
        
        .custom-file-upload {
            display: inline;
            padding: 12px 5px;
            cursor: pointer;
            color: black;
            border-radius: 5px;
            border: none;
        }
        .back-btn {
            background-color: rgba(80, 80, 80, 0.9);
            border: none;
            color: #ddd;
            padding: 12px 5px;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
            border-radius: 5px;
            cursor: pointer;
            font-size: 10px;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        .back-btn:hover {
            background-color: rgba(50, 50, 50, 0.9);
        }
        .custom-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: rgba(0, 0, 0, 0.9);
            padding: 20px;
            border-radius: 10px;
            border: 2px solid #00ff00;
            color: #fff;
            text-align: center;
            z-index: 2000;
            min-width: 300px;
            font-family: 'Press Start 2P', cursive;
            animation: glow 2s infinite alternate;
        }

        .custom-modal h3 {
            color: #00ff00;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .custom-modal p {
            margin: 10px 0;
            font-size: 0.8rem;
            line-height: 1.5;
        }

        .custom-modal button {
            background-color: #0a0;
            color: black;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.8rem;
            transition: all 0.3s ease;
        }

        .custom-modal button:hover {
            background-color: #0f0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reto: Platos Típicos Paceños</h1>
        <p class="card-text"><?php echo htmlspecialchars($descripcion); ?></p>
        <div class="preview-container">
            <video id="preview" style="display: none;" controls></video>
            <div class="input-file-container">
                <label for="fileInput" class="custom-file-upload">Subir Video</label>
                <input type="file" id="fileInput" accept="video/*" max="40000000" onchange="validateFileSize(this)">
                <button onclick="window.location.href='../views/evento.php'" class="back-btn">Volver</button>
            </div>
        </div>
        <button id="submitBtn" style="display: none;">Enviar</button>
    </div>

    <div class="overlay" id="overlay">
        <div class="card">
            <div class="message">Espere un momento, su video está siendo evaluada por el Game Master :)</div>
            <div class="loader"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let esperandoCalificacion = false;
            let challengeId = null;

            const videoInput = document.getElementById('fileInput');
            const preview = document.getElementById('preview');
            const submitBtn = document.getElementById('submitBtn');

            videoInput.addEventListener('change', function() {
                const file = this.files[0];
                const maxSize = 40 * 1024 * 1024; // 40MB en bytes

                if (file.size > maxSize) {
                    showCustomMessage('Error', '<p>El video es demasiado grande.</p><p>Por favor, sube un video de menos de 40MB.</p>');
                    this.value = ''; // Limpiar el input
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(event) {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                    submitBtn.style.display = 'block';
                };
                reader.readAsDataURL(file);
            });

            submitBtn.addEventListener('click', function() {
                if (esperandoCalificacion) {
                    alert('Ya has enviado un video. Por favor, espera la calificación.');
                    return;
                }

                const videoFile = document.querySelector('input[type="file"]').files[0];
                if (videoFile.size > 40 * 1024 * 1024) { // 40MB en bytes
                    alert('El video no debe superar los 40MB');
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const requestData = { 
                        challenge: e.target.result,
                        gameType: 'video',
                        juego_id: <?php echo json_encode($juego_id); ?>,
                        jugador_id: <?php echo json_encode($_SESSION['jugador_actual']['id']); ?>
                    };

                    fetch('../controllers/uploadChallenge.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.text())
                .then(text => {
                    try {
                        if (!text.trim()) {
                            throw new Error('Respuesta vacía del servidor');
                        }
                        return JSON.parse(text);
                    } catch (e) {
                        throw new Error(`Error al parsear JSON: ${e.message}\nRespuesta del servidor: ${text}`);
                    }
                })
                .then(data => {
                    if (data.success) {
                        esperandoCalificacion = true;
                        challengeId = data.challengeId;
                        showOverlay('Espere unos segundos, su video está siendo evaluado por el Game Master :)');
                        checkCalificacion();
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showCustomMessage('Error', '<p>Error al enviar el video.</p><p>Por favor, intenta nuevamente.</p>');
                });
            };
            reader.readAsDataURL(videoFile);
        });

        function showOverlay(message) {
            const overlay = document.getElementById('overlay');
            if (overlay) {
                overlay.style.display = 'flex';
            }
        }

        function hideOverlay() {
            const overlay = document.getElementById('overlay');
            if (overlay) {
                overlay.style.display = 'none';
            }
        }

        function checkCalificacion() {
            if (!esperandoCalificacion || !challengeId) return;

            fetch('../controllers/checkCalificacion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ challengeId: challengeId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.calificado) {
                    esperandoCalificacion = false;
                    hideOverlay();
                    if (data.status === 'aprobado') {
                        let mensaje = `
                            <p>¡Has completado el desafío exitosamente!</p>
                            <p>Puntos ganados: +1</p>
                            <p>Puntaje total: ${data.nuevoPuntaje}</p>
                        `;
                        showCustomMessage('¡Felicitaciones!', mensaje, () => {
                            window.location.href = '../views/evento.php';
                        });
                    } else {
                        showCustomMessage('Resultado', '<p>Tu desafío ha sido reprobado.</p><p>Continúa con el siguiente reto.</p>', () => {
                            window.location.href = '../views/evento.php';
                        });
                    }
                } else {
                    setTimeout(checkCalificacion, 2000);
                }
            })
            .catch(error => {
                console.error("Error al verificar calificación:", error);
                setTimeout(checkCalificacion, 2000);
            });
        }

        function showCustomMessage(title, message, callback) {
            // Remover modal anterior si existe
            const existingModal = document.querySelector('.custom-modal');
            if (existingModal) {
                existingModal.remove();
            }

            const modal = document.createElement('div');
            modal.className = 'custom-modal';
            modal.innerHTML = `
                <h3>${title}</h3>
                <div>${message}</div>
                <button onclick="closeCustomModal(this)" class="">Aceptar</button>
            `;
            document.body.appendChild(modal);

            window.closeCustomModal = function(button) {
                button.parentElement.remove();
                if (callback) callback();
            };
        }
    });

        function validateFileSize(input) {
            if (input.files[0].size > 40 * 1024 * 1024) {
                showCustomMessage('Error', '<p>El archivo es demasiado grande.</p><p>El tamaño máximo es 40MB.</p>');
                input.value = '';
                return false;
            }
            return true;
        }
    </script>
</body>
</html>