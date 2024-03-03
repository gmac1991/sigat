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
         $dados = array('msg' => '<div class="alert alert-warning text-center">Usuário/senha incorretos ou usuário desativado!</div>');
       }
        
       $this->load->view('paginas/acesso',$dados);

     } else {
       /*$fila_usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario'])->fila_usuario;
       header('Location: ' . base_url('painel/' . $fila_usuario));*/
       header('Location: ' . base_url('painel'));
     }

    //echo phpinfo();
  
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

    $this->load->library("consulta_ldap");

    $ldap = new Consulta_LDAP($dados['login_usuario'],$dados['senha_usuario']);

    
    
    $usuario = $this->usuario_model->validaUsuario($dados);

    if ($usuario === NULL) {

      // ------------ LOG -------------------

      $log = array(
        'acao_evento' => 'ERRO_LOGON',
        'desc_evento' => 'NOME DE USUARIO: ' . $dados['login_usuario'],
        'id_usuario_evento' => '1'
      );
    
      $this->db->insert('evento', $log);

      // -------------- /LOG ----------------

      header('Location: ' . base_url('/erro')); //caso usuario foi invalido ou inativo, retornar valor 'erro' para o index via URL

    } 

    else {

      $autentica_LDAP = $ldap->validaLogin();

      if ($autentica_LDAP === TRUE) {
      
        $_SESSION["usi"] = $dados['login_usuario'];
        $_SESSION["psi"] = $dados['senha_usuario'];

        $_SESSION['id_usuario'] = $usuario['id_usuario'];

        header('Location: ' . base_url('painel'));
        
        // ------------ LOG -------------------

        $log = array(
          'acao_evento' => 'LOGON_SISTEMA',
          'desc_evento' => '',
          'id_usuario_evento' => $_SESSION['id_usuario']
        );
    
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------


      }

      else {

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
    
    $this->load->helper('cookie');
    unset($_SESSION["usi"]);
    unset($_SESSION["psi]"]);
    delete_cookie("ci_session");
    
    header('Location: ' . base_url());

  }
  
  public function painel($id_fila = NULL) {

    
    if (isset($_SESSION['id_usuario'])) {


      $lista_filas = $this->consultas_model->listaFilas();

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      $id_fila_usuario = $usuario->fila_usuario;

      $triagem_usuario = $usuario->triagem_usuario;

      



      $dados = array( "filas" => $lista_filas, 
                      "fila_usuario" => $id_fila_usuario, 
                      "fila_atual" => $id_fila, 
                      "triagem_usuario" => $triagem_usuario,
                      
                    );

      $this->load->view('templates/cabecalho', $usuario);
      $this->load->view('paginas/painel', $dados);
      $this->load->view('templates/rodape');
    } else {
			
			header('Location: ' . base_url('/'));
		}
    
  }

  // public function apiAcesso() {

  //   $dados = array();
  //   $usuario = array();

  //   $dados['login_usuario'] = $GET['login_usuario'];
  //   $dados['senha_usuario'] = $GET['senha_usuario'];
  //   var_dump($_GET);

  //   // removendo aspas duplas e simples
  //   $dados['login_usuario'] = str_replace('"', "", $dados['login_usuario']);
  //   $dados['login_usuario'] = str_replace("'", "", $dados['login_usuario']);
  //   $dados['senha_usuario'] = str_replace('"', "", $dados['senha_usuario']);
  //   $dados['senha_usuario'] = str_replace("'", "", $dados['senha_usuario']);

  //   $this->load->library("consulta_ldap");

  //   $ldap = new Consulta_LDAP($dados['login_usuario'],$dados['senha_usuario']);

    
    
  //   //$usuario = $this->usuario_model->validaUsuario($dados);

  //   /* if ($usuario === NULL) {

  //     // ------------ LOG -------------------

  //     $log = array(
  //       'acao_evento' => 'ERRO_LOGON',
  //       'desc_evento' => 'NOME DE USUARIO: ' . $dados['login_usuario'],
  //       'id_usuario_evento' => '1'
  //     );
    
  //     $this->db->insert('evento', $log);

  //     // -------------- /LOG ----------------

  //     header('Location: ' . base_url('/erro')); //caso usuario foi invalido ou inativo, retornar valor 'erro' para o index via URL

  //   } 

  //   else { */

  //   $autentica_LDAP = $ldap->validaLogin();

  //   if ($autentica_LDAP === TRUE) {
    
  //     // $_SESSION["usi"] = $dados['login_usuario'];
  //     // $_SESSION["psi"] = $dados['senha_usuario'];
  //     $res = array(
  //       "auth" => true
  //     );
  //     // $_SESSION['id_usuario'] = $usuario['id_usuario'];

  //     header("Content-Type: application/json");
  //     echo json_encode($res);
      
  //     // ------------ LOG -------------------

  //     $log = array(
  //       'acao_evento' => 'LOGON_SISTEMA',
  //       'desc_evento' => '',
  //       //'id_usuario_evento' => $_SESSION['id_usuario']
  //     );
  
  //     $this->db->insert('evento', $log);

  //     // -------------- /LOG ----------------


  //   }

  //   else {
      
  //     // ------------ LOG -------------------

  //     $log = array(
  //       'acao_evento' => 'ERRO_LOGON',
  //       'desc_evento' => 'NOME DE USUARIO: ' . $dados['login_usuario'],
  //       'id_usuario_evento' => '1'
  //     );
    
  //     $this->db->insert('evento', $log);

  //     // -------------- /LOG ----------------    
  //     $res = array(
  //       "auth" => false
  //     );
  //     header("Content-Type: application/json");
  //     echo json_encode($res);
  //   }
  // }
  

    
}
