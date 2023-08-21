<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    function __construct() {
        parent::__construct();
    
        $this->load->model("consultas_model"); //carregando o model das consultas 
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model("admin_model"); //carregando o model usuario
    
        
    }

    public function index() { 

        if (isset($_SESSION['id_usuario'])) {
      
            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

            if ($usuario->autorizacao_usuario >= 4) {
                $this->load->view('templates/cabecalho', $usuario);
                $this->load->view('paginas/admin');
                $this->load->view('templates/rodape');

            } else {

                header('Location: ' . base_url('painel'));
            }
      
        } else {
        header('Location: ' . base_url(),false,403);
      }
    }


    public function listar_eventos() {

        if (isset($_SESSION['id_usuario'])) {

            $result_banco = $this->admin_model->listaEventos();
            
            $lista_painel['data'] = array();

            foreach ($result_banco as $linha) {

            $lista_painel['data'][] = array(
                0 => $linha->id_evento,
                1 => $linha->acao_evento,
                2 => $linha->desc_evento,
                3 => $linha->data_evento,
                4 => $linha->nome_usuario
                );
            }

            header("Content-Type: application/json");

            echo json_encode($lista_painel);
        }
		else {
			
			header('Location: ' . base_url(),false,403);
		}
    }
}

?>