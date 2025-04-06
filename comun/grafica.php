    <script type="text/javascript" src="js/jsapi.js"></script>
    <script type="text/javascript" src="js/uds_api_contents.js"></script>
    <script type="text/javascript">
      function Graficooffline() {
       var data = google.visualization.arrayToDataTable([
          ['Opción', 'Valoración'], 
          ['actividad1',3], 
          ['actividad2',1]
        ]);
new google.visualization.LineChart(document.getElementById('divrendimeinto')).
        draw( 
          data,
          {
             title: 'Rendimiento acádemico', 
            width: 700, height: 300, 
          }
        );
      }
      google.setOnLoadCallback(Graficooffline); 
    </script>
    <div id="divrendimeinto" style="width: 500px; height: 300px;"></div>


