document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('settings-search');
    const normalize = value => value.toLocaleLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
    if (search) {
        const filter = () => {
            const query = normalize(search.value.trim());
            let total = 0;
            document.querySelectorAll('.settings-group').forEach(group => {
                let matches = 0;
                group.querySelectorAll('.settings-card').forEach(card => {
                    card.hidden = !normalize(card.textContent + ' ' + group.querySelector('h2').textContent).includes(query);
                    if (!card.hidden) matches++;
                });
                group.hidden = matches === 0;
                total += matches;
            });
            document.getElementById('settings-empty').hidden = total > 0;
            document.getElementById('settings-count').textContent = `${total} opciones disponibles`;
        };
        search.addEventListener('input', filter);
        filter();
    }
    const restore = document.getElementById('settings-restore');
    if (restore) restore.addEventListener('click', () => {
        const status = document.getElementById('settings-status');
        try {
            localStorage.removeItem('sgaMenuOrder');
            localStorage.removeItem('sgaMenuHidden');
            status.textContent = 'Iconos y orden restaurados. Actualizando el menú…';
            window.location.reload();
        } catch (error) {
            status.textContent = 'El navegador no permite restablecer las preferencias guardadas.';
        }
    });
});
