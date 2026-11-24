<?php
require_once __DIR__ . '/../../comun/database.php';

function reset_encode(string $value): string { return rtrim(strtr(base64_encode($value), '+/', '-_'), '='); }
function reset_payload(string $token): ?array
{
    if (strlen($token) > 512 || !preg_match('/\A([A-Za-z0-9_-]+)\.([a-f0-9]{64})\z/', $token, $matches)) return null;
    $decoded = base64_decode(strtr($matches[1], '-_', '+/'), true);
    $payload = $decoded === false ? null : json_decode($decoded, true);
    if (!is_array($payload) || !is_string($payload['id'] ?? null) || !preg_match('/\A\d{1,20}\z/', $payload['id']) || !is_int($payload['expires'] ?? null)) return null;
    return $payload;
}
function reset_issue(string $id, string $storedPassword, string $secret, int $now): string
{
    if (strlen($secret) < 32) throw new RuntimeException('Configure la clave de recuperación.');
    $payload = reset_encode(json_encode(['id'=>$id,'expires'=>$now+1800], JSON_THROW_ON_ERROR));
    return $payload . '.' . hash_hmac('sha256', 'reset:' . $payload . ':' . hash('sha256',$storedPassword), $secret);
}
function reset_valid(string $token, string $storedPassword, string $secret, int $now): bool
{
    $payload = reset_payload($token);
    if (!$payload || strlen($secret)<32 || $payload['expires'] <= $now || $payload['expires'] > $now+1800) return false;
    [$body,$signature] = explode('.',$token,2);
    return hash_equals(hash_hmac('sha256','reset:' . $body . ':' . hash('sha256',$storedPassword),$secret),$signature);
}
function reset_request(mysqli $db, string $identity): void
{
    $statement = $db->prepare("SELECT id_usuario, clave, correo FROM usuario WHERE (id_usuario=? OR usuario=?) AND estado='activo' LIMIT 1");
    $statement->bind_param('ss',$identity,$identity); $statement->execute();
    $user = $statement->get_result()->fetch_assoc(); $statement->close();
    if (!$user || !filter_var($user['correo'],FILTER_VALIDATE_EMAIL)) return;
    $secret = GuaguaDatabase::environment('GUAGUA_RESET_SECRET');
    $base = GuaguaDatabase::environment('GUAGUA_PUBLIC_URL');
    if (!$secret || !$base || !filter_var($base,FILTER_VALIDATE_URL) || !in_array(parse_url($base,PHP_URL_SCHEME),['http','https'],true)) throw new RuntimeException('Configure recuperación y URL pública.');
    $token = reset_issue((string)$user['id_usuario'],(string)$user['clave'],$secret,time());
    $link = rtrim($base,'/') . '/usuario/restablecer.php?token=' . rawurlencode($token);
    $sender = GuaguaDatabase::environment('GUAGUA_MAIL_FROM');
    if (!$sender || !filter_var($sender,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Configure el remitente de correo.');
    $headers = 'From: ' . $sender . "\r\nContent-Type: text/plain; charset=UTF-8";
    if (!@mail($user['correo'],'Restablecer acceso a Guagua',"Use este enlace durante los próximos 30 minutos:\n" . $link . "\nSi no lo solicitó, ignore este mensaje.",$headers)) throw new RuntimeException('No se pudo entregar el correo de recuperación.');
}
function reset_apply(mysqli $db, string $token, string $password, string $secret): bool
{
    $payload = reset_payload($token);
    if (!$payload || strlen($password)<8 || strlen($password)>72 || str_contains($password,"\0")) return false;
    $db->begin_transaction();
    try {
        $statement = $db->prepare("SELECT clave FROM usuario WHERE id_usuario=? AND estado='activo' FOR UPDATE");
        $statement->bind_param('s',$payload['id']); $statement->execute();
        $user = $statement->get_result()->fetch_assoc(); $statement->close();
        if (!$user || !reset_valid($token,(string)$user['clave'],$secret,time())) { $db->rollback(); return false; }
        $hash = password_hash($password,PASSWORD_DEFAULT);
        $statement = $db->prepare("UPDATE usuario SET clave=?, mascota='NO' WHERE id_usuario=? AND clave=?");
        $statement->bind_param('sss',$hash,$payload['id'],$user['clave']); $statement->execute();
        $changed = $statement->affected_rows === 1; $statement->close();
        $db->commit(); return $changed;
    } catch (Throwable $error) { $db->rollback(); throw $error; }
}
