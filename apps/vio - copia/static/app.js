/* ============================================================
   VisoSearch v2 — Premium Frontend Logic
   ============================================================ */

'use strict';

// ---- App State ----
const state = {
  folderPath: '',
  totalImages: 0,
  currentResults: [],
  filteredResults: [],
  selectedQuery: '',
  searchHistory: JSON.parse(localStorage.getItem('vs_history') || '[]'),
  lightboxIdx: 0,
  pollTimer: null,
  viewMode: 'grid',
  sortMode: 'score-desc',
  filterMode: 'all',
  settingsCollapsed: false,
};

// ---- DOM refs ----
const $ = id => document.getElementById(id);

const dom = {
  // Header
  statusDot:      $('statusDot'),
  statusLabel:    $('statusLabel'),
  historyScroll:  $('historyScroll'),
  helpBtn:        $('helpBtn'),

  // Sidebar
  folderPath:     $('folderPath'),
  pathInputGroup: $('pathInputGroup'),
  clearPathBtn:   $('clearPathBtn'),
  loadFolderBtn:  $('loadFolderBtn'),
  folderPreview:  $('folderPreview'),
  previewCount:   $('previewCount'),
  previewStrip:   $('previewStrip'),
  resetFolderBtn: $('resetFolderBtn'),

  queryInput:     $('queryInput'),
  charCount:      $('charCount'),
  chipsWrap:      $('chipsWrap'),

  settingsLabel:  $('settingsLabel'),
  settingsCollapseIcon: $('settingsCollapseIcon'),
  settingsBody:   $('settingsBody'),
  thresholdSlider:$('thresholdSlider'),
  thresholdVal:   $('thresholdVal'),
  maxResultsSlider:$('maxResultsSlider'),
  maxResultsVal:  $('maxResultsVal'),

  searchBtn:      $('searchBtn'),
  searchBtnLabel: $('searchBtnLabel'),

  // States
  idleState:      $('idleState'),
  progressState:  $('progressState'),
  resultsState:   $('resultsState'),

  // Progress
  ringProgress:   $('ringProgress'),
  ringPct:        $('ringPct'),
  progressTitle:  $('progressTitle'),
  progressSubtitle: $('progressSubtitle'),
  progressFile:   $('progressFile'),
  progressBarFill: $('progressBarFill'),
  progressBarPct: $('progressBarPct'),
  psProcessed:    $('psProcessed'),
  psTotal:        $('psTotal'),
  psMatches:      $('psMatches'),
  cancelSearchBtn:$('cancelSearchBtn'),

  // Results
  resultsCount:   $('resultsCount'),
  resultsQueryBadge: $('resultsQueryBadge'),
  sortSelect:     $('sortSelect'),
  filterSelect:   $('filterSelect'),
  selectAllBtn:   $('selectAllBtn'),
  deselectAllBtn: $('deselectAllBtn'),
  gridViewBtn:    $('gridViewBtn'),
  listViewBtn:    $('listViewBtn'),
  exportBtn:      $('exportBtn'),
  exportLabel:    $('exportLabel'),
  gallery:        $('gallery'),
  noResults:      $('noResults'),
  tryAgainBtn:    $('tryAgainBtn'),

  // Export modal
  exportModal:    $('exportModal'),
  closeExportModal: $('closeExportModal'),
  exportCount:    $('exportCount'),
  exportPath:     $('exportPath'),
  createSubfolderCheck: $('createSubfolderCheck'),
  subfolderNameWrap: $('subfolderNameWrap'),
  subfolderName:  $('subfolderName'),
  cancelExportBtn:$('cancelExportBtn'),
  confirmExportBtn:$('confirmExportBtn'),

  // Lightbox
  lightbox:       $('lightbox'),
  lbBackdrop:     $('lbBackdrop'),
  lbImg:          $('lbImg'),
  lbFilename:     $('lbFilename'),
  lbScore:        $('lbScore'),
  lbSelectBtn:    $('lbSelectBtn'),
  lbClose:        $('lbClose'),
  lbPrev:         $('lbPrev'),
  lbNext:         $('lbNext'),
  lbCounter:      $('lbCounter'),
  lbPath:         $('lbPath'),

  // Help
  helpPanel:      $('helpPanel'),
  hpBackdrop:     $('hpBackdrop'),
  hpClose:        $('hpClose'),

  // Deps
  depsBanner:     $('depsBanner'),
  depsCmd:        $('depsCmd'),
  depsCopyBtn:    $('depsCopyBtn'),
  depsDismissBtn: $('depsDismissBtn'),

  toastContainer: $('toastContainer'),
  bgScene:        $('bgScene'),
  bgCanvas:       $('bgCanvas'),
};

