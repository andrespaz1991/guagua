<?php
// Reproduce el render completo del reporte y el menú con dependencias aisladas.
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$root=dirname(__DIR__);
$fixture=sys_get_temp_dir().'/guagua-cabecera-'.bin2hex(random_bytes(8));
$directories=[$fixture,$fixture.'/comun',$fixture.'/apps',$fixture.'/apps/reporte_actividades'];
$files=[];
foreach ($directories as $directory) mkdir($directory);
$write=static function(string $path,string $content) use ($fixture,&$files): void {
    file_put_contents($fixture.'/'.$path,$content); $files[]=$fixture.'/'.$path;
};
try {
    foreach (['cabecera_modulo.php','vista_modulo.php','menu.php'] as $file) $write('comun/'.$file,file_get_contents($root.'/comun/'.$file));
    $write('apps/reporte_actividades/index.php',file_get_contents($root.'/apps/reporte_actividades/index.php'));
    $write('comun/seguridad.php','<?php function guagua_iniciar_sesion_segura(): void {} function guagua_exigir_rol(array $roles): void {}');
    $write('comun/config.php','<?php foreach (["SGA_URL","SGA_COMUN_URL","SGA_COMUN_IMAGES","SGA_REPORTES_URL","SGA_USUARIO_URL"] as $name) define($name,"/guagua");');
    $write('comun/funciones.php','<?php');
    $write('comun/autoload.php','<?php');
    $write('comun/menu_account.php','');
    $write('comun/database.php', <<<'PHP'
<?php
// Solo prueba de render: ninguna conexión ni sesión real.
function guagua_db_request() {
    return new class {
        public function query($sql) {
            if (!str_contains($sql,'menu_items2')) throw new RuntimeException('Consulta inesperada');
            $GLOBALS['header_queries']=($GLOBALS['header_queries'] ?? 0)+1;
            return new class {
                public $num_rows=1;
                private $consumed=false;
                public function fetch_assoc() {
                    if ($this->consumed) return null;
                    $this->consumed=true;
                    return ['menu_item_id'=>120,'menu_item_name'=>'Reporte de actividades','url_target'=>'_self','icono'=>'icon-sga-app'];
                }
            };
        }
    };
}
PHP);
    // El CSS del menú se referencia con filemtime durante el head del reporte.
    mkdir($fixture.'/comun/css'); $directories[]=$fixture.'/comun/css';
    mkdir($fixture.'/comun/img'); $directories[]=$fixture.'/comun/img';
    mkdir($fixture.'/comun/img/png'); $directories[]=$fixture.'/comun/img/png';
    $write('comun/css/estilos_guagua.css',''); $write('comun/css/menu_account.css',''); $write('comun/img/png/icon-sga.php','');
    $probe= <<<'PHP'
$_SESSION=[];
$_SERVER=['SCRIPT_NAME'=>'/guagua/apps/reporte_actividades/index.php','HTTP_HOST'=>'localhost','DOCUMENT_ROOT'=>$argv[1]];
$_GET=$argv[2]==='embedded' ? ['guagua_embedded'=>'1'] : [];
ob_start();
require $argv[1].'/apps/reporte_actividades/index.php';
$html=ob_get_clean();
if (!str_contains($html,'id="report"') || !str_contains($html,'id="student-dialog"') || !str_contains($html,'</html>')) throw new RuntimeException('El render se interrumpió después del menú.');
$queries=$GLOBALS['header_queries'] ?? 0;
if ($queries!==($argv[2]==='embedded' ? 0 : 1)) throw new RuntimeException('Conexión del menú incorrecta.');
echo 'OK';
PHP;
    foreach (['direct','embedded'] as $mode) {
        $process=proc_open([PHP_BINARY,'-r',$probe,$fixture,$mode],[1=>['pipe','w'],2=>['pipe','w']],$pipes,$root);
        if (!is_resource($process)) throw new RuntimeException('No se pudo ejecutar el render.');
        $output=stream_get_contents($pipes[1]); fclose($pipes[1]);
        $error=stream_get_contents($pipes[2]); fclose($pipes[2]);
        $code=proc_close($process);
        if ($code!==0 || $error!=='' || $output!=='OK') throw new RuntimeException('Fallo en '.$mode.': '.$output.' '.$error);
    }
    echo "OK: reporte completo con menú real, conexión en ámbito local y modo integrado sin consultas adicionales.\n";
} finally {
    foreach (array_reverse($files) as $file) if (is_file($file)) unlink($file);
    foreach (array_reverse($directories) as $directory) rmdir($directory);
}
