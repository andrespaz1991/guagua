<?php
/** Resolver una ruta de medios real, sin salidas de la carpeta ni archivos de código. */
function guagua_media_path(string $base, string $relative): ?string
{
    $relative = str_replace('\\', '/', $relative);
    if ($relative === '' || preg_match('~(?:^|/)\.|[\x00\r\n:]~', $relative) || str_starts_with($relative, '/')) return null;
    $extensions = ['pdf','txt','csv','zip','doc','docx','xls','xlsx','ppt','pptx','odt','ods','jpg','jpeg','png','gif','webp','mp3','mp4','ogg','wav'];
    if (!in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), $extensions, true)) return null;
    $root = realpath($base); $file = realpath($base . DIRECTORY_SEPARATOR . $relative);
    if ($root === false || $file === false || !is_file($file)) return null;
    $root = rtrim(str_replace('\\','/',$root),'/') . '/';
    $normalized = str_replace('\\','/',$file);
    $inside = DIRECTORY_SEPARATOR === '\\' ? strncasecmp($normalized,$root,strlen($root))===0 : strncmp($normalized,$root,strlen($root))===0;
    return $inside ? $file : null;
}
