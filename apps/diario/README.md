Diario integra la aplicación local ../wsp con las transcripciones de Whisper.

Configuración privada: tmp/whatsapp-diario/diario-settings.json.
Consolidado y borrador persistentes: tmp/whatsapp-diario/consolidado.json y ultimo-diario.json.
La carpeta tmp está bloqueada por Apache. No se modifica la plantilla original.

Formatos reconocidos: TSV/CSV de Whisper, TXT, SRT, VTT y JSON con text o segments.
Se elige una extensión para evitar duplicar la misma transcripción en varios formatos.
Las fechas de los mensajes audio_... se convierten de UTC a America/Bogota.
Si el relato empieza declarando una fecha («El viernes 25 de septiembre»),
esa fecha prevalece sobre la del envío del mensaje. El año se toma del archivo
cuando no se dicta. Una fecha sin año conocido se marca para confirmar.
El rango de fechas de la pantalla se aplica solo cuando se marca la opción correspondiente.

El prompt base toma la estructura de Diario Agosto.pdf: fecha, título de jornada,
relato docente en primera persona y reflexión pedagógica. Las transcripciones se
tratan como datos, sin obedecer instrucciones encontradas dentro de ellas.
Los audios de una misma fecha se agrupan para generar una entrada por jornada.
Los archivos sin fecha quedan señalados para revisión.

Word: export_docx.py usa el Python incluido con Codex y lxml. Solo sustituye
word/document.xml; conserva el resto de las partes de Plantilla guía.docx intactas.
La portada y el nombre del archivo usan el mes y año de las fechas de las jornadas
del texto revisado (encabezados ## en español o con fecha AAAA-MM-DD). Si hay
varios meses, muestran el intervalo; si no hay fechas reconocibles, indican
«Periodo por confirmar». No se toman meses mencionados dentro del relato. Cada jornada
empieza en una página nueva. El texto editado en la pantalla es el que se exporta.

Después de cambiar el servicio Node hay que reiniciar Diario; los cambios de HTML
se cargan al recargar la pantalla.

Historial de WhatsApp: extraction-history.cjs carga primero los mensajes recientes
del chat y después los anteriores hasta cubrir la fecha inicial. Lee tanto los
mensajes devueltos como la colección actualizada por WhatsApp, elimina duplicados
y reintenta las cargas vacías. Si no logra cubrir el rango ni confirmar el inicio
del historial, devuelve una advertencia para mostrar la extracción como parcial.
El servidor ../wsp/server.js delega readExtractionHistory en este archivo.
Pruebas: node tests/diario-extraction-history.test.cjs y node tests/diario-service.test.cjs.

Whisper: el botón del módulo ejecuta el lanzador
C:/Users/Andres/Documents/Codex/2026-11-01/ex/outputs/Probar-faster-whisper.cmd
con --input y --output. El lanzador conserva su entorno propio de faster-whisper,
modelo medium y ajustes de GPU. whisper_workflow.py archiva output antes de
invocarlo y comprueba que genere TSV, TXT, SRT y VTT para cada audio.
La pausa final recibe entrada cerrada al ejecutarse desde el módulo, por lo que
no bloquea la finalización; el CMD original sigue disponible para uso manual.
El antiguo run_script.bat conserva su ejecución manual del motor anterior.
El registro de cada conversión se guarda en tmp/whatsapp-diario/whisper.log.
Los errores de Python se muestran en el módulo junto a la ruta del registro.

Antes de guardar una extracción con contenido, los archivos sueltos anteriores
de input y output se mueven a archivo_AAAA-MM-DD_HH-MM-SS_identificador dentro de
cada carpeta. Antes de transcribir se archiva únicamente output: los audios de
input permanecen disponibles para convertir. No se recorren ni mueven las
subcarpetas existentes. Si un movimiento falla se revierten los movimientos
anteriores del mismo archivado. La extracción y la conversión se bloquean entre
sí para impedir que el módulo mueva archivos de un trabajo activo.
Pruebas del archivado: python tests/diario-whisper-workflow.test.py.

Generación: se normalizan los encabezados habituales de reflexión (del día,
pedagógica, final o personal). Una reflexión vacía no se considera válida.
Cuando el modelo omite esta sección, se conserva el relato y se pide únicamente
la reflexión, con un máximo de dos intentos, antes de continuar con las demás
jornadas. El borrador anterior se conserva si la generación no llega a terminar.
Pruebas: node tests/diario-reflection.test.cjs.
