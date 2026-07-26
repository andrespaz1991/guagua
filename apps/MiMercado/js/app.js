// ==========================================
// MiMercado - App Logic
// ==========================================

const API_URL = 'api.php';
let chartQ, chartCat;
let chartStatsMensuales, chartStatsCat, chartStatsQuincena;
let listaItemsPendientes = [];
let listaSeleccionados = new Set();
let statsLoadVersion = 0;

document.addEventListener('DOMContentLoaded', () => {
    initTheme();
    initTabs();
    loadDashboard();
    checkAutoSync();
    
    // Configurar mes actual en dashboard
    const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const d = new Date();
    document.getElementById('currentDateBadge').textContent = meses[d.getMonth()] + ' ' + d.getFullYear();
    
    // Cargar categorias para el select de inventario
    fetchCategorias().then(cats => {
        const selectFilter = document.getElementById('filterCatInv');
        const selectForm = document.getElementById('prodCategoria');
        cats.forEach(c => {
            selectFilter.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
            selectForm.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
        });
    });

    // Configurar selectores de estadísticas
    const statsMonth = document.getElementById('statsMonthSelect');
    const statsYear = document.getElementById('statsYearSelect');
    meses.forEach((m, i) => {
        const opt = document.createElement('option');
        opt.value = i + 1;
        opt.textContent = m;
        if(i === d.getMonth()) opt.selected = true;
        statsMonth.appendChild(opt);
    });
    for(let y = d.getFullYear() - 2; y <= d.getFullYear() + 2; y++) {
        const opt = document.createElement('option');
        opt.value = y;
        opt.textContent = y;
        if(y === d.getFullYear()) opt.selected = true;
        statsYear.appendChild(opt);
    }

    // Listeners Inventario
    document.getElementById('searchInv').addEventListener('input', debounce(loadInventario, 500));
    document.getElementById('filterCatInv').addEventListener('change', loadInventario);
    
    // Input archivo importar
    const csvInput = document.getElementById('csvFile');
    csvInput.addEventListener('change', function(e) {
        if(this.files.length > 0) {
            document.querySelector('.upload-label span').textContent = this.files[0].name;
            document.getElementById('btnImportar').style.display = 'block';
        }
    });
});

// ==========================================
// TABS NAVIGATION
// ==========================================
function initTabs() {
    const tabs = document.querySelectorAll('.tab-btn');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Quitar activo
            document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            // Poner activo
            if (tab.id !== 'themeToggleBtn') {
                tab.classList.add('active');
                const target = tab.getAttribute('data-target');
                if (target) {
                    document.getElementById(target).classList.add('active');
                    
                    // Cargar datos según tab
                    if(target === 'inventario') loadInventario();
                    if(target === 'lista') loadListaCompras();
                    if(target === 'dashboard') loadDashboard();
                    if(target === 'alertas') loadAlertas();
                    if(target === 'estadisticas') loadEstadisticas();
                }
            }
        });
    });
}

// ==========================================
// FETCH HELPERS
// ==========================================
async function apiGet(action, params = {}) {
    const url = new URL(API_URL, window.location.href);
    url.searchParams.append('action', action);
    for (let k in params) url.searchParams.append(k, params[k]);
    
    const res = await fetch(url);
    return await res.json();
}

async function apiPost(action, body) {
    const isFormData = body instanceof FormData;
    const options = {
        method: 'POST',
        body: isFormData ? body : JSON.stringify(body)
    };
    if(!isFormData) {
        options.headers = { 'Content-Type': 'application/json' };
    }
    
    const url = new URL(API_URL, window.location.href);
    url.searchParams.append('action', action);
    
    const res = await fetch(url, options);
    return await res.json();
}

// ==========================================
// DASHBOARD
// ==========================================
async function loadDashboard() {
    try {
        const res = await apiGet('get_stats_dashboard');
        if(res.success) {
            const d = res.data;
            document.getElementById('statTotalGastado').textContent = '$' + parseFloat(d.total_gastado_mes).toLocaleString();
            document.getElementById('statTotalProductos').textContent = d.total_productos;
            document.getElementById('statPresupuesto').textContent = '$' + parseFloat(d.presupuesto_mes).toLocaleString();
            
            // Gráficas
            updateBarChart(d.gasto_q1, d.gasto_q2);
            loadCategoriasChart();
        }
    } catch(e) { console.error(e); }
}

function updateBarChart(q1, q2) {
    const ctx = document.getElementById('barChartQ').getContext('2d');
    if(chartQ) chartQ.destroy();
    
    chartQ = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Quincena 1 (1-15)', 'Quincena 2 (16-31)'],
            datasets: [{
                label: 'Gastos $',
                data: [q1, q2],
                backgroundColor: ['rgba(16, 185, 129, 0.7)', 'rgba(59, 130, 246, 0.7)'],
                borderColor: ['#10B981', '#3B82F6'],
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#94A3B8' } },
                x: { grid: { display: false }, ticks: { color: '#94A3B8' } }
            }
        }
    });
}

