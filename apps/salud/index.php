<?php
require_once __DIR__ . '/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
if (empty($_SESSION['id_usuario'])) { header('Location: ../../usuario/login.php'); exit; }
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi actividad · Guagua</title>
<style>
*{box-sizing:border-box}body{margin:0;background:#f2f6f5;color:#203b35;font:16px system-ui,sans-serif}main{max-width:1000px;margin:auto;padding:24px}a{color:#146c53}h1{margin-bottom:6px}section{background:white;border:1px solid #dce6e2;border-radius:14px;padding:22px;margin:20px 0}button,.button{background:#176c53;color:white;border:0;border-radius:7px;padding:10px 14px;cursor:pointer;display:inline-block;font:inherit;text-decoration:none}button:disabled{opacity:.5;cursor:wait}button.secondary{background:#e6eeeb;color:#203b35}input{padding:9px;max-width:100%;font:inherit;border:1px solid #aebfb8;border-radius:6px}#endpoint{width:100%}label{display:block;margin:12px 0}pre{background:#f2f6f5;padding:15px;white-space:pre-wrap;overflow-wrap:anywhere}code{overflow-wrap:anywhere}li{margin-bottom:12px;line-height:1.6}.muted{color:#5c7169}.scroll{overflow-x:auto}table{border-collapse:collapse;width:100%;white-space:nowrap}td,th{text-align:left;padding:12px;border-bottom:1px solid #e1e9e5}#status{min-height:24px}details summary{cursor:pointer;font-weight:600}#key{display:block;user-select:all;margin:12px 0}header p{margin-top:0}#filters{display:flex;gap:12px;align-items:end;flex-wrap:wrap}#filters label{margin:0}button{margin:4px 0}small{display:block}
</style></head><body><main>
<a href="../../index.php">← Inicio de Guagua</a>
<header><h1>Mi actividad</h1><p class="muted">Tu historial de Salud del iPhone, guardado en tu cuenta de Guagua.</p></header>
<p id="status" role="status" aria-live="polite">Cargando historial…</p>
<section><h2>Conectar con Google</h2>
<p>Consulta actividad, pulso, peso, grasa corporal y sueño disponibles en tu cuenta. La disponibilidad depende de las fuentes sincronizadas con Google Health.</p>
<?php $healthEndpoint = 'google.php'; require __DIR__ . '/../../comun/google_health_controls.php'; ?>
<p class="muted">Conecta una vez para recordar tu cuenta. Guagua guarda la autorización cifrada y renueva el acceso al abrir la página. Puedes retirarla con Desconectar Google. Los resultados de Google se guardan separados de los envíos del iPhone.</p>
</section>
<section><h2>Historial diario</h2>
<form id="filters"><label>Fuente<select id="source"><option value="google">Google Health</option><option value="iphone">iPhone</option></select></label><label>Desde<input type="date" id="from" required></label><label>Hasta<input type="date" id="to" required></label><button>Consultar</button></form>
<p id="totals" class="muted"></p>
<div id="overview" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin:20px 0"></div>
<style>.metric-tile{background:#f2f7f4;border:1px solid #dce6e2;border-radius:10px;padding:14px}.metric-tile strong{display:block;font-size:23px;margin:6px 0}.metric-tile small{color:#5c7169}select{padding:9px;border:1px solid #aebfb8;border-radius:6px;font:inherit}</style>
<div class="scroll"><table><thead><tr id="history-head"><th>Fecha</th></tr></thead><tbody id="history"></tbody></table></div>
<p class="muted">Un campo vacío significa que esa medida no se ha enviado. Los días sin registros no se cuentan como cero.</p></section>
<section><h2>Conectar el iPhone por Wi-Fi</h2>
<p>El computador debe estar encendido, con Apache funcionando, y el iPhone debe estar en la misma red. Abre Guagua en Safari usando la IP del computador; <code>localhost</code> en el iPhone apunta al teléfono.</p>
<label>Dirección de recepción para el atajo<input id="endpoint" type="url" spellcheck="false"></label>
<small class="muted">Si ves localhost, sustitúyelo por la IP Wi-Fi del computador, conservando la ruta y el puerto. La IP puede cambiar al reconectar la red.</small>
<p id="paired" class="muted"></p>
<button id="pair" type="button">Generar clave de envío</button>
<button id="revoke" type="button" class="secondary">Revocar clave</button>
<div id="credentials" hidden><p>Copia esta clave en el atajo. Solo se muestra al generarla; una clave nueva invalida la anterior.</p><code id="key"></code></div>
<p class="muted">La clave permite enviar actividad a tu cuenta; no permite leer el historial. Úsala únicamente en tu red de confianza.</p>
<details><summary>Crear el atajo «Enviar salud a Guagua»</summary>
<ol>
<li>Abre <strong>Atajos</strong> en el iPhone y crea uno llamado <strong>Enviar salud a Guagua</strong>.</li>
<li>Añade <strong>Fecha actual</strong> y ajústala al inicio del día. Guarda esta fecha como <strong>Inicio</strong>. Suma 1 día y guarda el resultado como <strong>Fin</strong>.</li>
<li>Añade <strong>Buscar muestras de salud</strong>: tipo <strong>Pasos</strong>, fecha de inicio entre <strong>Inicio</strong> y <strong>Fin</strong>, sin límite de resultados. Si permite filtrar la fuente, selecciona <strong>Mi Fitness</strong>. Autoriza a Atajos a leer Salud.</li>
<li>Obtén el <strong>Valor</strong> de las muestras y usa <strong>Calcular estadísticas → Suma</strong>. Guarda el resultado como <strong>Pasos</strong>. Si no hay muestras, omite el envío de ese día.</li>
<li>Usa <strong>Dar formato a fecha</strong> sobre <strong>Inicio</strong>, formato personalizado <code>yyyy-MM-dd</code>. Evita formatos regionales como 19/10/2026.</li>
<li>Añade <strong>Obtener contenido de URL</strong> con la dirección de recepción mostrada arriba. Método <strong>POST</strong>. En las cabeceras agrega <code>Authorization</code> con el valor <code>Bearer TU_CLAVE</code> (un espacio después de Bearer).</li>
<li>En el cuerpo selecciona <strong>JSON</strong>. Agrega <code>date</code> como texto con la fecha formateada y <code>steps</code> como <strong>número</strong> con la suma Pasos. No envíes el número como texto.</li>
<li>Añade <strong>Mostrar resultado</strong> con la respuesta de la URL. Ejecuta el atajo: una respuesta con <code>"ok":true</code> confirma que Guagua guardó el día. Después pulsa <strong>Consultar</strong> aquí o recarga el inicio.</li>
</ol>
<p><strong>Evitar pasos duplicados:</strong> no sumes muestras de Mi Fitness y del iPhone si corresponden a la misma caminata. Usa una sola fuente consistente. Comprueba la primera suma frente a Salud; si Atajos no permite seleccionar fuente, revisaremos el filtro antes de cargar el historial completo.</p>
<h3>Enviar días anteriores</h3>
<p>Cuando funcione el primer día, reemplaza Fecha actual por una fecha anterior al inicio del día. Para un lote, repite el bloque por cada día, avanzando Inicio y Fin un día cada vez y enviando una petición por día. Puedes reenviar días: Guagua sustituye los totales recibidos de esa fecha en lugar de sumarlos.</p>
<h3>Distancia y calorías</h3>
<p>Repite la búsqueda y suma para <strong>Distancia caminando y corriendo</strong> y <strong>Energía activa</strong> de la misma fuente y día. Convierte a <strong>kilómetros</strong> y <strong>kilocalorías</strong>, y agrega los números <code>distance_km</code> y <code>active_kcal</code> al JSON. Omite las medidas que no estén disponibles.</p>
<pre>{"date":"AAAA-MM-DD","steps":1234,"distance_km":0.85,"active_kcal":90}</pre>
<p>No hace falta automatizarlo: puedes ejecutar el atajo cuando llegues a tu Wi-Fi y luego abrir Guagua. Abrir la web no extrae datos de Salud por sí solo.</p>
</details></section>
</main><script>window.saludCsrf = <?= json_encode(guagua_csrf_token(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;</script><script src="app.js"></script><script src="../../comun/js/google_health.js"></script></body></html>
