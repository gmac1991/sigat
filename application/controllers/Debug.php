<?php

date_default_timezone_set('America/Sao_Paulo');


defined('BASEPATH') OR exit('No direct script access allowed');

class Debug extends CI_Controller {

    function __construct() {
        parent::__construct();

        $this->load->library('form_validation');
        $this->load->model("chamado_model"); //carregando o model chamado
        $this->load->model("interacao_model"); //carregando o model interacoes
        $this->load->model("usuario_model"); //carregando o model usuario
        $this->load->model("equipamento_model"); //carregando o model usuario
        $this->load->model("inservivel_model"); //carregando o model usuario
        $this->load->model("reparo_model"); //carregando o model usuario
        $this->load->model("bancada_model"); //carregando o model usuario


        
    }



    public function remessa() {
        $nao_incluir_remessa = function() {
            $result = $this->reparo_model->buscarReparoServicos(477);

            foreach ($result as $servico) {
                if ($servico->id_servico == 42 && $servico->realizado_reparo_servico == true)
                    return true;
            }

            return false;
        };
        header('Content-Type: application/json');
        $this->dd->dd($nao_incluir_remessa());

        $this->dd->dd($nao_incluir_remessa());

    }
}