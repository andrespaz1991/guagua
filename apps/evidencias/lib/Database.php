<?php
declare(strict_types=1);

final class EvidenceDatabase
{
    public static function connect(): mysqli
    {
        require_once dirname(__DIR__, 3) . '/comun/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        return guagua_db_mysqli();
    }
}
