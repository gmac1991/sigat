<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Usuario extends CI_Controller {
    function __construct() {
        parent::__construct();
        
        $this->load->model("consultas_model"); //carregando o model das consultas 
        $this->load->model("usuario_model");  //carregando o model usuario 
        $this->load->model('chamado_model');// carregando o model para os chamados bloqueados 
    }

    public function index($id) {
        if (isset($_SESSION['id_usuario'])){

                
            
            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);
            $usuario_buscado = $this->usuario_model->buscaUsuario($id);

            
            $usuario_visualizado = array();
            $usuario_visualizado['id_usuario_visualizado'] = $id;
            $usuario_visualizado['nivelAutorizacao'] = $usuario_buscado->autorizacao_usuario;
            $usuario_visualizado['nomeUsuario'] = $usuario_buscado->nome_usuario;
            $usuario_visualizado['loginUsuario'] = $usuario_buscado->login_usuario;
            $usuario_visualizado['totalInteracoes'] = $this->usuario_model->buscaTotalInteracoesUsuario($id);
            $usuario_visualizado['chamadosBloqueados'] = $this->chamado_model->buscarChamadosBloqueados($id);
            $usuario_visualizado['autorizacao'] = '';
            $usuario_visualizado['produtividade'] = $this->usuario_model->buscaTotalInteracoesUsuarioPorDia($id);
            $usuario_visualizado['chamadosAbertos'] = $this->usuario_model->buscaAberturaChamadosPorDia($id);
            $usuario_visualizado['chamadosFechados'] = $this->usuario_model->buscaEnceramentoChamadosPorDia($id);
            $usuario_visualizado['encerramento_usuario'] = $usuario_buscado->encerramento_usuario;
            $usuario_visualizado['triagem_usuario'] = $usuario_buscado->triagem_usuario;
            $usuario_visualizado['inservivel_usuario'] = $usuario_buscado->inservivel_usuario;
            

            switch ($usuario_buscado->autorizacao_usuario) {
                case 2:
                    $usuario_visualizado['autorizacao'] = 'Operação';
                    break;
                case 3:
                    $usuario_visualizado['autorizacao'] = 'Supervisão';
                    break;
                case 4:
                    $usuario_visualizado['autorizacao'] = 'Master';
                    break;
                default:
                    $usuario_visualizado['autorizacao'] = 'erro';
                    break;
            }

            $usuario_buscado->ultimo_login = $this->usuario_model->buscaUltimoLogin($id);

            
            if($usuario->autorizacao_usuario == 4 /*|| $_POST['easteregg'] == "dangomes"*/) {
                $this->load->view('templates/cabecalho', $usuario);
                $this->load->view('paginas/usuario',$usuario_visualizado);
                $this->load->view('templates/rodape');
            }else {

                header('Location: ' . base_url('painel'));
            }
        }else {

            header('Location: ' . base_url('painel'));
        }


    }

    public function listar_usuarios() {

        $usuarios = $this->usuario_model->buscaUsuarios();
        foreach($usuarios as &$usuario) {
            $usuario["triagem_usuario"] = $usuario["triagem_usuario"] === "0" ? FALSE : TRUE;
            $usuario["encerramento_usuario"] = $usuario["encerramento_usuario"] === "0" ? FALSE : TRUE;
            $usuario["status_usuario"] = $usuario["status_usuario"] == "0" ? FALSE : TRUE;
        }

        
        header("Content-Type: application/json");
        echo json_encode($usuarios);
    }

    public function test() {
        $usuario = array();
        $usuarios = $this->usuario_model->buscaUsuarios();
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
        $dados['status_usuario'] = $this->input->post('status_usuario');
        $dados["triagem_usuario"] = $dados["triagem_usuario"] === "true" ? 1 : 0;
        $dados["encerramento_usuario"] = $dados["encerramento_usuario"] === "true" ? 1 : 0;
        $dados["status_usuario"] = $dados["status_usuario"] === "true" ? 1 : 0;
       ;

        $dados_usuario = $this->usuario_model->atualizaUsuario($dados);

        // $usuario = $this->usuario_model->buscaUsuario($dados['id_usuario']);
        $dados_usuario["status_usuario"] = $dados_usuario["status_usuario"] === 0 ? FALSE : TRUE;
        $dados_usuario["triagem_usuario"] =  $dados_usuario["triagem_usuario"] === 0 ? FALSE : TRUE;
        $dados_usuario["encerramento_usuario"] = $dados_usuario["encerramento_usuario"] === 0 ? FALSE : TRUE;
        $dados_usuario["data_usuario"] = $this->input->post('data_usuario');

        header("Content-Type: application/json");
        echo json_encode($dados_usuario);
    }

    public function inserir_usuario() {
        if (isset($_SESSION['id_usuario'])) {
            $dados = array();
            
        
            $dados['nome_usuario'] = $this->input->post('nome_usuario');
            $dados['login_usuario'] = $this->input->post('login_usuario');
            $dados['status_usuario'] = $this->input->post('status_usuario');
            $dados["status_usuario"] = true;
            $dados['autorizacao_usuario'] = $this->input->post('autorizacao_usuario');
            $dados['triagem_usuario'] = $this->input->post('triagem_usuario');
            $dados['encerramento_usuario'] = $this->input->post('encerramento_usuario');
            $dados['data_criacao_usuario'] = $this->input->post('data_usuario');
            $dados['fila_usuario'] = 0;
            $dados["encerramento_usuario"] = $dados["encerramento_usuario"] === "true" ? 1 : 0;
            $dados["triagem_usuario"] = $dados["triagem_usuario"] === "true" ? 1 : 0;

            //if($dados['triagem_usuario'] == "true") {
            //    $dados['triagem_usuario'] = 1;
            //} else {
            //    $dados['triagem_usuario'] = 0;
            //}

            // removendo aspas duplas e simples
            $dados['login_usuario'] = str_replace('"', "", $dados['login_usuario']);
            
            $usuario = $this->usuario_model->validaUsuario($dados);

            if(!empty($usuario)) {
                echo '<script>alert("Usuário está ativo no sistema");</script>';
                return;
            }
            
            $insercao = $this->usuario_model->insereUsuario($dados);

            if (!empty($insercao)) {
                $insercao['data_usuario'] = date("Y-m-d H:i:s", strtotime($insercao['data_usuario']));
                $insercao['alteracao_usuario'] = date("Y-m-d H:i:s", strtotime($insercao['alteracao_usuario']));
                $insercao["encerramento_usuario"] = $insercao["encerramento_usuario"] === 0 ? FALSE : TRUE;
                $insercao["triagem_usuario"] = $insercao["triagem_usuario"] === 0 ? FALSE : TRUE;
                header("Content-Type: application/json");
                echo json_encode($insercao);
            }

            else {
                echo FALSE;
            }
        }
    }

    public function ativar_permissoes() {

    

        if (isset($_SESSION['id_usuario'])) {
          $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

          $id_usuario = $this->input->post('id_usuario');
          $permissao = $this->input->post('permissao');
          
          if ($usuario->autorizacao_usuario > 3) {
            $this->usuario_model->ativar_permissoes($id_usuario, $permissao);
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

?>
