<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Admin_model extends CI_Model {

    // FILAS

    public function buscaFila($id_fila)  {

        return $this->db->query('select * from fila where id_fila = ' . $id_fila)->row();
      
    }

    public function buscaFilas($fixas = TRUE)  {

        if($fixas) {
            return $this->db->query('select * from fila where id_fila <= 6')->result_array();

        }
        else {

            return $this->db->query('select * from fila where id_fila > 6')->result_array();
        }

        
      
    }

    public function atualizaFila($dados) {

        $valores = array(
            'nome_fila' => $dados['nome_fila'],
            'status_fila' => $dados['status_fila'],
            // 'requer_equipamento_fila' => $dados['requer_equipamento_fila'],
        );
        
        $this->db->where('id_fila', $dados['id_fila']);
        $this->db->update('fila', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ATUALIZAR_FILA',
            'desc_evento' => 'NOME FILA:' . $dados['nome_fila'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function insereFila($dados) {

        $valores = array(
            'nome_fila' => $dados['nome_fila'],
            'status_fila' => $dados['status_fila'],
            // 'requer_equipamento_fila' => $dados['requer_equipamento_fila'],
        );
        
        $this->db->insert('fila', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERIR_FILA',
            'desc_evento' => 'NOME FILA:' . $dados['nome_fila'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );
        
        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------
    }

    public function buscaUltimoFila() {

        return $this->db->query("SELECT * FROM fila ORDER BY id_fila DESC limit 1")->row();
    }

    public function listaEventos() {

        return $this->db->query("SELECT id_evento,acao_evento,desc_evento,nome_usuario, data_evento FROM evento
        INNER JOIN usuario
        ON evento.id_usuario_evento = usuario.id_usuario
        ORDER BY id_evento DESC LIMIT 500")->result();
    }


         
}


?>