const STORAGE_KEY = 'davinci-notebooks-v1';
const PORTRAIT_WIDTH = 1240;
const PORTRAIT_HEIGHT = 1754;
const DEFAULT_BOOK_COLOR = '#4F46E5';

const paperCanvas = document.getElementById('paperCanvas');
const drawingCanvas = document.getElementById('drawingCanvas');
const paperContext = paperCanvas.getContext('2d');
const drawingContext = drawingCanvas.getContext('2d');

let state = { books: [], activeBookId: null, activePageId: null };
let tool = 'pencil';
let brushColor = '#1B1F35';
let pencilSize = 4;
let eraserSize = 22;
let isDrawing = false;
let lastPoint = null;
let histories = new Map();
let bookModalMode = 'create';
let toastTimer;
let cloudReady = false;
let cloudSaving = false;
let cloudSavePending = false;
let cloudSaveTimer;
let canvasZoom = 1;
let rulerVisible = false;
let rulerPosition = { x: .5, y: .22 };
let rulerAngle = 0;
let rulerDragPointerId = null;

function uid(prefix) {
    return `${prefix}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
}

function createPage(title = 'Página 1', template = 'blank', orientation = 'portrait') {
    return { id: uid('page'), title, template, orientation, drawing: '' };
}

function createBook(name = 'Mi primer cuaderno', color = DEFAULT_BOOK_COLOR) {
    return { id: uid('book'), name, color, createdAt: Date.now(), pages: [createPage('Página 1', 'blank')] };
}

function getActiveBook() {
    return state.books.find(book => book.id === state.activeBookId) || null;
}

function getActivePage() {
    const book = getActiveBook();
    return book?.pages.find(page => page.id === state.activePageId) || null;
}

function normalizeState() {
    if (!Array.isArray(state.books) || !state.books.length) {
        const initialBook = createBook();
        state = { books: [initialBook], activeBookId: initialBook.id, activePageId: initialBook.pages[0].id };
        return;
    }
    state.books.forEach(book => {
        if (!/^#[0-9a-f]{6}$/i.test(book.color || '')) book.color = DEFAULT_BOOK_COLOR;
        if (!Array.isArray(book.pages) || !book.pages.length) book.pages = [createPage('Página 1', 'blank')];
        book.pages.forEach(page => {
            page.template = page.template === 'grid' ? 'grid' : 'blank';
            page.orientation = page.orientation === 'landscape' ? 'landscape' : 'portrait';
            page.drawing = page.drawing || '';
        });
    });
    if (!getActiveBook()) state.activeBookId = state.books[0].id;
    const book = getActiveBook();
    if (!getActivePage()) state.activePageId = book.pages[0].id;
}

function loadState() {
    try {
        const stored = JSON.parse(localStorage.getItem(STORAGE_KEY));
        if (stored && Array.isArray(stored.books)) state = stored;
    } catch (error) {
        console.warn('No fue posible recuperar los cuadernos', error);
    }
    normalizeState();
}

function serializedState() {
    return {
        activeBookId: state.activeBookId,
        activePageId: state.activePageId,
        updatedAt: state.updatedAt || Date.now(),
        books: state.books.map(book => ({
            id: book.id,
            name: book.name,
            color: book.color,
            createdAt: book.createdAt,
            pages: book.pages.map(page => ({ id: page.id, title: page.title, template: page.template, orientation: page.orientation, drawing: page.drawing || '' }))
        }))
    };
}

function persist({ sync = true } = {}) {
    try {
        if (sync) state.updatedAt = Date.now();
        localStorage.setItem(STORAGE_KEY, JSON.stringify(serializedState()));
        if (sync && cloudReady) {
            cloudSavePending = true;
            updateSaveStatus('Sincronizando…', 'sync');
            scheduleCloudSave();
        } else if (!cloudReady) {
            updateSaveStatus('Guardado localmente');
        }
    } catch (error) {
        console.warn('No fue posible guardar los cuadernos', error);
        updateSaveStatus('No se pudo guardar', 'error');
        showToast('El espacio del navegador está lleno. Exporta tus cuadernos para conservarlos.');
    }
}

function scheduleCloudSave(immediate = false) {
    if (!cloudReady) return;
    clearTimeout(cloudSaveTimer);
    cloudSaveTimer = setTimeout(syncToCloud, immediate ? 0 : 650);
}

async function syncToCloud() {
    if (!cloudReady || cloudSaving || !cloudSavePending) return;
    cloudSaving = true;
    cloudSavePending = false;
    try {
        const response = await fetch('api.php?action=save', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ state: serializedState() })
        });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Error de sincronización');
        updateSaveStatus('Sincronizado en todos tus dispositivos');
    } catch (error) {
        console.warn('No fue posible sincronizar DaVinci', error);
        updateSaveStatus('Sin conexión · guardado local', 'warning');
    } finally {
        cloudSaving = false;
        if (cloudSavePending) scheduleCloudSave();
    }
}

async function restoreCloudState() {
    const localUpdatedAt = Number(state.updatedAt || 0);
    updateSaveStatus('Recuperando tus cuadernos…', 'sync');
    try {
        const response = await fetch('api.php?action=load', { credentials: 'same-origin' });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'No fue posible recuperar tus datos');
        cloudReady = true;
        const remoteState = result.data?.state;
        const remoteUpdatedAt = Number(remoteState?.updatedAt || 0);
        if (remoteState && Array.isArray(remoteState.books) && remoteUpdatedAt >= localUpdatedAt) {
            state = remoteState;
            histories = new Map();
            normalizeState();
            persist({ sync: false });
            await renderApp();
            updateSaveStatus('Sincronizado en todos tus dispositivos');
        } else if (remoteState && Array.isArray(remoteState.books)) {
            cloudSavePending = true;
            scheduleCloudSave(true);
        } else {
            cloudSavePending = true;
            scheduleCloudSave(true);
        }
    } catch (error) {
        console.warn('No fue posible recuperar DaVinci desde el servidor', error);
        updateSaveStatus('Sin conexión · guardado local', 'warning');
    }
}

function getPageDimensions(page) {
    return page?.orientation === 'landscape'
        ? { width: PORTRAIT_HEIGHT, height: PORTRAIT_WIDTH }
        : { width: PORTRAIT_WIDTH, height: PORTRAIT_HEIGHT };
}

function resizeCanvases(page) {
    const { width, height } = getPageDimensions(page);
    if (paperCanvas.width !== width || paperCanvas.height !== height) {
        paperCanvas.width = width;
        paperCanvas.height = height;
        drawingCanvas.width = width;
        drawingCanvas.height = height;
    }
    document.getElementById('canvasStage').classList.toggle('is-landscape', page?.orientation === 'landscape');
    requestAnimationFrame(applyZoom);
}

function setZoom(value) {
    canvasZoom = Math.max(.5, Math.min(2, value));
    const percentage = Math.round(canvasZoom * 100);
    document.getElementById('zoomRange').value = percentage;
    document.getElementById('zoomValue').textContent = `${percentage}%`;
    applyZoom();
}

function applyZoom() {
    const viewport = document.getElementById('canvasViewport');
    const page = getActivePage();
    const wrap = document.getElementById('paperWrap');
    if (!viewport || !page || !wrap) return;
    const stage = document.getElementById('canvasStage');
    const isPresentation = Boolean(document.fullscreenElement) || stage.classList.contains('is-pseudo-fullscreen');
    const defaultWidth = page.orientation === 'landscape' ? 780 : 590;
    const presentationWidth = page.orientation === 'landscape' ? viewport.clientHeight * 1.36 : viewport.clientHeight * .68;
    const naturalWidth = isPresentation ? Math.max(defaultWidth, presentationWidth) : defaultWidth;
    const availableWidth = Math.max(220, viewport.clientWidth - 10);
    const baseWidth = Math.min(naturalWidth, availableWidth);
    wrap.style.width = `${Math.round(baseWidth * canvasZoom)}px`;
    renderRuler();
}

function drawPaper(context, template, width, height) {
    context.save();
    context.clearRect(0, 0, width, height);
    context.fillStyle = '#fffefd';
    context.fillRect(0, 0, width, height);
    if (template === 'grid') {
        const size = 34;
        context.strokeStyle = '#dce3f0';
        context.lineWidth = 1;
        context.beginPath();
        for (let x = 0; x <= width; x += size) { context.moveTo(x, 0); context.lineTo(x, height); }
        for (let y = 0; y <= height; y += size) { context.moveTo(0, y); context.lineTo(width, y); }
        context.stroke();
    }
    context.restore();
}

function imageFromData(dataUrl) {
    return new Promise((resolve, reject) => {
        if (!dataUrl) { resolve(null); return; }
        const image = new Image();
        image.onload = () => resolve(image);
        image.onerror = reject;
        image.src = dataUrl;
    });
}

async function renderActivePage() {
    const page = getActivePage();
    if (!page) return;
    const renderedPageId = page.id;
    resizeCanvases(page);
    const { width, height } = getPageDimensions(page);
    drawPaper(paperContext, page.template, width, height);
    drawingContext.clearRect(0, 0, width, height);
    try {
        const image = await imageFromData(page.drawing);
        if (image && getActivePage()?.id === renderedPageId) drawingContext.drawImage(image, 0, 0, width, height);
    } catch (error) {
        console.warn('No fue posible cargar el dibujo de la página', error);
    }
    const existing = histories.get(page.id);
    if (!existing) histories.set(page.id, { items: [page.drawing || ''], index: 0 });
    updateHistoryButtons();
    applyZoom();
}

function getHistory(page) {
    if (!histories.has(page.id)) histories.set(page.id, { items: [page.drawing || ''], index: 0 });
    return histories.get(page.id);
}

function snapshotPage() {
    const page = getActivePage();
    if (!page) return;
    const drawing = drawingCanvas.toDataURL('image/png');
    page.drawing = drawing;
    const history = getHistory(page);
    if (history.items[history.index] === drawing) return;
    history.items.splice(history.index + 1);
    history.items.push(drawing);
    if (history.items.length > 24) history.items.shift();
    history.index = history.items.length - 1;
    persist();
    renderPages();
    updateHistoryButtons();
}

async function restoreHistory(direction) {
    const page = getActivePage();
    if (!page) return;
    const history = getHistory(page);
    const nextIndex = history.index + direction;
    if (nextIndex < 0 || nextIndex >= history.items.length) return;
    history.index = nextIndex;
    page.drawing = history.items[history.index];
    persist();
    await renderActivePage();
    renderPages();
}

function updateHistoryButtons() {
    const page = getActivePage();
    const history = page ? getHistory(page) : { items: [], index: 0 };
    document.getElementById('undoButton').disabled = history.index <= 0;
    document.getElementById('redoButton').disabled = history.index >= history.items.length - 1;
}

function renderBooks() {
    const list = document.getElementById('bookList');
    list.innerHTML = '';
    state.books.forEach(book => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = `book-item ${book.id === state.activeBookId ? 'is-active' : ''}`;
        item.style.setProperty('--book-color', book.color || DEFAULT_BOOK_COLOR);
        item.innerHTML = `<span class="book-item-icon"><i class="fa-solid fa-book"></i></span><span><strong>${escapeHtml(book.name)}</strong><small>${book.pages.length} página${book.pages.length === 1 ? '' : 's'}</small></span>`;
        item.addEventListener('click', () => activateBook(book.id));
        list.appendChild(item);
    });
    document.getElementById('bookCount').textContent = state.books.length;
}

function thumbnailBackground(page) {
    const drawing = page.drawing ? `url("${page.drawing}")` : '';
    const grid = page.template === 'grid'
        ? 'linear-gradient(#d5daf0 1px, transparent 1px), linear-gradient(90deg, #d5daf0 1px, transparent 1px)'
        : '';
    return [drawing, grid].filter(Boolean).join(', ');
}

function renderPages() {
    const book = getActiveBook();
    const list = document.getElementById('pageList');
    list.innerHTML = '';
    if (!book) return;
    book.pages.forEach((page, index) => {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = `page-item ${page.id === state.activePageId ? 'is-active' : ''}`;
        const templateName = page.template === 'grid' ? 'Cuadriculada' : 'Blanca';
        const orientationName = page.orientation === 'landscape' ? 'Horizontal' : 'Vertical';
        item.innerHTML = `<span class="page-thumbnail ${page.template === 'grid' ? 'grid' : ''} ${page.orientation === 'landscape' ? 'landscape' : ''}"></span><span><strong>${escapeHtml(page.title || `Página ${index + 1}`)}</strong><small>${templateName} · ${orientationName}</small></span>`;
        const thumbnail = item.querySelector('.page-thumbnail');
        thumbnail.style.backgroundImage = thumbnailBackground(page);
        thumbnail.style.backgroundSize = page.drawing ? (page.template === 'grid' ? 'cover, 7px 7px' : 'cover') : '';
        item.addEventListener('click', () => activatePage(page.id));
        list.appendChild(item);
    });
    document.getElementById('pageCount').textContent = `${book.pages.length} página${book.pages.length === 1 ? '' : 's'}`;
}

function renderHeader() {
    const book = getActiveBook();
    const page = getActivePage();
    document.getElementById('activeBookTitle').textContent = book?.name || 'Cuaderno';
    document.getElementById('bookLabel').textContent = page ? `Cuaderno · ${page.title}` : 'Cuaderno';
}

async function renderApp() {
    normalizeState();
    renderBooks();
    renderPages();
    renderHeader();
    updateOrientationControls();
    await renderActivePage();
}

async function activateBook(bookId) {
    state.activeBookId = bookId;
    const book = getActiveBook();
    state.activePageId = book.pages[0].id;
    persist();
    await renderApp();
    closeMobileLibrary();
}

async function activatePage(pageId) {
    state.activePageId = pageId;
    persist();
    renderPages();
    renderHeader();
    await renderActivePage();
}

function pointFromEvent(event) {
    const rect = drawingCanvas.getBoundingClientRect();
    return { x: (event.clientX - rect.left) * (drawingCanvas.width / rect.width), y: (event.clientY - rect.top) * (drawingCanvas.height / rect.height) };
}

function prepareStroke() {
    drawingContext.lineCap = 'round';
    drawingContext.lineJoin = 'round';
    drawingContext.lineWidth = tool === 'eraser' ? eraserSize : pencilSize;
    drawingContext.strokeStyle = brushColor;
    drawingContext.globalCompositeOperation = tool === 'eraser' ? 'destination-out' : 'source-over';
}

function startDrawing(event) {
    if (event.pointerType === 'mouse' && event.button !== 0) return;
    isDrawing = true;
    lastPoint = pointFromEvent(event);
    prepareStroke();
    drawingContext.beginPath();
    drawingContext.arc(lastPoint.x, lastPoint.y, drawingContext.lineWidth / 2, 0, Math.PI * 2);
    drawingContext.fillStyle = tool === 'eraser' ? 'rgba(0,0,0,1)' : brushColor;
    drawingContext.fill();
    drawingContext.beginPath();
    drawingContext.moveTo(lastPoint.x, lastPoint.y);
    drawingCanvas.setPointerCapture(event.pointerId);
    event.preventDefault();
}

function draw(event) {
    if (!isDrawing) return;
    const point = pointFromEvent(event);
    drawingContext.lineTo(point.x, point.y);
    drawingContext.stroke();
    lastPoint = point;
    event.preventDefault();
}

function stopDrawing(event) {
    if (!isDrawing) return;
    isDrawing = false;
    drawingContext.closePath();
    drawingContext.globalCompositeOperation = 'source-over';
    if (event?.pointerId !== undefined && drawingCanvas.hasPointerCapture(event.pointerId)) drawingCanvas.releasePointerCapture(event.pointerId);
    snapshotPage();
}

function setTool(nextTool) {
    tool = nextTool;
    const pencil = document.getElementById('pencilTool');
    const eraser = document.getElementById('eraserTool');
    pencil.classList.toggle('is-active', tool === 'pencil');
    eraser.classList.toggle('is-active', tool === 'eraser');
    pencil.setAttribute('aria-pressed', String(tool === 'pencil'));
    eraser.setAttribute('aria-pressed', String(tool === 'eraser'));
    drawingCanvas.style.cursor = tool === 'eraser' ? 'cell' : 'crosshair';
    updateSizeControls();
}

function setColor(color) {
    brushColor = color;
    setTool('pencil');
    document.querySelectorAll('.color-swatch').forEach(swatch => swatch.classList.toggle('is-selected', swatch.dataset.color.toLowerCase() === color.toLowerCase()));
    document.getElementById('colorPicker').value = color;
}

function updateSizeControls() {
    document.getElementById('pencilSizeControl').hidden = tool !== 'pencil';
    document.getElementById('eraserSizeControl').hidden = tool !== 'eraser';
}

function updateOrientationControls() {
    const isLandscape = getActivePage()?.orientation === 'landscape';
    const portrait = document.getElementById('portraitOrientationButton');
    const landscape = document.getElementById('landscapeOrientationButton');
    portrait.classList.toggle('is-active', !isLandscape);
    landscape.classList.toggle('is-active', isLandscape);
    portrait.setAttribute('aria-pressed', String(!isLandscape));
    landscape.setAttribute('aria-pressed', String(isLandscape));
}

async function setPageOrientation(orientation) {
    const page = getActivePage();
    if (!page || page.orientation === orientation) return;
    page.drawing = drawingCanvas.toDataURL('image/png');
    page.orientation = orientation;
    histories.set(page.id, { items: [page.drawing], index: 0 });
    persist();
    await renderApp();
    showToast(`Página en orientación ${orientation === 'landscape' ? 'horizontal' : 'vertical'}.`);
}

async function toggleCanvasFullscreen() {
    const stage = document.getElementById('canvasStage');
    try {
        if (document.fullscreenElement) {
            await document.exitFullscreen();
        } else if (stage.classList.contains('is-pseudo-fullscreen')) {
            stage.classList.remove('is-pseudo-fullscreen');
            updateFullscreenButton();
        } else if (stage.requestFullscreen) {
            await stage.requestFullscreen();
            window.setTimeout(() => {
                if (!document.fullscreenElement) {
                    stage.classList.add('is-pseudo-fullscreen');
                    updateFullscreenButton();
                }
            }, 120);
        } else {
            stage.classList.add('is-pseudo-fullscreen');
            updateFullscreenButton();
        }
    } catch (error) {
        console.warn('No fue posible abrir la pantalla completa', error);
        stage.classList.add('is-pseudo-fullscreen');
        updateFullscreenButton();
        showToast('Se activó el modo de presentación del lienzo.');
    }
    window.setTimeout(applyZoom, 160);
}

function updateFullscreenButton() {
    const stage = document.getElementById('canvasStage');
    const isFullscreen = Boolean(document.fullscreenElement) || stage.classList.contains('is-pseudo-fullscreen');
    const button = document.getElementById('fullscreenCanvasButton');
    button.setAttribute('aria-pressed', String(isFullscreen));
    button.setAttribute('aria-label', isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa del lienzo');
    button.title = isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa';
    button.innerHTML = `<i class="fa-solid fa-${isFullscreen ? 'compress' : 'expand'}" aria-hidden="true"></i>`;
    const stageButton = document.getElementById('stageFullscreenButton');
    stageButton.setAttribute('aria-label', isFullscreen ? 'Salir de pantalla completa' : 'Pantalla completa del lienzo');
    stageButton.innerHTML = `<i class="fa-solid fa-${isFullscreen ? 'compress' : 'expand'}" aria-hidden="true"></i><span>${isFullscreen ? 'Salir' : 'Pantalla completa'}</span>`;
    requestAnimationFrame(applyZoom);
}

function updateSaveStatus(message = 'Sincronizado en todos tus dispositivos', mode = 'ok') {
    const status = document.getElementById('saveStatus');
    if (!status) return;
    const icon = mode === 'error' ? 'triangle-exclamation' : mode === 'sync' ? 'arrows-rotate' : mode === 'warning' ? 'cloud' : 'cloud-check';
    status.dataset.mode = mode;
    status.innerHTML = `<i class="fa-solid fa-${icon}" aria-hidden="true"></i> ${message}`;
}

function updateRuler() {
    const ruler = document.getElementById('ruler');
    const button = document.getElementById('rulerToggleButton');
    ruler.hidden = !rulerVisible;
    button.classList.toggle('is-active', rulerVisible);
    button.setAttribute('aria-pressed', String(rulerVisible));
    button.title = rulerVisible ? 'Ocultar regla' : 'Mostrar regla';
    renderRuler();
}

function renderRuler() {
    const ruler = document.getElementById('ruler');
    if (!ruler || !rulerVisible) return;
    ruler.style.left = `${rulerPosition.x * 100}%`;
    ruler.style.top = `${rulerPosition.y * 100}%`;
    ruler.style.transform = `translate(-50%, -50%) rotate(${rulerAngle}deg)`;
}

function moveRuler(event) {
    if (rulerDragPointerId !== event.pointerId) return;
    const wrapRect = document.getElementById('paperWrap').getBoundingClientRect();
    rulerPosition = {
        x: Math.max(.06, Math.min(.94, (event.clientX - wrapRect.left) / wrapRect.width)),
        y: Math.max(.04, Math.min(.96, (event.clientY - wrapRect.top) / wrapRect.height))
    };
    renderRuler();
    event.preventDefault();
}

function openModal(id) {
    document.getElementById(id).hidden = false;
    const input = document.querySelector(`#${id} input[type="text"]`);
    if (input) setTimeout(() => input.focus(), 50);
}