// ======================================================================
// INIT
// ======================================================================
document.addEventListener('DOMContentLoaded', () => {
  initCanvas();
  checkDependencies();
  renderHistory();
  initSliders();
  bindEvents();
});

// ======================================================================
// CANVAS PARTICLES
// ======================================================================
function initCanvas() {
  const canvas = dom.bgCanvas;
  const ctx = canvas.getContext('2d');
  let particles = [];
  let W, H;

  function resize() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
  }
  resize();
  window.addEventListener('resize', resize);

  function makeParticle() {
    return {
      x: Math.random() * W,
      y: Math.random() * H,
      r: Math.random() * 1.5 + 0.3,
      vx: (Math.random() - 0.5) * 0.2,
      vy: (Math.random() - 0.5) * 0.2,
      alpha: Math.random() * 0.4 + 0.05,
      hue: Math.random() > 0.5 ? 252 : 191, // violet or cyan
    };
  }

  for (let i = 0; i < 60; i++) particles.push(makeParticle());

  function loop() {
    ctx.clearRect(0, 0, W, H);
    particles.forEach(p => {
      p.x += p.vx; p.y += p.vy;
      if (p.x < 0) p.x = W; if (p.x > W) p.x = 0;
      if (p.y < 0) p.y = H; if (p.y > H) p.y = 0;
      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `hsla(${p.hue}, 80%, 70%, ${p.alpha})`;
      ctx.fill();
    });
    requestAnimationFrame(loop);
  }
  loop();
}

// ======================================================================
// STATUS
// ======================================================================
function setStatus(type, text) {
  dom.statusDot.className = `status-dot ${type}`;
  dom.statusLabel.textContent = text;
}

// ======================================================================
// DEPENDENCY CHECK
// ======================================================================
async function checkDependencies() {
  try {
    const res  = await fetch('/api/check-deps');
    const data = await res.json();
    if (data.all_ok) {
      const gpu = data.deps.torch?.includes('cu') ? '⚡ GPU' : '🖥 CPU';
      setStatus('ok', `CLIP listo · ${gpu}`);
    } else {
      setStatus('warn', 'Dependencias faltantes');
      dom.depsBanner.classList.remove('hidden');
    }
  } catch {
    setStatus('error', 'Sin conexión al servidor');
  }
}

// ======================================================================
// SEARCH HISTORY
// ======================================================================
function saveToHistory(query) {
  if (!query) return;
  state.searchHistory = state.searchHistory.filter(q => q !== query);
  state.searchHistory.unshift(query);
  if (state.searchHistory.length > 10) state.searchHistory = state.searchHistory.slice(0, 10);
  localStorage.setItem('vs_history', JSON.stringify(state.searchHistory));
  renderHistory();
}

