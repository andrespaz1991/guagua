<?php
declare(strict_types=1);
require_once __DIR__.'/Planner.php';

final class DailySchoolSchedule
{
    public static function personal(string $date, string $message = ''): array
    {
        DailyPlanner::date($date);
        $days = [1=>'lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
        $weekday = $days[(int)(new DateTimeImmutable($date))->format('N')];
        return ['date'=>$date,'weekday'=>$weekday,'subjects'=>[],
            'message'=>$message ?: 'No tienes clases asignadas para este '.$weekday.'. Organiza tus tareas en Personal.'];
    }

    public static function load(PDO $db, string $owner, int $institution, string $date): array
    {
        $result = self::personal($date);
        if ($owner === '') throw new InvalidArgumentException('Inicia sesión para consultar tu horario.');
        if ($institution <= 0) return self::personal($date,'No hay una institución seleccionada. Puedes organizar tus tareas en Personal.');
        // Assignment years use IDs; the OR also admits legacy rows storing the calendar year.
        // Like Mis cursos, use the assignment's academic year as the scope. Block dates
        // can belong to an earlier period after assignments are carried into a new year;
        // they must not hide that year's weekly timetable from the daily planner.
        // Normalize weekdays in PHP: legacy schedules contain accented and uppercase variants.
        $query = $db->prepare("SELECT a.id_asignacion, a.id_asignatura, m.nombre_materia,
                g.nombre_categoria_curso AS grado, h.dia, h.hora_inicio, h.hora_fin
            FROM asignacion a
            JOIN horario h ON h.id_asignacion=a.id_asignacion
            JOIN ano_lectivo y ON (a.ano_lectivo=y.id_ano_lectivo OR CAST(a.ano_lectivo AS CHAR)=CAST(y.nombre_ano_lectivo AS CHAR))
            LEFT JOIN materia_oficial m ON m.id_materia=a.id_asignatura
            LEFT JOIN categoria_curso g ON g.id_categoria_curso=a.id_categoria_curso
            WHERE a.id_docente=? AND a.institucion_educativa=?
                AND LOWER(TRIM(a.visible))='si' AND y.nombre_ano_lectivo=?
            ORDER BY h.hora_inicio, a.id_asignacion, h.hora_fin");
        $query->execute([$owner,$institution,substr($date,0,4)]);
        $subjects = [];
        foreach ($query->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (self::weekday((string)$row['dia']) !== self::weekday($result['weekday'])) continue;
            $id = (int)$row['id_asignacion'];
            if (!isset($subjects[$id])) {
                $subject = trim((string)$row['nombre_materia']);
                if ($subject === '') $subject = 'Materia '.$row['id_asignatura'];
                $grade = trim((string)$row['grado']);
                $subjects[$id] = ['source_key'=>'assignment:'.$institution.':'.$id,'assignment_id'=>$id,
                    'name'=>mb_substr($subject.($grade !== '' ? ' · '.$grade : ''),0,100,'UTF-8'),
                    'subject'=>$subject,'grade'=>$grade,'color'=>'blue','blocks'=>[]];
            }
            $block = ['start'=>substr((string)$row['hora_inicio'],0,5),'end'=>substr((string)$row['hora_fin'],0,5)];
            if (!in_array($block,$subjects[$id]['blocks'],true)) $subjects[$id]['blocks'][] = $block;
        }
        $result['subjects'] = array_values($subjects);
        if ($subjects) $result['message'] = 'Materias de tu horario para el '.$result['weekday'].'. Escribe las tareas debajo de cada una.';
        return $result;
    }

    private static function weekday(string $value): string
    {
        return strtr(mb_strtolower(trim($value),'UTF-8'),['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u']);
    }
}
