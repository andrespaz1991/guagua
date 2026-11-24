<?php
require_once __DIR__ . '/../comun/persona_imagen.php';
function assert_avatar($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
foreach (['f', 'F', ' femenino ', 'femenina', 'female', 'mujer'] as $gender) {
    foreach (['', 'user-icon.png', 'user-icon2.png', 'user-iconf2.png', 'USER-ICONF.PNG'] as $photo) {
        assert_avatar(guagua_persona_foto($photo, $gender) === 'user-iconf.png', 'El género femenino debe corregir cualquier icono predeterminado.');
    }
}
foreach (['m', 'M', ' masculino ', 'male', 'hombre'] as $gender) {
    assert_avatar(guagua_persona_foto('user-iconf.png', $gender) === 'user-icon.png', 'El género masculino debe corregir el icono femenino.');
}
assert_avatar(guagua_persona_foto('foto-inexistente-para-prueba.jpg', 'f') === 'user-iconf.png', 'Una foto ausente debe usar el género registrado.');
assert_avatar(guagua_persona_foto('', '') === 'user-icon2.png', 'Un género vacío debe usar el icono neutro.');
assert_avatar(guagua_genero_normalizado('sin confirmar') === '', 'No se deben inferir géneros desconocidos.');
foreach (glob(__DIR__ . '/../sga-data/foto/*') as $file) {
    if (!is_file($file) || guagua_foto_predeterminada($file)) continue;
    assert_avatar(guagua_persona_foto(basename($file), 'f') === basename($file), 'Las fotos personales deben conservarse.');
    assert_avatar(guagua_persona_foto(basename($file), 'm') === basename($file), 'Cambiar el género no debe sustituir una foto personal.');
    break;
}
echo "OK: variantes de género, iconos contradictorios, foto ausente, género desconocido y conservación de fotos personales.\n";