function renderHistory() {
  dom.historyScroll.innerHTML = '';
  if (state.searchHistory.length === 0) return;

  const label = document.createElement('span');
  label.style.cssText = 'font-size:.65rem;color:var(--text-muted);white-space:nowrap;font-weight:600;text-transform:uppercase;letter-spacing:.08em;padding:0 4px;';
  label.textContent = 'Historial:';
  dom.historyScroll.appendChild(label);

  state.searchHistory.forEach((q, i) => {
    const chip = document.createElement('button');
    chip.className = 'history-chip';
    chip.innerHTML = `<span>${q}</span><span class="hc-x" data-idx="${i}" title="Eliminar">✕</span>`;
    chip.addEventListener('click', (e) => {
      const xBtn = e.target.closest('.hc-x');
      if (xBtn) {
        e.stopPropagation();
        const idx = parseInt(xBtn.dataset.idx);
        state.searchHistory.splice(idx, 1);
        localStorage.setItem('vs_history', JSON.stringify(state.searchHistory));
        renderHistory();
      } else {
        dom.queryInput.value = q;
        dom.queryInput.dispatchEvent(new Event('input'));
        dom.queryInput.focus();
      }
    });
    dom.historyScroll.appendChild(chip);
  });
}

// ======================================================================
// SLIDERS
// ======================================================================
function initSliders() {
  updateSlider(dom.thresholdSlider, dom.thresholdVal, v => `${v}%`, 5, 60);
  updateSlider(dom.maxResultsSlider, dom.maxResultsVal, v => v == 0 ? 'Sin límite' : v, 0, 200);
}

function updateSlider(slider, display, format, min, max) {
  const update = () => {
    const v = parseFloat(slider.value);
    display.textContent = format(v);
    // Fill gradient on slider via background
    const pct = ((v - min) / (max - min)) * 100;
    slider.style.background = `linear-gradient(to right, #7c5cfc ${pct}%, rgba(255,255,255,0.08) ${pct}%)`;
  };
  update();
  slider.addEventListener('input', update);
}

