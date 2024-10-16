<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cholitas en el Mercado de las Brujas</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('https://www.hotelpresidente.com.bo/wp-content/uploads/2024/02/WhatsApp-Image-2024-02-16-at-19.21.24.jpeg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            font-family: 'Press Start 2P', cursive;
            color: #fff;
            text-shadow: 2px 2px 4px #000;
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
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.5);
            transition: all 0.3s;
            font-family: 'Press Start 2P', cursive;
            font-size: 0.7rem;
            display: inline-block;
            margin: 20px auto;
            width: 100%;
            max-width: 200px;
            text-align: center;
        }
        .custom-file-upload:hover{
            background-color: #ffec00;
            transform: scale(1.05);
        }
        #submitBtn:hover{
            background-color: #1f0;
            transform: scale(1.05);
        }
        #submitBtn{
            background-color: #0c0;
            box-shadow: 0 0 10px rgba(0, 215, 100, 0.5);
        }
        .custom- file-upload:active, #submitBtn:active {
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.8);
            transform: scale(0.95);
        }
        #fileInput {
            display: none;
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
            text-align: center;
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
    </style>
</head>
<body>
<div class="card">
        <h1>Reto: Cholitas en el Mercado de las Brujas</h1>
        <p>Sube una foto de una cholita en el Mercado de las Brujas</p>
        <div class="preview-container">
            <img id="preview" src="" alt="Preview de la foto">
        </div>
        <label for="fileInput" class="custom-file-upload">Subir foto</label>
        <input type="file" id="fileInput" accept="image/*">
        <button type="button" id="submitBtn" style="display: none;" class="submit">Enviar</button>
    </div>
    <div id="overlay" class="overlay">
        <div class="overlay-content">
            <p id="overlayMessage">Espere unos segundos, su foto está siendo evaluada por el Game Master :)</p>
            <div class="loader"></div>
        </div>
    </div>
<script>
    let esperandoCalificacion = false;
    let challengeId = null;

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
        if (esperandoCalificacion) {
            alert('Ya has enviado una foto. Por favor, espera la calificación.');
            return;
        }

        showOverlay('Enviando foto...');
        const challengeData = document.getElementById('preview').src;
        
        fetch('../controllers/uploadChallenge.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ challenge: challengeData, gameType: 'photo' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                esperandoCalificacion = true;
                challengeId = data.challengeId;
                showOverlay('Espere unos segundos, su foto está siendo evaluada por el Game Master :)');
                checkCalificacion(); // Iniciar la verificación
            } else {
                hideOverlay();
                alert('Error al enviar el desafío: ' + (data.message || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            hideOverlay();
            alert('Error al enviar el desafío: ' + error.message);
        });
    });

    function showOverlay(message) {
        const overlay = document.getElementById('overlay');
        const overlayMessage = document.getElementById('overlayMessage');
        overlayMessage.textContent = "Espere unos segundos, su foto está siendo evaluada por el Game Master :)";
        overlay.style.display = 'flex';
    }

    function hideOverlay() {
        const overlay = document.getElementById('overlay');
        overlay.style.display = 'none';
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
                alert('Tu foto ha sido ' + (data.status === 'aprobado' ? 'aprobada' : 'rechazada'));
                window.location.href = '../views/evento.php';
            } else {
                // Si aún no está calificado, volver a verificar después de un tiempo
                setTimeout(checkCalificacion, 5000); // Verifica cada 5 segundos
            }
        })
        .catch(error => {
            console.error('Error al verificar calificación:', error);
            setTimeout(checkCalificacion, 5000); // Reintenta en caso de error
        });
    }

    // Asegúrate de que esta función se llame cuando se envíe la foto
    function onPhotoSubmit(event) {
        event.preventDefault();
        // ... código para enviar la foto ...
        esperandoCalificacion = true;
        showOverlay('Espere unos segundos, su foto está siendo evaluada por el Game Master :)');
        checkCalificacion(); // Iniciar la verificación
    }

    // Asegúrate de que el formulario tenga un id, por ejemplo 'photoForm'
    document.getElementById('photoForm').addEventListener('submit', onPhotoSubmit);
</script>
</body>
</html>
