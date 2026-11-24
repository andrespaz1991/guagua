<?php
require_once __DIR__ . '/../../comun/sql.php';
require_once __DIR__ . '/../../comun/academico.php';
require_once __DIR__ . '/../../comun/opciones.php';

final class InicioAnual
{
    private mysqli $db;
    public const DAYS = ['lunes','martes','miercoles','jueves','viernes','sabado','domingo'];
    public function __construct(mysqli $db) { $this->db = $db; }

    public static function schema(): string
    {
        return "CREATE TABLE IF NOT EXISTS inicio_anual_borradores (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            institucion INT NOT NULL, usuario VARCHAR(255) NOT NULL, ano INT NOT NULL,
            datos LONGTEXT NOT NULL, paso INT NOT NULL DEFAULT 0, revision INT NOT NULL DEFAULT 1,
            estado VARCHAR(20) NOT NULL DEFAULT 'borrador', resultado LONGTEXT NULL,
            actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY propietario_ano (institucion, usuario, ano)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    }
    public function ensureSchema(): void
    {
        if (!$this->db->query(self::schema())) throw new RuntimeException('No fue posible preparar los borradores del asistente.');
    }
    private function row(string $sql, array $args = []): ?array { return guagua_sql($this->db,$sql,$args)->fetch_assoc() ?: null; }
    private function rows(string $sql, array $args = []): array { return guagua_sql($this->db,$sql,$args)->fetch_all(MYSQLI_ASSOC); }
    private function actor(array $actor): void
    {
        if (empty($actor['id_usuario']) || (int)($actor['id_institucion'] ?? 0) <= 0
            || !in_array($actor['rol'] ?? '', ['admin','directivo','docente'],true)) throw new InvalidArgumentException('Se requiere una sesión de gestión académica y una institución seleccionada.');
    }
    public static function canActivate(array $actor): bool { return in_array($actor['rol'] ?? '', ['admin','directivo'],true); }
    private static function year($value): int
    {
        $year = filter_var($value,FILTER_VALIDATE_INT);
        if (!$year || $year < 2000 || $year > 2100) throw new InvalidArgumentException('El año debe estar entre 2000 y 2100.');
        return $year;
    }
    public function catalog(array $actor): array
    {
        $this->actor($actor);
        $templates=[];
        if (is_file(__DIR__.'/../../comun/plantillas/plantilla_kids_m.php')) $templates[1]=['id'=>1,'nombre'=>'Plantilla principal de Guagua'];
        foreach (glob(__DIR__.'/../../comun/plantillas/plantilla*/plantilla_kids_m.php') ?: [] as $file) {
            if (preg_match('#/plantilla(\d+)/#',str_replace('\\','/',$file),$match) && (int)$match[1]>1) $templates[(int)$match[1]]=['id'=>(int)$match[1],'nombre'=>'Plantilla '.$match[1]];
        }
        return [
            'plantillas'=>array_values($templates),
            'institucion'=>$this->row('SELECT * FROM institucion_educativa WHERE id_institucion_educativa=?',[$actor['id_institucion']]),
            'anos'=>$this->rows('SELECT id_ano_lectivo,nombre_ano_lectivo,estado FROM ano_lectivo ORDER BY nombre_ano_lectivo DESC'),
            'activo'=>guagua_ano_activo($this->db), 'periodo'=>guagua_periodo_activo($this->db),
            'grados'=>$this->rows('SELECT id_categoria_curso AS id,nombre_categoria_curso AS nombre,nivel_educativo AS nivel FROM categoria_curso ORDER BY id_categoria_curso'),
            'materias'=>$this->rows('SELECT id_materia AS id,nombre_materia AS nombre FROM materia_oficial ORDER BY nombre_materia'),
            'docentes'=>$this->rows("SELECT id_usuario AS id,TRIM(CONCAT(nombre,' ',apellido)) AS nombre FROM usuario WHERE LOWER(TRIM(estado))='activo' AND FIND_IN_SET('docente',REPLACE(LOWER(rol),' ','')) > 0 ORDER BY apellido,nombre"),
            'estudiantes'=>$this->rows("SELECT id_usuario AS id,TRIM(CONCAT(apellido,' ',nombre)) AS nombre,observaciones AS grado FROM usuario WHERE LOWER(TRIM(estado))='activo' AND FIND_IN_SET('estudiante',REPLACE(LOWER(rol),' ','')) > 0 ORDER BY apellido,nombre"),
            'borradores'=>$this->rows('SELECT ano,paso,estado,actualizado FROM inicio_anual_borradores WHERE institucion=? AND usuario=? ORDER BY ano DESC',[$actor['id_institucion'],$actor['id_usuario']]),
            'puede_activar'=>self::canActivate($actor), 'usuario'=>(string)$actor['id_usuario'],
        ];
    }
    public function blank(int $year, array $actor, array $files = []): array
    {
        $this->actor($actor); self::year($year);
        $institution = $this->row('SELECT * FROM institucion_educativa WHERE id_institucion_educativa=?',[$actor['id_institucion']]);
        if ($institution) $institution['autoregistrarse']=strtoupper(trim((string)($institution['autoregistrarse'] ?? '')));
        return ['ano'=>$year,'descripcion'=>'','institucion'=>$institution ?: [],'periodos'=>[], 'grados'=>[], 'materias'=>[],
            'asignaciones'=>[], 'horarios'=>[], 'estudiantes'=>[], 'fecha_inscripcion'=>'', 'archivos'=>$files];
    }
    public function load(int $year, array $actor): ?array
    {
        $this->actor($actor); self::year($year);
        $row = $this->row('SELECT * FROM inicio_anual_borradores WHERE institucion=? AND usuario=? AND ano=?',[$actor['id_institucion'],$actor['id_usuario'],$year]);
        if (!$row) return null;
        return ['plan'=>json_decode($row['datos'],true,512,JSON_THROW_ON_ERROR), 'revision'=>(int)$row['revision'], 'paso'=>(int)$row['paso'],
            'estado'=>$row['estado'],'resultado'=>$row['resultado'] ? json_decode($row['resultado'],true,512,JSON_THROW_ON_ERROR) : null];
    }
    public function save(array $plan, int $revision, int $step, array $actor): array
    {
        $this->actor($actor); $year=self::year($plan['ano'] ?? null);
        if ($step<0 || $step>7) throw new InvalidArgumentException('Paso no válido.');
        foreach (['periodos','grados','materias','asignaciones','horarios','estudiantes'] as $key) {
            if (!isset($plan[$key]) || !is_array($plan[$key]) || count($plan[$key])>10000) throw new InvalidArgumentException('El borrador contiene una lista no válida: '.$key);
        }
        $json = json_encode($plan,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR);
        if (strlen($json)>4000000) throw new InvalidArgumentException('El borrador supera el tamaño admitido.');
        if ($revision===0) {
            try { guagua_sql($this->db,'INSERT INTO inicio_anual_borradores (institucion,usuario,ano,datos,paso) VALUES (?,?,?,?,?)',[$actor['id_institucion'],$actor['id_usuario'],$year,$json,$step]); }
            catch (Throwable $e) { if ($this->load($year,$actor)) throw new InvalidArgumentException('Ya existe un borrador de este año. Ábrelo para continuar.'); throw $e; }
        } else {
            $statement=$this->db->prepare("UPDATE inicio_anual_borradores SET datos=?,paso=?,revision=revision+1,estado='borrador',resultado=NULL WHERE institucion=? AND usuario=? AND ano=? AND revision=? AND estado IN ('borrador','preparado')");
            $institution=(int)$actor['id_institucion'];$user=(string)$actor['id_usuario'];
            try {
                $statement->bind_param('siisii',$json,$step,$institution,$user,$year,$revision);
                $statement->execute();
                if ($statement->affected_rows!==1) throw new InvalidArgumentException('El borrador cambió en otra pestaña o ya fue instalado. Vuelve a abrirlo.');
            } finally { $statement->close(); }
        }
        return $this->load($year,$actor);
    }
    public function copy(int $year, int $source, array $actor, array $files=[]): array
    {
        $plan=$this->blank($year,$actor,$files);
        $base=$this->row('SELECT * FROM ano_lectivo WHERE id_ano_lectivo=?',[$source]);
        if (!$base) throw new InvalidArgumentException('El año de referencia no existe.');
        $periods=$this->rows("SELECT * FROM periodo WHERE ano_lectivo=? ORDER BY estado_periodo DESC,id_periodo DESC",[$base['nombre_ano_lectivo']]);
        $seen=[];
        foreach ($periods as $p) {
            if (isset($seen[$p['nombre_periodo']])) continue;
            $seen[$p['nombre_periodo']]=true;
            $plan['periodos'][]=['nombre'=>$p['nombre_periodo'],'descripcion'=>$p['descripcion_periodo'],
                'inicio'=>self::shiftDate($p['fecha_inicio'],$year),'fin'=>self::shiftDate($p['fecha_fin'],$year),'activo'=>$p['estado_periodo']==='1'];
        }
        usort($plan['periodos'],fn($a,$b)=>strcmp($a['inicio'],$b['inicio']));
        $sql='SELECT a.*,g.nombre_categoria_curso,g.nivel_educativo,m.nombre_materia FROM asignacion a JOIN categoria_curso g ON g.id_categoria_curso=a.id_categoria_curso JOIN materia_oficial m ON m.id_materia=a.id_asignatura WHERE a.ano_lectivo=? AND a.institucion_educativa=?';
        $args=[$source,$actor['id_institucion']];
        if (!self::canActivate($actor)) { $sql.=' AND a.id_docente=?'; $args[]=$actor['id_usuario']; }
        $grades=[];$subjects=[];
        foreach ($this->rows($sql,$args) as $a) {
            $g='g'.$a['id_categoria_curso'];$m='m'.$a['id_asignatura'];$key='a'.$a['id_asignacion'];
            $grades[$g]=['key'=>$g,'id'=>(int)$a['id_categoria_curso'],'nombre'=>$a['nombre_categoria_curso'],'nivel'=>(int)$a['nivel_educativo']];
            $subjects[$m]=['key'=>$m,'id'=>(int)$a['id_asignatura'],'nombre'=>$a['nombre_materia']];
            $plan['asignaciones'][]=['key'=>$key,'grado'=>$g,'materia'=>$m,'docente'=>(string)$a['id_docente'],'visible'=>strtolower((string)$a['visible'])==='si'];
            foreach ($this->rows('SELECT * FROM horario WHERE id_asignacion=?',[$a['id_asignacion']]) as $h) {
                $day=strtr(strtolower($h['dia']),['é'=>'e','á'=>'a']);
                $plan['horarios'][]=['asignacion'=>$key,'dia'=>$day,'inicio'=>substr($h['hora_inicio'],0,5),'fin'=>substr($h['hora_fin'],0,5),'desde'=>self::shiftDate($h['fecha_inicio'],$year),'hasta'=>self::shiftDate($h['fecha_fin'],$year)];
            }
        }
        $plan['grados']=array_values($grades);$plan['materias']=array_values($subjects);
        return $plan;
    }
    private static function shiftDate(string $date,int $year): string
    {
        if (!self::date($date)) return '';
        $month=(int)substr($date,5,2); $day=(int)substr($date,8,2);
        return checkdate($month,$day,$year) ? sprintf('%04d-%02d-%02d',$year,$month,$day) : '';
    }
    private static function date($date): bool
    {
        if (!is_string($date)) return false;
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$date);
        return $d && $d->format('Y-m-d')===$date;
    }
    private function transactionalTables(): void
    {
        // Solo se ejecuta al confirmar la instalación, antes de abrir la transacción.
        // Las instalaciones antiguas usan MyISAM en año, asignaciones e institución.
        foreach (['ano_lectivo','periodo','institucion_educativa','categoria_curso','materia_oficial','asignacion','horario','inscripcion','inicio_anual_borradores'] as $table) {
            $definition=$this->db->query("SHOW CREATE TABLE `$table`")->fetch_row()[1];
            if (!preg_match('/\bENGINE=InnoDB\b/i',$definition)) {
                if (!$this->db->query("ALTER TABLE `$table` ENGINE=InnoDB")) throw new RuntimeException('No fue posible preparar el guardado seguro del año escolar.');
            }
        }
    }
    public function validate(array $p, array $actor, int $through=7): array
    {
        $this->actor($actor); $errors=[];
        try { $year=self::year($p['ano'] ?? null); } catch (InvalidArgumentException $e) { return [$e->getMessage()]; }
        if (trim((string)($p['institucion']['nombre_institucion'] ?? ''))==='') $errors[]='Escribe el nombre de la institución.';
        if (strlen((string)($p['institucion']['nombre_institucion'] ?? ''))>255) $errors[]='El nombre de la institución es demasiado extenso.';
        $email=trim((string)($p['institucion']['correo_institucion'] ?? ''));
        if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Escribe un correo institucional válido.';
        if (strlen((string)($p['descripcion'] ?? ''))>5000) $errors[]='La descripción del año es demasiado extensa.';
        if ($through<1) return $errors;
        $periods=$p['periodos'] ?? []; $active=0;$names=[];
        if (!$periods) $errors[]='Agrega los períodos académicos.';
        foreach ($periods as $i=>$period) {
            $name=trim((string)($period['nombre'] ?? ''));
            if ($name==='' || strlen($name)>255 || isset($names[$name])) $errors[]='Cada período debe tener un nombre único.';
            if (!ctype_digit($name) || (int)$name<1) $errors[]='Identifica cada período con un número positivo (1, 2, 3…).';
            $names[$name]=true;$active+=!empty($period['activo'])?1:0;
            if (strlen((string)($period['descripcion'] ?? ''))>255) $errors[]='La descripción de un período supera el tamaño permitido.';
            $start=$period['inicio'] ?? ''; $end=$period['fin'] ?? '';
            if (!self::date($start) || !self::date($end) || $end<$start || (int)substr($start,0,4)!==$year || (int)substr($end,0,4)!==$year) $errors[]='Revisa las fechas del período '.($i+1).': deben pertenecer al año elegido.';
            foreach (array_slice($periods,0,$i) as $other) {
                if ($start<=($other['fin'] ?? '') && $end>=($other['inicio'] ?? '')) $errors[]='Los períodos no pueden compartir fechas.';
            }
        }
        if ($active!==1) $errors[]='Selecciona un único período inicial para activar.';
        if ($through<2) return array_values(array_unique($errors));
        $maps=[];
        foreach (['grados','materias'] as $type) {
            $maps[$type]=[];
            if (empty($p[$type])) $errors[]='Selecciona al menos '.($type==='grados'?'un grado.':'una asignatura.');
            foreach ($p[$type] ?? [] as $item) {
                $key=(string)($item['key'] ?? '');$name=trim((string)($item['nombre'] ?? ''));
                if ($key==='' || isset($maps[$type][$key]) || $name==='' || strlen($name)>($type==='grados'?120:255)) $errors[]='Revisa los nombres y las claves de '.$type.'.';
                if ($type==='grados' && (!isset($item['nivel']) || (int)$item['nivel']<0 || (int)$item['nivel']>9)) $errors[]='Selecciona el nivel educativo de cada grado.';
                if ((int)($item['id'] ?? 0)>0) {
                    $table=$type==='grados'?'categoria_curso':'materia_oficial';$idField=$type==='grados'?'id_categoria_curso':'id_materia';
                    if (!$this->row("SELECT $idField FROM $table WHERE $idField=?",[(int)$item['id']])) $errors[]='Un elemento del catálogo ya no existe. Actualiza el asistente.';
                }
                $maps[$type][$key]=$item;
            }
        }
        if ($through<3) return array_values(array_unique($errors));
        $assignments=[];$combos=[];
        if (empty($p['asignaciones'])) $errors[]='Agrega al menos una asignación académica.';
        foreach ($p['asignaciones'] ?? [] as $a) {
            $key=(string)($a['key'] ?? '');$teacher=(string)($a['docente'] ?? '');
            if ($key==='' || isset($assignments[$key]) || !isset($maps['grados'][$a['grado'] ?? ''],$maps['materias'][$a['materia'] ?? ''])) $errors[]='Revisa los grados y asignaturas de las asignaciones.';
            $user=$this->row('SELECT rol,estado FROM usuario WHERE id_usuario=?',[$teacher]);
            if (!$user || strtolower(trim($user['estado']))!=='activo' || !in_array('docente',array_map('trim',explode(',',strtolower($user['rol']))),true)) $errors[]='Cada asignación necesita un docente activo.';
            if (!self::canActivate($actor) && $teacher!==(string)$actor['id_usuario']) $errors[]='Con perfil docente solo puedes preparar tus propias asignaciones.';
            $combo=($a['grado'] ?? '').':'.($a['materia'] ?? '').':'.$teacher;
            if (isset($combos[$combo])) $errors[]='Hay asignaciones repetidas.';
            $combos[$combo]=true;$assignments[$key]=$a;
        }
        if ($through<4) return array_values(array_unique($errors));
        foreach ($p['horarios'] ?? [] as $i=>$h) {
            $a=$assignments[$h['asignacion'] ?? ''] ?? null;
            if (!$a || !in_array($h['dia'] ?? '',self::DAYS,true) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',(string)($h['inicio'] ?? '')) || !preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/D',(string)($h['fin'] ?? '')) || $h['fin']<=$h['inicio']) $errors[]='Revisa el curso, día y horas del bloque '.($i+1).'.';
            $start=$h['desde'] ?? '';$end=$h['hasta'] ?? '';
            if (!self::date($start) || !self::date($end) || $start>$end || (int)substr($start,0,4)!==$year || (int)substr($end,0,4)!==$year) $errors[]='El rango de cada horario debe estar dentro del año escolar.';
            foreach (array_slice($p['horarios'],0,$i) as $other) {
                $b=$assignments[$other['asignacion'] ?? ''] ?? null;
                if ($a && $b && ($a['docente']===$b['docente'] || $a['grado']===$b['grado']) && ($h['dia'] ?? '')===($other['dia'] ?? '') && $start<=($other['hasta'] ?? '') && $end>=($other['desde'] ?? '') && ($h['inicio'] ?? '')<($other['fin'] ?? '') && ($h['fin'] ?? '')>($other['inicio'] ?? '')) $errors[]='Hay bloques de horario que se cruzan para un docente o grado.';
            }
        }
        if ($through<5) return array_values(array_unique($errors));
        $students=[];
        foreach ($p['estudiantes'] ?? [] as $e) {
            $id=(string)($e['id'] ?? '');$grade=(string)($e['grado'] ?? '');
            $user=$this->row('SELECT rol,estado FROM usuario WHERE id_usuario=?',[$id]);
            if (!$user || strtolower(trim($user['estado']))!=='activo' || !in_array('estudiante',array_map('trim',explode(',',strtolower($user['rol']))),true)) $errors[]='Selecciona estudiantes activos del directorio.';
            if (!isset($maps['grados'][$grade]) || !array_filter($assignments,fn($a)=>$a['grado']===$grade)) $errors[]='Cada estudiante necesita un grado con asignaciones.';
            if (isset($students[$id])) $errors[]='Un estudiante aparece más de una vez.';
            $students[$id]=true;
        }
        if ($students && (!self::date($p['fecha_inscripcion'] ?? '') || (int)substr($p['fecha_inscripcion'],0,4)!==$year)) $errors[]='Selecciona una fecha de inscripción del año elegido.';
        if ($through>=6) {
            foreach (['asistencia'] as $key) if (strlen((string)($p['archivos'][$key] ?? ''))>2000) $errors[]='La ruta de archivos es demasiado extensa.';
            $options=$p['institucion'] ?? [];
            if (!filter_var($options['plantilla'] ?? null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1]])) $errors[]='Selecciona una plantilla institucional válida.';
            else { try { guagua_plantilla_institucion($options); } catch (RuntimeException $e) { $errors[]=$e->getMessage(); } }
            if (!in_array($options['autoregistrarse'] ?? '', ['SI','NO'],true)) $errors[]='Indica si la institución permite el autorregistro.';
        }
        return array_values(array_unique($errors));
    }

    /** Instala el borrador guardado. Reutiliza coincidencias para que repetir no duplique. */
    public function install(int $year, int $revision, bool $activate, array $actor, ?callable $applyFiles=null): array
    {
        $this->actor($actor);self::year($year);
        if ($activate && !self::canActivate($actor)) throw new InvalidArgumentException('La activación general requiere perfil administrador o directivo.');
        $lock=$this->row("SELECT GET_LOCK('guagua.inicio_anual',10) AS ok");
        if ((int)($lock['ok'] ?? 0)!==1) throw new RuntimeException('Otro proceso está instalando un año. Inténtalo nuevamente.');
        $restore=null;
        try {
            $preflight=$this->load($year,$actor);
            if (!$preflight) throw new InvalidArgumentException('Guarda el borrador antes de instalar.');
            if ($preflight['estado']==='instalado') return $preflight['resultado'];
            if ($preflight['revision']!==$revision) throw new InvalidArgumentException('El borrador cambió. Vuelve a revisarlo antes de instalar.');
            $preflightErrors=$this->validate($preflight['plan'],$actor);
            if ($preflightErrors) throw new InvalidArgumentException(implode(' ',$preflightErrors));
            $this->transactionalTables();
            $this->db->begin_transaction();
            $stored=$this->row('SELECT * FROM inicio_anual_borradores WHERE institucion=? AND usuario=? AND ano=? FOR UPDATE',[$actor['id_institucion'],$actor['id_usuario'],$year]);
            if (!$stored) throw new InvalidArgumentException('Guarda el borrador antes de instalar.');
            if ($stored['estado']==='instalado') { $this->db->commit(); return json_decode($stored['resultado'],true,512,JSON_THROW_ON_ERROR); }
            if ((int)$stored['revision']!==$revision) throw new InvalidArgumentException('El borrador cambió. Vuelve a revisarlo antes de instalar.');
            $p=json_decode($stored['datos'],true,512,JSON_THROW_ON_ERROR);
            $errors=$this->validate($p,$actor);
            if ($errors) throw new InvalidArgumentException(implode(' ', $errors));
            $annual=$this->row('SELECT * FROM ano_lectivo WHERE nombre_ano_lectivo=? FOR UPDATE',[$year]);
            if (!$activate && $annual && $annual['estado']==='Activo') throw new InvalidArgumentException('Para modificar el año actualmente activo debes instalar y activar con perfil administrador o directivo.');
            $counts=['ano'=>$year,'activado'=>$activate,'grados'=>0,'materias'=>0,'asignaciones'=>0,'horarios'=>0,'inscripciones'=>0,'periodos'=>count($p['periodos'])];
            if (!$annual) {
                guagua_sql($this->db,"INSERT INTO ano_lectivo (nombre_ano_lectivo,descripcion_ano,estado) VALUES (?,?,'Inactivo')",[$year,$p['descripcion'] ?? '']);
                $annual=['id_ano_lectivo'=>$this->db->insert_id];
            } else guagua_sql($this->db,'UPDATE ano_lectivo SET descripcion_ano=? WHERE id_ano_lectivo=?',[$p['descripcion'] ?? '',$annual['id_ano_lectivo']]);
            $yearId=(int)$annual['id_ano_lectivo']; $counts['id_ano']=$yearId;
            // periodo usa una clave manual en instalaciones históricas de Guagua.
            $this->rows('SELECT id_periodo FROM periodo ORDER BY id_periodo DESC LIMIT 1 FOR UPDATE');
            $next=(int)($this->row('SELECT COALESCE(MAX(id_periodo),0) AS n FROM periodo')['n'])+1;
            $activeId=null;
            foreach ($p['periodos'] as $period) {
                $existing=$this->row('SELECT id_periodo FROM periodo WHERE ano_lectivo=? AND nombre_periodo=? ORDER BY estado_periodo DESC,id_periodo DESC LIMIT 1',[$year,$period['nombre']]);
                $id=$existing ? (int)$existing['id_periodo'] : $next++;
                if ($existing) guagua_sql($this->db,'UPDATE periodo SET descripcion_periodo=?,fecha_inicio=?,fecha_fin=? WHERE id_periodo=?',[$period['descripcion'] ?? '',$period['inicio'],$period['fin'],$id]);
                else guagua_sql($this->db,"INSERT INTO periodo (id_periodo,nombre_periodo,descripcion_periodo,fecha_inicio,fecha_fin,ano_lectivo,estado_periodo) VALUES (?,?,?,?,?,?,'0')",[$id,$period['nombre'],$period['descripcion'] ?? '',$period['inicio'],$period['fin'],$year]);
                if (!empty($period['activo'])) $activeId=$id;
            }
            $gradeMap=[];$subjectMap=[];
            foreach (['grados','materias'] as $type) foreach ($p[$type] as $item) {
                $id=(int)($item['id'] ?? 0);
                if (!$id) {
                    $table=$type==='grados'?'categoria_curso':'materia_oficial';$field=$type==='grados'?'id_categoria_curso':'id_materia';$name=$type==='grados'?'nombre_categoria_curso':'nombre_materia';
                    $existing=$this->row("SELECT $field AS id FROM $table WHERE $name=? LIMIT 1",[trim($item['nombre'])]);
                    if ($existing) $id=(int)$existing['id'];
                    elseif ($type==='grados') { guagua_sql($this->db,"INSERT INTO categoria_curso (nombre_categoria_curso,descripcion_categoria_curso,nivel_educativo) VALUES (?,'',?)",[trim($item['nombre']),$item['nivel']]);$id=$this->db->insert_id;$counts[$type]++; }
                    else { guagua_sql($this->db,'INSERT INTO materia_oficial (nombre_materia) VALUES (?)',[trim($item['nombre'])]);$id=$this->db->insert_id;$counts[$type]++; }
                }
                if ($type==='grados') $gradeMap[$item['key']]=$id; else $subjectMap[$item['key']]=$id;
            }
            $assignmentMap=[];
            foreach ($p['asignaciones'] as $a) {
                $grade=$gradeMap[$a['grado']];$subject=$subjectMap[$a['materia']];
                $existing=$this->row('SELECT id_asignacion FROM asignacion WHERE ano_lectivo=? AND institucion_educativa=? AND id_categoria_curso=? AND id_asignatura=? AND id_docente=? ORDER BY id_asignacion DESC LIMIT 1',[$yearId,$actor['id_institucion'],$grade,$subject,$a['docente']]);
                if ($existing) {
                    $id=(int)$existing['id_asignacion'];
                    guagua_sql($this->db,'UPDATE asignacion SET visible=? WHERE id_asignacion=?',[!empty($a['visible'])?'SI':'NO',$id]);
                }
                else {
                    guagua_sql($this->db,'INSERT INTO asignacion (institucion_educativa,id_curso,id_asignatura,id_docente,ano_lectivo,id_categoria_curso,visible) VALUES (?,?,?,?,?,?,?)',[$actor['id_institucion'],$subject,$subject,$a['docente'],$yearId,$grade,!empty($a['visible'])?'SI':'NO']);
                    $id=$this->db->insert_id;$counts['asignaciones']++;
                }
                $assignmentMap[$a['key']]=$id;
            }
            foreach ($p['horarios'] as $h) {
                $id=$assignmentMap[$h['asignacion']];
                $existing=$this->row('SELECT id_horario FROM horario WHERE id_asignacion=? AND dia=? AND hora_inicio=? AND hora_fin=? AND fecha_inicio=? AND fecha_fin=?',[$id,$h['dia'],$h['inicio'],$h['fin'],$h['desde'],$h['hasta']]);
                if ($existing) continue;
                $course=$this->row('SELECT id_docente,id_categoria_curso FROM asignacion WHERE id_asignacion=?',[$id]);
                $conflict=$this->row('SELECT h.id_horario FROM horario h JOIN asignacion a ON a.id_asignacion=h.id_asignacion WHERE a.ano_lectivo=? AND a.institucion_educativa=? AND (a.id_docente=? OR a.id_categoria_curso=?) AND h.dia=? AND h.fecha_inicio<=? AND h.fecha_fin>=? AND h.hora_inicio<? AND h.hora_fin>? LIMIT 1',[$yearId,$actor['id_institucion'],$course['id_docente'],$course['id_categoria_curso'],$h['dia'],$h['hasta'],$h['desde'],$h['fin'],$h['inicio']]);
                if ($conflict) throw new InvalidArgumentException('Un horario propuesto se cruza con un horario que ya existe en el año destino.');
                guagua_sql($this->db,'INSERT INTO horario (id_asignacion,fecha_inicio,fecha_fin,dia,hora_inicio,hora_fin) VALUES (?,?,?,?,?,?)',[$id,$h['desde'],$h['hasta'],$h['dia'],$h['inicio'],$h['fin']]);$counts['horarios']++;
            }
            foreach ($p['estudiantes'] as $student) foreach ($p['asignaciones'] as $a) {
                if ($a['grado']!==$student['grado']) continue;
                $id=$assignmentMap[$a['key']];
                if ($this->row('SELECT id_inscripcion FROM inscripcion WHERE id_asignacion=? AND id_estudiante=? LIMIT 1',[$id,$student['id']])) continue;
                guagua_sql($this->db,"INSERT INTO inscripcion (id_asignacion,id_estudiante,fecha_inscripcion,estado_inscripcion,fecha_retiro) VALUES (?,?,?,'En curso',NULL)",[$id,$student['id'],$p['fecha_inscripcion']]);$counts['inscripciones']++;
            }
            if ($activate) {
                guagua_sql($this->db,"UPDATE ano_lectivo SET estado='Inactivo' WHERE estado='Activo'");
                guagua_sql($this->db,"UPDATE ano_lectivo SET estado='Activo' WHERE id_ano_lectivo=?",[$yearId]);
                guagua_sql($this->db,"UPDATE periodo SET estado_periodo='0' WHERE estado_periodo='1'");
                guagua_sql($this->db,"UPDATE periodo SET estado_periodo='1' WHERE id_periodo=?",[$activeId]);
                $i=$p['institucion'];
                guagua_sql($this->db,'UPDATE institucion_educativa SET nombre_institucion=?,direccion_institucion=?,telefono_institucion=?,correo_institucion=?,formatos_no_permitidos=?,tamano_maximo_adjunto=?,autoregistrarse=?,plantilla=? WHERE id_institucion_educativa=?',[$i['nombre_institucion'],$i['direccion_institucion'] ?? '',$i['telefono_institucion'] ?? '',$i['correo_institucion'] ?? '',$i['formatos_no_permitidos'] ?? '',$i['tamano_maximo_adjunto'] ?? '',$i['autoregistrarse'],$i['plantilla'],$actor['id_institucion']]);
                if ($applyFiles) $restore=$applyFiles($p['archivos'] ?? [],$year);
            }
            guagua_sql($this->db,"UPDATE inicio_anual_borradores SET estado=?,resultado=?,revision=revision+1,paso=7 WHERE id=?",[$activate?'instalado':'preparado',json_encode($counts,JSON_UNESCAPED_UNICODE|JSON_THROW_ON_ERROR),$stored['id']]);
            if (!$this->db->commit()) throw new RuntimeException('No se pudo confirmar la instalación.');
            return $counts;
        } catch (Throwable $e) {
            $this->db->rollback();
            if (is_callable($restore)) $restore();
            throw $e;
        } finally { $this->row("SELECT RELEASE_LOCK('guagua.inicio_anual') AS ok"); }
    }
}