// ======================================================================
// EVENT BINDINGS
// ======================================================================
function bindEvents() {
  // Folder
  dom.folderPath.addEventListener('input', () => {
    const hasVal = dom.folderPath.value.trim() !== '';
    dom.clearPathBtn.classList.toggle('hidden', !hasVal);
    dom.searchBtn.disabled = !hasVal || dom.queryInput.value.trim() === '';
  });
  dom.folderPath.addEventListener('keydown', e => { if (e.key === 'Enter') loadFolder(); });
  dom.clearPathBtn.addEventListener('click', resetFolder);
  dom.loadFolderBtn.addEventListener('click', loadFolder);
  dom.resetFolderBtn.addEventListener('click', resetFolder);

  // Query
  dom.queryInput.addEventListener('input', () => {
    const len = dom.queryInput.value.length;
    dom.charCount.textContent = len;
    dom.searchBtn.disabled = dom.folderPath.value.trim() === '' || len === 0;
  });
  dom.queryInput.addEventListener('keydown', e => {
    if (e.key === 'Enter' && e.ctrlKey) startSearch();
  });

  // Chips
  dom.chipsWrap.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      dom.queryInput.value = chip.dataset.query;
      dom.queryInput.dispatchEvent(new Event('input'));
      dom.queryInput.focus();
    });
  });

  // Settings collapse
  dom.settingsLabel.addEventListener('click', () => {
    state.settingsCollapsed = !state.settingsCollapsed;
    dom.settingsBody.classList.toggle('collapsed', state.settingsCollapsed);
    dom.settingsCollapseIcon.classList.toggle('rotated', state.settingsCollapsed);
  });

  // Search
  dom.searchBtn.addEventListener('click', startSearch);
  dom.cancelSearchBtn.addEventListener('click', cancelSearch);

  // Results controls
  dom.sortSelect.addEventListener('change', () => {
    state.sortMode = dom.sortSelect.value;
    applyFilterSort();
  });
  dom.filterSelect.addEventListener('change', () => {
    state.filterMode = dom.filterSelect.value;
    applyFilterSort();
  });

  dom.selectAllBtn.addEventListener('click', () => {
    state.filteredResults.forEach(r => r.selected = true);
    renderGallery();
    updateExportBtn();
    showToast('Seleccionadas', `${state.filteredResults.length} imágenes seleccionadas`, 'info');
  });
  dom.deselectAllBtn.addEventListener('click', () => {
    state.filteredResults.forEach(r => r.selected = false);
    renderGallery();
    updateExportBtn();
  });

  dom.gridViewBtn.addEventListener('click', () => setViewMode('grid'));
  dom.listViewBtn.addEventListener('click', () => setViewMode('list'));

  dom.exportBtn.addEventListener('click', openExportModal);
  dom.tryAgainBtn.addEventListener('click', () => showPanel('idle'));

  // Export modal
  dom.closeExportModal.addEventListener('click',  closeExportModal);
  dom.cancelExportBtn.addEventListener('click',   closeExportModal);
  dom.exportModal.addEventListener('click', e => { if (e.target === dom.exportModal) closeExportModal(); });
  dom.createSubfolderCheck.addEventListener('change', () => {
    dom.subfolderNameWrap.style.display = dom.createSubfolderCheck.checked ? '' : 'none';
  });
  dom.confirmExportBtn.addEventListener('click', doExport);

  // Lightbox
  dom.lbClose.addEventListener('click',    closeLightbox);
  dom.lbBackdrop.addEventListener('click', closeLightbox);
  dom.lbPrev.addEventListener('click', () => navigateLightbox(-1));
  dom.lbNext.addEventListener('click', () => navigateLightbox(1));
  dom.lbSelectBtn.addEventListener('click', () => {
    toggleSelect(state.lightboxIdx);
    updateLightboxSelectBtn();
  });

  // Help
  dom.helpBtn.addEventListener('click', () => dom.helpPanel.classList.remove('hidden'));
  dom.hpClose.addEventListener('click', () => dom.helpPanel.classList.add('hidden'));
  dom.hpBackdrop.addEventListener('click', () => dom.helpPanel.classList.add('hidden'));

  // Deps banner
  dom.depsCopyBtn.addEventListener('click', () => {
    navigator.clipboard.writeText(dom.depsCmd.textContent)
      .then(() => showToast('Copiado', 'Comando copiado al portapapeles', 'success'))
      .catch(() => showToast('Error', 'No se pudo copiar', 'error'));
  });
  dom.depsDismissBtn.addEventListener('click', () => dom.depsBanner.classList.add('hidden'));

  // Global keyboard
  document.addEventListener('keydown', e => {
    const lb = !dom.lightbox.classList.contains('hidden');
    const hp = !dom.helpPanel.classList.contains('hidden');
    const md = !dom.exportModal.classList.contains('hidden');

    if (e.key === 'Escape') {
      if (md) closeExportModal();
      else if (lb) closeLightbox();
      else if (hp) dom.helpPanel.classList.add('hidden');
    }
    if (lb) {
      if (e.key === 'ArrowLeft')  navigateLightbox(-1);
      if (e.key === 'ArrowRight') navigateLightbox(1);
    }
  });
}

// ======================================================================
// FOLDER HANDLING
// ======================================================================
async function loadFolder() {
  const path = dom.folderPath.value.trim();
  if (!path) { showToast('Carpeta requerida', 'Ingresa la ruta de tu carpeta', 'warn'); return; }

  dom.loadFolderBtn.disabled = true;
  dom.loadFolderBtn.innerHTML = `<div class="btn-spinner"></div> Cargando`;

  try {
    const res  = await fetch('/api/browse-folder', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ path }),
    });
    const data = await res.json();

    if (data.error) {
      showToast('Error', data.error, 'error');
    } else {
      state.folderPath   = path;
      state.totalImages  = data.total;

      dom.previewCount.textContent = `${data.total} imagen${data.total !== 1 ? 'es' : ''} encontrada${data.total !== 1 ? 's' : ''}`;
      dom.previewStrip.innerHTML   = '';

      data.previews.forEach(img => {
        const el  = document.createElement('img');
        el.src    = `data:image/jpeg;base64,${img.thumbnail}`;
        el.alt    = img.filename;
        el.title  = img.filename;
        el.className = 'fp-thumb';
        dom.previewStrip.appendChild(el);
      });

      dom.folderPreview.classList.remove('hidden');
      dom.exportPath.value = path;

      // Enable search if query is set
      dom.searchBtn.disabled = dom.queryInput.value.trim() === '';

      showToast('Carpeta cargada', `${data.total} imágenes listas para buscar`, 'success');
    }
  } catch (e) {
    showToast('Sin conexión', 'No se pudo conectar con el servidor', 'error');
  } finally {
    dom.loadFolderBtn.disabled = false;
    dom.loadFolderBtn.innerHTML = `
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
      Cargar`;
  }
}

