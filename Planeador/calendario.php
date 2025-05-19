<script src="cssjscalendar/jquery.min.js"></script>
<script
  src="cssjscalendar/jquery-3.3.1.min.js"
  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
  crossorigin="anonymous"></script>

<script src="cssjscalendar/bootstrap.min.js"></script>
<script src="cssjscalendar/bootstrap.bundle.min.js"></script>
  
<link href="cssjscalendar/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<!------ Include the above in your HEAD tag ---------->
<link href="style.css" rel="stylesheet" >

<link href="cssjscalendar/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">



<div class="container theme-showcase">
  <h1>Calendar</h1>
<div id="holder" class="row" ></div>
</div>



<script type="text/tmpl" id="tmpl">
  {{ 
  var date = date || new Date(),
      month = date.getMonth(), 
      year = date.getFullYear(), 
      first = new Date(year, month, 1), 
      last = new Date(year, month + 1, 0),
      startingDay = first.getDay(), 
      thedate = new Date(year, month, 1 - startingDay),
      dayclass = lastmonthcss,
      today = new Date(),
      i, j; 
  if (mode === 'week') {
    thedate = new Date(date);
    thedate.setDate(date.getDate() - date.getDay());
    first = new Date(thedate);
    last = new Date(thedate);
    last.setDate(last.getDate()+6);
  } else if (mode === 'day') {
    thedate = new Date(date);
    first = new Date(thedate);
    last = new Date(thedate);
    last.setDate(thedate.getDate() + 1);
  }
  
  }}
  <table class="calendar-table table table-condensed table-tight">
    <thead>
      <tr>
        <td colspan="7" style="text-align: center">
          <table style="white-space: nowrap; width: 100%">
            <tr>
              <td style="text-align: left;">
                <span class="btn-group">
                  <button class="js-cal-prev btn btn-default"><</button>
                  <button class="js-cal-next btn btn-default">></button>
                </span>
                <button class="js-cal-option btn btn-default {{: first.toDateInt() <= today.toDateInt() && today.toDateInt() <= last.toDateInt() ? 'active':'' }}" data-date="{{: today.toISOString()}}" data-mode="month">{{: todayname }}</button>
              </td>
              <td>
                <span class="btn-group btn-group-lg">
                  {{ if (mode !== 'day') { }}
                    {{ if (mode === 'month') { }}<button class="js-cal-option btn btn-link" data-mode="year">{{: months[month] }}</button>{{ } }}
                    {{ if (mode ==='week') { }}
                      <button class="btn btn-link disabled">{{: shortMonths[first.getMonth()] }} {{: first.getDate() }} - {{: shortMonths[last.getMonth()] }} {{: last.getDate() }}</button>
                    {{ } }}
                    <button class="js-cal-years btn btn-link">{{: year}}</button> 
                  {{ } else { }}
                    <button class="btn btn-link disabled">{{: date.toDateString() }}</button> 
                  {{ } }}
                </span>
              </td>
              <td style="text-align: right">
                <span class="btn-group">
                  <button class="js-cal-option btn btn-default {{: mode==='year'? 'active':'' }}" data-mode="year">Año</button>
                  <button class="js-cal-option btn btn-default {{: mode==='month'? 'active':'' }}" data-mode="month">Mes</button>
                  <button class="js-cal-option btn btn-default {{: mode==='week'? 'active':'' }}" data-mode="week">Semana</button>
                  <button class="js-cal-option btn btn-default {{: mode==='day'? 'active':'' }}" data-mode="day">Dia</button>
                </span>
              </td>
            </tr>
          </table>
          
        </td>
      </tr>
    </thead>
    {{ if (mode ==='year') {
      month = 0;
    }}
    <tbody>
      {{ for (j = 0; j < 3; j++) { }}
      <tr>
        {{ for (i = 0; i < 4; i++) { }}
        <td class="calendar-month month-{{:month}} js-cal-option" data-date="{{: new Date(year, month, 1).toISOString() }}" data-mode="month">
          {{: months[month] }}
          {{ month++;}}
        </td>
        {{ } }}
      </tr>
      {{ } }}
    </tbody>
    {{ } }}
    {{ if (mode ==='month' || mode ==='week') { }}
    <thead>
      <tr class="c-weeks">
        {{ for (i = 0; i < 7; i++) { }}
          <th class="c-name">
            {{: days[i] }}
          </th>
        {{ } }}
      </tr>
    </thead>
    <tbody>
      {{ for (j = 0; j < 6 && (j < 1 || mode === 'month'); j++) { }}
      <tr>
        {{ for (i = 0; i < 7; i++) { }}
        {{ if (thedate > last) { dayclass = nextmonthcss; } else if (thedate >= first) { dayclass = thismonthcss; } }}
        <td class="calendar-day {{: dayclass }} {{: thedate.toDateCssClass() }} {{: date.toDateCssClass() === thedate.toDateCssClass() ? 'selected':'' }} {{: daycss[i] }} js-cal-option" data-date="{{: thedate.toISOString() }}">
          <div class="date">{{: thedate.getDate() }}</div>
          {{ thedate.setDate(thedate.getDate() + 1);}}
        </td>
        {{ } }}
      </tr>
      {{ } }}
    </tbody>
    {{ } }}
    {{ if (mode ==='day') { }}
    <tbody>
      <tr>
        <td colspan="7">
          <table class="table table-striped table-condensed table-tight-vert" >
            <thead>
              <tr>
                <th> </th>
                <th style="text-align: center; width: 100%">{{: days[date.getDay()] }}</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th class="timetitle" >All Day</th>
                <td class="{{: date.toDateCssClass() }}">  </td>
              </tr>
              <tr>
                <th class="timetitle" >Before 6 AM</th>
                <td class="time-0-0"> </td>
              </tr>
              {{for (i = 6; i < 22; i++) { }}
              <tr>
                <th class="timetitle" >{{: i <= 12 ? i : i - 12 }} {{: i < 12 ? "AM" : "PM"}}</th>
                <td class="time-{{: i}}-0"> </td>
              </tr>
              <tr>
                <th class="timetitle" >{{: i <= 12 ? i : i - 12 }}:30 {{: i < 12 ? "AM" : "PM"}}</th>
                <td class="time-{{: i}}-30"> </td>
              </tr>
              {{ } }}
              <tr>
                <th class="timetitle" >After 10 PM</th>
                <td class="time-22-0"> </td>
              </tr>
            </tbody>
          </table>
        </td>
      </tr>
    </tbody>
    {{ } }}
  </table>
