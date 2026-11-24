<?php
declare(strict_types=1);

// Transcripción del anexo 1 del SIE 2024. Los pesos iguales son una decisión
// de cálculo del módulo, no porcentajes establecidos por el documento.
$nivelNames = ['Superior', 'Alto', 'Básico', 'Bajo'];
$nivelScores = [5.0, 4.2, 3.5, 2.5];
$nivelClasses = ['selected-superior', 'selected-alto', 'selected-basico', 'selected-bajo'];
$nivelRanges = ['4,6 - 5,0', '4,0 - 4,5', '3,0 - 3,9', '1,0 - 2,9'];
$criterion = static function (string $id, string $title, string $competence, array $descriptions) use ($nivelNames, $nivelScores, $nivelClasses, $nivelRanges): array {
    $levels = [];
    foreach ($descriptions as $i => $description) {
        $levels[] = ['name' => $nivelNames[$i], 'score' => $nivelScores[$i], 'class' => $nivelClasses[$i], 'range' => $nivelRanges[$i], 'desc' => $description];
    }
    return ['id' => $id, 'title' => $title, 'competence' => $competence, 'levels' => $levels];
};
$oralWritten = [
    $criterion('sie_argumentacion', 'Habilidad argumentativa', 'SABER', [
        'Argumenta o justifica todas las respuestas de manera clara usando un vocabulario amplio.',
        'Argumenta o justifica la mayoría de las respuestas usando un vocabulario adecuado.',
        'Argumenta o justifica algunas de las respuestas usando un vocabulario adecuado.',
        'Argumenta o justifica la mayoría de las respuestas de manera poco clara y emplea muchas redundancias.'
    ]),
    $criterion('sie_contenidos', 'Contenidos evaluados', 'SABER', [
        'Domina el tema propuesto, logra conectarlo y explicarlo en sus diferentes aspectos. Se evidencia en la evaluación que existe una comprensión total.',
        'Logra explicar el tema relacionando los diferentes aspectos de este. La evaluación cumple con los diversos aspectos presentados.',
        'Conoce el tema superficialmente, logra explicar y argumentar algunos puntos planteados.',
        'Conoce poco del tema, expone con poca claridad los puntos planteados.'
    ]),
    $criterion('sie_procedimientos', 'Desarrollo de procedimientos', 'HACER', [
        'Desarrolla siempre los procedimientos requeridos de acuerdo con cada actividad planteada y de manera satisfactoria.',
        'Desarrolla casi siempre los procedimientos requeridos en la mayoría de las actividades planteadas.',
        'Desarrolla procedimientos en algunas actividades planteadas.',
        'Desarrolla muy pocos procedimientos en las actividades planteadas.'
    ]),
    $criterion('sie_relacion', 'Relación entre las temáticas', 'SABER', [
        'Responde con propiedad sobre la relación existente entre las temáticas y las preguntas de la evaluación.',
        'Relaciona los elementos de las temáticas sugeridas en la evaluación.',
        'Relaciona algunos elementos que constituyen las temáticas sugeridas en la evaluación.',
        'Se le dificulta relacionar las temáticas sugeridas en la evaluación.'
    ]),
    $criterion('sie_presentacion', 'Orden y presentación', 'SER', [
        'Siempre demuestra orden y estética en el desarrollo de la evaluación.',
        'En la mayoría de las ocasiones demuestra orden y estética en el desarrollo de la evaluación.',
        'En algunas ocasiones demuestra orden y estética en el desarrollo de la evaluación.',
        'Pocas veces demuestra orden y estética en el desarrollo de la evaluación.'
    ])
];
$data = [
    'oral' => $oralWritten,
    'escrita' => $oralWritten,
    'equipo' => [
        $criterion('sie_eq_escucha', 'Escucha asertiva', 'SER', [
            'Participa activamente, socializa aportes y sugerencias con sus compañeros, mostrando buena integración en el grupo.',
            'Participa y socializa aportes y sugerencias con sus compañeros.',
            'Participa con algunos aportes y sugerencias para sus compañeros.',
            'Le cuesta mucho participar con aportes y sugerencias para sus compañeros.'
        ]),
        $criterion('sie_eq_experiencias', 'Experiencias de aprendizaje', 'SABER', [
            'Comparte sus saberes de manera respetuosa y espontánea; además, valora las ideas y las opiniones de todos los integrantes del grupo.',
            'Comparte, casi siempre, sus saberes de manera respetuosa y espontánea y valora las ideas y las opiniones de todos los integrantes del grupo.',
            'Comparte algunos saberes de manera respetuosa y espontánea; además, valora algunas veces las ideas y las opiniones de todos los integrantes del grupo.',
            'En muy pocas ocasiones comparte sus saberes de manera respetuosa y espontánea con todos los integrantes del grupo.'
        ]),
        $criterion('sie_eq_objetivos', 'Objetivos comunes', 'HACER', [
            'Colabora en todas las propuestas de trabajo y realiza las actividades asignadas.',
            'Colabora con casi todas las propuestas de trabajo y realiza las actividades asignadas.',
            'Colabora en algunas de las propuestas de trabajo y realiza ciertas actividades asignadas.',
            'Colabora muy poco en las propuestas de trabajo y le cuesta realizar las actividades asignadas.'
        ]),
        $criterion('sie_eq_cooperativo', 'Trabajo cooperativo', 'HACER', [
            'Contribuye al trabajo en equipo y demuestra comprensión y motivación para alcanzar los resultados de calidad.',
            'Contribuye parcialmente al trabajo en equipo y demuestra comprensión y motivación para alcanzar los resultados de calidad.',
            'Contribuye algunas veces al trabajo en equipo y demuestra comprensión y motivación para alcanzar los resultados de calidad.',
            'Contribuye poco al trabajo en equipo y demuestra poca comprensión y motivación para alcanzar los resultados de calidad.'
        ]),
        $criterion('sie_eq_responsabilidad', 'Responsabilidad y cumplimiento', 'SER', [
            'Evidencia motivación en las actividades planteadas por el docente y contribuye de manera adecuada y efectiva al cumplimiento del trabajo en grupo.',
            'Evidencia, casi siempre, motivación en las actividades planteadas por el docente y contribuye de manera adecuada y efectiva al cumplimiento del trabajo en grupo.',
            'Evidencia limitada motivación en las actividades planteadas por el docente y contribuye algunas veces al cumplimiento del trabajo en grupo.',
            'El estudiante evidencia poca motivación en las actividades planteadas por el docente y no contribuye con el cumplimiento del trabajo en grupo.'
        ])
    ],
    'tertulia' => [
        $criterion('sie_ter_atencion', 'Atención', 'HACER', [
            'Escucha con especial atención e interés las orientaciones del moderador para participar de la actividad.',
            'Escucha las orientaciones del moderador para participar de la actividad.',
            'Escucha algunas orientaciones del moderador para participar de la actividad.',
            'Se le dificulta escuchar atentamente las orientaciones del moderador para participar de la actividad.'
        ]),
        $criterion('sie_ter_participacion', 'Participación', 'SABER', [
            'Participa activa y proactivamente respetando el contexto del diálogo o la temática.',
            'Participa y respeta el contexto del diálogo o la temática.',
            'Participa y respeta con algunas limitaciones el contexto del diálogo o la temática.',
            'Rara vez su participación se relaciona con el contexto del diálogo o la temática.'
        ]),
        $criterion('sie_ter_escucha', 'Escucha', 'SER', [
            'Escucha atenta y respetuosamente la opinión de sus compañeros.',
            'Escucha respetuosamente la opinión de sus compañeros.',
            'Escucha algunas opiniones de sus compañeros.',
            'Casi nunca escucha la opinión de sus compañeros.'
        ]),
        $criterion('sie_ter_palabra', 'Pide la palabra', 'HACER', [
            'Levanta la mano para pedir la palabra cuando quiere participar o dar su opinión sobre un tema.',
            'Casi siempre levanta la mano para pedir la palabra cuando quiere participar o dar su opinión sobre un tema.',
            'Pocas veces levanta la mano para pedir la palabra cuando quiere participar o dar su opinión sobre un tema.',
            'Casi nunca pide la palabra cuando quiere participar o dar su opinión sobre un tema.'
        ]),
        $criterion('sie_ter_respeto', 'Respeto', 'SER', [
            'Usa un lenguaje amable y respetuoso.', 'Usa casi siempre un lenguaje amable y respetuoso.',
            'Usa algunas veces un lenguaje amable y respetuoso.', 'Se le dificulta usar un lenguaje amable y respetuoso.'
        ]),
        $criterion('sie_ter_dominio', 'Dominio del tema', 'SABER', [
            'Evidencia amplio conocimiento y preparación de la temática central del encuentro.',
            'Evidencia conocimiento de la temática central del encuentro.',
            'Evidencia conocimiento parcial de la temática central del encuentro.',
            'Evidencia muy poco conocimiento de la temática central del encuentro.'
        ]),
        $criterion('sie_ter_tiempo', 'Uso del tiempo', 'HACER', [
            'Hace un uso responsable del tiempo con intervenciones claras y precisas cuando tiene la palabra.',
            'Hace un uso responsable del tiempo, con algunas intervenciones claras, pero imprecisas cuando tiene la palabra.',
            'Algunas veces hace un uso responsable del tiempo, pero sus intervenciones no logran ser muy claras y precisas cuando tiene la palabra.',
            'Casi nunca hace un uso responsable del tiempo y sus intervenciones no son claras, ni precisas cuando tiene la palabra.'
        ])
    ],
    'tarea' => [
        $criterion('sie_tar_cumplimiento', 'Cumplimiento', 'HACER', [
            'Cumple en su totalidad las actividades indicadas e incluye ejercicios y ejemplos de profundización.',
            'Cumple únicamente con lo indicado en las tareas.', 'Algunas veces cumple con lo indicado en las tareas.', 'Pocas veces cumple con lo indicado en las tareas.'
        ]),
        $criterion('sie_tar_dedicacion', 'Dedicación, orden y creatividad', 'SER', [
            'Demuestra completa dedicación, creatividad y cuidado al realizar sus tareas.',
            'Demuestra casi siempre dedicación, creatividad y cuidado al realizar sus tareas.',
            'Demuestra algunas veces dedicación, creatividad y cuidado al realizar sus tareas.',
            'Muy pocas veces demuestra dedicación, creatividad y cuidado al realizar sus tareas.'
        ]),
        $criterion('sie_tar_motivacion', 'Motivación', 'SER', [
            'En su totalidad evidencia motivación por las tareas.', 'Muchas veces demuestra motivación por las tareas.',
            'Algunas veces realiza con motivación las tareas propuestas.', 'Pocas veces demuestra motivación por las tareas.'
        ]),
        $criterion('sie_tar_conocimiento', 'Conocimiento', 'HACER', [
            'Siempre demuestra dominio y conocimiento en las tareas.', 'Muchas veces demuestra dominio y conocimiento en las tareas.',
            'Algunas veces demuestra dominio y conocimiento en las tareas.', 'Pocas veces demuestra dominio y conocimiento en las tareas.'
        ]),
        $criterion('sie_tar_analisis', 'Análisis y reflexión', 'SER', [
            'Siempre demuestra análisis y reflexión en sus tareas.', 'Muchas veces demuestra análisis y reflexión en sus tareas.',
            'Algunas veces evidencia análisis y reflexión en sus tareas.', 'Pocas veces evidencia análisis y reflexión en sus tareas.'
        ])
    ],
    'exposicion' => [
        $criterion('sie_exp_recursos', 'Uso de recursos didácticos y tecnológicos', 'HACER', [
            'Utiliza adecuadamente recursos didácticos y tecnológicos para enriquecer su presentación, haciéndola interesante y atractiva.',
            'Utiliza adecuadamente recursos didácticos y tecnológicos en su presentación.',
            'Utiliza recursos didácticos y tecnológicos, pero estos no aportan significativamente a su presentación.',
            'Se le dificulta utilizar recursos didácticos y tecnológicos en su presentación.'
        ]),
        $criterion('sie_exp_expresion', 'Expresión oral', 'HACER', [
            'Expresa sus ideas con fluidez y claridad, modulando la voz adecuadamente.',
            'Expresa sus ideas con claridad y buena modulación de la voz.',
            'Expresa sus ideas de manera básica, con algunos aspectos por mejorar frente a la fluidez y a la modulación de la voz.',
            'Expresa sus ideas sin claridad ni coherencia, generando algunas confusiones.'
        ]),
        $criterion('sie_exp_dominio', 'Dominio del tema y argumentación', 'SABER', [
            'Argumenta el tema expuesto con excelentes ejemplos o anécdotas que logran clarificar todos los aspectos abordados.',
            'Argumenta el tema expuesto con algunos ejemplos o anécdotas que logran clarificar casi todos los aspectos abordados.',
            'Argumenta el tema expuesto con lo básico intentando ser claro, pero sin utilizar ejemplos o anécdotas.',
            'Tiene dificultades para conectar el tema expuesto, le falta mucha claridad y no hace uso de ejemplos o anécdotas.'
        ]),
        $criterion('sie_exp_tiempo', 'Manejo del tiempo', 'SABER', [
            'Maneja apropiadamente el tiempo de exposición abarcando lo relevante del tema en el período establecido para lograr el objetivo.',
            'Maneja apropiadamente el tiempo de exposición, cumpliendo con algunos objetivos.',
            'Maneja claridades mínimas y excede un poco el tiempo de la exposición con el cumplimiento de algunos objetivos.',
            'Se le dificulta el manejo del tiempo con intervenciones muy cortas e imprecisas que no logran los objetivos mínimos.'
        ]),
        $criterion('sie_exp_comunicacion', 'Comunicación', 'SER', [
            'Siempre escucha y emplea buen vocabulario y postura correcta para exponer sus ideas con respeto.',
            'Casi siempre escucha y emplea un buen vocabulario y una postura correcta para exponer sus ideas.',
            'Ocasionalmente escucha y emplea un vocabulario correcto y una postura adecuada para exponer sus ideas.',
            'Se le dificulta escuchar y emplea un vocabulario inadecuado que fomenta discusiones y confrontaciones.'
        ])
    ],
    'autoevaluacion' => [
        $criterion('sie_auto_responsabilidad', 'Responsabilidad', 'HACER', [
            'Realizo mis actividades con motivación, responsabilidad, convicción y deseos de aprender.',
            'Realizo parcialmente mis actividades con motivación, responsabilidad, convicción y deseos de aprender.',
            'Realizo algunas veces mis actividades con motivación, responsabilidad, convicción y deseos de aprender.',
            'Casi nunca realizo mis actividades con motivación, responsabilidad, convicción y deseos de aprender.'
        ]),
        $criterion('sie_auto_puntualidad', 'Puntualidad en los compromisos', 'HACER', [
            'Siempre entrego puntualmente las actividades asignadas.', 'Casi siempre entrego puntualmente las actividades asignadas.',
            'En algunas ocasiones entrego puntualmente las actividades asignadas.', 'Casi nunca entrego puntualmente las actividades asignadas.'
        ]),
        $criterion('sie_auto_autonomia', 'Autonomía y profundización', 'SABER', [
            'Amplío siempre mis conocimientos aprovechando los recursos que están a mi alcance (cartillas, videos, programas de televisión, radio e internet).',
            'Amplío casi siempre mis conocimientos aprovechando los recursos que están a mi alcance (cartillas, videos, programas de televisión, radio e internet).',
            'Algunas veces amplío mis conocimientos aprovechando los recursos que están a mi alcance (cartillas, videos, programas de televisión, radio e internet).',
            'Rara vez o casi nunca amplío mis conocimientos aprovechando los recursos que están a mi alcance (cartillas, videos, programas de televisión, radio e internet).'
        ]),
        $criterion('sie_auto_soluciones', 'Búsqueda de soluciones', 'SABER', [
            'Siempre solicito al docente explicación cuando no entiendo alguna actividad o tema.',
            'Casi siempre solicito al docente explicación cuando no entiendo alguna actividad o tema.',
            'Algunas veces solicito al docente explicación cuando no entiendo alguna actividad o tema.',
            'Me abstengo de solicitar al docente explicación cuando no entiendo alguna actividad o tema.'
        ]),
        $criterion('sie_auto_participacion', 'Participación', 'HACER', [
            'Expreso con claridad mis puntos de vista frente a una situación o tema.',
            'Casi siempre expreso con claridad mis puntos de vista frente a una situación o tema.',
            'Algunas veces expreso con claridad mis puntos de vista frente a una situación o tema.',
            'Casi nunca suelo expresar con claridad mis puntos de vista frente a una situación o tema.'
        ]),
        $criterion('sie_auto_argumentacion', 'Argumentación', 'SABER', [
            'Argumento siempre sobre mis decisiones, posiciones, deseos y sentires frente a un tema o una situación con un lenguaje claro y respetuoso.',
            'Casi siempre argumento sobre mis decisiones, posiciones, deseos y sentires frente a un tema o una situación con un lenguaje claro y respetuoso.',
            'En algunas ocasiones argumento sobre mis decisiones, posiciones, deseos y sentires frente a un tema o una situación con un lenguaje claro y respetuoso.',
            'Casi nunca argumento sobre mis decisiones, posiciones, deseos y sentires frente a un tema o una situación con un lenguaje claro y respetuoso.'
        ]),
        $criterion('sie_auto_escucha', 'Escucha', 'SER', [
            'Escucho siempre con atención y respeto al docente y a mis compañeros de clase.',
            'Casi siempre escucho con atención y respeto al docente y a mis compañeros de clase.',
            'Algunas veces escucho con atención y respeto al docente y a mis compañeros de clase.',
            'Casi nunca escucho con atención y respeto al docente y a mis compañeros de clase.'
        ])
    ],
    'coevaluacion' => [
        $criterion('sie_co_comunicacion', 'Comunicación asertiva', 'SER', [
            'Siempre se comunica respetuosamente con sus compañeros y docentes y pide la palabra.',
            'Casi siempre se comunica respetuosamente con sus compañeros y docentes. A veces pide la palabra.',
            'Algunas veces se comunica respetuosamente con sus docentes y compañeros, aunque no siempre pide la palabra.',
            'En pocas ocasiones se comunica respetuosamente con sus compañeros y docentes. Tiene dificultades para pedir la palabra.'
        ]),
        $criterion('sie_co_participacion', 'Participación', 'HACER', [
            'Participa siempre y activamente en las actividades que se proponen.', 'Participa casi siempre en las actividades que se proponen.',
            'Participa a veces en las actividades que se proponen.', 'Casi nunca participa en las actividades que se proponen.'
        ]),
        $criterion('sie_co_respeto', 'Respeto', 'SER', [
            'Promueve siempre un ambiente de respeto, aprendizaje y buena actitud.', 'Promueve casi siempre un ambiente de respeto, aprendizaje y buena actitud.',
            'Promueve algunas veces un ambiente de respeto, aprendizaje y buena actitud.', 'Casi nunca promueve un ambiente de respeto, aprendizaje y buena actitud.'
        ]),
        $criterion('sie_co_colaboracion', 'Colaboración', 'SER', [
            'Ayuda a sus compañeros cuando necesitan apoyo.', 'Algunas veces ayuda a sus compañeros cuando necesitan apoyo.',
            'De vez en cuando ayuda a sus compañeros cuando necesitan apoyo.', 'Tiene dificultades para ayudar a sus compañeros cuando necesitan apoyo.'
        ]),
        $criterion('sie_co_instrucciones', 'Cumplimiento de instrucciones', 'HACER', [
            'Presenta siempre las actividades cumpliendo las indicaciones definidas por el docente.',
            'Presenta casi siempre las actividades cumpliendo las indicaciones definidas por el docente.',
            'Presenta algunas veces las actividades cumpliendo las indicaciones definidas por el docente.',
            'En muy pocas ocasiones presenta las actividades cumpliendo las indicaciones definidas por el docente.'
        ]),
        $criterion('sie_co_conocimiento', 'Conocimiento', 'SABER', [
            'Demuestra amplio conocimiento de los temas abordados en clase.', 'Demuestra casi siempre conocimiento de los temas abordados en clase.',
            'Demuestra algunas veces conocimiento de los temas abordados en clase.', 'Casi nunca demuestra conocimiento de los temas abordados en clase.'
        ])
    ]
];
return [
    'version' => 'sie-2024-anexo-1-v1',
    'source' => 'SIE 2024 · Anexo 1',
    'labels' => ['oral' => 'Oral', 'escrita' => 'Escrita', 'equipo' => 'Trabajo en equipo', 'tertulia' => 'Tertulias y debates', 'tarea' => 'Tareas', 'exposicion' => 'Exposiciones', 'autoevaluacion' => 'Autoevaluación', 'coevaluacion' => 'Coevaluación'],
    'pages' => ['oral' => '32-33', 'escrita' => '32-33', 'equipo' => '33-34', 'tertulia' => '35-36', 'tarea' => '36', 'exposicion' => '37-38', 'autoevaluacion' => '30-31', 'coevaluacion' => '31-32'],
    'criteria' => $data
];
