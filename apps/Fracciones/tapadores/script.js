const fractionMatrix = [
    ["1/4", "1/5", "1/4", "3/6"],
    ["3/3", "1/2", "1/3", "3/5"],
    ["3/6", "1/4", "2/3", "2/5"],
    ["3/4", "1/6", "2/4", "2/6"],
    ["1/5", "4/5", "6/6", "4/6"]
];

// Lista de colores
const colors = ['#FF6347', '#4682B4', '#32CD32', '#FFD700']; // Tomate, Azul acero, Verde lima, Oro

function drawSquare(fraction, canvasId) {
    const parts = fraction.split('/').map(Number);
    const numerator = parts[0];
    const denominator = parts[1];

    const canvas = document.getElementById(canvasId);
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

    // Asignar un color basado en el valor de la fracción
    const colorIndex = (numerator * 4 / denominator) | 0;
    const color = colors[colorIndex % colors.length];

    ctx.fillStyle = color;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.strokeStyle = 'black';
    ctx.lineWidth = 2;
    ctx.strokeRect(0, 0, canvas.width, canvas.height);
}

function generateTable() {
    const table = document.getElementById('fractionTable');
    let tableContent = '';

    fractionMatrix.forEach((row, rowIndex) => {
        tableContent += '<tr>';
        row.forEach((fraction, colIndex) => {
            const canvasId = `canvas-${rowIndex}-${colIndex}`;
            tableContent += `
                <td>
                    <canvas id="${canvasId}" width="100" height="100" ondrop="drop(event)" ondragover="allowDrop(event)"></canvas>
                </td>`;
        });
        tableContent += '</tr>';
    });

    table.innerHTML = tableContent;

    fractionMatrix.forEach((row, rowIndex) => {
        row.forEach((fraction, colIndex) => {
            const canvasId = `canvas-${rowIndex}-${colIndex}`;
            drawSquare(fraction, canvasId);
        });
    });
}

document.addEventListener('DOMContentLoaded', generateTable);

function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function drop(ev) {
    ev.preventDefault();
    const data = ev.dataTransfer.getData("text");
    const ficha = document.getElementById(data);
    const canvas = ev.target;
    const ctx = canvas.getContext('2d');

    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = ficha.style.backgroundColor;
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.strokeStyle = 'black';
    ctx.lineWidth = 2;
    ctx.strokeRect(0, 0, canvas.width, canvas.height);
}

// Asignar la funcionalidad de arrastrar a las fichas
document.querySelectorAll('.ficha').forEach((ficha, index) => {
    ficha.id = `ficha-${index}`;
    ficha.draggable = true;
    ficha.ondragstart = drag;
});