async function loadCategoriasChart() {
    const res = await apiGet('get_stats_por_categoria');
    if(res.success && res.data.length > 0) {
        const ctx = document.getElementById('doughnutChartCat').getContext('2d');
        if(chartCat) chartCat.destroy();
        
        const labels = res.data.map(d => d.nombre);
        const data = res.data.map(d => d.total);
        const colors = res.data.map(d => d.color);
        
        chartCat = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { color: '#F1F5F9' } }
                }
            }
        });
    }
}

// ==========================================
// ESTADÍSTICAS AVANZADAS
// ==========================================
async function loadEstadisticas() {
    const mes = Number(document.getElementById('statsMonthSelect').value);
    const anio = Number(document.getElementById('statsYearSelect').value);
    const version = ++statsLoadVersion;
    const empty = document.getElementById('statsEmpty');
    empty.querySelector('h3').textContent = 'Aún no hay compras en este periodo';
    empty.querySelector('p').textContent = 'Cuando registres una compra, aquí verás su distribución, tendencia y productos principales.';
    empty.hidden = true;
    document.getElementById('statsContent').hidden = true;
    setStatsLoading(true);

    try {
        const res = await apiGet('get_estadisticas_periodo', { mes, anio });
        if (version !== statsLoadVersion) return;
        if (!res.success) throw new Error(res.message || 'No fue posible cargar las estadísticas');

        const data = res.data;
        const tieneCompras = Number(data.resumen.total) > 0 || Number(data.resumen.registros) > 0;
        document.getElementById('statsEmpty').hidden = tieneCompras;
        document.getElementById('statsContent').hidden = !tieneCompras;
        if (!tieneCompras) return;

        renderResumenEstadisticas(data);
        renderChartsEstadisticas(data);
        renderListasEstadisticas(data.productos);
    } catch (error) {
        console.error(error);
        document.getElementById('statsContent').hidden = true;
        empty.hidden = false;
        empty.querySelector('h3').textContent = 'No pudimos cargar las estadísticas';
        empty.querySelector('p').textContent = 'Actualiza la página e inténtalo de nuevo. Si el problema continúa, revisa la conexión con la base de datos.';
    } finally {
        if (version === statsLoadVersion) setStatsLoading(false);
    }
}

function setStatsLoading(loading) {
    document.getElementById('statsLoading').classList.toggle('active', loading);
}

function formatMoney(value) {
    return new Intl.NumberFormat('es-CO', { style: 'currency', currency: 'COP', maximumFractionDigits: 0 }).format(Number(value) || 0);
}

function formatNumber(value) {
    return new Intl.NumberFormat('es-CO').format(Number(value) || 0);
}

function chartTheme() {
    const style = getComputedStyle(document.body);
    return {
        primary: style.getPropertyValue('--primary').trim() || '#10B981',
        secondary: style.getPropertyValue('--secondary').trim() || '#3B82F6',
        purple: style.getPropertyValue('--purple').trim() || '#8B5CF6',
        text: style.getPropertyValue('--text-primary').trim() || '#F1F5F9',
        muted: style.getPropertyValue('--text-secondary').trim() || '#94A3B8',
        grid: style.getPropertyValue('--border-color').trim() || 'rgba(255,255,255,0.1)'
    };
}

function setStatsText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = value;
}

