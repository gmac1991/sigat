<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Servico extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->model("servico_model"); //carregando o model dos servicos
        
    }

    public function listar_servicos_triagem($status = null) {
        $filas = $this->input->post("filas");
        $lista = $this->servico_model->listaServicos(NULL,$filas, $status); // 1 = serviços ativos
        for($i = 0; $i < sizeof($lista); $i++){
            $lista[$i]->valor_servico = number_format($lista[$i]->valor_servico, 2, '.', '');
            if($lista[$i]->status_servico == 1){
                $lista[$i]->status_servico = true;
            }else if($lista[$i]->status_servico == 0){
                $lista[$i]->status_servico = false;
            }
        }
        header('Content-Type: application/json');
        echo json_encode($lista);
    }

    public function inserir_servico(){

        $encoding = mb_internal_encoding(); // para palavras acentuadas.
        
        $dados['nome_servico'] = mb_strtoupper($this->input->post('nome_servico'), $encoding);
        $dados['id_fila_servico'] = $this->input->post('id_fila_servico');
        $dados['status_servico'] = $this->input->post('status_servico');
        $dados["status_servico"] = $dados["status_servico"] === "true" ? true : false;
        $dados['unidade_medida'] = strtoupper($this->input->post('unidade_medida'));
        $dados['valor_servico'] = floatval($this->input->post('valor_servico'));
        $dados['pontuacao_servico'] = $this->input->post('pontuacao_servico');
        $dados['data_ultima_alteracao'] = $this->input->post('data_ultima_alteracao');
        
        $valores = null;
        $teste = $this->servico_model->listaServicos($dados['nome_servico']);
        
        if(sizeof($teste) == 0){
            $valores = $this->servico_model->inserirServicos($dados);

            header('Content-Type: application/json');
            echo json_encode($valores);
        }
        
    }

    public function atualizar_servico(){
        
        $encoding = mb_internal_encoding(); // para palavras acentuadas.

        $dados['id_servico'] =  $this->input->post('id_servico');
        $dados['nome_servico'] = mb_strtoupper($this->input->post('nome_servico'), $encoding);
        $dados['id_fila_servico'] = $this->input->post('id_fila_servico');
        $dados['status_servico'] = $this->input->post('status_servico');
        $dados["status_servico"] = $dados["status_servico"] === "true" ? true : false;
        $dados['unidade_medida'] = strtoupper($this->input->post('unidade_medida'));
        $dados['valor_servico'] = floatval($this->input->post('valor_servico'));
        $dados['pontuacao_servico'] = $this->input->post('pontuacao_servico');
        
        $teste = $this->servico_model->listaServicos($dados['nome_servico']);
        
        $contador = 0; //Para garantir que não está atualizando um serviço com o mesmo nome de outro

        $valores = null;

        if(sizeof($teste) > 0){
            for($i=0; $i < sizeof($teste); $i++){
                if($dados['id_servico'] == $teste[$i]->id_servico){
                    $contador++;
                }
            }
        }else{
            $contador++;
        }
        
        if($contador > 0){
            $valores = $this->servico_model->atualizarServico($dados);

            header('Content-Type: application/json');
            echo json_encode($valores);
        }
        
        
    }

    

}