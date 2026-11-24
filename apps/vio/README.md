# 🔍 VisoSearch – Búsqueda Visual de Imágenes con IA

Aplicación web que usa **OpenAI CLIP** para buscar imágenes por descripción en lenguaje natural.

## 🚀 Inicio rápido

### 1. Instalar dependencias (solo primera vez)
Doble clic en `instalar_dependencias.bat` o ejecuta:
```bash
pip install flask flask-cors Pillow pillow-heif openai-clip torch torchvision
```

### 2. Iniciar el servidor
Doble clic en `iniciar.bat` o ejecuta:
```bash
python app.py
```

### 3. Abrir la app
Ve a: **http://localhost:5000**

---

## 💡 Cómo usar

1. **Carga una carpeta** – Escribe la ruta completa de tu carpeta de fotos (ej: `C:\Users\Andres\Pictures`)
2. **Escribe qué buscas** – En lenguaje natural: `"persona con sudadera roja"`, `"perro en playa"`, etc. Para ampliar una categoría usa sinónimos separados con `;`, por ejemplo: `"estudiante con sudadera; alumno con hoodie; persona con buzo deportivo"`.
3. **Ajusta el umbral** – Valor más bajo = más resultados (menos estricto). Valor más alto = más preciso.
4. **Busca** – La IA analiza cada imagen y muestra las que coinciden con tu descripción.
5. **Exporta** – Selecciona las fotos que quieres y cópialas a una carpeta destino. Para colecciones de iCloud se recomienda **Copiar** antes de usar **Mover**.

## ⚡ Rendimiento e iCloud

- La primera búsqueda crea un índice local de embeddings. Las siguientes búsquedas reutilizan las fotos que no hayan cambiado y solo analizan las nuevas o modificadas.
- El índice se guarda fuera de la biblioteca de fotos, en `%LOCALAPPDATA%\VisoSearch\cache`, por lo que no se sincroniza con iCloud.
- Se admiten archivos HEIC/HEIF (instalando `pillow-heif`), habituales en iPhone.
- Una carpeta de Windows no es un álbum de Fotos de iCloud. La app puede copiar o mover archivos a una carpeta local o sincronizada; para agregar esos archivos a un álbum de iCloud hay que hacerlo desde iCloud para Windows, iCloud.com o Fotos de Apple.

---

## 🧠 Tecnología

- **OpenAI CLIP** (ViT-B/32) – Modelo multimodal que entiende texto e imágenes juntos
- **PyTorch** con soporte CUDA (GPU NVIDIA) si está disponible
- **Flask** – Servidor web Python
- **Interfaz dark mode** con glassmorphism y animaciones

---

## 📁 Estructura

```
vio/
├── app.py                    # Servidor Flask + lógica CLIP
├── requirements.txt          # Dependencias Python
├── instalar_dependencias.bat # Instalador automático Windows
├── iniciar.bat               # Iniciador rápido
└── static/
    ├── index.html            # Interfaz principal
    ├── style.css             # Estilos premium dark UI
    └── app.js                # Lógica frontend
```

---

## ⚠️ Notas

- La **primera búsqueda** puede tardar más (descarga el modelo CLIP ~330MB)
- Con **GPU NVIDIA** la búsqueda es significativamente más rápida
- Formatos soportados: JPG, PNG, WEBP, BMP, GIF, TIFF, HEIC y HEIF
- La búsqueda es **recursiva** (incluye subcarpetas)
