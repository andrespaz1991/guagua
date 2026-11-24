<?php
class Institucion extends Clase_mysqli
{
    public $id_institucion_educativa = 0;
    public $nombre_institucion = '';
    public $logo_institucion = '';
    public $direccion_institucion = '';
    public $telefono_institucion = '';
    public $correo_institucion = '';
    public $BANNER_INSTITUCION = '';
    public $formatos_no_permitidos = '';
    public $tamano_maximo_adjunto = '';
    public $autoregistrarse = '';
    public $plantilla;

    public function __construct($id_iem = '')
    {
        $this->id_institucion_educativa = (int)($id_iem !== '' ? $id_iem : ($_SESSION['id_institucion'] ?? 0));
        if ($this->id_institucion_educativa > 0) $this->datos_institucion();
    }

    public function __SET($atributo, $valor)
    {
        return $this->$atributo = $valor;
    }

    public function datos_institucion($todas = 0)
    {
        require_once __DIR__ . '/../comun/sql.php';
        $db = $this->conectar();
        if ($todas) return $db->query('SELECT * FROM institucion_educativa ORDER BY id_institucion_educativa DESC')->fetch_all(MYSQLI_ASSOC);
        $filas = guagua_sql($db, 'SELECT * FROM institucion_educativa WHERE id_institucion_educativa = ? LIMIT 1', [$this->id_institucion_educativa], 'i')->fetch_all(MYSQLI_ASSOC);
        foreach ($filas[0] ?? [] as $campo => $valor) $this->__SET($campo, $valor);
        return $filas;
    }
}