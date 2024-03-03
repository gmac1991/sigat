<?php

defined('BASEPATH') OR exit('No direct script access allowed');

date_default_timezone_set('America/Sao_Paulo');

class Bancada_model extends CI_Model {


    public function listaBancadas($status = 0, $nome_bancada = null) {

        //status = 0 -> livres
        //status != 0 -> todos
        $this->db->select('id_bancada, nome_bancada, status_bancada, ocupado_bancada');
        $this->db->from('bancada');

        if ($status == 0) {
            $this->db->where('ocupado_bancada',0);
        }
        if($nome_bancada != null){
            $this->db->where('nome_bancada', $nome_bancada);
        }

        return $this->db->get()->result_array();
        
    }

    public function atualizarBancada($id_bancada,$ocupado, $status = null) {
        $out = FALSE;

        $this->db->set('ocupado_bancada', $ocupado);
        if($status !== null) {
            $this->db->set('status_bancada', $status);
        }
        $this->db->where('id_bancada', $id_bancada);

        $nome_status = "OCUPADO";

        if ($ocupado == 0) {
            $nome_status = "LIVRE";
        }

        $novo_status = "ATIVO";
        if($status !== null){
            if($status == false) $novo_status = "INATIVO";
        }
        

      

        
        
       if ($this->db->update('bancada')) {
            $out = TRUE;
            // ------------ LOG -------------------
            if($status === null){
                $log = array(
                    'acao_evento' => 'ALTERAR_OCUPACAO_BANCADA',
                    'desc_evento' => 'ID_BANCADA: ' . $id_bancada . ' - NOVO STATUS: ' . $nome_status,
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
            }else{
                $log = array(
                    'acao_evento' => 'ALTERAR_STATUS_BANCADA',
                    'desc_evento' => 'ID_BANCADA: ' . $id_bancada . ' - NOVO STATUS: ' . $novo_status,
                    'id_usuario_evento' => $_SESSION['id_usuario']
                );
            }
            
            
            $this->db->insert('evento', $log);

            // -------------- /LOG ----------------
       }

       return $out;

        
        
    }

    public function buscaBancada($id_bancada) {

        $this->db->from('bancada');
        $this->db->where('id_bancada',$id_bancada);

        return $this->db->get()->row();
        
    }

    public function inserirBancada($dados){

        $valores = array(
            'nome_bancada' => $dados['nome_bancada']
        );

        $this->db->insert('bancada', $dados);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERIR_NOVA_BANCADA',
            'desc_evento' => 'NOME_BANCADA: ' . $dados['nome_bancada'],
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

    }
}