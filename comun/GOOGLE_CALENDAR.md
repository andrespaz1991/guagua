# Tarjeta de Google Calendar

La tarjeta aparece en el inicio para los usuarios autenticados y conserva el orden
de las demás tarjetas. Muestra los siguientes ocho eventos del calendario principal,
incluidas las instancias de eventos recurrentes y los eventos de todo el día.

## Configuración inicial

1. En Google Cloud, habilitar Google Calendar API y configurar la pantalla de
   consentimiento OAuth. En modo de prueba, agregar las cuentas como usuarios de prueba.
2. Crear un cliente OAuth de tipo **Aplicación web**. Agregar el origen de Guagua a
   **Orígenes autorizados de JavaScript**, por ejemplo `http://localhost` (sin `/guagua`),
   y el origen HTTPS de producción. Incluir el puerto cuando no sea el predeterminado.
3. En el `.env` de Guagua, agregar `GUAGUA_GOOGLE_CLIENT_ID="IDENTIFICADOR.apps.googleusercontent.com"`.
   No se necesita un secreto de cliente para este flujo.
4. Recargar el inicio y pulsar **Conectar Google Calendar**. Seleccionar la cuenta
   y conceder acceso de lectura a los eventos.

La autorización utiliza Google Identity Services, flujo de código con ventana popup
y `calendar.events.readonly`. Configurar también `GUAGUA_GOOGLE_CLIENT_SECRET` y
`GUAGUA_HEALTH_TOKEN_KEY` (64 caracteres hexadecimales privados) en `.env`.
El servidor guarda access y refresh tokens cifrados con AES-256-GCM, por usuario y
servicio, en la base SQLite protegida de Salud. Nunca se devuelven al navegador.
Conectar una vez con este flujo permite recargar el inicio sin volver a pulsar el
botón: el servidor renueva automáticamente el acceso y consulta los próximos eventos.
**Actualizar** vuelve a consultar la agenda y **Desconectar** revoca el permiso.
Las conexiones anteriores, que solo guardaban el token en memoria, requieren una
última autorización para obtener el refresh token. Si Google no lo entrega, retirar
el acceso de Guagua en la cuenta Google y volver a conectar.
En proyectos OAuth en modo Pruebas, Google puede vencer los refresh tokens a los 7
días. Publicar el consentimiento evita ese límite de pruebas; una revocación de
Google o del usuario puede requerir conectar de nuevo.
La conexión de Google Calendar de Codex es independiente de la autorización de Guagua.

Referencias: https://developers.google.com/identity/oauth2/web/guides/use-token-model
y https://developers.google.com/workspace/calendar/api/v3/reference/events/list
