<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reto 2: Cholitas en el Mercado de las Brujas</title>
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
        .preview-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }
        .preview-container img {
            max-width: 45%;
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
        <h1>Reto 2: Cholitas en el Mercado de las Brujas</h1>
        <p>Encuentra y fotografía 5 cholitas paceñas con sus trajes típicos en el Mercado de las Brujas.</p>
        <input type="file" id="fileInput" accept="image/*" capture="environment" multiple style="display: none;">
        <button onclick="document.getElementById('fileInput').click()">Capturar Imágenes</button>
        <div id="previewContainer" class="preview-container"></div>
        <button id="submitBtn" onclick="enviarImagenes()">Enviar</button>
    </div>

    <script>
        const fileInput = document.getElementById('fileInput');
        const previewContainer = document.getElementById('previewContainer');
        const submitBtn = document.getElementById('submitBtn');
        let imageCount = 0;

        fileInput.addEventListener('change', function(event) {
            const files = event.target.files;
            for (let i = 0; i < files.length && imageCount < 5; i++) {
                const file = files[i];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        previewContainer.appendChild(img);
                        imageCount++;
                        if (imageCount === 5) {
                            submitBtn.style.display = 'block';
                        }
                    }
                    reader.readAsDataURL(file);
                }
            }
        });

        function enviarImagenes() {
            // Aquí iría la lógica para enviar las imágenes
            console.log('Imágenes enviadas:', previewContainer.innerHTML);
            alert('Imágenes enviadas con éxito!');
            // Podrías hacer una llamada a una API aquí
        }
    </script>
</body>
</html>