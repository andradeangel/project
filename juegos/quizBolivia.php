<?php
require_once('../database.php');
custom_session_start('player_session');

if (!isset($_SESSION['jugador_actual']) || !isset($_SESSION['evento_actual'])) {
    error_log("Redirección a evento.php por falta de datos de sesión");
    header('Location: ../views/evento.php');
    exit;
}

$juego_id = $_GET['juego_id'] ?? null;
$descripcion = $_GET['descripcion'] ?? 'Descripción no disponible';
$_SESSION['current_game_id'] = $juego_id;
$_SESSION['current_game_description'] = $descripcion;

// Función para actualizar el puntaje
function actualizarPuntaje($puntos) {
    global $conexion;
    $jugadorId = $_SESSION['jugador_actual']['id'];
    
    // Obtener puntaje actual y juego actual
    $sql = "SELECT puntaje, juego_actual FROM jugadores WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $jugadorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $puntajeActual = $row['puntaje'];
    $juegoActual = $row['juego_actual'];
    
    // Actualizar puntaje, juego actual y tiempo_fin
    $nuevoPuntaje = $puntajeActual + $puntos;
    $nuevoJuegoActual = $juegoActual + 1;
    $sql = "UPDATE jugadores 
            SET puntaje = ?, 
                juego_actual = ?, 
                tiempo_fin = CURRENT_TIMESTAMP 
            WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("iii", $nuevoPuntaje, $nuevoJuegoActual, $jugadorId);
    
    if ($stmt->execute()) {
        return ['success' => true, 'nuevoPuntaje' => $nuevoPuntaje];
    }
    return ['success' => false];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (isset($data['action']) && $data['action'] === 'actualizarPuntaje' && isset($data['puntos'])) {
        $resultado = actualizarPuntaje($data['puntos']);
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz sobre Bolivia</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: 'VT323', monospace;
            background-image: url('https://convenioandresbello.org/wp-content/uploads/2020/07/noticia49_bolivia_01.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: #fff;
            text-shadow: 1px 1px 2px #000;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 10px;
            margin: 10px 20px;
            max-width: 800px; /* Ajusta este valor según tus necesidades */
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            color: #ffd700;
            text-align: center;
            margin: 0;
            text-shadow: 2px 2px 4px #000;
        }
        .quiz-card {
            border: 2px solid #ffd700;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.5);
            background-color: rgba(0, 0, 0, 0.8);
            margin-bottom: 20px;
            transition: all 0.3s ease;
        }
        .quiz-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0 20px rgba(255, 215, 0, 0.8);
        }
        .card-title {
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            margin: 0;
        }
        .card-text {
            font-size: 1.4rem;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            margin: 0 0 5px 0;
        }
        .btn-check:checked + .btn-outline-light {
            background-color: #ffd700;
            color: #000;
            border-color: #ffd700;
        }
        .btn-outline-light {
            border-color: #ffd700;
            color: #ffd700;
            transition: all 0.3s ease;
            font-size: 1.2rem;
        }
        .btn-outline-light:hover {
            background-color: #ffd700;
            color: #000;
        }
        #submit {
            background-color: #caaa00;
            border: none;
            color: #000;
            font-size: 1.5rem;
            padding: 10px;
            transition: all 0.3s ease;
        }
        #submit:hover {
            background-color: #ff0;
        }
        #result {
            background-color: rgba(0, 0, 0, 0.8);

            border-radius: 15px;

            font-size: 2rem;
            color: #ffd700;
            text-shadow: 1px 1px 2px #000;
        }
        .back-btn {
            background-color: rgba(50, 50, 50, 0.9);
            border: none;
            color: #ddd;
            margin: 2px 0;
            border-radius: 5px;
            cursor: pointer;
            font-size: 20px;
            z-index: 1000;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
            display: block;
        }
        .back-btn:hover {
            background-color: rgba(80, 80, 80, 0.9);
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    
    <div class="container">
        <h1>Quiz de Conocimiento General sobre Bolivia</h1>
        <p class="card-text"><?php echo htmlspecialchars($descripcion); ?></p>
        <div id="quiz-container"></div>
        <button id="submit" class="btn btn-lg w-100 mb-2">Verificar Respuestas</button>
        <button onclick="window.location.href='../views/evento.php'" class="back-btn w-100">Volver</button>
        <div id="result" class="text-center fs-4 fw-bold"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const quizData = [
            {
                question: "¿Cuál es la capital constitucional de Bolivia?",
                options: ["La Paz", "Sucre", "Santa Cruz", "Cochabamba"],
                answer: 1
            },
            {
                question: "¿Cuál es el pico más alto de Bolivia?",
                options: ["Illimani", "Huayna Potosí", "Sajama", "Illampu"],
                answer: 2
            },
            {
                question: "¿Cuál es el lago navegable más alto del mundo, ubicado en Bolivia?",
                options: ["Lago Poopó", "Lago Titicaca", "Salar de Uyuni", "Lago Guiña"],
                answer: 1
            }
        ];

        const quizContainer = document.getElementById('quiz-container');
        const submitButton = document.getElementById('submit');
        const resultDiv = document.getElementById('result');

        function createQuiz() {
            quizData.forEach((question, index) => {
                const questionDiv = document.createElement('div');
                questionDiv.classList.add('card', 'quiz-card', 'mb-2');
                questionDiv.innerHTML = `
                    <div class="card-body">
                        <h2 class="card-title">Pregunta ${index + 1}</h2>
                        <p class="card-text">${question.question}</p>
                        <div class="options">
                            ${question.options.map((option, i) => `
                                <input type="radio" class="btn-check" name="q${index}" id="q${index}o${i}" value="${i}" autocomplete="off">
                                <label class="btn btn-outline-light w-100 mb-2 text-start" for="q${index}o${i}">${option}</label>
                            `).join('')}
                        </div>
                    </div>
                `;
                quizContainer.appendChild(questionDiv);
            });
        }

        function checkAnswers() {
        let score = 0;
        quizData.forEach((question, index) => {
            const selectedOption = document.querySelector(`input[name="q${index}"]:checked`);
            if (selectedOption && parseInt(selectedOption.value) === question.answer) {
                score++;
            }
        });

        let puntosGanados = 0;
        if (score === 3) puntosGanados = 3;
        else if (score === 2) puntosGanados = 2;
        else if (score === 1) puntosGanados = 1;

        fetch('<?php echo $_SERVER['PHP_SELF']; ?>', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'actualizarPuntaje',
                puntos: puntosGanados
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    Tu puntuación: ${score} de ${quizData.length}<br>
                    Puntos ganados: +${puntosGanados}<br>
                    Puntaje total: ${data.nuevoPuntaje}
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            resultDiv.textContent = `Tu puntuación: ${score} de ${quizData.length}`;
        });

        // Mostrar respuestas correctas e incorrectas
        quizData.forEach((question, index) => {
            const selectedOption = document.querySelector(`input[name="q${index}"]:checked`);
            const options = document.querySelectorAll(`input[name="q${index}"]`);
            
            options.forEach((option, i) => {
                const label = option.nextElementSibling;
                label.classList.remove('btn-outline-light', 'btn-success', 'btn-danger');
                
                if (i === question.answer) {
                    label.classList.add('btn-success');
                } else if (selectedOption && parseInt(selectedOption.value) === i) {
                    label.classList.add('btn-danger');
                } else {
                    label.classList.add('btn-outline-light');
                }
                option.disabled = true;
            });
        });

        // Cambiar el botón a "Aceptar" y su funcionalidad
        submitButton.textContent = 'Aceptar';
        submitButton.removeEventListener('click', checkAnswers);
        submitButton.addEventListener('click', () => {
            window.location.href = '../views/evento.php';
        });

        // Ocultar el botón "Volver"
        document.querySelector('.back-btn').style.display = 'none';
    }

    createQuiz();
    submitButton.addEventListener('click', checkAnswers);
    </script>
</body>
</html>