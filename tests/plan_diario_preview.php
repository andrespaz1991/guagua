<?php
/** Local browser QA only, with an isolated synthetic session. */
declare(strict_types=1);
if (PHP_SAPI !== 'cli-server' || !in_array($_SERVER['REMOTE_ADDR'] ?? '',['127.0.0.1','::1'],true)) { http_response_code(404); exit; }
$root = dirname(__DIR__);
$path = parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);
$file = match ($path) { '/','/index.php'=>'index.php', '/api.php'=>'api.php', '/app.js'=>'app.js', '/style.css'=>'style.css', default=>null };
if (!$file) { http_response_code(404); exit; }
if (in_array($file,['app.js','style.css'],true)) { header('Content-Type: '.($file === 'app.js' ? 'application/javascript' : 'text/css')); readfile($root.'/apps/plan_diario/'.$file); exit; }
$sessions = $root.'/tmp/plan-diario-sessions';
if (!is_dir($sessions)) mkdir($sessions,0700,true);
ini_set('session.save_path',$sessions);
ini_set('session.use_strict_mode','0');
ini_set('session.gc_probability','0');
session_name('guagua_session');
session_id('plan-diario-local-qa');
session_start();
$_SESSION['id_usuario'] = '__plan_diario_qa__';
$_SESSION['guagua_csrf'] = 'local-qa-token';
require $root.'/apps/plan_diario/'.$file;