function renderResumenEstadisticas(data) {
    const resumen = data.resumen;
    const presupuesto = data.presupuesto;
    const categoriaPrincipal = data.categorias[0];
    const variacion = resumen.variacion_porcentaje;

    setStatsText('statsTotalGastado', formatMoney(resumen.total));
    setStatsText('statsUnidadesCompradas', formatNumber(resumen.unidades));
    setStatsText('statsRegistros', `${formatNumber(resumen.registros)} registro${Number(resumen.registros) === 1 ? '' : 's'} de compra`);
    setStatsText('statsPromedioCompra', formatMoney(resumen.promedio_registro));
    setStatsText('statsCategoriaPrincipal', categoriaPrincipal ? categoriaPrincipal.nombre : 'Sin datos');
    setStatsText('statsCategoriaPrincipalValor', categoriaPrincipal ? `${formatMoney(categoriaPrincipal.total)} del gasto` : 'Aún no hay gastos');

    const variacionElement = document.getElementById('statsVariacion');
    variacionElement.className = '';
    if (variacion === null || variacion === undefined) {
        variacionElement.textContent = 'Sin comparación anterior';
    } else if (Number(variacion) > 0) {
        variacionElement.textContent = `↑ ${Math.abs(Number(variacion)).toLocaleString('es-CO')}% frente al mes anterior`;
        variacionElement.classList.add('is-up');
    } else if (Number(variacion) < 0) {
        variacionElement.textContent = `↓ ${Math.abs(Number(variacion)).toLocaleString('es-CO')}% frente al mes anterior`;
        variacionElement.classList.add('is-down');
    } else {
        variacionElement.textContent = 'Sin variación frente al mes anterior';
    }

    const presupuestoConfigurado = Number(presupuesto.valor) > 0;
    const porcentaje = Number(presupuesto.porcentaje_usado) || 0;
    const disponible = Number(presupuesto.disponible) || 0;
    const progreso = document.getElementById('statsBudgetProgress');
    const barra = progreso.parentElement;
    progreso.style.width = `${Math.min(Math.max(porcentaje, 0), 100)}%`;
    progreso.classList.toggle('is-over', porcentaje > 100);
    barra.setAttribute('aria-valuenow', String(Math.round(porcentaje)));
    setStatsText('statsBudgetTitle', presupuestoConfigurado ? `Presupuesto: ${formatMoney(presupuesto.valor)}` : 'Sin presupuesto configurado');
    setStatsText('statsBudgetAvailable', presupuestoConfigurado ? `${disponible < 0 ? 'Excedido por ' : 'Disponible: '}${formatMoney(Math.abs(disponible))}` : '—');
    setStatsText('statsBudgetDetail', presupuestoConfigurado ? `${formatMoney(resumen.total)} gastados durante el periodo` : 'Configura un presupuesto en el panel principal para comparar tus gastos.');
    setStatsText('statsBudgetPercent', presupuestoConfigurado ? `${porcentaje.toLocaleString('es-CO')}% usado` : 'Sin meta');

    const fecha = new Date(data.periodo.anio, data.periodo.mes - 1, 1);
    setStatsText('statsTrendPeriod', fecha.toLocaleDateString('es-CO', { month: 'long', year: 'numeric' }));
}

function obtenerSerieMensual(data) {
    const porMes = new Map(data.tendencia.map(item => [item.mes, Number(item.total) || 0]));
    const labels = [];
    const valores = [];
    for (let indice = 5; indice >= 0; indice--) {
        const fecha = new Date(data.periodo.anio, data.periodo.mes - 1 - indice, 1);
        const clave = `${fecha.getFullYear()}-${String(fecha.getMonth() + 1).padStart(2, '0')}`;
        labels.push(fecha.toLocaleDateString('es-CO', { month: 'short' }).replace('.', ''));
        valores.push(porMes.get(clave) || 0);
    }
    return { labels, valores };
}

function renderChartsEstadisticas(data) {
    const theme = chartTheme();
    const monedaTick = value => formatMoney(value).replace('COP', '$').trim();
    const tooltip = { backgroundColor: theme.text, titleColor: theme.muted, bodyColor: theme.primary, padding: 12, displayColors: false, callbacks: { label: context => formatMoney(context.parsed.y ?? context.parsed) } };
    const serie = obtenerSerieMensual(data);

    if (chartStatsMensuales) chartStatsMensuales.destroy();
    chartStatsMensuales = new Chart(document.getElementById('chartGastosMensuales'), {
        type: 'line',
        data: { labels: serie.labels, datasets: [{ data: serie.valores, borderColor: theme.primary, backgroundColor: `${theme.primary}26`, fill: true, tension: 0.38, pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: theme.primary, borderWidth: 3 }] },
        options: { responsive: true, maintainAspectRatio: false, interaction: { intersect: false, mode: 'index' }, plugins: { legend: { display: false }, tooltip }, scales: { y: { beginAtZero: true, border: { display: false }, ticks: { color: theme.muted, callback: monedaTick }, grid: { color: theme.grid } }, x: { border: { display: false }, ticks: { color: theme.muted }, grid: { display: false } } } }
    });

    const categorias = data.categorias || [];
    if (chartStatsCat) chartStatsCat.destroy();
    chartStatsCat = new Chart(document.getElementById('chartCategoriasStats'), {
        type: 'doughnut',
        data: { labels: categorias.map(item => item.nombre), datasets: [{ data: categorias.map(item => Number(item.total) || 0), backgroundColor: categorias.map(item => item.color || theme.muted), borderColor: 'transparent', borderWidth: 0, hoverOffset: 6 }] },
        options: { responsive: true, maintainAspectRatio: false, cutout: '72%', plugins: { legend: { display: false }, tooltip: { ...tooltip, callbacks: { label: context => `${context.label}: ${formatMoney(context.parsed)}` } } } }
    });
    renderLeyendaCategorias(categorias, Number(data.resumen.total) || 0);

    if (chartStatsQuincena) chartStatsQuincena.destroy();
    chartStatsQuincena = new Chart(document.getElementById('chartQuincenalStats'), {
        type: 'bar',
        data: { labels: ['Días 1–15', 'Días 16–fin'], datasets: [{ data: [Number(data.quincenas.primera) || 0, Number(data.quincenas.segunda) || 0], backgroundColor: [theme.secondary, theme.purple], borderRadius: 8, borderSkipped: false, maxBarThickness: 58 }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip }, scales: { y: { beginAtZero: true, border: { display: false }, ticks: { color: theme.muted, callback: monedaTick }, grid: { color: theme.grid } }, x: { border: { display: false }, ticks: { color: theme.muted }, grid: { display: false } } } }
    });
}

