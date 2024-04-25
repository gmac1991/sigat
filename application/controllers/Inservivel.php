<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Inservivel extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("inservivel_model");
        $this->load->model("chamado_model");
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model("consultas_model"); //carregando o model das consultas
        $this->load->model("reparo_model"); // carregando model de reparo
    }

    public function index() {
        if (isset($_SESSION['id_usuario'])){
            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

            if ($usuario->inservivel_usuario) {
                

                if (!$this->inservivel_model->lista_remessa_aberta()) {
                    $this->inservivel_model->abre_nova_remessa();
                } else if (!$this->inservivel_model->lista_remessa_aberta(true)) {
                    $this->inservivel_model->abre_nova_remessa(true);
                }

                $this->load->view('templates/cabecalho', $usuario);
                $this->load->view('paginas/inservivel/lista_inservivel');
                $this->load->view('templates/rodape');
            }else {
                header('Location: ' . base_url('/painel'));
            }
        }else {
            header('Location: ' . base_url('/painel'));
        }
    }

    public function listarRemessas() {
        if (isset($_SESSION['id_usuario'])){
            $remessas = $this->inservivel_model->listar_remessas();
            foreach ($remessas as &$remessa) {
                switch ($remessa['divisao_remessa']) {
                    case DGTI:
                        $remessa['cod_divisao'] = DGTI;
                        $remessa['divisao_remessa'] = "DGTI";
                        break;

                    case DIN:
                        $remessa['cod_divisao'] = DIN;
                        $remessa['divisao_remessa'] = "DIN";
                        break;
                }
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($remessas);
        }
    }
    
    public function visualizarDetalhado($id_remessa_inservivel) {
        if (isset($_SESSION['id_usuario'])){
            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);
            if ($usuario->inservivel_usuario) {
                $dados['remessa_inservivel'] = $this->inservivel_model->listar_remessa($id_remessa_inservivel);
                
                if ($dados['remessa_inservivel']) {
                    $dados['data_fechamento'] = $dados['remessa_inservivel']->data_fechamento;
                    $dados['data_entrega'] = $dados['remessa_inservivel']->data_entrega;
                    $dados['falha_envio'] = $dados['remessa_inservivel']->falha_envio;
                    $dados['nome_usuario'] = $usuario->nome_usuario;
                    $dados['id_remessa'] = $id_remessa_inservivel;
        
                    $dados['equipamentos'] = $this->inservivel_model->listar_pool($id_remessa_inservivel);
                    $dados['equipamentos'] = explode("::",$dados['equipamentos'][0]['pool_equipamentos']);
        
                    $this->load->view('templates/cabecalho', $usuario);
                    $this->load->view('paginas/inservivel/ver_inservivel', $dados);
                    $this->load->view('templates/rodape');
                } else {
                    show_404();
                }
            } else {
                header('Location: ' . base_url('/painel'));
            }
        } else {
            header('Location: ' . base_url('/painel'));
        }
    }

    public function listar_detalhado($id_remessa_inservivel) {
        if (isset($_SESSION['id_usuario'])){
            $dados['equipamentos'] = $this->inservivel_model->listar_pool($id_remessa_inservivel);
            $dados['equipamentos'] = explode("::",$dados['equipamentos'][0]['pool_equipamentos']);
            $dados['remessa_inservivel'] = array();
            if(!empty($dados['equipamentos'][0])) {
                foreach ($dados['equipamentos'] as $equipamento) {
                    array_push($dados['remessa_inservivel'], $this->inservivel_model->listar_equipamentos_remessa($id_remessa_inservivel, $equipamento));
                }
            }

            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode($dados['remessa_inservivel']);
        }
    }

    public function inserirLista(string $equip_inserv) {
        $remessa_aberta = $this->inservivel_model->lista_remessa_aberta();

        if($remessa_aberta->pool_equipamentos == null) {
            //$equipamentos = implode("::", $remessa_aberta->pool_equipamentos);
            $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $equip_inserv);
        } else {
            $equips = explode("::", $remessa_aberta->pool_equipamentos);
            array_push($equips, $equip_inserv);
            $equipamentos = implode("::", $equips);
            $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $equipamentos);
        }
    }

    public function fecharRemessa() {
        $id_remessa_inservivel = $this->input->post("id_remessa");
        $remessa = $this->inservivel_model->listar_remessa($id_remessa_inservivel);
        if ($remessa->data_fechamento == null && $remessa->pool_equipamentos) {
            $this->inservivel_model->fechar_remessa($id_remessa_inservivel);
            $this->inservivel_model->abre_nova_remessa();
        } else {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(array(
                "erro" => true,
                "mensagem" => "A lista está vázia!"
            ));
        }
    }

    public function registrarEntrega() {
        $dados = array();

        $dados['id_remessa'] = $this->input->post('id_remessa');
        $dados['erro_remessa'] = $this->input->post('erro_remessa') == 'true' ? true : false;
        $dados['equipamentos'] = $this->input->post('equipamentos') == 'null' ? null : $this->input->post('equipamentos');

        if (!$dados['erro_remessa']) {
            $dados['nome_recebedor'] = $this->input->post('nome_recebedor');
            $dados['data_recebimento'] = $this->input->post('data_recebimento');

            $config = array();
            $config['upload_path']          = $this->config->item('caminho_termos');
            $config['overwrite']            = true;
            $config['allowed_types']        = 'pdf'; //tipos de arquivos permitidos
            $config['max_size']             = 5000; //tamanho maximo: 5 Megabytes

            // ----- UPLOAD TERMO DA REMESSA ----
            $config['file_name'] = 'Remessa_' . date('d-m-Y') . '_' . $dados['id_remessa'] . '.pdf';

            $this->load->library('upload', $config);

            if (! $this->upload->do_upload('termo_remessa')) {
                $erro = true;
                $dados['erros_upload'] = array('error' => $this->upload->display_errors());

                foreach ($dados['erros_upload'] as $erro) {
                    echo $erro;
                }

                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(array(
                    "erro" => true,
                    "mensagem" => "Erro ao fazer upload do arquivo"
                ));
                return;
            } else {
                $dados['nome_termo_remessa'] =  $config['file_name'];
            }

            unset($this->upload);
            $dados['id_termo'] = $this->inservivel_model->salvarTermo($dados['id_remessa'], $dados['nome_termo_remessa']);
            $this->inservivel_model->entregaRemessa($dados['id_remessa'], $dados['nome_recebedor'], $dados['data_recebimento'], $dados['id_termo']);
        }else if($dados['equipamentos']){
            $dados['equipamentos'] = json_decode($dados['equipamentos']);
            $pool_equipamentos = $this->inservivel_model->listar_pool($dados['id_remessa'])[0]['pool_equipamentos'];
            $pool_equipamentos = explode("::", $pool_equipamentos);
            
            foreach ($dados['equipamentos'] as $equipamento) {
                $posicao = array_search($equipamento, $pool_equipamentos);
                if ($posicao !== false) {
                    unset($pool_equipamentos[$posicao]);
                    $pool_equipamentos = array_values($pool_equipamentos);
                }
            }
            $pool_equipamentos = implode("::", $pool_equipamentos);
            $this->inservivel_model->alterar_pool($dados['id_remessa'], $pool_equipamentos);
            $remessa_aberta = $this->inservivel_model->lista_remessa_aberta();
            $remessa_aberta->pool_equipamentos = explode("::", $remessa_aberta->pool_equipamentos);

            if ($remessa_aberta->pool_equipamentos[0] == null) {
                $remessa_aberta->pool_equipamentos = implode("::", $dados['equipamentos']);
                $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $remessa_aberta->pool_equipamentos);
            } else {
                $remessa_aberta->pool_equipamentos = array_merge($remessa_aberta->pool_equipamentos, $dados['equipamentos']);
                $remessa_aberta->pool_equipamentos = implode("::", $remessa_aberta->pool_equipamentos);
                $this->inservivel_model->alterar_pool($remessa_aberta->id_remessa_inservivel, $remessa_aberta->pool_equipamentos);
            }
        } else {
            $this->inservivel_model->erroRemessa($dados['id_remessa']);
        }
        
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(array(
            "erro" => false,
            "mensagem" => ""
        ));
    }

    public function gerarTermo($num_equipamento) {
        if (isset($_SESSION['id_usuario'])){
            
            if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $this->config->item('caminho_termos') . 'Laudo_Equipamento_' . $num_equipamento . '.pdf')) {
                header("Location: {$this->config->item('base_url')}/{$this->config->item('caminho_termos')}Laudo_Equipamento_{$num_equipamento}.pdf");
            }

            $laudo = $this->inservivel_model->lista_laudo_equipamento($num_equipamento);
            if($laudo) {
                $nome_usuario_resp = $this->usuario_model->buscaUsuario($laudo->id_usuario_reparo)->nome_usuario;

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
                $pdf->Cell(48,10,$num_equipamento,1,0,'');
                $pdf->Cell(0,10,utf8_decode($laudo->descricao_equipamento),1,0,'');
                $pdf->Ln();
                $pdf->SetFont('Arial','B',14);
                $pdf->Cell(0,10,utf8_decode('LAUDO'),1,0,'C');
                $pdf->Ln();
                $pdf->SetFont('Arial','',12);
                $pdf->Multicell(0,8,utf8_decode($laudo->texto_interacao), 1, 1);
                $pdf->SetFont('Arial','I',11);
                $pdf->Cell(0,10,utf8_decode("Elaborado por " . $nome_usuario_resp . " em ") . 
                date('d/m/Y - H:i:s', strtotime($laudo->data_fim_reparo)),0,0,'R');

                $pdf->Output('I','termo_' . '.pdf', TRUE);
            } else {
                show_404();
            }
        }
    }

    public function reverter_remessa(){
        if (isset($_SESSION['id_usuario'])) {

            $equip = $this->input->post('equip');
            $remessa = $this->input->post('remessa');
            $reparo = $this->input->post('reparo');
            $chamado = $this->input->post('chamado');
            $usuario = $_SESSION['id_usuario'];
            $result_remessa = null;
            $result = null;
            $result_remessa = $this->inservivel_model->listar_remessa($remessa);
            if($result_remessa != null){
                if($result_remessa->data_entrega == null){
                    $result = $this->reparo_model->excluirReparo($reparo);
                    if($result == true){
                        $mensagem = $this->inservivel_model->reverterRemessa($equip, $remessa, $reparo, $chamado, $usuario);
                    }else{
                        $mensagem['mensagem'] = 'Houve um erro no processamento de remessas.';
                        $mensagem['status'] = 'error';
                    }
                }else{
                    $mensagem['mensagem'] = 'Este equipamento já foi entregue e sua remessa não pode ser desfeita.';
                    $mensagem['status'] = 'error';
                }
            }else{
                $mensagem['mensagem'] = 'A remessa para este equipamento não pode ser revertida.';
                $mensagem['status'] = 'error';
            }

                
            header('Content-Type: application/json');
            echo json_encode($mensagem);
        }
    }

    public function listar_remessa($id){
        $remessa = $this->inservivel_model->listar_remessa($id);

        header('Content-Type: application/json');
            echo json_encode($remessa);
    }
}