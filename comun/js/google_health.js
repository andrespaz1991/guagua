(function () {
    'use strict';
    var root = document.getElementById('google-health-controls');
    if (!root) return;
    var connect = root.querySelector('[data-health-connect]');
    var sync = root.querySelector('[data-health-sync]');
    var status = root.querySelector('[data-health-status]');
    var setup = root.querySelector('[data-health-setup]');
    var disconnect=root.querySelector('[data-health-disconnect]');
    var connectionUrl=new URL('connection.php',new URL(root.dataset.endpoint,window.location.href));
    async function connection(action,body) {
        var url=new URL(connectionUrl);url.searchParams.set('action',action);
        var response=await fetch(url,{method:body ? 'POST':'GET',cache:'no-store',
            headers:{'Content-Type':'application/json','X-CSRF-Token':root.dataset.csrf,'X-Requested-With':'XMLHttpRequest'},
            body:body ? JSON.stringify(body):undefined});
        var data=await response.json();if(!response.ok) throw new Error(data.message || 'No se pudo acceder a la conexión guardada.');
        return data;
    }
    var prefix = 'https://www.googleapis.com/auth/googlehealth.';
    var scopes = ['activity_and_fitness.readonly','health_metrics_and_measurements.readonly','sleep.readonly'];
    var scope = scopes.map(function(s) {return prefix+s;}).join(' ');
    var granted = [];
    var connected=false, client, busy = false;
    async function run() {
        if (busy) return;
        if (!connected) { status.textContent = 'Conecta Google Health una vez para guardar la autorización.'; return; }
        busy = true; connect.disabled = sync.disabled = true;
        setup.hidden = true;
        status.textContent = 'Comprobando tu cuenta y consultando los pasos de Google Health…';
        try {
            var from = document.getElementById('from'), to = document.getElementById('to');
            function iso(d) {return d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0');}
            var end = new Date(); end.setHours(0,0,0,0);
            var start = new Date(end); start.setDate(start.getDate()-29);
            if (from && to) {start=new Date(from.value+'T00:00:00'); end=new Date(to.value+'T00:00:00');}
            if (isNaN(start) || isNaN(end) || start>end || (end-start)/86400000>89 || iso(end)>iso(new Date())) throw new Error('Selecciona un rango de hasta 90 días, sin fechas futuras.');
            var types = [
                ['steps',0,90],['distance',0,90],['active-energy-burned',0,90],['floors',0,90],['active-minutes',0,14],
                ['heart-rate',1,14],['weight',1,90],['body-fat',1,90],['sleep',2,90]
            ].filter(function(type) {return granted.indexOf(scopes[type[1]]) !== -1;});
            var received=0, completed=0, failures=[];
            for (var type of types) {
                for (var current=new Date(start); current<=end;) {
                    var until=new Date(current); until.setDate(until.getDate()+type[2]-1); if(until>end) until=new Date(end);
                    status.textContent='Consultando '+type[0]+' · '+iso(current)+' a '+iso(until)+'…';
                    var response = await fetch(root.dataset.endpoint, {
                        method:'POST', cache:'no-store', headers:{'Content-Type':'application/json','X-CSRF-Token':root.dataset.csrf},
                        body:JSON.stringify({from:iso(current),to:iso(until),type:type[0]}), signal:AbortSignal.timeout(65000)
                    });
                    var data=await response.json();
                    if (!response.ok) {
                        if (data.reason==='SERVICE_DISABLED' || data.reason==='PERMISSION_DENIED') setup.hidden=false;
                        if (data.reason==='UNAUTHENTICATED' || data.reason==='RECONNECT') {connected=false;sync.hidden=true;connect.hidden=false;}
                        if (['ACCOUNT_NOT_LINKED','SERVICE_DISABLED','UNAUTHENTICATED','RECONNECT'].indexOf(data.reason)!==-1) throw new Error(data.message);
                        failures.push(type[0]+': '+(data.message || 'consulta no disponible')); break;
                    }
                    received+=data.days; completed++;
                    current=new Date(until); current.setDate(current.getDate()+1);
                }
            }
            status.textContent=received ? 'Datos actualizados. Abre el historial para ver todas las medidas recibidas.' : 'Google no devolvió registros para las métricas autorizadas en este periodo.';
            if (granted.length<scopes.length) status.textContent+=' Algunos permisos no se concedieron; puedes conectar otra vez para añadirlos.';
            if (failures.length) status.textContent+=' Consultas incompletas: '+failures.join(' · ');
            if (completed) {
                var source = document.getElementById('source');
                if (source) source.value = 'google';
                document.dispatchEvent(new Event('guagua-health-updated'));
            }
        } catch(error) {
            status.textContent = error.name==='TypeError' || error.name==='TimeoutError' ? 'No se pudo completar la consulta. Los datos ya recibidos se conservan; intenta sincronizar de nuevo.' : error.message;
            document.dispatchEvent(new Event('guagua-health-updated'));
        } finally { busy = false; connect.disabled = sync.disabled = false; }
    }
    function initialize() {
        client = google.accounts.oauth2.initCodeClient({
            client_id:root.dataset.clientId, scope:scope, include_granted_scopes:false,
            ux_mode:'popup',
            callback:async function(result) {
                connect.disabled = false;
                if (result.error || !result.code) {
                    status.textContent = 'No se concedió el permiso de lectura de actividad. Puedes intentar conectar otra vez.';
                    return;
                }
                connect.disabled=true;
                try {
                    var saved=await connection('connect',{code:result.code});
                    connected=saved.connected;granted=scopes.filter(function(s) {return saved.scopes.indexOf(prefix+s)!==-1;});
                    connect.hidden=true;sync.hidden=disconnect.hidden=false;await run();
                } catch(error) {status.textContent=error.message;}
                finally {connect.disabled=false;}
            },
            error_callback:function() { connect.disabled = false; status.textContent = 'La ventana de Google se cerró o no pudo abrirse. Intenta conectar otra vez.'; }
        });
        connect.disabled = false;
    }
    if (!root.dataset.clientId) { status.textContent = 'Falta configurar el identificador de Google en Guagua.'; return; }
    if (window.google && google.accounts && google.accounts.oauth2) initialize();
    else {
        var script = document.createElement('script'); script.src = 'https://accounts.google.com/gsi/client'; script.async = true;
        script.onload = initialize;
        script.onerror = function() { status.textContent = 'No se pudo cargar Google. Recarga la página para volver a intentar.'; };
        document.head.appendChild(script);
    }
    connect.addEventListener('click', function() {
        connect.disabled = true; status.textContent = 'Esperando autorización de Google…';
        try { client.requestCode(); }
        catch(error) { connect.disabled = false; status.textContent = 'No se pudo abrir Google. Permite las ventanas emergentes e intenta de nuevo.'; }
    });
    sync.addEventListener('click',run);
    disconnect.addEventListener('click',async function() {
        disconnect.disabled=true;
        try {await connection('disconnect',{});connected=false;connect.hidden=false;sync.hidden=disconnect.hidden=true;status.textContent='Google desconectado. Tu historial se conserva.';}
        catch(error) {status.textContent=error.message;}
        finally {disconnect.disabled=false;}
    });
    connection('status').then(function(saved) {
        if(saved.connected) {
            connected=true;granted=scopes.filter(function(s) {return saved.scopes.indexOf(prefix+s)!==-1;});
            connect.hidden=true;sync.hidden=disconnect.hidden=false;run();
        } else if(!saved.configured) status.textContent='Falta configurar el secreto del cliente de Google para recordar la conexión.';
    }).catch(function(error) {status.textContent=error.message;});
}());
