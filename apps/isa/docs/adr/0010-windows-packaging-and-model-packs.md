# ADR 0010: Packaging Windows y paquetes de modelos separados

**Estado:** Accepted

## Contexto

La instalación debe ser sencilla y también posible sin Internet, pero modelos y voces pueden pesar mucho y tener licencias distintas.

## Decisión

Distribuir app base mediante instalador Windows y manejar modelos/voz como paquetes explícitos con manifest, checksum, origen y licencia. Evaluar WebView2 offline para distribución sin red.

## Consecuencias

- El usuario puede instalar activos desde un paquete local.
- No hay descargas silenciosas.
- La actualización de modelos queda desacoplada de la aplicación.
