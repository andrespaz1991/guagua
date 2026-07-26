// script.js

let fractionBag = [];

// Generar fracciones aleatorias
function getRandomFraction() {
    const numerator = Math.floor(Math.random() * 10) + 1;
    const denominator = Math.floor(Math.random() * 10) + 1;
    return `${numerator}/${denominator}`;
}

// Dibujar el gráfico de una fracción en un canvas
function drawCircle(fraction, canvas) {
    const [numerator, denominator] = fraction.split('/').map(Number);
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);

    if (isNaN(numerator) || isNaN(denominator) || denominator === 0) {
        ctx.fillStyle = '#ccc';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#000';
        ctx.font = '20px Arial';
        ctx.fillText('Inválido', 50, 75);
        return;
    }

    const radius = canvas.width / 2 - 10;
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const startAngle = 0;
    const endAngle = (numerator / denominator) * 2 * Math.PI;
    
    const randomColor = '#' + Math.floor(Math.random() * 16777215).toString(16);

    // Dibujar la parte coloreada
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.closePath();
    ctx.fillStyle = randomColor;
    ctx.fill();
    ctx.strokeStyle = 'black';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Dibujar el resto del círculo
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, endAngle, 2 * Math.PI);
    ctx.closePath();
    ctx.fillStyle = '#ffffff';
    ctx.fill();
    ctx.strokeStyle = 'black';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Dibujar los bordes del círculo y las divisiones
    for (let i = 0; i < denominator; i++) {
        const angle = (i / denominator) * 2 * Math.PI;
        ctx.beginPath();
        ctx.moveTo(centerX, centerY);
        ctx.lineTo(centerX + radius * Math.cos(angle), centerY + radius * Math.sin(angle));
        ctx.stroke();
    }

    // Mostrar la fracción en la esquina superior derecha
    ctx.fillStyle = 'black';
    ctx.font = 'bold 14px Arial';
    ctx.textAlign = 'right';
    ctx.fillText(fraction, canvas.width - 5, 15);
}

// Dibujar una fracción en un canvas
function drawFraction(fraction, canvas) {
    drawCircle(fraction, canvas);
}

// Generar una tarjeta de bingo
function generateBingoCard(cardId) {
    const table = document.createElement('table');
    table.id = cardId;
    for (let i = 0; i < 5; i++) {
        const row = document.createElement('tr');
        for (let j = 0; j < 5; j++) {
            const cell = document.createElement('td');
            const fraction = getRandomFraction();
            fractionBag.push(fraction); // Agregar la fracción a la bolsa
            const canvasId = `${cardId}-canvas-${i}-${j}`;
            const canvas = document.createElement('canvas');
            canvas.id = canvasId;
            canvas.width = 100;
            canvas.height = 100;
            cell.appendChild(canvas);
            row.appendChild(cell);
            drawFraction(fraction, canvas);
        }
        table.appendChild(row);
    }
    return table;
}

// Generar el tablero maestro
function generateMasterBoard() {
    const table = document.createElement('table');
    table.id = 'masterBoard';
    for (let i = 0; i < 5; i++) {
        const row = document.createElement('tr');
        for (let j = 0; j < 5; j++) {
            const cell = document.createElement('td');
            const fraction = getRandomFraction();
            fractionBag.push(fraction); // Agregar la fracción a la bolsa
            const canvasId = `masterBoard-canvas-${i}-${j}`;
            const canvas = document.createElement('canvas');
            canvas.id = canvasId;
            canvas.width = 100;
            canvas.height = 100;
            cell.appendChild(canvas);
            row.appendChild(cell);
            drawFraction(fraction, canvas);
        }
        table.appendChild(row);
    }
    return table;
}

// Generar las tarjetas de bingo y el tablero maestro
function generateBingoCards() {
    fractionBag = []; // Reiniciar la bolsa de fracciones
    const bingoCardsContainer = document.getElementById('bingoCardsContainer');
    bingoCardsContainer.innerHTML = '';
    for (let i = 0; i < 2; i++) { // Generar 2 tarjetas de bingo como ejemplo
        const card = generateBingoCard(`bingoCard-${i}`);
        bingoCardsContainer.appendChild(card);
    }

    const masterBoardContainer = document.getElementById('masterBoardContainer');
    masterBoardContainer.innerHTML = '<h2>Tablero Maestro</h2>';
    const masterBoard = generateMasterBoard();
    masterBoardContainer.appendChild(masterBoard);

    displayAllFractions(); // Mostrar todas las fracciones
}

// Mostrar todas las fracciones de la bolsa
function displayAllFractions() {
    const fractionsList = document.getElementById('fractionsList');
    fractionsList.innerHTML = '';
    fractionBag.forEach(fraction => {
        const fractionItem = document.createElement('div');
        fractionItem.className = 'fraction-item';
        fractionItem.innerHTML = `
            <canvas width="80" height="80"></canvas>
            <div>${fraction}</div>
        `;
        fractionsList.appendChild(fractionItem);
        const canvas = fractionItem.querySelector('canvas');
        drawFraction(fraction, canvas);
    });
}

document.addEventListener('DOMContentLoaded', generateBingoCards);