function renderLeyendaCategorias(categorias, total) {
    const legend = document.getElementById('statsCategoryLegend');
    legend.innerHTML = categorias.length ? '' : '<li class="stats-list-empty">Sin categorías registradas</li>';
    categorias.slice(0, 5).forEach(categoria => {
        const porcentaje = total > 0 ? Math.round((Number(categoria.total) / total) * 100) : 0;
        legend.innerHTML += `<li><span class="stats-legend-name"><i style="background:${categoria.color || '#64748B'}"></i>${escapeHtml(categoria.nombre)}</span><span><strong>${formatMoney(categoria.total)}</strong><small>${porcentaje}%</small></span></li>`;
    });
}

function renderListasEstadisticas(productos) {
    renderRanking('listTopComprados', productos.mas_comprados, producto => `${formatNumber(producto.unidades)} uds`);
    renderRanking('listTopCostosos', productos.mayor_gasto, producto => formatMoney(producto.total));
}

function renderRanking(id, productos, formatearValor) {
    const lista = document.getElementById(id);
    lista.innerHTML = productos.length ? '' : '<li class="stats-list-empty">Sin compras registradas</li>';
    productos.forEach((producto, indice) => {
        lista.innerHTML += `<li><span class="stats-rank">${indice + 1}</span><span class="stats-product-name">${escapeHtml(producto.nombre)}</span><strong>${formatearValor(producto)}</strong></li>`;
    });
}

// ==========================================
// INVENTARIO
// ==========================================
async function fetchCategorias() {
    const res = await apiGet('get_categorias');
    return res.success ? res.data : [];
}

