<?php
$root = dirname(__DIR__);
$directory = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
$filtered = new RecursiveCallbackFilterIterator($directory, static fn($entry) => !$entry->isDir() || !in_array(strtolower($entry->getFilename()), ['vendor', 'libraries', 'librerias', 'tmp', 'output', 'node_modules', '.git'], true));
$files = new RecursiveIteratorIterator($filtered);
$count = 0;
foreach ($files as $file) {
    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if ($file->getExtension() !== 'php' || preg_match('~(?:^|/)(?:vendor|libraries|librerias|tmp|output|node_modules|\.git)/~i', $relative) || $relative === 'comun/database.php') continue;
    $tokens = token_get_all(file_get_contents($file->getPathname()));
    $significant = array_values(array_filter($tokens, static fn($token) => !is_array($token) || !in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)));
    foreach ($significant as $index => $token) {
        if (!is_array($token)) continue;
        $name = strtolower(ltrim($token[1], '\\'));
        $previous = $significant[$index - 1] ?? null;
        if ((in_array($name, ['mysqli', 'pdo'], true) && is_array($previous) && $previous[0] === T_NEW)
            || ($token[0] === T_STRING && in_array($name, ['mysqli_connect', 'mysqli_real_connect', 'mysqli_init', 'mysql_connect'], true))) {
            throw new RuntimeException('Conexión fuera del proveedor: ' . $relative . ':' . $token[2]);
        }
    }
    ++$count;
}
echo "OK: $count archivos PHP revisados; conexiones centralizadas.\n";
