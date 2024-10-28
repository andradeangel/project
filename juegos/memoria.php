<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memorizando</title>
    <link rel="icon" href="../images/ico.png">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Press Start 2P', cursive;
            background-color: #1a1a1a;
            color: #fff;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            padding: 20px;
        }
        .game-container {
            text-align: center;
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            max-width: 100%;
            width: 450px;
        }
        .game-board {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 20px;
            justify-content: center;
        }
        .card {
            width: 100%;
            padding-bottom: 100%; /* Aspect ratio 1:1 */
            background-color: #4a4a4a;
            position: relative;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        .card:hover {
            box-shadow: 0 8px 16px rgba(0, 255, 0, 0.5);
        }
        .card img {
            position: absolute;
            top: 5%;
            left: 5%;
            width: 90%;
            height: 90%;
            object-fit: cover;
            display: none;
            border-radius: 5px;
        }
        .card.flipped img {
            display: block;
        }
        .card.flipped {
            transform: rotateY(180deg);
            background-color: #6a6a6a;
        }
        #restart-button {
            font-family: 'Press Start 2P', cursive;
            background-color: #4CAF50;
            border: none;
            padding: 10px 20px;
            color: white;
            text-shadow: 1px 1px 2px black;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            margin-top: 10px;
        }
        #restart-button:hover {
            background-color: #45a049;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
        }
        #win-message {
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
        #win-content {
            background-color: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);
            max-width: 90%;
        }
        #win-content img {
            width: 50px;
            margin-top: 10px;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        @media (max-width: 576px) {
            .game-container {
                padding: 10px;
                width: 95%;
            }
            .game-board {
                gap: 5px;
            }
            h1 {
                font-size: 1rem;
            }
            #restart-button {
                font-size: 0.7rem;
                padding: 8px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="game-container">
            <h1 class="mb-4">Juego de Memoria</h1>
            <div class="game-board mb-4">
                <!-- Las tarjetas se generarán dinámicamente con JavaScript -->
            </div>
            <button id="restart-button" class="btn">Reiniciar Juego</button>
        </div>
    </div>

    <div id="win-message">
        <div id="win-content">
            <h2>Muy buena memoria, aca tienes tu llave</h2>
            <img src="../images/key.png" alt="Llave">
        </div>
    </div>

    <script>
        const images = [
            'https://artishockrevista.com/wp-content/uploads/2020/06/1-illimaniinsitu.jpg',
            'https://www.opinion.com.bo/asset/thumbnail,992,558,center,center/media/opinion/images/2013/08/06/2013N102477.jpg',
            'https://i.pinimg.com/474x/20/78/27/2078277d2ee2fba144fb18acf676486b.jpg',
            'https://thumbs.dreamstime.com/b/bolivian-saltena-meat-pastries-traditional-savory-called-filled-thick-stew-which-popular-street-snack-bolivia-51131075.jpg',
            'https://adminweb.miteleferico.bo/uploads/NARANJA_009e523187.jpg',
            'https://www.bolivar.com.bo/data/picture/2011/Hinchas_Miraflores_Bolivar_The_Strongest_LRZIMA20141029_0021_1.jpg',
            'https://avianreport.com/wp-content/uploads/2023/05/condor-andino-450-550.x46230.webp',
            'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTQ_KvHDAdtG_qZy_IdT7HiCvjm_H7OXhPHeYg9tyFrHFanzr2Ep2-54nPDFhmfO6h1enI&usqp=CAU'
        ];

        let cards = [...images, ...images];
        let flippedCards = [];
        let matchedPairs = 0;

        function shuffleArray(array) {
            for (let i = array.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [array[i], array[j]] = [array[j], array[i]];
            }
        }

        function createCard(image) {
            const card = document.createElement('div');
            card.classList.add('card');
            const img = document.createElement('img');
            img.src = image;
            card.appendChild(img);
            card.addEventListener('click', flipCard);
            return card;
        }

        function flipCard() {
            if (flippedCards.length < 2 && !this.classList.contains('flipped')) {
                this.classList.add('flipped');
                flippedCards.push(this);

                if (flippedCards.length === 2) {
                    setTimeout(checkMatch, 1000);
                }
            }
        }

        function checkMatch() {
            const [card1, card2] = flippedCards;
            const img1 = card1.querySelector('img').src;
            const img2 = card2.querySelector('img').src;

            if (img1 === img2) {
                matchedPairs++;
                if (matchedPairs === images.length) {
                    showWinMessage();
                }
            } else {
                card1.classList.remove('flipped');
                card2.classList.remove('flipped');
            }

            flippedCards = [];
        }

        function showWinMessage() {
            document.getElementById('win-message').style.display = 'flex';
        }

        function initGame() {
            const gameBoard = document.querySelector('.game-board');
            gameBoard.innerHTML = '';
            matchedPairs = 0;
            shuffleArray(cards);

            cards.forEach(image => {
                const card = createCard(image);
                gameBoard.appendChild(card);
            });

            document.getElementById('win-message').style.display = 'none';
        }

        document.getElementById('restart-button').addEventListener('click', initGame);

        initGame();
    </script>
</body>
</html>