function resetFolder() {
  dom.folderPath.value = '';
  dom.folderPath.dispatchEvent(new Event('input'));
  dom.folderPreview.classList.add('hidden');
  dom.previewStrip.innerHTML = '';
  state.folderPath  = '';
  state.totalImages = 0;
  dom.searchBtn.disabled = true;
}

// ======================================================================
// SEARCH
// ======================================================================
async function startSearch() {
  const folder = dom.folderPath.value.trim();
  const query  = dom.queryInput.value.trim();

  if (!folder) { showToast('Carpeta requerida', 'Primero carga una carpeta', 'warn'); return; }
  if (!query)  { showToast('Búsqueda requerida', 'Escribe qué quieres buscar', 'warn'); return; }

  state.selectedQuery = query;
  saveToHistory(query);

  // UI: searching state
  dom.searchBtn.disabled = true;
  dom.searchBtnLabel.textContent = 'Buscando…';
  const spinnerHTML = `<div class="btn-spinner"></div>`;
  dom.searchBtn.querySelector('.search-btn-inner').insertAdjacentHTML('afterbegin', spinnerHTML);

  setStatus('busy', 'Analizando imágenes…');
  showPanel('progress');

  try {
    const res  = await fetch('/api/search', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        folder,
        query,
        threshold:   parseFloat(dom.thresholdSlider.value),
        max_results: parseInt(dom.maxResultsSlider.value),
      }),
    });
    const data = await res.json();

    if (data.error) {
      showToast('Error', data.error, 'error');
      showPanel('idle');
      resetSearchBtn();
    } else {
      startPolling();
    }
  } catch (e) {
    showToast('Error', 'No se pudo iniciar la búsqueda', 'error');
    showPanel('idle');
    resetSearchBtn();
  }
}

async function cancelSearch() {
  if (state.pollTimer) { clearInterval(state.pollTimer); state.pollTimer = null; }
  showPanel('idle');
  resetSearchBtn();
  setStatus('ok', 'CLIP listo');
  showToast('Cancelado', 'Búsqueda cancelada', 'warn');
}

function startPolling() {
  let firstProgress = true;
  state.pollTimer = setInterval(async () => {
    try {
      const res  = await fetch('/api/progress');
      const data = await res.json();

      if (firstProgress && data.total > 0) {
        dom.progressTitle.textContent    = 'Analizando imágenes…';
        dom.progressSubtitle.textContent = `Modelo CLIP procesando tu colección`;
        firstProgress = false;
      }

      updateProgressUI(data);

      if (!data.running) {
        clearInterval(state.pollTimer);
        state.pollTimer = null;
        if (data.error) {
          showToast('Error', data.error, 'error');
          showPanel('idle');
          setStatus('error', 'Error en búsqueda');
        } else {
          await fetchResults();
        }
        resetSearchBtn();
      }
    } catch (e) {
      clearInterval(state.pollTimer);
      state.pollTimer = null;
      showToast('Error', 'Error al obtener progreso', 'error');
      resetSearchBtn();
    }
  }, 350);
}

function updateProgressUI(data) {
  const pct  = data.total > 0 ? Math.round((data.processed / data.total) * 100) : 0;
  const circ = 2 * Math.PI * 60; // r=60, circumference = 377

  dom.ringProgress.style.strokeDashoffset = circ - (pct / 100) * circ;
  dom.ringPct.textContent       = `${pct}%`;
  dom.progressBarFill.style.width = `${pct}%`;
  dom.progressBarPct.textContent  = `${pct}%`;
  dom.psProcessed.textContent     = data.processed.toLocaleString();
  dom.psTotal.textContent         = data.total > 0 ? data.total.toLocaleString() : '—';
  dom.psMatches.textContent       = data.result_count || 0;

  if (data.current_file) {
    dom.progressFile.textContent = data.current_file;
  }
}

