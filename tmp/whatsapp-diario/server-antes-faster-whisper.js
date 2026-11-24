/**
 * SISTEMA DE RESUMEN DE WHATSAPP - BACKEND CON PERSISTENCIA MYSQL / CONFIG LOCAL
 * Autor: Andres Paz (IER Josefina)
 * Servidor Express + Socket.IO + WhatsApp-Web.js + OpenAI SDK (LMStudio)
 * 
 * [REPARADO]: Seguridad de archivos estáticos, inicialización de BD, gestión de memoria y mensajes.
 * [REPARADO 2]: El temporizador de conexión no se reiniciaba durante 'authenticated'/'loading_screen',
 *               por lo que forceNewQR() podía destruir sesiones válidas aún sincronizando. Además se
 *               amplió el bloqueo de archivos estáticos (node_modules, .git, package.json).
 */

const { Client, LocalAuth } = require('whatsapp-web.js');
const ChatFactory = require('whatsapp-web.js/src/factories/ChatFactory');
const { OpenAI } = require('openai');
const express = require('express');
const http = require('http');
const { Server } = require('socket.io');
const qrcodeUrl = require('qrcode');
const cors = require('cors');
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

// --- Configuración de Red y Servidor ---
const app = express();
app.use(cors());
app.use(express.json());

// [REPARACIÓN DE SEGURIDAD]: Evitar que express.static exponga las sesiones y configs al público.
app.use((req, res, next) => {
    const forbidden = ['.wwebjs_auth', 'config.json', 'server.js', 'app.js', '.env', 'node_modules', '.git', 'package.json', 'package-lock.json', 'audios_descargados'];
    if (forbidden.some(word => req.path.includes(word))) {
        return res.status(403).send('Acceso denegado a archivos sensibles del servidor.');
    }
    next();
});

app.use(express.static(path.join(__dirname)));

const server = http.createServer(app);
const io = new Server(server, { 
    cors: { origin: "*", methods: ["GET", "POST"] } 
});

app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

// --- Configuración de Base de Datos MySQL (Puerto 7000) ---
const dbConfig = {
    host: 'localhost',
    user: 'root',      
    password: '',      
    database: 'guagua',
    port: 7000         
};

const CONFIG_FILE = path.join(__dirname, 'config.json');
const DEFAULT_AUDIO_OUTPUT_PATH = 'D:\\xampp\\htdocs\\whisper\\input';
const WHISPER_PROJECT_ROOT = 'D:\\xampp\\htdocs\\whisper';
const WHISPER_OUTPUT_PATH = path.join(WHISPER_PROJECT_ROOT, 'output');
const WHISPER_SCRIPT_PATH = path.join(WHISPER_PROJECT_ROOT, 'run_script.bat');
const WHISPER_VENV_ACTIVATE_PATH = path.join(WHISPER_PROJECT_ROOT, 'venv', 'Scripts', 'activate.bat');
const WHISPER_CONSOLIDATED_PATH = path.join(WHISPER_OUTPUT_PATH, 'transcripcion_consolidada.txt');
const MAX_WHISPER_PREVIEW_CHARS = 80000;
const MAX_WHISPER_ANALYSIS_CHARS = 500000;
const MAX_MESSAGES_PER_CHAT = 2000;

// Los archivos exportados contienen conversaciones privadas: se guardan localmente,
// pero no se exponen mediante express.static.
fs.mkdirSync(DEFAULT_AUDIO_OUTPUT_PATH, { recursive: true });
fs.mkdirSync(WHISPER_OUTPUT_PATH, { recursive: true });

let pool = null;
let dbConnected = false;

let botState = 'INITIALIZING';
let qrData = null;
let loadingPercent = 0;
let loadingMessage = '';
let connectionTimeoutTimer = null;
let cachedChatsList = [];
let storeReady = false; 
let chatsLoadPromise = null;
let connectionStartedAt = 0;
let connectionDurationMs = null;
let connectionWarningSent = false;
let clientReadyPromise = null;
let readinessProbeTimer = null;
let extractionJobActive = false;
let consolidationJobActive = false;
let lastConnectionError = '';
let whisperProcess = null;
let activeWhisperTranscriptPath = null;

let currentConfig = { 
    apiKey: 'lm-studio', 
    lmStudioUrl: 'http://169.254.173.61:1234/v1', 
    modelName: 'gemma-2-2b-it',
    audioOutputPath: DEFAULT_AUDIO_OUTPUT_PATH,
    selectedGroups: [] 
};

const { createDiaryService } = require('../guagua/apps/diario/diario-service.cjs');
const diaryService = createDiaryService({config:currentConfig, outputPath:WHISPER_OUTPUT_PATH, OpenAI, normalizeUrl:getNormalizedLmStudioUrl});
diaryService.attach(io);
const { createWhisperWorkflow } = require('../guagua/apps/diario/whisper-workflow.cjs');
const whisperWorkflow = createWhisperWorkflow({ projectRoot: WHISPER_PROJECT_ROOT, logPath: path.join(__dirname, '../guagua/tmp/whatsapp-diario/whisper.log') });

// --- Manejo Global de Errores para que Node.js no se caiga ---
process.on('uncaughtException', (err) => console.error('Excepción no capturada:', err));
process.on('unhandledRejection', (reason, promise) => console.error('Promesa rechazada no manejada:', reason));

function getNormalizedLmStudioUrl(rawUrl) {
    let url = (rawUrl || 'http://169.254.173.61:1234/v1').trim();
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
        url = 'http://' + url;
    }
    url = url.replace(/\/+$/, '');
    if (!url.endsWith('/v1')) {
        url = url + '/v1';
    }
    return url;
}

function parseErrorMessage(err, defaultMsg = "Error inesperado") {
    if (!err) return defaultMsg;
    let msg = err.message || err;
    if (typeof msg !== 'string') {
        try { msg = JSON.stringify(msg); } catch (e) { msg = String(msg); }
    }
    if (!msg.trim()) {
        return err.stack || defaultMsg;
    }
    return msg;
}

function normalizeAudioOutputPath(value) {
    const raw = String(value || DEFAULT_AUDIO_OUTPUT_PATH).trim();
    if (!path.isAbsolute(raw)) {
        throw new Error('La ruta de salida debe ser una ruta absoluta, por ejemplo D:\\xampp\\htdocs\\whisper\\input.');
    }
    const resolved = path.resolve(raw);
    if (resolved.toLowerCase() === path.parse(resolved).root.toLowerCase()) {
        throw new Error('La ruta de salida no puede ser la raíz completa de una unidad.');
    }
    return resolved;
}

function getAudioOutputRoot() {
    const outputRoot = normalizeAudioOutputPath(currentConfig.audioOutputPath);
    fs.mkdirSync(outputRoot, { recursive: true });
    return outputRoot;
}

function isWhisperInputConfigured() {
    return getAudioOutputRoot().toLowerCase() === path.resolve(DEFAULT_AUDIO_OUTPUT_PATH).toLowerCase();
}

function parseWhisperTsv(filePath) {
    const lines = fs.readFileSync(filePath, 'utf8').replace(/^\uFEFF/, '').split(/\r?\n/);
    const dataLines = /^StartTime\tEndTime\tText$/i.test(lines[0]?.trim() || '') ? lines.slice(1) : lines;
    return dataLines
        .filter(line => line.trim())
        .map(line => {
            const [start = '', end = '', ...textParts] = line.split('\t');
            const text = textParts.join('\t').trim();
            return text ? `[${start.trim()} - ${end.trim()}] ${text}` : line.trim();
        })
        .join('\n');
}

function progressPayload(startedAt, details) {
    const percent = Math.max(0, Math.min(100, Number(details.percent) || 0));
    const elapsedMs = Math.max(0, Date.now() - startedAt);
    // The estimate is based on the average time of the work completed so far.
    // It becomes more stable as more chats/files are processed.
    const etaMs = percent > 0 && percent < 100
        ? Math.max(0, Math.round(elapsedMs * (100 - percent) / percent))
        : 0;
    return { ...details, percent, elapsedMs, etaMs };
}

async function consolidateWhisperTranscripts(onProgress = () => {}, options = {}) {
    const result = await diaryService.consolidate(onProgress, options);
    activeWhisperTranscriptPath = result.filePath;
    return result;
}

function getConnectionElapsedSeconds() {
    if (botState === 'READY' && Number.isFinite(connectionDurationMs)) {
        return Math.round(connectionDurationMs / 1000);
    }
    return connectionStartedAt ? Math.max(0, Math.floor((Date.now() - connectionStartedAt) / 1000)) : 0;
}

