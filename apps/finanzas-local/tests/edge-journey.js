async (page) => {
  const checks=[];
  const assert=(ok,name)=>{if(!ok)throw Error(name);checks.push(name);};
  const root='D:/xampp/htdocs/guagua/apps/finanzas-local/tests/fixtures/';
  await page.locator('#month').selectOption('2027-01');
  await page.getByRole('navigation').getByRole('button',{name:'Documentos y análisis',exact:false}).click();
  await page.locator('#pdfFiles').setInputFiles(root+'sin-texto-demo.pdf');
  await page.locator('#upload').click();
  await page.waitForSelector('#documentList [data-analyze]');
  await page.locator('#documentList [data-analyze]').click();
  await page.waitForSelector('#review[open]');
  assert(await page.locator('#reviewRows tr').count()===0, 'Un PDF vacío no genera movimientos ficticios');
  assert((await page.locator('#reviewWarnings').textContent()).includes('No se ejecutó OCR'), 'La revisión informa la ausencia de OCR');
  await page.locator('#addReviewRow').click();
  await page.locator('#reviewRows [data-k=description]').fill('Mercado transcrito del comprobante');
  await page.locator('#reviewRows [data-k=amount]').fill('100000');
  await page.locator('#reviewRows [data-k=kind]').selectOption('gasto');
  await page.locator('#reviewRows [data-k=category]').selectOption('alimentacion');
  await page.locator('#reviewOpening').fill('1000000');
  await page.locator('#reviewClosing').fill('900000');
  await page.locator('#verified').check();
  await page.locator('#applyReview').click();
  await page.waitForSelector('#reconcile:not([hidden])');
  const m=await page.evaluate(()=>Horizonte.getState().months[2]);
  assert(m.expenses.alimentacion===100000&&m.ledger.length===1, 'Se puede completar manualmente y aplicar un PDF sin texto');
  await page.locator('[data-confirm-account]').click();
  assert((await page.evaluate(()=>Horizonte.getState().months[2])).accounts[0].confirmed,'Los movimientos transcritos también se concilian');
  const security=await page.evaluate(async()=>{
    const a=await fetch('/api/analyze',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({id:'../../server.py'})});
    const b=await fetch('/server.py');
    const c=await fetch('/documentos/2027-01/archivo.pdf');
    return {badId:a.status,source:b.status,direct:c.status};
  });
  assert(security.badId===400&&security.source===404&&security.direct===404,'El servicio bloquea rutas arbitrarias y el código del servidor');
  await page.getByRole('navigation').getByRole('button',{name:'Deudas y créditos',exact:false}).click();
  await page.locator('#loanAmount').fill('100000');
  await page.locator('#loanRate').fill('22');
  await page.locator('#loanTerm').fill('24');
  await page.locator('#loanExtra').fill('100000');
  await page.locator('#simulateLoan').click();
  assert((await page.locator('#loanResult').textContent()).includes('1 mes'),'El extra que liquida el crédito limita el pago al saldo restante');
  const validation=await page.evaluate(()=>{
    const checks=[];
    for(const change of [s=>s.months[0].income.salario=-10,s=>s.months[0].debts[0].rate=Infinity,s=>s.months[1].key=s.months[0].key,s=>s.months[0].accounts[0].id=null]){
      const s=Horizonte.getState();change(s);try{Horizonte.validateState(s);checks.push(false);}catch{checks.push(true);}
    }
    return checks.every(Boolean);
  });
  assert(validation,'Los respaldos rechazan valores negativos, tasas no finitas y estructura inconsistente');
  await page.locator('#month').selectOption('2026-11');
  await page.getByRole('navigation').getByRole('button',{name:'Vista general',exact:false}).click();
  const result={passed:checks.length,checks};
  await page.evaluate(r=>{window.__edgeResult=r;},result);
  return result;
}