async function fetchResults() {
  try {
    const res  = await fetch('/api/results');
    const data = await res.json();
    state.currentResults = data.results || [];

    state.sortMode   = 'score-desc';
    state.filterMode = 'all';
    dom.sortSelect.value   = 'score-desc';
    dom.filterSelect.value = 'all';

    applyFilterSort();
    showPanel('results');

    const count = state.currentResults.length;
    dom.resultsCount.textContent    = `${count} resultado${count !== 1 ? 's' : ''}`;
    dom.resultsQueryBadge.textContent = `"${state.selectedQuery}"`;

    if (count === 0) {
      dom.gallery.classList.add('hidden');
      dom.noResults.classList.remove('hidden');
      showToast('Sin resultados', 'Intenta bajar el umbral de similitud', 'warn');
      setStatus('ok', '0 resultados');
    } else {
      dom.gallery.classList.remove('hidden');
      dom.noResults.classList.add('hidden');
      showToast('¡Listo!', `${count} imagen${count !== 1 ? 'es' : ''} encontrada${count !== 1 ? 's' : ''}`, 'success');
      setStatus('ok', `${count} coincidencias para "${state.selectedQuery}"`);
    }
    updateExportBtn();
  } catch (e) {
    showToast('Error', 'No se pudieron obtener resultados', 'error');
  }
}

// ======================================================================
// FILTER & SORT
// ======================================================================
function applyFilterSort() {
  let items = [...state.currentResults];

  // Filter
  switch (state.filterMode) {
    case 'high': items = items.filter(i => i.score >= 35); break;
    case 'mid':  items = items.filter(i => i.score >= 22 && i.score < 35); break;
    case 'low':  items = items.filter(i => i.score < 22);  break;
  }

  // Sort
  switch (state.sortMode) {
    case 'score-desc': items.sort((a, b) => b.score - a.score); break;
    case 'score-asc':  items.sort((a, b) => a.score - b.score); break;
    case 'name-asc':   items.sort((a, b) => a.filename.localeCompare(b.filename)); break;
    case 'name-desc':  items.sort((a, b) => b.filename.localeCompare(a.filename)); break;
  }

  state.filteredResults = items;
  dom.resultsCount.textContent = `${items.length} resultado${items.length !== 1 ? 's' : ''}`;
  renderGallery();
  updateExportBtn();
}

// ======================================================================
// GALLERY RENDERING
// ======================================================================
function renderGallery() {
  dom.gallery.innerHTML = '';
  state.filteredResults.forEach((img, idx) => {
    const card = createCard(img, idx);
    card.style.animationDelay = `${Math.min(idx * 0.03, 0.6)}s`;
    dom.gallery.appendChild(card);
  });
}

function createCard(img, idx) {
  const isGrid = dom.gallery.classList.contains('grid-view');
  const scoreClass = img.score >= 35 ? 'score-high' : img.score >= 22 ? 'score-mid' : 'score-low';
  const scoreLabel = `${img.score}%`;

  const card = document.createElement('div');
  card.className = `img-card${img.selected ? ' selected' : ''}`;
  card.dataset.idx = idx;

  card.innerHTML = `
    <div class="card-img-wrap">
      <img class="card-img" src="data:image/jpeg;base64,${img.thumbnail}" alt="${escHtml(img.filename)}" loading="lazy"/>
      <div class="card-overlay">
        <div class="card-overlay-actions">
          <button class="card-action" data-action="view" title="Ver en pantalla completa">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>
          </button>
        </div>
      </div>
      <div class="card-select-indicator" title="${img.selected ? 'Deseleccionar' : 'Seleccionar'}">
        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
      </div>
    </div>
    <div class="card-info">
      <span class="card-filename" title="${escHtml(img.path)}">${escHtml(img.filename)}</span>
      <span class="score-badge ${scoreClass}">${scoreLabel}</span>
    </div>
  `;

  // Events
  card.addEventListener('click', e => {
    const action = e.target.closest('[data-action]')?.dataset.action;
    if (action === 'view') {
      e.stopPropagation();
      openLightbox(idx);
    } else if (e.target.closest('.card-select-indicator')) {
      e.stopPropagation();
      toggleSelect(idx);
    } else {
      openLightbox(idx);
    }
  });

  card.addEventListener('dblclick', e => {
    e.preventDefault();
    toggleSelect(idx);
  });

  return card;
}

