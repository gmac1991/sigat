<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Secretaria_model extends CI_Model {
    public function buscaSecretarias() {
        return $this->db->query("
            SELECT *
            FROM secretaria
            ORDER BY sigla_secretaria ASC
        ")->result_array();
    }

    public function insereSecretaria($dados) {
        $valores = array(
            'id_secretaria' =>          NULL,
            'nome_secretaria' =>        $dados['nome_secretaria'],
            'sigla_secretaria' =>       $dados['sigla_secretaria'],
            'status_secretaria' =>      $dados['status_secretaria'],
            'ultima_alteracao' =>       $dados['ultima_alteracao'],
        );

        $this->db->trans_start();
        $this->db->insert('secretaria', $valores);
        $query = $this->db->query('SELECT id_secretaria from secretaria order by id_secretaria desc LIMIT 1');
        $linha = $query->row_array();
        $valores['id_secretaria'] = $linha['id_secretaria'];

        $this->db->trans_complete();

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'INSERIR_SECRETARIA',
            'desc_evento' => 'ID SECRETARIA: ' . $valores['id_secretaria'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }

    public function atualizaSecretaria($dados) {

        $valores = array(
            'id_secretaria' =>          $dados['id_secretaria'],
            'nome_secretaria' =>        $dados['nome_secretaria'],
            'sigla_secretaria' =>       $dados['sigla_secretaria'],
            'status_secretaria' =>      $dados['status_secretaria'],
            'ultima_alteracao' =>       $dados['ultima_alteracao'],
        );

        $this->db->where('id_secretaria', $dados['id_secretaria']);
        $this->db->update('secretaria', $valores);

        // ------------ LOG -------------------

        $log = array(
            'acao_evento' => 'ALTERAR_SECRETARIA',
            'desc_evento' => 'ID SECRETARIA: ' . $dados['id_secretaria'] ,
            'id_usuario_evento' => $_SESSION['id_usuario']
        );

        $this->db->insert('evento', $log);

        // -------------- /LOG ----------------

        return $valores;
    }
}