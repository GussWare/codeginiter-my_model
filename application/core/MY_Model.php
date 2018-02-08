<?php
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class MY_Model extends CI_Model
{
    /**
     * Nombre de la tabla
     *
     * @var string
     */
    protected $__table;
    /**
     * Nombre de la llave primaria
     *
     * @var int
     */
    protected $__primary_key;
    /**
     * Arreglo de campos de la tabla
     *
     * @var array
     */
    private $__fields;
    /**
     * Numero de registros del query
     *
     * @var int
     */
    private $__num_rows;
    /**
     * Numero de registros afectados a realizar un update
     *
     * @var int
     */
    private $__num_affected_rows;
    /**
     * Id del nuevo registro insertado en la tabla
     *
     * @var int
     */
    private $__insert_id;
    /**
     * Id del registro a actualizado
     *
     * @var int
     */
    private $__update_id;

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * load_table
     *
     * Metodo que se encarga de inicializar la tabla a la que pertenece el modelo
     * recibe como parametro el nombre de la tabla y la llave primaria de esta
     *
     * @access public
     * @param $table Nombre de la tabla
     * @param $primary_key Llave primaria autoincrementable de la tabla
     * @return object
     */
    public function load_table($table, $primary_key = "id", $fields = [])
    {
        $this->__table = $table;
        $this->__primary_key = $primary_key;

        if (count($fields) > 0) {
            $this->__fields = $fields;
        } else {
            $this->load_fields();
        }

        return $this;
    }

    /**
     * Metodo que se encarga de cargar de manera dinamica los nombres de los campos de la tabla
     *
     * @param string $table
     * @return void
     */
    public function load_fields($table = '')
    {
        if (!$table) {
            $table = $this->__table;
        }

        $this->__fields = $this->db->list_fields($table);
    }

    /**
     * set_primary_key
     *
     * Setea el valor de la llave primaria  a la que pertenece la tabla
     *
     * @access public
     * @param $primary_key Llave primaria autoincrementable de la tabla
     * @return object
     */
    public function set_primary_key($primary_key = 'id')
    {
        $this->__primary_key = $primary_key;
        return $this;
    }

    /**
     * Metodo que se encarga de setear los campos de la tabla
     *
     * @param array $fields
     * @return void
     */
    public function set_fields($fields = array())
    {
        $this->__fields = $fields;
    }

    /**
     * find_all
     *
     * Metodo que se encarga de generar la consulta de manera dinamica de acuerdo a los criterios de busqueda
     * recibe como parametros un arreglo con las condiciones de filtrado, los campos a mostrar en la consulta,
     * el criterio de ordenación, el offset para saber apartir de donde se mostrarar los registros asi como el limite
     * de registros a mostrar, adiconalmente se recibe un parametro para saber si se retornara un array de arrays o un array de objetos
     *
     * @access public
     * @param $conditions Arreglo de condiciones a filtrar
     * @param $fields Campos a mostrar de la consulta
     * @param $order Ordenacion de los registros
     * @param $start Registro apartir del cual se mostrara en la consulta
     * @param $limit Limite de registros a mostrar
     * @param $return_arr Bandera para saber si se registrar un array de arrays o un array de objectos
     * @return array
     */
    public function find_all($conditions = array(), $fields = array(), $order = null, $start = null, $limit = null, $return_arr = false)
    {
        if (count($fields) > 0) {
            $str_fields = '';
            foreach ($fields as $key => $value) {
                if (is_string($key)) {
                    $str_fields .= $key . ' AS ' . $value . ',';
                } else {
                    $str_fields .= $value . ',';
                }
            }
            if (!empty($str_fields)) {
                $str_fields = substr($str_fields, 0, -1);
            }
            $this->db->select($str_fields);
        }
        $this->db->from($this->__table);
        if (count($conditions) > 0) {
            $this->db->where($conditions);
        }
        if (isset($order) && is_string($order)) {
            $this->db->order_by($order);
        }
        if (isset($start) && isset($limit)) {
            $this->db->limit($start, $limit);
        }
        $query = $this->db->get();
        $data = array();
        if ($this->db->error()["code"] == 0) {
            $this->__num_rows = $query->num_rows();
            if ($this->__num_rows > 0) {
                $data = ($return_arr) ? $query->result_array() : $query->result();
            }
        }
        return $data;
    }
    /**
     * find
     *
     * Metodo que se encarga de recuperar un unico registro de la tabla de acuerdo a las condiciones de busqueda
     * retorna ya sea un array o un objecto con los datos obtenidos del query
     *
     * @access public
     * @param $conditions Arreglo de condiciones a filtrar
     * @param $fields Campos a mostrar de la consulta
     * @param $order Ordenacion de los registros
     * @param $return_arr Bandera para saber si se registrar un array de arrays o un array de objectos
     * @return array|object
     */
    public function find($conditions = array(), $fields = array(), $order = null, $start = null, $limit = null, $return_arr = false)
    {
        $data = $this->find_all($conditions, $fields, $order, $start, $limit, $return_arr);
        return (count($data) > 0) ? $data[0] : null;
    }
    /**
     * field
     *
     * Metodo que se encarga de recuperar un valor de un atributo del registro filtrado
     *
     * @access public
     * @param $conditions Arreglo de condiciones a filtrar
     * @param $fields Campos a mostrar de la consulta
     * @param $order Ordenacion de los registros
     * @return value
     */
    public function field($field = null, $conditions = null, $order = null, $start = null, $limit = null, $return_arr = false)
    {
        if (!isset($field)) {
            return null;
        }
        $value = $this->find($conditions, array($field), $order, $start, $limit, $return_arr);
        return (isset($value->$field)) ? $value->$field : null;
    }
    /**
     * find_count
     *
     * Metodo que retorna el numero de registros que coinsidan con el criterio de busqueda
     *
     * @access public
     * @param $conditions Arreglo de condiciones a filtrar
     * @return int
     */
    public function find_count($conditions = null)
    {
        if (isset($conditions)) {
            $this->db->where($conditions);
        }
        return $this->db->count_all($this->__table);
    }
    /**
     * find_by
     *
     * Merodo que se encarga de retornar un registro en particular, cuenta con 3 parametros field que es la columna por la
     * cual se realizara el filtrado y value que es el valor a filtrar, adicionalmente cuenta con un tercer parametro return_arr
     * que sirve para saber si el registro a retornar se regresara como un array o un objecto
     *
     * @access public
     * @param $field Columna por la cual se realizara el filtrado
     * @param $value Valor por el cual se filtrara el registro
     * @param $return_arr Bandera para identificar si se regresara un array o un objecto
     * @return array|object
     */
    public function find_by($field = null, $value = null, $return_arr = false)
    {
        if ($field == null || $value == null) {
            return null;
        }
        $where = array($field => $value);
        return $this->find($where, array(), null, null, null, $return_arr);
    }
    /**
     * find_all_by
     *
     * Metodo que retorna un array de registros deacuerdo al fitro llave valor que se pasa como paremtro
     *
     * @access public
     * @param $fields Campos a mostrar de la consulta
     * @param $order Ordenacion de los registros
     * @param $start Registro apartir del cual se mostrara en la consulta
     * @param $limit Limite de registros a mostrar
     * @param $return_arr Bandera para saber si se registrar un array de arrays o un array de objectos
     * @return array
     */
    public function find_all_by($field = null, $value = null, $fields = null, $order = null, $start = null, $limit = null, $return_arr = false)
    {
        if ($field == null || $value == null) {
            return null;
        }
        $where = array($field => $value);
        return $this->findAll($where, $fields, $order, $start, $limit, $return_arr);
    }
    /**
     * find_max
     *
     * Metodo que se encarga de recuperar el maximo valor en un campo, el campo debe ser tipo numerico
     *
     * @access public
     * @param $field Campo al cual se aplicara el Max
     * @param $conditions Condiciones de filtrado
     * @return numeric retorna el valor maximo
     */
    public function find_max($field = null, $conditions = null)
    {
        if ($field == null) {
            return null;
        }
        if (isset($conditions)) {
            $this->db->where($conditions);
        }
        $this->db->select_max($field);
        $this->db->from($this->__table);
        $query = $this->db->get();
        $max = null;
        if ($this->db->error()["code"] == 0) {
            if ($query->num_rows() > 0) {
                $record = $query->result();
                $max = $record[0]->$field;
            }
        }
        return $max;
    }
    /**
     * find_max_record
     *
     * Metodo que retorna el el record que contienen la columna con el maximo valor
     *
     * @access public
     * @param $field Campo al cual se aplicara el Max
     * @param $conditions Condiciones de filtrado
     * @return object Registro que contiene el valor maximo
     */
    public function find_max_record($field = null, $conditions = null, $return_arr = false)
    {
        if ($field == null) {
            return null;
        }
        $db_subquery = $this->db;
        $db_subquery->select_max($field);
        $db_subquery->from($this->__table);
        if (isset($conditions)) {
            $db_subquery->where($conditions);
        }
        $str_subquery = $db_subquery->get_compiled_select();
        if ($str_subquery == null || $str_subquery == false || $str_subquery == '') {
            return null;
        }
        $this->db->select('*');
        $this->db->from($this->__table);
        $this->db->where("({$str_subquery})", null, false);
        $query = $this->db->get();
        $record = null;
        if ($this->db->error()["code"] == 0) {
            $this->__num_rows = $query->num_rows();
            if ($this->__num_rows > 0) {
                $data = ($return_arr) ? $query->result_array() : $query->result();
                $record = $data[0];
            }
        }
        return $record;
    }
    /**
     * execute_query
     *
     * Metodo que se encarga de ejecutar un string de una consulta sql
     *
     * @access public
     * @param $sql string Consulta sql a ejecutar
     * @return object
     */
    public function execute_query($sql = null)
    {
        if ($sql == null || !is_string($sql)) {
            return null;
        }
        return $this->db->query($sql);
    }
    /**
     * add
     *
     * Metodo que se encarga de insertar un registro, recibe como parametros los datos a insertar
     *
     * @access public
     * @param $data object|array Datos a insertar
     * @return int Identificador del registro
     */
    public function add($data = null)
    {
        if (!isset($data)) {
            return null;
        }
        $new_data = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->__fields)) {
                $new_data[$key] = $value;
            }
        }
        $this->db->insert($this->__table, $new_data);
        $this->__insert_id = $this->db->insert_id();
        return $this->__insert_id;
    }
    /**
     * edit
     *
     * Metodo que se encarga de editar un registro, es necesario que venga el id dentro de la variable data
     *
     * @access public
     * @param $data object|array Datos a editar
     * @return int Identificador del registro
     */
    public function edit($data = null)
    {
        if ($data == null) {
            return null;
        }
        if (is_array($data)) {
            $data = (object) $data;
        }
        $primary_key = $this->__primary_key;
        if (!isset($data->$primary_key)) {
            return null;
        }
        $new_data = array();
        foreach ($data as $key => $value) {
            if (in_array($key, $this->__fields)) {
                $new_data[$key] = $value;
            }
        }
        $this->db->where($this->__primary_key, $data->$primary_key);
        $this->db->update($this->__table, $new_data);
        $this->__num_affected_rows = $this->db->affected_rows();
        $this->__update_id = $data->$primary_key;
        return $this->__update_id;
    }
    /**
     * save
     *
     * Metodo que se encarga de insertar o actualizar un registro dependiendo si entre los datos viene el el id
     * del registro, retorna el id
     *
     * @access public
     * @param $data object|array Datos a ingresar o editar
     * @return int Identificador del registro
     */
    public function save($data = null)
    {
        if ($data == null) {
            return null;
        }
        if (is_array($data)) {
            $data = (object) $data;
        }
        $primary_key_value = null;
        $pimary_key = $this->__primary_key;
        if (isset($data->$pimary_key)) {
            $primary_key_value = $this->edit($data);
        } else {
            $primary_key_value = $this->add($data);
        }
        return $primary_key_value;
    }
    /**
     * remove
     *
     * Metodo que se encarga de eliminar un registro de la tabla, recibe como parametro el id del registro
     *
     * @access public
     * @param $primary_key int identificador unico del registro
     * @return boolean
     */
    public function remove($primary_key = null)
    {
        if (($primary_key == null) || (!is_numeric($primary_key))) {
            return null;
        }
        return $this->db->delete($this->__table, array(
            $this->__primary_key => $primary_key,
        ));
    }
    /**
     * get_last_query
     *
     * Metodo que se encarga de generar un string con la ultima consulta generada
     *
     * @access public
     * @return string
     */
    public function get_last_query()
    {
        return $this->db->last_query();
    }
    /**
     * get_insert_string
     *
     * Metodo que se encarga de generar un string con la consulta de INSERTAR, recibe un array con los
     * registros y valores a ingresar en la tabla
     *
     * @access public
     * @param $data array|object Datos a insertar a la tabla
     * @return string
     */
    public function get_insert_string($data = null)
    {
        if ($data == null) {
            return null;
        }
        return $this->db->insert_string($table, $data);
    }
    /**
     * get_fields
     *
     * Metodo que se encarga de recuperar un array con los nombres de las columnas  de la tabla
     *
     * @access public
     * @return array
     */
    public function get_fields()
    {
        return $this->__fields;
    }
    /**
     * get_num_rows
     *
     * Metodo que se encarga de recuperar el numero de registros de una consulta
     *
     * @access public
     * @return int
     */
    public function get_num_rows()
    {
        return $this->__num_rows;
    }
    /**
     * get_affected_rows
     *
     * Metodo que se encarga de recuperar el numero de registros afectados por un update
     *
     * @access public
     * @return int
     */
    public function get_affected_rows()
    {
        return $this->__num_affected_rows;
    }
}
