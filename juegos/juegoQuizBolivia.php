<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz sobre Bolivia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        
        .quiz-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            background-color: #343a40; /* Fondo oscuro para la tarjeta */
        }
        .card-title, .card-text {
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5); /* Sombra negra para el texto */
        }
        .btn-check:checked + .btn-outline-light {
            background-color: #6c757d;
            color: white;
        }
        .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="text-center mb-5 text-info">Quiz de Conocimiento General sobre Bolivia</h1>
        <div id="quiz-container"></div>
        <button id="submit" class="btn btn-primary btn-lg w-100 mt-4">Verificar Respuestas</button>
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