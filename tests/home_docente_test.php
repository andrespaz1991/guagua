<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Renderizar la tarjeta real sin conectarse a datos de usuarios.
class Academico {}
class Fecha {
    public static function formato_hora_corta($value) { return $value; }
    public static function formato_fecha($value) { return $value; }
    public static function formato_hora($value) { return $value; }
}
class Persona {
    public string $nombre = 'Persona de prueba';
    public function __construct($id) {}
}
class Comun {
    public static function puntos_suspensivos($text, $length) { return substr($text, 0, $length); }
}
define('SGA_CURSOS_URL', '/guagua/cursos');
define('SGA_COMUN_URL', '/guagua/comun');
require_once dirname(__DIR__) . '/clases/Actividad.Class.php';

class HomeActividadFixture extends Actividad {
    public bool $recordatorios = false;
    public function consultar_horario_Asignacionold() {
        return [['id_asignacion'=>12, 'nombre_materia'=>'Curso de prueba', 'hora_inicio'=>'08:00', 'hora_fin'=>'09:00']];
    }
    public function actividades_pendientes_por_fecha($fecha = '', $limite = 10) {
        return $this->recordatorios ? [['identificacion'=>'prueba', 'contenido'=>'Cita de prueba', 'hora_inicio'=>'10:00', 'hora_fin'=>'11:00']] : [];
    }
    public function notaspendientes() {
        return $this->recordatorios ? [['id_nota'=>7, 'id_asignacion'=>12, 'fecha_nota'=>'2026-11-21', 'hora_nota'=>'10:00', 'nota'=>'Recordatorio']] : [];
    }
    public function consultar_materia($id) { return [(object)['nombre_materia'=>'Curso de prueba']]; }
    public function asignaciones_de_un_docente($docente) {
        throw new RuntimeException('La tarjeta no debe recorrer asignaciones para calcular contadores que no muestra.');
    }
}
$_SESSION = ['id_usuario'=>'docente-prueba'];
$fixture = new HomeActividadFixture();
foreach ([false, true] as $recordatorios) {
    $fixture->recordatorios = $recordatorios;
    ob_start(); $fixture->mis_actividades_docente(); $html = ob_get_clean();
    if (!str_contains($html, 'Curso de prueba') || !str_contains($html, 'asignacion=12')) throw new RuntimeException('Se perdieron los cursos del día.');
    if ($recordatorios && (!str_contains($html, 'Cita de prueba') || !str_contains($html, 'Recordatorio'))) throw new RuntimeException('Se perdieron las citas o las notas.');
}
echo "OK: tarjeta docente con cursos, citas y notas, sin consultas repetidas de contadores invisibles.\n";
