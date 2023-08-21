<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
class Fila extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("fila_model"); 
    }

    public function listar_filas() {
        if (isset($_SESSION['id_usuario'])) {
            header("Content-Type: application/json");
            echo json_encode($this->fila_model->listaFilas());
        }
    }

}