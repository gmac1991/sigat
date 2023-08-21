<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Fila_model extends CI_Model {

    public function listaFilas()  {
        return $this->db->query("
            SELECT * FROM fila
        ")->result_array();
    }
}