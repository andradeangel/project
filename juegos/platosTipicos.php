<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platos Típicos Paceños</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body {
            background-image: url('https://abi.bo/images/Noticias/Sociedad/jul-22/thimpu.jpg');
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
            background-color: #00ff00;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 10px;
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
            background-color: #00cc00;
            transform: scale(1.05);
        }
        #submitBtn {
            display: none;
            background-color: #0c0;
            box-shadow: 0 0 10px rgba(0, 255, 0, 0.5);
        }
        #submitBtn:hover{
            background-color: #1f0;
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
            margin-top: 20px; /* Añade un poco de espacio entre el mensaje y la animación */
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }   
    </style>
</head>
<body>
    <div class="card">
        <h1>Reto: Platos Típicos Paceños</h1>
        <p>Haz un video de 20 segundos o más probando 1 comida típica paceña: salteña, api con pastel, plato paceño u otros. Recomendación no mas de 30 segundos.</p>
        <div class="preview-container">
            <video id="preview" style="display: none;" controls></video>
            <input type="file" id="videoInput" accept="video/*" capture="environment" style="display: none;">
            <button class="custom-file-upload" onclick="document.getElementById('videoInput').click()">Subir Video</button>
        </div>
        <button id="submitBtn" onclick="enviarVideo()">Enviar</button>
    </div>
    <div class="overlay" id="overlay">
        <div class="card">
            <div class="message">Espere un momento, su video está siendo evaluada por el Game Master :)</div>
            <div class="loader"></div>
        </div>
    </div>

    <script>
        const videoInput = document.getElementById('videoInput');
        const preview = document.getElementById('preview');
        const submitBtn = document.getElementById('submitBtn');
        const overlay = document.getElementById('overlay');

        videoInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const videoURL = URL.createObjectURL(file);
                preview.src = videoURL;
                preview.style.display = 'block';
                submitBtn.style.display = 'block';
            }
        });

        function enviarVideo() {
            overlay.classList.add('show');
            // Aquí iría la lógica para enviar el video
            console.log('Video enviado:', preview.src);
        }
    </script>
</body>
</html>