</script>


<script>
    var $currentPopover = null;
  $(document).on('shown.bs.popover', function (ev) {
    var $target = $(ev.target);
    if ($currentPopover && ($currentPopover.get(0) != $target.get(0))) {
      $currentPopover.popover('toggle');
    }
    $currentPopover = $target;
  }).on('hidden.bs.popover', function (ev) {
    var $target = $(ev.target);
    if ($currentPopover && ($currentPopover.get(0) == $target.get(0))) {
      $currentPopover = null;
    }
  });


//quicktmpl is a simple template language I threw together a while ago; it is not remotely secure to xss and probably has plenty of bugs that I haven't considered, but it basically works
//the design is a function I read in a blog post by John Resig (http://ejohn.org/blog/javascript-micro-templating/) and it is intended to be loosely translateable to a more comprehensive template language like mustache easily
$.extend({
    quicktmpl: function (template) {return new Function("obj","var p=[],print=function(){p.push.apply(p,arguments);};with(obj){p.push('"+template.replace(/[\r\t\n]/g," ").split("{{").join("\t").replace(/((^|\}\})[^\t]*)'/g,"$1\r").replace(/\t:(.*?)\}\}/g,"',$1,'").split("\t").join("');").split("}}").join("p.push('").split("\r").join("\\'")+"');}return p.join('');")}
});

$.extend(Date.prototype, {
  //provides a string that is _year_month_day, intended to be widely usable as a css class
  toDateCssClass:  function () { 
    return '_' + this.getFullYear() + '_' + (this.getMonth() + 1) + '_' + this.getDate(); 
  },
  //this generates a number useful for comparing two dates; 
  toDateInt: function () { 
    return ((this.getFullYear()*12) + this.getMonth())*32 + this.getDate(); 
  },
  toTimeString: function() {
    var hours = this.getHours(),
        minutes = this.getMinutes(),
        hour = (hours > 12) ? (hours - 12) : hours,
        ampm = (hours >= 12) ? ' pm' : ' am';
    if (hours === 0 && minutes===0) { return ''; }
    if (minutes > 0) {
      return hour + ':' + minutes + ampm;
    }
    return hour + ampm;
  }
});


