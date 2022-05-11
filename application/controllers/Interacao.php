<?php

date_default_timezone_set('America/Sao_Paulo');


defined('BASEPATH') OR exit('No direct script access allowed');

class Interacao extends CI_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->model("chamado_model"); //carregando o model chamado
        $this->load->model("interacao_model"); //carregando o model interacoes
        $this->load->model("usuario_model"); //carregando o model usuario

        
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
      
    public function registrar_interacao() {
    
        $dados = array();

        $dados['texto'] = $this->input->post('txtInteracao');
        $dados['id_chamado'] = $this->input->post('id_chamado');
        $dados['tipo'] = $this->input->post('tipo');
        $dados['situacao'] = $this->input->post('situacao');
        $dados['id_fila'] = $this->input->post('id_fila');
        $dados['id_fila_ant'] = $this->input->post('id_fila_ant');
        $dados['equip_atendidos'] = $this->input->post('equipamentos_atendidos');
        $dados['id_usuario'] = $this->input->post('id_usuario');

        //var_dump($dados);
        
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


            }

            else {

                $dados['tipo'] = 'TENTATIVA_ENTREGA';


            }


        }
        
        $this->interacao_model->registraInteracao($dados);
         
    }
           
  
   
  
  
    public function gerar_termo($id_chamado) {

        $dados = $this->chamado_model->buscaChamado($id_chamado,'ENTREGA');
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
        $pdf->Cell(100,10,$dados['chamado']->ticket_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(12,10,'Data ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->data_chamado,1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(14,10,'Local ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->nome_local,1);
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

        $dados = $this->chamado_model->buscaChamado($id_chamado,'ENTREGA');

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
        $pdf->Cell(100,10,$dados['chamado']->ticket_chamado,1);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(12,10,'Data ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->data_chamado,1);
        $pdf->Ln();
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(14,10,'Local ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(0,10,$dados['chamado']->nome_local,1);
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
  
    public function gerar_laudo($id_chamado) {
  
       
        $interacao = $this->interacao_model->buscaInteracaoChamado($id_chamado,array('FECHAMENTO_INS', 'ATENDIMENTO_INS'));

        if ($interacao !== NULL) { 

            $dados = $this->chamado_model->buscaChamado($id_chamado,'INSERVIVEL');
    
            $this->load->library('pdf_html');
                
            $pdf = new PDF_HTML();
            $pdf->AliasNbPages();
            $pdf->AddPage();
            $pdf->SetFont('Arial','B',18);
            $pdf->Cell(0,10,utf8_decode('Laudo Técnico'),0,0,'C');
            $pdf->Ln(10);
            $pdf->SetFont('Arial','',11);
            $pdf->Cell(0,10,'Emitido em: ' . date('d/m/Y - H:i:s'),0,0,'R');
            $pdf->Ln(15);
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(100,10,$dados['chamado']->ticket_chamado,1);
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(12,10,'Data ',1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(0,10,$dados['chamado']->data_chamado,1);
            $pdf->Ln();
            $pdf->SetFont('Arial','B',13);
            $pdf->Cell(14,10,'Local ',1);
            $pdf->SetFont('Arial','',13);
            $pdf->Cell(0,10,$dados['chamado']->nome_local,1);
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
    
            $pdf->Cell(36,10,'ID',1,0,'C');
            $pdf->Cell(0,10,utf8_decode('Descrição'),1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();
    
            foreach($dados['equipamentos'] as $equip) {
    
            $pdf->Cell(36,10,$equip->num_equipamento,1,0,'');
            $pdf->Cell(0,10,$equip->descricao_equipamento,1,0,'');
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
            $pdf->WriteHTML(utf8_decode($texto));
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
            
            //header('Content-Type: charset=utf-8');

            $pdf->SetTitle('LAUDO_' . date('d-m-Y') . "_" . $id_chamado);
    
    
            $pdf->Output('I','laudo_tecnico_' . $id_chamado . '.pdf');

            // ------------ LOG -------------------

            $log = array(
                'acao_evento' => 'GERAR_LAUDO_INSERVIVEL',
                'desc_evento' => 'ID CHAMADO: ' . $id_chamado . ' - NOME: laudo_tecnico_' . $id_chamado . '.pdf',
                'id_usuario_evento' => $_SESSION['id_usuario']
            );
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
  
        }
        else {
            header('HTTP/1.0 404 Not Found');
        }
        
    }
}

?>