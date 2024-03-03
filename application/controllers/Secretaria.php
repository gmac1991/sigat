<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Secretaria extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("secretaria_model"); //carregando o model das consultas 
    }

    public function listar_secretarias() {
        if (isset($_SESSION['id_usuario'])) {
            $secretarias = $this->secretaria_model->buscaSecretarias();
            foreach($secretarias as &$secretaria) {
                $secretaria["status_secretaria"] = $secretaria["status_secretaria"] === "0" ? FALSE : TRUE;
            }
            header("Content-Type: application/json");
            echo json_encode($secretarias);
        }
    }

    public function inserir_secretaria() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            $dados['nome_secretaria'] = $this->input->post('nome_secretaria');
            $dados['sigla_secretaria'] = $this->input->post('sigla_secretaria');
            $dados['status_secretaria'] = true;
            $dados['ultima_alteracao'] = date("Y-m-d H:i:s");
            $valores = $this->secretaria_model->insereSecretaria($dados);

            header("Content-Type: application/json");
            echo json_encode($valores);
        }
    }

    public function atualizar_secretaria() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            $dados['id_secretaria'] = $this->input->post('id_secretaria');
            $dados['nome_secretaria'] = $this->input->post('nome_secretaria');
            $dados['sigla_secretaria'] = $this->input->post('sigla_secretaria');
            $dados['status_secretaria'] = $this->input->post('status_secretaria');
            $dados["status_secretaria"] = $dados["status_secretaria"] === "true" ? true : false;
            $dados['ultima_alteracao'] = date("Y-m-d H:i:s");

            $secretaria = $this->secretaria_model->atualizaSecretaria($dados);

            header("Content-Type: application/json");
            echo json_encode($secretaria);
        }
    }
}