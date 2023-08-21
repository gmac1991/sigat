<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
    function __construct() {
        parent::__construct();
        
        $this->load->model("consultas_model"); //carregando o model das consultas 
        $this->load->model("usuario_model");  //carregando o model usuario 

        
    }

    public function listar_usuarios() {

        $usuarios = $this->usuario_model->buscaUsuarios();
        foreach($usuarios as &$usuario) {
            $usuario["triagem_usuario"] = $usuario["triagem_usuario"] === "0" ? FALSE : TRUE;
            $usuario["encerramento_usuario"] = $usuario["encerramento_usuario"] === "0" ? FALSE : TRUE;
        }
        header("Content-Type: application/json");
        echo json_encode($usuarios);
    }

    public function atualizar_usuario() {

        $dados = array();

        $dados['id_usuario'] = $this->input->post('id_usuario');
        $dados['nome_usuario'] = $this->input->post('nome_usuario');
        $dados['login_usuario'] = $this->input->post('login_usuario');
        $dados['status_usuario'] = $this->input->post('status_usuario');
        $dados['autorizacao_usuario'] = $this->input->post('autorizacao_usuario');
        $dados['fila_usuario'] = $this->input->post('fila_usuario');
        $dados['triagem_usuario'] = $this->input->post('triagem_usuario');
        $dados['encerramento_usuario'] = $this->input->post('encerramento_usuario');
        $dados["triagem_usuario"] = $dados["triagem_usuario"] === "true" ? 1 : 0;
        $dados["encerramento_usuario"] = $dados["encerramento_usuario"] === "true" ? 1 : 0;

        $dados_usuario = $this->usuario_model->atualizaUsuario($dados);

        // $usuario = $this->usuario_model->buscaUsuario($dados['id_usuario']);
        $dados_usuario["triagem_usuario"] =  $dados_usuario["triagem_usuario"] === 0 ? FALSE : TRUE;
        $dados_usuario["encerramento_usuario"] = $dados_usuario["encerramento_usuario"] === 0 ? FALSE : TRUE;

        header("Content-Type: application/json");
        echo json_encode($dados_usuario);
    }

    public function inserir_usuario() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            
            $dados['nome_usuario'] = $this->input->post('nome_usuario');
            $dados['login_usuario'] = $this->input->post('login_usuario');
            $dados['status_usuario'] = $this->input->post('status_usuario');
            $dados['autorizacao_usuario'] = $this->input->post('autorizacao_usuario');
            $dados['data_criacao_usuario'] = $this->input->post('autorizacao_usuario');
            $dados['fila_usuario'] = 0;
            $dados['triagem_usuario'] = $this->input->post('triagem_usuario');

            if($dados['triagem_usuario'] == "true") {
                $dados['triagem_usuario'] = 1;
            } else {
                $dados['triagem_usuario'] = 0;
            }

            // removendo aspas duplas e simples
            $dados['login_usuario'] = str_replace('"', "", $dados['login_usuario']);
            $dados['login_usuario'] = str_replace("'", "", $dados['login_usuario']);
            $usuario = $this->usuario_model->validaUsuario($dados);

            if(!empty($usuario)) {
                echo '<script>alert("Usuário está ativo no sistema");</script>';
                return;
            }
            
            $insercao = $this->usuario_model->insereUsuario($dados);
            if (!empty($insercao)) {
                $insercao['data_usuario'] = date("d/m/Y", strtotime($insercao['data_usuario']));
                $insercao['alteracao_usuario'] = date("d/m/Y", strtotime($insercao['alteracao_usuario']));
                header("Content-Type: application/json");
                echo json_encode($insercao);
            }

            else {
                echo FALSE;
            }
        }
    }
}

?>
