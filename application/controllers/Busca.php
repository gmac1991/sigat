<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Busca extends CI_Controller {

  function __construct() {
    parent::__construct();
    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("usuario_model"); 
  }

  function index() {

    $t = $this->input->get("t");
    $ticketbusca = $this->input->get("ticketbusca");
    

    $dados = array();

    if(isset($t)){
      $dados['termo'] = urldecode($t);

      $dados['result'] = $this->consultas_model->buscaRapida($t);

      $this->load->view('paginas/busca',$dados);
    }

  }


}