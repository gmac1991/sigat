<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Servico extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("servico_model"); //carregando o model dos servicos
        
    }

    public function listar_servicos_triagem() {
        $grupos = $this->input->post("grupos");
        $lista = $this->servico_model->listaServicos(NULL,$grupos,"ATIVO"); 
        header('Content-Type: application/json');
        echo json_encode($lista);
    }

    

}