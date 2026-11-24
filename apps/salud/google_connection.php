<?php
require_once __DIR__ . '/google_health.php';

/** Tokens cifrados por usuario; el navegador solo recibe el estado y los permisos. */
final class GuaguaHealthConnection
{
    private $transport;
    public function __construct(private PDO $db, private string $clientId, private string $secret, private string $key, ?callable $transport = null)
    {
        if (!preg_match('/\A[a-f0-9]{64}\z/', $key)) throw new RuntimeException('Falta la clave de cifrado de Google Health.');
        $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        $db->exec('PRAGMA busy_timeout=5000');
        $db->exec('CREATE TABLE IF NOT EXISTS google_connections (owner TEXT PRIMARY KEY, encrypted TEXT NOT NULL)');
        $this->transport=$transport;
    }
    public function configured(): bool { return $this->clientId !== '' && $this->secret !== ''; }
    private function request(string $url, array $fields): array
    {
        if ($this->transport) return ($this->transport)($url,$fields);
        $ch=curl_init($url);
        curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>http_build_query($fields),
            CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_CONNECTTIMEOUT=>8,CURLOPT_TIMEOUT=>25,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS]);
        $raw=curl_exec($ch);$status=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);
        if ($raw===false) throw new GuaguaGoogleHealthError('NETWORK','No se pudo renovar la conexión con Google. Intenta nuevamente.',502);
        if ($url==='https://oauth2.googleapis.com/revoke' && $status===200) return [];
        $data=json_decode($raw,true);
        if (!is_array($data)) throw new GuaguaGoogleHealthError('RESPONSE','Google devolvió una respuesta no válida.',502);
        if ($status>=400 || isset($data['error'])) {
            if (($data['error'] ?? '')==='invalid_grant') throw new GuaguaGoogleHealthError('RECONNECT','Google retiró o venció la autorización guardada. Conecta tu cuenta una vez más.',401);
            if (($data['error'] ?? '')==='invalid_client') throw new GuaguaGoogleHealthError('CONFIGURATION','Google no aceptó las credenciales de la aplicación. Revisa el cliente OAuth.',400);
            throw new GuaguaGoogleHealthError('OAUTH_ERROR','Google no pudo completar la autorización (HTTP '.$status.').',502);
        }
        return $data;
    }
    private function read(string $owner): ?array
    {
        $q=$this->db->prepare('SELECT encrypted FROM google_connections WHERE owner=?');$q->execute([$owner]);
        $value=$q->fetchColumn();if ($value===false) return null;
        $raw=base64_decode($value,true);
        if ($raw===false || strlen($raw)<29) throw new RuntimeException('Formato de conexión inválido.');
        $plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',hex2bin($this->key),OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16),$owner.'|'.$this->clientId);
        if ($plain===false) throw new RuntimeException('No se pudo descifrar la conexión.');
        return json_decode($plain,true,16,JSON_THROW_ON_ERROR);
    }
    private function save(string $owner, array $data): void
    {
        $iv=random_bytes(12);$tag='';
        $encrypted=openssl_encrypt(json_encode($data,JSON_THROW_ON_ERROR),'aes-256-gcm',hex2bin($this->key),OPENSSL_RAW_DATA,$iv,$tag,$owner.'|'.$this->clientId);
        if ($encrypted===false) throw new RuntimeException('No se pudo cifrar la conexión.');
        $q=$this->db->prepare('INSERT INTO google_connections(owner,encrypted) VALUES(?,?) ON CONFLICT(owner) DO UPDATE SET encrypted=excluded.encrypted');
        $q->execute([$owner,base64_encode($iv.$tag.$encrypted)]);
    }
    public function status(string $owner): array
    {
        $data=$this->read($owner);
        return ['configured'=>$this->configured(),'connected'=>$data!==null,'scopes'=>$data['scopes'] ?? []];
    }
    public function connect(string $owner, string $code, string $origin): array
    {
        if (!$this->configured()) throw new GuaguaGoogleHealthError('CONFIGURATION','Falta configurar el secreto del cliente de Google.',400);
        $data=$this->request('https://oauth2.googleapis.com/token',[
            'client_id'=>$this->clientId,'client_secret'=>$this->secret,'code'=>$code,'redirect_uri'=>$origin,'grant_type'=>'authorization_code'
        ]);
        if (empty($data['access_token']) || empty($data['refresh_token'])) throw new GuaguaGoogleHealthError('NO_REFRESH_TOKEN','Google no entregó acceso persistente. Retira el permiso de Guagua en tu cuenta de Google y conecta de nuevo.',400);
        $scopes=preg_split('/\s+/',trim((string)($data['scope'] ?? ''))) ?: [];
        $calendar=str_starts_with($owner,'calendar:');
        $health=array_filter($scopes,fn($s)=>$calendar ? $s==='https://www.googleapis.com/auth/calendar.events.readonly' : str_starts_with($s,'https://www.googleapis.com/auth/googlehealth.'));
        if (!$health) throw new GuaguaGoogleHealthError('PERMISSION_DENIED','No se concedieron los permisos solicitados.',403);
        $this->save($owner,['access_token'=>$data['access_token'],'refresh_token'=>$data['refresh_token'],
            'expires'=>time()+max(0,(int)($data['expires_in'] ?? 0)), 'scopes'=>array_values($health)]);
        return $this->status($owner);
    }
    public function accessToken(string $owner, bool $force = false): string
    {
        $data=$this->read($owner);
        if (!$data) throw new GuaguaGoogleHealthError('RECONNECT','Conecta Google Health para guardar la autorización.',401);
        if (!$force && $data['expires']>time()+60) return $data['access_token'];
        try {
            $new=$this->request('https://oauth2.googleapis.com/token',['client_id'=>$this->clientId,'client_secret'=>$this->secret,
                'refresh_token'=>$data['refresh_token'],'grant_type'=>'refresh_token']);
        } catch (GuaguaGoogleHealthError $e) {
            if ($e->reason==='RECONNECT') $this->forget($owner);
            throw $e;
        }
        if (empty($new['access_token'])) throw new GuaguaGoogleHealthError('RESPONSE','Google no entregó una autorización renovada.',502);
        $data['access_token']=$new['access_token'];$data['expires']=time()+max(0,(int)($new['expires_in'] ?? 0));
        if (!empty($new['refresh_token'])) $data['refresh_token']=$new['refresh_token'];
        $this->save($owner,$data);
        return $data['access_token'];
    }
    private function forget(string $owner): void
    {
        $q=$this->db->prepare('DELETE FROM google_connections WHERE owner=?');$q->execute([$owner]);
    }
    public function disconnect(string $owner): void
    {
        $data=$this->read($owner);
        if ($data) $this->request('https://oauth2.googleapis.com/revoke',['token'=>$data['refresh_token']]);
        $this->forget($owner);
    }
}

function guagua_health_connection(): GuaguaHealthConnection
{
    return new GuaguaHealthConnection(new PDO('sqlite:'.__DIR__.'/data/salud.sqlite'),
        trim((string)GuaguaDatabase::environment('GUAGUA_GOOGLE_CLIENT_ID')),
        trim((string)GuaguaDatabase::environment('GUAGUA_GOOGLE_CLIENT_SECRET')),
        trim((string)GuaguaDatabase::environment('GUAGUA_HEALTH_TOKEN_KEY')));
}