function toggleSelect(idx) {
  const item = state.filteredResults[idx];
  if (!item) return;
  item.selected = !item.selected;

  // Also sync with currentResults
  const orig = state.currentResults.find(r => r.path === item.path);
  if (orig) orig.selected = item.selected;

  const card = dom.gallery.querySelector(`[data-idx="${idx}"]`);
  if (card) {
    card.classList.toggle('selected', item.selected);
    const ind = card.querySelector('.card-select-indicator');
    if (ind) ind.title = item.selected ? 'Deseleccionar' : 'Seleccionar';
  }
  updateExportBtn();
  updateLightboxSelectBtn();
}

function updateExportBtn() {
  const count = state.filteredResults.filter(r => r.selected).length;
  dom.exportLabel.textContent = count > 0 ? `Exportar ${count}` : 'Exportar';
  dom.exportBtn.disabled      = count === 0;
}

function setViewMode(mode) {
  state.viewMode = mode;
  dom.gallery.classList.toggle('grid-view', mode === 'grid');
  dom.gallery.classList.toggle('list-view',  mode === 'list');
  dom.gridViewBtn.classList.toggle('active', mode === 'grid');
  dom.listViewBtn.classList.toggle('active',  mode === 'list');
  renderGallery();
}

// ======================================================================
// LIGHTBOX
// ======================================================================
function openLightbox(idx) {
  state.lightboxIdx = idx;
  renderLightbox();
  dom.lightbox.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeLightbox() {
  dom.lightbox.classList.add('hidden');
  document.body.style.overflow = '';
}

function navigateLightbox(dir) {
  const len = state.filteredResults.length;
  state.lightboxIdx = ((state.lightboxIdx + dir) + len) % len;
  renderLightbox();
}

function renderLightbox() {
  const img = state.filteredResults[state.lightboxIdx];
  if (!img) return;

  // Animate swap
  dom.lbImg.style.opacity = '0';
  dom.lbImg.style.transform = 'scale(0.97)';

  setTimeout(() => {
    dom.lbImg.src               = `data:image/jpeg;base64,${img.thumbnail}`;
    dom.lbImg.alt               = img.filename;
    dom.lbImg.style.opacity     = '1';
    dom.lbImg.style.transform   = 'scale(1)';
    dom.lbImg.style.transition  = 'opacity 0.25s ease, transform 0.25s ease';
  }, 100);

  dom.lbFilename.textContent = img.filename;

  const scoreClass = img.score >= 35 ? 'score-high' : img.score >= 22 ? 'score-mid' : 'score-low';
  dom.lbScore.className      = `lb-score-badge ${scoreClass}`;
  dom.lbScore.textContent    = `${img.score}% similitud`;
  dom.lbPath.textContent     = img.path;
  dom.lbCounter.textContent  = `${state.lightboxIdx + 1} / ${state.filteredResults.length}`;

  updateLightboxSelectBtn();
}

function updateLightboxSelectBtn() {
  const img = state.filteredResults[state.lightboxIdx];
  if (!img) return;
  dom.lbSelectBtn.classList.toggle('selected', img.selected);
  dom.lbSelectBtn.title = img.selected ? 'Deseleccionar' : 'Seleccionar';
}

// ======================================================================
// EXPORT MODAL
// ======================================================================
function openExportModal() {
  const count = state.filteredResults.filter(r => r.selected).length;
  if (count === 0) { showToast('Sin selección', 'Selecciona al menos una imagen', 'warn'); return; }
  dom.exportCount.textContent = count;

  if (state.selectedQuery) {
    const safe = state.selectedQuery.replace(/[^\w\s\-áéíóúñü]/gi, '').trim().replace(/\s+/g, '_').substring(0, 40);
    dom.subfolderName.value = safe || 'visosearch_resultados';
  }

  dom.exportModal.classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeExportModal() {
  dom.exportModal.classList.add('hidden');
  document.body.style.overflow = '';
}

async function doExport() {
  const dest = dom.exportPath.value.trim();
  if (!dest) { showToast('Ruta requerida', 'Ingresa la carpeta destino', 'warn'); return; }

  const paths = state.filteredResults.filter(r => r.selected).map(r => r.path);
  if (!paths.length) { showToast('Sin selección', 'No hay imágenes seleccionadas', 'warn'); return; }

  dom.confirmExportBtn.disabled = true;
  dom.confirmExportBtn.innerHTML = `<div class="btn-spinner"></div> Exportando…`;

  try {
    const res  = await fetch('/api/export', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        destination:      dest,
        paths,
        create_subfolder: dom.createSubfolderCheck.checked,
        subfolder_name:   dom.subfolderName.value.trim(),
      }),
    });
    const data = await res.json();

    if (data.error) {
      showToast('Error', data.error, 'error');
    } else {
      closeExportModal();
      showToast(
        '¡Exportado!',
        `${data.copied} imagen${data.copied !== 1 ? 'es' : ''} guardada${data.copied !== 1 ? 's' : ''} en: ${data.destination}`,
        'success'
      );
    }
  } catch (e) {
    showToast('Error', 'Error al exportar: ' + e.message, 'error');
  } finally {
    dom.confirmExportBtn.disabled  = false;
    dom.confirmExportBtn.innerHTML = `
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      Exportar ahora`;
  }
}

