<?php
/** Catálogos permitidos: los nombres de tabla y columna nunca vienen del navegador. */
$staff = ['admin','directivo','docente'];
return [
    'contenido' => ['title'=>'Contenidos','table'=>'contenido','key'=>'contenido','fields'=>['contenido'=>'Contenido'],'roles'=>$staff],
    'objetivos' => ['title'=>'Objetivos','table'=>'objetivos','key'=>'objetivos','fields'=>['objetivos'=>'Objetivo'],'roles'=>$staff],
    'recursos' => ['title'=>'Recursos educativos','table'=>'recursos','key'=>'id_recurso','fields'=>['nombre_recurso'=>'Nombre del recurso','descripcion_recurso'=>'Descripción'],'roles'=>$staff],
    'tarjeta' => ['title'=>'Tarjetas de inicio','table'=>'tarjeta','key'=>'id_tarjeta','fields'=>['titulo'=>'Título','permisos'=>'Permisos (lista JSON)','icono'=>'Archivo de icono','href'=>'Ruta de destino','class_color'=>'Color Bootstrap','accion_rapida'=>'Acción rápida'],'roles'=>['admin','directivo'],'help'=>'Los permisos son una lista JSON, por ejemplo ["docente","admin"]. El contenido de cada tarjeta se define en comun/home_cards.php.'],
    'eventos' => ['title'=>'Eventos institucionales','table'=>'eventos','key'=>'id','fields'=>['nom'=>'Nombre','descripcion'=>'Descripción','fecha'=>'Fecha','hora_inicio'=>'Hora inicial','hora_fin'=>'Hora final'],'roles'=>$staff],
];
