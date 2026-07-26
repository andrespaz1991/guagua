# 🔍 VisoSearch – Búsqueda Visual de Imágenes con IA

Aplicación web que usa **OpenAI CLIP** para buscar imágenes por descripción en lenguaje natural.

## 🚀 Inicio rápido

### 1. Instalar dependencias (solo primera vez)
Doble clic en `instalar_dependencias.bat` o ejecuta:
```bash
pip install flask flask-cors Pillow openai-clip torch torchvision
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
2. **Escribe qué buscas** – En lenguaje natural: `"persona con sudadera roja"`, `"perro en playa"`, etc.
3. **Ajusta el umbral** – Valor más bajo = más resultados (menos estricto). Valor más alto = más preciso.
4. **Busca** – La IA analiza cada imagen y muestra las que coinciden con tu descripción.
5. **Exporta** – Selecciona las fotos que quieres y expórtalas a una carpeta destino.

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
- Formatos soportados: JPG, PNG, WEBP, BMP, GIF, TIFF
- La búsqueda es **recursiva** (incluye subcarpetas)
