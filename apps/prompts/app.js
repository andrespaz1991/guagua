'use strict';
const $ = id => document.getElementById(id);
let library = {categories: [], prompts: []}, version = 0, filter = 'all', editing = null, categoryEditing = null, busy = true;
const uid = () => crypto.randomUUID ? crypto.randomUUID() : Date.now().toString(36) + Math.random().toString(36).slice(2);
const escapeHtml = text => String(text).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const message = text => { $('status').textContent = text; };
async function request(options) {
    const response = await fetch('api.php', options);
    const data = await response.json();
    if (!response.ok) throw new Error(data.message || 'No se pudo completar la operación.');
    return data;
}
async function save(next) {
    if (busy) return false;
    busy = true; document.body.classList.add('saving');
    try {
        const result = await request({method:'POST', headers:{'Content-Type':'application/json','X-CSRF-Token':window.promptsCsrf}, body:JSON.stringify({version,library:next})});
        library = next; version = result.version; render(); message('Cambios guardados.'); return true;
    } catch (error) { message(error.message); return false; }
    finally { busy = false; document.body.classList.remove('saving'); }
}
function render() {
    const count = id => library.prompts.filter(p => id === 'all' || (id === 'favorites' ? p.favorite : id === 'uncategorized' ? !p.category : p.category === id)).length;
    const nav = (id, name, color) => `<div class="nav-row"><button data-filter="${id}" class="${filter === id ? 'active' : ''}"><span class="dot" style="background:${color}"></span>${escapeHtml(name)} <small>${count(id)}</small></button>${!['all','favorites','uncategorized'].includes(id) ? `<button data-category-edit="${id}" aria-label="Editar ${escapeHtml(name)}">⋯</button>` : ''}</div>`;
    $('categories').innerHTML = nav('all','Todos los prompts','#64748b') + nav('favorites','Favoritos','#f59e0b') + nav('uncategorized','Sin categoría','#94a3b8') + '<p class="eyebrow">CATEGORÍAS</p>' + library.categories.map(c => nav(c.id,c.name,c.color)).join('');
    const search = $('search').value.toLocaleLowerCase();
    const prompts = library.prompts.filter(p => (filter === 'all' || (filter === 'favorites' ? p.favorite : filter === 'uncategorized' ? !p.category : p.category === filter)) && `${p.title} ${p.content} ${p.tags}`.toLocaleLowerCase().includes(search));
    prompts.sort((a,b) => $('sort').value === 'title' ? a.title.localeCompare(b.title,'es') : b.updated.localeCompare(a.updated));
    $('cards').innerHTML = prompts.length ? prompts.map(p => {
        const category = library.categories.find(c => c.id === p.category);
        return `<article class="card" style="--accent:${p.color}"><div class="card-top"><span class="badge" style="--badge:${category?.color || '#64748b'}">${escapeHtml(category?.name || 'Sin categoría')}</span><button data-action="favorite" data-id="${p.id}" aria-label="${p.favorite ? 'Quitar de' : 'Añadir a'} favoritos" aria-pressed="${p.favorite}">${p.favorite ? '★' : '☆'}</button></div><h2>${escapeHtml(p.title)}</h2><p class="preview">${escapeHtml(p.content)}</p><div class="tags">${p.tags.split(',').map(t => t.trim()).filter(Boolean).map(t => `<span>#${escapeHtml(t)}</span>`).join('')}</div><footer><button class="copy" data-action="copy" data-id="${p.id}">⧉ Copiar</button><button data-action="duplicate" data-id="${p.id}" aria-label="Duplicar ${escapeHtml(p.title)}">Duplicar</button><button data-action="edit" data-id="${p.id}">Editar</button><button data-action="delete" data-id="${p.id}" aria-label="Eliminar ${escapeHtml(p.title)}">Eliminar</button></footer></article>`;
    }).join('') : '<div class="empty"><span>✦</span><h2>Un lugar para tus mejores prompts</h2><p>Crea un prompt o cambia los filtros para encontrar tus instrucciones.</p><button class="primary" id="empty-new">＋ Crear prompt</button></div>';
    $('empty-new')?.addEventListener('click', () => openPrompt());
}
function openPrompt(prompt = null) {
    if (busy) return;
    editing = prompt?.id || null;
    $('editor-heading').textContent = prompt ? 'Editar prompt' : 'Nuevo prompt';
    $('category').innerHTML = '<option value="">Sin categoría</option>' + library.categories.map(c => `<option value="${c.id}">${escapeHtml(c.name)}</option>`).join('');
    for (const field of ['title','content','tags']) $(field).value = prompt?.[field] || '';
    $('category').value = prompt?.category || (library.categories.some(c => c.id === filter) ? filter : '');
    $('color').value = prompt?.color || library.categories.find(c => c.id === $('category').value)?.color || '#6366f1';
    $('favorite').checked = prompt?.favorite || false;
    $('editor').showModal(); $('title').focus();
}
$('new').onclick = () => openPrompt();
document.querySelectorAll('[data-close]').forEach(button => button.onclick = () => $(button.dataset.close).close());
$('search').oninput = render; $('sort').onchange = render;
$('category').onchange = () => { $('color').value = library.categories.find(c => c.id === $('category').value)?.color || '#6366f1'; };
$('categories').onclick = event => {
    const button = event.target.closest('button'); if (!button) return;
    if (button.dataset.filter) { filter = button.dataset.filter; render(); }
    else if (button.dataset.categoryEdit) openCategory(library.categories.find(c => c.id === button.dataset.categoryEdit));
};
$('cards').onclick = async event => {
    const button = event.target.closest('[data-action]'); if (!button || busy) return;
    const prompt = library.prompts.find(p => p.id === button.dataset.id); if (!prompt) return;
    if (button.dataset.action === 'edit') openPrompt(prompt);
    if (button.dataset.action === 'copy') {
        try { await navigator.clipboard.writeText(prompt.content); message('Prompt copiado al portapapeles.'); }
        catch { openPrompt(prompt); $('content').focus(); $('content').select(); message('Usa Ctrl+C para copiar el texto seleccionado.'); }
    }
    if (button.dataset.action === 'duplicate') {
        const suffix = ' (copia)';
        const duplicate = {...prompt, id:uid(), title:Array.from(prompt.title).slice(0,180 - suffix.length).join('') + suffix, updated:new Date().toISOString()};
        if (await save({...library,prompts:[duplicate,...library.prompts]})) message('Prompt duplicado. Puedes editar la nueva tarjeta.');
    }
    if (button.dataset.action === 'favorite') await save({...library,prompts:library.prompts.map(p => p.id === prompt.id ? {...p,favorite:!p.favorite} : p)});
    if (button.dataset.action === 'delete' && confirm(`¿Eliminar «${prompt.title}»?`)) await save({...library,prompts:library.prompts.filter(p => p.id !== prompt.id)});
};
$('prompt-form').onsubmit = async event => {
    event.preventDefault();
    const prompt = {id:editing || uid(), title:$('title').value.trim(), content:$('content').value.trim(), tags:$('tags').value.trim(), category:$('category').value, color:$('color').value, favorite:$('favorite').checked, updated:new Date().toISOString()};
    if (!prompt.title || !prompt.content) { message('Escribe un título y contenido.'); return; }
    if (await save({...library,prompts:editing ? library.prompts.map(p => p.id === editing ? prompt : p) : [prompt,...library.prompts]})) $('editor').close();
};
function openCategory(category = null) {
    if (busy) return;
    categoryEditing = category?.id || null;
    $('category-name').value = category?.name || ''; $('category-color').value = category?.color || '#6366f1';
    $('category-delete').hidden = !category;
    $('category-editor').showModal(); $('category-name').focus();
}
$('category-new').onclick = () => openCategory();
$('category-form').onsubmit = async event => {
    event.preventDefault(); const name = $('category-name').value.trim(); if (!name) return;
    const category = {id:categoryEditing || uid(), name, color:$('category-color').value};
    if (await save({...library,categories:categoryEditing ? library.categories.map(c => c.id === categoryEditing ? category : c) : [...library.categories,category]})) $('category-editor').close();
};
$('category-delete').onclick = async () => {
    if (!categoryEditing || !confirm('¿Eliminar esta categoría? Los prompts se conservarán sin categoría.')) return;
    const next = {...library,categories:library.categories.filter(c => c.id !== categoryEditing),prompts:library.prompts.map(p => p.category === categoryEditing ? {...p,category:''} : p)};
    if (await save(next)) { if (filter === categoryEditing) { filter = 'all'; render(); } $('category-editor').close(); }
};
$('export').onclick = () => {
    const url = URL.createObjectURL(new Blob([JSON.stringify(library,null,2)],{type:'application/json'}));
    const link = document.createElement('a'); link.href = url; link.download = 'mis-prompts.json'; link.click(); setTimeout(() => URL.revokeObjectURL(url),1000);
};
request().then(data => {library = data.library; version = data.version; busy = false; render(); message(`${library.prompts.length} prompts en tu biblioteca.`);}).catch(error => message(error.message));
