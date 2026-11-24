<?php
require_once __DIR__ . '/sql.php';
/** Metadatos compartidos por los módulos durante una petición, sin SELECT repetidos. */
function guagua_institution_context(mysqli $db, int $institution): array
{
    static $institutions = [];
    $key = $db->thread_id . ':' . $institution;
    if (!array_key_exists($key,$institutions)) {
        $institutions[$key] = guagua_sql($db,'SELECT * FROM institucion_educativa WHERE id_institucion_educativa = ? LIMIT 1',[$institution],'i')->fetch_assoc() ?: [];
    }
    return $institutions[$key];
}
