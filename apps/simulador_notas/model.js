(function(root) {
  'use strict';
  function calculate(row, p4, weights, target) {
    const values = [row.p1,row.p2,row.p3,p4];
    if (weights.some(w=>!Number.isFinite(w)||w<0) || weights[3]<=0 || Math.abs(weights.reduce((a,b)=>a+b,0)-100)>0.00001 || !Number.isFinite(target)||target<1||target>5) return {valid:false,final:null,required:null,status:'pending'};
    const history = values.slice(0,3).every((v,i)=>weights[i]===0 || (Number.isFinite(v)&&v>=1&&v<=5));
    const accumulated = history ? values.slice(0,3).reduce((sum,v,i)=>sum+(v??0)*weights[i]/100,0) : null;
    const required=history?(target-accumulated)/(weights[3]/100):null;
    const complete=history&&Number.isFinite(p4)&&p4>=1&&p4<=5;
    const final=complete?accumulated+p4*weights[3]/100:null;
    return {valid:true,final,required,status:final===null?'pending':final+1e-9>=target?'pass':'risk'};
  }
  // El acumulado es el aporte al 100 % del año; el promedio normaliza solo P1–P3.
  // Usar el acumulado como nota actual marcaría en riesgo incluso un promedio de 3,5.
  function annualRisk(row, p4, weights, target) {
    const calculation = calculate(row, p4, weights, target);
    const missingPeriods = [1,2,3].filter(i => weights[i-1] > 0 && (!Number.isFinite(row['p'+i]) || row['p'+i] < 1 || row['p'+i] > 5));
    const historyWeight = weights.slice(0,3).reduce((sum, weight) => sum + weight, 0);
    const accumulated = calculation.valid && !missingPeriods.length
      ? [1,2,3].reduce((sum, i) => sum + (row['p'+i] ?? 0) * weights[i-1] / 100, 0) : null;
    const historyAverage = accumulated !== null && historyWeight > 0 ? accumulated / (historyWeight / 100) : null;
    const invalidP4 = p4 !== null && p4 !== undefined && (!Number.isFinite(p4) || p4 < 1 || p4 > 5);
    const grade = calculation.valid && !invalidP4 ? (calculation.final ?? historyAverage) : null;
    return {
      ...calculation, accumulated, historyAverage, historyWeight, missingPeriods, grade,
      basis: grade === null ? 'missing' : calculation.final !== null ? 'final' : 'history',
      status: grade === null ? 'incomplete' : grade + 1e-9 < target ? 'risk' : 'pass',
      gap: grade === null ? null : Math.max(0, target - grade)
    };
  }
  function resolveP4(row, scenarios = {}) {
    const value = Object.prototype.hasOwnProperty.call(scenarios, row.id) ? scenarios[row.id] : row.p4;
    return Number.isFinite(value) && value >= 1 && value <= 5 ? value : null;
  }
  function degree(row) {
    if (Number.isInteger(row.degree)) return row.degree;
    const group=String(row.group||'');
    const match=/^\d+\s+Completa\s+(\d+)\s+\d+\s+20\d{2}$/i.exec(group)||/^(?:Grado\s*)?(6|7|8|9|10|11)(?:\s*[-°ºA-Za-z(].*)?$/i.exec(group);
    return match?Number(match[1]):null;
  }
  /** Los escenarios no completan las notas ausentes del PDF de referencia. */
  function missingNotes(records, source = {}) {
    const result = [];
    for (const row of records) {
      for (let period = 1; period <= 4; period++) {
        const value = row['p' + period];
        if (Number.isFinite(value) && value >= 1 && value <= 5) continue;
        const absent = value === null || value === undefined || value === '';
        result.push({id:row.id+'|'+period,record:row,period,status:absent?'missing':'invalid',
          reason:absent?'Nota no registrada en el PDF':'Nota fuera de la escala de 1,00 a 5,00',
          source:source.source||'PDF de referencia',page:row.source_page||null});
      }
    }
    return result;
  }
  /** La ficha promedia solo finales completas; las materias pendientes no valen cero. */
  function studentReport(records, weights, target) {
    const valid=value=>Number.isFinite(value)&&value>=1&&value<=5;
    const subjects=records.map(record=>({record,result:calculate(record,record.p4,weights,target)}));
    const finals=subjects.map(entry=>entry.result.final).filter(Number.isFinite);
    const periods=[1,2,3,4].map(period=>{
      const values=records.map(record=>record['p'+period]).filter(valid);
      return {count:values.length,average:values.length?values.reduce((a,b)=>a+b,0)/values.length:null};
    });
    return {subjects,periods,total:records.length,complete:finals.length,pending:records.length-finals.length,
      average:finals.length?finals.reduce((a,b)=>a+b,0)/finals.length:null,
      risks:subjects.filter(entry=>entry.result.status==='risk').length};
  }
  const api={calculate,annualRisk,resolveP4,degree,missingNotes,studentReport};
  if(typeof module!=='undefined') module.exports=api;
  root.NotasModel=api;
})(typeof globalThis!=='undefined'?globalThis:this);