(function ($) {

  //t here is a function which gets passed an options object and returns a string of html. I am using quicktmpl to create it based on the template located over in the html block
  var t = $.quicktmpl($('#tmpl').get(0).innerHTML);
  
  function calendar($el, options) {
    //actions aren't currently in the template, but could be added easily...
    $el.on('click', '.js-cal-prev', function () {
      switch(options.mode) {
      case 'year': options.date.setFullYear(options.date.getFullYear() - 1); break;
      case 'month': options.date.setMonth(options.date.getMonth() - 1); break;
      case 'week': options.date.setDate(options.date.getDate() - 7); break;
      case 'day':  options.date.setDate(options.date.getDate() - 1); break;
      }
      draw();
    }).on('click', '.js-cal-next', function () {
      switch(options.mode) {
      case 'year': options.date.setFullYear(options.date.getFullYear() + 1); break;
      case 'month': options.date.setMonth(options.date.getMonth() + 1); break;
      case 'week': options.date.setDate(options.date.getDate() + 7); break;
      case 'day':  options.date.setDate(options.date.getDate() + 1); break;
      }
      draw();
    }).on('click', '.js-cal-option', function () {
      var $t = $(this), o = $t.data();
      if (o.date) { o.date = new Date(o.date); }
      $.extend(options, o);
      draw();
    }).on('click', '.js-cal-years', function () {
      var $t = $(this), 
          haspop = $t.data('popover'),
          s = '', 
          y = options.date.getFullYear() - 2, 
          l = y + 5;
      if (haspop) { return true; }
      for (; y < l; y++) {
        s += '<button type="button" class="btn btn-default btn-lg btn-block js-cal-option" data-date="' + (new Date(y, 1, 1)).toISOString() + '" data-mode="year">'+y + '</button>';
      }
      $t.popover({content: s, html: true, placement: 'auto top'}).popover('toggle');
      return false;
    }).on('click', '.event', function () {
      var $t = $(this), 
          index = +($t.attr('data-index')), 
          haspop = $t.data('popover'),
          data, time;
          
      if (haspop || isNaN(index)) { return true; }
      data = options.data[index];
      time = data.start.toTimeString();
      if (time && data.end) { time = time + ' - ' + data.end.toTimeString(); }
      $t.data('popover',true);
      $t.popover({content: '<p><strong>' + time + '</strong></p>'+data.text, html: true, placement: 'auto left'}).popover('toggle');
      return false;
    });
    function dayAddEvent(index, event) {
      if (!!event.allDay) {
        monthAddEvent(index, event);
        return;
      }
var classes = 'event';
if (event.className) {
    classes += ' ' + event.className;
}
var $event = $('<div/>', {'href': 'planeador.php?pdf=1&idplan='+event.id_plan+'','class': classes, text: event.title+'('+event.grado+')', title: event.title+'('+event.grado+')'+'('+event.text+')', 'data-index': index}),

      start = event.start,
          end = event.end || start,
          time = event.start.toTimeString(),
          hour = start.getHours(),
          timeclass = '.time-22-0',
          startint = start.toDateInt(),
          dateint = options.date.toDateInt(),
          endint = end.toDateInt();
      if (startint > dateint || endint < dateint) { return; }
      
      if (!!time) {
        $event.html('<strong>' + time + '</strong> ' + $event.html());
      }
      $event.toggleClass('begin', startint === dateint);
      $event.toggleClass('end', endint === dateint);
      if (hour < 6) {
        timeclass = '.time-0-0';
      }
      if (hour < 22) {
        timeclass = '.time-' + hour + '-' + (start.getMinutes() < 30 ? '0' : '30');
      }
      $(timeclass).append($event);
    }
    
    function monthAddEvent(index, event) {
      console.log(event);
var classes = 'event';
if (event.className) {
    classes += ' ' + event.className;
}
var $event = $('<div/>', {'class': classes, text: '', title: event.title+'('+event.grado+')'+'('+event.text+')', 'data-index': index}),

      e = new Date(event.start),
          dateclass = e.toDateCssClass(),
          day = $('.' + e.toDateCssClass()),
          empty = $('<div/>', {'class':'clear event', html:' '}), 
          numbevents = 0, 
          time = event.start.toTimeString(),
          endday = event.end && $('.' + event.end.toDateCssClass()).length > 0,
          checkanyway = new Date(e.getFullYear(), e.getMonth(), e.getDate()+40),
          existing,
          i;
          
      $event.toggleClass(event.className, !!event.allDay);
      console.log('------'+event.id_plan);
        $event.html('<a style="text-decoration: none; color: inherit;" target="_blank" href="planeador.php?pdf=1&idplan='+event.id_plan+'">'+event.title+'('+event.grado+')'+'<a> ' + $event.html());

      if (!!time) {
        $event.html('<strong>' + time + '</strong> ' + $event.html());
      }
      if (!event.end) {
        $event.addClass('materia-matematicas');
        $('.' + event.start.toDateCssClass()).append($event);
        return;
      }
            
      while (e <= event.end && (day.length || endday || options.date < checkanyway)) {
        if(day.length) { 
          existing = day.find('.event').length;
          numbevents = Math.max(numbevents, existing);
          for(i = 0; i < numbevents - existing; i++) {
            day.append(empty.clone());
          }
          day.append(
            $event.
            toggleClass('begin', dateclass === event.start.toDateCssClass()).
            toggleClass('end', dateclass === event.end.toDateCssClass())
          );
          $event = $event.clone();
          $event.html(' ');
        }
        e.setDate(e.getDate() + 1);
        dateclass = e.toDateCssClass();
        day = $('.' + dateclass);
      }
    }
    function yearAddEvents(events, year) {
      var counts = [0,0,0,0,0,0,0,0,0,0,0,0];
      $.each(events, function (i, v) {
        if (v.start.getFullYear() === year) {
            counts[v.start.getMonth()]++;
        }
      });
      $.each(counts, function (i, v) {
        if (v!==0) {
            $('.month-'+i).append('<span class="badge">'+v+'</span>');
        }
      });
    }
    
    function draw() {
      $el.html(t(options));
      //potential optimization (untested), this object could be keyed into a dictionary on the dateclass string; the object would need to be reset and the first entry would have to be made here
      $('.' + (new Date()).toDateCssClass()).addClass('today');
      if (options.data && options.data.length) {
        if (options.mode === 'year') {
            yearAddEvents(options.data, options.date.getFullYear());
        } else if (options.mode === 'month' || options.mode === 'week') {
            $.each(options.data, monthAddEvent);
        } else {
            $.each(options.data, dayAddEvent);
        }
      }
    }
    
    draw();    
  }
  
  ;(function (defaults, $, window, document) {
    $.extend({
      calendar: function (options) {
        return $.extend(defaults, options);
      }
    }).fn.extend({
      calendar: function (options) {
        options = $.extend({}, defaults, options);
        return $(this).each(function () {
          var $this = $(this);
          calendar($this, options);
        });
      }
    });
  })({
    days: ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"],
    months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
    shortMonths: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
    date: (new Date()),
        daycss: ["c-sunday", "", "", "", "", "", "c-saturday"],
        todayname: "Hoy",
        thismonthcss: "current",
        lastmonthcss: "outside",
        nextmonthcss: "outside",
    mode: "month",
    data: []
  }, jQuery, window, document);
    
})(jQuery);
</script>
<?php
function fechasQueCaenEnDia($fechaInicio, $fechaFin, $diaBuscado) {
    $diasSemana = [
        'domingo' => 0,
        'lunes' => 1,
        'martes' => 2,
        'miércoles' => 3,
        'miercoles' => 3, // para permitir sin tilde
        'jueves' => 4,
        'viernes' => 5,
        'sábado' => 6,
        'sabado' => 6
    ];

    $diaBuscado = strtolower($diaBuscado);

    if (!isset($diasSemana[$diaBuscado])) {
        return []; // Día inválido
    }

    $numeroDia = $diasSemana[$diaBuscado];
    $fechasCoinciden = [];

    $fecha = new DateTime($fechaInicio);
    $fechaFin = new DateTime($fechaFin);

    while ($fecha <= $fechaFin) {
        if ((int)$fecha->format('w') === $numeroDia) {
            $fechasCoinciden[] = $fecha->format('Y-m-d');
        }
        $fecha->modify('+1 day');
    }

    return $fechasCoinciden;
}

