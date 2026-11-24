(() => {
  const cfg = window.DNG_CONFIG;
  const input = document.getElementById('files');
  const zone = document.getElementById('dropzone');
  const list = document.getElementById('file-list');
  const results = document.getElementById('results');
  const resultList = document.getElementById('result-list');
  const summary = document.getElementById('result-summary');
  const zipLink = document.getElementById('zip-link');
  const button = document.getElementById('convert');
  const qualityField = document.getElementById('quality-field');
  const outputInput = document.getElementById('output-dir');
  const validateButton = document.getElementById('validate-dir');
  const outputStatus = document.getElementById('output-status');
  const outputStorageKey = 'guagua.dngWord.outputDir.' + cfg.userKey;
  let validatedDirectory = null;
  if (outputInput) {
    try { outputInput.value = localStorage.getItem(outputStorageKey) || ''; } catch {}
    if (outputInput.value) outputStatus.textContent = 'Carpeta recordada; se comprobará antes de convertir.';
    outputInput.addEventListener('input', () => {
      validatedDirectory = null;
      outputStatus.className = 'output-status';
      outputStatus.textContent = outputInput.value.trim()
        ? 'Comprueba la carpeta antes de convertir.'
        : 'Sin carpeta fija: utiliza los enlaces de descarga.';
    });
  }
  let selected = [];

  const size = bytes => bytes >= 1048576 ? (bytes / 1048576).toFixed(1) + ' MB' : Math.ceil(bytes / 1024) + ' KB';
  const format = () => document.querySelector('input[name="format"]:checked').value;
  function renderFiles() {
    list.replaceChildren();
    selected.forEach(file => {
      const row = document.createElement('div');
      row.className = 'file-item';
      const name = document.createElement('span');
      name.className = 'file-name';
      name.textContent = file.name;
      const weight = document.createElement('span');
      weight.className = 'file-size';
      weight.textContent = size(file.size);
      row.append(name, weight);
      list.append(row);
    });
  }
  function choose(files) {
    const all = Array.from(files);
    const invalid = all.find(file => !/\.dng$/i.test(file.name) || file.size < 1 || file.size > 209715200);
    if (all.length > 20 || invalid || all.reduce((sum, file) => sum + file.size, 0) > 1073741824) {
      alert(invalid ? 'Solo se aceptan archivos .dng de hasta 200 MB.' : 'Selecciona un máximo de 20 archivos y 1 GB por lote.');
      return;
    }
    selected = all;
    renderFiles();
  }
  zone.addEventListener('click', event => { if (event.target !== input) input.click(); });
  zone.addEventListener('keydown', event => { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); input.click(); } });
  zone.addEventListener('dragover', event => { event.preventDefault(); zone.classList.add('dragging'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('dragging'));
  zone.addEventListener('drop', event => { event.preventDefault(); zone.classList.remove('dragging'); choose(event.dataTransfer.files); });
  input.addEventListener('change', () => choose(input.files));
  document.querySelectorAll('input[name="format"]').forEach(radio => radio.addEventListener('change', () => {
    qualityField.hidden = format() === 'png';
  }));

  async function api(action, values, blob) {
    const form = new FormData();
    form.append('action', action);
    form.append('csrf_token', cfg.csrf);
    Object.entries(values).forEach(([key, value]) => form.append(key, String(value)));
    if (blob) form.append('chunk', blob, 'chunk.bin');
    const response = await fetch('api.php', { method: 'POST', body: form, credentials: 'same-origin' });
    let data;
    try { data = await response.json(); } catch { throw new Error('El servidor no devolvió una respuesta válida. Revisa los límites de PHP.'); }
    if (!response.ok || !data.ok) throw new Error(data.error || 'Ocurrió un error durante la conversión.');
    return data;
  }
  async function validateOutputDirectory() {
    if (!outputInput) return '';
    const requested = outputInput.value.trim();
    if (!requested) {
      validatedDirectory = null;
      outputStatus.className = 'output-status';
      outputStatus.textContent = 'Sin carpeta fija: utiliza los enlaces de descarga.';
      try { localStorage.removeItem(outputStorageKey); } catch {}
      return '';
    }
    const data = await api('validate_dir', { path: requested });
    validatedDirectory = data.path;
    outputInput.value = data.path;
    outputStatus.className = 'output-status ok';
    outputStatus.textContent = 'Carpeta lista: ' + data.path;
    try { localStorage.setItem(outputStorageKey, data.path); } catch {}
    return data.path;
  }
  if (validateButton) {
    validateButton.addEventListener('click', async () => {
      validateButton.disabled = true;
      try {
        await validateOutputDirectory();
      } catch (error) {
        validatedDirectory = null;
        outputStatus.className = 'output-status error';
        outputStatus.textContent = error.message;
      } finally {
        validateButton.disabled = false;
      }
    });
  }
  function resultRow(file) {
    const row = document.createElement('div');
    row.className = 'result-item';
    const block = document.createElement('div');
    block.className = 'name-block';
    const name = document.createElement('span');
    name.className = 'file-name';
    name.textContent = file.name;
    const detail = document.createElement('small');
    detail.textContent = 'En espera';
    block.append(name, detail);
    row.append(block);
    resultList.append(row);
    return { row, detail };
  }
  async function run() {
    if (!cfg.ready || !selected.length) { if (!selected.length) alert('Selecciona al menos un archivo DNG.'); return; }
    button.disabled = true;
    results.hidden = false;
    resultList.replaceChildren();
    zipLink.hidden = true;
    summary.textContent = 'Preparando el lote…';
    const files = selected.slice();
    const options = { format: format(), edge: document.getElementById('edge').value, quality: document.getElementById('quality').value };
    const rows = files.map(resultRow);
    let done = 0;
    let job;
    try {
      summary.textContent = 'Comprobando carpeta de salida…';
      const outputDir = await validateOutputDirectory();
      job = (await api('start', { output_dir: outputDir })).job;
      for (let fileIndex = 0; fileIndex < files.length; fileIndex++) {
        const file = files[fileIndex], { row, detail } = rows[fileIndex];
        try {
          const chunks = Math.ceil(file.size / 6291456);
          for (let index = 0; index < chunks; index++) {
            const part = file.slice(index * 6291456, Math.min((index + 1) * 6291456, file.size));
            detail.textContent = 'Subiendo ' + Math.round(index / chunks * 100) + '%';
            await api('chunk', { job, file: fileIndex, name: file.name, size: file.size, index }, part);
          }
          detail.textContent = 'Revelando RAW…';
          const data = await api('convert', {
            job, file: fileIndex, ...options
          });
          detail.textContent = data.info.width + ' × ' + data.info.height + ' px · ' + size(data.info.bytes);
          if (data.info.source === 'embedded_jpeg') {
            detail.textContent += ' · JPEG integrado del DNG (' + data.info.source_width + ' × ' + data.info.source_height + ' px)';
          }
          if (data.saved_path) detail.textContent += ' · Guardada en ' + data.saved_path;
          if (data.save_error) { row.classList.add('error'); detail.textContent += ' · No se pudo guardar en la carpeta: ' + data.save_error; }
          const link = document.createElement('a');
          link.href = data.download;
          link.textContent = 'Descargar ↓';
          row.append(link);
          done++;
        } catch (error) {
          row.classList.add('error');
          detail.textContent = error.message;
        }
        summary.textContent = done + ' de ' + files.length + ' convertidas';
      }
      if (done > 1) {
        zipLink.href = 'api.php?action=zip&job=' + encodeURIComponent(job);
        zipLink.hidden = false;
      }
      if (!done) summary.textContent = 'No se pudo convertir ninguna imagen.';
    } catch (error) {
      summary.textContent = error.message;
    } finally {
      button.disabled = false;
    }
  }
  button.addEventListener('click', run);
})();








