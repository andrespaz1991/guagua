/* All images stay in the browser. No network requests or third-party dependencies. */
(() => {
  'use strict';
  const $ = id => document.getElementById(id);
  const Engine = window.SliderEngine;
  const defaults = { mode: 'blur', color: '#e7ece4', blur: 24, zoom: 1, positionX: .5, positionY: .5 };
  const state = { items: [], selected: null, width: Number($('width').value), height: Number($('height').value), defaults: { ...defaults }, sample: null, preview: null, busy: false, valid: true, generation: 0 };
  const modeDescriptions = {
    blur: 'Conserva la imagen completa y rellena con un fondo desenfocado.',
    mirror: 'Completa los bordes reflejando la propia imagen, sin deformarla.',
    color: 'Conserva la imagen completa sobre el color que elijas.',
    transparent: 'Conserva la imagen completa y deja los bordes transparentes.',
    cover: 'Llena todo el espacio. Los bordes que no encajen se recortan.'
  };
  let previewFrame = 0, toastTimer, dragDepth = 0;
  const selected = () => state.items.find(item => item.id === state.selected);
  const settings = () => selected()?.settings || state.defaults;
  const fullOptions = item => ({ width: state.width, height: state.height, ...(item?.settings || settings()) });
  const frame = () => new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 0)));
  const formatBytes = bytes => bytes < 1024 * 1024 ? `${Math.round(bytes / 1024)} KB` : `${(bytes / 1024 / 1024).toFixed(1)} MB`;
  const modeLabel = mode => ({ blur: 'Desenfoque', mirror: 'Reflejo', color: 'Color sólido', transparent: 'Transparente', cover: 'Recorte' })[mode];

  function toast(message, error = false) {
    clearTimeout(toastTimer);
    $('toast').textContent = message;
    $('toast').classList.toggle('error', error);
    $('toast').hidden = false;
    toastTimer = setTimeout(() => { $('toast').hidden = true; }, error ? 8500 : 4500);
  }

  function syncRanges() {
    document.querySelectorAll('input[type=range]').forEach(input => {
      const fraction = (Number(input.value) - Number(input.min)) / (Number(input.max) - Number(input.min));
      input.style.setProperty('--range-fill', `${fraction * 100}%`);
    });
  }

  function refreshDisabled() {
    const noImages = !state.items.length;
    ['downloadAll', 'downloadOne'].forEach(id => { $(id).disabled = state.busy || noImages || !state.valid; });
    $('clearAll').disabled = state.busy || noImages;
    $('applyAll').disabled = state.busy || noImages;
    $('loadDemo')?.toggleAttribute('disabled', state.busy);
    $('fileInput').disabled = state.busy;
    document.querySelector('.settings-panel').inert = state.busy;
    document.querySelector('.position-controls').inert = state.busy;
    $('gallery').inert = state.busy;
    $('resetImage').disabled = state.busy;
    $('dropzone').setAttribute('aria-disabled', String(state.busy));
    document.querySelector('.editor-panel').setAttribute('aria-busy', String(state.busy));
    if (!state.busy) $('downloadLabel').textContent = noImages ? 'Descargar imágenes' : `Descargar ${state.items.length} ${state.items.length === 1 ? 'imagen' : 'imágenes'}`;
  }

  function setBusy(value, message) {
    state.busy = value;
    if (message) $('downloadLabel').textContent = message;
    refreshDisabled();
  }

  function enforceFormat(notify = false) {
    if ($('format').value === 'image/jpeg' && state.items.some(item => item.settings.mode === 'transparent')) {
      $('format').value = 'image/png';
      if (notify) toast('Cambiamos a PNG para conservar los fondos transparentes.');
    }
    if (!state.items.length && settings().mode === 'transparent' && $('format').value === 'image/jpeg') $('format').value = 'image/png';
    const isPng = $('format').value === 'image/png';
    $('quality').disabled = isPng;
    $('qualityValue').textContent = isPng ? 'Sin pérdida' : `${$('quality').value}%`;
    $('formatHint').textContent = isPng ? 'PNG conserva la transparencia y exporta sin pérdida. Puede pesar más.' : $('format').value === 'image/webp' ? 'WebP reduce el peso y admite transparencia. Usa un slider compatible con este formato.' : 'JPG: ideal para fotografías. Ajusta la calidad para reducir el peso.';
  }

  function syncControls() {
    const s = settings();
    document.querySelectorAll('input[name=mode]').forEach(input => { input.checked = input.value === s.mode; });
    $('blur').value = s.blur; $('blurValue').value = String(s.blur);
    $('color').value = s.color; $('colorValue').textContent = s.color.toUpperCase();
    $('zoom').value = Math.round(s.zoom * 100); $('zoomValue').value = `${Math.round(s.zoom * 100)}%`;
    $('positionX').value = Math.round(s.positionX * 100); $('positionXValue').value = `${Math.round(s.positionX * 100)}%`;
    $('positionY').value = Math.round(s.positionY * 100); $('positionYValue').value = `${Math.round(s.positionY * 100)}%`;
    $('blurControls').hidden = s.mode !== 'blur'; $('colorControls').hidden = s.mode !== 'color';
    $('modeDescription').textContent = modeDescriptions[s.mode];
    $('scopeLabel').textContent = selected() ? 'Ajustando solo esta imagen. Puedes copiar el ajuste a todas.' : 'Ajuste predeterminado para las imágenes nuevas.';
    enforceFormat(); syncRanges(); refreshDisabled();
  }

  function refreshSummary() {
    $('imageCount').textContent = state.items.length;
    $('outputSize').textContent = `${state.width} × ${state.height} px`;
    const count = state.items.length;
    $('exportSummary').textContent = count ? `${count} ${count === 1 ? 'imagen lista' : 'imágenes listas'} · ${state.width} × ${state.height} px` : 'Agrega imágenes para preparar tu descarga';
    const item = selected();
    $('sampleBadge').hidden = !!item;
    $('selectedName').textContent = item?.file.name || 'Así puede verse tu próxima imagen';
    $('selectedMeta').textContent = item ? `${item.width} × ${item.height} px · ${formatBytes(item.file.size)}` : 'Carga tus archivos para empezar';
    $('previewCaption').textContent = item ? `${modeLabel(item.settings.mode)} · Proporciones originales conservadas` : 'Una imagen, infinitas posibilidades.';
    refreshDisabled();
  }

  function schedulePreview() {
    cancelAnimationFrame(previewFrame);
    previewFrame = requestAnimationFrame(drawPreview);
  }

  function drawPreview() {
    const source = state.preview || state.sample;
    if (!source) return;
    const ratio = state.width / state.height;
    const container = $('canvasContainer');
    const availableWidth = Math.max(180, $('previewStage').clientWidth - (innerWidth < 800 ? 34 : 56));
    const maxHeight = innerWidth < 620 ? 350 : 420;
    const displayWidth = Math.min(availableWidth, maxHeight * ratio);
    container.style.width = `${displayWidth}px`;
    container.style.aspectRatio = String(ratio);
    const density = Math.min(devicePixelRatio || 1, 1.6);
    const scale = Math.min(1, (displayWidth * density) / state.width, (maxHeight * density) / state.height);
    const width = Math.max(1, Math.round(state.width * scale));
    const height = Math.max(1, Math.round(state.height * scale));
    try {
      const rendered = Engine.render(source, { ...fullOptions(), width, height, blur: settings().blur * scale, preview: true });
      const canvas = $('previewCanvas');
      canvas.width = rendered.width; canvas.height = rendered.height;
      canvas.getContext('2d').drawImage(rendered, 0, 0);
      rendered.width = rendered.height = 1;
      const item = selected();
      const placement = Engine.getPlacement(item?.width || source.width, item?.height || source.height, fullOptions());
      const notices = [];
      if (placement.cropped) notices.push('El encuadre actual recorta bordes. Ajusta el zoom o la posición para elegir qué conservar.');
      if (item && placement.upscaled) notices.push('La imagen original es más pequeña: al ampliarla puede perder nitidez.');
      $('cropNotice').hidden = !notices.length;
      $('cropNoticeText').textContent = notices.join(' ');
      $('previewCanvas').setAttribute('aria-label', `Vista previa: ${modeLabel(settings().mode)}, ${state.width} por ${state.height} píxeles`);
    } catch (error) { toast(error.message, true); }
  }

  function updateSetting(key, value) {
    if (state.busy) return;
    const item = selected();
    if (item) item.settings[key] = value;
    state.defaults[key] = value;
    syncControls(); refreshSummary(); schedulePreview(); updateGalleryLabels();
  }

  function validateSize() {
    const width = Number($('width').value), height = Number($('height').value);
    let error = '';
    if (!Number.isInteger(width) || !Number.isInteger(height) || width < 64 || height < 64 || width > 6000 || height > 6000) error = 'Usa números enteros entre 64 y 6000 px.';
    else if (width * height > 24000000) error = 'Máximo 24 megapíxeles. Reduce el ancho o el alto.';
    state.valid = !error;
    $('sizeError').hidden = !error; $('sizeError').textContent = error;
    $('width').setAttribute('aria-invalid', String(!!error)); $('height').setAttribute('aria-invalid', String(!!error));
    if (!error) {
      state.width = width; state.height = height;
      const gcd = (a, b) => b ? gcd(b, a % b) : a;
      const divisor = gcd(width, height);
      $('ratioInfo').textContent = `Proporción ${width / divisor}:${height / divisor} · Hasta 24 megapíxeles`;
      const matching = [...$('preset').options].find(option => option.value === `${width}x${height}`);
      $('preset').value = matching ? matching.value : 'custom';
      refreshSummary(); schedulePreview();
    }
    refreshDisabled();
  }

  function updateGalleryLabels() {
    state.items.forEach((item, index) => {
      const card = $('gallery').querySelector(`[data-id="${item.id}"]`);
      if (!card) return;
      card.classList.toggle('active', item.id === state.selected);
      card.querySelector('.thumb-select').setAttribute('aria-pressed', String(item.id === state.selected));
      card.querySelector('small').textContent = `${item.width} × ${item.height} · ${modeLabel(item.settings.mode)}`;
      card.querySelector('.thumb-number').textContent = String(index + 1).padStart(2, '0');
    });
  }

  function renderGallery() {
    if (!state.items.length) {
      const empty = document.createElement('div'); empty.className = 'gallery-empty';
      empty.innerHTML = '<span class="empty-stack"><svg class="icon"><use href="#i-image"/></svg></span><p>Aquí empieza tu colección<span>Agrega imágenes y selecciona cada una para afinar su encuadre.</span></p><button id="loadDemo" class="text-button">Probar con ejemplos <svg class="icon"><use href="#i-arrow"/></svg></button>';
      $('gallery').replaceChildren(empty);
      $('loadDemo').addEventListener('click', loadExamples);
      return;
    }
    const fragment = document.createDocumentFragment();
    state.items.forEach((item, index) => {
      const card = document.createElement('div'); card.className = 'thumbnail-card'; card.dataset.id = item.id;
      const button = document.createElement('button'); button.className = 'thumb-select'; button.type = 'button';
      button.setAttribute('aria-label', `Editar ${item.file.name}`);
      const img = document.createElement('img'); img.src = item.thumbUrl; img.alt = ''; img.draggable = false;
      const name = document.createElement('strong'); name.textContent = item.file.name; name.title = item.file.name;
      const meta = document.createElement('small');
      button.append(img, name, meta); button.addEventListener('click', () => { if (!state.busy) selectItem(item.id); });
      const number = document.createElement('span'); number.className = 'thumb-number';
      const remove = document.createElement('button'); remove.className = 'thumb-remove'; remove.title = `Quitar ${item.file.name}`; remove.setAttribute('aria-label', remove.title);
      remove.innerHTML = '<svg class="icon"><use href="#i-close"/></svg>'; remove.addEventListener('click', () => removeItem(item.id));
      const order = document.createElement('div'); order.className = 'thumb-order';
      const left = document.createElement('button'); left.textContent = '←'; left.disabled = index === 0; left.setAttribute('aria-label', `Mover ${item.file.name} antes`); left.addEventListener('click', () => moveItem(item.id, -1));
      const orderText = document.createElement('span'); orderText.textContent = 'Orden en el ZIP';
      const right = document.createElement('button'); right.textContent = '→'; right.disabled = index === state.items.length - 1; right.setAttribute('aria-label', `Mover ${item.file.name} después`); right.addEventListener('click', () => moveItem(item.id, 1));
      order.append(left, orderText, right); card.append(button, number, remove, order); fragment.append(card);
    });
    $('gallery').replaceChildren(fragment); updateGalleryLabels();
  }

  function makePreview(decoded, limit = 1800) {
    const scale = Math.min(1, limit / Math.max(decoded.width, decoded.height));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(decoded.width * scale)); canvas.height = Math.max(1, Math.round(decoded.height * scale));
    canvas.getContext('2d').drawImage(decoded.source, 0, 0, canvas.width, canvas.height);
    return canvas;
  }

  async function selectItem(id) {
    const token = ++state.generation;
    state.selected = id;
    state.preview = null;
    refreshSummary(); syncControls(); updateGalleryLabels();
    const item = selected();
    if (!item) { schedulePreview(); return; }
    let decoded;
    try {
      decoded = await Engine.decodeFile(item.file);
      if (token !== state.generation) return;
      state.preview = { source: makePreview(decoded), width: item.width, height: item.height }; schedulePreview();
    } catch (error) {
      if (token === state.generation) { toast(error.message, true); schedulePreview(); }
    } finally { decoded?.close(); }
  }

  async function importFiles(fileList) {
    if (state.busy || !fileList.length) return;
    const files = Array.from(fileList);
    setBusy(true, 'Abriendo imágenes…');
    let firstId = null, added = 0; const errors = [];
    for (const file of files) {
      if (state.items.length >= 50) { errors.push('El lote admite hasta 50 imágenes.'); break; }
      if (!/\.(jpe?g|png|webp|avif|gif)$/i.test(file.name) && !/^image\/(jpeg|png|webp|avif|gif)$/i.test(file.type)) { errors.push(`${file.name}: formato no admitido.`); continue; }
      if (file.size > 30 * 1024 * 1024) { errors.push(`${file.name}: supera los 30 MB.`); continue; }
      let decoded;
      try {
        decoded = await Engine.decodeFile(file);
        if (decoded.width * decoded.height > 80000000) throw new Error('Supera los 80 megapíxeles de entrada.');
        const thumb = makePreview(decoded, 280);
        const blob = await Engine.encode(thumb, 'image/png');
        thumb.width = thumb.height = 1;
        const id = `image-${crypto.randomUUID ? crypto.randomUUID() : Math.random().toString(36).slice(2)}`;
        const item = { id, file, width: decoded.width, height: decoded.height, thumbUrl: URL.createObjectURL(blob), settings: { ...state.defaults } };
        state.items.push(item); firstId ||= id; added++;
        $('downloadLabel').textContent = `Abriendo ${added}/${files.length}…`;
      } catch (error) { errors.push(`${file.name}: ${error.message}`); }
      finally { decoded?.close(); }
      await frame();
    }
    renderGallery(); setBusy(false); refreshSummary(); enforceFormat();
    if (firstId) await selectItem(firstId);
    if (errors.length) toast(`${added ? `${added} ${added === 1 ? 'imagen agregada' : 'imágenes agregadas'}. ` : ''}${errors.slice(0, 2).join(' ')}${errors.length > 2 ? ` Y ${errors.length - 2} archivo(s) más no admitidos.` : ''}`, true);
    else if (added) toast(`${added} ${added === 1 ? 'imagen agregada' : 'imágenes agregadas'}. Selecciona cada una para ajustar su encuadre.`);
    $('fileInput').value = '';
  }

  function removeItem(id) {
    if (state.busy) return;
    const index = state.items.findIndex(item => item.id === id);
    if (index < 0) return;
    URL.revokeObjectURL(state.items[index].thumbUrl);
    state.items.splice(index, 1); renderGallery();
    if (state.selected === id) selectItem(state.items[Math.min(index, state.items.length - 1)]?.id || null);
    refreshSummary(); enforceFormat();
  }

  function moveItem(id, direction) {
    if (state.busy) return;
    const index = state.items.findIndex(item => item.id === id), target = index + direction;
    if (index < 0 || target < 0 || target >= state.items.length) return;
    [state.items[index], state.items[target]] = [state.items[target], state.items[index]];
    renderGallery();
  }

  function downloadBlob(blob, name) {
    const url = URL.createObjectURL(blob), link = document.createElement('a');
    link.href = url; link.download = name; document.body.append(link); link.click(); link.remove();
    setTimeout(() => URL.revokeObjectURL(url), 60000);
  }

  async function exportImages(single = false) {
    if (state.busy || !state.items.length) return;
    validateSize(); if (!state.valid) { toast($('sizeError').textContent, true); return; }
    enforceFormat(true);
    const items = single ? [selected()] : [...state.items];
    if (!items[0]) return;
    const mime = $('format').value, quality = Number($('quality').value) / 100;
    const ext = { 'image/jpeg': 'jpg', 'image/webp': 'webp', 'image/png': 'png' }[mime];
    const entries = []; let total = 0;
    setBusy(true, 'Preparando…');
    try {
      for (let index = 0; index < items.length; index++) {
        $('downloadLabel').textContent = `Procesando ${index + 1} de ${items.length}…`; await frame();
        const item = items[index]; let decoded, canvas;
        try {
          decoded = await Engine.decodeFile(item.file);
          canvas = Engine.render(decoded, fullOptions(item));
          const data = await Engine.encode(canvas, mime, quality);
          total += data.size;
          if (total > 700 * 1024 * 1024) throw new Error('El lote supera los 700 MB. Descarga menos imágenes o reduce el tamaño de salida.');
          const base = window.SliderZip.sanitizeBase(item.file.name.replace(/\.[^.]+$/, '')).slice(0, 100) || 'imagen';
          entries.push({ name: `${String(state.items.indexOf(item) + 1).padStart(2, '0')}-${base}-${state.width}x${state.height}.${ext}`, data });
        } finally { decoded?.close(); if (canvas) canvas.width = canvas.height = 1; }
      }
      $('downloadLabel').textContent = single ? 'Descargando…' : 'Creando ZIP…'; await frame();
      if (single) downloadBlob(entries[0].data, entries[0].name);
      else downloadBlob(await window.SliderZip.create(entries), `slider-${state.width}x${state.height}.zip`);
      toast(`${single ? 'Imagen preparada' : 'ZIP preparado'} · ${entries.length} ${entries.length === 1 ? 'imagen' : 'imágenes'} · ${formatBytes(total)}`);
    } catch (error) { toast(error.message || 'No se pudo preparar la descarga. Inténtalo con un lote menor.', true); }
    finally { setBusy(false); enforceFormat(); }
  }

  function makeLandscape(width, height, variant = 0) {
    const canvas = document.createElement('canvas'); canvas.width = width; canvas.height = height;
    const c = canvas.getContext('2d');
    const palettes = [ ['#d8dfbf','#95b6b8','#78918c','#3f6f68','#214f4d','#133d39'], ['#efd3b1','#d3a59c','#b1878a','#866c7f','#574e67','#303b4f'], ['#e1e6c8','#a3c8cc','#86b8ba','#588e8a','#3c7373','#254b52'] ];
    const p = palettes[variant % 3];
    const sky = c.createLinearGradient(0, 0, 0, height); sky.addColorStop(0, p[0]); sky.addColorStop(1, p[1]); c.fillStyle = sky; c.fillRect(0, 0, width, height);
    c.fillStyle = '#f5ecd3'; c.beginPath(); c.arc(width * .72, height * .25, Math.min(width, height) * .095, 0, Math.PI * 2); c.fill();
    for (let layer = 0; layer < 4; layer++) {
      c.fillStyle = p[layer + 2]; c.beginPath(); c.moveTo(0, height);
      for (let n = 0; n <= 16; n++) {
        const x = n * width / 16;
        const y = height * (.48 + layer * .13) - Math.sin(n * .8 + layer * 1.5 + variant) * height * (.12 - layer * .018) - (n % 2 ? height * .05 : 0);
        c.lineTo(x, y);
      }
      c.lineTo(width, height); c.closePath(); c.fill();
    }
    c.globalAlpha = .15; c.fillStyle = '#f1f4d8';
    c.beginPath(); c.moveTo(width * .49, height * .68); c.bezierCurveTo(width * .25, height * .85, width * .82, height * .83, width * .65, height); c.lineTo(width * .44, height); c.bezierCurveTo(width * .7, height * .84, width * .18, height * .89, width * .49, height * .68); c.fill(); c.globalAlpha = 1;
    // Fine deterministic grain gives the examples a printed landscape finish.
    let seed = 5489 + variant;
    for (let n = 0; n < Math.min(18000, width * height / 35); n++) {
      seed = (seed * 16807) % 2147483647; const x = seed % width;
      seed = (seed * 16807) % 2147483647; const y = seed % height;
      c.fillStyle = n % 2 ? '#ffffff0a' : '#00000009'; c.fillRect(x, y, 1.5, 1.5);
    }
    return canvas;
  }

  async function loadExamples() {
    if (state.busy) return;
    setBusy(true, 'Creando ejemplos…');
    const files = [];
    try {
      for (const [width, height, variant, name] of [[700, 1000, 0, 'ejemplo-bosque.png'], [1200, 700, 1, 'ejemplo-atardecer.png'], [800, 800, 2, 'ejemplo-montanas.png']]) {
        const canvas = makeLandscape(width, height, variant); const blob = await Engine.encode(canvas, 'image/png');
        files.push(new File([blob], name, { type: 'image/png' })); canvas.width = canvas.height = 1;
      }
    } catch (error) { toast(error.message, true); }
    finally { setBusy(false); }
    await importFiles(files);
  }

  $('preset').addEventListener('change', () => { if ($('preset').value !== 'custom') { [$('width').value, $('height').value] = $('preset').value.split('x'); validateSize(); } else $('width').focus(); });
  $('width').addEventListener('input', validateSize); $('height').addEventListener('input', validateSize);
  document.querySelectorAll('input[name=mode]').forEach(input => input.addEventListener('change', () => updateSetting('mode', input.value)));
  $('blur').addEventListener('input', () => updateSetting('blur', Number($('blur').value)));
  $('color').addEventListener('input', () => updateSetting('color', $('color').value));
  document.querySelectorAll('[data-color]').forEach(button => button.addEventListener('click', () => updateSetting('color', button.dataset.color)));
  $('zoom').addEventListener('input', () => updateSetting('zoom', Number($('zoom').value) / 100));
  $('positionX').addEventListener('input', () => updateSetting('positionX', Number($('positionX').value) / 100));
  $('positionY').addEventListener('input', () => updateSetting('positionY', Number($('positionY').value) / 100));
  $('quality').addEventListener('input', () => { enforceFormat(); syncRanges(); }); $('format').addEventListener('change', () => enforceFormat(true));
  $('applyAll').addEventListener('click', () => { const current = { ...settings() }; state.items.forEach(item => { item.settings = { ...current }; }); state.defaults = current; updateGalleryLabels(); enforceFormat(true); toast('El fondo, zoom y posición se aplicaron a todas las imágenes.'); });
  $('resetImage').addEventListener('click', () => { if (state.busy) return; Object.assign(settings(), { zoom: 1, positionX: .5, positionY: .5 }); syncControls(); schedulePreview(); });
  $('clearAll').addEventListener('click', () => { if (state.busy) return; state.items.forEach(item => URL.revokeObjectURL(item.thumbUrl)); state.items = []; state.selected = null; state.preview = null; state.generation++; renderGallery(); refreshSummary(); syncControls(); schedulePreview(); });
  $('downloadOne').addEventListener('click', () => exportImages(true)); $('downloadAll').addEventListener('click', () => exportImages(false));
  $('fileInput').addEventListener('change', event => importFiles(event.target.files));
  $('dropzone').addEventListener('click', () => { if (!state.busy) $('fileInput').click(); });
  $('dropzone').addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); if (!state.busy) $('fileInput').click(); } });
  $('loadDemo').addEventListener('click', loadExamples);
  const hasFiles = event => Array.from(event.dataTransfer?.types || []).includes('Files');
  window.addEventListener('dragenter', event => { if (!hasFiles(event)) return; event.preventDefault(); dragDepth++; if (!state.busy) $('importOverlay').hidden = false; });
  window.addEventListener('dragover', event => { if (hasFiles(event)) { event.preventDefault(); event.dataTransfer.dropEffect = state.busy ? 'none' : 'copy'; } });
  window.addEventListener('dragleave', event => { if (!hasFiles(event)) return; dragDepth = Math.max(0, dragDepth - 1); if (!dragDepth) $('importOverlay').hidden = true; });
  window.addEventListener('drop', event => { if (!hasFiles(event)) return; event.preventDefault(); dragDepth = 0; $('importOverlay').hidden = true; if (!state.busy) importFiles(event.dataTransfer.files); });
  window.addEventListener('blur', () => { dragDepth = 0; $('importOverlay').hidden = true; });
  $('helpButton').addEventListener('click', () => $('helpDialog').showModal());
  ['closeHelp', 'gotIt'].forEach(id => $(id).addEventListener('click', () => $('helpDialog').close()));
  $('helpDialog').addEventListener('click', event => { if (event.target === $('helpDialog')) { const r = $('helpDialog').getBoundingClientRect(); if (event.clientX < r.left || event.clientX > r.right || event.clientY < r.top || event.clientY > r.bottom) $('helpDialog').close(); } });
  let pointer = null;
  $('canvasContainer').addEventListener('pointerdown', event => {
    if (state.busy || event.button !== 0) return;
    const source = state.preview || state.sample; if (!source) return;
    const placement = Engine.getPlacement(source.width, source.height, fullOptions());
    pointer = { x: event.clientX, y: event.clientY, positionX: settings().positionX, positionY: settings().positionY, placement };
    $('canvasContainer').setPointerCapture(event.pointerId);
  });
  $('canvasContainer').addEventListener('pointermove', event => {
    if (!pointer || state.busy) return;
    const rect = $('canvasContainer').getBoundingClientRect();
    const diffX = state.width - pointer.placement.width, diffY = state.height - pointer.placement.height;
    const s = settings();
    if (Math.abs(diffX) > 1) s.positionX = Math.max(0, Math.min(1, pointer.positionX + (event.clientX - pointer.x) / rect.width * state.width / diffX));
    if (Math.abs(diffY) > 1) s.positionY = Math.max(0, Math.min(1, pointer.positionY + (event.clientY - pointer.y) / rect.height * state.height / diffY));
    state.defaults.positionX = s.positionX; state.defaults.positionY = s.positionY;
    syncControls(); schedulePreview();
  });
  ['pointerup', 'pointercancel', 'lostpointercapture'].forEach(event => $('canvasContainer').addEventListener(event, () => { pointer = null; }));
  new ResizeObserver(schedulePreview).observe($('previewStage'));
  window.addEventListener('beforeunload', () => state.items.forEach(item => URL.revokeObjectURL(item.thumbUrl)));
  state.sample = makeLandscape(840, 660);
  validateSize(); syncControls(); refreshSummary(); schedulePreview();
})();
