async (page) => {
  const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('file:///D:/xampp/htdocs/guagua/output/diagnostico-guagua/Mapa_interactivo_Guagua.html');
  const opened=page.context().waitForEvent('page');
  await page.locator('[data-node="plataforma"]').dblclick();
  const popup=await opened;
  await popup.locator('[data-node="institucion"]').waitFor();
  if(!popup.url().startsWith('file:///')||!popup.url().includes('proceso=plataforma'))throw Error('No se abrió la rama en otra ventana local');
  await popup.close();
  await page.locator('[data-node="familias"]').click();
  await page.locator('[data-node="matricula"]').click();
  await page.getByRole('heading',{name:'Matrícula anual',exact:true}).waitFor();
  if(await page.locator('#gm-detail h2').textContent()!=='Matrícula anual')throw Error('La copia autónoma no actualiza el detalle');
  if(!(await page.locator('#gm-detail a').first().getAttribute('href')).startsWith('http://localhost/guagua/'))throw Error('Base de enlaces incorrecta');
  if(errors.length)throw Error(errors.join('\n'));
  await page.locator('[data-overview]').click();
  return {archivoAutonomo:'correcto',dobleClicEnVentanaNueva:'correcto',interacciones:'correctas',destinoDeModulos:'http://localhost/guagua/',errores:errors};
}
