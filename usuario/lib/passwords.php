<?php
/** Compatibilidad de lectura: los hashes almacenados nunca son contraseñas válidas. */
function login_password_valida($password, $stored, $usuario = ''): bool
{
    if (!is_string($password) || $password === '' || !is_string($stored) || $stored === '') return false;
    if (!empty(password_get_info($stored)['algo'])) return password_verify($password, $stored);
    // Cuentas heredadas: admitir su contraseña, sin aceptar el hash como contraseña.
    if (preg_match('/\A[0-9a-f]{40}\z/i', $stored)) {
        foreach ([sha1($password . 'SGA'), sha1(strtolower($password) . 'SGA'), sha1($password)] as $hash) {
            if (hash_equals(strtolower($stored), $hash)) return true;
        }
        return false;
    }
    if (preg_match('/\A[0-9a-f]{32}\z/i', $stored)) return hash_equals(strtolower($stored), md5($password));
    // Texto plano antiguo: conservar el acceso hasta cambiar la contraseña.
    return hash_equals($stored, $password);
}
