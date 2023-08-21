<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class ModeloMensagem extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("ModeloMensagem_model"); //carregando o model da
    }

    public function listar_modelo_mensagem() {
        if (isset($_SESSION['id_usuario'])) {
            if (isset($_GET['tipo']) && isset($_GET['id_fila'])) {
                $tipo = $_GET['tipo'];
                $id_fila = $_GET['id_fila'];
                $result_banco = $this->ModeloMensagem_model->listaModeloMensagem($tipo, $id_fila);
            } else {
                $result_banco = $this->ModeloMensagem_model->listaModeloMensagem(NULL, NULL);
            }

            foreach($result_banco as &$mensagem) {
                $mensagem["status_modelo_mensagem"] = $mensagem["status_modelo_mensagem"] == 0 ? FALSE : TRUE;
            }

            header('Content-Type: application/json');
            echo json_encode($result_banco);
        }
    }

    public function inserir_modelo_mensagem() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();

            $dados['mensagem_modelo_mensagem'] = $this->input->post('mensagem_modelo_mensagem');
            $dados['tipo_modelo_mensagem'] = $this->input->post('tipo_modelo_mensagem');
            $dados['fila_modelo_mensagem'] = $this->input->post('fila_modelo_mensagem');
            $dados['status_modelo_mensagem'] = $this->input->post('status_modelo_mensagem');

            $this->ModeloMensagem_model->insereModeloMensagem($dados);

            header("Content-Type: application/json");

            echo json_encode($this->ModeloMensagem_model->buscaUltimoModeloMensagem());
        }
    }

    public function atualizar_modelo_mensagem() {

        $dados = array();

        $dados['id_modelo_mensagem'] = $this->input->post('id_modelo_mensagem');
        $dados['fila_modelo_mensagem'] = $this->input->post('fila_modelo_mensagem');
        $dados['mensagem_modelo_mensagem'] = $this->input->post('mensagem_modelo_mensagem');
        $dados['tipo_modelo_mensagem'] = $this->input->post('tipo_modelo_mensagem');
        //$dados['status_modelo_mensagem'] = $this->input->post('status_modelo_mensagem');
        $dados['status_modelo_mensagem'] = $this->input->post('status_modelo_mensagem');
        $dados["status_modelo_mensagem"] = $dados["status_modelo_mensagem"] === "true" ? 1 : 0;
        
        $dados_modelo_mensagem = $this->ModeloMensagem_model->atualizaModeloMensagem($dados);
        $dados_modelo_mensagem["status_modelo_mensagem"] =  $dados_modelo_mensagem["status_modelo_mensagem"] === 0 ? FALSE : TRUE;
        $dados_modelo_mensagem["data_modelo_mensagem"] = $this->input->post('data_modelo_mensagem');

        header("Content-Type: application/json");
        //echo json_encode($this->ModeloMensagem_model->buscaModeloMensagem($dados['id_modelo_mensagem']));
        echo json_encode($dados_modelo_mensagem);
    }
}