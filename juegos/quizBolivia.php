<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz sobre Bolivia</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=VT323&display=swap" rel="stylesheet">
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
            margin: 10px 0;
            max-width: 800px; /* Ajusta este valor según tus necesidades */
        }
        h1 {
            font-size: 2.8rem;
            color: #ffd700;
            text-align: center;
            margin-bottom: 30px;
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
            font-size: 3rem;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        .card-text {
            font-size: 2rem;
            color: #fff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
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
            font-size: 1.5rem;
        }
        .btn-outline-light:hover {
            background-color: #ffd700;
            color: #000;
        }
        #submit {
            background-color: #ffd700;
            border: none;
            color: #000;
            font-size: 1.5rem;
            padding: 10px;
            transition: all 0.3s ease;
        }
        #submit:hover {
            background-color: #ffec00;
            transform: scale(1.03);
        }
        #result {
            background-color: rgba(0, 0, 0, 0.8);
            border: 2px solid #ffd700;
            border-radius: 15px;
            padding: 15px;
            margin-top: 20px;
            font-size: 2rem;
            color: #ffd700;
            text-shadow: 1px 1px 2px #000;
        } </style>
</head>
<body class="d-flex align-items-center justify-content-center">
    <div class="container">
        <h1>Quiz de Conocimiento General sobre Bolivia</h1>
        <div id="quiz-container"></div>
        <button id="submit" class="btn btn-lg w-100 mt-4">Verificar Respuestas</button>
        <div id="result" class="mt-4 text-center fs-4 fw-bold"></div>
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
                questionDiv.classList.add('card', 'quiz-card', 'mb-4');
                questionDiv.innerHTML = `
                    <div class="card-body">
                        <h5 class="card-title">Pregunta ${index + 1}</h5>
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
            resultDiv.textContent = `Tu puntuación: ${score} de ${quizData.length}`;
            
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
                });
            });

            submitButton.disabled = true;
        }

        createQuiz();
        submitButton.addEventListener('click', checkAnswers);
    </script>
</body>
</html>