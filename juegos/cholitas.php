<?php
require_once('../database.php');
custom_session_start('player_session');

error_log("SESSION en cholitas.php: " . print_r($_SESSION, true));

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
    <title>Cholitas en el Mercado de las Brujas</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-image: url('https://www.hotelpresidente.com.bo/wp-content/uploads/2024/02/WhatsApp-Image-2024-02-16-at-19.21.24.jpeg');
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
            border: 3px solid #ffd700;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            width: 90%;
            max-width: 500px;
            animation: glow 2s infinite alternate;
        }
        @keyframes glow {
            from { box-shadow: 0 0 20px rgba(255, 215, 0, 0.5); }
            to { box-shadow: 0 0 30px rgba(255, 215, 0, 0.8); }
        }
        .card h1 {
            color: #ffd700;
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
        .preview-container img {
            width: 100%;
            max-width: 300px;
            height: auto;
            margin: 10px 0;
            border: 2px solid #ffd700;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            transition: transform 0.5s;
        }
        .preview-container img:hover {
            transform: scale(1.05);
        }
        .custom-file-upload, #submitBtn {
            background-color: #ffd700;
            padding: 12px 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            transition: all 0.3s;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.7rem;
            display: inline;
            margin: 0 auto;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }
        .custom-file-upload{
            margin: 10px;
            color: #000;
        }
        .custom-file-upload:hover{
            background-color: #ffec00;
        }
        #submitBtn:hover{
            background-color: #1f0;
        }
        #submitBtn{
            background-color: #0c0;
            box-shadow: 0 0 10px rgba(0, 215, 100, 0.5);
        }
        .custom-file-upload:active, #submitBtn:active {
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
            transform: scale(0.95);
        }
        #fileInput {
            display: none;
        }
        .input-file-container {
            position: relative;
            display: inline-block;
            padding: 10px;
        }
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .overlay-content {
            color: #fff;
            font-size: 1rem;
            padding: 20px;
            background-color: rgba(0, 0, 0, 0.6);
            border: 3px solid #ffd700;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.5);
            max-width: 80%;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .loader {
            border: 16px solid #333;
            border-top: 16px solid #ffd700;
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
        .back-btn {
            background-color: rgba(80, 80, 80, 0.9);
            border: none;
            color: #ddd;
            padding: 7px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 10px;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            margin: 10px;
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
            border: 2px solid #ffd700;
            color: #fff;
            text-align: center;
            z-index: 2000;
            min-width: 300px;
            font-family: 'Press Start 2P', cursive;
            animation: glow 2s infinite alternate;
        }
        .custom-modal h3 {
            color: #ffd700;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }
        .custom-modal p {
            margin: 10px 0;
            font-size: 0.8rem;
            line-height: 1.5;
        }
        .custom-modal button {
            background-color: #ffd700;
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
            background-color: #ffec00;
        }
        .camera-btn {
            display: inherit;
            background-color: #ffd700;
            border: 0;
            color: #000;
            padding: 12px 5px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 10px;
            transition: all 1s ease;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            z-index: 1000;
            margin: 10px;
            align-items: center;
        }
        
        .camera-btn:hover {
            background-color: #ffec00;
        }

        #camera-preview {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #000;
            z-index: 2000;
        }

        #captureBtn {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2001;
            background-color: #ffd700;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            border: none;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
        }

        .input-file-container{
            display: flex;
            padding:0;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reto: Cholitas en la Calle de las Paraguas 📷</h1>
        <p><?php echo htmlspecialchars($descripcion); ?></p>
        <div class="preview-container">
            <img id="preview" src="" alt="'Preview de foto">
            <div class="input-file-container">
                <label for="fileInput" class="custom-file-upload">
                    <i class="fas fa-upload"></i> Subir foto
                </label>
                <input type="file" id="fileInput" accept="image/*">
                <button class="camera-btn" id="cameraBtn">
                    <i class="fas fa-camera" style="margin-right: 10px;"></i>Abrir 
                </button>
                <button onclick="window.location.href='../views/evento.php'" class="back-btn">Volver</button>
            </div>
        </div>
        <button type="button" id="submitBtn" style="display: none;" class="submit">Enviar</button>
    </div>

    <!-- Cámara en pantalla completa -->
    <div id="cameraContainer" style="display: none;">
        <video id="camera-preview" autoplay playsinline></video>
        <button id="captureBtn">
            <i class="fas fa-camera"></i>
        </button>
    </div>

    <div id="overlay" class="overlay">
        <div class="overlay-content">
            <p id="overlayMessage">Espere unos segundos, su foto está siendo evaluada por el Game Master :)</p>
            <div class="loader"></div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let esperandoCalificacion = false;
            let challengeId = null;
            let stream = null;

            // Función para iniciar la cámara
            async function startCamera() {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: 'environment',
                            width: { ideal: window.innerWidth },
                            height: { ideal: window.innerHeight }
                        }, 
                        audio: false 
                    });
                    const videoElement = document.getElementById('camera-preview');
                    const cameraContainer = document.getElementById('cameraContainer');
                    
                    videoElement.srcObject = stream;
                    cameraContainer.style.display = 'block';
                    videoElement.style.display = 'block';
                    document.getElementById('captureBtn').style.display = 'block';
                } catch (err) {
                    console.error('Error al acceder a la cámara:', err);
                    showCustomMessage('Error', 'No se pudo acceder a la cámara. Por favor, verifica los permisos.');
                }
            }

            // Función para detener la cámara
            function stopCamera() {
                if (stream) {
                    stream.getTracks().forEach(track => track.stop());
                    document.getElementById('cameraContainer').style.display = 'none';
                    document.getElementById('camera-preview').style.display = 'none';
                }
            }

            // Botón para activar la cámara
            document.getElementById('cameraBtn').addEventListener('click', startCamera);

            // Botón para capturar foto
            document.getElementById('captureBtn').addEventListener('click', function() {
                const video = document.getElementById('camera-preview');
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                
                document.getElementById('preview').src = canvas.toDataURL('image/jpeg');
                document.getElementById('submitBtn').style.display = 'block';
                stopCamera();
            });

            document.getElementById('fileInput').addEventListener('change', function() {
                const file = this.files[0];
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.getElementById('preview').src = event.target.result;
                    document.getElementById('submitBtn').style.display = 'block';
                };
                reader.readAsDataURL(file);
            });

            document.getElementById('submitBtn').addEventListener('click', function() {
                console.log('Iniciando envío de foto...');
                if (esperandoCalificacion) {
                    console.log('Ya esperando calificación, abortando...');
                    showCustomMessage('Error', 'Ya has enviado una foto. Por favor, espera la calificación.');
                    return;
                }

                const challengeData = document.getElementById('preview').src;
                console.log('Datos de la foto preparados, enviando a servidor...');

                const requestData = { 
                    challenge: challengeData,
                    gameType: 'photo',
                    juego_id: <?php echo json_encode($juego_id); ?>,
                    jugador_id: <?php echo json_encode($_SESSION['jugador_actual']['id']); ?>
                };

                console.log('Datos a enviar:', requestData);

                fetch('../controllers/uploadChallenge.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => {
                    return response.text().then(text => {
                        try {
                            if (!text.trim()) {
                                throw new Error('Respuesta vacía del servidor');
                            }
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error(`Error al parsear JSON: ${e.message}\nRespuesta del servidor: ${text}`);
                        }
                    });
                })
                .then(data => {
                    console.log('Datos parseados:', data);
                    if (data.success) {
                        esperandoCalificacion = true;
                        challengeId = data.challengeId;
                        showOverlay('Espere unos segundos, su foto está siendo evaluada por el Game Master :)');
                        checkCalificacion();
                    } else {
                        hideOverlay();
                        console.error('Error al enviar el desafío:', data.message);
                        showCustomMessage('Error', 'Error al enviar el desafío: ' + (data.message || 'Error desconocido'));
                    }
                })
                .catch(error => {
                    console.error('Error completo:', error);
                    hideOverlay();
                    showCustomMessage('Error', 'Error al enviar el desafío: ' + error.message);
                });
            });

            function showOverlay(message) {
                const overlay = document.getElementById('overlay');
                if (overlay) {
                    overlay.style.display = 'flex';
                    const overlayMessage = document.getElementById('overlayMessage');
                    if (overlayMessage) {
                        overlayMessage.innerText = message;
                    }
                } else {
                    console.error('Elemento overlay no encontrado');
                }
            }

            function hideOverlay() {
                const overlay = document.getElementById('overlay');
                if (overlay) {
                    overlay.style.display = 'none';
                } else {
                    console.error('Elemento overlay no encontrado');
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
                                <p>Puntos ganados: +10</p>
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
                    <button onclick="closeCustomModal(this)">Aceptar</button>
                `;
                document.body.appendChild(modal);

                window.closeCustomModal = function(button) {
                    button.parentElement.remove();
                    if (callback) callback();
                };
            }
        });
    </script>
</body>
</html>