$botoniniciar = document.querySelector("#iniciar");
$botoniniciar.addEventListener("click", function() {
const tieneSoporteUserMedia = () =>
    !!(navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia)
const _getUserMedia = (...arguments) =>
    (navigator.getUserMedia || (navigator.mozGetUserMedia || navigator.mediaDevices.getUserMedia) || navigator.webkitGetUserMedia || navigator.msGetUserMedia).apply(navigator, arguments);

const $video = document.querySelector("#video"),
    $canvas = document.querySelector("#canvas"),
    $guardar = document.querySelector("#guardar"),
    $boton = document.querySelector("#boton"),
    $botondetener = document.querySelector("#detener"),
    $botoniniciar = document.querySelector("#iniciar"),
    $listaDeDispositivos = document.querySelector("#listaDeDispositivos");
$("#video").css({ "height": "200", "width:": "200" });

const limpiarSelect = () => {
    for (let x = $listaDeDispositivos.options.length - 1; x >= 0; x--)
        $listaDeDispositivos.remove(x);
};
const obtenerDispositivos = () => navigator
    .mediaDevices
    .enumerateDevices();

// La función que es llamada después de que ya se dieron los permisos
// Lo que hace es llenar el select con los dispositivos obtenidos
const llenarSelectConDispositivosDisponibles = () => {
    limpiarSelect();
    obtenerDispositivos()
        .then(dispositivos => {
            const dispositivosDeVideo = [];
            dispositivos.forEach(dispositivo => {
                const tipo = dispositivo.kind;
                if (tipo === "videoinput") {
                    dispositivosDeVideo.push(dispositivo);
                }
            });

            // Vemos si encontramos algún dispositivo, y en caso de que si, entonces llamamos a la función
            if (dispositivosDeVideo.length > 0) {
                // Llenar el select
                dispositivosDeVideo.forEach(dispositivo => {
                    const option = document.createElement('option');
                    option.value = dispositivo.deviceId;
                    option.text = dispositivo.label;
                    $listaDeDispositivos.appendChild(option);
                });
            }
        });
}

(function() {
    // Comenzamos viendo si tiene soporte, si no, nos detenemos
    if (!tieneSoporteUserMedia()) {
        alert("Lo siento. Tu navegador no soporta esta característica");
        $estado.innerHTML = "Parece que tu navegador no soporta esta característica. Intenta actualizarlo.";
        return;
    }
    //Aquí guardaremos el stream globalmente
    let stream;
    // Comenzamos pidiendo los dispositivos
    obtenerDispositivos()
        .then(dispositivos => {
            // Vamos a filtrarlos y guardar aquí los de vídeo
            const dispositivosDeVideo = [];
            dispositivos.forEach(function(dispositivo) {
                const tipo = dispositivo.kind;
                if (tipo === "videoinput") {
                    dispositivosDeVideo.push(dispositivo);
                }
            });
            if (dispositivosDeVideo.length > 0) {
                mostrarStream(dispositivosDeVideo[0].deviceId);
            }
        });
    const mostrarStream = idDeDispositivo => {
        _getUserMedia({
                video: {
                    // Justo aquí indicamos cuál dispositivo usar
                    deviceId: idDeDispositivo,
                }
            },
            (streamObtenido) => {
                // Aquí ya tenemos permisos, ahora sí llenamos el select,
                // pues si no, no nos daría el nombre de los dispositivos
                llenarSelectConDispositivosDisponibles();

                // Escuchar cuando seleccionen otra opción y entonces llamar a esta función
                $listaDeDispositivos.onchange = () => {
                    // Detener el stream
                 stream = streamObtenido;

                // Mandamos el stream de la cámara al elemento de vídeo
                $video.srcObject = stream;

                $video.pause();


                    // Mostrar el nuevo stream con el dispositivo seleccionado
                    mostrarStream($listaDeDispositivos.value);
                }

                // Simple asignación
                stream = streamObtenido;

                // Mandamos el stream de la cámara al elemento de vídeo
                $video.srcObject = stream;

                $video.play();
 $botondetener.addEventListener("click", function() {
  stream.getTracks().forEach(function(track) {
                            track.stop();
                        });
  
  exit();
});

                //Escuchar el click del botón para tomar la foto
    $boton.addEventListener("click", function() {                      
                    //Pausar reproducción
                    //$video.pause();
                    //Obtener contexto del canvas y dibujar sobre él
                    let contexto = $canvas.getContext("2d");
                    $canvas.width = $video.videoWidth;
                    $canvas.height = $video.videoHeight;
                    contexto.drawImage($video, 0, 0, $canvas.width, $canvas.height);


var contador=0;
$guardar.addEventListener("click", function() {

contador=contador+1;
if(contador== 1){

            var foto = $canvas.toDataURL(); //Esta es la foto, en base 64
               //document.getElementById("fotousuario").src=foto;
                //$estado.innerHTML = "Enviando foto. Por favor, espera...";
                var xhr = new XMLHttpRequest();
               
                xhr.open("POST", "./guardar_foto.php", true);
                              xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                //xhr.send(encodeURIComponent(foto));
                var imagenaenviar=encodeURIComponent(foto);
                var cedula=document.getElementById('id_usuario').value;
                document.getElementById('inputcamara').value=cedula+'.png';
                
                xhr.send("foo="+imagenaenviar+"&lorem="+cedula);
                
                xhr.onreadystatechange = function() {
                    if(xhr.readyState == XMLHttpRequest.DONE && xhr.status == 200) {
                        console.log("La foto fue enviada correctamente");
                        console.log(xhr);
                  //      $estado.innerHTML = "Foto guardada con éxito. Puedes verla <a target='_blank' href='./" + xhr.responseText + "'> aquí</a>";
                    }
                }
exit();
}

                });

/* antes
                    let foto = $canvas.toDataURL(); //Esta es la foto, en base 64
                       alert('a');     
                    let enlace = document.createElement('a'); // Crear un <a>
                    enlace.download = "foto_parzibyte.me.png";
                    enlace.href = foto;
                    enlace.click();
                    //Reanudar reproducción
  */               
                    $video.play();
                }); //ok
            }, (error) => {
                console.log("Permiso denegado o error: ", error);
                $estado.innerHTML = "No se puede acceder a la cámara, o no diste permiso.";
            });
    }
})();
});