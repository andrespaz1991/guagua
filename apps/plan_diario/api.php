<?php
declare(strict_types=1);
require_once __DIR__.'/../../comun/seguridad.php';
guagua_iniciar_sesion_segura();
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
if (empty($_SESSION['id_usuario'])) guagua_error_acceso(401,'Inicia sesión en Guagua para abrir tu plan.');
if (!in_array($_SERVER['REQUEST_METHOD'],['GET','POST'],true)) guagua_error_acceso(405,'Método no permitido.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') guagua_exigir_csrf();
$owner = (string)$_SESSION['id_usuario'];
$institution = (int)($_SESSION['id_institucion'] ?? $_SESSION['institucion'] ?? 0);
session_abort();
require_once __DIR__.'/../../comun/database.php';
require_once __DIR__.'/database.php';
require_once __DIR__.'/lib/SchoolSchedule.php';
try {
    date_default_timezone_set(GuaguaDatabase::settings()['timezone']);
    $input = [];
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $raw = file_get_contents('php://input',false,null,0,500001);
        if (strlen($raw) > 500000) guagua_error_acceso(413,'La solicitud es demasiado grande.');
        $input = json_decode($raw,true,32,JSON_THROW_ON_ERROR);
        if (!is_array($input) || !is_string($input['action'] ?? null)) throw new InvalidArgumentException('Solicitud no válida.');
    }
    $date = DailyPlanner::date($input['date'] ?? $_GET['date'] ?? date('Y-m-d'));
    if (($input['view'] ?? $_GET['view'] ?? null) === 'home') {
        require_once __DIR__.'/lib/Today.php';
        $db = daily_planner_database();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($input['action'] !== 'complete') throw new InvalidArgumentException('Desde Inicio solo puedes marcar las tareas de la fecha elegida.');
            (new DailyPlanner($db,$owner))->run($date,'complete',$input);
        }
        echo json_encode(['ok'=>true,'data'=>DailyPlannerToday::load($db,$owner,$date)],JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['report'] ?? null) === 'compliance') {
        require_once __DIR__.'/lib/Compliance.php';
        $result = DailyCompliance::load(daily_planner_database(),$owner,$_GET['from'] ?? $date,$_GET['to'] ?? $date,date('Y-m-d'));
        echo json_encode(['ok'=>true,'data'=>$result],JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        exit;
    }
    try {
        $schedule = $institution > 0
            ? DailySchoolSchedule::load(guagua_db_pdo(),$owner,$institution,$date)
            : DailySchoolSchedule::personal($date,'No hay una institución seleccionada. Puedes organizar tus tareas en Personal.');
    } catch (Throwable $error) {
        error_log('Plan diario horario: '.$error->getMessage());
        throw new RuntimeException('No se pudo consultar tu horario escolar. Intenta nuevamente; tus tareas guardadas se conservan.',503,$error);
    }
    $db = daily_planner_database();
    $planner = new DailyPlanner($db,$owner,$schedule);
    $result = $planner->run($date,$input['action'] ?? null,$input);
    echo json_encode(['ok'=>true,'data'=>$result],JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
} catch (InvalidArgumentException | JsonException $error) {
    http_response_code(422); echo json_encode(['message'=>$error->getMessage()],JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    $conflict = $error->getCode() === 409;
    $scheduleError = $error->getCode() === 503;
    http_response_code($conflict ? 409 : ($scheduleError ? 503 : 500));
    if (!$conflict) error_log('Plan diario: '.$error->getMessage());
    echo json_encode(['message'=>($conflict || $scheduleError) ? $error->getMessage() : 'No se pudo conectar o guardar tu plan. Intenta nuevamente.'],JSON_UNESCAPED_UNICODE);
}
