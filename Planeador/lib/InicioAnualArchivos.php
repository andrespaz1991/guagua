<?php
final class InicioAnualArchivos
{
    private string $attendance;
    private string $reports;
    private string $environmentPath;
    public function __construct(string $attendance,string $reports,string $environmentPath='')
    { $this->attendance=$attendance;$this->reports=$reports;$this->environmentPath=$environmentPath; }
    private function read(string $path): array
    {
        if (!is_file($path)) return [];
        $data=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
        if (!is_array($data)) throw new RuntimeException('Un archivo de configuración no es válido.');
        return $data;
    }
    private function hash(string $path): string { return is_file($path) ? hash_file('sha256',$path) : ''; }
    public function current(array $groups): array
    {
        $attendance=$this->read($this->attendance);$reports=$this->read($this->reports);$rows=[];
        foreach ($groups as $group) $rows[]=['id'=>(int)$group['id'],'nombre'=>$group['nombre'], 'local'=>$reports[$group['id']]['local'] ?? $group['ruta_excel'], 'web'=>$reports[$group['id']]['web'] ?? ''];
        return ['asistencia'=>$this->environmentPath ?: ($attendance['ruta'] ?? ''),'reportes'=>$rows,
            'asistencia_entorno'=>$this->environmentPath!=='','huellas'=>['asistencia'=>$this->hash($this->attendance),'reportes'=>$this->hash($this->reports)]];
    }
    public function apply(array $options,int $year): callable
    {
        foreach (['asistencia'=>$this->attendance,'reportes'=>$this->reports] as $key=>$path) {
            if (($options['huellas'][$key] ?? null)!==$this->hash($path)) throw new InvalidArgumentException('Las rutas guardadas cambiaron mientras preparabas el año. Vuelve al paso Archivos y actualízalas antes de instalar.');
        }
        if ($this->environmentPath!=='' && ($options['asistencia'] ?? '')!==$this->environmentPath) throw new InvalidArgumentException('La ruta de asistencia se administra desde la configuración del servidor.');
        $snapshots=[];
        foreach ([$this->attendance,$this->reports] as $path) $snapshots[$path]=is_file($path)?file_get_contents($path):null;
        $restore=static function() use ($snapshots) {
            foreach ($snapshots as $path=>$contents) {
                if ($contents===null) { if (is_file($path)) unlink($path); }
                else file_put_contents($path,$contents,LOCK_EX);
            }
        };
        try {
            $attendance=$this->read($this->attendance);$attendance['ruta']=trim((string)($options['asistencia'] ?? ''));$attendance['anio']=$year;
            $reports=$this->read($this->reports);
            foreach ($options['reportes'] ?? [] as $row) {
                $id=(int)($row['id'] ?? 0);$web=trim((string)($row['web'] ?? ''));
                if ($id<=0 || ($web!=='' && (!filter_var($web,FILTER_VALIDATE_URL) || !in_array(strtolower((string)parse_url($web,PHP_URL_SCHEME)),['http','https'],true)))) throw new InvalidArgumentException('Revisa las rutas y enlaces de los reportes.');
                $reports[$id]=['local'=>trim((string)($row['local'] ?? '')),'web'=>$web];
            }
            foreach ([$this->attendance=>$attendance,$this->reports=>$reports] as $path=>$values) {
                if (file_put_contents($path,json_encode($values,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR),LOCK_EX)===false) throw new RuntimeException('No fue posible guardar las rutas configuradas.');
            }
            return $restore;
        } catch (Throwable $e) { $restore();throw $e; }
    }
}
