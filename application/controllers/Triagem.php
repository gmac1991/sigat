<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Triagem extends CI_Controller {

  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario

    
  }

  public function index($id_triagem = NULL) { //controle dos chamados
    if (isset($_SESSION['id_usuario'])) {
    
		$dados = NULL;
		
		$dados['triagem'] = $this->consultas_model->buscaTriagem($id_triagem); //traz chamado migrado
		
		if (isset($dados['triagem'])) { // se o chamado existir
			
			if ($dados['triagem']->id_fila_chamado == NULL) { //se nao tiver fila setada..
				
				$usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

				$this->load->view('templates/cabecalho', $usuario);

				$lista_filas = $this->consultas_model->listaFilas(); 
				$lista_solicitantes = $this->consultas_model->listaSolicitantes();
				$lista_locais = $this->consultas_model->listaLocais();
				$dados = array_merge($dados,array("filas" => $lista_filas, "solicitantes" => $lista_solicitantes, "locais" => $lista_locais));

				
				$dados['usuarios'] = $this->usuario_model->buscaUsuarios(); //traz a lista de todos os usuarios

				$this->load->view('paginas/triagem', $dados);

				$this->load->view('templates/rodape');
			}
			else {
				show_404();
			}
			
			
		}
		
		else {
			show_404();
		}
		

    } else {
      header('Location: ' . base_url(),false,403);
    }
  }


public function listar_triagem() {

    $result_banco = $this->consultas_model->listaTriagem();
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {
		
	

		$lista_painel['data'][] = array(
                              0 => $linha->id_triagem,
                              1 => $linha->ticket_triagem,
                              2 => $linha->data_chamado,
                              3 => $linha->nome_solicitante_chamado,
							                4 => $linha->email_chamado,
                              5 => "<a href=\"" . base_url('triagem/' . $linha->id_chamado) . 
                              "\" rel=\"noopener\" role=\"button\"" .
                              " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i></a> "); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
 
}

?>