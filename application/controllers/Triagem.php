<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Triagem extends CI_Controller {

  private $tags_permitidas = "<table><tr><th><td><tbody><thead><p><br><ul><ol><li><span><style><mark><pre><div><font>";


  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    $this->load->library("Charset_normalizer");

    
  }

  public function index($id_triagem = NULL) { //controle dos chamados
    if (isset($_SESSION['id_usuario'])) {
    
		$dados = NULL;
		
		$dados['triagem'] = $this->consultas_model->buscaTriagem($id_triagem); //traz chamado migrado
		
		if (isset($dados['triagem'])) { // se o chamado existir
				
      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      $this->load->view('templates/cabecalho', $usuario);

      //$lista_filas = $this->consultas_model->listaFilas(); 
      $lista_solicitantes = $this->consultas_model->listaSolicitantes();
      $lista_locais = $this->consultas_model->listaLocais();
      $dados = array_merge($dados,array(/*"filas" => $lista_filas,*/ "solicitantes" => $lista_solicitantes, "locais" => $lista_locais));

      
      $dados['usuarios'] = $this->usuario_model->buscaUsuarios(); //traz a lista de todos os usuarios

      $this->load->view('paginas/triagem', $dados);

      $this->load->view('templates/rodape');
			
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
                              2 => $linha->data_triagem,
                              3 => $linha->nome_solicitante_triagem,
                              4 => $linha->email_triagem,
                              // 5 => "<a href=\"" . base_url('triagem/' . $linha->id_triagem) . 
                              // "\" rel=\"noopener\" role=\"button\"" .
                              // " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i></a> "
                            ); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }

  public function gerar_descricao_iframe($id_triagem) {

    $ticket_triagem =  $this->db->query("select ticket_triagem from triagem 
    where id_triagem = " . $id_triagem)->row()->ticket_triagem;

    $desc = $this->db->query("select descricao_triagem from triagem where id_triagem = " . $id_triagem)->row()->descricao_triagem;

    $chamado_existente = $this->db->query("select * from chamado 
                        where ticket_chamado = '" 
                        . $ticket_triagem . "' 
                        and status_chamado = 'ABERTO'");

    $cn = new Charset_normalizer;

    header('Content-Type: text/html;');

    $diff = "";
    
    if ($chamado_existente->num_rows() > 0) {

        $this->load->library('simplediff');

        $sd = new SimpleDiff;

        $novo_texto = $desc;
        $antigo_texto = $chamado_existente->row()->descricao_chamado;
        $diff = $sd->htmlDiff($antigo_texto,$novo_texto);



        echo $cn->normalize(strip_tags($diff,$this->tags_permitidas));
    }
    else {

      $out = preg_replace('/\<\/html\>/i','<br /><br />', $desc);

      //echo $cn->normalize(strip_tags($out,$this->tags_permitidas));
      echo $cn->normalize($out);
    }
  }
}
