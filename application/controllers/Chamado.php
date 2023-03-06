<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chamado extends CI_Controller {
  
  

  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    //$this->load->library("mailer");
    $this->load->library("Charset_normalizer");

    
  
    
  }

  public function index($pagina = NULL, $id_chamado = NULL) { //controle dos chamados
    if (isset($_SESSION['id_usuario'])) {
    
      if ( ! file_exists(APPPATH.'views/paginas/chamado/'.$pagina.'.php') ) {
       show_404();
      }
      $dados = NULL;

      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

      $this->load->view('templates/cabecalho', $usuario);


      if ($pagina == 'ver_chamado') {

        $dados = $this->chamado_model->buscaChamado($id_chamado); //traz patrimonios, info do chamado e anexos...
        $dados['usuarios'] = $this->usuario_model->buscaUsuarios(); //traz a lista de todos os usuarios

        //var_dump($dados['chamado']->id_ticket_chamado);

        
        $dados['usuario'] = $usuario; //dados do usuário logado

        //print_r($dados);

        if (isset($dados['chamado'])) {

          //

          $dados['ticket']  = $this->consultas_model->buscaTicket($dados['chamado']->id_ticket_chamado,43); // fila SIGAT

          $this->load->view('paginas/chamado/'.$pagina, $dados);

         //

        } else {

          show_404();
        }
      }

      $this->load->view('templates/rodape');

    } else {
      header('Location: ' . base_url(),false,403);
    }
  }

  public function importar_chamado() {
  
      $dados = array();
  
      // campos
      $dados['id_ticket'] =          $this->input->post("id_ticket");
      $dados['nome_solicitante'] =    str_replace(array("'","\""),"",$this->input->post("nome_solicitante"));
      $dados['resumo_solicitacao'] =  str_replace(array("'","\""),"",$this->input->post("resumo_solicitacao"));
      $dados['telefone'] =            $this->input->post("telefone");
      $dados['nome_local'] =          $this->input->post("nome_local");
      $dados['comp_local'] =          str_replace(array("'","\""),"",$this->input->post("comp_local"));
      $dados['listaEquipamentos'] =   json_decode($this->input->post("listaEquipamentos"));
      $dados['anexos'] =              json_decode($this->input->post("g_anexos"));
      $dados['num_ticket'] =          "Ticket#" . $this->input->post("num_ticket");
      $dados['id_usuario'] =          $_SESSION["id_usuario"];

      
      
    $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;
  
    $novo_chamado = $this->chamado_model->importaChamado($dados);

    echo $novo_chamado["msg"];

    $user = $this->config->item('ticketsys_login');
    $pwd = $this->config->item('ticketsys_pwd');

    $api_url = $this->config->item('url_ticketsys_api');

    $url = $api_url."Ticket/" . $dados['id_ticket'] . "?UserLogin=".$user."&Password=".$pwd;

    $data_arr = array();

    $body = $nome_usuario . " criou o chamado #" .$novo_chamado["novo_id"]. " no SIGAT.\n\n" .
    
    "ID SIGAT: #".$novo_chamado["novo_id"]." | IMPORTACAO_SIGAT\n" .
    "Esta mensagem é automática, não responda.";

    $data_arr = array(
      "Ticket" => array(
        "QueueID" => 43, # mover para fila SIGAT
      ),        
      "Article" => array(
        "Subject" => "[SIGAT] Novo Chamado",
        "Body" => $body,
        "ContentType" => "text/plain; charset=utf8"
      )
    );

    $data = json_encode($data_arr);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($curl);
    curl_close($curl);

  }

  public function encerrar_chamado() { //encerrar chamado

    $dados = array();

    // campos

    $dados['id_chamado'] =        $this->input->post("id_chamado");
    $dados['id_usuario'] =        $_SESSION['id_usuario'];

    $this->chamado_model->encerraChamado($dados);

    $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;
    $result =  $this->chamado_model->buscaChamado($dados['id_chamado']);
    $id_ticket = $result['chamado']->id_ticket_chamado;


    $api_url = $this->config->item('url_ticketsys_api');

    $user = $this->config->item('ticketsys_login');
    $pwd = $this->config->item('ticketsys_pwd');

    $url = $api_url."Ticket/" . $id_ticket . "?UserLogin=".$user."&Password=".$pwd;

    $data_arr = array();

    $body = $nome_usuario . " encerrou o chamado #". $dados['id_chamado']. "\n\n" .
            "ID SIGAT: #".$id_ticket." | ENCERRAMENTO_SIGAT\n" .
            "Esta mensagem é automática, não responda.";

    $data_arr = array(
        "Ticket" => array(
        "StateID" => 2,    // novo estado: fechado com êxito   
      ),        
      "Article" => array(
        "Subject" => "[SIGAT] Encerramento",
        "Body" => $body,
        "ContentType" => "text/plain; charset=utf8",
        "IsVisibleForCustomer" => 1,
      )
    );

    $data = json_encode($data_arr);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($curl);
    curl_close($curl);

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

    $id_ticket =        $this->input->post("id_ticket");
    $desc_devo =        $this->input->post("desc_devo");

    $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;

    $user = $this->config->item('ticketsys_login');
    $pwd = $this->config->item('ticketsys_pwd');

    $api_url = $this->config->item('url_ticketsys_api');

    $url = $api_url."Ticket/" . $id_ticket . "?UserLogin=".$user."&Password=".$pwd;

    $data_arr = array();

    $body = $nome_usuario . " devolveu este ticket. Motivo:\n\n" . $desc_devo . "\n\n" .
            "ID TICKET: #".$id_ticket." | DEVOLUCAO_SIGAT\n" .
            "Esta mensagem é automática, não responda.";

    $data_arr = array(
      "Ticket" => array(
        "QueueID" => 5, # mover para fila Nivel0
      ),        
      "Article" => array(
        "Subject" => "[SIGAT] Devolução",
        "Body" => $body,
        "ContentType" => "text/plain; charset=utf8"
      )
    );

    $data = json_encode($data_arr);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_exec($curl);
    curl_close($curl);
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
		

    $dataInicio = $linha->data_ultima_interacao == NULL ? new DateTime($linha->data_chamado) : new DateTime($linha->data_ultima_interacao);

    // var_dump($dataInicio);

    $dataFim = new DateTime("now");

    $intervalo = date_diff($dataInicio,$dataFim);

    $tempo_espera_oculto = $intervalo->format("%Y-%M-%D-%H-%M"); // para ordernação do DataTables
    
    $tempo_espera_display = ""; // exibição amigável
    
    $tempo_espera = 0; // para calculo do tempo de espera

    $tempo_espera_fmt = $intervalo->format('%y::%m::%d::%h::%i');
    $array_tempo_espera = explode("::",$tempo_espera_fmt);


    // Exibição do tempo de espera

    if ($array_tempo_espera[0] >= 1) {
      $tempo_espera_display .= $array_tempo_espera[0]."a ";
    }
    if ($array_tempo_espera[1] >= 1) {
      $tempo_espera_display .= $array_tempo_espera[1]."m ";
    }
    if ($array_tempo_espera[2] >= 1) {
      if($array_tempo_espera[0] == 0)
        $tempo_espera_display .= $array_tempo_espera[2]."d ";
    }
    if ($array_tempo_espera[3] >= 1) {
      if($array_tempo_espera[1] == 0)
        $tempo_espera_display .= $array_tempo_espera[3]."h ";

    }
    if ($array_tempo_espera[4] >= 1) {
      if($array_tempo_espera[2] == 0)
        $tempo_espera_display .= $array_tempo_espera[4]."m";

    }

    // Calculo do tempo de espera em horas


    if ($array_tempo_espera[0] >= 1) {
      $tempo_espera += $array_tempo_espera[0] * 12 * 30 * 24; //ano
    }
    if ($array_tempo_espera[1] >= 1) {
      $tempo_espera += $array_tempo_espera[1] * 30 * 24; //mes

    }
    if ($array_tempo_espera[2] >= 1) {
      $tempo_espera += $array_tempo_espera[2] * 24;
    }

    $tempo_espera += $array_tempo_espera[3];

  
    $tempo_medio = $this->consultas_model->conf()->tempo_medio_atendimento;
    $tempo_max = $this->consultas_model->conf()->tempo_max_atendimento;

    $aviso_tempo = "";
    

    if($tempo_espera >= $tempo_medio && $tempo_espera < $tempo_max) {

      $aviso_tempo  = " <span class=\"text-warning\"><i class=\"fas fa-clock\"></i></span>";

    }

    if($tempo_espera > $tempo_max) {

      $aviso_tempo = " <span class=\"text-danger\"><i class=\"fas fa-clock\"></i></span>";

    }


		
		if ($linha->entrega_chamado == 1)
			$nome_local .= " <span class=\"badge badge-success\" title=\"Entrega\"><i class=\"fas fa-truck\"></i></span>"; //inserindo badge de entrega
	
    if ($this->consultas_model->temEquipEspera($linha->id_chamado) > 0)
      $nome_local .= " <span class=\"badge badge-warning\" title=\"Espera\"><i class=\"fas fa-hourglass-half\"></i></span>"; //inserindo badge de espera

    $percent_atend = round((100*$linha->atend_equips) / $linha->total_equips,0);
    $percent_abert = round((100*($linha->total_equips - $linha->atend_equips) / $linha->total_equips),0);

    $tam = strlen($linha->resumo_chamado);
    //var_dump($tam);
    $resumo = mb_strimwidth($linha->resumo_chamado,0,30,"...");
    //var_dump($resumo);
    //$resumo = $linha->resumo_chamado;

		$lista_painel['data'][] = array(
                              0 => $linha->id_chamado,
                              1 => $linha->prioridade_chamado,
                              2 => $linha->ticket_chamado,
                              3 => $linha->nome_solicitante_chamado . $aviso_tempo,
                              4 => "<span title=\"".
                                    $linha->resumo_chamado .
                                    "\">" .
                                    $resumo . "</span>",
                              5 => $nome_local,
                              6 => $linha->data_chamado,
                              7 => $tempo_espera_oculto,
                              8 => $tempo_espera_display,
                              9 => $linha->nome_responsavel,
                              10 => "<div class=\"progress\">
                                    <div class=\"progress-bar bg-info\" style=\"width: " . $percent_atend . 
                                    "%;height: 100%\">" .$percent_atend  . "%</div>
                                    <div class=\"progress-bar\" style=\"width: " 
                                    . $percent_abert . "%;height: 100%; background: #CDFFFF\"></div>
                                    </div>",
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
                              5 => $linha->data_encerramento_chamado,
                              // 6 => $linha->nome_responsavel,
                              // 7 => $linha->nome_fila,
                            );
    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  

  public function priorizar_chamado() {

    

    if (isset($_SESSION['id_usuario'])) {
      $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);
      $id_chamado = $this->input->post("id_chamado");

      if ($usuario->autorizacao_usuario > 3) {
        $this->chamado_model->priorizaChamado($id_chamado);
       
      }

      else {
        header("HTTP/1.1 406 Not Acceptable");
      }
    }
    else {
      header("HTTP/1.1 403 Forbidden");
    }
    
  }
}

?>