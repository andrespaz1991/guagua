# Prueba de Google Health en Guagua

Esta integración comprueba si la cuenta autorizada tiene un perfil vinculado a
Google Health y consulta hasta 90 días de actividad, mediciones y sueño. No presupone que
el historial de Google Fit se haya migrado.

## Configuración

1. Abrir el proyecto del cliente OAuth usado por Guagua en Google Cloud.
2. Habilitar **Google Health API** (`health.googleapis.com`).
3. En Google Auth Platform → Acceso a los datos, agregar el permiso
   los permisos `https://www.googleapis.com/auth/googlehealth.activity_and_fitness.readonly`,
   `https://www.googleapis.com/auth/googlehealth.health_metrics_and_measurements.readonly`
   y `https://www.googleapis.com/auth/googlehealth.sleep.readonly`.
4. Si el proyecto está en pruebas, agregar la cuenta del usuario a Usuarios de prueba.
5. Mantener `http://localhost` en Orígenes autorizados de JavaScript del cliente web.
6. Abrir `http://localhost/guagua/` en el computador y pulsar **Conectar Google Health**.

Se reutiliza `GUAGUA_GOOGLE_CLIENT_ID`. No hace falta compartir secretos. Los tokens
se guardan cifrados en el servidor con AES-256-GCM; nunca se devuelven al navegador
ni se registran. Se necesitan `GUAGUA_GOOGLE_CLIENT_SECRET` y una clave privada
`GUAGUA_HEALTH_TOKEN_KEY` de 64 caracteres hexadecimales en `.env`.
Conectar una vez con el nuevo flujo permite renovar el acceso al recargar y
sincronizar sin volver a pulsar Conectar. Desconectar revoca y elimina la autorización
guardada, conservando el historial. Si el proyecto OAuth está en modo Pruebas,
Google puede vencer el refresh token a los 7 días y requerir nueva autorización.

En la pantalla Historial, elegir el rango y pulsar **Sincronizar Google** para traer
periodos anteriores. **Consultar** solo lee los datos guardados localmente. Cada lote
admite hasta 90 días. La tarjeta del inicio consulta los últimos 30 días guardados.
Se consultan pasos, distancia, energía activa, pisos, minutos activos, pulso medio,
mínimo y máximo, peso medio diario, grasa corporal media y sueño (total dormido,
profundo, REM y ligero). Los lotes de pulso y minutos activos se dividen en rangos
de hasta 14 días. La distancia se convierte de milímetros a km y el peso de gramos
a kg. El sueño se agrupa por la fecha local de despertar y usa sesiones reconciliadas.
Si una consulta no está disponible, las demás pueden continuar y se muestra el error.
Los permisos se solicitan por categoría: las categorías denegadas se omiten.
Las fases de sueño ausentes permanecen sin datos; no se inventan ceros.

## Resultados posibles

- `ACCOUNT_NOT_LINKED`: iniciar sesión con esa cuenta en la app Google Health.
  Vincularla no garantiza que aparezcan datos antiguos de Google Fit.
- `SERVICE_DISABLED`: habilitar la API en el proyecto correcto.
- `PERMISSION_DENIED`: revisar permiso de actividad, acceso a la API y usuarios de prueba.
- Cuenta vinculada sin pasos: la consulta funcionó pero ese periodo no devolvió datos.
  No se crean totales ficticios ni se elimina el historial previo.

Los datos de Google y los del iPhone tienen espacios de almacenamiento separados.
No se suman entre sí. El servidor solo llama a endpoints fijos de Google Health;
la sincronización requiere sesión de Guagua y un token CSRF válido.

Referencias:
- https://developers.google.com/health/setup
- https://developers.google.com/health/aggregate-data
- https://developers.google.com/health/endpoints
