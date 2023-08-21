<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Triagem extends CI_Controller {



  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    $this->load->model("servico_model"); //carregando o model servico
    $this->load->model("triagem_model"); //carregando o model triagem
    //$this->load->library("Charset_normalizer");

    
  }

  public function index($id_ticket = NULL) { //exibe triagem
    if (isset($_SESSION['id_usuario'])) {

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      if ($usuario->triagem_usuario == 0) {

        header('Location: ' . base_url());

      }

      else {

        $dados = array();

      
		    $dados['triagem']  = $this->triagem_model->buscaTicket($id_ticket); //traz info do ticket		    
		   
		    if (isset($dados['triagem'] )) { // se o ticket existir

          $dados['fila_sigat'] =  $this->config->item('conversao_id_filas')[$dados['triagem']['t_info']->queue_id];


          $this->load->view('templates/cabecalho', $usuario);

          $this->load->view('paginas/triagem', $dados);

          $this->load->view('templates/rodape');
			
		    }
        else {
          show_404();
        }
		  }
    } else {
      header('Location: ' . base_url(),false,403);
    }
  }


  public function listar_triagem() {

    $result_banco = $this->triagem_model->listaTriagem();
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {

      $lista_painel['data'][] = array(
        0 => $linha->id,
        1 => "Ticket#" . $linha->tn,
        2 => $linha->create_time,
        3 => $linha->title,
        4 => $linha->a_from,
      ); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }



  public function carregar_anexos_ticket() {

    if (isset($_SESSION['id_usuario'])) {

      $result = array();
            
      $id_ticket = $this->input->get('id_ticket');

      $result = $this->triagem_model->buscaAnexosTicket($id_ticket);

      header("Content-Type: application/json");
          
      echo json_encode($result);

    } else {
      header('Location: ' . base_url(),false,403);
    }



  }

}
