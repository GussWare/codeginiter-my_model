<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Usuarios extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->model('Usuario_model');
    }

    public function index()
    {
        $data = $this->Usuario_model->find_all();

        var_dump($data);
    }

}

/* End of file Usuarios.php */
