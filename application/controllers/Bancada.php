<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bancada extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("consultas_model"); //carregando o model das consultas
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model('equipamento_model'); //carregando o model equipamento
        $this->load->model('bancada_model'); //carregando o model equipamento
    }

    public function lista_bancadas() {
        //status = 0 -> livres
        //status != 0 -> todos

        $status = $this->input->post("status");
        if (isset($_SESSION['id_usuario'])) {
            
            $result = $this->bancada_model->listaBancadas($status);
            
            for($i=0; $i < sizeof($result); $i++){
                if($result[$i]['ocupado_bancada'] == 1) $result[$i]['ocupado_bancada'] = true;
                else if($result[$i]['ocupado_bancada'] == 0) $result[$i]['ocupado_bancada'] = false;

                if($result[$i]['status_bancada'] == 1) $result[$i]['status_bancada'] = true;
                else if($result[$i]['status_bancada'] == 0) $result[$i]['status_bancada'] = false;
            }

            header("Content-Type: application/json");

            echo json_encode($result);

        } else {
            header('HTTP/1.0 403 Forbidden');
        }
    }

    public function inserir_bancada(){

        $dados['nome_bancada'] = $this->input->post("nome_bancada");
        $dados['status_bancada'] = true;
        if (isset($_SESSION['id_usuario'])) {
            
            $teste = $this->bancada_model->listaBancadas(1, $dados['nome_bancada']);
            if(sizeof($teste) == 0){
                $this->bancada_model->inserirBancada($dados);

                header("Content-Type: application/json");

                echo json_encode($dados);
            }
            
            

        } else {
            header('HTTP/1.0 403 Forbidden');
        }

    }

    public function atualizar_bancada(){
        if (isset($_SESSION['id_usuario'])){
            $dados['id_bancada'] = $this->input->post('id_bancada');
            $dados['nome_bancada'] = $this->input->post('nome_bancada');
            $dados['status_bancada'] = filter_var($this->input->post('status_bancada'), FILTER_VALIDATE_BOOLEAN);
            $dados['ocupado_bancada'] = filter_var($this->input->post('ocupado_bancada'), FILTER_VALIDATE_BOOLEAN);
            
            $this->bancada_model->atualizarBancada($dados['id_bancada'],$dados['ocupado_bancada'], $dados['status_bancada']);
    
            header("Content-Type: application/json");
    
            echo json_encode($dados);
        }else {
            header('HTTP/1.0 403 Forbidden');
        }
       
        
    }
}