<?php

date_default_timezone_set('America/Sao_Paulo');


defined('BASEPATH') OR exit('No direct script access allowed');

class Interacao extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('form_validation');
        $this->load->model("chamado_model"); //carregando o model chamado
        $this->load->model("interacao_model"); //carregando o model interacoes
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model("equipamento_model"); //carregando o model usuario
        $this->load->model("inservivel_model"); //carregando o model usuario
        $this->load->model("reparo_model"); //carregando o model usuario
        $this->load->model("bancada_model"); //carregando o model usuario


        
    }

    public function remover_interacao() {

        $dados = $this->input->post();

        $chamado = $this->chamado_model->buscaChamado($dados['id_chamado'])['chamado'];
        $interacao = $this->interacao_model->buscaInteracao($dados['id_interacao']);

        //var_dump($chamado);

        if(($chamado->id_responsavel == $dados['id_usuario'] && $interacao->id_usuario_interacao == $dados['id_usuario']) 
            || $dados['auto_usuario'] >= 4) {

            $this->interacao_model->removeInteracao($dados['id_interacao']);

        }
        else {
            header('HTTP/1.0 403 Forbidden');
        }
        
    }
    public function registrar_interacao_reparo() {
        if (isset($_SESSION['id_usuario'])){
            $dados = array();

            $dados['texto'] = $this->input->post('txtInteracaoReparo');
            $dados['id_chamado'] = $this->input->post('id_chamado');
            $dados['id_reparo'] = $this->input->post('id_reparo');
            $dados['tipo'] = $this->input->post('tipo');
            $dados['id_fila'] = 3;
            $dados['id_fila_ant'] = NULL;
            $dados['equip_atendidos'] = array(0 => $this->input->post('num_equipamento'));
            $dados['id_usuario'] = $this->input->post('id_usuario');

            $fase1 = FALSE;
            $fase2 = FALSE;

            switch ($dados['tipo']) {
                case 'INSERVIVEL_REPARO':

                    $reparo = $this->reparo_model->buscarReparo($dados['id_reparo']);

                    try {
                        $libera_bancada = $this->bancada_model->atualizarBancada($reparo->id_bancada_reparo,0);

                        $fase1 = true;
                    } catch (\Throwable $th) {
                        $fase1 = false;
                    }

                   
                    // caso for lousa ignorar esse trecho
                    $nao_incluir_remessa = function($id_reparo, &$dados) {
                        $result = $this->reparo_model->buscarReparoServicos($id_reparo);
                    
                        foreach ($result as $servico) {
                            if ($servico->id_servico == 42 && $servico->realizado_reparo_servico == true) {
                                $dados['tipo'] = 'INSERVIVEL';
                                return true;
                            }
                        }
                        return false;
                    };
                    


                    $equip_inserv = $dados['equip_atendidos'][0];
                    if ($libera_bancada && $nao_incluir_remessa($reparo->id_reparo, $dados) == false) {
                        $reparoDivisao = DGTI;
                        if ($reparo->id_bancada_reparo == $this->config->item('bancada_din')) {
                            $reparoDivisao = DIN;
                        }

                        $remessa_aberta = $this->inservivel_model->lista_remessa_aberta($reparoDivisao);
                        //inserindo na ultima remessa 
                        if ($remessa_aberta->pool_equipamentos == null) {
                            $equipamentos = implode("::", $dados['equip_atendidos']);
                            $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $equipamentos);
                        } else {
                            $equips = explode("::", $remessa_aberta->pool_equipamentos);
                            array_push($equips, $equip_inserv);
                            $equipamentos = implode("::", $equips);
                            $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $equipamentos);
                        }
                    }

                    try {
                        if (isset($remessa_aberta->id_remessa_inservivel)) {
                            $finaliza_reparo = $this->reparo_model->atualizarReparo($dados['id_reparo'],"FINALIZADO", $_SESSION['id_usuario'], $remessa_aberta->id_remessa_inservivel);
                        } else {
                            $finaliza_reparo = $this->reparo_model->atualizarReparo($dados['id_reparo'],"FINALIZADO", $_SESSION['id_usuario']);
                        }

                        $fase1 = true;
                    } catch (\Throwable $th) {
                        return $fase1 = false;
                    }

                    break;
                case 'SERVICO_REPARO':
                    $reparo = $this->reparo_model->buscarReparo($dados['id_reparo']);
                    $libera_bancada = $this->bancada_model->atualizarBancada($reparo->id_bancada_reparo, 0);
                    $finaliza_reparo = $this->reparo_model->atualizarReparo($dados['id_reparo'],"FINALIZADO");

                    break;
            }

            if ($fase1) {

                $fase2 = $this->interacao_model->registraInteracao($dados);

                $fase2 = $fase2 == NULL ? TRUE : FALSE;

            }
            

            $result = array();
            $result['operacao'] = $fase1 && $fase2;

            if ($fase1 && $fase2) {
                $res['status'] = 200;
                $res['mensagem'] = "Operação realizada com sucesso.";

                if ($dados['tipo'] == 'INSERVIVEL_REPARO' || $dados['tipo'] == 'INSERVIVEL') {
                    /* $dados = $this->chamado_model->buscaChamado($dados['id_chamado'],"'ENTREGA'"); */
                    $laudo = $this->inservivel_model->lista_laudo_equipamento($equip_inserv);
                    $nome_usuario_atual = $this->usuario_model->buscaUsuario($_SESSION['id_usuario'])->nome_usuario;
                    $nome_arquivo = "Laudo_Equipamento_{$equip_inserv}.pdf";
                    $patch_arquivo = "{$this->config->item('caminho_termos')}Laudo_Equipamento_{$equip_inserv}.pdf";

                    $this->load->library('pdf');
                    $pdf = new PDF();
                    $pdf->AliasNbPages();
                    $pdf->AddPage();
                    $pdf->SetFont('Arial','B',18);
                    $pdf->Cell(0,10,'Laudo '.utf8_decode('Técnico'),0,0,'C');
                    $pdf->Ln(10);
                    $pdf->SetFont('Arial','',11);
                    $pdf->Cell(0,10,"Documento gerado em " . date('d/m/Y - H:i:s'),0,0,'R');
        
                    $pdf->Ln(15);
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(55,10,$laudo->ticket_chamado,1,0,'L');
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(38,10,'Data de abertura',1,0,'R');
                    $pdf->SetFont('Arial','',12);
                    $pdf->Cell(40,10,date('d/m/Y H:i:s', strtotime($laudo->data_chamado)),1,0,'C');
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(15,10,"SIGAT",1,0,'L');
                    $pdf->SetFont('Arial','',12);
                    $pdf->Cell(0,10,"#".$laudo->id_chamado,1,0,'L');
                    
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(25,10,'Solicitante',1);
                    $pdf->SetFont('Arial','',12);
                    $pdf->Cell(0,10,utf8_decode($laudo->nome_solicitante_chamado),1);
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(25,10,'Local ',1);
                    $pdf->SetFont('Arial','',11);
                    $pdf->Cell(0,10,utf8_decode($laudo->nome_local),1);
                    $pdf->Ln();
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',13);
                    $pdf->Cell(0,10,"Equipamento",0,0,'C');
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',12);
                    $pdf->Cell(48,10,'Num.',1,0,'C');
                    $pdf->Cell(0,10,utf8_decode('Descrição'),1,0,'C');
                    $pdf->SetFont('Arial','',12);
                    $pdf->Ln();
                    $pdf->Cell(48,10,$equip_inserv,1,0,'');
                    $pdf->Cell(0,10,utf8_decode($laudo->descricao_equipamento),1,0,'');
                    $pdf->Ln();
                    $pdf->SetFont('Arial','B',14);
                    $pdf->Cell(0,10,utf8_decode('LAUDO'),1,0,'C');
                    $pdf->Ln();
                    $pdf->SetFont('Arial','',12);
                    $pdf->Multicell(0,8,utf8_decode($laudo->texto_interacao), 1, 1);
                    $pdf->SetFont('Arial','I',11);
                    $pdf->Cell(0,10,utf8_decode("Elaborado por " . $nome_usuario_atual . " em ") . 
                    date('d/m/Y - H:i:s', strtotime($laudo->data_fim_reparo)),0,0,'R');

                    $pdf->Output('F', $patch_arquivo, TRUE);

                    if (file_exists($patch_arquivo)) {
                        $id_ticket_chamado = $laudo->ticket_chamado;
                        $id_ticket_chamado = explode('#', $id_ticket_chamado);
                        $id_ticket_chamado = end($id_ticket_chamado);

                        // configuração API Otobo
                        $api_url = $this->config->item('url_ticketsys_api');
                        $user = $this->config->item('ticketsys_login');
                        $pwd = $this->config->item('ticketsys_pwd');
                        $url_otobo = $api_url."Ticket/" . $laudo->id_ticket_chamado . "?UserLogin=".$user."&Password=".$pwd;
                        $responsavel_otobo = strtolower($this->interacao_model->buscaSolicitanteOtobo($laudo->id_ticket_chamado));
                        $url_api_ldap = "{$this->config->item('api_ldap')}ldap/{$responsavel_otobo}";

                        $data_arr = array();
                        $fp = fopen($patch_arquivo, "rb");
                        $binario = fread($fp, filesize($patch_arquivo));
                        fclose($fp);

                        $data_arr = array("Attachment" => array(
                            "Content" => base64_encode($binario),
                            "ContentType" => "application/pdf",
                            "Filename" => $nome_arquivo
                        ));

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url_api_ldap);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        // SOMENTE DEV
                        if (ENVIRONMENT == 'development') {
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        }
                        $res_ldap = json_decode(curl_exec($curl));
                        curl_close($curl);

                        if ($res_ldap != null) {
                            $responsavel_otobo = $res_ldap->commonName;
                        } else {
                            $responsavel_otobo = $laudo->nome_solicitante_chamado;
                        }
                        $body = "Caro(a) {$responsavel_otobo},<br><br>
                        O equipamento {$equip_inserv} vinculado a este ticket, solicitado por {$laudo->nome_solicitante_chamado}, foi laudado tecnicamente como inservível.<br>
                        Encaminhamos anexo o laudo técnico do referido equipamento.
                        <br><br>Por favor, ao entrar em contato conosco, tenha em mãos o número do ticket criado ou <u><strong>responda sempre neste e-mail</strong></u>.<br><br>--<br>Atenciosamente,<br><strong>Central de Serviços de Tecnologia da Informação</strong><br>Prefeitura Municipal de Sorocaba<br>Av. Eng. Carlos Reinaldo Mendes - Paço Municipal<br>email: <u><strong>informatica@sorocaba.sp.gov.br</strong></u></span>";
                        $data_arr += array(
                            "Article" => array(
                                "Subject" => "Re: Ticket#$id_ticket_chamado - Laudo técnico - $equip_inserv",// titulo
                                "From" => "$nome_usuario_atual via SIGAT<informatica@sorocaba.sp.gov.br>",
                                "Body" => $body,
                                "ContentType" => "text/html; charset=utf8",
                                "IsVisibleForCustomer" => 1,
                                "CommunicationChannel" => 'Email',
                                "TicketNumber" => $laudo->ticket_chamado
                            )
                        );

                        $json_data = json_encode($data_arr);
                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url_otobo);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        // SOMENTE DEV
                        if (ENVIRONMENT == 'development') {
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        }

                        $res_otobo = json_decode(curl_exec($curl));
                        curl_close($curl);
        
                        if (isset($res->Error)) {
                            $res = array(
                                "status" => 500,
                                "error" => true,
                                "mensagem" => "Otobo não respondeu"
                            );
                        } else {
                            $this->enviar_email_smtp(array(
                                array(
                                    "name_file" =>$nome_arquivo,
                                    "file" => $patch_arquivo
                                )
                            ), $id_ticket_chamado, $res_otobo, $body);


                            $arquivo = $this->interacao_model->buscarAnexoOtrs($res_otobo, "application/pdf")[0];
        
                            try {
                                $this->interacao_model->insereAnexoSigat($dados['id_chamado'], $arquivo->id_anexo_otrs, $arquivo->nome_arquivo_otrs);
        
                                if (file_exists($patch_arquivo)) {
                                    unlink($patch_arquivo);
                                    /* $this->interacao_model->logEnviarEmail($laudo->id_chamado); */
                                }
        
                                $res = array(
                                    "status" => 200,
                                    "error" => false,
                                    "mensagem" => null
                                );
                            } catch (\Throwable $th) {
                                $res = array(
                                    "status" => 500,
                                    "error" => true,
                                    "mensagem" => "Ocorreu um erro ao tentar enviar o laudo do equipamento."
                                );
                            }
                        }
                    }
                }
            } else {
                $res['status'] = 500;
                $res['mensagem'] = "Operação retornou erro.";
            }


            /* $this->dd->dd($res); */

            http_response_code($res['status']);
            header('Content-Type: application/json');
            echo json_encode($res);

            /* $this->interacao_model->registraInteracao($dados); */

            // if inservivel
            // dar load no model_inservivel
        }
    }
      
    public function registrar_interacao() {
    
        $dados = array();

        $dados['texto'] =                   $this->input->post('txtInteracao');
        $dados['id_chamado'] =              $this->input->post('id_chamado');
        $dados['tipo'] =                    $this->input->post('tipo');
        $dados['situacao'] =                $this->input->post('situacao');
        $dados['id_fila'] =                 $this->input->post('id_fila');
        $dados['id_fila_ant'] =             $this->input->post('id_fila_ant');
        $dados['equip_atendidos'] =         $this->input->post('equipamentos_atendidos');
        $dados['servicos_atendidos'] =      $this->input->post('servicos_atendidos');
        $dados['id_servicos_atendidos'] =   $this->input->post('id_servicos_atendidos');
        $dados['id_usuario'] =              $this->input->post('id_usuario');
        

        if (null !== $this->input->post('servicos_atendidos'))
        {
            $dados['nome_servico'] = [];
            foreach ($dados['servicos_atendidos'] as $servico) {
                array_push($dados['nome_servico'], $this->chamado_model->listar_servico($servico)[0]['nome_servico']);
            }

        }
        
        
        $this->interacao_model->registraInteracao($dados);
    
    }
  
    public function registrar_entrega() {
        
        $dados = array();

        $dados['id_usuario'] = $_SESSION['id_usuario'];
  
        $dados['nome_recebedor'] = $this->input->post('nome_recebedor');
       
        $dados['id_chamado'] = $this->input->post('id_chamado');
        $opcao_entrega = $this->input->post('opcao_entrega');
        $erro_entrega = $this->input->post('erro_entrega');
        $termo_resp = $this->input->post('termo_resp');


        $config = array();

        $config['upload_path']          = $this->config->item('caminho_termos');
        $config['overwrite']            = TRUE;
        $config['allowed_types']        = 'pdf'; //tipos de arquivos permitidos
        $config['max_size']             = 5000; //tamanho maximo: 5 Megabytes

        if ($opcao_entrega == 1) { //entrega OK

            $dados['tipo'] = 'ENTREGA';
  
            // ----- UPLOAD TERMO ENTREGA ----  
            
            $config['file_name'] = 'Entrega_' . date('d-m-Y') . '_' . $dados['id_chamado'] . '.pdf';
            
            $this->load->library('upload', $config);

            if (! $this->upload->do_upload('termo_entrega')) {

                echo "Erros no Termo de Entrega<br>";

                $dados['erros_upload'] = array('error' => $this->upload->display_errors());

                foreach ($dados['erros_upload'] as $erro) {

                    echo $erro;

                }

            } else {

                $dados['nome_termo_entrega'] =  $config['file_name'];
            }

            unset($this->upload);

            // ----- UPLOAD TERMO RESP (se houver) ----
            
            if ($termo_resp == 1) {

                $config['file_name'] = 'Resp_' . date('d-m-Y') . '_' . $dados['id_chamado'] . '.pdf';

                $this->load->library('upload', $config);

                if (! $this->upload->do_upload('termo_responsabilidade')) {

                    echo "Erros no Termo de Resp.<br>";

                    $dados['erros_upload'] = array('error' => $this->upload->display_errors());

                    foreach ($dados['erros_upload'] as $erro) {

                        echo $erro;

                    }   
                }

                else {

                    $dados['nome_termo_responsabilidade'] =  $config['file_name'];
                }
            }
        }

        else { //problema na entrega

            if ($erro_entrega == 1) { //defeito na entrega


                $dados['tipo'] = 'FALHA_ENTREGA';
                $dados['txtFalhaEntrega'] = $this->input->post('txtFalhaEntrega');
                //var_dump($this->input->post('txtFalhaEntrega'));

            }

            else {
                $dados['txtFalhaEntrega'] = $this->input->post('txtFalhaEntrega');
                $dados['tipo'] = 'TENTATIVA_ENTREGA';


            }


        }
        
        $this->interacao_model->registraInteracao($dados);
         
    }
           
    public function gerar_termo($id_chamado) {

        $dados = $this->chamado_model->buscaChamado($id_chamado,"'ENTREGA'");
        $nome_usuario_atual = $this->usuario_model->buscaUsuario($_SESSION['id_usuario'])->nome_usuario;

        $this->load->library('pdf');
                
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,10,'Termo de Entrega',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,"Emitido em " . date('d/m/Y - H:i:s'),0,0,'R');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(60,10,$dados['chamado']->ticket_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(24,10,"Chamado",1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(45,10,"#".$dados['chamado']->id_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(12,10,'Data ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->data_chamado,1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(14,10,'Local ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,utf8_decode($dados['chamado']->nome_local),1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(25,10,'Solicitante ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,utf8_decode($dados['chamado']->nome_solicitante_chamado),1);
        $pdf->Ln();
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Equipamentos',0,0,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();

        if (!empty($dados['equipamentos'])) {

            $pdf->Cell(36,10,'ID',1,0,'C');
            $pdf->Cell(0,10,utf8_decode('Descrição'),1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();
            
            foreach($dados['equipamentos'] as $equip) {

                $pdf->Cell(36,10,$equip->num_equipamento,1,0,'');
                $pdf->Cell(0,10,$equip->descricao_equipamento,1,0,'');
                $pdf->Ln();

            }

        }


        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Recebido por: ');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(120,8,'','B',1);
        $pdf->Ln(0);
        $pdf->SetFont('Arial','I',10);
        $pdf->Ln(0);
        $pdf->Cell(140,8,'(nome por extenso ou assinatura e carimbo)',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Entregue por ' . utf8_decode($nome_usuario_atual));
       
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Data: ',0,0,'R');
        $pdf->Cell(30,10,date('d/m/Y'));

        $pdf->SetTitle('TE_' . date('d-m-Y') . "_" . $id_chamado);

        $pdf->Output('I','termo_entrega_' . $id_chamado . '.pdf', TRUE);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'GERAR_TERMO_ENTREGA',
            'desc_evento' => 'ID CHAMADO: ' . $id_chamado . ' - NOME: termo_entrega_' . $id_chamado . '.pdf',
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

    
    
    }

    public function gerar_termo_resp($id_chamado) {

        $dados = $this->chamado_model->buscaChamado($id_chamado,"'ENTREGA'");

        $this->load->library('pdf');
                
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,10,'Termo de Responsabilidade',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,"Emitido em " . date('d/m/Y - H:i:s'),0,0,'R');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(60,10,$dados['chamado']->ticket_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(24,10,"Chamado",1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(45,10,"#".$dados['chamado']->id_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(12,10,'Data ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->data_chamado,1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(14,10,'Local ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,utf8_decode($dados['chamado']->nome_local),1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(25,10,'Solicitante ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,utf8_decode($dados['chamado']->nome_solicitante_chamado),1);
        $pdf->Ln();
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Equipamentos',0,0,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();

        
        if (!empty($dados['equipamentos'])) {

            $pdf->Cell(36,10,'ID',1,0,'C');
            $pdf->Cell(0,10,utf8_decode('Descrição'),1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();

            foreach($dados['equipamentos'] as $equip) {

                $pdf->Cell(36,10,$equip->num_equipamento,1,0,'');
                $pdf->Cell(0,10,$equip->descricao_equipamento,1,0,'');
                $pdf->Ln();
            }

        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(190,8,utf8_decode('Declaro para os devidos fins, que na presente data recebi da Prefeitura Municipal de Sorocaba, o (s) material (ais) abaixo relacionado (s), para uso exclusivo da municipalidade, pelo (s) qual (ais) assumo inteira responsabilidade pelo seu bom uso e conservação, ficando ainda ciente que deverei comunicar por escrito à Seção de Administração e Controle de Materiais Permanentes, qualquer alteração do (s) mesmo (s). Sendo caracterizado o mau uso ou não localizado, será comunicado à Secretaria Jurídica para as demais providências, em acordo com o Decreto n. º 23117/2017.'));

        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,utf8_decode('Responsável: '));
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(120,8,'','B',1);
        $pdf->Ln(0);
        $pdf->SetFont('Arial','I',10);
        
        $pdf->Ln(0);
        $pdf->Cell(140,8,'(nome por extenso ou assinatura e carimbo)',0,0,'C');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Data: ',0,0,'R');
        $pdf->Cell(30,10,date('d/m/Y'));

        $pdf->SetTitle('TR_' . date('d-m-Y') . "_" . $id_chamado);

        $pdf->Output('I','termo_resp_' . $id_chamado . '.pdf', TRUE);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'GERAR_TERMO_RESP',
            'desc_evento' => 'ID CHAMADO: ' . $id_chamado . ' - NOME: termo_resp_' . $id_chamado . '.pdf',
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

    
    
    }
  
    public function gerar_laudo($id_interacao) {       
        $interacao = $this->interacao_model->buscaInteracaoChamado($id_interacao);

        if ($interacao !== NULL) { 

            //$dados = $this->chamado_model->buscaChamado($interacao->id_chamado_interacao,'INSERVIVEL');
    
            $this->load->library('pdf_html');
                
            $pdf = new PDF_HTML();
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',18);
            $pdf->Cell(0,10,utf8_decode('Laudo Técnico'),0,0,'C');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(0,10,'Emitido em: ' . date('d/m/Y - H:i:s') . ' | #' . $id_interacao,0,0,'R');
            $pdf->Ln(15);
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(60,10, $interacao->ticket_chamado,1);
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(24,10,"Chamado",1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(45,10,"#".$interacao->id_chamado_interacao,1);
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(12,10,'Data ',1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(0,10, $interacao->data_chamado,1);
            $pdf->Ln();
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(14,10,'Local ',1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(0,10,utf8_decode($interacao->nome_local),1);
            $pdf->Ln();
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(25,10,'Solicitante ',1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(0,10,utf8_decode($interacao->nome_solicitante_chamado),1);
            $pdf->Ln();
            $pdf->Ln(10);
            $pdf->SetFont('Arial','B',14);
            $pdf->Cell(0,10,'Equipamentos',0,0,'C');
            $pdf->SetFont('Arial','B',12);
            $pdf->Ln();
    
            $pdf->Cell(36,10,'ID',1,0,'C');
            $pdf->Cell(0,10,utf8_decode('Descrição'),1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();
            
            $equips = explode("::",$interacao->pool_equipamentos);
            foreach($equips as $num_equip) {

                $desc = $this->equipamento_model->buscaDescEquipamento($num_equip);
                $pdf->Cell(36,10,$num_equip,1,0,'');
                $pdf->Cell(0,10,$desc,1,0,'');
                $pdf->Ln();
    
    
            }
    
            preg_match("/(.*)(?=<hr)/",$interacao->texto_interacao, $texto); //fazendo o parse para pegar o texto antes do <hr>
    
            $texto = strip_tags($texto[0]); //removendo as tags html
    
            $pdf->Ln(10);
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(0,10,'Laudo','B',0,'C');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',12);
            $pdf->Ln(5);
            $pdf->WriteHTML(utf8_decode(html_entity_decode(($texto))));
            $pdf->Ln(10);
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(120,10,'Data: ',0,0,'R');
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(0,10,$interacao->data_interacao);
            $pdf->Ln(10);
            $pdf->SetFont('Arial','B',12);
            $pdf->Cell(120,10,utf8_decode('Técnico: '),0,0,'R');
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(0,10,utf8_decode($interacao->nome_usuario));
            
            header('Content-Type: charset=utf-8');

            $pdf->SetTitle('LAUDO_' . date('d-m-Y') . "_" . $interacao->id_chamado_interacao);
    
    
            $pdf->Output('I','laudo_tecnico_' . $interacao->id_chamado_interacao . '.pdf');

            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'GERAR_LAUDO_INSERVIVEL',
                'desc_evento' => 'ID CHAMADO: ' . $interacao->id_chamado_interacao . ' - NOME: laudo_tecnico_' . $interacao->id_chamado_interacao . '.pdf',
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
  
        }
        else {
            header('HTTP/1.0 404 Not Found');
        }
        
    }

    public function busca_email_ldap($login) {
        $url_api_ldap = $this->config->item('api_ldap');
        $url_api_ldap .= "ldap/{$login}";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url_api_ldap);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        // SOMENTE DEV
        if (ENVIRONMENT == 'development') {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        }
        $res_ldap = json_decode(curl_exec($curl));
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);
        if ($res_ldap != null) {
            $response_json = array(
                "email" => $res_ldap->email,
                "nome" => $res_ldap->displayName
            );

            $response = $res_ldap->email;
        } else {
            $response_json = null;
            $response = null;
        }

        http_response_code($http_code);
        header('Content-Type: application/json');
        echo json_encode($response_json);

        return $response;
    }





    private function enviar_email_smtp($upload = null, $id_ticket_chamado, $article, $body, $emails_copia = null) {
        $this->load->library('Mailer');
        $email = new PHPMailer(true);
        // DEV::DEBUG || PRODUCTION::DEBUG OFF
        $email->SMTPDebug = SMTP::DEBUG_OFF;
        if (ENVIRONMENT == 'development') {
            $email->SMTPDebug = SMTP::DEBUG_ON;
        }
        $email->isSMTP();
        $email->isHTML(true);
        $email->CharSet    = 'UTF-8';
        $email->Host       = "webmail.sorocaba.sp.gov.br";
        $email->SMTPAuth   = true;
        $email->Username   = $this->config->item('usr_email');
        $email->Password   = $this->config->item('pass_email');
        $email->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $email->Port       = 587;
        $email->setFrom($this->config->item('dominio_email'), 'Central De Serviços De Tecnologia Da Informação');

        if ($upload) {
            foreach ($upload as $i => $arquivo) {
                $email->addAttachment($arquivo['file'], $arquivo['name_file']);
            }
        }

        $email->Subject = "Re: Ticket#$id_ticket_chamado";
        $email->Body    = $body;
        $email->AltBody = "Ticket#$id_ticket_chamado";

        $remetentes = $this->busca_email_solicitante($article->TicketID);
        // remetente (para)


        if ($emails_copia !== null) {
            $remetentes = array_merge($remetentes, $emails_copia);
        }

        foreach ($remetentes['address'] as $remetente) {
            $email->addAddress($remetente);
        }
        // email em cópia
        foreach ($remetentes['cc'] as $remetente) {
            $email->addCC($remetente);
        }
        // email em cópia oculta
        foreach ($remetentes['cco'] as $remetente) {
            $email->addBCC($remetente);
        }
        //$this->dd->dd($email);

        try {
            $email->send();

            return array(
                "status" => 200,
                "error" => false,
                "mensagem" => null
            );
        } catch (\Throwable $th) {
            return array(
                "status" => 500,
                "error" => true,
                "message" => "Ocorreu um erro ao enviar o e-mail ao remetente."
            );
        }
    }

    public function busca_email_solicitante($TicketID) {
        $articles = $this->interacao_model->buscarEmailSolicitanteOtobo($TicketID);
        $array_emails['address'] = [];
        $array_emails['cc'] = [];
        $array_emails['cco'] = [];
        if (count($articles) == 0) {
            $articles = $this->interacao_model->buscarEmailSolicitanteOtobo($TicketID, false);            

            if (preg_match('/<([^>]+)>/', $articles[0]->a_to, $matches)) {
                // $email->addAddress(strtolower($matches[1]));
                array_push($array_emails['address'], strtolower($matches[1]));
            }
        } else {
            // insere quem abriu o chamado no indice 0 do array e set como destino
            if (preg_match('/<([^>]+)>/', $articles[0]->a_from, $matches)) {
                // $email->addAddress(strtolower($matches[1]));
                array_push($array_emails['address'], strtolower($matches[1]));
            }
        }



        // percorre todos os emails
        foreach ($articles as $article) {
            $emailsCC = explode(",", $article->a_cc);
            $emailsCCo = explode(",", $article->a_bcc);
            //$emailsCC = ['t_jopedro', 'gxmacedo'];

            // CCO
            if ($emailsCCo[0] !== "" || count($emailsCCo) > 1) {
                foreach ($emailsCC as $email_user) {
                    // if para caso email esteja 'nome.user <nome.user@sorocaba.sp.gov.br> assim pegando somente o conteúdo dentro das <>
                    if (preg_match('/<([^>]+)>/', $email_user, $matches)) {
                        $email_user = strtolower($matches[1]);

                    // if para caso contenha somente o email
                    } else if (strpos($email_user, '@') !== false) {
                        $email_user = strtolower($email_user);

                    // if para caso tenha só usuário e consultar a api para pegar o email do usuario
                    } else if ($email_user != ""){
                        $email_user = $this->busca_email_ldap($email_user);
                    }

                    if (!in_array($email_user, $array_emails['cco']) && $email_user != "") {
                        array_push($array_emails['cco'], $email_user);
                        // $email->addBCC($email_user);
                    }
                }
            }

            // CC
            if ($emailsCC[0] !== "" || count($emailsCC) > 1) {
                foreach ($emailsCC as $email_user) {
                    // if para caso email esteja 'teste <teste@sorocaba.sp.gov.br> assim pegando somente o conteúdo dentro das <>
                    if (preg_match('/<([^>]+)>/', $email_user, $matches)) {
                        $email_user = strtolower($matches[1]);

                    // if para caso tenha só usuário e consultar a api para pegar o email do usuario
                    } else if (strpos($email_user, '@') !== false) {
                        $email_user = strtolower($email_user);

                    // if para caso tenha só usuário e consultar a api para pegar o email do usuario
                    } else if ($email_user != ""){
                        $url_api_ldap = "{$this->config->item('api_ldap')}ldap/{$email_user}";

                        $curl = curl_init();
                        curl_setopt($curl, CURLOPT_URL, $url_api_ldap);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
                        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                        // SOMENTE DEV
                        if (ENVIRONMENT == 'development') {
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                        }
                        $res_ldap = json_decode(curl_exec($curl));
                        curl_close($curl);

                        if ($res_ldap != null) {
                            $email_user = $res_ldap->email;
                        } else {
                            $email_user = null;
                        }
                    }

                    if (!in_array($email_user, $array_emails['cc']) && $email_user != "") {
                        array_push($array_emails['cc'], $email_user);
                        // $email->addCC($email_user);
                    }
                }
            }
        }

        if ($this->uri->segment(1) == "chamado") {
            http_response_code(206);
            header('Content-Type: application/json');
            echo json_encode($array_emails);

            return;
        }

        return $array_emails;
    }

    public function enviar_email() {
        if (isset($_SESSION['id_usuario'])) {
            // validação para caso envie arquivo maior que o servidor PHP aceita
            $this->form_validation->set_rules('conteudo', 'Conteudo', 'required');
            if ($this->form_validation->run() == TRUE) {
                $dados = array();
                $data_arr = array();
                $dados['conteudo'] = $this->input->post("conteudo");
                $anexo = $this->input->post("anexos");
                $nome_usuario = $this->usuario_model->buscaUsuario($_SESSION["id_usuario"])->nome_usuario;
                $upload = null;
                $id_ticket = $this->input->post("id_ticket_chamado");
                $id_chamado = $this->input->post("id_chamado");
                $id_ticket_chamado = $this->chamado_model->listaTicketChamado($id_chamado);
                $id_ticket_chamado = explode('#', $id_ticket_chamado->ticket_chamado);
                $id_ticket_chamado = end($id_ticket_chamado);
                $assinatura = "
                    Atenciosamente,
                    <br>{$nome_usuario}<br>
                    Coordenadoria Geral de TI<br>
                    Secretaria de Administração<br>
                    Prefeitura Municipal de Sorocaba<br>
                    Email: <a href='mailto:informatica@sorocaba.sp.gov.br'>informatica@sorocaba.sp.gov.br</a><br>
                    Telefone: <a href=tel:3238-2174>3238-2174</a>
                ";

                $api_url = $this->config->item('url_ticketsys_api');
                $user = $this->config->item('ticketsys_login');
                $pwd = $this->config->item('ticketsys_pwd');
                $url = $api_url."Ticket/" . $id_ticket . "?UserLogin=".$user."&Password=".$pwd;
                $anexo = isset( $_FILES['anexoEmail'] ) ? $_FILES['anexoEmail'] : null;
                if( !is_null( $anexo ) ) { //caso tenha anexo
                    $this->load->library('files');
                    $arquivos = $_FILES['anexoEmail'];

                    //Diretório onde a anexo será gravada temporariamente
                    $dirToSave = $this->config->item('caminho_termos');
                    //Limite do tamanho máximo que a anexo deve ter
                    $lengthLimit = $this->config->item('limit_size_file'); //8 MB por arquivo
                    //Extensões permitidas para os arquivos ou * para todos
                    $fileExtension = array('*');
                    //Inicializa os parametros necessários para upload da anexo
                    $this->files->initialize( $dirToSave, $lengthLimit, $lengthLimit, $fileExtension );
                    //Verifica se alguma anexo foi selecionada
                    $anexo = isset( $_FILES['anexoEmail'] ) ? $_FILES['anexoEmail'] : null;

                    $anexos = array();
                    //Seta o arquivo para upload
                    $this->files->setFile( $anexo );

                    //Processa o arquivo e recebe o retorno
                    $upload = $this->files->processMultFiles();

                    //Verifica retornbou algum código, se sim, ocorreu algum erro no upload
                    if(!isset($upload['code'])) {
                        // inicia var data_arr
                        $data_arr = array();

                        // percorre os arquivos que foi feito upload
                        foreach ($upload as $i => $arquivo) {
                            $fp = fopen($arquivo['file'] , "rb");
                            if(filesize($arquivo['file']) <= 0) {
                                //$this->files->deleteFileProcessed($upload);
                                $res = array(
                                    "status" => 400,
                                    "error" => true,
                                    "message" => "Não é possivel enviar arquivo do tamanho de 0 KB."
                                );
                                http_response_code($res['status']);
                                header('Content-Type: application/json');
                                echo json_encode($res);
                                return false;
                            }
                            $binario = fread($fp, filesize($arquivo['file']));
                            $anexo = array(
                                "Content" => base64_encode($binario),
                                "ContentType" => $arquivos['type'][$i],
                                "Filename" => $arquivo['name_file']
                            );

                            array_push($anexos,$anexo);
                            $data_arr = array("Attachment" => $anexos);
                            fclose($fp);
                        }
                    } else {
                        // retorno caso der erro
                        $res = array(
                            "status" => 400,
                            "error" => true,
                            "message" => $upload['status']
                        );
                        http_response_code($res['status']);
                        header('Content-Type: application/json');
                        echo json_encode($res);
                        return false;
                    }
                }

                
                $remetentes = $this->input->post("remetentes");
                $body = $dados['conteudo'] . "<br><br>" . $assinatura;
                $data_arr += array(
                    "Article" => array(
                        // está com bug no Otobo
                        /* "Cc" => $remetentes['cc'],
                        'Bcc' => $remetentes['cco'], */
                        "Subject" => "Re: Ticket#$id_ticket_chamado",//$titulo,
                        "Body" => $body,
                        "ContentType" => "text/html; charset=utf8",
                        "IsVisibleForCustomer" => 1,
                        "CommunicationChannel" => 'Email',
                        "From" => "$nome_usuario via SIGAT<informatica@sorocaba.sp.gov.br>"
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
                $dados = array();
                $res_otobo = json_decode(curl_exec($curl));
                curl_close($curl);

                if (isset($res_otobo->Error) || (isset($res_otobo->TicketID) && $res_otobo == null)) {
                    $res = array(
                        "status" => 500,
                        "error" => true,
                        "message" => "Ocorreu um erro ao criar artigo no Otobo."
                    );
                } else {
                    $res = $this->enviar_email_smtp($upload, $id_ticket_chamado, $res_otobo, $body, $remetentes);

                    if (!is_null($anexo)) {
                        $this->files->deleteFileProcessed($upload);
                        $result = $this->interacao_model->buscarAnexoOtrs($res_otobo, $arquivos['type']);

                        foreach ($result as $arquivo) {
                            $this->interacao_model->insereAnexoSigat($id_chamado, $arquivo->id_anexo_otrs, $arquivo->nome_arquivo_otrs);
                        }
                    }
                    $this->interacao_model->logEnviarEmail($id_chamado);
                }
            } else {
                $res = array(
                    "status" => 400,
                    "error" => true,
                    "message" => "O arquivo é muito grande!"
                );
            }
            http_response_code($res['status']);
            header('Content-Type: application/json');
            echo json_encode($res);
        }
    }
}

?>