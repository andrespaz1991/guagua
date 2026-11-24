<?php
/**
 * Crear curso
 *
 * Crea una asignación académica para el año, materia y grado seleccionados.
 * El formulario está disponible únicamente para docentes y administradores.
 */

require_once __DIR__ . '/../comun/autoload.php';
require_once __DIR__ . '/../comun/conexion.php';
require_once __DIR__ . '/../comun/config.php';
require_once __DIR__ . '/../comun/funciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['modulo'] = 'cursos';

function crear_curso_e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function crear_curso_obtener_opciones(mysqli $mysqli, string $sql, string $clave): array
{
    $resultado = $mysqli->query($sql);
    $opciones = [];

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $opciones[(int) $fila[$clave]] = $fila;
        }
    }

    return $opciones;
}

$rol = $_SESSION['rol'] ?? 'invitado';
$puedeCrear = in_array($rol, ['admin', 'docente'], true);
$idInstitucion = max(0, (int) ($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0));
$idDocenteSesion = trim((string) ($_SESSION['id_usuario'] ?? $_SESSION['identificacion_usu'] ?? ''));
$errores = [];

if (!isset($_SESSION['crear_curso_csrf'])) {
    $_SESSION['crear_curso_csrf'] = bin2hex(random_bytes(32));
}

$materias = crear_curso_obtener_opciones(
    $mysqli,
    'SELECT id_materia, nombre_materia, descripcion FROM materia_oficial ORDER BY nombre_materia ASC',
    'id_materia'
);
$categorias = crear_curso_obtener_opciones(
    $mysqli,
    'SELECT id_categoria_curso, nombre_categoria_curso FROM categoria_curso ORDER BY id_categoria_curso ASC',
    'id_categoria_curso'
);
$anosLectivos = crear_curso_obtener_opciones(
    $mysqli,
    'SELECT id_ano_lectivo, nombre_ano_lectivo, estado FROM ano_lectivo ORDER BY nombre_ano_lectivo DESC',
    'id_ano_lectivo'
);
$iconos = crear_curso_obtener_opciones(
    $mysqli,
    'SELECT id_iconos, icono, imagen_icono FROM iconos ORDER BY icono ASC',
    'id_iconos'
);

$docentes = [];
if ($rol === 'admin') {
    $docentes = crear_curso_obtener_opciones(
        $mysqli,
        "SELECT id_usuario, nombre, apellido FROM usuario WHERE rol LIKE '%docente%' OR rol LIKE '%admin%' ORDER BY nombre ASC, apellido ASC",
        'id_usuario'
    );
}

$anoPredeterminado = 0;
foreach ($anosLectivos as $idAno => $ano) {
    if (strcasecmp((string) $ano['estado'], 'Activo') === 0) {
        $anoPredeterminado = $idAno;
        break;
    }
}

$valores = [
    'materia' => 0,
    'categoria' => 0,
    'ano_lectivo' => $anoPredeterminado,
    'docente' => $idDocenteSesion,
    'descripcion' => '',
    'visible' => 'si',
    'icono' => isset($iconos[5]) ? 5 : (int) (array_key_first($iconos) ?? 0),
];