function closeModal(id) { document.getElementById(id).hidden = true; }

function showToast(message) {
    const toast = document.getElementById('toast');
    clearTimeout(toastTimer);
    toast.textContent = message;
    toast.hidden = false;
    toastTimer = setTimeout(() => { toast.hidden = true; }, 3200);
}

function filename(value) {
    return (value || 'davinci').normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/[^a-z0-9]+/gi, '-').replace(/(^-|-$)/g, '').toLowerCase();
}

async function composePage(page) {
    const { width, height } = getPageDimensions(page);
    const output = document.createElement('canvas');
    output.width = width;
    output.height = height;
    const context = output.getContext('2d');
    drawPaper(context, page.template, width, height);
    const image = await imageFromData(page.drawing);
    if (image) context.drawImage(image, 0, 0, width, height);
    return output;
}

function downloadData(dataUrl, name) {
    const anchor = document.createElement('a');
    anchor.href = dataUrl;
    anchor.download = name;
    document.body.appendChild(anchor);
    anchor.click();
    anchor.remove();
}

async function exportPageImage() {
    const page = getActivePage();
    const book = getActiveBook();
    if (!page || !book) return;
    const output = await composePage(page);
    downloadData(output.toDataURL('image/png'), `${filename(book.name)}-${filename(page.title)}.png`);
    showToast('Imagen de la página guardada.');
}

