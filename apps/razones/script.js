(() => {
  const state = { ratio: 'seno', level: 'facil', mode: 'razon', opposite: 3, adjacent: 4, angle: 35, knownSide: 'hypotenuse', knownValue: 10, targetSide: 'opposite', unit: 'm', storyTheme: 'aleatorio', multipleChoice: false, options: [], optionKey: '', storyIndex: 0 };
  const names = { seno: 'Seno', coseno: 'Coseno', tangente: 'Tangente', cotangente: 'Cotangente', secante: 'Secante', cosecante: 'Cosecante' };
  const definitions = {
    seno: { short: 'opuesto ÷ hipotenusa', numerator: 'opposite', denominator: 'hypotenuse', symbol: 'sen θ' },
    coseno: { short: 'adyacente ÷ hipotenusa', numerator: 'adjacent', denominator: 'hypotenuse', symbol: 'cos θ' },
    tangente: { short: 'opuesto ÷ adyacente', numerator: 'opposite', denominator: 'adjacent', symbol: 'tan θ' },
    cotangente: { short: 'adyacente ÷ opuesto', numerator: 'adjacent', denominator: 'opposite', symbol: 'cot θ' },
    secante: { short: 'hipotenusa ÷ adyacente', numerator: 'hypotenuse', denominator: 'adjacent', symbol: 'sec θ' },
    cosecante: { short: 'hipotenusa ÷ opuesto', numerator: 'hypotenuse', denominator: 'opposite', symbol: 'csc θ' }
  };
  const levels = {
    facil: { help: 'Medidas enteras y una razón sencilla.', values: [[3,4],[5,12],[8,15],[6,8]] },
    medio: { help: 'Combinaciones variadas para simplificar o aproximar.', values: [[5,7],[7,9],[9,12],[11,6],[8,11]] },
    dificil: { help: 'Decimales y resultados que requieren aproximación.', values: [[4.5,7.5],[6.5,9],[11.5,4],[8.5,13.5],[14,9.5]] }
  };
  const $ = (selector) => document.querySelector(selector);
  const format = (value, decimals = 2) => Number(value.toFixed(decimals)).toString().replace('.', ',');
  const fraction = (value) => Number.isInteger(value) ? String(value) : String(value).replace('.', ',');
  const withUnit = (value) => `${value}${state.unit ? ` ${state.unit}` : ''}`;
  const sideNames = { opposite: 'cateto opuesto', adjacent: 'cateto adyacente', hypotenuse: 'hipotenusa' };
  const stories = [
    { id: 'campo', text: 'Un campesino está diseñando un camino inclinado para llegar a su cultivo. Usa un croquis en forma de triángulo rectángulo.' },
    { id: 'escalera', text: 'Una escalera se apoya contra una pared y forma un triángulo rectángulo con el suelo.' },
    { id: 'rampa', text: 'Para construir una rampa de acceso, el encargado prepara un plano con un triángulo rectángulo.' },
    { id: 'edificio', text: 'Desde un punto de la plaza, una estudiante observa la parte alta de un edificio y representa la situación con un triángulo rectángulo.' },
    { id: 'arbol', text: 'Un grupo de ciencias quiere estimar la altura de un árbol sin escalarlo y dibuja un triángulo rectángulo.' },
    { id: 'cometa', text: 'Un niño eleva una cometa con una cuerda tensa y analiza su posición con un triángulo rectángulo.' },
    { id: 'dron', text: 'Un dron asciende desde un punto de despegue; su recorrido y la distancia al suelo forman un triángulo rectángulo.' },
    { id: 'carretera', text: 'Un ingeniero revisa la inclinación de una carretera y representa el tramo en un triángulo rectángulo.' },
    { id: 'cancha', text: 'En una cancha deportiva, el entrenador marca una trayectoria y la representa con un triángulo rectángulo.' },
    { id: 'rescate', text: 'Un equipo de rescate calcula una distancia segura en una ladera usando un plano triangular.' },
    { id: 'barco', text: 'Un navegante observa un faro desde su barco y organiza sus datos en un triángulo rectángulo.' }
  ];
  const getMeasures = () => {
    if (state.mode === 'razon') return { opposite: state.opposite, adjacent: state.adjacent, hypotenuse: Math.hypot(state.opposite, state.adjacent) };
    const radians = state.angle * Math.PI / 180;
    if (state.knownSide === 'hypotenuse') return { opposite: state.knownValue * Math.sin(radians), adjacent: state.knownValue * Math.cos(radians), hypotenuse: state.knownValue };
    if (state.knownSide === 'adjacent') return { opposite: state.knownValue * Math.tan(radians), adjacent: state.knownValue, hypotenuse: state.knownValue / Math.cos(radians) };
    return { opposite: state.knownValue, adjacent: state.knownValue / Math.tan(radians), hypotenuse: state.knownValue / Math.sin(radians) };
  };
  const activeSides = () => state.mode === 'lado' ? [definitions[state.ratio].numerator, definitions[state.ratio].denominator] : ['opposite', 'adjacent', 'hypotenuse'];
  const currentResult = () => {
    const measures = getMeasures();
    if (state.mode === 'lado') return measures[state.targetSide];
    const def = definitions[state.ratio];
    return measures[def.numerator] / measures[def.denominator];
  };
  const setAngleBadge = (degrees) => {
    const group = $('#angleBadge'), rect = group.querySelector('rect'), label = group.querySelector('text');
    const text = `θ = ${format(degrees, 1)}°`, width = 82;
    group.setAttribute('transform', 'translate(120 260)'); rect.setAttribute('width', width); rect.setAttribute('height', 27);
    label.setAttribute('x', width / 2); label.setAttribute('y', 15); label.textContent = text;
  };
  const setLabel = (id, x, y, text) => {
    const group = $(id), rect = group.querySelector('rect'), label = group.querySelector('text');
    const width = Math.max(94, text.length * 6.8 + 20);
    group.setAttribute('transform', `translate(${x - width / 2} ${y - 13})`);
    rect.setAttribute('width', width); rect.setAttribute('height', 27);
    label.setAttribute('x', width / 2); label.setAttribute('y', 15); label.textContent = text;
  };
  const updateTriangle = () => {
    const { opposite, adjacent, hypotenuse } = getMeasures();
    const baseStart = 95, baseEnd = 480, baseY = 345, maxHeight = 250;
    const scale = Math.min(1, maxHeight / (opposite / adjacent * (baseEnd - baseStart)));
    const topY = baseY - Math.max(72, opposite / adjacent * (baseEnd - baseStart) * scale);
    const d = `M ${baseStart} ${baseY} L ${baseEnd} ${baseY} L ${baseEnd} ${topY} Z`;
    $('#triangleArea').setAttribute('d', d);
    $('#oppositeLine').setAttribute('d', `M ${baseEnd} ${baseY} L ${baseEnd} ${topY}`);
    $('#adjacentLine').setAttribute('d', `M ${baseStart} ${baseY} L ${baseEnd} ${baseY}`);
    $('#hypotenuseLine').setAttribute('d', `M ${baseStart} ${baseY} L ${baseEnd} ${topY}`);
    const right = `M ${baseEnd - 33} ${baseY} L ${baseEnd - 33} ${baseY - 33} L ${baseEnd} ${baseY - 33}`;
    document.querySelector('.right-angle').setAttribute('d', right);
    const degrees = state.mode === 'lado' ? state.angle : Math.atan(opposite / adjacent) * 180 / Math.PI;
    $('#angleValue').textContent = `${format(degrees, 1)}°`;
    $('#angleArc').setAttribute('d', `M ${baseStart + 45} ${baseY} A 45 45 0 0 1 ${baseStart + 40} ${baseY - 21}`);
    $('#thetaLabel').setAttribute('x', baseStart + 47); $('#thetaLabel').setAttribute('y', baseY - 29); $('#thetaLabel').textContent = 'θ'; setAngleBadge(degrees);
    const display = (side, value) => state.mode === 'lado' && state.targetSide === side ? '?' : format(value);
    const visual = (side, value) => state.mode === 'lado' && state.targetSide === side ? '?' : withUnit(format(value));
    setLabel('#adjacentLabel', (baseStart + baseEnd) / 2, baseY + 34, `adyacente = ${visual('adjacent', adjacent)}`);
    setLabel('#oppositeLabel', baseEnd + 57, (baseY + topY) / 2, `opuesto = ${visual('opposite', opposite)}`);
    setLabel('#hypotenuseLabel', baseStart + (baseEnd - baseStart) * .45, topY + (baseY - topY) * .34 - 26, `hipotenusa = ${visual('hypotenuse', hypotenuse)}`);
    $('#oppositeMeasure').textContent = `Opuesto = ${visual('opposite', opposite)}`;
    $('#adjacentMeasure').textContent = `Adyacente = ${visual('adjacent', adjacent)}`;
    $('#hypotenuseMeasure').textContent = `Hipotenusa = ${visual('hypotenuse', hypotenuse)}`;
    const visible = activeSides();
    ['opposite', 'adjacent', 'hypotenuse'].forEach(side => {
      $(`#${side}Label`).setAttribute('display', visible.includes(side) ? 'inline' : 'none');
      $(`#${side}Measure`).hidden = !visible.includes(side);
      document.querySelector(`.legend [data-side="${side}"]`).hidden = !visible.includes(side);
    });
  };
  const sideEquation = () => {
    const measures = getMeasures(), target = state.targetSide, known = state.knownSide, definition = definitions[state.ratio];
    const targetLetter = target === 'opposite' ? 'O' : target === 'adjacent' ? 'A' : 'H';
    const operation = target === definition.numerator ? '×' : '÷';
    const result = format(measures[target]);
    return { symbol: definition.symbol, targetLetter, operation, result, equation: `${targetLetter} = ${fraction(state.knownValue)} ${operation} ${definition.symbol} ${format(state.angle, 1)}° = ${result}` };
  };
  const updateStory = () => {
    const base = state.storyTheme === 'aleatorio' ? stories[state.storyIndex % stories.length] : stories.find(story => story.id === state.storyTheme);
    if (state.mode === 'lado') {
      $('#storyText').textContent = `${base.text} El ángulo θ mide ${format(state.angle, 1)}° y el ${sideNames[state.knownSide]} mide ${withUnit(fraction(state.knownValue))}. ¿Cuánto mide el ${sideNames[state.targetSide]}?`;
      return;
    }
    $('#storyText').textContent = `${base.text} A partir de las medidas del croquis, calcula el ${names[state.ratio].toLowerCase()} del ángulo θ.`;
  };
  const updateChoiceOptions = () => {
    const container = $('#choiceOptions');
    container.hidden = !state.multipleChoice;
    if (!state.multipleChoice) return;
    const answer = currentResult();
    const key = [state.mode, state.ratio, state.angle, state.knownSide, state.knownValue, state.targetSide, state.opposite, state.adjacent, answer].join('|');
    if (state.optionKey !== key) {
      const gap = answer < 1 ? .12 : Math.max(.8, answer * .16);
      const candidates = [answer, Math.max(answer < 1 ? .05 : .1, answer + gap), Math.max(answer < 1 ? .03 : .1, answer - gap), answer + gap * 2];
      const unique = [];
      candidates.forEach(value => { if (!unique.some(item => Math.abs(item - value) < .01)) unique.push(value); });
      while (unique.length < 4) unique.push(answer + (unique.length + 1) * gap * 1.8);
      state.options = unique.slice(0, 4).sort(() => Math.random() - .5);
      state.optionKey = key;
      $('#choiceFeedback').textContent = '';
      $('#choiceFeedback').className = '';
    }
    const list = $('#choiceList'); list.innerHTML = '';
    state.options.forEach((value, index) => { const button = document.createElement('button'); const letter = document.createElement('b'); const optionText = state.mode === 'lado' ? withUnit(format(value)) : format(value); button.type = 'button'; button.dataset.value = value; letter.textContent = `${String.fromCharCode(65 + index)})`; button.append(letter, document.createTextNode(` ${optionText}`)); list.appendChild(button); });
  };
  const updateExercise = () => {
    const measures = getMeasures(); const def = definitions[state.ratio];
    updateStory();
    if (state.mode === 'lado') {
      const equation = sideEquation();
      $('#formulaName').textContent = `Encuentra el ${sideNames[state.targetSide]}`;
      $('#formulaText').textContent = `usa ${equation.symbol} con θ = ${format(state.angle, 1)}°`;
      $('#formula').innerHTML = `<span>${equation.targetLetter}</span><b>=</b><span>${fraction(state.knownValue)} ${equation.operation} ${equation.symbol}</span><b>=</b><strong>${equation.result}</strong>`;
      $('#challengeTag').textContent = `NIVEL ${state.level.toUpperCase()}`;
      $('#challengeText').textContent = `Si θ = ${format(state.angle, 1)}° y el ${sideNames[state.knownSide]} mide ${withUnit(fraction(state.knownValue))}, encuentra el ${sideNames[state.targetSide]}.`;
      $('#solutionIntro').textContent = `Relacionamos el ${sideNames[state.knownSide]} con el ${sideNames[state.targetSide]} usando ${equation.symbol}.`;
      $('#solutionMath').textContent = equation.equation;
      $('#solutionTip').textContent = 'Redondea el resultado a dos decimales si es necesario.';
      updateChoiceOptions();
      return;
    }
    const top = measures[def.numerator], bottom = measures[def.denominator], result = currentResult();
    $('#formulaName').textContent = names[state.ratio]; $('#formulaText').textContent = def.short;
    $('#formula').innerHTML = `<span>${def.symbol}</span><b>=</b><span class="fraction"><i>${fraction(top)}</i><i>${fraction(bottom)}</i></span><b>=</b><strong>${format(result)}</strong>`;
    $('#challengeTag').textContent = `NIVEL ${state.level.toUpperCase()}`;
    $('#challengeText').textContent = `En el triángulo mostrado, calcula el ${names[state.ratio].toLowerCase()} del ángulo θ.`;
    $('#solutionIntro').textContent = `Para hallar el ${names[state.ratio].toLowerCase()}, usamos ${def.short}.`;
    $('#solutionMath').textContent = `${def.symbol} = ${fraction(top)} / ${fraction(bottom)} = ${format(result)}`;
    $('#solutionTip').textContent = state.ratio === 'seno' || state.ratio === 'tangente' ? 'Busca primero el lado opuesto al ángulo θ.' : 'El lado adyacente toca al ángulo θ y no es la hipotenusa.';
    updateChoiceOptions();
  };
  const updateControls = () => {
    ['opposite','adjacent'].forEach((key) => { $(`#${key}`).value = state[key]; $(`#${key}Number`).value = state[key]; $(`#${key}Output`).textContent = fraction(state[key]); });
    document.querySelectorAll('.ratio-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.ratio === state.ratio));
    document.querySelectorAll('.level-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.level === state.level));
    document.querySelectorAll('.mode-btn').forEach(btn => btn.classList.toggle('active', btn.dataset.mode === state.mode));
    $('#multipleChoice').checked = state.multipleChoice;
    $('#unitInput').value = state.unit;
    $('#storyTemplate').value = state.storyTheme;
    $('#levelHelp').textContent = levels[state.level].help;
    $('#ratioConfig').hidden = false; $('#measurementsConfig').hidden = state.mode === 'lado'; $('#sideConfig').hidden = state.mode !== 'lado';
    $('#angle').value = state.angle; $('#angleNumber').value = state.angle; $('#angleOutput').textContent = `${format(state.angle, 1)}°`;
    const definition = definitions[state.ratio], usableSides = [definition.numerator, definition.denominator];
    if (!usableSides.includes(state.knownSide)) state.knownSide = definition.denominator;
    if (!usableSides.includes(state.targetSide) || state.targetSide === state.knownSide) state.targetSide = usableSides.find(side => side !== state.knownSide);
    const known = $('#knownSide'); known.innerHTML = '';
    usableSides.forEach(side => { const option = document.createElement('option'); option.value = side; option.textContent = sideNames[side].charAt(0).toUpperCase() + sideNames[side].slice(1); known.appendChild(option); });
    known.value = state.knownSide; $('#knownValue').value = state.knownValue; $('#knownValueNumber').value = state.knownValue; $('#knownValueOutput').textContent = fraction(state.knownValue);
    const target = $('#targetSide'); target.innerHTML = '';
    usableSides.filter(side => side !== state.knownSide).forEach(side => { const option = document.createElement('option'); option.value = side; option.textContent = sideNames[side].charAt(0).toUpperCase() + sideNames[side].slice(1); target.appendChild(option); });
    target.value = state.targetSide;
    $('#ratioPath').textContent = `Con ${definition.symbol} se usan ${sideNames[definition.numerator]} e ${sideNames[definition.denominator]}.`;
  };
  const render = () => { updateControls(); updateTriangle(); updateExercise(); };
  const setMeasure = (key, value) => { const parsed = Number(value); if (Number.isFinite(parsed)) { state[key] = Math.max(.5, Math.min(24, parsed)); render(); } };
  document.querySelectorAll('.ratio-btn').forEach(button => button.addEventListener('click', () => { state.ratio = button.dataset.ratio; $('#studentAnswer').value = ''; $('#feedback').textContent = ''; render(); }));
  document.querySelectorAll('.level-btn').forEach(button => button.addEventListener('click', () => { state.level = button.dataset.level; render(); }));
  document.querySelectorAll('.mode-btn').forEach(button => button.addEventListener('click', () => { state.mode = button.dataset.mode; $('#studentAnswer').value = ''; $('#feedback').textContent = ''; render(); }));
  $('#multipleChoice').addEventListener('change', event => { state.multipleChoice = event.target.checked; state.optionKey = ''; render(); });
  $('#unitInput').addEventListener('input', event => { state.unit = event.target.value.trim(); render(); });
  $('#storyTemplate').addEventListener('change', event => { state.storyTheme = event.target.value; if (state.storyTheme === 'aleatorio') state.storyIndex = Math.floor(Math.random() * stories.length); render(); });
  ['opposite','adjacent'].forEach(key => { $(`#${key}`).addEventListener('input', (event) => setMeasure(key, event.target.value)); $(`#${key}Number`).addEventListener('change', (event) => setMeasure(key, event.target.value)); });
  $('#angle').addEventListener('input', event => { state.angle = Number(event.target.value); render(); }); $('#angleNumber').addEventListener('change', event => { state.angle = Math.max(5, Math.min(85, Number(event.target.value) || 35)); render(); });
  $('#knownValue').addEventListener('input', event => { state.knownValue = Number(event.target.value); render(); }); $('#knownValueNumber').addEventListener('change', event => { state.knownValue = Math.max(1, Math.min(24, Number(event.target.value) || 10)); render(); });
  $('#knownSide').addEventListener('change', event => { state.knownSide = event.target.value; if (state.targetSide === state.knownSide) state.targetSide = state.knownSide === 'hypotenuse' ? 'opposite' : 'hypotenuse'; render(); }); $('#targetSide').addEventListener('change', event => { state.targetSide = event.target.value; render(); });
  $('#newExercise').addEventListener('click', () => { if (state.mode === 'razon') { const options = levels[state.level].values; const choice = options[Math.floor(Math.random() * options.length)]; state.opposite = choice[0]; state.adjacent = choice[1]; } else { const angles = state.level === 'facil' ? [30,45,60] : state.level === 'medio' ? [25,35,40,50,55] : [17.5,27.5,37.5,52.5,67.5]; state.angle = angles[Math.floor(Math.random() * angles.length)]; state.knownValue = [5,6,8,10,12][Math.floor(Math.random() * 5)]; } state.storyIndex = Math.floor(Math.random() * stories.length); state.optionKey = ''; $('#studentAnswer').value = ''; $('#feedback').textContent = ''; $('#solution').hidden = true; $('#solutionToggle').classList.remove('open'); render(); });
  $('#solutionToggle').addEventListener('click', () => { const solution = $('#solution'); solution.hidden = !solution.hidden; $('#solutionToggle').classList.toggle('open', !solution.hidden); });
  const validateAnswer = (value) => {
    const raw = String(value ?? $('#studentAnswer').value).trim().replace(',', '.'); const feedback = $('#feedback');
    if (!raw) { feedback.textContent = 'Escribe una respuesta para comprobarla.'; feedback.className = 'feedback incorrect'; return false; }
    let answer;
    if (raw.includes('/')) { const [a,b] = raw.split('/').map(Number); answer = a / b; } else { answer = Number(raw); }
    if (!Number.isFinite(answer)) { feedback.textContent = 'Usa un número decimal o una fracción, por ejemplo 0,60 o 3/5.'; feedback.className = 'feedback incorrect'; return false; }
    if (Math.abs(answer - currentResult()) <= .012) { feedback.textContent = '¡Correcto! Identificaste los lados adecuados.'; feedback.className = 'feedback correct'; return true; }
    feedback.textContent = 'Aún no. Revisa qué lados corresponden a esta razón.'; feedback.className = 'feedback incorrect'; return false;
  };
  $('#choiceList').addEventListener('click', event => {
    const button = event.target.closest('button'); if (!button) return;
    const correct = validateAnswer(button.dataset.value);
    $('#studentAnswer').value = format(Number(button.dataset.value));
    document.querySelectorAll('#choiceList button').forEach(item => item.classList.toggle('selected', item === button));
    const choiceFeedback = $('#choiceFeedback');
    choiceFeedback.textContent = correct ? '¡Correcto! Esa es la respuesta.' : 'No es la respuesta. Observa de nuevo los datos.';
    choiceFeedback.className = correct ? 'correct' : 'incorrect';
  });
  $('#checkAnswer').addEventListener('click', validateAnswer); $('#studentAnswer').addEventListener('keydown', event => { if (event.key === 'Enter') validateAnswer(); });
  const updateCalculator = () => {
    const degrees = Number($('#calculatorAngle').value);
    if (!Number.isFinite(degrees)) {
      ['#calcSin', '#calcCos', '#calcTan', '#calcCot', '#calcCsc'].forEach(id => { $(id).textContent = '—'; });
      $('#calculatorNote').textContent = 'Escribe un ángulo válido en grados.';
      return;
    }
    const radians = degrees * Math.PI / 180, sine = Math.sin(radians), cosine = Math.cos(radians);
    const clean = value => Math.abs(value) < 1e-10 ? 0 : value;
    const value = number => format(clean(number), 4);
    const quotient = (numerator, denominator) => Math.abs(denominator) < 1e-10 ? 'No definida' : value(numerator / denominator);
    $('#calcSin').textContent = value(sine);
    $('#calcCos').textContent = value(cosine);
    $('#calcTan').textContent = quotient(sine, cosine);
    $('#calcCot').textContent = quotient(cosine, sine);
    $('#calcCsc').textContent = quotient(1, sine);
    $('#calculatorNote').textContent = `Resultados para θ = ${format(degrees, 2)}°. Se aproximan a cuatro decimales.`;
  };
  $('#calculatorAngle').addEventListener('input', updateCalculator);
  document.querySelectorAll('.angle-presets button').forEach(button => button.addEventListener('click', () => { $('#calculatorAngle').value = button.dataset.angle; updateCalculator(); }));
  updateCalculator();
  render();
})();