async function loadInventario() {
    const grid = document.getElementById('productosGrid');
    const search = document.getElementById('searchInv').value;
    const cat = document.getElementById('filterCatInv').value;
    
    grid.innerHTML = '<div class="loading-spinner"></div>';
    
    try {
        const res = await apiGet('get_productos', { search: search, categoria: cat });
        if(res.success) {
            grid.innerHTML = '';
            if(res.data.length === 0) {
                grid.innerHTML = `<div class="empty-state" style="grid-column: 1/-1"><i class="fa fa-cubes"></i><p>No se encontraron productos.</p></div>`;
                return;
            }
            
            res.data.forEach(p => {
                // Duración total = unitaria × cantidad
                const durTotal = parseInt(p.duracion_total) || (parseInt(p.duracion_dias) * parseInt(p.cantidad));
                // Color duracion basado en duración total
                let pct = (p.dias_restantes / durTotal) * 100;
                if(pct > 100) pct = 100; if(pct < 0) pct = 0;
                let colorBar = '#22C55E';
                if(pct < 40) colorBar = '#F59E0B';
                if(pct < 15) colorBar = '#EF4444';
                
                const img = p.foto ? `<img src="${p.foto}" class="product-img">` : `<div class="product-img"><i class="fa fa-image"></i></div>`;
                
                grid.innerHTML += `
                    <div class="product-card" id="card-${p.id}">
                        ${img}
                        <div class="product-content">
                            <span class="cat-badge" style="background: ${p.categoria_color || '#666'}">${p.categoria_nombre || 'Sin categoría'}</span>
                            <h3 class="product-title">${p.nombre}</h3>
                            <div class="product-price">$${parseFloat(p.precio).toLocaleString()} <span style="font-size:12px;color:var(--text-secondary);font-weight:400">/ ${p.unidad}</span></div>
                            
                            <div class="qty-stepper">
                                <button class="qty-btn minus" onclick="updateQuantityAsync(${p.id}, ${parseInt(p.cantidad) - 1})">−</button>
                                <input type="number" class="qty-val" id="qty-${p.id}" value="${p.cantidad}" min="0"
                                       onchange="updateQuantityAsync(${p.id}, parseInt(this.value))">
                                <button class="qty-btn" onclick="updateQuantityAsync(${p.id}, ${parseInt(p.cantidad) + 1})">+</button>
                            </div>

                            <div class="duracion-text"><span>Duración total</span> <span>${p.dias_restantes} / ${durTotal} días</span></div>
                            <div class="duracion-total-tag"><i class="fa fa-info-circle"></i> ${p.duracion_dias}d × ${p.cantidad} uds = ${durTotal}d</div>
                            <div class="duracion-bar-container">
                                <div class="duracion-bar" style="width: ${pct}%; background: ${colorBar}"></div>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn-icon" onclick="addToListaFromInv(${p.id})" title="Agregar a lista de compras"><i class="fa fa-cart-plus"></i></button>
                                <button class="btn-icon" onclick='editProduct(${JSON.stringify(p).replace(/'/g, "&#39;")})' title="Editar"><i class="fa fa-pencil"></i></button>
                                <button class="btn-icon delete" onclick="deleteProduct(${p.id})" title="Eliminar"><i class="fa fa-trash"></i></button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    } catch(e) { console.error(e); }
}

// ==========================================
// MODAL PRODUCTO
// ==========================================
function openProductModal() {
    document.getElementById('productForm').reset();
    document.getElementById('prodId').value = '';
    document.getElementById('modalTitle').textContent = 'Nuevo Producto';
    document.getElementById('productModal').classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

function editProduct(p) {
    document.getElementById('prodId').value = p.id;
    document.getElementById('prodNombre').value = p.nombre;
    document.getElementById('prodCategoria').value = p.id_categoria;
    document.getElementById('prodPrecio').value = p.precio;
    document.getElementById('prodDuracion').value = p.duracion_dias;
    document.getElementById('prodCantidad').value = p.cantidad;
    document.getElementById('modalTitle').textContent = 'Editar Producto';
    document.getElementById('productModal').classList.add('active');
}

async function saveProduct() {
    const form = document.getElementById('productForm');
    if(!form.reportValidity()) return;
    
    const formData = new FormData(form);
    const res = await apiPost('save_producto', formData);
    
    if(res.success) {
        Swal.fire({icon: 'success', title: 'Éxito', text: res.message, background: '#1E293B', color: '#fff'});
        closeModal('productModal');
        loadInventario();
    } else {
        Swal.fire({icon: 'error', title: 'Error', text: res.message, background: '#1E293B', color: '#fff'});
    }
}

async function deleteProduct(id) {
    const result = await Swal.fire({
        title: '¿Estás seguro?',
        text: "Se eliminará este producto del inventario",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#EF4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Sí, eliminar',
        background: '#1E293B', color: '#fff'
    });
    
    if(result.isConfirmed) {
        const res = await apiPost('delete_producto', { id: id });
        if(res.success) loadInventario();
    }
}

async function eliminarDuplicadosInventario() {
    const confirmacion = await Swal.fire({
        title: '¿Quitar productos duplicados?',
        text: 'Se conservará el registro más antiguo y se sumarán sus cantidades. Las compras y pendientes se mantendrán asociados.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar duplicados',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#EF4444',
        background: '#1E293B', color: '#fff'
    });
    if (!confirmacion.isConfirmed) return;

    const res = await apiPost('eliminar_duplicados', {});
    Swal.fire({
        icon: res.success ? 'success' : 'error',
        title: res.success ? 'Inventario revisado' : 'No se pudo completar',
        text: res.message,
        background: '#1E293B', color: '#fff'
    });
    if (res.success) {
        loadInventario();
        loadDashboard();
    }
}

// ==========================================
// LISTA DE COMPRAS
// ==========================================
async function loadListaCompras() {
    const ul = document.getElementById('shoppingList');
    ul.innerHTML = '<div class="loading-spinner"></div>';
    
    try {
        const res = await apiGet('get_lista');
        if(res.success) {
            ul.innerHTML = '';
            listaItemsPendientes = res.data;
            const idsPendientes = new Set(res.data.map(item => String(item.id)));
            listaSeleccionados = new Set([...listaSeleccionados].filter(id => idsPendientes.has(String(id))));
            
            if(res.data.length === 0) {
                ul.innerHTML = `<div class="empty-state"><i class="fa fa-check-circle"></i><p>Tu lista está vacía.</p></div>`;
                actualizarResumenLista();
                return;
            }
            
            res.data.forEach(item => {
                const seleccionado = listaSeleccionados.has(String(item.id));
                const cantidad = Math.max(1, parseInt(item.cantidad) || 1);
                const precioUnitario = parseFloat(item.precio_estimado) || 0;
                const subtotal = precioUnitario * cantidad;
                
                ul.innerHTML += `
                    <li class="list-item ${seleccionado ? 'checked' : ''}" id="list-item-${item.id}">
                        <label class="custom-checkbox" title="Seleccionar para comprar">
                            <input type="checkbox" ${seleccionado ? 'checked' : ''} onchange="toggleSeleccionItem(${item.id}, this.checked)">
                            <i class="fa fa-check"></i>
                        </label>
                        <div class="item-name">
                            ${escapeHtml(item.nombre_producto)}
                        </div>
                        <div class="list-quantity">
                            <label for="list-qty-${item.id}">Cantidad a comprar</label>
                            <div class="qty-stepper">
                                <button class="qty-btn minus" onclick="cambiarCantidadLista(${item.id}, -1)" title="Restar">−</button>
                                <input type="number" class="qty-val" id="list-qty-${item.id}" value="${cantidad}" min="1" step="1" aria-label="Cantidad a comprar de ${escapeHtml(item.nombre_producto)}" onchange="guardarCantidadLista(${item.id}, this.value)">
                                <button class="qty-btn" onclick="cambiarCantidadLista(${item.id}, 1)" title="Sumar">+</button>
                            </div>
                        </div>
                        <div class="list-price-detail">
                            <span>Precio unitario</span>
                            <strong>$${precioUnitario.toLocaleString()}</strong>
                        </div>
                        <div class="list-subtotal">
                            <span>Total</span>
                            <strong>$${subtotal.toLocaleString()}</strong>
                        </div>
                        <button class="btn-icon delete" style="border:none" onclick="deleteItemLista(${item.id})"><i class="fa fa-trash"></i></button>
                    </li>
                `;
            });
            actualizarResumenLista();
        }
    } catch(e) { console.error(e); }
}

function escapeHtml(valor) {
    const contenedor = document.createElement('div');
    contenedor.textContent = valor ?? '';
    return contenedor.innerHTML;
}

function toggleSeleccionItem(id, seleccionado) {
    const clave = String(id);
    if (seleccionado) listaSeleccionados.add(clave);
    else listaSeleccionados.delete(clave);
    document.getElementById(`list-item-${id}`)?.classList.toggle('checked', seleccionado);
    actualizarResumenLista();
}

function toggleSeleccionarTodos() {
    const seleccionarTodo = listaSeleccionados.size !== listaItemsPendientes.length;
    listaSeleccionados = new Set(seleccionarTodo ? listaItemsPendientes.map(item => String(item.id)) : []);
    document.querySelectorAll('#shoppingList input[type="checkbox"]').forEach(input => {
        input.checked = seleccionarTodo;
        input.closest('.list-item')?.classList.toggle('checked', seleccionarTodo);
    });
    actualizarResumenLista();
}

function actualizarResumenLista() {
    const total = listaItemsPendientes.reduce((acumulado, item) => {
        return listaSeleccionados.has(String(item.id))
            ? acumulado + (parseFloat(item.precio_estimado) || 0) * (parseInt(item.cantidad) || 1)
            : acumulado;
    }, 0);
    document.getElementById('listaTotalEstimado').textContent = '$' + total.toLocaleString();

    const todosSeleccionados = listaItemsPendientes.length > 0 && listaSeleccionados.size === listaItemsPendientes.length;
    const btnTodos = document.getElementById('btnSeleccionarTodos');
    const btnComprar = document.getElementById('btnComprarSeleccionados');
    if (btnTodos) btnTodos.innerHTML = `<i class="fa fa-${todosSeleccionados ? 'square-o' : 'check-square-o'}"></i> ${todosSeleccionados ? 'Quitar selección' : 'Seleccionar todos'}`;
    if (btnComprar) btnComprar.disabled = listaSeleccionados.size === 0;
}

function cambiarCantidadLista(id, cambio) {
    const input = document.getElementById(`list-qty-${id}`);
    if (!input) return;
    guardarCantidadLista(id, Math.max(1, (parseInt(input.value) || 1) + cambio));
}

async function guardarCantidadLista(id, cantidad) {
    const cantidadValida = Math.max(1, parseInt(cantidad) || 1);
    const res = await apiPost('update_cantidad_lista', { id, cantidad: cantidadValida });
    if (res.success) loadListaCompras();
    else Swal.fire({ icon: 'error', title: 'Error', text: res.message, background: '#1E293B', color: '#fff' });
}

async function agregarItemLista() {
    const nombre = document.getElementById('nuevoItemNombre').value;
    if(!nombre.trim()) return;
    
    const res = await apiPost('save_item_lista', { nombre_producto: nombre });
    if(res.success) {
        document.getElementById('nuevoItemNombre').value = '';
        loadListaCompras();
    }
}

async function addToListaFromInv(id_producto) {
    const res = await apiPost('save_item_lista', { 
        id_producto: id_producto
    });
    if(res.success) {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', 
            title: 'Agregado a la lista', showConfirmButton: false, timer: 1500,
            background: '#1E293B', color: '#fff'
        });
    }
}

async function deleteItemLista(id) {
    await apiPost('delete_item_lista', { id: id });
    listaSeleccionados.delete(String(id));
    loadListaCompras();
}

async function comprarSeleccionados() {
    const ids = [...listaSeleccionados];
    if (!ids.length) return;
    const total = listaItemsPendientes.reduce((acumulado, item) => listaSeleccionados.has(String(item.id))
        ? acumulado + (parseFloat(item.precio_estimado) || 0) * (parseInt(item.cantidad) || 1)
        : acumulado, 0);
    const confirmacion = await Swal.fire({
        title: '¿Guardar compra?',
        html: `Se registrarán <strong>${ids.length}</strong> producto(s) por <strong>$${total.toLocaleString()}</strong>.<br>Los no seleccionados seguirán pendientes.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Guardar compra',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#10B981',
        background: '#1E293B', color: '#fff'
    });
    if (!confirmacion.isConfirmed) return;

    const res = await apiPost('comprar_items_lista', { ids });
    if (res.success) {
        listaSeleccionados.clear();
        await Promise.all([loadListaCompras(), loadInventario(), loadDashboard()]);
        Swal.fire({ icon: 'success', title: 'Compra guardada', text: res.message, background: '#1E293B', color: '#fff' });
    } else {
        Swal.fire({ icon: 'error', title: 'No se guardó la compra', text: res.message, background: '#1E293B', color: '#fff' });
    }
}

// ==========================================
// IMPORTAR
// ==========================================
async function procesarImportacion() {
    const file = document.getElementById('csvFile').files[0];
    if(!file) return;
    
    const formData = new FormData();
    formData.append('file', file);
    
    document.getElementById('btnImportar').innerHTML = '<i class="fa fa-spinner fa-spin"></i> Importando...';
    document.getElementById('btnImportar').disabled = true;
    
    const res = await apiPost('importar_csv', formData);
    
    document.getElementById('btnImportar').innerHTML = 'Importar Productos';
    document.getElementById('btnImportar').disabled = false;
    
    if(res.success) {
        Swal.fire({icon: 'success', title: 'Importación Completada', text: res.message, background: '#1E293B', color: '#fff'});
        document.getElementById('csvFile').value = '';
        document.querySelector('.upload-label span').textContent = 'Arrastra el archivo o haz clic aquí';
        document.getElementById('btnImportar').style.display = 'none';
    } else {
        Swal.fire({icon: 'error', title: 'Error', text: res.message, background: '#1E293B', color: '#fff'});
    }
}

// Utils
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => { clearTimeout(timeout); func(...args); };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ==========================================
// TEMA Y CONFIGURACION
// ==========================================
function initTheme() {
    const savedTheme = localStorage.getItem('mimercado_theme');
    if (savedTheme === 'light') {
        document.body.classList.add('light-theme');
        document.getElementById('themeToggleBtn').innerHTML = '<i class="fa fa-moon-o"></i>';
    }
    
    // Init push switch
    const pushEnabled = localStorage.getItem('mimercado_push');
    if (pushEnabled === 'true') {
        document.getElementById('pushNotifToggle').checked = true;
        document.getElementById('pushStatusMsg').style.display = 'block';
    }

    // Init sync switches
    if (localStorage.getItem('mimercado_sync_cero') === 'true') {
        document.getElementById('syncCeroToggle').checked = true;
    }
    if (localStorage.getItem('mimercado_sync_tiempo') === 'true') {
        document.getElementById('syncTiempoToggle').checked = true;
    }
}

function toggleTheme() {
    document.body.classList.toggle('light-theme');
    const isLight = document.body.classList.contains('light-theme');
    
    localStorage.setItem('mimercado_theme', isLight ? 'light' : 'dark');
    document.getElementById('themeToggleBtn').innerHTML = isLight ? '<i class="fa fa-moon-o"></i>' : '<i class="fa fa-sun-o"></i>';
}

function togglePushNotifications(checkbox) {
    if (checkbox.checked) {
        if ("Notification" in window) {
            Notification.requestPermission().then(permission => {
                if (permission === "granted") {
                    localStorage.setItem('mimercado_push', 'true');
                    document.getElementById('pushStatusMsg').style.display = 'block';
                    new Notification("MiMercado", { body: "Las alertas push han sido activadas." });
                } else {
                    checkbox.checked = false;
                    Swal.fire({icon: 'warning', title: 'Permiso Denegado', text: 'No se otorgaron permisos para notificaciones.'});
                }
            });
        } else {
            checkbox.checked = false;
            Swal.fire({icon: 'error', title: 'Error', text: 'Tu navegador no soporta notificaciones push.'});
        }
    } else {
        localStorage.setItem('mimercado_push', 'false');
        document.getElementById('pushStatusMsg').style.display = 'none';
    }
}

// ==========================================
// SYNC: POR CANTIDAD (EN CERO)
// ==========================================
function toggleSyncCero(checkbox) {
    localStorage.setItem('mimercado_sync_cero', checkbox.checked ? 'true' : 'false');
    if(checkbox.checked) checkAutoSync();
}

// ==========================================
// SYNC: POR TIEMPO (VENCIDOS)
// ==========================================
function toggleSyncTiempo(checkbox) {
    localStorage.setItem('mimercado_sync_tiempo', checkbox.checked ? 'true' : 'false');
    if(checkbox.checked) checkAutoSync();
}

// ==========================================
// AUTO SYNC (evalúa ambos switches)
// ==========================================
async function checkAutoSync() {
    const syncCero = localStorage.getItem('mimercado_sync_cero') === 'true';
    const syncTiempo = localStorage.getItem('mimercado_sync_tiempo') === 'true';
    if(!syncCero && !syncTiempo) return;

    try {
        const res = await apiGet('get_productos', { estado: 'todos' });
        if(!res.success) return;

        let productsToSync = [];

        res.data.forEach(p => {
            let shouldSync = false;
            // Sync por cantidad: cantidad == 0
            if(syncCero && parseInt(p.cantidad) === 0) shouldSync = true;
            // Sync por tiempo: duración total agotada (dias_restantes <= 0)
            if(syncTiempo && p.dias_restantes <= 0) shouldSync = true;

            if(shouldSync) productsToSync.push(p);
        });

        if(productsToSync.length === 0) return;

        const resLista = await apiGet('get_lista');
        if(!resLista.success) return;
        const enListaIds = resLista.data.map(item => String(item.id_producto));

        for(const p of productsToSync) {
            if(!enListaIds.includes(String(p.id))) {
                const formData = new FormData();
                formData.append('action', 'save_item_lista');
                formData.append('id_producto', p.id);
                formData.append('nombre_producto', p.nombre);
                formData.append('precio_estimado', p.precio);
                formData.append('cantidad', 1);
                formData.append('prioridad', 'alta');
                await fetch(API_URL, { method: 'POST', body: formData });
            }
        }
    } catch(e) { console.error("Error en auto sync", e); }
}

// ==========================================
// ACTUALIZAR CANTIDAD (Asíncrono)
// ==========================================
async function updateQuantityAsync(productId, newQty) {
    if(newQty < 0) newQty = 0;
    
    // Actualizar UI inmediatamente
    const input = document.getElementById('qty-' + productId);
    if(input) input.value = newQty;
    
    try {
        const res = await fetch(API_URL + '?action=update_quantity', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: productId, cantidad: newQty })
        });
        const data = await res.json();
        
        if(data.success) {
            // Si llegó a 0 y la sync por cero está activa, agregar a lista
            if(newQty === 0 && localStorage.getItem('mimercado_sync_cero') === 'true') {
                checkAutoSync();
            }
            // Recargar tarjeta para actualizar la duración total
            loadInventario();
        } else {
            Swal.fire({icon: 'error', title: 'Error', text: data.message || 'No se pudo actualizar la cantidad.'});
        }
    } catch(e) {
        console.error('Error actualizando cantidad:', e);
        Swal.fire({icon: 'error', title: 'Error', text: 'Error de conexión al actualizar la cantidad.'});
    }
}