if ($puedeCrear && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores = [
        'materia' => max(0, (int) ($_POST['materia'] ?? 0)),
        'categoria' => max(0, (int) ($_POST['categoria'] ?? 0)),
        'ano_lectivo' => max(0, (int) ($_POST['ano_lectivo'] ?? 0)),
        'docente' => $rol === 'admin' ? trim((string) ($_POST['docente'] ?? '')) : $idDocenteSesion,
        'descripcion' => trim((string) ($_POST['descripcion'] ?? '')),
        'visible' => ($_POST['visible'] ?? 'si') === 'no' ? 'no' : 'si',
        'icono' => max(0, (int) ($_POST['icono'] ?? 0)),
    ];

    if (!hash_equals($_SESSION['crear_curso_csrf'], (string) ($_POST['csrf_token'] ?? ''))) {
        $errores[] = 'La solicitud expiró. Actualice la página e inténtelo nuevamente.';
    }
    if (!isset($materias[$valores['materia']])) {
        $errores[] = 'Seleccione una materia válida.';
    }
    if (!isset($categorias[$valores['categoria']])) {
        $errores[] = 'Seleccione un grado o categoría válida.';
    }
    if (!isset($anosLectivos[$valores['ano_lectivo']])) {
        $errores[] = 'Seleccione un año lectivo válido.';
    }
    if (!isset($iconos[$valores['icono']])) {
        $errores[] = 'Seleccione un icono válido.';
    }
    if ($valores['docente'] === '' || ($rol === 'admin' && !isset($docentes[(int) $valores['docente']]))) {
        $errores[] = 'Seleccione el docente responsable del curso.';
    }
    if (function_exists('mb_strlen') ? mb_strlen($valores['descripcion'], 'UTF-8') > 240 : strlen($valores['descripcion']) > 240) {
        $errores[] = 'La descripción no puede superar los 240 caracteres.';
    }

    if (!$errores) {
        $consultaExistente = $mysqli->prepare(
            'SELECT id_asignacion
             FROM asignacion
             WHERE institucion_educativa = ?
               AND id_asignatura = ?
               AND id_categoria_curso = ?
               AND ano_lectivo = ?
               AND id_docente = ?
             LIMIT 1'
        );

        if (!$consultaExistente) {
            $errores[] = 'No fue posible validar las asignaciones existentes.';
        } else {
            $consultaExistente->bind_param(
                'iiiis',
                $idInstitucion,
                $valores['materia'],
                $valores['categoria'],
                $valores['ano_lectivo'],
                $valores['docente']
            );
            $consultaExistente->execute();
            $cursoExistente = $consultaExistente->get_result()->fetch_assoc();
            $consultaExistente->close();

            if ($cursoExistente) {
                $idExistente = (int) $cursoExistente['id_asignacion'];
                $errores[] = 'Ya existe este curso para el docente y año seleccionados. <a href="curso.php?asignacion=' . $idExistente . '">Abrir curso existente</a>.';
            }
        }
    }

    if (!$errores) {
        $insertar = $mysqli->prepare(
            "INSERT INTO asignacion
                (institucion_educativa, id_curso, id_asignatura, id_docente, ano_lectivo, descripcion, id_categoria_curso, visible, icono_asignacion)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$insertar) {
            $errores[] = 'No fue posible preparar la creación del curso.';
        } else {
            // id_curso mantiene la convención histórica: coincide con la materia oficial.
            $idCurso = $valores['materia'];
            $insertar->bind_param(
                'iiisisisi',
                $idInstitucion,
                $idCurso,
                $valores['materia'],
                $valores['docente'],
                $valores['ano_lectivo'],
                $valores['descripcion'],
                $valores['categoria'],
                $valores['visible'],
                $valores['icono']
            );

            if ($insertar->execute()) {
                $idAsignacion = (int) $mysqli->insert_id;
                $insertar->close();
                $_SESSION['crear_curso_csrf'] = bin2hex(random_bytes(32));
                header('Location: curso.php?asignacion=' . $idAsignacion . '&creado=1');
                exit;
            }

            $insertar->close();
            $errores[] = 'No fue posible crear el curso. Inténtelo nuevamente.';
        }
    }
}

