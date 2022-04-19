<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chamado extends CI_Controller {

  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    $this->load->library("mailer");

    
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
      $dados['id_triagem'] =          $this->input->post("id_triagem");
      $dados['nome_solicitante'] =    str_replace(array("'","\""),"",$this->input->post("nome_solicitante"));
      $dados['resumo_solicitacao'] =  str_replace(array("'","\""),"",$this->input->post("resumo_solicitacao"));
      $dados['telefone'] =            $this->input->post("telefone");
      $dados['nome_local'] =          $this->input->post("nome_local");
      $dados['comp_local'] =          str_replace(array("'","\""),"",$this->input->post("comp_local"));
      $dados['listaEquipamentos'] =   json_decode($this->input->post("listaEquipamentos"));
      $dados['anexos'] =              json_decode($this->input->post("g_anexos"));
      $dados['textoTriagem'] =        $this->input->post("textoTriagem");
      $dados['ticket_triagem'] =      $this->input->post("ticket_triagem");
      $dados['email_triagem'] =       $this->input->post("email_triagem");
      $dados['id_usuario'] =          $_SESSION["id_usuario"];

      
      
    $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;
  
    $novo_chamado = $this->chamado_model->importaChamado($dados);

    echo $novo_chamado["msg"];

   

      $mail = new Mailer(true);
  
      try {
  
         
          // //Attachments
          // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
          // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name
  
          //Content
          $mail->isHTML(true);                                  //Set email format to HTML
          $mail->Subject = 'SIGAT - NOVO CHAMADO - ' . $dados['ticket_triagem'];
          $mail->Body = '<span style="font-family:Arial,Helvetica,sans-serif">
          <h2>SIGAT</h2>
          <h3><em>'.$dados['ticket_triagem'].'</em></h3>
          <p><strong>'.$nome_usuario.'</strong> criou o chamado #'.$novo_chamado["novo_id"].' no SIGAT.<br />
          <a href="'. base_url("chamado/" . $novo_chamado["novo_id"]) . '">Clique para acessar</a></p>
          ---<br>
          <span style="font-size: 11px">ID SIGAT: #'.$novo_chamado["novo_id"].' | IMPORTACAO_SIGAT<br />
          Esta mensagem &eacute; autom&aacute;tica, n&atilde;o responda.</span></span>';
  
          $mail->send();
  
      } catch (Exception $e) {
          echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
    
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

  public function devolver_chamado() { 

    //$dados = array();

    // campos

    $id_triagem =        $this->input->post("id_triagem");
    $desc_devo =        $this->input->post("desc_devo");
   // $dados['id_usuario'] =        $_SESSION['id_usuario'];

   

    $ticket = $this->chamado_model->buscaTicketTriagem($id_triagem);

    $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;

    $this->load->library("mailer");

    $mail = new Mailer(true);

    try {

        // //Attachments
        // $mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = utf8_decode('SIGAT - DEVOLUÇÃO ') . $ticket;
        $mail->Body    = 
        '<span style="font-family:Arial,Helvetica,sans-serif">
        <h2>SIGAT</h2>
        <h3><em>'.$ticket.'</em></h3>
        <p>Este ticket foi devolvido pelo SIGAT por <strong>'.$nome_usuario.'</strong>.<br />
        <strong>Motivo:</strong> '.$desc_devo.'</p>
        ---<br>
        <span style="font-size:11px">ID SIGAT: #'.$id_triagem.' | DEVOLUCAO_SIGAT<br />
        Esta mensagem &eacute; autom&aacute;tica, n&atilde;o responda.</span></span>';

        $mail->send();
        //header("Location: " . base_url('painel?v=triagem'));

        $this->chamado_model->devolveChamado($id_triagem);

    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }

  }

  public function listar_chamados_painel($id_fila = NULL) {

    $result_banco = $this->consultas_model->listaChamados($id_fila,$_SESSION['id_usuario']);
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {
		
	
		$nome_local = $linha->nome_local;
		
		$nome_local .=  " <span class=\"badge badge-secondary\">";
		
		switch ($linha->id_fila_chamado) {
			case 1:
        $fila = $this->consultas_model->listaFila(1);
				$nome_local .= "<i class=\"" . $fila->icone_fila . " title=\"" . $fila->nome_fila ."></i>";
				break;
			case 2:
				$fila = $this->consultas_model->listaFila(2);
				$nome_local .= "<i class=\"" . $fila->icone_fila . " title=\"" . $fila->nome_fila ."></i>";
				break;
			case 3:
				$fila = $this->consultas_model->listaFila(3);
				$nome_local .= "<i class=\"" . $fila->icone_fila . " title=\"" . $fila->nome_fila ."></i>";
				break;
		}
		
		$nome_local .=  "</span>";
		
		
		
		if ($linha->entrega_chamado == 1)
			$nome_local .= " <span class=\"badge badge-success\" title=\"Entrega\"><i class=\"fas fa-truck\"></i></span>"; //inserindo badge de entrega
	

		$lista_painel['data'][] = array(
                              0 => $linha->id_chamado,
                              1 => $linha->ticket_chamado,
                              2 => $linha->nome_solicitante_chamado,
                              3 => $nome_local,
                              4 => $linha->data_chamado,
                              5 => $linha->nome_responsavel,
                              6 => $linha->status_chamado,
                              7 => "<button class=\"btn btn-secondary btn-sm btn-block PopoverPainel\"" .
                              " data-chamado=\"" . $linha->id_chamado . "\">" .
                              "<i class=\"far fa-clock\"></i></button>", // ultima interacao
                              // 7 => "<a href=\"" . base_url('chamado/' . $linha->id_chamado) . 
                              // "\" role=\"button\"" .
                              // " class=\"d-block btn btn-sm btn-info\"><i class=\"fas fa-search\"></i></a> "
                            ); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
  

  public function listar_encerrados_painel() {

    $result_banco = $this->consultas_model->listaEncerrados();
    $lista_painel['data'] = array();

    foreach ($result_banco as $linha) {

      $lista_painel['data'][] = array(0 => $linha->id_chamado,
                              1 => $linha->ticket_chamado,
                              2 => $linha->nome_solicitante_chamado,
                              3 => $linha->nome_local,
                              4 => $linha->data_chamado,
                              5 => $linha->data_alt_chamado,
                              6 => $linha->nome_responsavel,
                              7 => $linha->nome_fila,
                            );
    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
 
}

?>