// ==========================================
// ALERTAS (SEMAFORO)
// ==========================================
async function loadAlertas() {
    const grid = document.getElementById('alertasGrid');
    grid.innerHTML = '<div class="loading-spinner"></div>';
    
    try {
        const res = await apiGet('get_productos', { estado: 'todos' });
        if(res.success) {
            grid.innerHTML = '';
            
            // Filtrar productos que necesitan alerta (vencidos, por vencer o agotados)
            const alertas = res.data.filter(p => p.activo == 0 || p.dias_restantes <= 3);
            
            if(alertas.length === 0) {
                grid.innerHTML = `<div class="empty-state" style="grid-column: 1/-1"><i class="fa fa-check-circle text-emerald"></i><p>Todo está en orden. No hay alertas.</p></div>`;
                return;
            }
            
            alertas.forEach(p => {
                let semaforoClase = 'verde';
                let semaforoLuz = 'v';
                let estadoTexto = 'Buen estado';
                let alertColor = '#22C55E';
                
                if (p.activo == 0 || p.dias_restantes <= 0) {
                    semaforoClase = 'rojo';
                    semaforoLuz = 'r';
                    estadoTexto = p.activo == 0 ? 'Agotado' : 'Vencido';
                    alertColor = '#EF4444';
                } else if (p.dias_restantes <= 3) {
                    semaforoClase = 'amarillo';
                    semaforoLuz = 'a';
                    estadoTexto = `Por vencer en ${p.dias_restantes} días`;
                    alertColor = '#F59E0B';
                }
                
                const img = p.foto ? `<img src="${p.foto}" class="product-img" style="height:120px;">` : `<div class="product-img" style="height:120px;"><i class="fa fa-image"></i></div>`;
                
                grid.innerHTML += `
                    <div class="product-card" style="border-left: 4px solid ${alertColor}">
                        ${img}
                        <div class="product-content">
                            <h3 class="product-title" style="margin-bottom:5px;">${p.nombre}</h3>
                            <div class="semaforo-indicator">
                                <div class="semaforo ${semaforoClase}">
                                    <div class="luz v ${semaforoClase == 'verde' ? 'verde' : ''}"></div>
                                    <div class="luz a ${semaforoClase == 'amarillo' ? 'amarilla' : ''}"></div>
                                    <div class="luz r ${semaforoClase == 'rojo' ? 'roja' : ''}"></div>
                                </div>
                                <span style="color: ${alertColor}">${estadoTexto}</span>
                            </div>
                            <div class="product-actions">
                                <button class="btn btn-secondary btn-block" style="width:100%" onclick="addToListaFromInv(${p.id})">
                                    <i class="fa fa-cart-plus"></i> Comprar
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
    } catch(e) { console.error(e); }
}
