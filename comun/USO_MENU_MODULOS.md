# Menú común para módulos PHP

En un archivo nuevo de Guagua, cargue las dependencias en el `head` y dibuje el menú como primer elemento del `body`:

```php
<?php require_once __DIR__ . '/../comun/cabecera_modulo.php'; ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi módulo</title>
    <?php guagua_menu_estilos(); ?>
</head>
<body>
    <?php guagua_menu_renderizar(); ?>

    <main>
        <!-- Contenido propio del módulo -->
    </main>
</body>
</html>
```

La ruta del `require_once` cambia según la profundidad del archivo. Por ejemplo, desde `apps/nuevo/index.php` use `__DIR__ . '/../../comun/cabecera_modulo.php'`.

Para módulos que ya son páginas completas o aplicaciones independientes, use `comun/modulo.php?id=ID_DEL_MENU`; el contenedor les añade el mismo menú sin modificar su código.

El contenedor envía `guagua_embedded=1`. Las plantillas y `guagua_menu_renderizar()` omiten la cabecera y el pie internos en ese modo, y conservan la compatibilidad con `?embebido`. Las navegaciones y los formularios dentro del iframe también se reconocen mediante `Sec-Fetch-Dest: iframe`. Esta detección afecta únicamente la presentación; cada módulo sigue comprobando la sesión y los permisos.