function createPdf(orientation = 'portrait') {
    if (!window.jspdf?.jsPDF) {
        showToast('No se pudo cargar el generador de PDF. Revisa tu conexión e inténtalo otra vez.');
        return null;
    }
    return new window.jspdf.jsPDF({ orientation, unit: 'pt', format: 'a4', compress: true });
}

function pdfDimensions(orientation) {
    return orientation === 'landscape' ? { width: 841.89, height: 595.28 } : { width: 595.28, height: 841.89 };
}

async function exportPagePdf() {
    const page = getActivePage();
    const book = getActiveBook();
    const pdf = createPdf(page?.orientation || 'portrait');
    if (!page || !book || !pdf) return;
    const output = await composePage(page);
    const size = pdfDimensions(page.orientation);
    pdf.addImage(output.toDataURL('image/jpeg', .92), 'JPEG', 0, 0, size.width, size.height, undefined, 'FAST');
    pdf.save(`${filename(book.name)}-${filename(page.title)}.pdf`);
    showToast('PDF de la página guardado.');
}

async function exportBookPdf() {
    const book = getActiveBook();
    const pdf = createPdf(book?.pages[0]?.orientation || 'portrait');
    if (!book || !pdf) return;
    showToast(`Preparando ${book.pages.length} página${book.pages.length === 1 ? '' : 's'} para PDF...`);
    for (let index = 0; index < book.pages.length; index++) {
        if (index > 0) pdf.addPage('a4', book.pages[index].orientation || 'portrait');
        const output = await composePage(book.pages[index]);
        const size = pdfDimensions(book.pages[index].orientation);
        pdf.addImage(output.toDataURL('image/jpeg', .9), 'JPEG', 0, 0, size.width, size.height, undefined, 'FAST');
    }
    pdf.save(`${filename(book.name)}-cuaderno-completo.pdf`);
    showToast('PDF del cuaderno completo guardado.');
}

