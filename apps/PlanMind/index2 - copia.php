<?php
/**
 * Variante de PlanMind que completa los referentes curriculares desde las
 * tablas usadas por apps/dba, sin recargar el formulario.
 */

function planmind2_send_json(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function planmind2_grado_numero(string $grado): string
{
    $normalizado = strtolower(trim($grado));
    $normalizado = strtr($normalizado, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u']);
    $equivalencias = [
        'preescolar' => '0', 'primero' => '1', 'segundo' => '2', 'tercero' => '3',
        'cuarto' => '4', 'quinto' => '5', 'sexto' => '6', 'septimo' => '7',
        'octavo' => '8', 'noveno' => '9', 'decimo' => '10', 'undecimo' => '11',
    ];

    if (isset($equivalencias[$normalizado])) {
        return $equivalencias[$normalizado];
    }
    if (preg_match('/\d+/', $normalizado, $coincidencia)) {
        return $coincidencia[0];
    }
    return $grado;
}

function planmind2_referentes_curriculares(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        planmind2_send_json(['ok' => false, 'message' => 'Método no permitido.'], 405);
    }

    $periodo = max(0, (int)($_GET['periodo'] ?? 0));
    $materia = max(0, (int)($_GET['materia'] ?? 0));
    $grado = trim((string)($_GET['grado'] ?? ''));
    if ($periodo <= 0 || $materia <= 0 || $grado === '') {
        planmind2_send_json(['ok' => false, 'message' => 'Grado, materia y periodo son obligatorios.'], 422);
    }

    try {
        require_once __DIR__ . '/../../comun/config.php';
        $mysqli = new mysqli(SERVIDORBD, USUARIOBD, CLAVEBD, BASEDEDATOS);
        if ($mysqli->connect_errno) {
            throw new RuntimeException('No fue posible conectar con la base de datos.');
        }
        $mysqli->set_charset('utf8mb4');

        // La pantalla apps/dba asocia estándar -> DBA -> eje temático.
        // Acepta tanto el nombre del grado (Sexto) como su identificador (6).
        $gradoNumero = planmind2_grado_numero($grado);
        $sql = "SELECT d.id_dba, d.nombre_dba, et.id_eje_tematico, et.nombre_eje_tematico
                FROM estandar e
                INNER JOIN dba d ON d.id_estandar = e.id_estandar
                LEFT JOIN eje_tematico et ON et.id_dba = d.id_dba
                WHERE e.id_periodo = ?
                  AND e.id_materia_oficial = ?
                  AND (
                    CAST(e.grado AS CHAR) = ?
                    OR CAST(e.grado AS CHAR) = ?
                    OR EXISTS (
                        SELECT 1
                        FROM grado g
                        WHERE g.id_grado = e.grado
                          AND (g.nombre = ? OR g.nombre = ?)
                    )
                  )
                ORDER BY d.id_dba ASC, et.id_eje_tematico ASC";
        $statement = $mysqli->prepare($sql);
        if (!$statement) {
            throw new RuntimeException('No fue posible consultar los referentes curriculares.');
        }
        $statement->bind_param('iissss', $periodo, $materia, $grado, $gradoNumero, $grado, $gradoNumero);
        $statement->execute();
        $result = $statement->get_result();

        $dbas = [];
        $ejes = [];
        $dbasVistos = [];
        $ejesVistos = [];
        while ($fila = $result->fetch_assoc()) {
            $dba = trim((string)($fila['nombre_dba'] ?? ''));
            if ($dba !== '' && !isset($dbasVistos[$dba])) {
                $dbasVistos[$dba] = true;
                $dbas[] = $dba;
            }

            $eje = trim((string)($fila['nombre_eje_tematico'] ?? ''));
            if ($eje !== '' && !isset($ejesVistos[$eje])) {
                $ejesVistos[$eje] = true;
                $ejes[] = $eje;
            }
        }
        $statement->close();
        $mysqli->close();

        planmind2_send_json([
            'ok' => true,
            'dba' => implode("\n", $dbas),
            'ejes_tematicos' => implode("\n", $ejes),
            'encontrados' => ['dbas' => count($dbas), 'ejes_tematicos' => count($ejes)],
        ]);
    } catch (Throwable $error) {
        planmind2_send_json(['ok' => false, 'message' => $error->getMessage()], 500);
    }
}

if (($_GET['action'] ?? '') === 'referentes') {
    planmind2_referentes_curriculares();
}

/*
 * Mantiene index2.php alineado con la pantalla principal: se reutiliza el
 * formulario original y se inyecta únicamente la mejora curricular.
 */
ob_start();
require __DIR__ . '/index.php';
$contenido = ob_get_clean();
$contenido = str_replace('/\\/index\\.php$/i', '/\\/index2\\.php$/i', $contenido);

$scriptReferentes = <<<'HTML'
<script>
(() => {
    const dbaInput = document.getElementById('dba');
    const ejesInput = document.getElementById('ejes_tematicos');
    const gradoInput = document.getElementById('grado');
    const materiaInput = document.getElementById('materia');
    const periodoInput = document.getElementById('periodo');
    if (!dbaInput || !ejesInput || !gradoInput || !materiaInput || !periodoInput) return;

    const estado = document.createElement('p');
    estado.className = 'text-[11px] text-slate-500 mt-1';
    estado.setAttribute('aria-live', 'polite');
    ejesInput.parentElement.appendChild(estado);

    let controlador = null;
    let consecutivo = 0;

    function notificarCambio(campo) {
        campo.dispatchEvent(new Event('input', { bubbles: true }));
        campo.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function asignarReferentes(dba, ejes) {
        dbaInput.value = dba;
        ejesInput.value = ejes;
        notificarCambio(dbaInput);
        notificarCambio(ejesInput);
    }

    async function cargarReferentes() {
        const grado = gradoInput.value.trim();
        const materia = materiaInput.value.trim();
        const periodo = periodoInput.value.trim();
        const solicitud = ++consecutivo;

        if (!grado || !materia || !periodo) {
            if (controlador) controlador.abort();
            asignarReferentes('', '');
            estado.textContent = 'Seleccione grado, materia y periodo para cargar los referentes.';
            return;
        }

        if (controlador) controlador.abort();
        controlador = new AbortController();
        dbaInput.setAttribute('aria-busy', 'true');
        ejesInput.setAttribute('aria-busy', 'true');
        estado.textContent = 'Consultando DBA y ejes temáticos…';

        const url = new URL(window.location.href);
        url.search = '';
        url.searchParams.set('action', 'referentes');
        url.searchParams.set('grado', grado);
        url.searchParams.set('materia', materia);
        url.searchParams.set('periodo', periodo);

        try {
            const respuesta = await fetch(url, { signal: controlador.signal, cache: 'no-store' });
            const datos = await respuesta.json();
            if (solicitud !== consecutivo) return;
            if (!respuesta.ok || !datos.ok) throw new Error(datos.message || 'No se pudieron cargar los referentes.');

            asignarReferentes(datos.dba || '', datos.ejes_tematicos || '');
            estado.textContent = datos.encontrados.dbas
                ? `Referentes cargados: ${datos.encontrados.dbas} DBA y ${datos.encontrados.ejes_tematicos} eje(s) temático(s).`
                : 'No hay DBA ni ejes temáticos configurados para esta combinación.';
        } catch (error) {
            if (error.name === 'AbortError') return;
            if (solicitud !== consecutivo) return;
            asignarReferentes('', '');
            estado.textContent = `No fue posible cargar los referentes: ${error.message}`;
            console.error('Error cargando DBA y ejes temáticos:', error);
        } finally {
            if (solicitud === consecutivo) {
                dbaInput.removeAttribute('aria-busy');
                ejesInput.removeAttribute('aria-busy');
            }
        }
    }

    [gradoInput, materiaInput, periodoInput].forEach(campo => {
        campo.addEventListener('change', cargarReferentes);
    });
    cargarReferentes();
})();
</script>
HTML;

$contenido = str_replace('</body>', $scriptReferentes . "\n</body>", $contenido);
echo $contenido;
