<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto 1: Captura el Teleférico</title>
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
        img {
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
        <h1>Reto 1: Captura el Teleférico</h1>
        <p>Captura una foto del teleférico de La Paz con la ciudad de fondo.</p>
        <input type="file" id="fileInput" accept="image/*" capture="environment" style="display: none;">
        <button onclick="document.getElementById('fileInput').click()">Capturar Imagen</button>
        <img id="preview" style="display: none;" alt="Vista previa de la imagen">
        <button id="submitBtn" onclick="enviarImagen()">Enviar</button>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const preview = document.getElementById('preview');
        const submitBtn = document.getElementById('submitBtn');

        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    submitBtn.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });

        function enviarImagen() {
            // Aquí iría la lógica para enviar la imagen
            console.log('Imagen enviada:', preview.src);
            alert('Imagen enviada con éxito!');
            // Podrías hacer una llamada a una API aquí
        }
    </script>
</body>
</html>