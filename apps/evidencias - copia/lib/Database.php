<?php
declare(strict_types=1);

final class EvidenceDatabase
{
    public static function connect(): mysqli
    {
        require_once dirname(__DIR__, 3) . '/comun/config.php';

        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $host = SERVIDORBD;
        $port = null;
        if (substr_count($host, ':') === 1) {
            [$host, $portValue] = explode(':', $host, 2);
            $port = (int) $portValue;
        }

        $connection = new mysqli($host, USUARIOBD, CLAVEBD, BASEDEDATOS, $port);
        $connection->set_charset('utf8mb4');
        $connection->query("SET time_zone = '" . TIME_ZONE_OFFSET . "'");

        return $connection;
    }
}
