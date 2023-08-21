<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Local extends CI_Controller {
    function __construct() {
        parent::__construct();

        $this->load->model("local_model"); //carregando o model das consultas 
    }

    public function listar_locais() {
        if (isset($_SESSION['id_usuario'])) {
            header("Content-Type: application/json");
            echo json_encode($this->local_model->buscaLocais());
        }
    }

    public function listar_secretarias() {
        if (isset($_SESSION['id_usuario'])) {
            header("Content-Type: application/json");
            echo json_encode($this->local_model->buscaSecretarias());
        }
    }

    public function inserir_local() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            $dados['id_local'] = $this->input->post('id_local');
            $dados['nome_local'] = $this->input->post('nome_local');
            $dados['endereco_local'] = $this->input->post('endereco_local');
            $dados['secretaria_local'] = $this->input->post('secretaria_local');
            $dados['regiao_local'] = $this->input->post('regiao_local');
            $valores = $this->local_model->insereLocal($dados);

            header("Content-Type: application/json");
            echo json_encode($valores);
        }
    }

    public function atualizar_local() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            $dados['id_local'] = $this->input->post('id_local');
            $dados['nome_local'] = $this->input->post('nome_local');
            $dados['endereco_local'] = $this->input->post('endereco_local');
            $dados['secretaria_local'] = $this->input->post('secretaria_local');
            $dados['regiao_local'] = $this->input->post('regiao_local');
            $local = $this->local_model->atualizaLocal($dados);

            header("Content-Type: application/json");
            echo json_encode($local);
        }
    }
}