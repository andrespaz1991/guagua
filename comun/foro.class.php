<?php
/**
 * Clase Foros
 *  
 * 
 * 
 * 
 * 
 * */
 
class foro{
    
    public $propiedad1;
    public $propiedad2;
    public $propiedad3;
    public $propiedad4;
    
    function __construct($propiedad1="",$propiedad2="",$propiedad3="",$propiedad4="") {
        if($propiedad1!="") $this->$propiedad1 = $propiedad1;
        if($propiedad2!="") $this->$propiedad2 = $propiedad2;
        if($propiedad3!="") $this->$propiedad3 = $propiedad3;
        if($propiedad4!="") $this->$propiedad4 = $propiedad4;
    }
    
    function consultar_mis_foros($buscar="",$contexto = "general"){
    @session_start();
    require("../comun/conexion.php");
    $wherecontexto = '';
    $wherecontexto = '`contexto` = "'.$contexto.'"  ';
    $sql_misforos = 'SELECT `iconos`.`imagen_icono`,`grupo_foro`.`id_grupo_foro`, `grupo_foro`.`nombre_grupo`, `grupo_foro`.`roles_grupo`, `grupo_foro`.`contexto`, `grupo_foro`.`valor`, COUNT(`entrada`.`id`) AS num_entradas FROM `grupo_foro` left join `entrada` on `grupo_foro`.`id_grupo_foro` = `entrada`.`grupo` left join `iconos` on `grupo_foro`.`icono` = `iconos`.`id_iconos` WHERE '.$wherecontexto.' ';
    if ($_SESSION['rol']!="admin") 
    $sql_misforos .=' and `roles_grupo` LIKE "%'.$_SESSION['rol'].'%" ';
$parametro_buqueda_array = explode(" ",$buscar);
if (count($parametro_buqueda_array)>0)
foreach ($parametro_buqueda_array as $id => $parametro_buquedai){
$sql_misforos.= ' and concat(LOWER(`grupo_foro`.`nombre_grupo`)) LIKE "%'.mb_strtolower($parametro_buquedai, 'UTF-8').'%" ';
#echo $parametro_buquedai;
}
    $sql_misforos .= ' GROUP BY  `grupo_foro`.`id_grupo_foro`';
    #echo $sql_misforos;
    $consulta_misforos = $mysqli->query($sql_misforos);
    $mis_foros = array();
    while($row_misforos=$consulta_misforos->fetch_assoc()){
    $mis_foros[]=$row_misforos;
    }
    return $mis_foros;
    }
    function datos_foro($id_grupo){
    require dirname(__FILE__).'/../comun/conexion.php';
    require_once dirname(__FILE__).'/../comun/funciones.php';
    $sql_ent_gral = "SELECT * from `grupo_foro` WHERE `id_grupo_foro` = $id_grupo";
    $row_ent_gral=array();
    if ($consulta_ent_gral = $mysqli->query($sql_ent_gral))
    $row_ent_gral = $consulta_ent_gral->fetch_assoc();
    return $row_ent_gral;
    }
    function listar_entradas($id_grupo){
    require dirname(__FILE__).'/../comun/conexion.php';
    require_once dirname(__FILE__).'/../comun/funciones.php';
    $sql = "SELECT usuario.foto,`entrada`.`id`,`entrada`.`estado`, `entrada`.`suscribirse`, `entrada`.`contenido`, `entrada`.`rol_quien_comenta`, `entrada`.`fecha`, `entrada`.`grupo`, `entrada`.`usuario`, `entrada`.`estrellas`, `entrada`.`visitas`, `usuario`.`usuario`, concat(`usuario`.`nombre`,' ', `usuario`.`apellido`) as nombre_usuario, COUNT(`comentario`.`id_comentario`) AS num_comentarios FROM `entrada` left join `comentario` on `entrada`.`id` = `comentario`.`id_entrada` inner join `usuario` on `entrada`.`usuario` = `usuario`.`id_usuario` where  `entrada`.`grupo`='".$id_grupo."' GROUP BY `entrada`.`id` ORDER BY `entrada`.`fecha` desc";
    $consulta_centradas = $mysqli->query($sql);
    $total_entradas = $consulta_centradas->num_rows;
    $mis_entradas = array();
    #$mis_entradas[]=$row_ent_gral
        while($row = $consulta_centradas->fetch_assoc()){
        $mis_entradas[] = $row;
        }
    return $mis_entradas;
    }
    function listar_comentarios($id_entrada){
    require dirname(__FILE__).'/../comun/conexion.php';
    require_once dirname(__FILE__).'/../comun/funciones.php';
    $sql = "SELECT *,usuario.foto, concat(usuario.nombre,\" \",usuario.apellido) as nombre_usuario,`entrada`.`estado` FROM entrada inner join usuario on entrada.usuario=usuario.id_usuario where id = '$id_entrada'";
    $consulta_entrada = $mysqli->query($sql);
	$num = $consulta_entrada->num_rows;
	$mi_entrada = array();
	$mis_comentarios = array();
	if ($row_entrada=$consulta_entrada->fetch_assoc()){
	    $mi_entrada['datos_entrada']=$row_entrada;
    $sql_com = "SELECT `comentario`.`id_comentario`, `comentario`.`fecha`, `comentario`.`contenido`, `usuario`.`usuario`, concat(`usuario`.`nombre`,' ', `usuario`.`apellido`) as nombre_usuario FROM `comentario` inner join `usuario` on `comentario`.`usuario` = `usuario`.`id_usuario` WHERE `id_entrada` = ".$id_entrada." ORDER BY comentario.fecha desc";
	$consulta_comentarios = $mysqli->query($sql_com);
	$num = $consulta_comentarios->num_rows;
	if ($num>0){
	while ($row_comentarios=$consulta_comentarios->fetch_assoc()){
	    $mis_comentarios[] = $row_comentarios;
	}
	}
	$mi_entrada['comentarios']=$mis_comentarios;
	return($mi_entrada);
    }//fin if
    }//fin function listar entrada
    function top5($grupo=""){
    require dirname(__FILE__).'/../comun/conexion.php';
     $where = "";
     $mis_comentarios=array();
    if ($grupo!=""){
      $where = " WHERE `grupo_foro`.`id_grupo_foro` ='".$grupo."'";  
    $sql = 'SELECT entrada.id, entrada.contenido, entrada.fecha, entrada.usuario, entrada.estrellas, COUNT(`comentario`.`id_comentario`) AS num_comentarios, usuario.foto, concat(usuario.nombre," ",usuario.apellido) as nombre_usuario FROM entrada inner join usuario on entrada.usuario=usuario.id_usuario inner join `comentario` on `comentario`.`id_entrada` = `entrada`.`id` inner join `grupo_foro` on `entrada`.`grupo` = `grupo_foro`.`id_grupo_foro` '.$where.' GROUP BY `entrada`.`id` ORDER BY num_comentarios desc LIMIT 5';
    $consulta_comentarios = $mysqli->query($sql);
	$num = $consulta_comentarios->num_rows;
	if ($num>0){
	while ($row_comentarios=$consulta_comentarios->fetch_assoc()){
	    $mis_comentarios[] = $row_comentarios;
	}
	}
    }//fin if
	return($mis_comentarios);
    }
    function guardar_entrada($contenido,$grupo){
        @session_start();
        require dirname(__FILE__).'/../comun/conexion.php';
        $sql = "INSERT INTO `entrada`(`contenido`, `rol_quien_comenta`, `fecha`, `grupo`, `usuario`, `estado`) VALUES (
    '".$contenido."','".$_SESSION['rol']."','".date("Y-m-d H:i:s")."','".$grupo."','".$_SESSION['id_usuario']."','Publicado');";
    	$insertar = $mysqli->query($sql);
    	if ($mysqli->affected_rows > 0){
    	    return true;
    	}else{
    	    return false;
    	}

    }
    function guardar_comentario($contenido,$entrada){
        @session_start();
        require dirname(__FILE__).'/../comun/conexion.php';
        require_once dirname(__FILE__).'/../comun/funciones.php';
        $sql = "INSERT INTO `comentario`(`id_entrada`, `fecha`, `contenido`, `usuario`, `rol_quien_comenta`, `estado`) VALUES ('".$entrada."','".date("Y-m-d H:i:s")."','".$contenido."','".$_SESSION['id_usuario']."','".$_SESSION['rol']."','Publicado');";
    	$insertar = $mysqli->query($sql);
    $tema = consultar_nombre_tema($entrada);//consultar tema
    $suscriptores = consultar_susctiptores($entrada);
    //print_r($suscriptores);

        foreach ($suscriptores as $identificacion => $nombre){
            $mensaje = "Hola ".$nombre.", ";
            $mensaje .= $_SESSION['nombre']." ".$_SESSION['apellido'];
            $mensaje .= " ha comentado en el tema ".$tema.". ";
            $mensaje .= "\n '".$contenido."'.";
            $resultado_mensaje = enviar_mensaje($identificacion,$mensaje,$identificacion,'suscripciones');
           
        }

    	if ($mysqli->affected_rows > 0){
    	    return true;
    	}else{
    	    return false;
    	}

    }
}//fin class foro
?>