<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Equipamento extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->model("consultas_model"); //carregando o model das consultas
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model('equipamento_model'); //carregando o medel equipamento
    }

    public function index($id) {

        
        if (isset($_SESSION['id_usuario'])){
            
            
            $dados = array();
            $dados['id'] = $id;
            $dados['bg_info'] = false;
            $dados['equip_existe'] = true;

            $info_equip = $this->equipamento_model->buscarInfoEquipamento($id);
            //$this->dd->dd($info_equip);
            $usuarios_equip = $this->equipamento_model->buscarUsuariosEquipamento($id);
           
            //se obtiver informações do equipamento no BG Info
            if(!empty($info_equip)) {
                $dados['bg_info'] = true;
                //Extraindo os valores retornados pelo BG info
                $ip = explode(' ', $info_equip[0]['IP_2']);
                $info_equip[0]['IP_2'] = $ip[0];
                $espaco_livre = explode(' ',$info_equip[0]['Free_Space']);
                $espco_total = explode(' ',$info_equip[0]['Volumes']);
                $dados['info_equipamento'] = $info_equip[0];
                $armazenamento = array();
                //Coletando dados e calculando armazenamento por unidade
                for ($i = 0; $i < sizeof($espco_total) -1; $i += 3){
                    if(isset($espco_total[$i + 2])){
                        $percentagem = round((floatval($espco_total[$i + 1]) - floatval($espaco_livre[$i + 1])) / floatval($espco_total[$i + 1]) * 100);
                        $armazenamento[$i]['percentagem'] = $percentagem;
                        $armazenamento[$i]['unidade'] = $espco_total[$i];
                        $armazenamento[$i]['espaco_total'] = $espco_total[$i + 1] . ' ' . $espco_total[$i + 2];
                        $armazenamento[$i]['livre'] = $espaco_livre[$i + 1] . ' ' . $espaco_livre[$i + 2];
                    }
                }
                $dados['armazenamento'] = $armazenamento;
            }

            if(!empty($usuarios_equip)){ //Coletando informações dos usuários que utilizaram o equipamento
               $dados['usuarios_equip'] = $usuarios_equip;
            }
            
            $usuario = $this->usuario_model->buscaUsuario($_SESSION['id_usuario']);

           $descricao = $this->equipamento_model->buscaDescEquipamento($id);
           
           if($descricao == null){

                $descricao = 'Equipamento Inexistente!';
                $dados['equip_existe'] = false;
                $url = @file_get_contents($this->config->item('api_sim') . $id); //Coletando descrição da API SIM

                $obj = json_decode($url);
                if (isset($obj->descrBem)){
                 $descricao = $obj->descrBem;
                 $dados['equip_existe'] = true;
                }


           }else{
            $dados_equipamento = $this->equipamento_model->buscaChamadosEquipamento($id);
            $dados['dados_equipamento'] = $dados_equipamento;
           }
           $dados['descricao'] = $descricao;
           $this->load->view('templates/cabecalho', $usuario);
           $this->load->view('paginas/equipamento', $dados);
           $this->load->view('templates/rodape');
           
        }else {

            header('Location: ' . base_url('/painel'));
        }
    
    }

    public function registra_lacre() {
        $id_reparo_servico = $this->input->post('id_reparo_servico');
        $num_equipamento = $this->input->post('num_equipamento');
        $tag_equipamento = $this->input->post('tag_equipamento');

        if (preg_match('/^[a-zA-Z0-9]/', $tag_equipamento)) {
            $this->equipamento_model->alterarTag($num_equipamento, $tag_equipamento, $id_reparo_servico);
        } else {
            
        }
    }

}