function closeMobileLibrary() { document.querySelector('.library-panel').classList.remove('is-open'); }

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value || '';
    return element.innerHTML;
}

function bindEvents() {
    drawingCanvas.addEventListener('pointerdown', startDrawing);
    drawingCanvas.addEventListener('pointermove', draw);
    drawingCanvas.addEventListener('pointerup', stopDrawing);
    drawingCanvas.addEventListener('pointercancel', stopDrawing);
    drawingCanvas.addEventListener('pointerleave', event => { if (event.buttons === 0) stopDrawing(event); });

    document.getElementById('pencilTool').addEventListener('click', () => setTool('pencil'));
    document.getElementById('eraserTool').addEventListener('click', () => setTool('eraser'));
    document.querySelectorAll('.color-swatch').forEach(swatch => swatch.addEventListener('click', () => setColor(swatch.dataset.color)));
    document.getElementById('colorPicker').addEventListener('input', event => setColor(event.target.value));
    document.getElementById('brushSize').addEventListener('input', event => { pencilSize = Number(event.target.value); document.getElementById('brushSizeValue').textContent = `${pencilSize} px`; });
    document.getElementById('eraserSize').addEventListener('input', event => { eraserSize = Number(event.target.value); document.getElementById('eraserSizeValue').textContent = `${eraserSize} px`; });
    document.getElementById('portraitOrientationButton').addEventListener('click', () => setPageOrientation('portrait'));
    document.getElementById('landscapeOrientationButton').addEventListener('click', () => setPageOrientation('landscape'));
    document.getElementById('fullscreenCanvasButton').addEventListener('click', toggleCanvasFullscreen);
    document.getElementById('stageFullscreenButton').addEventListener('click', toggleCanvasFullscreen);
    document.addEventListener('fullscreenchange', updateFullscreenButton);
    document.getElementById('zoomRange').addEventListener('input', event => setZoom(Number(event.target.value) / 100));
    document.getElementById('zoomOutButton').addEventListener('click', () => setZoom(canvasZoom - .1));
    document.getElementById('zoomInButton').addEventListener('click', () => setZoom(canvasZoom + .1));
    document.getElementById('rulerToggleButton').addEventListener('click', () => { rulerVisible = !rulerVisible; updateRuler(); });
    const ruler = document.getElementById('ruler');
    ruler.addEventListener('pointerdown', event => {
        rulerDragPointerId = event.pointerId;
        ruler.setPointerCapture(event.pointerId);
        event.preventDefault();
        event.stopPropagation();
    });
    ruler.addEventListener('pointermove', moveRuler);
    ruler.addEventListener('pointerup', event => {
        if (rulerDragPointerId !== event.pointerId) return;
        rulerDragPointerId = null;
        if (ruler.hasPointerCapture(event.pointerId)) ruler.releasePointerCapture(event.pointerId);
    });
    ruler.addEventListener('pointercancel', () => { rulerDragPointerId = null; });
    ruler.addEventListener('dblclick', event => {
        rulerAngle = rulerAngle === 0 ? 90 : 0;
        renderRuler();
        event.preventDefault();
    });
    window.addEventListener('resize', applyZoom);
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && document.getElementById('canvasStage').classList.contains('is-pseudo-fullscreen')) {
            document.getElementById('canvasStage').classList.remove('is-pseudo-fullscreen');
            updateFullscreenButton();
        }
    });
    document.getElementById('undoButton').addEventListener('click', () => restoreHistory(-1));
    document.getElementById('redoButton').addEventListener('click', () => restoreHistory(1));

    document.getElementById('newBookButton').addEventListener('click', () => {
        bookModalMode = 'create';
        document.getElementById('bookModalTitle').textContent = 'Nuevo cuaderno';
        document.getElementById('bookNameInput').value = '';
        document.getElementById('bookColorInput').value = DEFAULT_BOOK_COLOR;
        document.querySelector('#bookForm button[type="submit"]').textContent = 'Crear cuaderno';
        openModal('bookModal');
    });
    document.getElementById('renameBookButton').addEventListener('click', () => {
        bookModalMode = 'rename';
        document.getElementById('bookModalTitle').textContent = 'Renombrar cuaderno';
        document.getElementById('bookNameInput').value = getActiveBook()?.name || '';
        document.getElementById('bookColorInput').value = getActiveBook()?.color || DEFAULT_BOOK_COLOR;
        document.querySelector('#bookForm button[type="submit"]').textContent = 'Guardar cambios';
        openModal('bookModal');
    });
    document.getElementById('bookForm').addEventListener('submit', async event => {
        event.preventDefault();
        const input = document.getElementById('bookNameInput');
        const name = input.value.trim();
        const color = document.getElementById('bookColorInput').value;
        if (!name) return;
        if (bookModalMode === 'rename') {
            getActiveBook().name = name;
            getActiveBook().color = color;
            showToast('Cuaderno renombrado.');
        } else {
            const book = createBook(name, color);
            state.books.unshift(book);
            state.activeBookId = book.id;
            state.activePageId = book.pages[0].id;
            histories = new Map();
            showToast('Nuevo cuaderno creado.');
        }
        persist();
        closeModal('bookModal');
        await renderApp();
    });

    document.getElementById('newPageButton').addEventListener('click', () => {
        const nextNumber = (getActiveBook()?.pages.length || 0) + 1;
        document.getElementById('pageNameInput').value = `Página ${nextNumber}`;
        document.querySelector('input[name="pageTemplate"][value="blank"]').checked = true;
        document.querySelector('input[name="pageOrientation"][value="portrait"]').checked = true;
        updateTemplateSelection();
        updatePageOrientationSelection();
        openModal('pageModal');
    });
    document.getElementById('pageForm').addEventListener('submit', async event => {
        event.preventDefault();
        const book = getActiveBook();
        const title = document.getElementById('pageNameInput').value.trim() || `Página ${book.pages.length + 1}`;
        const template = document.querySelector('input[name="pageTemplate"]:checked').value;
        const orientation = document.querySelector('input[name="pageOrientation"]:checked').value;
        const page = createPage(title, template, orientation);
        book.pages.push(page);
        state.activePageId = page.id;
        persist();
        closeModal('pageModal');
        await renderApp();
        showToast('Página añadida al cuaderno.');
    });
    document.querySelectorAll('input[name="pageTemplate"]').forEach(input => input.addEventListener('change', updateTemplateSelection));
    document.querySelectorAll('input[name="pageOrientation"]').forEach(input => input.addEventListener('change', updatePageOrientationSelection));

    document.querySelectorAll('[data-close-modal]').forEach(button => button.addEventListener('click', () => closeModal(button.dataset.closeModal)));
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.addEventListener('click', event => { if (event.target === backdrop) backdrop.hidden = true; }));

    document.getElementById('clearButton').addEventListener('click', () => document.getElementById('confirmModal').hidden = false);
    document.getElementById('cancelClearButton').addEventListener('click', () => closeModal('confirmModal'));
    document.getElementById('confirmClearButton').addEventListener('click', () => {
        drawingContext.clearRect(0, 0, drawingCanvas.width, drawingCanvas.height);
        snapshotPage();
        closeModal('confirmModal');
        showToast('Página limpiada.');
    });

    const exportMenu = document.getElementById('exportMenu');
    const exportButton = document.getElementById('exportMenuButton');
    exportButton.addEventListener('click', () => { exportMenu.hidden = !exportMenu.hidden; exportButton.setAttribute('aria-expanded', String(!exportMenu.hidden)); });
    exportMenu.addEventListener('click', async event => {
        const action = event.target.closest('[data-export]')?.dataset.export;
        if (!action) return;
        exportMenu.hidden = true;
        exportButton.setAttribute('aria-expanded', 'false');
        if (action === 'image') await exportPageImage();
        if (action === 'page-pdf') await exportPagePdf();
        if (action === 'book-pdf') await exportBookPdf();
    });
    document.addEventListener('click', event => { if (!event.target.closest('.export-menu-wrap')) { exportMenu.hidden = true; exportButton.setAttribute('aria-expanded', 'false'); } });

    document.getElementById('mobileLibraryButton').addEventListener('click', () => document.querySelector('.library-panel').classList.toggle('is-open'));
}

function updateTemplateSelection() {
    document.querySelectorAll('.template-option').forEach(option => option.classList.toggle('is-selected', option.querySelector('input').checked));
}

function updatePageOrientationSelection() {
    document.querySelectorAll('.orientation-option').forEach(option => option.classList.toggle('is-selected', option.querySelector('input').checked));
}

window.addEventListener('beforeunload', () => {
    const page = getActivePage();
    if (page && isDrawing) page.drawing = drawingCanvas.toDataURL('image/png');
    persist();
    if (cloudReady && navigator.sendBeacon) {
        const payload = JSON.stringify({ state: serializedState() });
        if (payload.length < 60000) navigator.sendBeacon('api.php?action=save', new Blob([payload], { type: 'application/json' }));
    }
});

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState !== 'hidden') return;
    const page = getActivePage();
    if (page && isDrawing) page.drawing = drawingCanvas.toDataURL('image/png');
    persist();
    if (cloudReady) syncToCloud();
});

async function initializeApp() {
    loadState();
    bindEvents();
    setTool('pencil');
    setZoom(1);
    updateRuler();
    await renderApp();
    await restoreCloudState();
}

initializeApp();
