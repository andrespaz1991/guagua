<?php
/** Unidades internas, límites de entrada y etiquetas compartidas por API y vistas. */
function guagua_salud_metrics(): array
{
    return [
        'steps'=>['label'=>'Pasos','unit'=>'','max'=>300000,'integer'=>true],
        'distance_km'=>['label'=>'Distancia','unit'=>'km','max'=>1000],
        'active_kcal'=>['label'=>'Energía activa','unit'=>'kcal','max'=>50000],
        'floors'=>['label'=>'Pisos subidos','unit'=>'','max'=>10000,'integer'=>true],
        'active_minutes'=>['label'=>'Minutos activos','unit'=>'min','max'=>1440],
        'heart_rate_avg'=>['label'=>'Pulso medio','unit'=>'lpm','max'=>400],
        'heart_rate_min'=>['label'=>'Pulso mínimo','unit'=>'lpm','max'=>400],
        'heart_rate_max'=>['label'=>'Pulso máximo','unit'=>'lpm','max'=>400],
        'weight_kg'=>['label'=>'Peso medio del día','unit'=>'kg','max'=>1000],
        'body_fat_pct'=>['label'=>'Grasa corporal media','unit'=>'%','max'=>100],
        'sleep_minutes'=>['label'=>'Tiempo dormido','unit'=>'min','max'=>1440],
        'sleep_deep_minutes'=>['label'=>'Sueño profundo','unit'=>'min','max'=>1440],
        'sleep_rem_minutes'=>['label'=>'Sueño REM','unit'=>'min','max'=>1440],
        'sleep_light_minutes'=>['label'=>'Sueño ligero','unit'=>'min','max'=>1440],
    ];
}
