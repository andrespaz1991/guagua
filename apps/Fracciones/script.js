const fractionMatrix = [
    ["1/4", "1/5", "1/4", "3/6"],
    ["3/3", "1/2", "1/3", "3/5"],
    ["3/6", "1/4", "2/3", "2/5"],
    ["3/4", "1/6", "2/4", "2/6"],
    ["1/5", "4/5", "6/6", "4/6"]
];

// Lista de colores predeterminados
const colors = ['#FF6347', '#4682B4', '#32CD32', '#FFD700']; // Tomate, Azul acero, Verde lima, Oro

function drawCircle(fraction, canvasId) {
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
        ctx.fillText('Inválido', 25, 50);
        return;
    }

    const radius = canvas.width / 2;
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const startAngle = 0;
    const endAngle = (numerator / denominator) * 2 * Math.PI;

    // Asignar un color basado en el valor de la fracción
    const colorIndex = (numerator * 4 / denominator) | 0;
    const color = colors[colorIndex % colors.length];

    // Dibujar la parte coloreada
    ctx.beginPath();
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
    ctx.closePath();
    ctx.fillStyle = color;
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
}

function generateTable() {
    const table = document.getElementById('fractionTable');
    let tableContent = '';

    fractionMatrix.forEach((row, rowIndex) => {
        tableContent += '<tr>';
        row.forEach((fraction, colIndex) => {
            const canvasId = `canvas-${rowIndex}-${colIndex}`;
            tableContent += `
                <td style="border: 2px solid black;">
                    <canvas id="${canvasId}" width="100" height="100" ondrop="drop(event)" ondragover="allowDrop(event)"></canvas>
                </td>`;
        });
        tableContent += '</tr>';
    });

    table.innerHTML = tableContent;

    fractionMatrix.forEach((row, rowIndex) => {
        row.forEach((fraction, colIndex) => {
            const canvasId = `canvas-${rowIndex}-${colIndex}`;
            drawCircle(fraction, canvasId);
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

    const radius = canvas.width / 2;
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;

    ctx.beginPath();
    ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    ctx.closePath();
    ctx.fillStyle = ficha.style.backgroundColor;
    ctx.fill();
}

// Asignar la funcionalidad de arrastrar a las fichas
document.querySelectorAll('.ficha').forEach((ficha, index) => {
    ficha.id = `ficha-${index}`;
    ficha.draggable = true;
    ficha.ondragstart = drag;
});
