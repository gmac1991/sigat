<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Chamado extends CI_Controller {
  
  

  function __construct() {
    parent::__construct();

    $this->load->model("consultas_model"); //carregando o model das consultas 
    $this->load->model("chamado_model"); //carregando o model chamado
    $this->load->model("usuario_model"); //carregando o model usuario
    $this->load->model("interacao_model"); //carregando o model interacao
    $this->load->model("triagem_model"); //carregando o model triagem
    $this->load->model("reparo_model"); //carregando o model triagem
    $this->load->model("local_model"); //carregando o model local
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
        $dados['usuarios'] = $this->usuario_model->buscaUsuarios(); //traz a lista de todos os usuario

        
        $dados['usuario'] = $usuario; //dados do usuário logado

        if (isset($dados['chamado'])) {
          $dados['ticket']  = $this->triagem_model->buscaTicket($dados['chamado']->id_ticket_chamado,43); // fila SIGAT

          $valores['id_local'] = $dados['chamado']->id_local;

          $dados['telefones'] = $this->local_model->listarTelefones($valores);
          //$this->dd->dd($dados['ticket']);
          $this->load->view('paginas/chamado/'.$pagina, $dados);
          //$this->dd->dd($dados['chamado']);
         //

        } else {

          show_404();
        }
      }

      $this->load->view('templates/rodape');

    } else {
      header('Location: ' . base_url('/painel'));
    }
  }

  public function importar_chamado() {
  
    $dados = array();
  
    // campos
    $dados['id_ticket'] =           $this->input->post("id_ticket");
    $dados['nome_solicitante'] =    str_replace(array("'","\""),"",$this->input->post("nome_solicitante"));
    $dados['resumo_solicitacao'] =  str_replace(array("'","\""),"",$this->input->post("resumo_solicitacao"));
    $dados['telefone'] =            $this->input->post("telefone");
    $dados['celular'] =            $this->input->post("celular");
    $dados['nome_local'] =          $this->input->post("nome_local");
    $dados['comp_local'] =          str_replace(array("'","\""),"",$this->input->post("comp_local"));

    if (NULL !== $this->input->post("listaEquipamentos")) {
      $dados['listaEquipamentos'] = json_decode($this->input->post("listaEquipamentos"));
    } 

    if (NULL !== $this->input->post("listaServicos")) {
      $dados['listaServicos'] =  json_decode($this->input->post("listaServicos"));
    }

   
    $dados['anexos'] =              json_decode($this->input->post("g_anexos"));
    $dados['num_ticket'] =          "Ticket#" . $this->input->post("num_ticket");
    $dados['id_usuario'] =          $_SESSION["id_usuario"];
    $dados['id_fila'] =             $this->input->post("id_fila");

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
        "QueueID" => $this->config->item('id_fila_sigat_otobo'), # mover para fila SIGAT
      ),        
      "Article" => array(
        "Subject" => "[SIGAT] Novo Chamado",
        "Body" => $body,
        "ContentType" => "text/plain; charset=utf8",
      )
    );

    $data = json_encode($data_arr);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    // SOMENTE DEV
    if (ENVIRONMENT == 'development') {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    }
    

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
     // SOMENTE DEV
     if (ENVIRONMENT == 'development') {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    }
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
    $dados['celular'] =          $this->input->post("celular");
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
        "QueueID" => $this->config->item('id_fila_nivel0_otobo'), # mover para fila Nivel0
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
     // SOMENTE DEV
     if (ENVIRONMENT == 'development') {
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    }
    curl_exec($curl); 
    curl_close($curl);
  }

  public function finalizar_manual_chamado() {

    $id_chamado =  $this->input->post("id_chamado");
    
    $dados = array (
      'tipo' => 'FECHAMENTO_MAN',
      'id_chamado' => $id_chamado,
      'id_usuario' => $_SESSION['id_usuario'],

   ); 
   

    $this->chamado_model->finalizaManualChamado($id_chamado);

    $this->interacao_model->registraInteracao($dados);


  }

  public function listar_chamados_painel($id_fila = NULL) {

    $result_banco = $this->chamado_model->listaChamados($id_fila,$_SESSION['id_usuario']);
 
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
    // [01,00,00]
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

  
    $tempo_medio = $this->config->item('tempo_medio_atendimento');
    $tempo_max = $this->config->item('tempo_max_atendimento');

    $aviso_tempo = "";
    

    if($tempo_espera >= $tempo_medio && $tempo_espera < $tempo_max) {

      $aviso_tempo  = " <span class=\"text-warning\"><i class=\"fas fa-clock\"></i></span>";

    }

    if($tempo_espera > $tempo_max) {

      $aviso_tempo = " <span class=\"text-danger\"><i class=\"fas fa-clock\"></i></span>";

    }


		
		if ($linha->entrega_chamado == 1)
			$nome_local .= " <span class=\"badge badge-success\" title=\"Entrega\"><i class=\"fas fa-truck\"></i></span>"; //inserindo badge de entrega
	
    if ($this->chamado_model->temEquipEspera($linha->id_chamado) > 0)
      $nome_local .= " <span class=\"badge badge-warning\" title=\"Espera\"><i class=\"fas fa-hourglass-half\"></i></span>"; //inserindo badge de espera

    // $percent_atend = round((100*$linha->atend_equips) / $linha->total_equips,0);
    // $percent_abert = round((100*($linha->total_equips - $linha->atend_equips) / $linha->total_equips),0);

    $tam = strlen($linha->resumo_chamado);

    $status = $linha->prioridade_chamado;

    if ($status == 1 && $linha->status_chamado == 'FECHADO') {

      $status = 'FECHADO';
    }

    if ($status == 0) {

      $status = $linha->status_chamado;
    }

 
   
  
    $resumo = mb_strimwidth($linha->resumo_chamado,0,30,"...");


		$lista_painel['data'][] = array(
                              0 => $linha->id_chamado,
                              1 => $status,
                              2 => $linha->ticket_chamado,
                              3 => $linha->nome_solicitante_chamado . $aviso_tempo,
                              4 => "<span title=\"".
                                    $linha->resumo_chamado .
                                    "\">" .
                                    $resumo . "</span>",
                              5 => $nome_local,
                              6 => $linha->regiao_local,
                              7 => "<input class=\"chkExpo\" type=\"checkbox\" value=\"" . $linha->id_chamado . "\">",
                              8 => $linha->data_chamado,
                              9 => $tempo_espera_oculto,
                              10 => $tempo_espera_display,
                              11 => $linha->nome_responsavel,
                
                            ); //detalhes

    }

    header('Content-Type: application/json');

    echo json_encode($lista_painel);

  }
  
  public function listar_encerrados_painel() {

    $result_banco = $this->chamado_model->listaEncerrados();
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

  public function imprimir_chamados() {

    

    if (isset($_SESSION['id_usuario'])) {
     
      
      $entrada = $this->input->get("chamados");
      $chamados = explode(",", $entrada);

      //$this->load->library('pdf');
      $this->load->library('pdf_html');
                
        $pdf = new PDF_HTML();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->Cell(0,10,'CHAMADOS',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,"Emitido em " . date('d/m/Y - H:i:s'),0,0,'R');
        $pdf->Ln(15);

      foreach ($chamados as $id) {

      

        $dados = $this->chamado_model->buscaChamado($id,"'FECHADO','ENTREGUE','ATENDIDO','REMESSA','INSERVIVEL','FALHA','ESPERA'");

        $chamado = $dados['chamado'];
        $equips = $dados['equipamentos'];
        $servicos = $dados['servicos'];

      

        

        $ult_interacao = $this->interacao_model->buscaUltimaInteracao($id);

        if(isset($ult_interacao)) preg_match("/(.*)(?=<hr)/",$ult_interacao->texto_interacao, $texto); //fazendo o parse para pegar o texto antes do <hr>

        //var_dump($ult_interacao);
       
        
        $str_equips = NULL;

        foreach ($equips as $e) {

          $str_equips .= $e->num_equipamento . " - " . utf8_decode($e->descricao_equipamento) . "\n";
      
        }

        $str_servicos = NULL;

        foreach ($servicos as $s) {

          $str_servicos .= utf8_decode($s->nome_servico) . " (" . $s->quantidade . " " . $s->unidade_medida . ")\n";
      
        }
      

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,8,$chamado->ticket_chamado . " - Chamado #" . $id,'B',0,'R');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,"Data",1,0,"R");
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(80,8,$chamado->data_chamado,1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,"Fila",1,0,"R");
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,8,utf8_decode($chamado->nome_fila_chamado),1);
        $pdf->Ln();

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,"Solicitante",1,0,"R");
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(80,8,utf8_decode($chamado->nome_solicitante_chamado),1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,"Telefone",1,0,'R');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,8,$chamado->telefone_chamado,1);
        $pdf->Ln();

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,"Local",1,0,'R');
        $pdf->SetFont('Arial','',);
        $pdf->Cell(0,8,utf8_decode($chamado->nome_local),1);
        $pdf->Ln();

        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(22,8,utf8_decode("Endereço"),1,0,"R");
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(130,8,utf8_decode($chamado->endereco_local),1);
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(15,8,utf8_decode("Região"),1,0,'R');
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,8,$chamado->regiao_local,1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(28,8,"Complemento",1,0,"R");
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,8,utf8_decode($chamado->complemento_chamado),1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,8,"Resumo",1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(0,8,utf8_decode($chamado->resumo_chamado),1,'L');
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,8,"Equipamentos pendentes",1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(0,8,$str_equips,1,'L');
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(0,8,utf8_decode("Serviços pendentes"),1);
        $pdf->Ln();
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(0,8,$str_servicos,1,'L');
        $pdf->SetFont('Arial','B',11);
        $pdf->Cell(33,8,utf8_decode("Última interação"),0);
        $pdf->SetFont('Arial','',11);

        
        if ($ult_interacao != NULL) {
          $pdf->Cell(0,8,utf8_decode($ult_interacao->data_interacao_br . " - " . $ult_interacao->nome_usuario),0,0,'R');
          
        }
        else {
          $pdf->Cell(0,8,"N/A",1,'L');
          
        }
        $pdf->Ln();
        if ($ult_interacao != NULL && isset($texto[0])) {
          
          $pdf->WriteHTML(utf8_decode(html_entity_decode($texto[0])));
          
        }
        else {
          
          if (isset($ult_interacao) && $ult_interacao->tipo_interacao == 'ENC'){

            $pdf->MultiCell(0,8,utf8_decode("Chamado encerrado."),1,'L');

          }
          else{

            $pdf->MultiCell(0,8,utf8_decode("Sem interações."),1,'L');

          }
          
        }
        $pdf->Ln();
        $pdf->Cell(0,8,"","T",0,0);
        $pdf->Ln(7);
       
      }

       

        $pdf->SetTitle('LC_' . date('d-m-Y'));

        $pdf->Output('I','impressao_chamado.pdf',FALSE);

      
    }
    else {
      header("HTTP/1.1 403 Forbidden");
    }
    
  }

  public function listar_servicos_chamado($id_chamado){
    if (isset($_SESSION['id_usuario'])) {
            
            
            $item = $this->input->post('item');
            if(!empty($item) && $item != null){
              $item['id_chamado'] = $id_chamado;
              $this->chamado_model->inserir_servicos($item);
            }
            
            $linhas = $this->chamado_model->listar_servicos_chamado($id_chamado);

            header("Content-Type: application/json");

            echo json_encode($linhas);
            
            

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
  }

  public function listar_servicos($id_fila){
    if (isset($_SESSION['id_usuario'])) {
            
            
            $servicos = $this->chamado_model->listar_servicos($id_fila);

            header("Content-Type: application/json");

            echo json_encode($servicos);

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
  }

  public function excluir_servico($id){

    if (isset($_SESSION['id_usuario'])) {
      $item = $this->input->post('item');
      if(!empty($item) && $item != null){
        $item['id_chamado'] = $this->input->post('g_id_chamado');
        $this->chamado_model->excluir_servico($item);
      }

      header("Content-Type: application/json");

      echo json_encode($item);
    
    } else {
        header('HTTP/1.0 403 Forbidden');
    }

  }

  public function atualizar_servicos_chamado($id_chamado){
    if (isset($_SESSION['id_usuario'])) {
            
            
            $item = $this->input->post('item');
            //$this->dd->dd($item);
            if(!empty($item) && $item != null){
              $item['id_chamado'] = $id_chamado;
              $this->chamado_model->atualizar_servicos($item);
            }
            
            
            header("Content-Type: application/json");

            echo json_encode($item);
            
            

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
  }

  public function zerar_nao_lidos($chamado){
    if (isset($_SESSION['id_usuario'])) {
      
      $this->chamado_model->zerar_chamados($chamado);

      header("Content-Type: application/json");

      echo json_encode('funciona');
    }
  }
}

?>