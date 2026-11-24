(function($) {
        'use strict';

        var $contenedor = $('#span_buscar_mis_cursos');
        var temporizadorBusqueda;
        var solicitudBusqueda;
        var secuenciaBusqueda = 0;
        var $pagina = $('#mis-cursos-page');
        var $feedback = $('#mc-feedback');
        window.misCursosAniosInactivos = JSON.parse($pagina.attr('data-inactive-years') || '[]');

        function inicializarMenusContextuales() {
            if (!$.contextMenu) {
                return;
            }

            $contenedor.find('[contextmenu]').each(function() {
                var $curso = $(this);
                var menuId = $curso.attr('contextmenu');

                if (!$curso.data('contextMenu')) {
                    $.contextMenu({
                        selector: '.' + menuId,
                        items: $.contextMenu.fromMenu($('#' + menuId))
                    });
                    $curso.data('contextMenu', true);
                }
            });
        }

        function actualizarAniosNoVigentes() {
            if (!$('#mc-search').val()) {
                (window.misCursosAniosInactivos || []).forEach(function(ano) {
                    $('#id_' + ano.id_ano_lectivo).hide();
                });
            }
            $contenedor.find('[aria-controls]').each(function() {
                $(this).attr('aria-expanded', $('#' + $(this).attr('aria-controls')).is(':visible') ? 'true' : 'false');
            });
        }

        function inicializarControlVisibilidad() {
            $contenedor.off('click.misCursos', '.control-visibilidad-btn');
            $contenedor.on('click.misCursos', '.control-visibilidad-btn', function(event) {
                event.preventDefault();
                event.stopPropagation();

                var $boton = $(this);
                $feedback.empty();
                var idAsignacion = $boton.data('id');
                var nuevoEstado = $boton.data('visible') === 'si' ? 'no' : 'si';
                $boton.prop('disabled', true);

                $.ajax({
                    url: 'actualizar_visibilidad.php',
                    type: 'POST',
                    data: JSON.stringify({ id_asignacion: idAsignacion, visible: nuevoEstado }),
                    headers: { 'X-CSRF-Token': $pagina.attr('data-csrf') },
                    contentType: 'application/json; charset=utf-8',
                    dataType: 'json'
                }).done(function(respuesta) {
                    if (!respuesta.success) {
                        $feedback.text(respuesta.message || 'No se pudo actualizar el curso.');
                        return;
                    }

                    var oculto = nuevoEstado === 'no';
                    $('#curso-' + idAsignacion).toggleClass('oculto', oculto).find('.curso-card__status').text(oculto ? 'Oculto' : 'Disponible');
                    $boton.text(oculto ? 'Mostrar' : 'Ocultar').data('visible', nuevoEstado);
                }).fail(function() {
                    $feedback.text('No fue posible actualizar la visibilidad del curso. Inténtelo de nuevo.');
                }).always(function() {
                    $boton.prop('disabled', false);
                });
            });
        }

        function actualizarListado(termino) {
            var campo = $('#mc-field').val() || 'todos';
            var secuencia = ++secuenciaBusqueda;
            if (solicitudBusqueda) {
                solicitudBusqueda.abort();
            }

            $feedback.text('Buscando cursos…');
            $contenedor.addClass('is-loading').attr('aria-busy', 'true');
            solicitudBusqueda = $.ajax({
                url: '?buscar_mis_cursos=1',
                type: 'POST',
                data: { datos: termino, campo: campo }
            }).done(function(html) {
                if (secuencia !== secuenciaBusqueda) return;
                $feedback.empty();
                if ($.contextMenu) $contenedor.find('[contextmenu]').each(function() { $.contextMenu('destroy', '.' + $(this).attr('contextmenu')); });
                $contenedor.html(html);
                actualizarAniosNoVigentes();
                inicializarMenusContextuales();
                if (typeof cargar_drag_and_drop_asignacion === 'function') {
                    cargar_drag_and_drop_asignacion();
                }
            }).fail(function(xhr, estado) {
                if (estado !== 'abort' && secuencia === secuenciaBusqueda) {
                    $feedback.text('No se pudo actualizar el listado. Los cursos anteriores siguen visibles. Inténtelo nuevamente.');
                }
            }).always(function() {
                if (secuencia === secuenciaBusqueda) {
                    $contenedor.removeClass('is-loading').attr('aria-busy', 'false');
                    solicitudBusqueda = null;
                }
            });
        }

        window.buscar_mis_cursos = function(termino) {
            clearTimeout(temporizadorBusqueda);
            termino = typeof termino === 'string' ? termino : ($('#mc-search').val() || '');
            temporizadorBusqueda = setTimeout(function() {
                actualizarListado(termino);
            }, 220);
        };

        $(function() {
            actualizarAniosNoVigentes();
            $('#mc-search-form').on('submit', function(event) { event.preventDefault(); window.buscar_mis_cursos(); });
            $('#mc-search').on('input', function() { window.buscar_mis_cursos(this.value); });
            $('#mc-field').on('change', function() { window.buscar_mis_cursos(); });
            $('#mc-reset').on('click', function() { $('#mc-search').val('').trigger('input').focus(); });
            $('.mc-views button').on('click', function() {
                $pagina.toggleClass('mc-list', $(this).data('view') === 'list');
                $('.mc-views button').attr('aria-pressed', 'false');
                $(this).attr('aria-pressed', 'true');
            });
            $contenedor.on('click', '.curso-year__toggle, .curso-category__toggle', function() {
                var $target = $('#' + $(this).attr('aria-controls'));
                var abrir = $(this).attr('aria-expanded') !== 'true';
                $target.toggle(abrir);
                $(this).attr('aria-expanded', abrir ? 'true' : 'false');
            });
            inicializarControlVisibilidad();
            inicializarMenusContextuales();
            if (typeof cargar_drag_and_drop_asignacion === 'function') cargar_drag_and_drop_asignacion();
        });
    })(jQuery);
