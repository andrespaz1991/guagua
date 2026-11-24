# Archivos sin referencias estáticas

Se revisaron 545 archivos propios PHP/JS/HTML y herramientas de consola, y 54 entradas de menú. Se excluyeron librerías de terceros, datos de usuarios, pruebas y salidas.

Estos candidatos no tienen referencias por nombre en el código examinado ni una entrada directa en el menú. Esto **no demuestra que estén sin uso**: faltan registros históricos de acceso, cron externo, favoritos y llamadas dinámicas. No se eliminó ningún archivo. Las clases cargadas por autoload se conservan.

| Archivo candidato | Líneas | Observación |
|---|---:|---|
| `index3.php` | 512 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `usuario/importar.php` | 54 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `tarjeta.php` | 4 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `usuario/registrarse.php` | 6 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `usuario/Recuperar_contra.php` | 3 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `miplaneador/reporte_planeador.php` | 274 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `usuario/acudiente_estudiante.php` | 492 | SQL y entrada HTTP en misma línea (revisar flujo): 1; SELECT * (revisar volumen): 1 |
| `mensajes/bandeja_salida.php` | 10 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `red/subir_scorm.php` | 33 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `red/readfile2.php` | 3 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `red/iconos_red.php` | 66 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `asistencia/informe_asistencia.php` | 443 | SELECT * (revisar volumen): 1 |
| `asistencia/crud_asistencias.php` | 654 | SELECT * (revisar volumen): 1 |
| `cursos/inscribir_estudiante.php` | 41 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `cursos/funcionesbd.php` | 42 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/unir.php` | 349 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/recursos.php` | 4 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `seguimiento/mysql.class.php` | 70 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `seguimiento/actualizar_proyecto.php` | 5 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `seguimiento/actualizar_datos.php` | 57 | SELECT * (revisar volumen): 1 |
| `Planeador/admin_malla.php` | 602 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/consulta_ia.php` | 69 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/contenido.php` | 4 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/rubrica/importar_exposiciones_emprendimiento.php` | 85 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/rubrica/estimar_anteriores.php` | 58 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/duplicar_plan.php` | 87 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/dba.php` | 141 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/evidencias/setup/uninstall.php` | 66 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/evidencias/report.php` | 78 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/eventos/eventos.php` | 4 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `Planeador/objetivos.php` | 4 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `apps/dba/api/save_json.php` | 28 | Sin referencia detectada; verificar acceso directo y tareas externas. |
| `comun/img/figuras/icon-mascotas.php` | 19 | Sin referencia detectada; verificar acceso directo y tareas externas. |

Inventario completo: `output/inventario-codigo-guagua.csv`. Referencias detectadas pueden estar en comentarios o archivos igualmente huérfanos; no equivalen a uso confirmado.
