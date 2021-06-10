<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
    function __construct() {
        parent::__construct();
        
        $this->load->model("consultas_model"); //carregando o model das consultas 
        $this->load->model("usuario_model");  //carregando o model usuario 

        
    }

    public function listar_usuarios() {

        header("Content-Type: application/json");

        echo json_encode($this->usuario_model->buscaUsuarios());
    }

    public function atualizar_usuario() {

        $dados = array();

        $dados['id_usuario'] = $this->input->post('id_usuario');
        $dados['nome_usuario'] = $this->input->post('nome_usuario');
        $dados['login_usuario'] = $this->input->post('login_usuario');
        $dados['status_usuario'] = $this->input->post('status_usuario');
        $dados['autorizacao_usuario'] = $this->input->post('autorizacao_usuario');
        $dados['fila_usuario'] = $this->input->post('fila_usuario');

        $this->usuario_model->atualizaUsuario($dados);

        header("Content-Type: application/json");

        echo json_encode($this->usuario_model->buscaUsuario($dados['id_usuario']));
        
    }

    public function inserir_usuario() {

        $dados = array();

        $dados['nome_usuario'] = $this->input->post('nome_usuario');
        $dados['login_usuario'] = $this->input->post('login_usuario');
        $dados['status_usuario'] = $this->input->post('status_usuario');
        $dados['autorizacao_usuario'] = $this->input->post('autorizacao_usuario');
        $dados['fila_usuario'] = $this->input->post('fila_usuario');

        $this->usuario_model->insereUsuario($dados);

        header("Content-Type: application/json");

        echo json_encode($this->usuario_model->buscaUltimoUsuario());
        
    }
    

}

?>
