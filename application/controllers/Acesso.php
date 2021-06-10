<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Acesso extends CI_Controller {

  function __construct() {
    parent::__construct();

    

    
    
    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("usuario_model");  //carregando o model usuario 
    
    
    

    
  }
  
  public function index($resposta = NULL) {

   if (!isset($_SESSION['id_usuario'])) {

    $dados = array();

    if ($resposta == 'erro') { // caso o usuario seja invalido, enviar a msg para a view
      $dados = array('msg' => '<div class="alert alert-warning">Usuário ou senha incorretos!</div>');
    }
      
      $this->load->view('paginas/acesso',$dados);

    } else {
      /*$fila_usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario'])->fila_usuario;
      header('Location: ' . base_url('painel/' . $fila_usuario));*/
      header('Location: ' . base_url('painel'));
    }
      
  }

  public function entrar() {

    $dados = array();
    $usuario = array();

    $dados['login_usuario'] = $this->input->post('login_usuario');
    $dados['senha_usuario'] = $this->input->post('senha_usuario');

    // removendo aspas duplas e simples
    $dados['login_usuario'] = str_replace('"', "", $dados['login_usuario']);
    $dados['login_usuario'] = str_replace("'", "", $dados['login_usuario']);
    $dados['senha_usuario'] = str_replace('"', "", $dados['senha_usuario']);
    $dados['senha_usuario'] = str_replace("'", "", $dados['senha_usuario']);

    $usuario = $this->usuario_model->validaUsuario($dados);

    

    if (!empty($usuario)) {
      $_SESSION['id_usuario'] = $usuario['id_usuario'];

      /*$fila_usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario'])->fila_usuario;
      header('Location: ' . base_url('painel/' . $fila_usuario));*/
      header('Location: ' . base_url('painel'));

      // ------------ LOG -------------------

      $log = array(
        'acao_evento' => 'LOGON_SISTEMA',
        'desc_evento' => '',
        'id_usuario_evento' => $_SESSION['id_usuario']
      );
  
      $this->db->insert('evento', $log);

    // -------------- /LOG ----------------

    } else {
		
		//var_dump($usuario);
		
		// ------------ LOG -------------------

		$log = array(
			'acao_evento' => 'ERRO_LOGON',
			'desc_evento' => 'NOME DE USUARIO: ' . $dados['login_usuario'],
			'id_usuario_evento' => '1'
		 );
	  
		$this->db->insert('evento', $log);

    // -------------- /LOG ----------------
      
      
      header('Location: ' . base_url('/erro')); //caso usuario foi invalido, retornar valor 'erro' para o index via URL


    }
    
    

  }

  public function sair() {

  
    // ------------ LOG -------------------

    $log = array(
      'acao_evento' => 'LOGOFF_SISTEMA',
      'desc_evento' => '',
      'id_usuario_evento' => $_SESSION['id_usuario']
    );
  
    $this->db->insert('evento', $log);

    // -------------- /LOG ----------------

    session_destroy();
    setcookie("ci_session", "", 1);
    
    header('Location: ' . base_url());

  }
  
  public function painel($id_fila = NULL) {

    
    if (isset($_SESSION['id_usuario'])) {

     // $lista_chamados = $this->consultas_model->listaChamados($id_fila,$_SESSION['id_usuario']);

      $lista_filas = $this->consultas_model->listaFilas();

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);


      if (empty($this->consultas_model->listaFila($id_fila))) {

        $id_fila = 0;
      }

      $dados = array("filas" => $lista_filas, "id_fila" => $id_fila, "fila_atual" => $id_fila);

      $this->load->view('templates/cabecalho', $usuario);
      $this->load->view('paginas/painel', $dados);
      $this->load->view('templates/rodape');
    } else {
			
			header('Location: ' . base_url(),false,403);
		}
    
  }
  

    
}
