(() => {
  'use strict';

  const canvas = document.getElementById('earthCanvas');
  const ctx = canvas.getContext('2d');
  const wrap = document.getElementById('canvasWrap');
  const speedInput = document.getElementById('speed');
  const frictionInput = document.getElementById('friction');
  const speedValue = document.getElementById('speedValue');
  const frictionValue = document.getElementById('frictionValue');
  const playButton = document.getElementById('playButton');
  const stepButton = document.getElementById('stepButton');
  const resetButton = document.getElementById('resetButton');
  const statusChip = document.getElementById('statusChip');
  const statusText = document.getElementById('statusText');
  const stressValue = document.getElementById('stressValue');
  const stressMeter = document.getElementById('stressMeter');
  const mountainValue = document.getElementById('mountainValue');
  const magnitudeValue = document.getElementById('magnitudeValue');
  const eventCount = document.getElementById('eventCount');
  const timeValue = document.getElementById('timeValue');
  const quakeBanner = document.getElementById('quakeBanner');
  const quakeMagnitude = document.getElementById('quakeMagnitude');
  const explanationText = document.getElementById('explanationText');
  const processSteps = [...document.querySelectorAll('.process-step')];

  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const formatter = new Intl.NumberFormat('es-CO', { minimumFractionDigits: 1, maximumFractionDigits: 1 });
  const state = {
    running: false,
    stress: 12,
    mountain: 0.3,
    time: 0,
    events: 0,
    magnitude: null,
    quakeAge: 99,
    phase: 0,
    compression: 0,
    lastFrame: performance.now()
  };

  let css = {};
  let displayWidth = 0;
  let displayHeight = 0;
  let animationId = 0;

  function refreshColors() {
    const s = getComputedStyle(document.documentElement);
    css = {
      bg: '#081923',
      crust: '#6b4939',
      crustLight: '#a77656',
      crustDark: '#3d2b28',
      mantle: '#331d24',
      mantleHot: '#8f3d35',
      cyan: s.getPropertyValue('--cyan').trim(),
      yellow: s.getPropertyValue('--yellow').trim(),
      orange: s.getPropertyValue('--orange').trim(),
      red: s.getPropertyValue('--red').trim(),
      muted: s.getPropertyValue('--muted').trim(),
      text: s.getPropertyValue('--text').trim()
    };
  }

  function resizeCanvas() {
    const rect = canvas.getBoundingClientRect();
    const dpr = Math.min(window.devicePixelRatio || 1, 2);
    displayWidth = Math.max(320, rect.width);
    displayHeight = rect.height;
    canvas.width = Math.round(displayWidth * dpr);
    canvas.height = Math.round(displayHeight * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    draw();
  }

  function roundedRect(x, y, w, h, r) {
    ctx.beginPath();
    ctx.roundRect(x, y, w, h, r);
  }

  function mountainSurface(center, groundY, side, height, seedOffset = 0) {
    const points = [];
    const count = 28;
    const start = side < 0 ? 0 : center;
    const end = side < 0 ? center : displayWidth;
    for (let i = 0; i <= count; i++) {
      const x = start + (end - start) * (i / count);
      const distance = Math.abs(x - center);
      const range = Math.min(displayWidth * .29, 260);
      const envelope = Math.max(0, 1 - distance / range);
      const centralRise = Math.pow(envelope, 1.6) * height;
      const detail = envelope * (Math.sin(x * .051 + seedOffset) * 5 + Math.sin(x * .113) * 2.5);
      points.push([x, groundY - centralRise - detail]);
    }
    return points;
  }

  function drawPlate(points, groundY, side) {
    const center = displayWidth / 2;
    ctx.beginPath();
    ctx.moveTo(points[0][0], points[0][1]);
    points.slice(1).forEach(([x, y]) => ctx.lineTo(x, y));
    if (side < 0) {
      ctx.lineTo(center + 24, groundY + 104);
      ctx.lineTo(0, groundY + 94);
    } else {
      ctx.lineTo(displayWidth, groundY + 94);
      ctx.lineTo(center - 24, groundY + 104);
    }
    ctx.closePath();
    const gradient = ctx.createLinearGradient(0, groundY - 100, 0, groundY + 120);
    gradient.addColorStop(0, css.crustLight);
    gradient.addColorStop(.24, css.crust);
    gradient.addColorStop(1, css.crustDark);
    ctx.fillStyle = gradient;
    ctx.fill();

    ctx.save();
    ctx.clip();
    ctx.strokeStyle = 'rgba(255,225,187,.12)';
    ctx.lineWidth = 1;
    for (let y = groundY + 25; y < groundY + 96; y += 25) {
      ctx.beginPath();
      ctx.moveTo(side < 0 ? 0 : center - 10, y);
      ctx.lineTo(side < 0 ? center + 10 : displayWidth, y + (side * 5));
      ctx.stroke();
    }
    ctx.restore();

    ctx.beginPath();
    points.forEach(([x, y], i) => i ? ctx.lineTo(x, y) : ctx.moveTo(x, y));
    ctx.strokeStyle = '#c29570';
    ctx.lineWidth = 2;
    ctx.stroke();
  }

  function drawRockTexture(groundY) {
    ctx.save();
    ctx.fillStyle = 'rgba(255,220,180,.15)';
    for (let i = 0; i < 35; i++) {
      const x = (i * 91 + 23) % displayWidth;
      const y = groundY + 18 + ((i * 47) % 70);
      const size = 1.2 + (i % 3) * .55;
      ctx.beginPath();
      ctx.arc(x, y, size, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.restore();
  }

  function drawMantle(groundY, t) {
    const gradient = ctx.createLinearGradient(0, groundY + 80, 0, displayHeight);
    gradient.addColorStop(0, css.mantle);
    gradient.addColorStop(1, '#170f18');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, groundY + 72, displayWidth, displayHeight - groundY);

    ctx.save();
    ctx.globalAlpha = .34;
    for (let i = 0; i < 12; i++) {
      const x = ((i * 137 + t * (i % 2 ? 3 : -2)) % (displayWidth + 80)) - 40;
      const y = groundY + 125 + (i % 3) * 38;
      ctx.fillStyle = i % 2 ? '#b34835' : '#61302d';
      ctx.beginPath();
      ctx.ellipse(x, y, 24 + (i % 4) * 6, 5, -.12, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.restore();
  }

  function drawArrows(groundY, t) {
    const pulse = state.running ? Math.sin(t * 4) * 4 : 0;
    const y = groundY - 48;
    ctx.save();
    ctx.strokeStyle = css.cyan;
    ctx.fillStyle = css.cyan;
    ctx.lineWidth = 2;
    ctx.globalAlpha = .9;
    [[displayWidth * .24 + pulse, 1], [displayWidth * .76 - pulse, -1]].forEach(([x, dir]) => {
      ctx.beginPath();
      ctx.moveTo(x - dir * 28, y);
      ctx.lineTo(x + dir * 28, y);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(x + dir * 28, y);
      ctx.lineTo(x + dir * 19, y - 6);
      ctx.lineTo(x + dir * 19, y + 6);
      ctx.closePath();
      ctx.fill();
    });
    ctx.restore();
  }

  function drawFault(center, groundY) {
    const glow = Math.min(1, state.stress / 100);
    ctx.save();
    ctx.strokeStyle = `rgba(255, 107, 94, ${.2 + glow * .65})`;
    ctx.lineWidth = 2 + glow * 2;
    ctx.shadowColor = css.red;
    ctx.shadowBlur = glow * 18;
    ctx.beginPath();
    ctx.moveTo(center - 2, groundY - 36 - state.mountain * 9);
    ctx.lineTo(center + 9, groundY + 10);
    ctx.lineTo(center - 11, groundY + 42);
    ctx.lineTo(center + 7, groundY + 80);
    ctx.stroke();
    ctx.restore();

    if (state.stress > 28) {
      const x = center + 7;
      const y = groundY + 78;
      ctx.save();
      ctx.fillStyle = css.yellow;
      ctx.shadowColor = css.red;
      ctx.shadowBlur = 12;
      ctx.beginPath();
      ctx.arc(x, y, 4.5, 0, Math.PI * 2);
      ctx.fill();
      ctx.restore();

      ctx.fillStyle = 'rgba(245,251,252,.72)';
      ctx.font = '600 10px Inter, system-ui, sans-serif';
      ctx.fillText('Hipocentro', x + 11, y + 3);
    }
  }

  function drawWaves(center, groundY) {
    if (state.quakeAge > 2.5) return;
    const progress = state.quakeAge / 2.5;
    ctx.save();
    ctx.strokeStyle = css.red;
    ctx.lineWidth = 2;
    for (let i = 0; i < 4; i++) {
      const p = Math.max(0, progress - i * .12);
      const radius = 18 + p * Math.min(displayWidth * .35, 270);
      ctx.globalAlpha = Math.max(0, .75 - p * .75);
      ctx.beginPath();
      ctx.ellipse(center, groundY + 72, radius, radius * .42, 0, Math.PI, Math.PI * 2);
      ctx.stroke();
    }
    ctx.restore();
  }

  function draw() {
    if (!displayWidth || !displayHeight) return;
    const t = performance.now() / 1000;
    const center = displayWidth / 2;
    const groundY = displayHeight * .47;
    const mountainHeight = 15 + Math.min(100, state.mountain * 17);
    const shake = state.quakeAge < .8 && !reducedMotion ? (1 - state.quakeAge / .8) * 5 : 0;

    ctx.save();
    ctx.clearRect(0, 0, displayWidth, displayHeight);
    ctx.translate(Math.sin(t * 80) * shake, Math.cos(t * 63) * shake * .5);

    const sky = ctx.createLinearGradient(0, 0, 0, groundY + 40);
    sky.addColorStop(0, '#071923');
    sky.addColorStop(1, '#123540');
    ctx.fillStyle = sky;
    ctx.fillRect(-8, -8, displayWidth + 16, groundY + 90);

    ctx.globalAlpha = .2;
    ctx.fillStyle = '#b7f1ef';
    for (let i = 0; i < 18; i++) {
      ctx.beginPath();
      ctx.arc((i * 103 + 37) % displayWidth, 70 + ((i * 41) % 65), 1, 0, Math.PI * 2);
      ctx.fill();
    }
    ctx.globalAlpha = 1;

    drawMantle(groundY, t);
    const left = mountainSurface(center, groundY, -1, mountainHeight, 0);
    const right = mountainSurface(center, groundY, 1, mountainHeight * .96, 1.3);
    drawPlate(left, groundY, -1);
    drawPlate(right, groundY, 1);
    drawRockTexture(groundY);

    const ridge = left.slice(-7).concat(right.slice(0, 8));
    ctx.beginPath();
    ridge.forEach(([x, y], i) => i ? ctx.lineTo(x, y) : ctx.moveTo(x, y));
    ctx.strokeStyle = 'rgba(226, 221, 199, .82)';
    ctx.lineWidth = 2;
    ctx.stroke();

    if (state.mountain > 1.7) {
      ctx.save();
      ctx.fillStyle = 'rgba(225,239,231,.78)';
      ctx.beginPath();
      const peakY = groundY - mountainHeight - Math.sin(center * .051) * 5;
      ctx.moveTo(center - 35, peakY + 28);
      ctx.lineTo(center, peakY);
      ctx.lineTo(center + 28, peakY + 25);
      ctx.lineTo(center + 9, peakY + 17);
      ctx.lineTo(center - 3, peakY + 24);
      ctx.lineTo(center - 14, peakY + 15);
      ctx.closePath();
      ctx.fill();
      ctx.restore();
    }

    drawFault(center, groundY);
    drawWaves(center, groundY);
    drawArrows(groundY, t);

    ctx.fillStyle = 'rgba(245,251,252,.55)';
    ctx.font = '500 10px Inter, system-ui, sans-serif';
    ctx.fillText('ASTENOSFERA', displayWidth - 105, displayHeight - 23);
    ctx.restore();
  }

  function threshold() {
    return 58 + Number(frictionInput.value) * .38;
  }

  function triggerQuake() {
    const friction = Number(frictionInput.value) / 100;
    const speed = Number(speedInput.value);
    state.magnitude = Math.min(8.7, 5.0 + friction * 3.2 + speed * .045);
    state.events += 1;
    state.quakeAge = 0;
    state.stress = 16 + friction * 9;
    state.mountain = Math.min(6.8, state.mountain + .18 + friction * .18);
    state.phase = 2;
    updateUI();
  }

  function advance(seconds) {
    const speed = Number(speedInput.value);
    const friction = Number(frictionInput.value) / 100;
    state.time += seconds * speed * .007;
    state.compression += seconds * speed * .012;
    state.stress += seconds * (.95 + speed * .14) * (.55 + friction * .9);
    state.mountain = Math.min(6.8, state.mountain + seconds * speed * .0014);
    state.quakeAge += seconds;

    if (state.stress >= threshold()) triggerQuake();
    else if (state.quakeAge < 1.2) state.phase = 2;
    else if (state.mountain >= 1.2) state.phase = 3;
    else if (state.stress >= 38) state.phase = 1;
    else state.phase = 0;
  }

  function setRangeProgress(input) {
    const value = ((Number(input.value) - Number(input.min)) / (Number(input.max) - Number(input.min))) * 100;
    input.style.setProperty('--range-progress', `${value}%`);
  }

  function updateUI() {
    speedValue.textContent = `${speedInput.value} cm/año`;
    frictionValue.textContent = `${frictionInput.value} %`;
    stressValue.textContent = `${Math.round(state.stress)} %`;
    stressMeter.style.width = `${Math.min(100, state.stress)}%`;
    mountainValue.textContent = `${formatter.format(state.mountain)} km`;
    magnitudeValue.textContent = state.magnitude ? `M ${formatter.format(state.magnitude)}` : 'Sin eventos';
    eventCount.textContent = `${state.events} ${state.events === 1 ? 'evento registrado' : 'eventos registrados'}`;
    timeValue.textContent = `${formatter.format(state.time)} millones de años`;
    playButton.querySelector('span').textContent = state.running ? 'Pausar' : 'Iniciar';
    playButton.querySelector('svg').innerHTML = state.running
      ? '<path d="M8 5h3v14H8Zm5 0h3v14h-3Z"/>'
      : '<path d="m8 5 11 7-11 7Z"/>';

    statusChip.className = 'status-chip';
    if (state.quakeAge < 1.3) {
      statusChip.classList.add('is-quake');
      statusText.textContent = 'Ruptura sísmica';
    } else if (state.running) {
      statusChip.classList.add('is-running');
      statusText.textContent = state.stress > 55 ? 'Tensión alta' : 'Placas en movimiento';
    } else {
      statusText.textContent = 'En pausa';
    }

    const showBanner = state.quakeAge < 2.2 && state.magnitude;
    quakeBanner.classList.toggle('is-visible', Boolean(showBanner));
    quakeBanner.setAttribute('aria-hidden', showBanner ? 'false' : 'true');
    quakeMagnitude.textContent = state.magnitude ? `M ${formatter.format(state.magnitude)}` : 'M 0,0';

    const explanations = [
      'Las placas se empujan lentamente. La falla permanece trabada por la fricción y comienza a almacenar energía elástica.',
      'La roca se deforma mientras aumenta la tensión. Cuanto mayor es la fricción, más energía puede acumularse antes de la ruptura.',
      'La tensión supera la resistencia de la roca. La falla se desliza bruscamente y libera ondas sísmicas: ocurre un terremoto.',
      'La compresión engrosa y pliega la corteza. Repetido durante millones de años, este proceso eleva una cordillera.'
    ];
    explanationText.textContent = explanations[state.phase];
    processSteps.forEach((step, index) => {
      step.classList.toggle('is-active', index === state.phase);
      step.classList.toggle('is-complete', index < state.phase || (state.events > 0 && index === 2));
    });
    setRangeProgress(speedInput);
    setRangeProgress(frictionInput);
  }

  function animate(now) {
    const dt = Math.min(.05, (now - state.lastFrame) / 1000);
    state.lastFrame = now;
    if (state.running) advance(dt * 3.5);
    else state.quakeAge += dt;
    updateUI();
    draw();
    animationId = requestAnimationFrame(animate);
  }

  playButton.addEventListener('click', () => {
    state.running = !state.running;
    state.lastFrame = performance.now();
    updateUI();
  });

  stepButton.addEventListener('click', () => {
    advance(2.8);
    updateUI();
    draw();
  });

  resetButton.addEventListener('click', () => {
    Object.assign(state, {
      running: false,
      stress: 12,
      mountain: .3,
      time: 0,
      events: 0,
      magnitude: null,
      quakeAge: 99,
      phase: 0,
      compression: 0,
      lastFrame: performance.now()
    });
    updateUI();
    draw();
  });

  [speedInput, frictionInput].forEach(input => input.addEventListener('input', updateUI));
  window.addEventListener('resize', resizeCanvas);

  refreshColors();
  updateUI();
  resizeCanvas();
  cancelAnimationFrame(animationId);
  animationId = requestAnimationFrame(animate);
})();
