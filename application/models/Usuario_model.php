<?php 

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario_model extends MY_Model {

    
    public function __construct()
    {
        parent::__construct();

        $this->load_table('usuarios', 'id_usuario');
    }
}

/* End of file Usuario_model.php */
