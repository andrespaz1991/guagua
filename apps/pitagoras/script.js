(() => {
  const svg = document.getElementById('pitagorasSvg');
  const rotateSpace = document.getElementById('rotateSpace');
  const droplets = document.getElementById('droplets');
  const waterStreams = document.getElementById('waterStreams');
  const waterLayers = [document.getElementById('waterA'), document.getElementById('waterB'), document.getElementById('waterC')];
  const select = document.getElementById('triangleSelect');
  const animationButtons = [document.getElementById('animateBtn'), document.getElementById('fillBtn')];
  const text = (id, value) => { document.getElementById(id).textContent = value; };
  const attr = (id, name, value) => { document.getElementById(id).setAttribute(name, value); };
  let filled = false;
  let rotating = false;
  let rotation = 0;
  let pourTimer;

  function values() {
    const [b, a] = select.value.split(',').map(Number);
    const c = Math.sqrt(a * a + b * b);
    return { a, b, c };
  }

  function drawDroplets() {
    droplets.innerHTML = '';
  }

  function formatPoint({ x, y }) {
    return `${x.toFixed(1)},${y.toFixed(1)}`;
  }

  function setPolygon(ids, points) {
    const value = points.map(formatPoint).join(' ');
    ids.forEach((id) => attr(id, 'points', value));
  }

  function updateGeometry(a, b, c) {
    const left = { x: 255, y: 400 };
    const right = { x: 505, y: 400 };
    const scale = 250 / c;
    const aLength = a * scale;
    const bLength = b * scale;
    const apexX = left.x + ((a * a - b * b + c * c) / (2 * c)) * scale;
    const apex = { x: apexX, y: left.y - Math.sqrt(Math.max(0, aLength * aLength - (apexX - left.x) ** 2)) };
    const sideA = { x: left.x - apex.x, y: left.y - apex.y };
    const sideB = { x: right.x - apex.x, y: right.y - apex.y };
    const normalA = { x: -sideA.y, y: sideA.x };
    const normalB = { x: sideB.y, y: -sideB.x };
    const aSquare = [apex, left, { x: left.x + normalA.x, y: left.y + normalA.y }, { x: apex.x + normalA.x, y: apex.y + normalA.y }];
    const bSquare = [apex, right, { x: right.x + normalB.x, y: right.y + normalB.y }, { x: apex.x + normalB.x, y: apex.y + normalB.y }];
    setPolygon(['clipShapeA', 'tankA', 'gridA', 'outlineA'], aSquare);
    setPolygon(['clipShapeB', 'tankB', 'gridB', 'outlineB'], bSquare);
    attr('centerTriangle', 'd', `M${formatPoint(apex)} L${formatPoint(left)} L${formatPoint(right)} Z`);
    attr('hypotenuseLine', 'd', `M${formatPoint(left)} L${formatPoint(right)}`);
    const unitA = { x: sideA.x / aLength, y: sideA.y / aLength };
    const unitB = { x: sideB.x / bLength, y: sideB.y / bLength };
    const markerA = { x: apex.x + unitA.x * 25, y: apex.y + unitA.y * 25 };
    const markerCorner = { x: markerA.x + unitB.x * 25, y: markerA.y + unitB.y * 25 };
    const markerB = { x: apex.x + unitB.x * 25, y: apex.y + unitB.y * 25 };
    attr('rightAngle', 'd', `M${formatPoint(markerA)} L${formatPoint(markerCorner)} L${formatPoint(markerB)}`);
    ['pivotRing', 'pivotCore'].forEach((id) => { attr(id, 'cx', apex.x); attr(id, 'cy', apex.y); });

    const centerA = { x: (apex.x + left.x + normalA.x) / 2, y: (apex.y + left.y + normalA.y) / 2 };
    const centerB = { x: (apex.x + right.x + normalB.x) / 2, y: (apex.y + right.y + normalB.y) / 2 };
    const angleA = Math.atan2(sideA.y, sideA.x) * 180 / Math.PI - 180;
    const angleB = Math.atan2(sideB.y, sideB.x) * 180 / Math.PI;
    [['labelA', centerA.y - 10, angleA], ['areaA', centerA.y + 17, angleA]].forEach(([id, y, angle]) => {
      attr(id, 'x', centerA.x); attr(id, 'y', y); attr(id, 'transform', `rotate(${angle.toFixed(1)} ${centerA.x.toFixed(1)} ${y.toFixed(1)})`);
    });
    [['labelB', centerB.y - 10, angleB], ['areaB', centerB.y + 17, angleB]].forEach(([id, y, angle]) => {
      attr(id, 'x', centerB.x); attr(id, 'y', y); attr(id, 'transform', `rotate(${angle.toFixed(1)} ${centerB.x.toFixed(1)} ${y.toFixed(1)})`);
    });
    attr('sideLabelA', 'x', (apex.x + left.x) / 2 - 7); attr('sideLabelA', 'y', (apex.y + left.y) / 2 - 3);
    attr('sideLabelB', 'x', (apex.x + right.x) / 2 + 10); attr('sideLabelB', 'y', (apex.y + right.y) / 2 - 3);
    attr('streamA', 'd', `M${formatPoint(centerA)} C${(centerA.x + 25).toFixed(1)} 355, 320 430, 358 492`);
    attr('streamB', 'd', `M${formatPoint(centerB)} C${(centerB.x - 25).toFixed(1)} 365, 465 440, 404 492`);
  }

  function updateDisplay() {
    const { a, b, c } = values();
    const a2 = a * a;
    const b2 = b * b;
    const c2 = c * c;
    text('measureA', a);
    text('measureB', b);
    text('measureC', c);
    text('labelA', `a = ${a}`);
    text('areaA', `a² = ${a2}`);
    text('labelB', `b = ${b}`);
    text('areaB', `b² = ${b2}`);
    text('labelC', `c = ${c}`);
    text('areaC', `c² = ${c2}`);
    text('sumArea', `${a2} + ${b2} = ${c2}`);
    text('areaResult', '¡Igual al área verde!');
    updateGeometry(a, b, c);
    resetExperiment(`Nuevo triángulo: ${a}² + ${b}² = ${c}². El agua está lista.`);
  }

  function setButtons(disabled) {
    animationButtons.forEach((button) => { button.disabled = disabled; });
  }

  function applyRotation() {
    rotateSpace.style.transform = `rotate(${rotation}deg)`;
    waterLayers.forEach((water) => { water.style.transform = `rotate(${-rotation}deg)`; });
  }

  function animatePour() {
    if (filled) {
      resetExperiment('El agua volvió a los dos cuadrados pequeños.');
      return;
    }
    clearTimeout(pourTimer);
    setButtons(true);
    droplets.classList.remove('is-pouring');
    waterStreams.classList.remove('is-pouring');
    void droplets.getBoundingClientRect();
    droplets.classList.add('is-pouring');
    waterStreams.classList.add('is-pouring');
    text('liveExplanation', 'El agua azul de los dos catetos baja hacia el cuadrado verde…');
    pourTimer = setTimeout(() => {
      svg.classList.add('is-filled');
      filled = true;
      waterStreams.classList.remove('is-pouring');
      text('liveExplanation', '¡Lo llenó por completo! a² + b² tiene exactamente la misma área que c².');
      text('areaResult', '¡Cuadrado grande lleno!');
      animationButtons[0].innerHTML = '<span aria-hidden="true">↺</span> Ver de nuevo';
      animationButtons[1].innerHTML = '<span aria-hidden="true">↺</span> Vaciar y repetir';
      setButtons(false);
    }, 920);
  }

  function resetExperiment(message = 'Listo: presiona “Ver el agua moverse”.') {
    clearTimeout(pourTimer);
    svg.classList.remove('is-filled');
    droplets.classList.remove('is-pouring');
    waterStreams.classList.remove('is-pouring');
    filled = false;
    text('liveExplanation', message);
    animationButtons[0].innerHTML = '<span aria-hidden="true">▶</span> Ver el agua moverse';
    animationButtons[1].innerHTML = '<span aria-hidden="true">💧</span> Pasar agua al cuadrado verde';
    setButtons(false);
  }

  function rotateFigure() {
    if (rotating) return;
    rotating = true;
    rotation = (rotation + 90) % 360;
    applyRotation();
    if (filled) {
      setButtons(true);
      waterStreams.classList.remove('is-pouring');
      waterStreams.classList.add('is-reversing');
      svg.classList.remove('is-filled');
      filled = false;
      text('liveExplanation', 'Al girar, el agua del cuadrado verde llena de nuevo los dos cuadrados pequeños.');
      setTimeout(() => {
        waterStreams.classList.remove('is-reversing');
        text('areaResult', '¡Los catetos se llenaron de nuevo!');
        animationButtons[0].innerHTML = '<span aria-hidden="true">▶</span> Ver el agua moverse';
        animationButtons[1].innerHTML = '<span aria-hidden="true">💧</span> Pasar agua al cuadrado verde';
        setButtons(false);
      }, 1080);
    } else {
      text('liveExplanation', '¡Gira! El recipiente cambia de posición, pero el agua se queda horizontal.');
    }
    setTimeout(() => { rotating = false; }, 720);
  }

  animationButtons.forEach((button) => button.addEventListener('click', animatePour));
  document.getElementById('rotateBtn').addEventListener('click', rotateFigure);
  document.getElementById('resetBtn').addEventListener('click', () => resetExperiment());
  select.addEventListener('change', updateDisplay);
  drawDroplets();
  updateDisplay();
})();
