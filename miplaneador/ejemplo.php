<?php
    include_once('tbs_class.php'); 
    include_once('plugins/tbs_plugin_opentbs.php'); 
    require_once("../comun/autoload.php");
    require '../comun/conexion.php';
    $TBS = new clsTinyButStrong; 
    $TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN); 
    //Parametros

$sql_vallesol='select * from planeador_vallesol
inner join materia on materia.id_materia=planeador_vallesol.materia
where planeador_vallesol.id_plan="'.$_GET['id'].'"';
$consulta_vallesol=$mysqli->query($sql_vallesol);

while($row=$consulta_vallesol->fetch_assoc()){ 
    $fecha_creacion=$row['fecha_creacion'];
    $fecha_inicio=Fecha::formato_fecha($row['fecha_inicio']).' al '.Fecha::formato_fecha($row['fecha_fin']);
    $fecha_fin=$row['fecha_fin'];
    $grado=Comun::extraerTextoEntreParentesisValida($row['nombre_materia']);
    $materia=Comun::eliminar_sobrante($row['nombre_materia']);
    $periodo=$row['periodo'];
    $tiempo_plan=$row['tiempo_plan'].' Horas';
    $dba=$row['dba'];
    $estrategias=$row['estrategias'];
    $evidencias=$row['evidencias'];
    $observaciones=$row['observaciones'];
    $recursos=$row['recursos'];
    $reflexion=$row['reflexion'];
    $objetivo=$row['objetivo'];
    $eje_tematico=$row['eje_tematico'];

     
    #$nomprofesor = 'Anderson Code';
    #$fechaprofesor = '04/06/2020';
    #$firmadecano = 'firma.png';
    //Cargando template
    $template = 'planeador.docx';
    $TBS->LoadTemplate($template, OPENTBS_ALREADY_UTF8);
    //Escribir Nuevos campos
    $TBS->MergeField('pro.fecha_inicio', trim($fecha_inicio));
    $TBS->MergeField('pro.fecha_fin', trim($fecha_fin));
    $TBS->MergeField('pro.materia', trim($materia));
    $TBS->MergeField('pro.grado', trim($grado));
    $TBS->MergeField('pro.periodo', trim($periodo));
    $TBS->MergeField('pro.tiempo_plan', trim($tiempo_plan));
    $TBS->MergeField('pro.dba', trim($dba));
    $TBS->MergeField('pro.estrategias', trim($estrategias));
    $TBS->MergeField('pro.evidencias', trim($evidencias));
    $TBS->MergeField('pro.observaciones', trim($observaciones));
    $TBS->MergeField('pro.recursos', trim($recursos));
    $TBS->MergeField('pro.reflexion', trim($reflexion));
    $TBS->MergeField('pro.objetivo', trim( $objetivo));
    $TBS->MergeField('pro.eje_tematico', trim(str_replace(' ','', $eje_tematico)));
    
    
    $TBS->VarRef['x'] = $firmadecano;

    $TBS->PlugIn(OPENTBS_DELETE_COMMENTS);

    $save_as = (isset($_POST['save_as']) && (trim($_POST['save_as'])!=='') && ($_SERVER['SERVER_NAME']=='localhost')) ? trim($_POST['save_as']) : '';
    $output_file_name = str_replace('.', '_'.date('Y-m-d').$save_as.'.', $template);
    if ($save_as==='') {
        $TBS->Show(OPENTBS_DOWNLOAD, $output_file_name); 
        exit();
    } else {
        $TBS->Show(OPENTBS_FILE, $output_file_name);
        exit("File [$output_file_name] has been created.");
    }
}
?>