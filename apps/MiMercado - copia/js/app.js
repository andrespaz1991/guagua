// ==========================================
// MiMercado - App Logic
// ==========================================

const API_URL = 'api.php';
let chartQ, chartCat;

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
                // Color duracion
                let pct = (p.dias_restantes / p.duracion_dias) * 100;
                if(pct > 100) pct = 100; if(pct < 0) pct = 0;
                let colorBar = '#22C55E';
                if(pct < 40) colorBar = '#F59E0B';
                if(pct < 15) colorBar = '#EF4444';
                
                const img = p.foto ? `<img src="${p.foto}" class="product-img">` : `<div class="product-img"><i class="fa fa-image"></i></div>`;
                
                grid.innerHTML += `
                    <div class="product-card">
                        ${img}
                        <div class="product-content">
                            <span class="cat-badge" style="background: ${p.categoria_color || '#666'}">${p.categoria_nombre || 'Sin categoría'}</span>
                            <h3 class="product-title">${p.nombre}</h3>
                            <div class="product-price">
                                $${parseFloat(p.precio).toLocaleString()} <span style="font-size:12px;color:var(--text-secondary);font-weight:400">/ ${p.unidad}</span>
                                <span style="float:right; font-size:13px; background:var(--surface); color:var(--text-primary); padding:2px 8px; border-radius:12px;">Disp: ${p.cantidad}</span>
                            </div>
                            
                            <div class="duracion-text"><span>Duración aprox</span> <span>${p.dias_restantes} / ${p.duracion_dias} días</span></div>
                            <div class="duracion-bar-container">
                                <div class="duracion-bar" style="width: ${pct}%; background: ${colorBar}"></div>
                            </div>
                            
                            <div class="product-actions">
                                <button class="btn-icon" onclick="addToListaFromInv(${p.id}, '${p.nombre}', ${p.precio})" title="Agregar a lista de compras"><i class="fa fa-cart-plus"></i></button>
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
            let total = 0;
            
            if(res.data.length === 0) {
                ul.innerHTML = `<div class="empty-state"><i class="fa fa-check-circle"></i><p>Tu lista está vacía.</p></div>`;
                document.getElementById('listaTotalEstimado').textContent = '$0';
                return;
            }
            
            res.data.forEach(item => {
                const isChecked = item.comprado == 1;
                if(!isChecked) total += (parseFloat(item.precio_estimado) * parseInt(item.cantidad));
                
                ul.innerHTML += `
                    <li class="list-item ${isChecked ? 'checked' : ''}">
                        <div class="custom-checkbox" onclick="toggleComprado(${item.id}, ${isChecked ? 0 : 1})">
                            <i class="fa fa-check"></i>
                        </div>
                        <div class="item-name">
                            ${item.nombre_producto} 
                            <span style="font-size:12px;color:var(--text-secondary)">x${item.cantidad}</span>
                        </div>
                        <div class="item-price">$${parseFloat(item.precio_estimado).toLocaleString()}</div>
                        <button class="btn-icon delete" style="border:none" onclick="deleteItemLista(${item.id})"><i class="fa fa-trash"></i></button>
                    </li>
                `;
            });
            
            document.getElementById('listaTotalEstimado').textContent = '$' + total.toLocaleString();
        }
    } catch(e) { console.error(e); }
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

async function addToListaFromInv(id_producto, nombre, precio) {
    const res = await apiPost('save_item_lista', { 
        id_producto: id_producto,
        nombre_producto: nombre,
        precio_estimado: precio
    });
    if(res.success) {
        Swal.fire({
            toast: true, position: 'top-end', icon: 'success', 
            title: 'Agregado a la lista', showConfirmButton: false, timer: 1500,
            background: '#1E293B', color: '#fff'
        });
    }
}

async function toggleComprado(id, estado) {
    await apiPost('toggle_comprado', { id: id, comprado: estado });
    loadListaCompras();
}

async function deleteItemLista(id) {
    await apiPost('delete_item_lista', { id: id });
    loadListaCompras();
}

async function limpiarListaComprada() {
    const res = await apiPost('limpiar_lista');
    if(res.success) loadListaCompras();
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

    // Init sync switch
    const syncEnabled = localStorage.getItem('mimercado_sync');
    if (syncEnabled === 'true') {
        document.getElementById('syncVencidosToggle').checked = true;
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

function toggleSyncVencidos(checkbox) {
    localStorage.setItem('mimercado_sync', checkbox.checked ? 'true' : 'false');
    if(checkbox.checked) checkAutoSync();
}

async function checkAutoSync() {
    if(localStorage.getItem('mimercado_sync') === 'true') {
        try {
            const res = await apiGet('get_productos', { estado: 'todos' });
            if(res.success) {
                const vencidos = res.data.filter(p => p.activo == 0 || p.dias_restantes <= 0);
                if(vencidos.length > 0) {
                    const resLista = await apiGet('get_lista');
                    if(resLista.success) {
                        const enListaIds = resLista.data.map(item => item.id_producto);
                        for(const p of vencidos) {
                            if(!enListaIds.includes(p.id)) {
                                // Add to list silently
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
                    }
                }
            }
        } catch(e) { console.error("Error en auto sync", e); }
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
                                <button class="btn btn-secondary btn-block" style="width:100%" onclick="addToListaFromInv(${p.id}, '${p.nombre}', ${p.precio})">
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
