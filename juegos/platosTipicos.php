<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto 3: Platos Típicos Paceños</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f0f0f0;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 300px;
            width: 100%;
        }
        h1 {
            font-size: 1.5em;
            margin-bottom: 15px;
        }
        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        button:hover {
            background-color: #0056b3;
        }
        video {
            max-width: 100%;
            border-radius: 4px;
            margin-bottom: 10px;
        }
        #submitBtn {
            display: none;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>Reto 3: Platos Típicos Paceños</h1>
        <p>Haz un video corto probando 3 platos típicos paceños: salteña, api con pastel, y ranga ranga.</p>
        <input type="file" id="videoInput" accept="video/*" capture="environment" style="display: none;">
        <button onclick="document.getElementById('videoInput').click()">Grabar Video</button>
        <video id="preview" style="display: none;" controls></video>
        <button id="submitBtn" onclick="enviarVideo()">Enviar</button>
    </div>

    <script>
        const videoInput = document.getElementById('videoInput');
        const preview = document.getElementById('preview');
        const submitBtn = document.getElementById('submitBtn');

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
            // Aquí iría la lógica para enviar el video
            console.log('Video enviado:', preview.src);
            alert('Video enviado con éxito!');
            // Podrías hacer una llamada a una API aquí
        }
    </script>
</body>
</html>