ob_start();
?>
<style>
    .crear-curso-page { max-width: 1120px; margin: 30px auto 55px; padding: 0 20px; }
    .crear-curso-hero { position: relative; overflow: hidden; padding: 31px 36px; border-radius: 18px; color: #fff; background: linear-gradient(118deg, #123f31, #20714f); box-shadow: 0 15px 35px rgba(20, 69, 51, .17); }
    .crear-curso-hero::after { position: absolute; top: -74px; right: -35px; width: 265px; height: 265px; border: 42px solid rgba(255, 255, 255, .08); border-radius: 50%; content: ''; }
    .crear-curso-hero__content { position: relative; z-index: 1; max-width: 680px; }
    .crear-curso-hero__eyebrow { margin: 0 0 8px !important; color: #bcebd0; font-size: 11px !important; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; }
    .crear-curso-hero h1 { margin: 0 !important; font-size: 31px !important; font-weight: 700; line-height: 1.18 !important; }
    .crear-curso-hero__description { margin: 10px 0 0 !important; font-size: 15px !important; line-height: 1.5 !important; opacity: .9; }
    .crear-curso-layout { display: grid; grid-template-columns: minmax(0, 1fr) 290px; gap: 22px; margin-top: 22px; }
    .crear-curso-form, .crear-curso-aside { border: 1px solid #dfe9e3; border-radius: 16px; background: #fff; box-shadow: 0 7px 24px rgba(22, 61, 47, .06); }
    .crear-curso-form { padding: 28px; }
    .crear-curso-form__heading { display: flex; align-items: center; gap: 11px; margin-bottom: 25px; }
    .crear-curso-form__heading span { display: inline-flex; width: 35px; height: 35px; align-items: center; justify-content: center; border-radius: 10px; color: #176443; background: #e9f6ee; font-weight: 700; }
    .crear-curso-form__heading h2 { margin: 0 !important; color: #173f32; font-size: 20px !important; font-weight: 700; line-height: 1.2 !important; }
    .crear-curso-form__heading p { margin: 3px 0 0 !important; color: #718078; font-size: 12px !important; line-height: 1.35 !important; }
    .crear-curso-fields { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 19px; }
    .crear-curso-field { min-width: 0; }
    .crear-curso-field--full { grid-column: 1 / -1; }
    .crear-curso-field label { display: block; margin: 0 0 7px; color: #314b3f; font-size: 12px !important; font-weight: 700; line-height: 1.25 !important; }
    .crear-curso-field .form-control { height: 42px; border: 1px solid #cfded6; border-radius: 8px; color: #20372d; box-shadow: none; font-size: 14px; }
    .crear-curso-field textarea.form-control { height: 103px; padding-top: 10px; resize: vertical; }
    .crear-curso-field .form-control:focus { border-color: #3a9a6d; box-shadow: 0 0 0 3px rgba(58, 154, 109, .13); }
    .crear-curso-icon-preview { display: flex; align-items: center; gap: 11px; margin-top: 10px; padding: 9px 10px; border: 1px solid #dfece4; border-radius: 9px; background: #f8fbf9; }
    .crear-curso-icon-preview__image { display: flex; width: 38px; height: 38px; flex: 0 0 38px; align-items: center; justify-content: center; border-radius: 11px; background: #e7f5ec; }
    .crear-curso-icon-preview__image img { max-width: 28px; max-height: 28px; object-fit: contain; }
    .crear-curso-icon-preview strong { display: block; color: #345647; font-size: 12px; line-height: 1.25; }
    .crear-curso-icon-preview span { display: block; margin-top: 2px; color: #75867c; font-size: 11px; line-height: 1.25; }
    .crear-curso-help { margin: 6px 0 0 !important; color: #809087; font-size: 11px !important; line-height: 1.35 !important; }
    .crear-curso-options { display: flex; flex-wrap: wrap; gap: 11px; padding: 12px; border: 1px solid #e2ebe6; border-radius: 9px; background: #f8fbf9; }
    .crear-curso-options label { display: inline-flex; align-items: center; gap: 6px; margin: 0; cursor: pointer; font-weight: 600; }
    .crear-curso-options input { margin: 0; accent-color: #237b55; }
    .crear-curso-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 27px; padding-top: 20px; border-top: 1px solid #e6eee9; }
    .crear-curso-actions .btn { border-radius: 8px; font-size: 13px; font-weight: 700; }
    .crear-curso-actions .btn-primary { padding: 11px 18px; border-color: #1d6f4c; background: #1d6f4c; }
    .crear-curso-actions .btn-primary:hover, .crear-curso-actions .btn-primary:focus { border-color: #145438; background: #145438; }
    .crear-curso-aside { align-self: start; overflow: hidden; }
    .crear-curso-aside__title { padding: 19px 20px 14px; border-bottom: 1px solid #e4ece7; }
    .crear-curso-aside__title h2 { margin: 0 !important; color: #173f32; font-size: 16px !important; font-weight: 700; line-height: 1.2 !important; }
    .crear-curso-aside__title p { margin: 5px 0 0 !important; color: #718078; font-size: 12px !important; line-height: 1.45 !important; }
    .crear-curso-steps { padding: 6px 20px 16px; margin: 0; list-style: none; }
    .crear-curso-steps li { position: relative; min-height: 46px; padding: 15px 0 0 35px; color: #52665b; font-size: 12px; line-height: 1.4; }
    .crear-curso-steps li::before { position: absolute; top: 13px; left: 0; display: flex; width: 24px; height: 24px; align-items: center; justify-content: center; border-radius: 50%; color: #26784f; background: #e7f5ec; content: '✓'; font-weight: 700; }
    .crear-curso-steps strong { display: block; margin-bottom: 2px; color: #274b3b; font-size: 12px; }
    .crear-curso-alert { margin: 0 0 20px; border: 1px solid #ebc2c2; border-radius: 10px; color: #963b3b; background: #fff8f8; }
    .crear-curso-alert ul { margin: 0; padding: 12px 16px 12px 32px; }
    .crear-curso-alert a { color: #7c2424; font-weight: 700; text-decoration: underline; }
    .crear-curso-permission { max-width: 600px; margin: 55px auto; padding: 35px; border: 1px solid #e2e9e5; border-radius: 16px; background: #fff; text-align: center; box-shadow: 0 8px 25px rgba(22, 61, 47, .06); }
    .crear-curso-permission__icon { display: inline-flex; width: 56px; height: 56px; align-items: center; justify-content: center; border-radius: 18px; color: #8b6222; background: #fff5df; font-size: 25px; }
    .crear-curso-permission h1 { margin: 16px 0 8px !important; color: #173f32; font-size: 22px !important; font-weight: 700; }
    .crear-curso-permission p { margin: 0 0 20px !important; color: #66776d; }
    @media (max-width: 860px) { .crear-curso-layout { grid-template-columns: 1fr; } .crear-curso-aside { display: none; } }
    @media (max-width: 600px) { .crear-curso-page { margin-top: 16px; padding: 0 12px; } .crear-curso-hero { padding: 25px 21px; border-radius: 14px; } .crear-curso-hero h1 { font-size: 27px !important; } .crear-curso-form { padding: 20px 16px; } .crear-curso-fields { grid-template-columns: 1fr; gap: 16px; } .crear-curso-actions { align-items: stretch; flex-direction: column-reverse; } .crear-curso-actions .btn { width: 100%; } }
</style>

<?php if (!$puedeCrear): ?>
    <main class="crear-curso-page">
        <section class="crear-curso-permission">
            <span class="crear-curso-permission__icon" aria-hidden="true">⌁</span>
            <h1>Acceso restringido</h1>
            <p>Solo los docentes y administradores pueden crear cursos académicos.</p>
            <a href="mis_cursos.php" class="btn btn-default">Volver a mis cursos</a>
        </section>
    </main>
<?php else: ?>
    <main class="crear-curso-page">
        <header class="crear-curso-hero">
            <div class="crear-curso-hero__content">
                <p class="crear-curso-hero__eyebrow">Gestión académica</p>
                <h1>Crear un nuevo curso</h1>
                <p class="crear-curso-hero__description">Configure la asignatura, grado y año lectivo. Al guardar, podrá ingresar directamente al espacio del curso.</p>
            </div>
        </header>

        <div class="crear-curso-layout">
            <section class="crear-curso-form" aria-labelledby="crear-curso-titulo-formulario">
                <div class="crear-curso-form__heading">
                    <span aria-hidden="true">1</span>
                    <div>
                        <h2 id="crear-curso-titulo-formulario">Información del curso</h2>
                        <p>Los campos marcados son necesarios para crear la asignación.</p>
                    </div>
                </div>

                <?php if ($errores): ?>
                    <div class="crear-curso-alert" role="alert">
                        <ul>
                            <?php foreach ($errores as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="post" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo crear_curso_e($_SESSION['crear_curso_csrf']); ?>">
                    <div class="crear-curso-fields">
                        <div class="crear-curso-field">
                            <label for="materia">Materia</label>
                            <select class="form-control" id="materia" name="materia" required>
                                <option value="">Seleccione una materia</option>
                                <?php foreach ($materias as $idMateria => $materia): ?>
                                    <option value="<?php echo $idMateria; ?>" <?php echo $valores['materia'] === $idMateria ? 'selected' : ''; ?>><?php echo crear_curso_e($materia['nombre_materia']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="crear-curso-field">
                            <label for="categoria">Grado o categoría</label>
                            <select class="form-control" id="categoria" name="categoria" required>
                                <option value="">Seleccione un grado</option>
                                <?php foreach ($categorias as $idCategoria => $categoria): ?>
                                    <option value="<?php echo $idCategoria; ?>" <?php echo $valores['categoria'] === $idCategoria ? 'selected' : ''; ?>><?php echo crear_curso_e($categoria['nombre_categoria_curso']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="crear-curso-field">
                            <label for="ano_lectivo">Año lectivo</label>
                            <select class="form-control" id="ano_lectivo" name="ano_lectivo" required>
                                <option value="" <?= $valores['ano_lectivo'] === 0 ? 'selected' : '' ?>>Seleccione un año lectivo</option>
                                <?php foreach ($anosLectivos as $idAno => $ano): ?>
                                    <option value="<?php echo $idAno; ?>" <?php echo $valores['ano_lectivo'] === $idAno ? 'selected' : ''; ?>><?php echo crear_curso_e($ano['nombre_ano_lectivo']); ?><?php echo strcasecmp((string) $ano['estado'], 'Activo') === 0 ? ' — Activo' : ''; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php if ($rol === 'admin'): ?>
                            <div class="crear-curso-field">
                                <label for="docente">Docente responsable</label>
                                <select class="form-control" id="docente" name="docente" required>
                                    <option value="">Seleccione un docente</option>
                                    <?php foreach ($docentes as $idDocente => $docente): ?>
                                        <option value="<?php echo $idDocente; ?>" <?php echo (string) $valores['docente'] === (string) $idDocente ? 'selected' : ''; ?>><?php echo crear_curso_e(trim($docente['nombre'] . ' ' . $docente['apellido'])); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="docente" value="<?php echo crear_curso_e($idDocenteSesion); ?>">
                            <div class="crear-curso-field">
                                <label>Docente responsable</label>
                                <div class="form-control" aria-readonly="true">Curso asignado a su cuenta</div>
                            </div>
                        <?php endif; ?>
                        <div class="crear-curso-field">
                            <label for="icono">Icono del curso</label>
                            <select class="form-control" id="icono" name="icono" required>
                                <?php foreach ($iconos as $idIcono => $icono): ?>
                                    <option value="<?php echo $idIcono; ?>" data-imagen="<?php echo crear_curso_e(SGA_COMUN_URL . '/img/png/' . $icono['imagen_icono']); ?>" <?php echo $valores['icono'] === $idIcono ? 'selected' : ''; ?>><?php echo crear_curso_e($icono['icono']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="crear-curso-icon-preview" aria-live="polite">
                                <div class="crear-curso-icon-preview__image"><img id="vista-previa-icono" src="" alt="Vista previa del icono seleccionado"></div>
                                <div><strong id="vista-previa-icono-nombre">Icono seleccionado</strong><span>Así se verá en la tarjeta del curso.</span></div>
                            </div>
                            <p class="crear-curso-help">El icono puede modificarse después desde la configuración del curso.</p>
                        </div>
                        <div class="crear-curso-field">
                            <label>Disponibilidad</label>
                            <div class="crear-curso-options">
                                <label><input type="radio" name="visible" value="si" <?php echo $valores['visible'] === 'si' ? 'checked' : ''; ?>> Visible para estudiantes</label>
                                <label><input type="radio" name="visible" value="no" <?php echo $valores['visible'] === 'no' ? 'checked' : ''; ?>> Guardar como oculto</label>
                            </div>
                        </div>
                        <div class="crear-curso-field crear-curso-field--full">
                            <label for="descripcion">Descripción <span class="text-muted">(opcional)</span></label>
                            <textarea class="form-control" id="descripcion" name="descripcion" maxlength="240" placeholder="Ej. Espacio para actividades, recursos y seguimiento de Matemáticas de grado sexto."><?php echo crear_curso_e($valores['descripcion']); ?></textarea>
                            <p class="crear-curso-help">Esta información será visible como referencia dentro de la tarjeta del curso.</p>
                        </div>
                    </div>
                    <div class="crear-curso-actions">
                        <a href="mis_cursos.php" class="btn btn-default">Cancelar</a>
                        <button class="btn btn-primary" type="submit">Crear curso y continuar <span aria-hidden="true">→</span></button>
                    </div>
                </form>
            </section>

            <aside class="crear-curso-aside" aria-label="Guía de creación">
                <div class="crear-curso-aside__title">
                    <h2>Antes de guardar</h2>
                    <p>Una configuración correcta ayuda a encontrar y organizar cada curso.</p>
                </div>
                <ol class="crear-curso-steps">
                    <li><strong>Seleccione la materia</strong>Use el nombre oficial de la asignatura.</li>
                    <li><strong>Asigne el grado</strong>El curso quedará agrupado en esa categoría.</li>
                    <li><strong>Confirme el año</strong>Se recomienda utilizar el año lectivo activo.</li>
                    <li><strong>Revise la visibilidad</strong>Puede mantenerlo oculto mientras lo configura.</li>
                </ol>
            </aside>
        </div>
    </main>
<?php endif; ?>

<script>
    (function() {
        var selector = document.getElementById('icono');
        var preview = document.getElementById('vista-previa-icono');
        var nombre = document.getElementById('vista-previa-icono-nombre');

        if (!selector || !preview || !nombre) {
            return;
        }

        function actualizarVistaPrevia() {
            var opcion = selector.options[selector.selectedIndex];
            preview.style.display = 'block';
            preview.src = opcion.getAttribute('data-imagen') || '';
            preview.alt = 'Vista previa: ' + opcion.text;
            nombre.textContent = opcion.text;
        }

        preview.addEventListener('error', function() {
            preview.style.display = 'none';
        });
        selector.addEventListener('change', actualizarVistaPrevia);
        actualizarVistaPrevia();
    })();
</script>

<?php
$contenido = ob_get_clean();
include __DIR__ . '/../comun/plantilla.php';
