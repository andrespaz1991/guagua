
document.addEventListener('DOMContentLoaded', function() {
    var RESOURCES_PER_PAGE = 8;
    var searchInput = document.getElementById('recurso-search-input');
    var noRecursosGlobalMessage = document.getElementById('no-recursos-global');
    var canManageRed = document.querySelector('.red-main-container').dataset.canManage === 'true';
    var paginationSystems = new Map();

    function getFilteredCards(grid) {
        return Array.prototype.slice.call(grid.querySelectorAll('.recurso-card')).filter(function(card) {
            return card.dataset.searchMatch !== '0';
        });
    }

    function setupPagination(materiaWrapper) {
        var currentPage = 1;
        var grid = materiaWrapper.querySelector('.recursos-grid');
        var controls = materiaWrapper.querySelector('.pagination-controls') || document.createElement('div');

        if (!controls.classList.contains('pagination-controls')) {
            controls.className = 'pagination-controls';
            materiaWrapper.appendChild(controls);
        }

        function showPage(page) {
            var visibleResources = getFilteredCards(grid);
            var totalPages = Math.max(1, Math.ceil(visibleResources.length / RESOURCES_PER_PAGE));
            currentPage = Math.min(Math.max(page, 1), totalPages);
            var startIndex = (currentPage - 1) * RESOURCES_PER_PAGE;
            var endIndex = startIndex + RESOURCES_PER_PAGE;

            Array.prototype.forEach.call(grid.querySelectorAll('.recurso-card'), function(resource) {
                resource.style.display = 'none';
            });

            visibleResources.forEach(function(resource, index) {
                resource.style.display = (index >= startIndex && index < endIndex) ? 'flex' : 'none';
            });

            updateControls(visibleResources.length, totalPages);
        }

        function updateControls(totalResources, totalPages) {
            controls.innerHTML = '';
            if (totalResources <= RESOURCES_PER_PAGE) {
                return;
            }

            var prevButton = document.createElement('button');
            prevButton.textContent = 'Anterior';
            prevButton.className = 'pagination-button';
            prevButton.disabled = currentPage === 1;
            prevButton.addEventListener('click', function() {
                showPage(currentPage - 1);
            });
            controls.appendChild(prevButton);

            for (var i = 1; i <= totalPages; i++) {
                var pageButton = document.createElement('button');
                pageButton.textContent = i;
                pageButton.className = 'pagination-button';
                if (i === currentPage) {
                    pageButton.classList.add('active');
                }
                pageButton.addEventListener('click', (function(pageNumber) {
                    return function() {
                        showPage(pageNumber);
                    };
                })(i));
                controls.appendChild(pageButton);
            }

            var nextButton = document.createElement('button');
            nextButton.textContent = 'Siguiente';
            nextButton.className = 'pagination-button';
            nextButton.disabled = currentPage === totalPages;
            nextButton.addEventListener('click', function() {
                showPage(currentPage + 1);
            });
            controls.appendChild(nextButton);
        }

        showPage(1);
        return {
            show: showPage
        };
    }

    document.querySelectorAll('.materia-wrapper').forEach(function(materiaWrapper) {
        materiaWrapper.querySelectorAll('.recurso-card').forEach(function(card) {
            card.dataset.searchMatch = '1';
        });
        paginationSystems.set(materiaWrapper, setupPagination(materiaWrapper));
    });

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var searchTerm = this.value.trim().toLowerCase();
            var totalVisibleResources = 0;

            document.querySelectorAll('.materia-wrapper').forEach(function(materiaWrapper) {
                var visibleInSection = 0;

                materiaWrapper.querySelectorAll('.recurso-card').forEach(function(resource) {
                    var title = (resource.dataset.tituloRed || '').toLowerCase();
                    var isMatch = title.indexOf(searchTerm) !== -1;
                    resource.dataset.searchMatch = isMatch ? '1' : '0';
                    if (isMatch) {
                        visibleInSection++;
                        totalVisibleResources++;
                    }
                });

                materiaWrapper.style.display = visibleInSection > 0 ? 'block' : 'none';
                var system = paginationSystems.get(materiaWrapper);
                if (system) {
                    system.show(1);
                }
            });

            if (noRecursosGlobalMessage) {
                noRecursosGlobalMessage.style.display = totalVisibleResources === 0 ? 'block' : 'none';
            }
        });
    }

    if (typeof $ !== 'undefined' && typeof $.contextMenu === 'function') {
        $.contextMenu({
            selector: '.context-menu-red',
            build: function($trigger) {
                var idRed = $trigger.data('id-red');
                var tituloRed = $trigger.data('titulo-red');
                var items = {
                    info: { name: tituloRed, icon: 'info', disabled: true },
                    sep1: '---------',
                    descargar: { name: 'Descargar', icon: 'download' }
                };

                if (canManageRed) {
                    items.modificar = { name: 'Modificar', icon: 'edit' };
                    items.eliminar = { name: 'Eliminar', icon: 'delete' };
                }

                items.sep2 = '---------';
                items.salir = { name: 'Salir', icon: 'quit' };

                return {
                    callback: function(key) {
                        if (key === 'descargar') {
                            window.location.href = '../comun/funciones.php?ruta_red=' + encodeURIComponent(idRed);
                        } else if (key === 'modificar' && canManageRed) {
                            window.location.href = 'nuevo_red.php?id_red=' + encodeURIComponent(idRed);
                        } else if (key === 'eliminar' && canManageRed && confirm('Esta seguro que desea eliminar "' + tituloRed + '"?')) {
                            window.location.href = '../comun/funciones.php?elred=' + encodeURIComponent(idRed);
                        }
                    },
                    items: items
                };
            }
        });
    }

    document.querySelectorAll('.materia-header').forEach(function(header) {
        header.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.click();
            }
        });
        header.addEventListener('click', function() {
            var targetSelector = this.getAttribute('data-toggle-target');
            var gridElement = document.querySelector(targetSelector);
            var paginationElement = this.parentElement.querySelector('.pagination-controls');

            if (gridElement) {
                var isCollapsed = gridElement.classList.toggle('grid-hidden');
                this.classList.toggle('collapsed', isCollapsed);
                this.setAttribute('aria-expanded', String(!isCollapsed));
                if (paginationElement) {
                    paginationElement.style.display = isCollapsed ? 'none' : 'flex';
                }
            }
        });
    });

});
