<?php
$desafios = [
    [
        'pregunta' => '¿Cuántos segundos tiene un año?',
        'respuesta' => '31536000',
        'pista' => 'Piensa en días y horas'
    ],
    [
        'pregunta' => '¿Cuántos días tiene un siglo?',
        'respuesta' => '36500',
        'pista' => 'Recuerda los años bisiestos'
    ]
];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $respuesta = $_POST['respuesta'];
    
    foreach ($desafios as $desafio) {
        if ($respuesta == $desafio['respuesta']) {
            echo json_encode(['correcto' => true]);
            exit;
        }
    }
    
    echo json_encode(['correcto' => false]);
    exit;
}
?>
<!DOCTYPE html>
<html>
<body>
    <div id="juego-panico">
        <h2>ZONA DE PÁNICO</h2>
        <div id="pregunta"></div>
        <div id="pista"></div>
        <input type="text" id="respuesta">
        <div id="tiempo">60</div>
        <button onclick="verificarRespuesta()">RESPONDER</button>
        
        <!-- Efectos de sonido y visuales -->
        <audio id="sonido-fondo" loop>
            <source src="sonido_tension.mp3" type="audio/mpeg">
        </audio>
    </div>

    <script>
    let desafios = <?php echo json_encode($desafios); ?>;
    let indiceActual = 0;
    let tiempo = 60;
    
    window.onload = function() {
        document.getElementById('pregunta').innerText = desafios[indiceActual]['pregunta'];
        document.getElementById('pista').innerText = desafios[indiceActual]['pista'];
        document.getElementById('sonido-fondo').play();
        
        setInterval(function() {
            tiempo--;
            document.getElementById('tiempo').innerText = tiempo;
            
            if (tiempo <= 10) {
                document.body.style.backgroundColor = 'red';
            }
            
            if (tiempo <= 0) {
                alert('¡TIEMPO AGOTADO! DESAFÍO FALLIDO');
                document.getElementById('sonido-fondo').pause();
            }
        }, 1000);
    }

    function verificarRespuesta() {
        let respuesta = document.getElementById('respuesta').value;
        
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'respuesta=' + respuesta
        })
        .then(response => response.json())
        .then(data => {
            if (data.correcto) {
                indiceActual++;
                document.body.style.backgroundColor = 'green';
                
                if (indiceActual < desafios.length) {
                    document.getElementById('pregunta').innerText = desafios[indiceActual]['pregunta'];
                    document.getElementById('pista').innerText = desafios[indiceActual]['pista'];
                    document.getElementById('respuesta').value = '';
                    tiempo = 60;
                } else {
                    alert('¡DESAFÍO COMPLETADO!');
                    document.getElementById('sonido-fondo').pause();
                }
            } else {
                alert('RESPUESTA INCORRECTA. ¡CUIDADO!');
                document.body.style.backgroundColor = 'red';
            }
        });
    }
    </script>
</body>
</html>