// Ejemplo de uso:

// 1) Conexión y consulta
require 'conexion.php';
$fecha_limite='2025-'.date('m').'-01';
$result = $mysqli->query("
    SELECT
  p.*,h.*,
        p.id_plan,
        p.grado,
        p.materia,
        m.nombre_materia,
        p.fecha_inicio AS fecha_iniciop,
        p.fecha_fin AS fecha_finp,
        h.fecha_inicio AS horario_fecha_inicio,
        h.hora_inicio AS horario_hora_inicio,
        h.hora_fin AS horario_hora_fin,
        h.fecha_fin AS horario_fecha_fin,
        p.objetivo AS texto_planeacion
    FROM planeador_vallesol AS p
    JOIN asignacion     AS a ON a.id_asignacion = p.materia    
    JOIN materia_oficial AS m ON m.id_materia = a.id_asignatura
    JOIN horario        AS h ON h.id_asignacion = a.id_asignacion
    where p.fecha_inicio>='".$fecha_limite."'
    ORDER BY p.id_plan desc 
");
 
if (!$result) {
    die("Error en consulta: " . $mysqli->error);
}

// 2) Construir el array de eventos, generando ISO‑strings
$eventos = [];
$conta=0;
while ($r = $result->fetch_assoc()) {
#$inicio = '2025-05-12';
#$fin = '2025-05-16';
#$dia = $r['dia'];
/*
echo "<pre>";
echo($r['id_plan']).'<br>';
echo($r['nombre_materia']).'<br>';
echo($r['fecha_iniciop']).'<br>';
echo($r['fecha_finp']).'<br>';
echo($r['hora_inicio']).'<br>';
echo($r['dia']).'<br>';
echo "</pre>";
*/
$resultado = fechasQueCaenEnDia($r['fecha_iniciop'], $r['fecha_finp'], $r['dia'])[0];
    $fdata =explode('-', $resultado);
    $anio=$fdata[0];
    $fecha = new DateTime($resultado); // por ejemplo, 9 de mayo de 2025
$fecha->modify('-1 month');
 $mes = $fdata[1]; // Imprime: 2025-04-09
    $dia = $fdata[2];
    $hora_parts = explode(':', $r['horario_hora_inicio']);
    $hora =   $hora_parts[0];
$minutos = $hora_parts[1] ;

#########################


//$resultado=$fecha->format('Y-m-d');
#print_r($resultado);

// Imprimir el array de fechas y horas (opcional para verificar)
    // Construir DateTime PHP a partir de fecha + hora
    $startDt = new DateTime("{$resultado} {$r['horario_hora_inicio']}");
    $endDt   = new DateTime("{$resultado} {$r['horario_hora_fin']}");

    // Detectar evento all-day (00:00 a 23:59)
    $allDay = $startDt->format('H:i') === '00:00' && $endDt->format('H:i') === '23:59';
    if ($allDay) {
        // Para que el plugin pinte todo el día, movemos el fin al día siguiente 00:00
        $endDt = clone $startDt;
        $endDt->modify('+1 day');
    }
    
    // Formato ISO‑8601 garantizado
    $startIso = $startDt->format('c'); 
    $endIso   = $endDt->format('c');
    $datainicio="$anio,$mes, $dia, $hora, $minutos";
    $datafin="$anio,$mes, $dia, $hora, $minutos";

$messages = [
    [
        "role" => "system",
        "content" => "You are a assistant."
    ],
    [
        "role" => "user",
        "content" => "Dime el tema resumen unicamente en una palabra en español nunca en inglés y devuelve unicamente esa palabra de esta clase :".$r['objetivo']
    ]
];
require_once '../ia/index.php';
if(isset($_get['ia'])){
$response = call_lm_studio_api($messages);
}else{
$response = $r['objetivo'];
}

#echo $response;

    $eventos[] = [
        'title'  => $r['nombre_materia'],
        'grado'  =>  $r['grado'] ,
        'id_plan'  =>  $r['id_plan'] ,
        'start'  =>  $startIso ,
        'end'    => $endIso,
        'allDay' => 'false',
        'text'   => $response,
        'className' => 'materia-'.$r['nombre_materia'],

    ];
    
    $conta=$conta+1;
}



// 3) Inyectar el JSON en JS
?>
<script>
  // rawData: array de objetos con campos title, start (ISO), end (ISO|null), allDay, text

var rawData = <?php
echo json_encode($eventos);
  ?>;
console.log(rawData);
  // 4) Convertir cadenas ISO a objetos Date
  <?php $contador=0; ?>
  var data = rawData.map(function(e, index) {
    console.log(`Procesando evento ${index + 1}/${rawData.length}`);
////////

switch (e.title) {
  case "Educación Física":
miclase='Educacion_Fisica'; 
    break;
  case "matematicas":
miclase='matematicas'; 

    break;
  case "Ciencias Sociales":
miclase='Sociales'; 
    break;
  case "Emprendimiento":
miclase='Emprendimiento'; 

    break;
  case "Tecnología e informática":
miclase='tecnologia'; 
    break;
    case "Urbanidad":
miclase='urbanidad'; 
    break;
  default:
    miclase='matematicas';
   // console.log("Lo lamentamos, por el momento no disponemos de " + miclase + ".");
}


//////




    return {
        title: e.title,
        id_plan: e.id_plan,
        start: new Date(e.start),
        grado: e.grado,
        end: new Date(e.end),
        allDay: e.allDay,
        text: e.text,
      className: 'materia-'+miclase,

    };
});
  console.log('Parsed:', data);

  // 5) Ordenar cronológicamente (opcional)
  data.sort(function(a,b){ return a.start - b.start; });

  // 6) Inicializar el calendario cuando jQuery y el plugin estén listos
  $(function(){
    $('#holder').calendar({ data: data });
  });
</script>