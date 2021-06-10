<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Interacao extends CI_Controller {

    function __construct() {
        parent::__construct();
        
        $this->load->model("chamado_model"); //carregando o model chamado
        $this->load->model("interacao_model"); //carregando o model interacoes

        
    }

    public function remover_interacao() {

        $this->interacao_model->removeInteracao($this->input->post('id_interacao'));
    }
      
    public function registrar_interacao() {
    
        $dados = array();

        $dados['texto'] = $this->input->post('txtInteracao');
        $dados['id_chamado'] = $this->input->post('id_chamado');
        $dados['tipo'] = $this->input->post('tipo');
        $dados['situacao'] = $this->input->post('situacao');
        $dados['id_fila'] = $this->input->post('id_fila');
        $dados['id_fila_ant'] = $this->input->post('id_fila_ant');
        $dados['patri_atendidos'] = $this->input->post('patrimonios_atendidos');
        $dados['equip_atendidos'] = $this->input->post('equipamentos_atendidos');
        $dados['id_usuario'] = $this->input->post('id_usuario');
        
        $this->interacao_model->registraInteracao($dados);
    
    }
  
    public function registrar_entrega() {
        
        $dados = array();

        $dados['id_usuario'] = $_SESSION['id_usuario'];
  
        $dados['nome_recebedor'] = $this->input->post('nome_recebedor');
       
        $dados['id_chamado'] = $this->input->post('id_chamado');
        $dados['tipo'] = 'ENTREGA';
  
        // ----- UPLOAD TERMO ENTREGA ----  
        
        $config = array();
        
        $config['upload_path']          = './termos/';
        $config['overwrite']            = TRUE;
        $config['allowed_types']        = 'pdf'; //tipos de arquivos permitidos
        $config['max_size']             = 5000; //tamanho maximo: 5 Megabytes
        
        $config['file_name'] = 'Entrega_' . date('d-m-Y') . '_' . $dados['id_chamado'] . '.pdf';
        
        $this->load->library('upload', $config);

        if (! $this->upload->do_upload('termo_entrega')) {

            $dados['erros_upload'] = array('error' => $this->upload->display_errors());

            foreach ($dados['erros_upload'] as $erro) {

                echo strip_tags($erro);

            }

        } else {

            $dados['nome_termo_entrega'] = $config['file_name'];

            unset($this->upload);

            // ----- UPLOAD TERMO RESP (se houver) ---- 

            $config = array();

            $config['upload_path']          = './termos/';
            $config['overwrite']            = TRUE;
            $config['allowed_types']        = 'pdf'; //tipos de arquivos permitidos
            $config['max_size']             = 5000; //tamanho maximo: 5 Megabytes
            $config['file_name'] = 'Resp_' . date('d-m-Y') . '_' . $dados['id_chamado'] . '.pdf';

            $this->load->library('upload', $config);

            if ($this->upload->do_upload('termo_responsabilidade')) {

                $dados['nome_termo_responsabilidade'] = $config['file_name'];    
            }

            else {

                $dados['erros_upload'] = array('error' => $this->upload->display_errors());

                foreach ($dados['erros_upload'] as $erro) {

                    echo strip_tags($erro);

                }
            }

            echo "ok"; //nao apagar
            $this->interacao_model->registraInteracao($dados);

        }
         
    }
           
  
    public function registrar_falha_entrega() {
        
        $dados = array();
        
        $dados['id_chamado'] = $this->input->post('id_chamado');
        
        $dados['tipo'] = 'FALHA_ENTREGA';
  
        $dados['texto'] = $this->input->post('descr_falha');

        $dados['id_usuario'] = $_SESSION['id_usuario'];
          
        $this->interacao_model->registraInteracao($dados); 
  
    }
  
  
    public function gerar_termo($id_chamado) {

        $dados = $this->chamado_model->buscaChamado($id_chamado,'ENTREGA');

        $this->load->library('pdf');
                
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,10,'Termo de Entrega',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,date('d/m/Y'),0,0,'R');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(42,10,'Num. do chamado ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(30,10,$dados['chamado']->id_chamado,1);
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
        $pdf->Cell(0,10,$dados['chamado']->nome_solicitante_chamado,1);
        $pdf->Ln();
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Equipamentos',0,0,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();

        // =========== PATRIMONIADOS ==========
        if (!empty($dados['patrimonios'])) {

            $pdf->Cell(36,10,'Num. patrimonio',1,0,'C');
            $pdf->Cell(0,10,'Descricao',1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();
            
            foreach($dados['patrimonios'] as $equip) {

                $pdf->Cell(36,10,$equip->num_patrimonio,1,0,'');
                $json = file_get_contents('https://sistemas.sorocaba.sp.gov.br/acesso_patrimonio/api/patrimonio/' . $equip->num_patrimonio);
                $pdf->Cell(0,10,json_decode($json)->descrBem,1,0,'');
                $pdf->Ln();

            }

        }

        // ========== SEM PATRIMONIO ==========
        if (!empty($dados['equipamentos'])) {

            $pdf->Cell(36,10,'Num. serie',1,0,'C');
            $pdf->Cell(0,10,'Descricao',1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();

            foreach($dados['equipamentos'] as $equip) {

                $pdf->Cell(36,10,$equip->num_equipamento,1,0,'');
                $pdf->Cell(0,10,$equip->desc_equipamento,1,0,'');
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
        //$pdf->SetX(-20);
        $pdf->Ln(0);
        $pdf->Cell(140,8,'(nome por extenso ou assinatura e carimbo)',0,0,'C');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Data: ',0,0,'R');
        $pdf->Cell(30,10,date('d/m/Y'));

        $pdf->Output('D','termo_entrega_' . $id_chamado . '.pdf', TRUE);

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
        $pdf->Cell(0,10,date('d/m/Y'),0,0,'R');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(42,10,'Num. do chamado ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(30,10,$dados['chamado']->id_chamado,1);
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
        $pdf->Cell(0,10,$dados['chamado']->nome_solicitante_chamado,1);
        $pdf->Ln();
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Equipamentos',0,0,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();

        // =========== PATRIMONIADOS ==========
        if (!empty($dados['patrimonios'])) {

            $pdf->Cell(36,10,'Num. patrimonio',1,0,'C');
            $pdf->Cell(0,10,'Descricao',1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();
            
            foreach($dados['patrimonios'] as $equip) {

                $pdf->Cell(36,10,$equip->num_patrimonio,1,0,'');
                $json = file_get_contents('https://sistemas.sorocaba.sp.gov.br/acesso_patrimonio/api/patrimonio/' . $equip->num_patrimonio);
                $pdf->Cell(0,10,json_decode($json)->descrBem,1,0,'');
                $pdf->Ln();

            }

        }

        // ========== SEM PATRIMONIO ==========
        if (!empty($dados['equipamentos'])) {

            $pdf->Cell(36,10,'Num. serie',1,0,'C');
            $pdf->Cell(0,10,'Descricao',1,0,'C');
            $pdf->SetFont('Arial','',12);
            $pdf->Ln();

            foreach($dados['equipamentos'] as $equip) {

                $pdf->Cell(36,10,$equip->num_equipamento,1,0,'');
                $pdf->Cell(0,10,$equip->desc_equipamento,1,0,'');
                $pdf->Ln();
            }

        }

        $pdf->Ln(5);

        $pdf->SetFont('Arial','',10);
        $pdf->MultiCell(190,8,'Declaro para os devidos fins, que na presente data ' .
        'recebi da Prefeitura Municipal de Sorocaba, o (s) material (ais) acima relacionado (s), ' .
        'para uso exclusivo da municipalidade, pelo (s) qual (ais) assumo inteira responsabilidade ' . 
        'pelo seu bom uso e conservacao, ficando ainda ciente que deverei comunicar por escrito a ' . 
        'Secao de Administracao de Materiais Permanentes qualquer alteracao do (s) mesmo (s). '.
        'Sendo caracterizado o mau uso ou nao localizado, sera comunicado a ' .
        'Secretaria de Administracao para as demais providencias, ' .
        'em acordo com o Decreto n. 16.573 de 22 de abril de 2009.');

        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Responsavel: ');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(120,8,'','B',1);
        $pdf->Ln(0);
        $pdf->SetFont('Arial','I',10);
        //$pdf->SetX(-20);
        $pdf->Ln(0);
        $pdf->Cell(140,8,'(nome por extenso ou assinatura e carimbo)',0,0,'C');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(30,10,'Data: ',0,0,'R');
        $pdf->Cell(30,10,date('d/m/Y'));

        $pdf->Output('D','termo_resp_' . $id_chamado . '.pdf', TRUE);

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
  
        $dados = $this->chamado_model->buscaChamado($id_chamado,'INSERVIVEL');
        $interacao = $this->interacao_model->buscaInteracao($id_chamado,array('FECHAMENTO_INS', 'ATENDIMENTO_INS'));
  
        //var_dump($interacao->texto_interacao);
  
        $this->load->library('pdf_html');
             
        $pdf = new PDF_HTML();
        $pdf->AliasNbPages();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',18);
        $pdf->Cell(0,10,'Laudo Tecnico',0,0,'C');
        $pdf->Ln(10);
        $pdf->SetFont('Arial','',11);
        $pdf->Cell(0,10,'Emissao: ' . date('d/m/Y'),0,0,'R');
        $pdf->Ln(15);
        $pdf->SetFont('Arial','B',13);
        $pdf->Cell(42,10,'Num. do chamado ',1);
        $pdf->SetFont('Arial','',13);
        $pdf->Cell(30,10,$dados['chamado']->id_chamado,1);
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
        $pdf->Cell(0,10,$dados['chamado']->nome_solicitante_chamado,1);
        $pdf->Ln();
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',14);
        $pdf->Cell(0,10,'Equipamentos',0,0,'C');
        $pdf->SetFont('Arial','B',12);
        $pdf->Ln();
  
        $pdf->Cell(36,10,'Num. patrimonio',1,0,'C');
        $pdf->Cell(0,10,'Descricao',1,0,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln();
  
        foreach($dados['patrimonios'] as $equip) {
  
          $pdf->Cell(36,10,$equip->num_patrimonio,1,0,'');
          $json = file_get_contents('https://sistemas.sorocaba.sp.gov.br/acesso_patrimonio/api/patrimonio/' . $equip->num_patrimonio);
          $pdf->Cell(0,10,utf8_decode(json_decode($json)->descrBem),1,0,'');
          $pdf->Ln();
  
  
        }
  
        preg_match("/(.*)(?=<hr)/",$interacao->texto_interacao, $texto); //fazendo o parse para pegar o texto antes do <hr>
  
        // $texto = strip_tags($texto[0]); //removendo as tags html
  
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(0,10,'Laudo','B',0,'C');
        $pdf->SetFont('Arial','',12);
        $pdf->Ln(10);
        $pdf->Cell(0,10,'Os equipamentos acima foram classificados como INSERVIVEL conforme laudo abaixo:');
        $pdf->Ln(5);
        $pdf->WriteHTML(utf8_decode($texto[0]));
        $pdf->Ln(10);
        $pdf->Cell(0,1,'','B',0,'C');
        $pdf->Ln(5);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(120,10,'Data: ',0,0,'R');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,10,$interacao->data_interacao);
        $pdf->Ln(10);
        $pdf->SetFont('Arial','B',12);
        $pdf->Cell(120,10,'Tecnico: ',0,0,'R');
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,10,$interacao->nome_usuario);
        
        //header('Content-Type: charset=utf-8');
  
  
        $pdf->Output('D','laudo_tecnico_' . $id_chamado . '.pdf');

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'GERAR_LAUDO_INSERVIVEL',
            'desc_evento' => 'ID CHAMADO: ' . $id_chamado . ' - NOME: laudo_tecnico_' . $id_chamado . '.pdf',
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
  
        
        
    }

    public function adicionar_equipamentos() {

        $dados = array();


        $dados['patrimonios'] =       $this->input->post("listaPatrimoniosEquip");
        $dados['json_equip'] =        $this->input->post("json_equip");
        $dados['id_usuario'] =        $_SESSION['id_usuario'];
        $dados['id_chamado'] =        $this->input->post("id_chamado");
        $dados['tipo'] =              'ADC_EQUIP';


        $this->interacao_model->registraInteracao($dados);



    }

}

?>