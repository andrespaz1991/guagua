/* Funciones compartidas con el script exportado. Sin dependencias externas. */
(function (root) {
    'use strict';
    function createCore() {
        const normalize = value => String(value ?? '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, ' ').trim();
        const lines = value => [...new Set(String(value || '').split(/\r?\n/).map(s => s.trim()).filter(Boolean))];
        const tokens = value => normalize(value).split(' ').filter(s => s.length > 2 && !['los','las','del','una','uno','con','para','por','que','sus'].includes(s));
        function similarity(a, b) {
            const x = normalize(a), y = normalize(b);
            if (!x || !y) return 0;
            if (x === y) return 1;
            // La negación no se elimina como palabra vacía.
            if (/\b(no|nunca|sin)\b/.test(x) !== /\b(no|nunca|sin)\b/.test(y)) return 0;
            const tx = new Set(tokens(x)), ty = new Set(tokens(y));
            const overlap = [...tx].filter(t => ty.has(t)).length;
            const wordScore = tx.size + ty.size ? 2 * overlap / (tx.size + ty.size) : 0;
            const pairs = text => { const result = new Map(); for (let i=0;i<text.length-1;i++) { const key=text.slice(i,i+2); result.set(key,(result.get(key)||0)+1); } return result; };
            const px=pairs(x), py=pairs(y);
            const common=[...px].reduce((n,[k,v])=>n+Math.min(v,py.get(k)||0),0);
            const charScore=2*common/Math.max(1,x.length+y.length-2);
            return Math.min(.99, .7*wordScore+.3*charScore);
        }
        function rank(query, catalog, mode='similar', threshold=75) {
            const candidates = catalog.map((item,index)=>({index,item,score:similarity(query,item.text)})).sort((a,b)=>b.score-a.score);
            const best=candidates[0], minimum=mode==='exact'?1:threshold/100;
            if (!best || best.score<minimum) return {status:'missing', candidates, selected:-1};
            const ambiguous=candidates[1] && best.score-candidates[1].score<.06;
            return {status:ambiguous?'ambiguous':best.score===1?'exact':'similar',candidates,selected:ambiguous?-1:best.index};
        }
        function fromUrl(value) {
            const url=new URL(value);
            if(url.protocol!=='https:'||!/(^|\.)sisga\.com\.co$/i.test(url.hostname))throw new Error('Usa un enlace HTTPS de sisga.com.co.');
            return {subject:url.searchParams.get('sAsi')||'',group:url.searchParams.get('sGru')||'',period:url.searchParams.get('sPer')||url.searchParams.get('Per')||''};
        }
        function validate(raw) {
            if (!raw || raw.schema!=='guagua.sisga.indicadores' || raw.version!==1) throw new Error('El archivo no es un perfil de indicadores SISGA compatible (versión 1).');
            const str=(v,max=2000)=> { if(typeof v!=='string'||v.length>max) throw new Error('El perfil contiene texto inválido o demasiado largo.'); return v.trim(); };
            const arr=(v,max=500)=> { if(!Array.isArray(v)||v.length>max) throw new Error('El perfil contiene una lista inválida o demasiado extensa.'); return v; };
            const strings=(v,max)=>arr(v,max).map(s=>str(s));
            const meta=raw.metadata||{}, students=raw.students||{}, matching=raw.matching||{}, selectors=raw.selectors||{}, targets=raw.targets||{};
            if (!['all','list'].includes(students.mode)) throw new Error('Selecciona todos los estudiantes o una lista.');
            if (!['exact','similar'].includes(matching.mode) || !Number.isFinite(matching.threshold) || matching.threshold<50 || matching.threshold>100) throw new Error('La similitud debe estar entre 50 y 100.');
            const catalog={};
            for(const type of ['strengths','difficulties']) catalog[type]=arr(raw.catalog?.[type]||[],3000).map(item=>({text:str(item.text),value:str(item.value??''),selected:!!item.selected}));
            const year=Number(meta.year);
            if(!Number.isInteger(year)||year<2000||year>2200) throw new Error('El año debe estar entre 2000 y 2200.');
            const sourceUrl=str(raw.sourceUrl||'',3000);if(sourceUrl)fromUrl(sourceUrl);
            return {schema:raw.schema,version:1,id:str(raw.id||'',120),name:str(raw.name||'',120),sourceUrl,metadata:{subject:str(meta.subject||'',120),group:str(meta.group||'',80),period:str(meta.period||'',40),year},students:{mode:students.mode,names:strings(students.names||[],3000)},targets:{strengths:strings(targets.strengths||[],100),difficulties:strings(targets.difficulties||[],100)},matching:{mode:matching.mode,threshold:matching.threshold},selectors:{all:str(selectors.all||'',500),students:str(selectors.students||'',500),strengths:str(selectors.strengths||'',500),difficulties:str(selectors.difficulties||'',500)},catalog};
        }
        return {normalize,lines,similarity,rank,validate,fromUrl};
    }
    root.SisgaIndicatorCoreFactory=createCore;
    root.SisgaIndicatorCore=createCore();
})(typeof window!=='undefined'?window:globalThis);
