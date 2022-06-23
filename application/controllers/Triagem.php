<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Triagem extends CI_Controller {



  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    $this->load->library("Charset_normalizer");

    
  }

  public function index($id_ticket = NULL) { //exibe triagem
    if (isset($_SESSION['id_usuario'])) {

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      if ($usuario->triagem_usuario == 0) {

        header('Location: ' . base_url());

      }

      else {

        $triagem = NULL;
		
		    $triagem  = $this->consultas_model->buscaTicket($id_ticket,37); //traz chamado migrado, fila Suporte Atendimento
		
		    if (isset($triagem )) { // se o chamado existir

          $this->load->view('templates/cabecalho', $usuario);

          $this->load->view('paginas/triagem', $triagem);

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

    $result_banco = $this->consultas_model->listaTriagem();
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {
    
  

    // $lista_painel['data'][] = array(
    //                           0 => $linha->id_triagem,
    //                           1 => $linha->ticket_triagem,
    //                           2 => $linha->data_triagem,
    //                           3 => $linha->nome_solicitante_triagem,
    //                           4 => $linha->email_triagem,
    //                           // 5 => "<a href=\"" . base_url('triagem/' . $linha->id_triagem) . 
    //                           // "\" rel=\"noopener\" role=\"button\"" .
    //                           // " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i></a> "
    //                         ); //detalhes


    $lista_painel['data'][] = array(
      0 => $linha->id,
      1 => "Ticket#" . $linha->tn,
      2 => $linha->create_time,
      3 => $linha->title,
      4 => $linha->a_from,
      //4 => $linha->email_triagem,
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



      $out = preg_replace('/\<\/span\>/','<br /><br /></span>', $desc);
      $out = preg_replace('/\<\/html\>/','<br /><br /></html>', $out);

      echo $cn->normalize(strip_tags($out,$this->tags_permitidas));
      //echo $cn->normalize($out);
    }
  }
}
