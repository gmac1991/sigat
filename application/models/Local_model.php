<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Local_model extends CI_Model {
    public function buscaFila($id_fila)  {

        return $this->db->query('select * from fila where id_fila = ' . $id_fila)->row();
      
    }

    public function buscaLocais()  {
        return $this->db->query("
            SELECT local.*, secretaria.nome_secretaria, secretaria.sigla_secretaria, secretaria.id_secretaria
            FROM local
            INNER JOIN secretaria ON local.secretaria_local = secretaria.id_secretaria;    
        ")->result_array();
    }

    public function buscaSecretarias() {
        return $this->db->query("
            SELECT id_secretaria, nome_secretaria, sigla_secretaria
            FROM secretaria
            WHERE status_secretaria = true
            ORDER BY sigla_secretaria ASC
        ")->result_array();
    }

    public function insereLocal($dados) {
        $valores = array(
            'id_local' =>             NULL,
            'nome_local' =>           $dados['nome_local'],
            'endereco_local' =>       $dados['endereco_local'],
            'secretaria_local' =>     $dados['secretaria_local'],
            'regiao_local' =>         $dados['regiao_local'],
        );

        $this->db->trans_start();
        $this->db->insert('local', $valores);
        $query = $this->db->query('SELECT id_local from local order by id_local desc LIMIT 1');
        $linha = $query->row_array();
        $valores['id_local'] = $linha['id_local'];

        $this->db->trans_complete();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERE_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $valores['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function atualizaLocal($dados) {

        $valores = array(
            'id_local' =>             $dados['id_local'],
            'nome_local' =>           $dados['nome_local'],
            'endereco_local' =>       $dados['endereco_local'],
            'secretaria_local' =>     $dados['secretaria_local'],
            'regiao_local' =>         $dados['regiao_local'],
        );

        $this->db->where('id_local', $dados['id_local']);
        $this->db->update('local', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_LOCAL',
            'desc_evento' => 'ID LOCAL: ' . $dados['id_local'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }
}