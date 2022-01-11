<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chamado extends CI_Controller {

  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario

    
  }

  public function index($pagina = NULL, $id_chamado = NULL) { //controle dos chamados
    if (isset($_SESSION['id_usuario'])) {
    
      if ( ! file_exists(APPPATH.'views/paginas/chamado/'.$pagina.'.php') ) {
       show_404();
      }
      $dados = NULL;

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      $this->load->view('templates/cabecalho', $usuario);


      if ($pagina == 'abrir_chamado') {

        $lista_filas = $this->consultas_model->listaFilas(); 
        $lista_solicitantes = $this->consultas_model->listaSolicitantes();
        $lista_locais = $this->consultas_model->listaLocais();
        $dados = array("filas" => $lista_filas, "solicitantes" => $lista_solicitantes, "locais" => $lista_locais);

        $this->load->view('paginas/chamado/'.$pagina, $dados);

      }

      elseif ($pagina == 'ver_chamado') {

        $dados = $this->chamado_model->buscaChamado($id_chamado); //traz patrimonios, info do chamado e anexos...
        $dados['usuarios'] = $this->usuario_model->buscaUsuarios(); //traz a lista de todos os usuarios

        if (isset($dados['chamado'])) {

          $this->load->view('paginas/chamado/'.$pagina, $dados);

        } else {

          show_404();
        }
      }

      $this->load->view('templates/rodape');

    } else {
      header('Location: ' . base_url(),false,403);
    }
  }

  public function registrar_chamado() { //registro do chamado

    

    $dados = array();

    // campos

    $dados['id_fila'] =           $this->input->post("id_fila");
    $dados['nome_solicitante'] =  $this->input->post("nome_solicitante");
    $dados['nome_local'] =        $this->input->post("nome_local");
    $dados['telefone'] =          $this->input->post("telefone");
    $dados['descricao'] =         $this->input->post("descricao");
    $dados['listaPatrimonios'] =  $this->input->post("listaPatrimonios");
    $dados['id_usuario'] =        $this->input->post("id_usuario");


    // ------ UPLOAD DO ANEXO --------- //


    if($this->input->post("temAnexo") == 1) {

    $config = array();

    $config['upload_path']          = './anexos/';
    $config['allowed_types']        = 'gif|jpg|png|pdf|doc|docx|xls|xlsx|odt|ods|jpeg|txt'; //tipos de arquivos permitidos
    $config['max_size']             = 5000; //tamanho maximo: 5 Megabytes

    $this->load->library('upload', $config);

    if (! $this->upload->do_upload('anexo')) {

      $dados['erros_upload'] = array('error' => $this->upload->display_errors());
      //echo 'passei aqui';

      } else {

      $dados['nome_anexo'] = trim($this->upload->data('file_name'));
      //echo 'passei aqui';

      }

    }

    $this->chamado_model->registraChamado($dados);

  }

  public function importar_chamado() {
    

    

      $dados = array();
  
      // campos
  
      $dados['id_fila'] =           $this->input->post("id_fila");
      $dados['id_chamado'] =        $this->input->post("id_chamado");
      $dados['nome_solicitante'] =  $this->input->post("nome_solicitante");
      $dados['nome_local'] =        $this->input->post("nome_local");
      $dados['telefone'] =          $this->input->post("telefone");
      $dados['listaPatrimonios'] =  $this->input->post("listaPatrimonios");
      $dados['id_usuario'] =        $this->input->post("id_usuario");
  
  
      $this->chamado_model->importaChamado($dados);
    
  }

  public function encerrar_chamado() { //encerrar chamado

    $dados = array();

    // campos

    $dados['id_chamado'] =        $this->input->post("id_chamado");
   
    $dados['id_usuario'] =        $_SESSION['id_usuario'];

    $this->chamado_model->encerraChamado($dados);


  }

  

  public function alterar_chamado() { //alteracao do chamado

    $dados = array();

    // campos

    $dados['id_chamado'] =        $this->input->post("id_chamado");
    $dados['nome_solicitante'] =  $this->input->post("nome_solicitante");
    $dados['nome_local'] =        $this->input->post("nome_local");
    $dados['telefone'] =          $this->input->post("telefone");
    $dados['id_responsavel'] =    $this->input->post("id_responsavel");
    $dados['id_usuario'] =        $_SESSION['id_usuario'];

    $this->chamado_model->alteraChamado($dados);

  }

  public function listar_chamados_painel($id_fila = NULL) {

    $result_banco = $this->consultas_model->listaChamados($id_fila,$_SESSION['id_usuario']);
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {
		
	
		$nome_local = $linha->nome_local;
		
		$nome_local .=  " <span class=\"badge badge-secondary\">";
		
		switch ($linha->id_fila_chamado) {
			case 1:
				$nome_local .= "<i class=\"fas fa-headset\" title=\"Atendimento Remoto\"></i>";
				break;
			case 2:
				$nome_local .=  "<i class=\"fas fa-walking\" title=\"Atendimento Presencial\"></i>";
				break;
			case 3:
				$nome_local .=  "<i class=\"fas fa-tools\" title=\"Manutenção de Hardware\"></i>";
				break;
			case 4:
				$nome_local .=  "<i class=\"fas fa-wifi\" title=\"Manutenção de Rede\"></i>";
				break;
			case 5:
				$nome_local .=  "<i class=\"fas fa-phone-alt\" title=\"Telefonia\"></i>";
				break;
			case 6:
				$nome_local .=  "<i class=\"fas fa-hand-holding-medical\" title=\"Solicitação de Equipamento\"></i>";
				break;
		}
		
		$nome_local .=  "</span>";
		
		
		
		if ($linha->entrega_chamado == 1)
			$nome_local .= " <span class=\"badge badge-success\" title=\"Entrega\"><i class=\"fas fa-truck\"></i></span>"; //inserindo badge de entrega
	

		$lista_painel['data'][] = array(0 => $linha->id_chamado,
                              1 => $linha->nome_solicitante_chamado,
                              2 => $nome_local,
                              3 => $linha->data_chamado,
                              4 => $linha->nome_responsavel,
                              5 => $linha->status_chamado,
                              6 => "<button class=\"btn btn-secondary btn-sm btn-block PopoverPainel\"" .
                              " data-chamado=\"" . $linha->id_chamado . "\">" .
                              "<i class=\"far fa-clock\"></i></button>", // ultima interacao
                              7 => "<a href=\"" . base_url('chamado/' . $linha->id_chamado) . 
                              "\" rel=\"noopener\" target=\"_blank\" role=\"button\"" .
                              " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i></a> "); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
  

  public function listar_encerrados_painel($id_fila) {

    $result_banco = $this->consultas_model->listaEncerrados($id_fila);
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {

      $lista_painel['data'][] = array(0 => $linha->id_chamado,
                              1 => $linha->nome_solicitante_chamado,
                              2 => $linha->nome_local,
                              3 => $linha->data_chamado,
                              4 => $linha->nome_responsavel,
                              5 => $linha->status_chamado,
							  6 => "", // ultima interacao
                              7 => "<a href=\"" . base_url('chamado/' . $linha->id_chamado) . "\" rel=\"noopener\" target=\"_blank\" role=\"button\"" .
                            " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i> Detalhes</a>"); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
 
}

?>