function statusPayload(extra = {}) {
    return {
        status: botState,
        qrData,
        loadingPercent,
        loadingMessage,
        dbConnected,
        connectionElapsedSeconds: getConnectionElapsedSeconds(),
        connectionDurationMs,
        lastConnectionError,
        ...extra
    };
}

function emitStatus(target = io, extra = {}) {
    target.emit('status', statusPayload(extra));
}

function emitSystemAlert(level, message, details = '') {
    io.emit('system_alert', { level, message, details, at: new Date().toISOString() });
}

function safeFileSegment(value, fallback = 'sin_nombre') {
    const safe = String(value || fallback)
        .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
        .replace(/[<>:"/\\|?*\x00-\x1F]/g, '_')
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 80);
    return safe || fallback;
}

function getChatExportFolder(exportFolder, chat) {
    const chatId = chat && chat.id && chat.id._serialized ? chat.id._serialized : 'chat';
    // El ID evita que chats con el mismo nombre escriban en la misma carpeta.
    const suffix = safeFileSegment(chatId, 'chat').slice(-24);
    return path.join(exportFolder, `${safeFileSegment(chat?.name || chatId)}_${suffix}`);
}

function extensionFromMime(mimeType, isAudio = false) {
    const mime = String(mimeType || '').split(';')[0].toLowerCase();
    const extensions = {
        'audio/ogg': 'ogg', 'audio/opus': 'opus', 'audio/mpeg': 'mp3',
        'audio/mp4': 'm4a', 'audio/aac': 'aac', 'audio/wav': 'wav',
        'audio/webm': 'webm', 'video/mp4': 'mp4', 'video/3gpp': '3gp',
        'video/webm': 'webm', 'image/jpeg': 'jpg', 'image/png': 'png',
        'image/webp': 'webp', 'image/gif': 'gif', 'application/pdf': 'pdf',
        'application/zip': 'zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document': 'docx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet': 'xlsx',
        'application/msword': 'doc', 'application/vnd.ms-excel': 'xls'
    };
    return extensions[mime] || (isAudio ? 'ogg' : 'bin');
}

function isAudioMessage(message) {
    return message.type === 'ptt' || message.type === 'audio' || /^audio\//i.test(message.mimetype || '');
}

function getSerializedMessageId(message) {
    const id = message?.id || message?._data?.id;
    if (!id) return null;
    if (typeof id === 'string') return id;
    if (typeof id._serialized === 'string' && id._serialized) return id._serialized;

    // En versiones recientes de WhatsApp Web, getMessageModel entrega el
    // WAWebMsgKey como campos separados y omite _serialized.
    const remote = id.remote?._serialized || id.remote;
    const messageKey = id.id;
    const participant = id.participant?._serialized || id.participant;
    const fromMe = id.fromMe ?? message?.fromMe;
    if (remote && messageKey && typeof fromMe === 'boolean') {
        return `${fromMe}_${remote}_${messageKey}${participant ? `_${participant}` : ''}`;
    }
    return null;
}

function getMessageAuthor(message) {
    if (message._data && message._data.notifyName) return message._data.notifyName;
    if (message.author) return String(message.author).split('@')[0];
    if (message.from) return String(message.from).split('@')[0];
    return 'Usuario';
}

function dateRangeToTimestamps(startDate, endDate) {
    if (!/^\d{4}-\d{2}-\d{2}$/.test(startDate || '') || !/^\d{4}-\d{2}-\d{2}$/.test(endDate || startDate || '')) {
        throw new Error('Seleccione una fecha inicial y una fecha final válidas.');
    }
    const start = new Date(`${startDate}T00:00:00`);
    const end = new Date(`${endDate || startDate}T23:59:59.999`);
    if (Number.isNaN(start.getTime()) || Number.isNaN(end.getTime()) || end < start) {
        throw new Error('El rango de fechas no es válido.');
    }
    return { startTs: start.getTime() / 1000, endTs: end.getTime() / 1000 };
}


function hasExtractableMedia(message) {
    return Boolean(message && (message.hasMedia || message._data?.mediaData ||
        message._data?.mediaKey || ['ptt', 'audio', 'image', 'video', 'document', 'sticker'].includes(message.type)));
}

async function readExtractionHistory(chat, startTs, endTs) {
    const { readExtractionHistory: readHistory } = require('../guagua/apps/diario/extraction-history.cjs');
    const Message = require('whatsapp-web.js/src/structures/Message');
    return readHistory({ client, chat, startTs, endTs, cap: MAX_MESSAGES_PER_CHAT, Message });
}

async function safeDownloadMedia(clientInstance, message, retries = 2) {
    if (!hasExtractableMedia(message)) throw new Error('El mensaje ya no contiene un archivo multimedia.');
    if (!clientInstance || !clientInstance.pupPage) throw new Error('La página de WhatsApp Web no está disponible.');

    const MessageMedia = require('whatsapp-web.js/src/structures/MessageMedia');
    const messageId = getSerializedMessageId(message);
    if (!messageId) throw new Error('No se pudo identificar el mensaje multimedia en WhatsApp Web.');
    const telemetry = [];

    for (let attempt = 1; attempt <= retries; attempt++) {
        const startedAt = Date.now();
        try {
            const browserResult = await clientInstance.pupPage.evaluate(async (msgId, waitMs) => {
                const wait = ms => new Promise(resolve => setTimeout(resolve, ms));
                const describe = msg => ({
                    stage: msg?.mediaData?.mediaStage || 'sin_stage',
                    hasDirectPath: Boolean(msg?.directPath || msg?.mediaData?.directPath),
                    hasMediaKey: Boolean(msg?.mediaKey || msg?.mediaData?.mediaKey)
                });

                try {
                    const MsgCollection = window.require('WAWebCollections').Msg;
                    let msg = MsgCollection.get(msgId);

                    // WhatsApp Web actual usa WAWebMsgKey. Pasar el texto crudo a
                    // getMessagesById provoca DataError en algunas versiones.
                    if (!msg && typeof MsgCollection.getModelsArray === 'function') {
                        msg = MsgCollection.getModelsArray().find(model => model?.id?.toString?.() === msgId);
                    }
                    if (!msg) {
                        try {
                            const MsgKey = window.require('WAWebMsgKey');
                            const key = MsgKey?.fromString?.(msgId);
                            const fetched = key && await MsgCollection.getMessagesById?.([key]);
                            msg = fetched?.messages?.[0];
                        } catch (error) {
                            return { error: `No se pudo recuperar la clave del mensaje: ${error?.message || String(error)}` };
                        }
                    }
                    if (!msg) return { error: 'El mensaje no está disponible en la memoria de WhatsApp Web.' };
                    if (!msg.mediaData) return { error: 'El mensaje no incluye mediaData.', telemetry: describe(msg) };
                    if (msg.mediaData.mediaStage === 'REUPLOADING') {
                        return { error: 'El archivo expiró en WhatsApp; solicite que se reenvíe desde el teléfono.', telemetry: describe(msg) };
                    }

                    // No se fuerza la desencriptación mientras WhatsApp sigue en FETCHING.
                    // Audios antiguos pueden necesitar decenas de segundos para pasar de
                    // INIT/FETCHING a RESOLVED.
                    if (msg.mediaData.mediaStage !== 'RESOLVED') {
                        const download = msg.downloadMedia?.({ downloadEvenIfExpensive: true, rmrReason: 1 });
                        if (download && typeof download.then === 'function') {
                            await Promise.race([
                                download,
                                wait(waitMs).then(() => { throw new Error(`La descarga no terminó en ${Math.round(waitMs / 1000)} s.`); })
                            ]);
                        }
                    }

                    if (msg.mediaData.mediaStage !== 'RESOLVED') {
                        return {
                            error: `WhatsApp no terminó de preparar el archivo (estado: ${msg.mediaData.mediaStage || 'sin_stage'}).`,
                            telemetry: describe(msg)
                        };
                    }

                    const downloadModule = window.require('WAWebDownloadManager');
                    const downloadManager = downloadModule?.downloadManager || downloadModule;
                    if (!downloadManager || typeof downloadManager.downloadAndMaybeDecrypt !== 'function') {
                        return { error: 'El módulo de descarga de WhatsApp Web no está disponible.', telemetry: describe(msg) };
                    }

                    const mockQpl = { addAnnotations() { return this; }, addPoint() { return this; } };
                    const decryptedMedia = await downloadManager.downloadAndMaybeDecrypt({
                        directPath: msg.directPath || msg.mediaData?.directPath,
                        encFilehash: msg.encFilehash || msg.mediaData?.encFilehash,
                        filehash: msg.filehash || msg.mediaData?.filehash,
                        mediaKey: msg.mediaKey || msg.mediaData?.mediaKey,
                        mediaKeyTimestamp: msg.mediaKeyTimestamp || msg.mediaData?.mediaKeyTimestamp,
                        type: msg.type || msg.mediaData?.type,
                        mimetype: msg.mimetype || msg.mediaData?.mimetype || (msg.type === 'ptt' ? 'audio/ogg' : 'application/octet-stream'),
                        signal: new AbortController().signal,
                        downloadQpl: mockQpl
                    });
                    if (!decryptedMedia) return { error: 'WhatsApp no devolvió bytes desencriptados.', telemetry: describe(msg) };

                    const data = await window.WWebJS.arrayBufferToBase64Async(decryptedMedia);
                    return {
                        data,
                        mimetype: msg.mimetype || msg.mediaData?.mimetype || 'application/octet-stream',
                        filename: msg.filename || msg.mediaData?.filename || null,
                        filesize: msg.size || msg.mediaData?.size || null,
                        telemetry: describe(msg)
                    };
                } catch (error) {
                    return { error: error?.message || String(error) };
                }
            }, messageId, 90000);

            if (browserResult?.data) {
                return new MessageMedia(
                    browserResult.mimetype,
                    browserResult.data,
                    browserResult.filename,
                    browserResult.filesize
                );
            }

            telemetry.push({
                attempt,
                durationMs: Date.now() - startedAt,
                error: browserResult?.error || 'WhatsApp devolvió una respuesta vacía.',
                state: browserResult?.telemetry?.stage || 'desconocido'
            });
        } catch (error) {
            telemetry.push({
                attempt,
                durationMs: Date.now() - startedAt,
                error: parseErrorMessage(error),
                state: 'error_de_puppeteer'
            });
        }

        if (attempt < retries) await new Promise(resolve => setTimeout(resolve, 1500 * attempt));
    }

    const details = telemetry.map(item =>
        `intento ${item.attempt}: ${item.error} (estado: ${item.state}, ${(item.durationMs / 1000).toFixed(1)} s)`
    ).join(' | ');
    throw new Error(`No se pudo descargar el archivo. Telemetría: ${details}`);
}

// ============================================================
// BASE DE DATOS
// ============================================================

async function initDatabase() {
    try {
        pool = mysql.createPool(dbConfig);
        const conn = await pool.getConnection();
        
        // [REPARACIÓN]: Crear las tablas si no existen para evitar que falle en instancias nuevas
        console.log("🗄️ Verificando estructura de la base de datos...");
        await conn.query(`
            CREATE TABLE IF NOT EXISTS configuracion (
                id INT PRIMARY KEY,
                apiKey VARCHAR(255) DEFAULT 'lm-studio',
                lmStudioUrl VARCHAR(255) DEFAULT 'http://169.254.173.61:1234/v1',
                modelName VARCHAR(255) DEFAULT 'gemma-2-2b-it',
                audioOutputPath VARCHAR(500) NULL
            )
        `);
        const [outputPathColumn] = await conn.query("SHOW COLUMNS FROM configuracion LIKE 'audioOutputPath'");
        if (!outputPathColumn.length) {
            await conn.query('ALTER TABLE configuracion ADD COLUMN audioOutputPath VARCHAR(500) NULL');
        }
        await conn.query(`
            CREATE TABLE IF NOT EXISTS grupos_seleccionados (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nombre VARCHAR(255)
            )
        `);
        await conn.query(`
            CREATE TABLE IF NOT EXISTS resumenes_historicos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                fecha_inicio DATE,
                fecha_fin DATE,
                grupos_analizados TEXT,
                contenido_resumen TEXT,
                fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);

        conn.release();
        dbConnected = true;
        console.log("🗄️ Conexión establecida con MySQL (guagua) en puerto 7000.");
        await syncSettings();
    } catch (error) {
        dbConnected = false;
        console.warn("⚠️ MySQL no disponible. Usando config.json:", error.message);
        loadLocalConfig();
    }
}

function loadLocalConfig() {
    try {
        if (fs.existsSync(CONFIG_FILE)) {
            const raw = fs.readFileSync(CONFIG_FILE, 'utf8');
            const parsed = JSON.parse(raw);
            currentConfig.lmStudioUrl = parsed.lmStudioUrl || 'http://169.254.173.61:1234/v1';
            currentConfig.modelName = parsed.modelName || 'gemma-2-2b-it';
            currentConfig.audioOutputPath = normalizeAudioOutputPath(parsed.audioOutputPath || DEFAULT_AUDIO_OUTPUT_PATH);
            currentConfig.selectedGroups = parsed.selectedGroups || parsed.groups || [];
            console.log("📂 Configuración cargada desde config.json.");
        }
    } catch (err) {
        console.error("❌ Error leyendo config.json:", err.message);
    }
}

function saveLocalConfig() {
    try {
        fs.writeFileSync(CONFIG_FILE, JSON.stringify({
            lmStudioUrl: currentConfig.lmStudioUrl,
            modelName: currentConfig.modelName,
            audioOutputPath: currentConfig.audioOutputPath,
            selectedGroups: currentConfig.selectedGroups
        }, null, 2));
    } catch (err) {
        console.error("❌ Error guardando config.json:", err.message);
    }
}

async function syncSettings() {
    if (!dbConnected) {
        loadLocalConfig();
        io.emit('config_data', currentConfig);
        return;
    }
    try {
        const [configRows] = await pool.query('SELECT * FROM configuracion WHERE id = 1');
        if (configRows.length > 0) {
            currentConfig.apiKey = configRows[0].apiKey || 'lm-studio';
            currentConfig.lmStudioUrl = configRows[0].lmStudioUrl || 'http://169.254.173.61:1234/v1';
            currentConfig.modelName = configRows[0].modelName || 'gemma-2-2b-it';
            currentConfig.audioOutputPath = normalizeAudioOutputPath(configRows[0].audioOutputPath || DEFAULT_AUDIO_OUTPUT_PATH);
            if (!configRows[0].audioOutputPath) {
                await pool.query('UPDATE configuracion SET audioOutputPath = ? WHERE id = 1', [currentConfig.audioOutputPath]);
            }
        } else {
            await pool.query(
                'INSERT INTO configuracion (id, apiKey, lmStudioUrl, modelName, audioOutputPath) VALUES (?, ?, ?, ?, ?)',
                [1, 'lm-studio', 'http://169.254.173.61:1234/v1', 'gemma-2-2b-it', DEFAULT_AUDIO_OUTPUT_PATH]
            );
        }
        const [groupRows] = await pool.query('SELECT nombre FROM grupos_seleccionados');
        currentConfig.selectedGroups = groupRows.map(row => row.nombre);
        saveLocalConfig();
        console.log("📂 Configuración sincronizada desde MySQL.");
        io.emit('config_data', currentConfig);
    } catch (error) {
        console.error("❌ Error en sincronización DB:", error.message);
        loadLocalConfig();
        io.emit('config_data', currentConfig);
    }
}

// ============================================================
// WHATSAPP CLIENT
// ============================================================

// [REPARACIÓN]: El error "Attempted to use detached Frame" / "LifecycleWatcher disposed" que
// aparece dentro de Client.initialize() -> page.goto() NO viene de la lógica de este archivo:
// ocurre por completo dentro de node_modules/puppeteer-core y node_modules/whatsapp-web.js.
// La causa más común y bien documentada es un desfase entre el Chrome del SISTEMA (que se
// autoactualiza solo, en segundo plano, cada pocas semanas) y la versión de puppeteer-core que
// trae instalado whatsapp-web.js, que espera un build de Chrome específico. Usar
// WSP_USE_SYSTEM_CHROME=false deja que Puppeteer use su propio Chromium (ya probado y
// compatible) en vez del Chrome instalado en Windows, para descartar esta causa.
let browserPath = null;
if (process.env.WSP_USE_SYSTEM_CHROME !== 'false') {
    browserPath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
    if (!fs.existsSync(browserPath)) browserPath = 'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe';
    if (!fs.existsSync(browserPath)) {
        const userLocalChrome = path.join(process.env.LOCALAPPDATA || '', 'Google\\Chrome\\Application\\chrome.exe');
        if (fs.existsSync(userLocalChrome)) browserPath = userLocalChrome;
        else browserPath = null;
    }
}

const puppeteerOptions = {
    headless: true,
    args: [
        '--no-sandbox',
        '--disable-setuid-sandbox',
        '--disable-dev-shm-usage',
        '--disable-accelerated-2d-canvas',
        '--no-first-run',
        '--disable-gpu'
        // [REPARACIÓN]: Se quitaron '--single-process' y '--no-zygote'. '--single-process' en
        // particular ya no está soportado de forma estable en builds recientes de Chrome y es
        // una causa conocida de errores de frames "detached"/lifecycle en Puppeteer.
    ]
};
if (browserPath) {
    puppeteerOptions.executablePath = browserPath;
}

let client;
let reconnectTimer = null;
let reconnectAttempts = 0;
let reconnectSuppressed = false;
let intentionalLogout = false;

function clearReconnectTimer() {
    if (reconnectTimer) {
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
    }
}

function scheduleReconnect(reason, sourceClient) {
    if (reconnectSuppressed || intentionalLogout || client !== sourceClient || reconnectTimer) return;

    const maxAttempts = 5;
    if (reconnectAttempts >= maxAttempts) {
        const details = `Último motivo: ${reason || 'sin detalle'}. Se agotaron ${maxAttempts} reintentos automáticos.`;
        io.emit('log', { msg: `❌ ${details}`, type: 'error' });
        emitSystemAlert('error', 'WhatsApp no pudo reconectarse automáticamente.', details);
        return;
    }

    reconnectAttempts++;
    const delayMs = Math.min(30000, 2000 * (2 ** (reconnectAttempts - 1)));
    botState = 'INITIALIZING';
    loadingPercent = 0;
    loadingMessage = `Reconectando automáticamente (${reconnectAttempts}/${maxAttempts})...`;
    emitStatus();
    io.emit('log', { msg: `🔄 WhatsApp se reconectará en ${Math.round(delayMs / 1000)} s (${reconnectAttempts}/${maxAttempts}). Motivo: ${reason || 'sin detalle'}`, type: 'info' });

    reconnectTimer = setTimeout(async () => {
        reconnectTimer = null;
        if (reconnectSuppressed || intentionalLogout || client !== sourceClient) return;
        try {
            reconnectSuppressed = true;
            await sourceClient.destroy().catch(() => {});
            reconnectSuppressed = false;
            if (client !== sourceClient) return;
            const newClient = createWhatsAppClient();
            newClient.initialize().catch(error => {
                const message = parseErrorMessage(error);
                console.error('❌ Error al reconectar WhatsApp:', message);
                lastConnectionError = message;
                scheduleReconnect(message, newClient);
            });
        } catch (error) {
            reconnectSuppressed = false;
            const message = parseErrorMessage(error);
            console.error('❌ Error preparando reconexión:', message);
            lastConnectionError = message;
            scheduleReconnect(message, sourceClient);
        }
    }, delayMs);
}

function resetConnectionTimer(timeoutSeconds = 60) {
    if (connectionTimeoutTimer) clearTimeout(connectionTimeoutTimer);
    connectionTimeoutTimer = setTimeout(() => {
        if (botState !== 'READY' && botState !== 'WAITING_QR') {
            // Nunca destruimos una sesión automáticamente por una conexión lenta. Esto
            // era la causa de QR repetidos cuando WhatsApp seguía sincronizando chats.
            const elapsed = getConnectionElapsedSeconds();
            const message = `La conexión lleva ${elapsed}s sin progreso reciente.`;
            console.warn(`⚠️ ${message}`);
            io.emit('log', { msg: `⚠️ ${message} Revise la red o reinicie manualmente si persiste.`, type: 'error' });
            if (!connectionWarningSent) {
                connectionWarningSent = true;
                emitSystemAlert('warning', 'La conexión con WhatsApp está tardando más de lo habitual.', 'La sesión se conserva. Puede esperar, revisar la red o reiniciar manualmente.');
            }
            emitStatus();
        }
    }, timeoutSeconds * 1000);
}

function clearConnectionTimer() {
    if (connectionTimeoutTimer) {
        clearTimeout(connectionTimeoutTimer);
        connectionTimeoutTimer = null;
    }
}

function clearReadinessProbe() {
    if (readinessProbeTimer) {
        clearTimeout(readinessProbeTimer);
        readinessProbeTimer = null;
    }
}

async function finishWhatsAppReadiness(whatsappClient, source = 'evento ready') {
    if (client !== whatsappClient || botState === 'READY') return;
    if (clientReadyPromise) return clientReadyPromise;

    clientReadyPromise = (async () => {
        clearReadinessProbe();
        clearConnectionTimer();
        botState = 'LOADING';
        loadingPercent = Math.max(95, loadingPercent || 0);
        loadingMessage = 'Hidratando módulos internos...';
        emitStatus();
        io.emit('log', { msg: `WhatsApp conectado. Verificando módulos internos (${source})...`, type: 'info' });

        const hydrated = await waitForStoreReady(15, 2000);
        if (client !== whatsappClient) return;

        storeReady = hydrated;
        botState = 'READY';
        qrData = null;
        connectionDurationMs = Date.now() - connectionStartedAt;
        loadingPercent = 100;
        loadingMessage = hydrated ? 'Sistema listo' : 'Sistema listo (verificación parcial)';
        emitStatus();
        io.emit('log', {
            msg: `${hydrated ? '✅' : '⚠️'} WhatsApp conectado en ${getConnectionElapsedSeconds()}s${hydrated ? ' y módulos listos.' : '. Algunos módulos podrían tardar.'}`,
            type: hydrated ? 'success' : 'info'
        });
        clearReconnectTimer();
        reconnectAttempts = 0;

        try {
            const chats = await safeGetChats(2, 1500);
            io.emit('log', { msg: `📂 ${chats.length} chats precargados en memoria.`, type: 'success' });
        } catch (error) {
            console.warn('Precarga de chats falló:', parseErrorMessage(error));
        }
    })();

    try {
        await clientReadyPromise;
    } finally {
        if (client === whatsappClient) clientReadyPromise = null;
    }
}

function scheduleReadinessProbe(whatsappClient) {
    clearReadinessProbe();
    readinessProbeTimer = setTimeout(() => {
        readinessProbeTimer = null;
        if (client !== whatsappClient || botState === 'READY') return;
        // Algunas compilaciones recientes de WhatsApp Web quedan visualmente en 99%
        // y no emiten el evento ready de la librería, aunque los módulos ya están listos.
        finishWhatsAppReadiness(whatsappClient, 'verificación al 99%').catch(error => {
            console.warn('Verificación de disponibilidad falló:', parseErrorMessage(error));
        });
    }, 4000);
}

async function forceNewQR() {
    try {
        reconnectSuppressed = true;
        intentionalLogout = false;
        clearReconnectTimer();
        clearReadinessProbe();
        clientReadyPromise = null;
        reconnectAttempts = 0;
        botState = 'INITIALIZING';
        qrData = null;
        storeReady = false;
        cachedChatsList = [];
        chatsLoadPromise = null;
        loadingPercent = 0;
        loadingMessage = 'Generando nuevo QR...';
        emitStatus();
        
        if (client) {
            await client.destroy().catch(e => console.warn("Advertencia al destruir cliente:", e.message));
            // [REPARACIÓN]: Pequeña pausa crucial para liberar el bloqueo de archivos y puertos antes de recrear el cliente
            await new Promise(r => setTimeout(r, 2000));
        }
        
        const newClient = createWhatsAppClient();
        reconnectSuppressed = false;
        newClient.initialize().catch(err => {
            console.error("Error al forzar inicialización:", err);
        });
    } catch (err) {
        reconnectSuppressed = false;
        console.error("Error en forceNewQR:", err);
    }
}

async function waitForStoreReady(maxAttempts = 15, intervalMs = 2000) {
    for (let i = 0; i < maxAttempts; i++) {
        try {
            if (!client || !client.pupPage) return false;
            
            const ready = await client.pupPage.evaluate(() => {
                try {
                    // whatsapp-web.js 1.34.x obtiene chats desde WAWebCollections,
                    // no desde window.Store.Chat. La comprobación anterior siempre
                    // fallaba en WhatsApp Web actual aunque el cliente ya estuviera listo.
                    const collections = window.require('WAWebCollections');
                    return Boolean(
                        window.WWebJS &&
                        typeof window.WWebJS.getChats === 'function' &&
                        collections &&
                        collections.Chat &&
                        typeof collections.Chat.getModelsArray === 'function'
                    );
                } catch(e) { return false; }
            });

            if (ready) {
                console.log(`✅ Store de WhatsApp hidratado correctamente (intento ${i + 1}/${maxAttempts}).`);
                return true;
            }
        } catch (err) {
            if (err.message && (err.message.includes('Target closed') || err.message.includes('Session closed'))) {
                return false;
            }
        }
        
        console.log(`⏳ Store aún no disponible. Esperando... (${i + 1}/${maxAttempts})`);
        io.emit('log', { msg: `Hidratando WhatsApp Web... (${i + 1}/${maxAttempts})`, type: 'info' });
        await new Promise(r => setTimeout(r, intervalMs));
    }
    return false;
}

async function getChatsFromCollection() {
    if (!client || !client.pupPage) throw new Error('La página de WhatsApp Web no está disponible.');

    // client.getChats() serializa metadatos completos de todos los grupos. En algunas
    // cuentas un metadato de grupo no sincronizado hace fallar TODA la lista con el
    // error minificado "r". Para seleccionar y leer historial solo necesitamos el
    // identificador, título y tipo, que ya están en la colección local.
    const models = await client.pupPage.evaluate(() => {
        const collection = window.require('WAWebCollections').Chat;
        return collection.getModelsArray()
            .map(chat => {
                const id = chat.id;
                const serializedId = id && (id._serialized || id.toString());
                if (!serializedId) return null;
                return {
                    id: {
                        _serialized: serializedId,
                        user: id.user,
                        server: id.server
                    },
                    formattedTitle: chat.formattedTitle || chat.name || serializedId,
                    isGroup: Boolean(chat.groupMetadata) || id.server === 'g.us',
                    isReadOnly: Boolean(chat.groupMetadata?.announce),
                    unreadCount: chat.unreadCount || 0,
                    t: chat.t || 0,
                    archive: Boolean(chat.archive),
                    pin: Boolean(chat.pin),
                    isMuted: Boolean(chat.mute?.expiration)
                };
            })
            .filter(Boolean);
    });

    return models.map(model => ChatFactory.create(client, model));
}

async function safeGetChats(retries = 4, initialDelayMs = 2000) {
    // Varias pestañas pueden pedir los chats al mismo tiempo cuando llega READY.
    // WhatsApp Web no maneja bien esas evaluaciones concurrentes durante la hidratación.
    if (chatsLoadPromise) {
        io.emit('log', { msg: 'Reutilizando la carga de chats que ya está en curso...', type: 'info' });
        return chatsLoadPromise;
    }
    chatsLoadPromise = safeGetChatsInternal(retries, initialDelayMs);
    try {
        return await chatsLoadPromise;
    } finally {
        chatsLoadPromise = null;
    }
}

async function safeGetChatsInternal(retries = 4, initialDelayMs = 2000) {
    if (!client) throw new Error("Cliente de WhatsApp no inicializado.");

    if (!storeReady) {
        io.emit('log', { msg: 'Esperando a que WhatsApp Web termine de cargar sus módulos internos...', type: 'info' });
        const hydrated = await waitForStoreReady(10, 2000);
        if (hydrated) storeReady = true;
        else console.warn("⚠️ No se pudo verificar el Store, intentando getChats de todos modos...");
    }

    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            const chats = await Promise.race([
                getChatsFromCollection(),
                new Promise((_, reject) => setTimeout(() => reject(new Error("Timeout al consultar chats (15s)")), 15000))
            ]);

            if (chats && Array.isArray(chats) && chats.length > 0) {
                cachedChatsList = chats;
                return chats;
            }
        } catch (err) {
            const cleanErr = parseErrorMessage(err);
            console.warn(`⚠️ Intento ${attempt}/${retries} getChats falló: ${cleanErr}`);
            const delay = initialDelayMs * attempt;
            await new Promise(r => setTimeout(r, delay));
        }
    }

    if (cachedChatsList.length > 0) {
        console.log(`ℹ️ Usando ${cachedChatsList.length} chats de la caché previa.`);
        return cachedChatsList;
    }

    let diagnostic = '';
    try {
        diagnostic = await client.pupPage.evaluate(() => {
            try {
                const collections = window.require('WAWebCollections');
                return JSON.stringify({
                    wwebjs: Boolean(window.WWebJS),
                    getChats: typeof window.WWebJS?.getChats,
                    chatCollection: Boolean(collections?.Chat),
                    getModelsArray: typeof collections?.Chat?.getModelsArray
                });
            } catch (error) {
                return JSON.stringify({ diagnosticError: error.message || String(error) });
            }
        });
    } catch (error) {
        diagnostic = parseErrorMessage(error);
    }
    throw new Error(`No se pudieron obtener los chats después de ${retries} intentos. Diagnóstico: ${diagnostic || 'no disponible'}`);
}

function createWhatsAppClient() {
    storeReady = false;
    clientReadyPromise = null;
    clearReadinessProbe();
    connectionStartedAt = Date.now();
    connectionDurationMs = null;
    connectionWarningSent = false;
    lastConnectionError = '';
    
    const whatsappClient = new Client({
        authStrategy: new LocalAuth({ dataPath: path.join(__dirname, '.wwebjs_auth') }),
        puppeteer: puppeteerOptions,
    });
    client = whatsappClient;
    const isCurrentClient = () => client === whatsappClient;

    resetConnectionTimer(60);

    whatsappClient.on('qr', async (qr) => {
        if (!isCurrentClient()) return;
        console.log("📌 Nuevo Código QR generado.");
        clearReadinessProbe();
        clearConnectionTimer();
        botState = 'WAITING_QR';
        try {
            qrData = await qrcodeUrl.toDataURL(qr);
        } catch (err) {
            qrData = null;
        }
        io.emit('qr', qrData);
        emitStatus();
        io.emit('log', { msg: 'Código QR generado. Escanea con tu WhatsApp para conectar.', type: 'info' });
    });

    whatsappClient.on('authenticated', () => {
        if (!isCurrentClient()) return;
        console.log("🔑 Sesión autenticada.");
        clearReadinessProbe();
        botState = 'AUTHENTICATED';
        // [REPARACIÓN]: El temporizador de conexión (creado junto con el cliente) seguía corriendo
        // durante la sincronización posterior a la autenticación. En cuentas con muchos grupos esa
        // sincronización puede tardar más de 40s, y el timeout original destruía una sesión válida
        // a mitad de carga, forzando un nuevo QR sin motivo. Lo reiniciamos aquí con más margen.
        resetConnectionTimer(60);
        emitStatus();
        io.emit('log', { msg: 'Sesión autenticada. Cargando datos de WhatsApp...', type: 'info' });
    });

    whatsappClient.on('loading_screen', (percent, message) => {
        if (!isCurrentClient()) return;
        // WhatsApp Web puede emitir un último evento "loading_screen: 99%"
        // después de ready. No debe degradar un cliente que ya está operativo.
        if (botState === 'READY') return;
        botState = 'LOADING';
        loadingPercent = percent;
        loadingMessage = message;
        // [REPARACIÓN]: Cada evento de progreso reinicia el temporizador. Así, mientras WhatsApp
        // siga reportando avance (aunque sea lento), no se fuerza una reconexión innecesaria; solo
        // se dispara si la carga realmente se queda "colgada" sin progreso por 60s seguidos.
        resetConnectionTimer(60);
        emitStatus();
        io.emit('log', { msg: `Cargando WhatsApp: ${percent}% (${message || 'Sincronizando'})`, type: 'info' });
        if (Number(percent) >= 99) scheduleReadinessProbe(whatsappClient);
    });

    whatsappClient.on('ready', async () => {
        if (!isCurrentClient()) return;
        console.log("⏳ Evento 'ready' recibido. Esperando hidratación del Store...");
        await finishWhatsAppReadiness(whatsappClient, 'evento ready');
    });

    whatsappClient.on('auth_failure', (msg) => {
        if (!isCurrentClient()) return;
        console.error("❌ Fallo de autenticación:", msg);
        clearReadinessProbe();
        clearConnectionTimer();
        botState = 'AUTH_FAILURE';
        qrData = null;
        storeReady = false;
        emitStatus();
        io.emit('log', { msg: `❌ Fallo de autenticación: ${parseErrorMessage(msg)}`, type: 'error' });
        emitSystemAlert('error', 'No fue posible autenticar WhatsApp.', parseErrorMessage(msg));
    });

    whatsappClient.on('disconnected', (reason) => {
        if (!isCurrentClient()) return;
        console.log("❌ Desconectado:", reason);
        clearReadinessProbe();
        clearConnectionTimer();
        botState = 'DISCONNECTED';
        qrData = null;
        storeReady = false;
        cachedChatsList = [];
        chatsLoadPromise = null;
        lastConnectionError = reason || 'Pérdida de conexión';
        emitStatus();
        io.emit('log', { msg: `WhatsApp desconectado: ${reason || 'Pérdida de conexión'}`, type: 'error' });
        emitSystemAlert('error', 'WhatsApp se desconectó.', reason || 'Pérdida de conexión');
        scheduleReconnect(reason, whatsappClient);
    });

    return whatsappClient;
}

createWhatsAppClient();

// ============================================================
// GESTIÓN DE SOCKETS
// ============================================================

io.on('connection', (socket) => {
    console.log('🖥️ Cliente conectado al panel.');
    
    emitStatus(socket);
    socket.emit('config_data', currentConfig);

    socket.on('get_status', () => {
        emitStatus(socket);
    });

    socket.on('get_config', () => {
        socket.emit('config_data', currentConfig);
    });

    socket.on('force_qr', async () => {
        io.emit('log', { msg: 'Generando nuevo código QR...', type: 'info' });
        await forceNewQR();
    });

    socket.on('restart_whatsapp', async () => {
        try {
            io.emit('log', { msg: 'Reiniciando cliente de WhatsApp...', type: 'info' });
            await forceNewQR();
        } catch (err) {
            io.emit('log', { msg: `Error reiniciando: ${parseErrorMessage(err)}`, type: 'error' });
        }
    });

    socket.on('logout_whatsapp', async () => {
        try {
            io.emit('log', { msg: 'Cerrando sesión de WhatsApp...', type: 'info' });
            clearConnectionTimer();
            if (client) {
                await client.logout().catch(() => {});
                await client.destroy().catch(() => {});
                await new Promise(r => setTimeout(r, 2000)); // Retraso crítico para liberar memoria/puertos
            }
            botState = 'DISCONNECTED';
            qrData = null;
            storeReady = false;
            cachedChatsList = [];
            chatsLoadPromise = null;
            emitStatus();
            createWhatsAppClient();
            await client.initialize().catch(err => console.error("Error al re-inicializar:", err));
        } catch (err) {
            io.emit('log', { msg: `Error al cerrar sesión: ${parseErrorMessage(err)}`, type: 'error' });
        }
    });

    socket.on('update_config', async (newConfig) => {
        try {
            diaryService.updateSettings(newConfig);
            if (newConfig.lmStudioUrl) currentConfig.lmStudioUrl = newConfig.lmStudioUrl;
            if (newConfig.modelName) currentConfig.modelName = newConfig.modelName;
            if (newConfig.apiKey) currentConfig.apiKey = newConfig.apiKey;
            if (Object.prototype.hasOwnProperty.call(newConfig, 'audioOutputPath')) {
                currentConfig.audioOutputPath = normalizeAudioOutputPath(newConfig.audioOutputPath);
                getAudioOutputRoot();
            }
            if (newConfig.selectedGroups) currentConfig.selectedGroups = newConfig.selectedGroups;

            if (dbConnected) {
                await pool.query(
                    'UPDATE configuracion SET apiKey = ?, lmStudioUrl = ?, modelName = ?, audioOutputPath = ? WHERE id = 1',
                    [currentConfig.apiKey, currentConfig.lmStudioUrl, currentConfig.modelName, currentConfig.audioOutputPath]
                );
                if (newConfig.selectedGroups) {
                    await pool.query('DELETE FROM grupos_seleccionados');
                    for (const groupName of newConfig.selectedGroups) {
                        await pool.query('INSERT INTO grupos_seleccionados (nombre) VALUES (?)', [groupName]);
                    }
                }
            }
            saveLocalConfig();
            io.emit('config_data', currentConfig);
            io.emit('log', { msg: 'Configuración guardada.', type: 'success' });
        } catch (error) {
            io.emit('log', { msg: `Error al guardar configuración: ${parseErrorMessage(error)}`, type: 'error' });
            socket.emit('diary_settings_result', {error:parseErrorMessage(error)});
        }
    });

    socket.on('start_whisper', () => {
        if (!isWhisperInputConfigured()) {
            return socket.emit('whisper_job_result', { status: 'error', message: `Whisper procesa los archivos de ${DEFAULT_AUDIO_OUTPUT_PATH}. Guarde esa misma ruta antes de iniciarlo.` });
        }
        if (extractionJobActive) {
            return socket.emit('whisper_job_result', { status: 'error', message: 'Espere a que termine la extracción antes de transcribir.' });
        }
        if (whisperProcess && whisperProcess.exitCode === null && !whisperProcess.killed) {
            return socket.emit('whisper_job_result', { status: 'running', message: 'Whisper ya está procesando la carpeta input.' });
        }
        try {
            whisperProcess = whisperWorkflow.start(getAudioOutputRoot(), WHISPER_OUTPUT_PATH, result => {
                whisperProcess = null;
                const failed = result.error || result.code !== 0;
                const message = failed
                    ? `Whisper terminó con un error (código ${result.code ?? 'desconocido'}). ${result.error?.message || result.tail || 'No se recibió salida.'} Registro: ${result.logPath}`
                    : 'Whisper terminó correctamente. Ahora use “3. Unir transcripciones”.';
                io.emit('whisper_job_result', { status: failed ? 'error' : 'completed', message, logPath: result.logPath });
                io.emit('log', { msg: message, type: failed ? 'error' : 'success' });
            });
            io.emit('whisper_job_result', { status: 'started', message: 'Whisper está preparando la transcripción. Los archivos sueltos anteriores de output se archivarán automáticamente.', logPath: whisperWorkflow.logPath });
        } catch (error) {
            whisperProcess = null;
            socket.emit('whisper_job_result', { status: 'error', message: `No se pudo iniciar Whisper: ${parseErrorMessage(error)}` });
        }
    });

    socket.on('consolidate_whisper_tsv', async (options = {}) => {
        if (consolidationJobActive) {
            return socket.emit('whisper_transcript_ready', { error: 'Ya hay una consolidación en curso. Espere a que finalice.' });
        }
        try {
            consolidationJobActive = true;
            const transcript = await consolidateWhisperTranscripts(progress => {
                socket.emit('consolidation_progress', progress);
            }, options);
            socket.emit('whisper_transcript_ready', transcript);
            io.emit('log', { msg: `📄 ${transcript.fileCount} TSV consolidados en transcripcion_consolidada.txt.`, type: 'success' });
        } catch (error) {
            const message = parseErrorMessage(error);
            socket.emit('whisper_transcript_ready', { error: message });
            io.emit('log', { msg: `No se pudo consolidar los TSV: ${message}`, type: 'error' });
        } finally {
            consolidationJobActive = false;
        }
    });

    socket.on('analyze_whisper_transcript', async (data = {}) => {
        const startedAt = Date.now();
        try {
            const result = await diaryService.generate(data.instruction || '', progress => socket.emit('diary_generation_progress',progress));
            socket.emit('whisper_analysis_result',{result,error:null,durationMs:Date.now()-startedAt});
        } catch(error) {
            socket.emit('whisper_analysis_result',{error:'No se pudo generar el diario: '+parseErrorMessage(error),durationMs:Date.now()-startedAt});
        }
    });

    socket.on('test_lmstudio', async (configTest) => {
        const targetUrl = getNormalizedLmStudioUrl(configTest?.lmStudioUrl || currentConfig.lmStudioUrl);
        const startedAt = Date.now();
        try {
            const openai = new OpenAI({ baseURL: targetUrl, apiKey: 'lm-studio', timeout: 15000, maxRetries: 0 });
            const modelsList = await openai.models.list();
            const availableModels = modelsList.data.map(m => m.id);
            const elapsedMs = Date.now() - startedAt;
            socket.emit('test_lmstudio_result', { 
                success: true, 
                msg: `Conexión exitosa en ${(elapsedMs / 1000).toFixed(2)} s. Modelos: ${availableModels.join(', ') || 'ninguno reportado'}`,
                elapsedMs,
                models: availableModels
            });
        } catch (err) {
            const elapsedMs = Date.now() - startedAt;
            socket.emit('test_lmstudio_result', { 
                success: false, 
                elapsedMs,
                msg: `Error conectando a ${targetUrl} después de ${(elapsedMs / 1000).toFixed(2)} s: ${parseErrorMessage(err)}` 
            });
            emitSystemAlert('warning', 'LM Studio no respondió a la prueba de conexión.', parseErrorMessage(err));
        }
    });

    socket.on('fetch_groups', async () => {
        if (botState !== 'READY') {
            return socket.emit('log', { msg: 'WhatsApp no está listo aún. Espere a que se complete la conexión.', type: 'error' });
        }
        try {
            socket.emit('log', { msg: 'Obteniendo lista de chats...', type: 'info' });
            const chats = await safeGetChats(4, 2000);
            const chatList = chats.map(c => ({ 
                id: c.id._serialized,
                name: c.name || "Sin Nombre", 
                isGroup: c.isGroup 
            }));
            socket.emit('groups_list', chatList);
            socket.emit('log', { msg: `✅ ${chatList.length} chats cargados correctamente.`, type: 'success' });
        } catch (error) {
            const cleanErr = parseErrorMessage(error);
            socket.emit('log', { msg: `Error obteniendo chats: ${cleanErr}`, type: 'error' });
        }
    });

    socket.on('extract_content', async (data = {}) => {
        const startedAt = Date.now();
        const emitExtractionProgress = details => socket.emit(
            'extraction_progress', progressPayload(startedAt, details)
        );
        const finish = (error = null, result = null) => {
            extractionJobActive = false;
            socket.emit('extraction_result', { error, result, durationMs: Date.now() - startedAt });
        };

        if (extractionJobActive) {
            return socket.emit('extraction_result', { error: 'Ya hay una extracción en curso. Espere a que finalice.' });
        }

        try {
            extractionJobActive = true;
            if (whisperProcess && whisperProcess.exitCode === null && !whisperProcess.killed) throw new Error('Espere a que Whisper termine antes de extraer otro lote.');
            if (botState !== 'READY') throw new Error('WhatsApp no está conectado o todavía está sincronizando.');

            const selectedChats = [...new Set((data.selectedChats || []).filter(Boolean))];
            const includeAudio = Boolean(data.includeAudio);
            const includeText = Boolean(data.includeText);
            const includeMedia = Boolean(data.includeMedia);
            if (!selectedChats.length) throw new Error('Seleccione al menos un grupo o chat individual.');
            if (!includeAudio && !includeText && !includeMedia) throw new Error('Seleccione al menos un tipo de contenido para extraer.');

            const { startTs, endTs } = dateRangeToTimestamps(data.startDate, data.endDate);
            const allChats = await safeGetChats(4, 2000);
            const chats = selectedChats
                .map(id => allChats.find(chat => chat.id && chat.id._serialized === id))
                .filter(Boolean);
            if (!chats.length) throw new Error('Los chats seleccionados ya no están disponibles. Actualice la lista y vuelva a seleccionar.');

            const stamp = new Date().toISOString().replace(/[:.]/g, '-');
            const exportFolder = getAudioOutputRoot();
            const textExportRoot = path.join(exportFolder, `textos_${stamp}`);
            const mediaExportRoot = path.join(exportFolder, `multimedia_${stamp}`);
            const manifestPath = path.join(exportFolder, `manifiesto_${stamp}.json`);

            const plan = [];
            const readErrors = [];
            for (let index = 0; index < chats.length; index++) {
                const chat = chats[index];
                emitExtractionProgress({
                    phase: 'reading', current: index + 1, total: chats.length,
                    percent: Math.round(((index + 0.25) / chats.length) * 35),
                    message: `Leyendo historial de ${chat.name || 'chat'} (${index + 1}/${chats.length})...`
                });
                try {
                    const history = await readExtractionHistory(chat, startTs, endTs);
                    const filtered = history.messages;
                    if (history.warning) readErrors.push((chat.name || chat.id._serialized) + ': ' + history.warning);
                    plan.push({ chat, messages: filtered });
                } catch (err) {
                    const message = `${chat.name || chat.id._serialized}: ${parseErrorMessage(err)}`;
                    readErrors.push(message);
                    io.emit('log', { msg: `No se pudo leer ${message}`, type: 'error' });
                }
            }

            if (!plan.length) throw new Error('No se pudo leer el historial de los chats seleccionados. ' + readErrors.join(' | '));
            const mediaCandidates = [];
            const textByChat = new Map();
            let textCount = 0;
            for (const entry of plan) {
                const textLines = [];
                for (const message of entry.messages) {
                    if (includeText && typeof message.body === 'string' && message.body.trim()) {
                        const when = new Date(message.timestamp * 1000).toLocaleString('es-CO');
                        textLines.push(`[${when}] ${getMessageAuthor(message)}: ${message.body.trim().replace(/\r?\n/g, ' ')}`);
                        textCount++;
                    }
                    if (hasExtractableMedia(message)) {
                        const audio = isAudioMessage(message);
                        if ((audio && includeAudio) || (!audio && includeMedia)) {
                            mediaCandidates.push({ chat: entry.chat, message, audio });
                        }
                    }
                }
                if (textLines.length) textByChat.set(entry.chat.id._serialized, textLines);
            }

            if (!mediaCandidates.length && !textByChat.size) throw new Error('No se encontró contenido del tipo seleccionado en el rango elegido.');
            emitExtractionProgress({ phase: 'archiving', percent: 35, message: 'Archivando archivos sueltos anteriores de input y output...' });
            const archives = whisperWorkflow.prepareExtraction(exportFolder, WHISPER_OUTPUT_PATH);
            if (archives.length) io.emit('log', { msg: 'Archivos anteriores conservados en: ' + archives.map(a => a.archive).join(' | '), type: 'info' });
            activeWhisperTranscriptPath = null;
            let savedAudio = 0;
            let savedMedia = 0;
            let failedFiles = 0;
            const fileErrors = [];
            const manifest = [];
            const totalWork = Math.max(mediaCandidates.length + textByChat.size, 1);
            let completedWork = 0;

            for (const [chatId, lines] of textByChat) {
                const chat = chats.find(item => item.id._serialized === chatId);
                const chatFolder = getChatExportFolder(textExportRoot, chat);
                await fs.promises.mkdir(chatFolder, { recursive: true });
                await fs.promises.writeFile(path.join(chatFolder, 'mensajes.txt'), lines.join('\n') + '\n', 'utf8');
                manifest.push({ chat: chat?.name || chatId, type: 'text', file: path.relative(exportFolder, path.join(chatFolder, 'mensajes.txt')), count: lines.length });
                completedWork++;
                emitExtractionProgress({
                    phase: 'saving_text', current: completedWork, total: totalWork,
                    percent: 35 + Math.round((completedWork / totalWork) * 65),
                    message: `Guardando mensajes de ${chat?.name || 'chat'}...`
                });
            }

            for (let index = 0; index < mediaCandidates.length; index++) {
                const { chat, message, audio } = mediaCandidates[index];
                emitExtractionProgress({
                    phase: 'downloading', current: index + 1, total: mediaCandidates.length,
                    percent: 35 + Math.round((completedWork / totalWork) * 65),
                    message: `Guardando ${audio ? 'audio' : 'archivo multimedia'} ${index + 1}/${mediaCandidates.length} de ${chat.name || 'chat'}...`
                });
                try {
                    const media = await Promise.race([
                        safeDownloadMedia(client, message, 2),
                        new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout al descargar el archivo (200s)')), 200000))
                    ]);
                    if (!media || !media.data) throw new Error('WhatsApp no entregó los datos del archivo.');

                    const isAudioFile = audio || isAudioMessage(message) || /^audio\//i.test(media.mimetype || '');
                    // Whisper examina únicamente los archivos en input/, no sus
                    // subcarpetas. Los audios se guardan por tanto directamente
                    // en la ruta configurada; el resto conserva su organización.
                    const chatFolder = isAudioFile ? exportFolder : getChatExportFolder(mediaExportRoot, chat);
                    fs.mkdirSync(chatFolder, { recursive: true });
                    const time = new Date(message.timestamp * 1000).toISOString().replace(/[:.]/g, '-');
                    const fileMessageId = safeFileSegment(getSerializedMessageId(message), String(index + 1));
                    const extension = extensionFromMime(media.mimetype || message.mimetype, isAudioFile);
                    const filename = `${isAudioFile ? 'audio' : 'multimedia'}_${time}_${fileMessageId}.${extension}`;
                    const filePath = path.join(chatFolder, filename);
                    fs.writeFileSync(filePath, Buffer.from(media.data, 'base64'));
                    manifest.push({
                        chat: chat.name || chat.id._serialized,
                        type: isAudioFile ? 'audio' : 'multimedia',
                        file: path.relative(exportFolder, filePath),
                        mimeType: media.mimetype || message.mimetype || 'application/octet-stream',
                        timestamp: new Date(message.timestamp * 1000).toISOString()
                    });
                    if (isAudioFile) savedAudio++; else savedMedia++;
                } catch (err) {
                    failedFiles++;
                    const errorText = `${chat.name || chat.id._serialized}: ${parseErrorMessage(err)}`;
                    fileErrors.push(errorText);
                    io.emit('log', { msg: `No se pudo guardar un archivo de ${errorText}`, type: 'error' });
                }
                completedWork++;
            }

            const result = {
                folder: exportFolder,
                outputFolder: exportFolder,
                manifestPath,
                relativeFolder: path.relative(__dirname, exportFolder),
                chatsProcessed: plan.length,
                scannedMessages: plan.reduce((total, entry) => total + entry.messages.length, 0),
                textCount,
                savedAudio,
                savedMedia,
                failedFiles,
                partial: readErrors.length > 0 || failedFiles > 0,
                errors: [...readErrors, ...fileErrors],
                historyLimit: MAX_MESSAGES_PER_CHAT,
                archives
            };
            fs.writeFileSync(manifestPath, JSON.stringify({
                generatedAt: new Date().toISOString(), range: { startDate: data.startDate, endDate: data.endDate }, result, files: manifest
            }, null, 2), 'utf8');
            emitExtractionProgress({ phase: 'complete', current: totalWork, total: totalWork, percent: 100, message: 'Extracción terminada.' });
            io.emit('log', { msg: `✅ Extracción terminada: ${savedAudio} audios, ${textCount} textos y ${savedMedia} archivos multimedia.`, type: 'success' });
            finish(null, result);
        } catch (error) {
            const cleanErr = parseErrorMessage(error);
            console.error('❌ Error en extracción:', cleanErr);
            io.emit('log', { msg: `Error en extracción: ${cleanErr}`, type: 'error' });
            emitSystemAlert('error', 'La extracción de WhatsApp no se pudo completar.', cleanErr);
            finish(`Fallo en la extracción: ${cleanErr}`);
        }
    });

    socket.on('generate_summary', async (data = {}) => {
        const { startDate, endDate } = data;
        const selectedGroups = data.selectedChats || data.selectedGroups || [];
        const startedAt = Date.now();
        const finish = (error = null, result = null, transcript = null) => socket.emit('summary_result', {
            error, result, transcript, durationMs: Date.now() - startedAt
        });

        try {
            if (botState !== 'READY') throw new Error("WhatsApp no está conectado.");
            if (!selectedGroups || selectedGroups.length === 0) throw new Error("Seleccione al menos un grupo o contacto.");
            const { startTs, endTs } = dateRangeToTimestamps(startDate, endDate);

            io.emit('log', { msg: `Analizando ${selectedGroups.length} chats seleccionados...`, type: 'info' });
            
            const allChats = await safeGetChats(4, 2000);
            let transcript = "";

            for (const name of selectedGroups) {
                const chat = allChats.find(c => c.name === name || c.id._serialized === name);
                if (chat) {
                    io.emit('log', { msg: `Leyendo historial: ${chat.name}...`, type: 'info' });
                    
                    let messages = [];
                    for (let attempt = 1; attempt <= 3; attempt++) {
                        try {
                            messages = await Promise.race([
                                chat.fetchMessages({ limit: 800 }),
                                new Promise((_, reject) => setTimeout(() => reject(new Error('Timeout al leer historial')), 15000))
                            ]);
                            break;
                        } catch (e) {
                            const cleanErr = parseErrorMessage(e);
                            if (attempt < 3) {
                                io.emit('log', { msg: `Reintentando leer ${chat.name} (${attempt}/3): ${cleanErr}`, type: 'info' });
                                await new Promise(r => setTimeout(r, 2000 * attempt));
                            } else {
                                io.emit('log', { msg: `No se pudo leer ${chat.name} después de 3 intentos. Omitiendo.`, type: 'info' });
                                messages = [];
                            }
                        }
                    }

                    // [REPARACIÓN]: Cambiamos "m.type === 'chat'" por comprobación de m.body para incluir texto en imágenes o audios citados
                    const filtered = messages.filter(m => 
                        m.timestamp >= startTs && 
                        m.timestamp <= endTs && 
                        m.body && 
                        typeof m.body === 'string' &&
                        m.body.trim().length > 0
                    );
                    
                    if (filtered.length > 0) {
                        transcript += `\n### CHAT: ${chat.name} (${chat.isGroup ? 'GRUPO' : 'INDIVIDUAL'}) ###\n`;
                        filtered.forEach(m => {
                            const timeStr = new Date(m.timestamp * 1000).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            
                            // [REPARACIÓN]: Mejor extracción del nombre de usuario si no está guardado el contacto
                            const author = getMessageAuthor(m);
                            
                            const cleanText = m.body.replace(/\n+/g, ' ').trim();
                            transcript += `[${timeStr}] ${author}: ${cleanText}\n`;
                        });
                    }
                }
            }

            if (!transcript.trim()) return finish('No se encontraron mensajes en el rango seleccionado.');

            const normalizedUrl = getNormalizedLmStudioUrl(currentConfig.lmStudioUrl);
            io.emit('log', { msg: `Enviando a LMStudio (${currentConfig.modelName})...`, type: 'info' });

            const openai = new OpenAI({ baseURL: normalizedUrl, apiKey: 'lm-studio', timeout: 120000, maxRetries: 0 });

            const systemContent = "Eres un asistente administrativo y pedagógico experto para la IER Josefina. Procesa y analiza las conversaciones con máxima claridad, estructuración profesional en Markdown con viñetas y tono institucional en español.";
            const userContent = `Genera un reporte ejecutivo estructurado en Markdown (Resumen General, Temas Tratados, Acuerdos/Decisiones y Tareas Pendientes) de estos chats del ${startDate} al ${endDate}:\n${transcript}`;

            const completion = await openai.chat.completions.create({
                model: currentConfig.modelName,
                messages: [
                    { role: "system", content: systemContent },
                    { role: "user", content: userContent }
                ],
                temperature: 0.7
            });

            const summary = completion.choices[0].message.content;

            if (dbConnected) {
                try {
                    await pool.query(
                        'INSERT INTO resumenes_historicos (fecha_inicio, fecha_fin, grupos_analizados, contenido_resumen) VALUES (?, ?, ?, ?)',
                        [startDate, endDate, selectedGroups.join(', '), summary]
                    );
                } catch (dbErr) {
                    console.error("⚠️ Error guardando en DB:", dbErr.message);
                }
            }

            io.emit('log', { msg: '✅ Resumen finalizado.', type: 'success' });
            finish(null, summary, transcript);

        } catch (error) {
            const cleanErr = parseErrorMessage(error);
            console.error("❌ Error en resumen:", cleanErr);
            finish(`Fallo en el proceso: ${cleanErr}`);
        }
    });

    socket.on('ask_custom_instruction', async (data = {}) => {
        const { customInstruction, transcript, context } = data;
        const startedAt = Date.now();
        const finish = (error = null, result = null) => socket.emit('custom_instruction_result', { error, result, durationMs: Date.now() - startedAt });

        try {
            if (!customInstruction || !customInstruction.trim()) throw new Error("Debe ingresar una instrucción.");
            
            const normalizedUrl = getNormalizedLmStudioUrl(currentConfig.lmStudioUrl);
            io.emit('log', { msg: `Procesando instrucción con LMStudio (${currentConfig.modelName})...`, type: 'info' });

            const openai = new OpenAI({ baseURL: normalizedUrl, apiKey: 'lm-studio', timeout: 120000, maxRetries: 0 });

            const systemPrompt = "Eres un asistente de IA avanzado para la IER Josefina. Analiza detalladamente la información proporcionada y responde a la solicitud específica del usuario con precisión, estructura clara en Markdown y lenguaje institucional.";
            
            let userPrompt = `Basándote en la siguiente transcripción y contexto de conversaciones de WhatsApp:\n\n`;
            if (transcript && transcript.trim()) {
                userPrompt += `--- TRANSCRIPCIÓN ---\n${transcript.trim()}\n\n`;
            }
            if (context && context.trim()) {
                userPrompt += `--- RESUMEN PREVIO ---\n${context.trim()}\n\n`;
            }
            userPrompt += `--- INSTRUCCIÓN DEL USUARIO ---\n${customInstruction.trim()}`;

            const completion = await openai.chat.completions.create({
                model: currentConfig.modelName,
                messages: [
                    { role: "system", content: systemPrompt },
                    { role: "user", content: userPrompt }
                ],
                temperature: 0.7
            });

            const result = completion.choices[0].message.content;
            io.emit('log', { msg: '✅ Respuesta de IA completada.', type: 'success' });
            finish(null, result);

        } catch (error) {
            const cleanErr = parseErrorMessage(error);
            console.error("❌ Error en instrucción:", cleanErr);
            finish(`Error: ${cleanErr}`);
        }
    });
});

// ============================================================
// INICIAR SISTEMA
// ============================================================
initDatabase().then(() => {
    server.listen(3000, () => {
        console.log('🚀 Servidor en http://localhost:3000 | LMStudio: ' + currentConfig.lmStudioUrl);
        client.initialize().catch(err => console.error("Error al arrancar WhatsApp:", err));
    });
});
