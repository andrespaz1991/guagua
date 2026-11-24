from pathlib import Path
import json,subprocess
from PIL import Image,ImageOps,ImageDraw
root=Path(__file__).parent
exe=Path(r'C:\Users\Andres\.cache\codex-runtimes\codex-primary-runtime\dependencies\native\poppler\Library\bin\pdftoppm.exe')
m=json.loads((root/'manifest.json').read_text(encoding='utf-8'))
out=root/'revision-visual'; out.mkdir(exist_ok=True)
for prefix in ['MT','CS']:
    panels=[]
    for g in range(6,10):
        p=next(x['source'] for x in m if Path(x['source']).name==f'{prefix}_Grado{g}.pdf')
        tiles=[]
        for page in [7,8]:
            target=out/f'{prefix}-{g}-{page}'
            subprocess.run([str(exe),'-f',str(page),'-l',str(page),'-singlefile','-scale-to','1000','-png',p,str(target)],check=True,stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
            tiles.append(Image.open(target.with_suffix('.png')).convert('RGB'))
        row=Image.new('RGB',(tiles[0].width+tiles[1].width,tiles[0].height+30),'white')
        ImageDraw.Draw(row).text((10,8),f'{prefix} grado {g} - indice - paginas PDF 7 y 8',fill='black')
        x=0
        for tile in tiles: row.paste(tile,(x,30)); x+=tile.width
        panels.append(row)
    sheet=Image.new('RGB',(max(p.width for p in panels),sum(p.height for p in panels)),'white')
    y=0
    for panel in panels: sheet.paste(panel,(0,y)); y+=panel.height
    sheet.save(out/f'indices-{prefix}.png')
    print(out/f'indices-{prefix}.png')
