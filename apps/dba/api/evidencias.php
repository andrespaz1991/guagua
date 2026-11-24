<?php
ini_set('display_errors', '0');
require_once __DIR__ . '/../../../comun/conexion.php';
guagua_exigir_rol(guagua_roles_gestion_curricular());
require_once __DIR__ . '/../lib/evidencias.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json; charset=utf-8');

function ev_responder(array $data, int $code=200): void {
    http_response_code($code); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE); exit;
}
function ev_id($id): int {
    if (filter_var($id, FILTER_VALIDATE_INT, ['options'=>['min_range'=>1]]) === false) throw new InvalidArgumentException('Identificador no válido.');
    return (int)$id;
}
$transaction=false;
try {
    $method=$_SERVER['REQUEST_METHOD'];
    $data=[];
    if ($method === 'POST') {
        guagua_exigir_csrf();
        $data = isset($_FILES['archivo']) ? $_POST : json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) throw new InvalidArgumentException('La solicitud no contiene datos válidos.');
    } elseif ($method !== 'GET') {
        ev_responder(['status'=>'error','message'=>'Método no admitido.'],405);
    }
    $action=$data['action'] ?? $_GET['action'] ?? '';
    if ($method === 'GET' && $action === 'models') {
        session_write_close();
        $result=dba_lm_request('models');
        $models=array_values(array_filter(array_column($result['data'] ?? [],'id'), static fn($m)=>is_string($m) && !preg_match('/embed|moondream/i',$m)));
        ev_responder(['status'=>'success','models'=>$models,'server'=>getenv('GUAGUA_LM_STUDIO_URL') ?: 'http://169.254.173.61:1234']);
    }
    if ($method === 'GET' && $action === 'template') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="plantilla_evidencias_dba.csv"');
        $out=fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
        fputcsv($out,['id_dba','dba_referencia','evidencia','palabras_clave'],';');
        $rows=$mysqli->query('SELECT id_dba,nombre_dba,palabras_clave FROM dba ORDER BY id_dba');
        while($row=$rows->fetch_assoc()) {
            $safe=static fn($s)=>preg_match('/^[=+@\-\t\r]/u',(string)$s) ? "'".$s : $s;
            fputcsv($out,[$row['id_dba'],$safe($row['nombre_dba']),'',$safe($row['palabras_clave'] ?? '')],';');
        }
        fclose($out); exit;
    }
    if ($method !== 'POST') ev_responder(['status'=>'error','message'=>'Operación no válida.'],400);
    if ($action === 'generate') {
        $id=ev_id($data['id_dba'] ?? 0);
        session_write_close(); set_time_limit(160);
        ev_responder(['status'=>'success','proposal'=>dba_generar($mysqli,$id,dba_texto($data['modelo'] ?? '',150),(int)($data['cantidad'] ?? 1))]);
    }
    if ($action === 'preview_import') {
        $file=$_FILES['archivo'] ?? null;
        if (!$file || $file['error']!==UPLOAD_ERR_OK || $file['size']<1 || $file['size']>5*1024*1024) throw new InvalidArgumentException('Seleccione un archivo de entre 1 byte y 5 MB.');
        $ext=strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));
        $raw=file_get_contents($file['tmp_name']);
        $raw=preg_replace('/^\xEF\xBB\xBF/','',$raw);
        if (!mb_check_encoding($raw,'UTF-8')) throw new InvalidArgumentException('Guarde el archivo con codificación UTF-8.');
        if ($ext === 'json') {
            $rows=json_decode($raw,true);
            $rows=$rows['registros'] ?? $rows;
            if (!is_array($rows) || array_keys($rows)!==range(0,count($rows)-1)) throw new InvalidArgumentException('El JSON debe ser una lista de registros.');
        } elseif (in_array($ext,['csv','txt'],true)) {
            $handle=fopen('php://temp','r+'); fwrite($handle,$raw); rewind($handle);
            $first=strtok($raw,"\r\n"); $scores=[';'=>substr_count($first,';'),','=>substr_count($first,','),"\t"=>substr_count($first,"\t")]; arsort($scores); $sep=array_key_first($scores);
            $headers=fgetcsv($handle,0,$sep);
            if (!$headers) throw new InvalidArgumentException('Faltan encabezados.');
            $headers=array_map(static fn($h)=>mb_strtolower(trim($h)),$headers);
            if (!in_array('id_dba',$headers,true) || !in_array('evidencia',$headers,true) || count(array_unique($headers))!==count($headers)) throw new InvalidArgumentException('Use los encabezados id_dba y evidencia, sin columnas duplicadas.');
            $rows=[];
            while(($values=fgetcsv($handle,0,$sep))!==false) {
                if(count($values)===1 && trim((string)$values[0])==='') continue;
                if(count($values)!==count($headers)) throw new InvalidArgumentException('La fila '.(count($rows)+2).' tiene columnas incompletas.');
                $rows[]=array_combine($headers,$values);
                if(count($rows)>1000) throw new InvalidArgumentException('Importe como máximo 1.000 filas por archivo.');
            } fclose($handle);
        } else throw new InvalidArgumentException('Formato no admitido. Use CSV, TXT o JSON.');
        if(count($rows)>1000) throw new InvalidArgumentException('Importe como máximo 1.000 filas por archivo.');
        $valid=[]; $errors=[]; $empty=0; $seen=[];
        foreach($rows as $index=>$row) {
            try {
                if(!is_array($row)) throw new InvalidArgumentException('La fila debe ser un objeto.');
                $text=dba_texto($row['evidencia'] ?? '');
                if($text==='') { $empty++; continue; }
                $id=ev_id($row['id_dba'] ?? 0); $dba=dba_contexto($mysqli,$id);
                $key=$id.':'.mb_strtolower($text);
                if(isset($seen[$key])) continue;
                $seen[$key]=true;
                $valid[]=['id_dba'=>$id,'evidencia'=>$text,'palabras_clave'=>dba_palabras($row['palabras_clave'] ?? ''),'nombre_dba'=>$dba['nombre_dba']];
            } catch(InvalidArgumentException $e) { $errors[]='Fila '.($index+2).': '.$e->getMessage(); }
        }
        ev_responder(['status'=>'success','rows'=>$valid,'errors'=>$errors,'empty'=>$empty]);
    }
    $mysqli->begin_transaction(); $transaction=true;
    if ($action === 'save_batch') {
        $rows=$data['rows'] ?? [];
        if(!is_array($rows) || count($rows)<1 || count($rows)>1000) throw new InvalidArgumentException('Seleccione entre 1 y 1.000 evidencias.');
        $source=($data['source'] ?? '')==='ia' ? 'ia_pendiente' : 'importada';
        $model=$source==='ia_pendiente' ? dba_texto($data['modelo'] ?? '',150) : '';
        $created=0; $skipped=0;
        foreach($rows as $row) {
            if(!is_array($row)) throw new InvalidArgumentException('Registro no válido.');
            $id=ev_id($row['id_dba'] ?? 0);
            $rowModel=$source==='ia_pendiente' ? dba_texto($row['modelo'] ?? $model,150) : '';
            $saved=dba_guardar_evidencia($mysqli,$id,dba_texto($row['evidencia'] ?? ''),$source,$rowModel);
            $saved ? $created++ : $skipped++;
            $keywords=dba_palabras($row['palabras_clave'] ?? '');
            if($keywords!=='') {
                // Las sugerencias completan campos vacíos. La edición manual es explícita.
                $s=$mysqli->prepare("UPDATE dba SET palabras_clave=? WHERE id_dba=? AND TRIM(COALESCE(palabras_clave,''))=''");
                $s->bind_param('si',$keywords,$id); $s->execute();
            }
        }
        $message="$created evidencias guardadas; $skipped duplicadas omitidas.";
    } elseif($action==='save') {
        $id=ev_id($data['id_dba'] ?? 0); dba_contexto($mysqli,$id);
        $text=dba_texto($data['evidencia'] ?? '');
        if($text==='') throw new InvalidArgumentException('Escriba la evidencia de aprendizaje.');
        $evidenceId=(int)($data['id_evidencia'] ?? 0);
        if($evidenceId) {
            $s=$mysqli->prepare('SELECT id_evidencia_aprendizaje FROM evidencia_de_aprendizaje WHERE id_evidencia_aprendizaje=? AND id_dba=? FOR UPDATE');
            $s->bind_param('ii',$evidenceId,$id); $s->execute();
            if(!$s->get_result()->fetch_assoc()) throw new InvalidArgumentException('La evidencia ya no existe. Actualice la vista.');
            $s=$mysqli->prepare("UPDATE evidencia_de_aprendizaje SET descripcion_evidencia=?, origen=IF(origen LIKE 'ia_%','ia_revisada',origen) WHERE id_evidencia_aprendizaje=? AND id_dba=?");
            $s->bind_param('sii',$text,$evidenceId,$id); $s->execute();
        } else dba_guardar_evidencia($mysqli,$id,$text);
        $message='Evidencia guardada.';
    } elseif($action==='delete') {
        $id=ev_id($data['id_evidencia'] ?? 0);
        $s=$mysqli->prepare('DELETE FROM evidencia_de_aprendizaje WHERE id_evidencia_aprendizaje=?'); $s->bind_param('i',$id); $s->execute();
        if(!$s->affected_rows) throw new InvalidArgumentException('La evidencia ya no existe.');
        $message='Evidencia eliminada.';
    } else throw new InvalidArgumentException('Operación no válida.');
    $mysqli->commit(); $transaction=false;
    ev_responder(['status'=>'success','message'=>$message]);
} catch(Throwable $e) {
    if($transaction) $mysqli->rollback();
    $validation=$e instanceof InvalidArgumentException;
    if(!$validation) error_log('DBA evidencias: '.$e->getMessage());
    ev_responder(['status'=>'error','message'=>$validation || $e instanceof RuntimeException && !($e instanceof mysqli_sql_exception) ? $e->getMessage() : 'No fue posible completar la operación. Actualice e intente de nuevo.'],$validation ? 422 : 502);
}
