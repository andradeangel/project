<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Juego de Memoria de Bolivia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1a1a1a;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            text-align: center;
            width: 100%;
            max-width: 800px;
            padding: 20px;
        }

        h1 {
            margin-bottom: 20px;
        }

        #game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 20px auto;
        }

        .card {
            aspect-ratio: 3/4;
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            cursor: pointer;
            transition: transform 0.3s ease;
            border: 2px solid #4CAF50;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card.flipped {
            transform: rotateY(180deg);
        }

        #score {
            font-size: 1.2em;
            margin-bottom: 20px;
        }

        #restart-btn {
            font-size: 1em;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #restart-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Juego de Memoria de Bolivia</h1>
        <div id="game-board"></div>
        <div id="score">Intentos: <span id="attempts">0</span></div>
        <button id="restart-btn">Reiniciar Juego</button>
    </div>

    <script>
        const cards = [
            { id: 1, name: 'Illimani', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Illimani_desde_La_Paz.jpg/320px-Illimani_desde_La_Paz.jpg' },
            { id: 2, name: 'Salar de Uyuni', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/Salar_Uyuni_au_coucher_du_soleil.JPG/320px-Salar_Uyuni_au_coucher_du_soleil.JPG' },
            { id: 3, name: 'Tiwanaku', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/75/Puerta_del_Sol%2C_Tiahuanaco%2C_Bolivia.jpg/320px-Puerta_del_Sol%2C_Tiahuanaco%2C_Bolivia.jpg' },
            { id: 4, name: 'La Paz', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/9/92/La_Paz_Bolivia.jpg/320px-La_Paz_Bolivia.jpg' },
            { id: 5, name: 'Cochabamba', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Cristo_de_Concordia.jpg/320px-Cristo_de_Concordia.jpg' },
            { id: 6, name: 'Santa Cruz', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/PradoSantaCruz.jpg/320px-PradoSantaCruz.jpg' },
            { id: 7, name: 'Lago Titicaca', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Titicaca_Lake%2C_Bolivia.jpg/320px-Titicaca_Lake%2C_Bolivia.jpg' },
            { id: 8, name: 'Carnaval de Oruro', image: 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/0c/Carnaval_de_Oruro_-_Bolivia.jpg/320px-Carnaval_de_Oruro_-_Bolivia.jpg' }
        ];

        let flippedCards = [];
        let matchedPairs = 0;
        let attempts = 0;

        function shuffleCards(array) {
            const shuffled = array.concat(array).sort(() => Math.random() - 0.5);
            return shuffled.map((card, index) => ({ ...card, id: index }));
        }

        function createCard(card) {
            const cardElement = document.createElement('div');
            cardElement.classList.add('card');
            cardElement.dataset.id = card.id;
            cardElement.style.backgroundImage = `url('https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Bolivia_%28state%29.svg/320px-Flag_of_Bolivia_%28state%29.svg.png')`;
            cardElement.addEventListener('click', flipCard);
            return cardElement;
        }

        function flipCard() {
            if (flippedCards.length < 2 && !this.classList.contains('flipped')) {
                this.classList.add('flipped');
                this.style.backgroundImage = `url('${cards.find(card => card.id == this.dataset.id).image}')`;
                flippedCards.push(this);

                if (flippedCards.length === 2) {
                    attempts++;
                    document.getElementById('attempts').textContent = attempts;
                    setTimeout(checkMatch, 1000);
                }
            }
        }

        function checkMatch() {
            const [card1, card2] = flippedCards;
            const id1 = card1.dataset.id;
            const id2 = card2.dataset.id;

            if (cards.find(card => card.id == id1).name === cards.find(card => card.id == id2).name) {
                matchedPairs++;
                if (matchedPairs === cards.length) {
                    alert(`¡Felicidades! Has completado el juego en ${attempts} intentos.`);
                }
            } else {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
                card1.style.backgroundImage = `url('https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Bolivia_%28state%29.svg/320px-Flag_of_Bolivia_%28state%29.svg.png')`;
                card2.style.backgroundImage = `url('https://upload.wikimedia.org/wikipedia/commons/thumb/4/41/Flag_of_Bolivia_%28state%29.svg/320px-Flag_of_Bolivia_%28state%29.svg.png')`;
            }

            flippedCards = [];
        }

        function initGame() {
            const gameBoard = document.getElementById('game-board');
            const shuffledCards = shuffleCards(cards);
            gameBoard.innerHTML = '';
            shuffledCards.forEach(card => {
                gameBoard.appendChild(createCard(card));
            });
        }

        document.getElementById('restart-btn').addEventListener('click', () => {
            flippedCards = [];
            matchedPairs = 0;
            attempts = 0;
            document.getElementById('attempts').textContent = attempts;
            initGame();
        });

        initGame();
    </script>
</body>
</html>