// ======================================================================
// PANELS
// ======================================================================
function showPanel(name) {
  dom.idleState.classList.add('hidden');
  dom.progressState.classList.add('hidden');
  dom.resultsState.classList.add('hidden');

  if (name === 'idle')     dom.idleState.classList.remove('hidden');
  if (name === 'progress') dom.progressState.classList.remove('hidden');
  if (name === 'results')  dom.resultsState.classList.remove('hidden');
}

function resetSearchBtn() {
  const hasPath  = dom.folderPath.value.trim() !== '';
  const hasQuery = dom.queryInput.value.trim() !== '';
  dom.searchBtn.disabled = !hasPath || !hasQuery;
  dom.searchBtnLabel.textContent = 'Buscar con IA';

  // Remove any spinner
  const spinner = dom.searchBtn.querySelector('.btn-spinner');
  if (spinner) spinner.remove();
}

// ======================================================================
// TOAST NOTIFICATIONS
// ======================================================================
const TOAST_ICONS = {
  success: `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`,
  error:   `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`,
  info:    `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>`,
  warn:    `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
};

function showToast(title, message, type = 'info') {
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `
    <div class="toast-icon-wrap">${TOAST_ICONS[type] || TOAST_ICONS.info}</div>
    <div class="toast-body">
      <div class="toast-title">${escHtml(title)}</div>
      <div class="toast-msg">${escHtml(message)}</div>
    </div>
    <button class="toast-dismiss" title="Cerrar">✕</button>
  `;

  toast.querySelector('.toast-dismiss').addEventListener('click', () => dismissToast(toast));
  dom.toastContainer.appendChild(toast);

  setTimeout(() => dismissToast(toast), 5000);
}

function dismissToast(toast) {
  toast.classList.add('fade-out');
  setTimeout(() => toast.remove(), 320);
}

// ======================================================================
// UTILITIES
// ======================================================================
function escHtml(str) {
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}
