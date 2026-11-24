const fs = require('fs');
const vm = require('vm');
const ws = new WebSocket('ws://127.0.0.1:53051/devtools/page/1F6518CD38C2C2625289D24C0C9D7AAE');
let id = 0;
const pending = new Map();
ws.onmessage = event => {
    const response = JSON.parse(event.data);
    if (!response.id) return;
    const request = pending.get(response.id);
    if (!request) return;
    pending.delete(response.id);
    if (response.error || response.result?.exceptionDetails) request.reject(new Error(JSON.stringify(response.error || response.result.exceptionDetails)));
    else request.resolve(response.result.result.value);
};
const evaluate = (fn, ...args) => new Promise((resolve,reject) => {
    const requestId = ++id;
    pending.set(requestId, {resolve,reject});
    ws.send(JSON.stringify({id:requestId,method:'Runtime.evaluate',params:{expression:'('+fn.toString()+')(...'+JSON.stringify(args)+')',awaitPromise:true,returnByValue:true}}));
});
ws.onopen = async () => {
    try {
        const source = fs.readFileSync('tmp/diario-server-reparado.js','utf8');
        const begin = source.indexOf('function hasExtractableMedia(');
        const end = source.indexOf('async function initDatabase', begin);
        // Extract only the helpers; never initialize a second WhatsApp client.
        const stop = source.indexOf('\n//', source.indexOf('throw new Error(`No se pudo descargar el archivo.',begin));
        const client = {pupPage:{evaluate}};
        const context = {client,require: name => require('D:/xampp/htdocs/wsp/node_modules/'+name),MAX_MESSAGES_PER_CHAT:2000,setTimeout,clearTimeout,parseErrorMessage:e=>e.message};
        vm.createContext(context);
        const helpers = source.slice(begin, stop > 0 ? stop : end);
        vm.runInContext('function getSerializedMessageId(m){return m.id._serialized;}\n'+helpers,context);
        const samples = await evaluate(() => {
            const a = window.require('WAWebCollections').Msg.getModelsArray();
            return ['ptt','image'].map(type => a.find(m=>m.type===type&&m.mediaData&&m.directPath)).filter(Boolean).map(m=>({id:{_serialized:m.id.toString()},type:m.type,hasMedia:true,chatId:m.id.remote.toString(),timestamp:m.t}));
        });
        for (const sample of samples) {
            const history = await context.readExtractionHistory({id:{_serialized:sample.chatId}},sample.timestamp-1,sample.timestamp+1);
            const media = await context.safeDownloadMedia(client,sample,1);
            const bytes = Buffer.from(media.data,'base64');
            if (!bytes.length || !history.messages.length) throw new Error('Descarga o historial vacío');
            console.log(JSON.stringify({type:sample.type,mime:media.mimetype,bytes:bytes.length,historyMessages:history.messages.length,warning:history.warning}));
        }
        ws.close(); process.exit(0);
    } catch (error) {console.error(error.message);ws.close();process.exit(1);}
};
setTimeout(()=>process.exit(2),55000);
