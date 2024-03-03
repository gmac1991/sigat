<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Local extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("consultas_model"); //carregando o model das consultas
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model("local_model"); //carregando o model das consultas
        $this->load->model("secretaria_model"); //carregando o model das secretarias

    }


    public function index ($id){
        
        if (isset($_SESSION['id_usuario'])){

            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);
            
            $local = $this->local_model->buscaLocal($id);
            $dados = array();
            $dados['id'] = $id;
            $dados['local'] = $local[0];
            $dados['usuario'] = $usuario;
            $regioes = array("NORTE", "SUL", "LESTE", "OESTE", "CENTRAL", "INTERNA");
            $dados['regioes'] = $regioes;
            $secretarias = $this->secretaria_model->buscaSecretarias();
            $dados['secretarias'] = $secretarias;
            $dados['tel'] = $this->input->get('tel');
            //-------Pesquisando telefones
            $valores['id_local'] = $id; 
            $telefones = $this->local_model->listarTelefones($valores);
            $dados['telefones'] = $telefones;
           //-------- /Pesquisando telefones
            
            $this->load->view('templates/cabecalho', $usuario);
            $this->load->view('paginas/local', $dados);
            $this->load->view('templates/rodape');

            
            if($this->input->post('id_local') != null && $usuario->autorizacao_usuario > 3){

                $valores = array();
                $valores['id_local'] = $this->input->post('id_local');
                $valores['nome_local'] = $this->input->post('nome_local');
                $valores['endereco_local'] = $this->input->post('endereco_local');
                $valores['secretaria_local'] = $this->input->post('secretaria_local');
                $valores['regiao_local'] = $this->input->post('regiao_local');
                $valores["status_local"] = $this->input->post('status_local');
                
                $this->local_model->atualizaLocal($valores);
                
                header('Location: ' . base_url('//local/' . $id));
            }

            if($this->input->post('telefone') != null && $usuario->autorizacao_usuario > 3){
                $valores = array();
                $valores['telefone'] = $this->input->post('telefone');
                $valores['setor'] = $this->input->post('setor');
                $valores['id_local'] = $id;

                if($this->input->post('acao') == 'inserir'){
                    $this->local_model->adicionarTelefone($valores);
                }

                if($this->input->post('acao') == 'editar'){
                    $valores['id'] = $this->input->post('id');
                    $this->local_model->editarTelefone($valores);
                }

                if($this->input->post('acao') == 'excluir'){
                    $id = $this->input->post('id');
                    $this->local_model->excluirTelefone($id);
                }
                
            }
            
        }else {

            header('Location: ' . base_url('/painel'));
        }
    }


    public function listar_locais() {
        if (isset($_SESSION['id_usuario'])) {
            $locais = $this->local_model->buscaLocais();
            foreach($locais as &$local) {
                $local["status_local"] = $local["status_local"] === "0" ? FALSE : TRUE;
            }

            header("Content-Type: application/json");
            echo json_encode($locais);
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
            $dados['status_local'] = true;
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
            $dados['status_local'] = $this->input->post('status_local');
            $dados["status_local"] = $dados["status_local"] === "true" ? true : false;

            $local = $this->local_model->atualizaLocal($dados);

            header("Content-Type: application/json");
            echo json_encode($local);
        }
    }

    public function ativar_local() {

    

        if (isset($_SESSION['id_usuario'])) {
          $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);
          $id_local = $this->input->post("id_local");
          
          if ($usuario->autorizacao_usuario > 3) {
            $this->local_model->ativar_local($id_local);
           
          }
    
          else {
            header("HTTP/1.1 406 Not Acceptable");
          }
        }
        else {
          header("HTTP/1.1 403 Forbidden");
        }
        
      }
}